<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use App\Models\MKendaraan;
use App\Models\TSpk;
use App\Models\MManpower;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource (untuk driver).
     */
    public function index()
    {
        $userId = session('user_id');
        $manpower = MManpower::where('mp_id', $userId)->first();
        $manpowerId = $manpower ? $manpower->id : null;

        $deliveries = TShippingDeliveryHeader::with(['kendaraan', 'driver'])
            ->when($manpowerId, function($q) use ($manpowerId) {
                return $q->where('driver_id', $manpowerId);
            })
            ->orderBy('tanggal_berangkat', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Fetch SJs that are already in an active/finished delivery trip
        $usedSjs = TShippingDeliveryHeader::whereNotNull('no_surat_jalan')
            ->where('status', '!=', 'OPEN') // If still OPEN, it means it's pending for scan/start
            ->pluck('no_surat_jalan')
            ->unique()
            ->all();

        $pendingSjs = TSpk::with(['customer', 'plantgate'])
            ->whereNotNull('no_surat_jalan')
            ->when($manpowerId, function($q) use ($manpowerId) {
                return $q->where('driver_id', $manpowerId);
            })
            ->whereNotIn('no_surat_jalan', $usedSjs)
            ->orderBy('updated_at', 'desc')
            ->get();
        
        return view('shipping.delivery', compact('deliveries', 'pendingSjs'));
    }

    /**
     * Show scan page for a specific SJ
     */
    public function scan(TSpk $spk)
    {
        if (!userCan('shipping.delivery.create')) {
            abort(403, 'Unauthorized action.');
        }

        $spk->load(['customer', 'plantgate']);
        return view('shipping.scan_sj', compact('spk'));
    }

    /**
     * Store delivery from SJ + Truck scan
     */
    public function storeFromScan(Request $request): \Illuminate\Http\JsonResponse

    {
        if (!userCan('shipping.delivery.create')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'spk_id' => 'required|exists:t_spk,id',
            'kendaraan_barcode' => 'required|string', // Nopol
        ]);

        try {
            // Set Timezone to Asia/Jakarta
            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            
            $spk = TSpk::findOrFail($request->spk_id);
            
            // Get Driver from Session
            if (!session()->has('user_id')) {
                return response()->json(['success' => false, 'message' => 'Sesi berakhir. Silakan login kembali.'], 401);
            }
            $userId = session('user_id');
            $manpower = \App\Models\MManpower::where('mp_id', $userId)->first();
            if (!$manpower) {
                return response()->json(['success' => false, 'message' => 'Data Manpower Anda tidak ditemukan.'], 404);
            }

            // Find truck by nopol
            $truck = MKendaraan::where('nopol_kendaraan', $request->kendaraan_barcode)->first();
            if (!$truck) {
                return response()->json([
                    'success' => false,
                    'message' => 'Truck dengan Nopol ' . $request->kendaraan_barcode . ' tidak ditemukan.'
                ], 404);
            }

            // Validasi: Apakah Truck yang di-scan sesuai dengan yang di-assign di SPK?
            if ($spk->nomor_plat) {
                $spkPlat = strtoupper(str_replace(' ', '', $spk->nomor_plat));
                $scanPlat = strtoupper(str_replace(' ', '', $truck->nopol_kendaraan));
                
                if ($spkPlat !== $scanPlat) {
                    return response()->json([
                        'success' => false,
                        'message' => "Scan Gagal! SPK ini ditugaskan untuk truck {$spk->nomor_plat}, tetapi Anda menscan {$truck->nopol_kendaraan}."
                    ], 422);
                }
            }

            // Logic: Cari apakah sudah ada "Plan" untuk truck ini hari ini yang statusnya OPEN?
            // Prioritaskan yang nomor SJ nya sama jika sudah di-assign via Dispatch
            $deliveryHeader = TShippingDeliveryHeader::where('kendaraan_id', $truck->id)
                ->whereDate('tanggal_berangkat', $today)
                ->where('status', 'OPEN')
                ->where('no_surat_jalan', $spk->no_surat_jalan)
                ->first();
            
            if (!$deliveryHeader) {
                // Fallback: Cari header OPEN manapun untuk truck ini
                $deliveryHeader = TShippingDeliveryHeader::where('kendaraan_id', $truck->id)
                    ->whereDate('tanggal_berangkat', $today)
                    ->where('status', 'OPEN')
                    ->first();
            }

            // Calculate status based on SPK's planned departure time
            $status = 'NORMAL';
            if ($spk->jam_berangkat_plan) {
                // Combine today's date with SPK's planned time
                $planDateTime = Carbon::parse($today . ' ' . $spk->jam_berangkat_plan, 'Asia/Jakarta');
                $diffInMinutes = $now->diffInMinutes($planDateTime, false); // false = signed diff (negative if now > plan)

                // Logic Status:
                // ADVANCED: Berangkat lebih awal > 5 menit (now is before plan, diff is positive)
                // DELAY: Terlambat > 5 menit (now is after plan, diff is negative) - SUDAH ADA ACTUAL
                // NORMAL: Toleransi +/- 5 menit
                if ($diffInMinutes > 5) {
                    $status = 'ADVANCED'; // Early > 5 mins (e.g. plan 16:00, actual 09:00, diff = +420 mins)
                } elseif ($diffInMinutes < -5) {
                    $status = 'DELAY'; // Late > 5 mins (e.g. plan 08:00, actual 09:00, diff = -60 mins)
                } else {
                    $status = 'NORMAL'; // Within tolerance
                }
            }

            // Construct Full Destination Name
            $custName = $spk->customer->nama_perusahaan ?? '-';
            $gateName = $spk->plantgate->nama_plantgate ?? '';
            $fullDestination = $custName . ($gateName ? " ($gateName)" : "");

            $delivery = DB::transaction(function () use ($spk, $truck, $manpower, $deliveryHeader, $now, $status, $today, $fullDestination) {
                if ($deliveryHeader) {
                    // Update PLAN existing
                    $deliveryHeader->update([
                        'driver_id' => $manpower->id,
                        'no_surat_jalan' => $spk->no_surat_jalan,
                        'destination' => $fullDestination,
                        'status' => $status,
                        'waktu_berangkat' => $now, // Actual departure time set here
                        'keterangan' => 'Updated via scan by ' . $manpower->nama . '. Status: ' . $status,
                    ]);
                } else {
                    // Create NEW if no plan exists
                    $deliveryHeader = TShippingDeliveryHeader::create([
                        'periode' => $now->format('Y-m'),
                        'kendaraan_id' => $truck->id,
                        'driver_id' => $manpower->id,
                        'destination' => $fullDestination,
                        'no_surat_jalan' => $spk->no_surat_jalan,
                        'tanggal_berangkat' => $now,
                        'waktu_berangkat' => $now,
                        'status' => $status,
                        'total_trip' => 0,
                        'total_delivered' => 0,
                        'keterangan' => 'Auto-created from SJ Scan by ' . $manpower->nama,
                    ]);
                }

                // INTEGRASI CONTROL TRUCK:
                // Cari apakah ada Plan Detail (BERANGKAT) untuk dicopy keterangannya
                $planDetail = TShippingDeliveryDetail::where('delivery_header_id', $deliveryHeader->id)
                    ->where('keterangan', 'like', 'BERANGKAT%')
                    ->first();

                $keteranganActual = 'BERANGKAT|Actual Scan';
                if ($planDetail) {
                    // Reuse format: BERANGKAT|C1|SJ:..|SPK:..
                    $keteranganActual = $planDetail->keterangan; 
                } else {
                    // Fallback construct
                    $cycle = $spk->cycle_number ?? 1;
                    $keteranganActual = "BERANGKAT|C{$cycle}|SJ:{$spk->no_surat_jalan}|SPK:{$spk->nomor_spk}";
                }

                // Buat detail untuk "ACTUAL" (Jam saat ini)
                TShippingDeliveryDetail::create([
                    'delivery_header_id' => $deliveryHeader->id,
                    'tanggal' => $today,
                    'jam' => (int)$now->format('H'),
                    'status' => 'IN_TRANSIT',
                    'waktu_update' => $now,
                    'keterangan' => $keteranganActual, // Same signature as Plan so Board recognizes it
                    'lokasi_saat_ini' => 'Departed (Actual) via scan'
                ]);

                // --- DEPARTURE ALERT (DRIVER SCAN) ---
                try {
                    $targetOps = env('FONNTE_GROUP_OPS', '0812xxxx');
                    
                    // Construct Message
                    $msgDep = "🚛 *INFO PENGIRIMAN BERANGKAT (OTW)*\n\n";
                    $msgDep .= "Truck: *{$truck->nopol_kendaraan}*\n";
                    $msgDep .= "Surat Jalan: {$spk->no_surat_jalan}\n";
                    $msgDep .= "Tujuan: {$fullDestination}\n\n";
                    
                    $msgDep .= "Plan Jam: {$spk->jam_berangkat_plan}\n";
                    $msgDep .= "Actual Jam: " . $now->format('H:i') . "\n";
                    $msgDep .= "Status Waktu: *{$status}*\n\n";
                    
                    $msgDep .= "_Scan by {$manpower->nama}_";

                    // \App\Helpers\FonnteHelper::send($targetOps, $msgDep);
                } catch (\Exception $eDep) {
                    // Ignore
                }

                return $deliveryHeader;
            });

            return response()->json([
                'success' => true,
                'message' => "Scan Berhasil! Status Delivery: {$status}. Driver: {$manpower->nama}",
                'redirect' => route('shipping.delivery.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Store From Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!userCan('shipping.delivery.create')) {
            abort(403, 'Unauthorized action.');
        }

        $trucks = MKendaraan::active()
            ->orderBy('nopol_kendaraan')
            ->get();
        
        $drivers = \App\Models\MManpower::orderBy('nama')->get();
        
        return view('shipping.create', compact('trucks', 'drivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!userCan('shipping.delivery.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'kendaraan_id' => 'required|exists:M_Kendaraan,id',
            'driver_id' => 'nullable|exists:m_manpower,id',
            'destination' => 'nullable|string|max:255',
            'no_surat_jalan' => 'nullable|string|max:100',
            // 'tanggal_berangkat' => 'required|date', // Removed as per request
            'waktu_berangkat' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        // Generate periode (YYYY-MM)
        // Use server time for tanggal_berangkat
        $tanggalBerangkat = Carbon::now();
        $periode = $tanggalBerangkat->format('Y-m');

        $delivery = TShippingDeliveryHeader::create([
            'periode' => $periode,
            'kendaraan_id' => $validated['kendaraan_id'],
            'driver_id' => $validated['driver_id'] ?? null,
            'destination' => $validated['destination'] ?? '-', // Default to dash if not provided
            'no_surat_jalan' => $validated['no_surat_jalan'] ?? null,
            'tanggal_berangkat' => $tanggalBerangkat,
            'waktu_berangkat' => $validated['waktu_berangkat'] ? Carbon::parse($validated['waktu_berangkat']) : Carbon::now(),
            'status' => 'OPEN',
            'total_trip' => 0,
            'total_delivered' => 0,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data delivery berhasil ditambahkan',
            'data' => $delivery
        ]);
    }

    /**
     * Report Arrival at Destination (Driver)
     */
    public function reportArrival(Request $request, TShippingDeliveryHeader $delivery): \Illuminate\Http\JsonResponse

    {
        if (!userCan('shipping.delivery.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'foto' => 'required|image|max:5120', // Max 5MB
            'lokasi' => 'nullable|string',
        ]);

        try {
            $now = Carbon::now('Asia/Jakarta');
            
            // Handle Image Upload
            $path = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = 'proof_' . $delivery->id . '_' . $now->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('delivery_proofs', $filename, 'public');
            }

            DB::transaction(function () use ($delivery, $now, $path, $request) {
                // Parse Locations (GPS)
                $latitude = null;
                $longitude = null;
                if ($request->lokasi && strpos($request->lokasi, ',') !== false) {
                    $parts = explode(',', $request->lokasi);
                    $latitude = trim($parts[0]);
                    $longitude = trim($parts[1]);
                }

                // Update Header Status
                $delivery->update([
                    'status' => 'ARRIVED',
                    'waktu_tiba' => $now,
                    'keterangan' => $delivery->keterangan . "\nArrived reported at " . $now->format('Y-m-d H:i')
                ]);

                // INTEGRASI CONTROL TRUCK:
            // Cari detail BERANGKAT untuk ambil Cycle info
            $berangkatDetail = TShippingDeliveryDetail::where('delivery_header_id', $delivery->id)
                ->where('keterangan', 'like', 'BERANGKAT%')
                ->first();
            
            $keteranganDatang = 'DATANG|Actual';
            if ($berangkatDetail) {
                // Format: BERANGKAT|C1 -> DATANG|C1
                $parts = explode('|', $berangkatDetail->keterangan);
                if (isset($parts[0])) $parts[0] = 'DATANG';
                $keteranganDatang = implode('|', $parts);
            }

            // Buat detail untuk "DATANG ACTUAL" (Jam saat ini)
            TShippingDeliveryDetail::updateOrCreate(
                [
                    'delivery_header_id' => $delivery->id,
                    'tanggal' => $now->format('Y-m-d'),
                    'jam' => (int)$now->format('H'),
                ],
                [
                    'status' => 'ARRIVED',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'waktu_update' => $now,
                    'lokasi_saat_ini' => 'Tiba di Tujuan',
                    'foto_bukti' => $path,
                    'keterangan' => $keteranganDatang
                ]
            );          });

            // --- ARRIVAL ALERT ---
            try {
                $targetOps = env('FONNTE_GROUP_OPS', '0812xxxx');
                $truck = $delivery->kendaraan->nopol_kendaraan ?? '-';
                $dest = $delivery->destination ?? '-';
                
                $msgArr = "🏁 *INFO KEDATANGAN (ARRIVAL)*\n\n";
                $msgArr .= "Truck: *{$truck}*\n";
                $msgArr .= "Tujuan: {$dest}\n";
                $msgArr .= "Waktu: " . $now->format('H:i') . "\n";
                $msgArr .= "_Status: TIBA DI LOKASI_";
                
                // \App\Helpers\FonnteHelper::send($targetOps, $msgArr);
            } catch (\Exception $eArr) {
                Log::error('Arrival Alert Error: ' . $eArr->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Kedatangan berhasil dilaporkan. Terima kasih!',
                'redirect' => route('shipping.delivery.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Report Arrival Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melaporkan kedatangan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finish Trip (Balik PT) - Triggers 'DATANG ACTUAL' on Board
     */
    public function finishTrip(Request $request, TShippingDeliveryHeader $delivery): \Illuminate\Http\JsonResponse

    {
        if (!userCan('shipping.delivery.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $now = Carbon::now('Asia/Jakarta');
            
            DB::transaction(function () use ($delivery, $now) {
                // Update Header
                $delivery->update([
                    'status' => 'COMPLETED',
                    'waktu_tiba' => $now // Usually waktu_tiba is arrival at destination, but for full cycle it might be back to base?
                    // Let's assume 'waktu_tiba' in header tracks completion of job in this context.
                ]);

                // Construct Keterangan from BERANGKAT detail
                $berangkatDetail = TShippingDeliveryDetail::where('delivery_header_id', $delivery->id)
                    ->where('keterangan', 'like', 'BERANGKAT%')
                    ->first();
                
                $keteranganDatang = 'DATANG|Actual';
                if ($berangkatDetail) {
                    // Format: BERANGKAT|C1|... -> DATANG|C1|...
                    $parts = explode('|', $berangkatDetail->keterangan);
                    if (isset($parts[0])) $parts[0] = 'DATANG';
                    $keteranganDatang = implode('|', $parts);
                }

                // Create DATANG Detail
                TShippingDeliveryDetail::create([
                    'delivery_header_id' => $delivery->id,
                    'tanggal' => $now->format('Y-m-d'),
                    'jam' => (int)$now->format('H'),
                    'status' => 'COMPLETED',
                    'waktu_update' => $now,
                    'keterangan' => $keteranganDatang,
                    'lokasi_saat_ini' => 'Kembali ke PT (Finish)'
                ]);

                // Cleanup GPS Logs (User Request)
                // Remove raw GPS data to manage storage, keeping only the milestone details
                \App\Models\TGpsLog::where('delivery_header_id', $delivery->id)->delete();
            });

            // --- RETURN ALERT ---
            try {
                $targetOps = env('FONNTE_GROUP_OPS', '0812xxxx');
                $truck = $delivery->kendaraan->nopol_kendaraan ?? '-';
                
                $msgFin = "🏠 *INFO KEMBALI (RETURN)*\n\n";
                $msgFin .= "Truck: *{$truck}*\n";
                $msgFin .= "Status: KEMBALI DI POOL/PT\n";
                $msgFin .= "Waktu: " . $now->format('H:i') . "\n";
                $msgFin .= "_Armada Ready Next Trip_";
                
                // \App\Helpers\FonnteHelper::send($targetOps, $msgFin);
            } catch (\Exception $eFin) {
                Log::error('Return Alert Error: ' . $eFin->getMessage());
            }


            return response()->json([
                'success' => true,
                'message' => 'Trip Finished! Armada siap untuk cycle berikutnya.',
                'redirect' => route('shipping.delivery.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Finish Trip Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan trip: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show report arrival page
     */
    public function showArrivalForm(TShippingDeliveryHeader $delivery)
    {
        return view('shipping.report_arrival', compact('delivery'));
    }

    /**
     * Display the specified resource.
     */
    public function show(TShippingDeliveryHeader $delivery)
    {
        $delivery->load(['kendaraan', 'driver', 'details']);
        return response()->json($delivery);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TShippingDeliveryHeader $delivery)
    {
        if (!userCan('shipping.delivery.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $delivery->load(['kendaraan', 'driver']);
        $trucks = MKendaraan::active()
            ->orderBy('nopol_kendaraan')
            ->get();
        
        $drivers = \App\Models\MManpower::orderBy('nama')->get();
        
        return view('shipping.edit', compact('delivery', 'trucks', 'drivers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TShippingDeliveryHeader $delivery)
    {
        if (!userCan('shipping.delivery.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'kendaraan_id' => 'required|exists:M_Kendaraan,id',
            'driver_id' => 'nullable|exists:M_Manpower,id',
            'destination' => 'required|string|max:255',
            'no_surat_jalan' => 'nullable|string|max:100',
            'tanggal_berangkat' => 'required|date',
            'waktu_berangkat' => 'nullable|date',
            'waktu_tiba' => 'nullable|date',
            'status' => 'required|in:OPEN,IN_TRANSIT,ARRIVED,DELIVERED,CANCELLED',
            'keterangan' => 'nullable|string',
        ]);

        // Update periode if tanggal_berangkat changed
        $tanggalBerangkat = Carbon::parse($validated['tanggal_berangkat']);
        $periode = $tanggalBerangkat->format('Y-m');

        $delivery->update([
            'periode' => $periode,
            'kendaraan_id' => $validated['kendaraan_id'],
            'driver_id' => $validated['driver_id'] ?? null,
            'destination' => $validated['destination'],
            'no_surat_jalan' => $validated['no_surat_jalan'] ?? null,
            'tanggal_berangkat' => $tanggalBerangkat,
            'waktu_berangkat' => $validated['waktu_berangkat'] ? Carbon::parse($validated['waktu_berangkat']) : null,
            'waktu_tiba' => $validated['waktu_tiba'] ? Carbon::parse($validated['waktu_tiba']) : null,
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data delivery berhasil diupdate'
        ]);
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(TShippingDeliveryHeader $delivery)
    {
        $delivery->load(['kendaraan', 'driver']);
        return view('shipping.delete', compact('delivery'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TShippingDeliveryHeader $delivery)
    {
        if (!userCan('shipping.delivery.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $delivery->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data delivery berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus delivery: ' . $e->getMessage()
            ], 500);
        }
    }
}
