<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use App\Models\MKendaraan;
use App\Models\TSpk;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ControlTruckController extends Controller
{
    /**
     * Tampilkan halaman control truck dengan tabel input langsung
     */
    public function monitoring(Request $request)
    {
        if (!userCan('shipping.controltruck.view')) {
            abort(403, 'Unauthorized action.');
        }

        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $nomorPlat = $request->input('nomor_plat');
        
        // Parse tanggal
        $selectedDate = Carbon::parse($tanggal);
        
        // Get all trucks (kendaraan) yang punya delivery di tanggal tersebut
        $deliveryHeaders = TShippingDeliveryHeader::with(['kendaraan', 'driver', 'details'])
            ->whereDate('tanggal_berangkat', $tanggal)
            ->get();
        
        // Get all kendaraan
        $kendaraanQuery = MKendaraan::orderBy('nopol_kendaraan');
        
        if ($nomorPlat) {
            $kendaraanQuery->where('nopol_kendaraan', 'like', "%{$nomorPlat}%");
        }
        
        $kendaraans = $kendaraanQuery->get();
        
        // Get SPKs for the date (Planning from PPIC)
        $spkQuery = TSpk::with(['customer', 'plantgate', 'driver']);
        
        // We want SPKs that are EITHER:
        // 1. Dated for today
        // 2. OR already linked to an active delivery today
        $activeSjs = $deliveryHeaders->pluck('no_surat_jalan')->filter()->unique();
        
        $spks = $spkQuery->where(function($q) use ($tanggal, $activeSjs) {
                $q->whereDate('tanggal', $tanggal)
                  ->orWhereIn('no_surat_jalan', $activeSjs);
            })
            ->get();
        
        $trucks = [];
        $timeSlots = array_merge(
            ['07.00', '08.00', '09.00', '10.00', '11.00', '12.00', '13.00', '14.00', '15.00', '16.00'],
            ['17.00', '18.00', '19.00', '20.00', '21.00', '22.00', '23.00']
        );

        // 1. ADD UNASSIGNED SPKs as individual rows at the TOP
        $unassignedSpks = $spks->whereNull('nomor_plat');
        if ($unassignedSpks->isNotEmpty()) {
            $times = ['DATANG' => ['PLAN' => [], 'ACTUAL' => []], 'BERANGKAT' => ['PLAN' => [], 'ACTUAL' => []]];
            
            // Aggregate planning times for visualization
            foreach ($unassignedSpks as $spk) {
                if ($spk->jam_berangkat_plan) {
                    $hourPart = explode(':', $spk->jam_berangkat_plan)[0];
                    $hourSlot = str_pad($hourPart, 2, '0', STR_PAD_LEFT) . '.00';
                    $cycleNum = $spk->cycle_number ?? 1;
                    $cycleStr = 'C' . $cycleNum;
                    $timeStr = $spk->jam_berangkat_plan . ($cycleStr ? ' ' . $cycleStr : '');
                    if (!isset($times['BERANGKAT']['PLAN'][$hourSlot])) {
                        $times['BERANGKAT']['PLAN'][$hourSlot] = [];
                    }
                    $times['BERANGKAT']['PLAN'][$hourSlot][] = $timeStr;
                }
            }
            
            // Create individual rows for each unassigned SPK (max 4)
            $customers = [];
            $actualCycles = [];
            
            foreach ($unassignedSpks as $spk) {
                $cycleNum = $spk->cycle_number ?? $spk->cycle ?? 1;
                $actualCycles[] = [
                    'actual_cycle' => $cycleNum,
                    'spk' => $spk,
                    'delivery' => null,
                    'sj' => $spk->no_surat_jalan ?? '-'
                ];
            }
            
            // Sort by cycle number
            usort($actualCycles, function($a, $b) {
                return $a['actual_cycle'] <=> $b['actual_cycle'];
            });
            
            // Take first 4 cycles
            $cyclesToDisplay = array_slice($actualCycles, 0, 4);
            
            // Create rows for each SPK
            for ($slot = 0; $slot < 4; $slot++) {
                if (isset($cyclesToDisplay[$slot])) {
                    $cycleData = $cyclesToDisplay[$slot];
                    $spk = $cycleData['spk'];
                    $actualCycleNum = $cycleData['actual_cycle'];
                    
                    $custName = '-';
                    if ($spk->customer) {
                        $custName = $spk->customer->nama_perusahaan ?? '-';
                        if ($spk->plantgate) {
                            $custName .= " ({$spk->plantgate->nama_plantgate})";
                        }
                    }
                    
                    $customers[] = [
                        'customer_id' => 0,
                        'customer_name' => $custName,
                        'driver_name' => $spk->driver->nama ?? '-',
                        'surat_jalan' => $spk->no_surat_jalan ?? '-',
                        'status' => 'BELUM DITUGASKAN',
                        'actual_cycle' => $actualCycleNum,
                        'times' => $times
                    ];
                } else {
                    // Empty slot
                    $customers[] = [
                        'customer_id' => 0,
                        'customer_name' => '-',
                        'driver_name' => '-',
                        'surat_jalan' => '-',
                        'status' => 'OPEN',
                        'actual_cycle' => null,
                        'times' => $times
                    ];
                }
            }
            
            $trucks[] = [
                'id' => 0,
                'nopol' => 'PENDING (BELUM ASSIGN)',
                'driver' => '-',
                'status' => 'PENDING',
                'customers' => $customers,
                'is_pending' => true
            ];
        }

        // 2. Process all existing real trucks
        foreach ($kendaraans as $kendaraan) {
            $truckDeliveries = $deliveryHeaders->where('kendaraan_id', $kendaraan->id);
            $latestDelivery = $truckDeliveries->sortBy('id')->last();
            
            // Initialize aggregate structure
            $times = [
                'DATANG' => ['PLAN' => [], 'ACTUAL' => []],
                'BERANGKAT' => ['PLAN' => [], 'ACTUAL' => []]
            ];
            
            // A. Aggregate details from Delivery Headers
            foreach ($truckDeliveries as $header) {
                // FALLBACK: If Header has waktu_berangkat but no BERANGKAT detail exists, 
                // we should show it in the ACTUAL row.
                if ($header->waktu_berangkat) {
                    $wb = Carbon::parse($header->waktu_berangkat);
                    $hSlot = $wb->format('H') . '.00';
                    $cycleMarker = '';
                    if ($header->no_surat_jalan) {
                        $s = $spks->where('no_surat_jalan', $header->no_surat_jalan)->first();
                        if ($s) $cycleMarker = ' C' . ($s->cycle_number ?? 1);
                    }
                    if (!isset($times['BERANGKAT']['ACTUAL'][$hSlot])) {
                        $times['BERANGKAT']['ACTUAL'][$hSlot] = [['time' => $wb->format('H:i') . $cycleMarker, 'foto' => null]];
                    }
                }

                if ($header->waktu_tiba) {
                    $wt = Carbon::parse($header->waktu_tiba);
                    $hSlotTiba = $wt->format('H') . '.00';
                    $cycleMarkerTiba = '';
                    if ($header->no_surat_jalan) {
                        $s = $spks->where('no_surat_jalan', $header->no_surat_jalan)->first();
                        if ($s) $cycleMarkerTiba = ' C' . ($s->cycle_number ?? 1);
                    }
                    if (!isset($times['DATANG']['ACTUAL'][$hSlotTiba])) {
                        $times['DATANG']['ACTUAL'][$hSlotTiba] = [['time' => $wt->format('H:i') . $cycleMarkerTiba, 'foto' => null]];
                    }
                }

                if ($header->details) {
                    foreach ($header->details as $detail) {
                        $hourSlot = str_pad($detail->jam, 2, '0', STR_PAD_LEFT) . '.00';
                        if (!in_array($hourSlot, $timeSlots)) continue;

                        $waktuUpdate = $detail->waktu_update ? Carbon::parse($detail->waktu_update) : null;
                        $ket = $detail->keterangan ?? '';
                        $parts = explode('|', $ket);
                        $savedActivity = $parts[0] ?? ''; 
                        $cycle = $parts[1] ?? '';
                        
                        // Plan time mapping (from detail jam if no waktu_update)
                        $timeStr = $waktuUpdate ? $waktuUpdate->format('H:i') : str_pad($detail->jam, 2, '0', STR_PAD_LEFT) . ':00';
                        if ($cycle) $timeStr .= ' ' . $cycle;
                        
                        $data = [
                            'time' => $timeStr,
                            'foto' => $detail->foto_bukti ? Storage::url($detail->foto_bukti) : null
                        ];
                        
                        $isPlan = ($detail->status === 'OPEN' || !$waktuUpdate);
                        $type = $isPlan ? 'PLAN' : 'ACTUAL';
                        $activity = ($savedActivity === 'BERANGKAT') ? 'BERANGKAT' : 'DATANG';
                        
                        if (!isset($times[$activity][$type][$hourSlot])) {
                            $times[$activity][$type][$hourSlot] = [];
                        }

                        // Prevent duplicates for same Cycle in same Slot
                        $foundIndex = -1;
                        if ($cycle) {
                            foreach ($times[$activity][$type][$hourSlot] as $idx => $existingItem) {
                                $existingTime = is_array($existingItem) ? $existingItem['time'] : $existingItem;
                                $existingParts = explode(' ', $existingTime);
                                $existingCycle = count($existingParts) > 1 ? implode(' ', array_slice($existingParts, 1)) : '';
                                
                                if ($existingCycle === $cycle) {
                                    $foundIndex = $idx;
                                    break;
                                }
                            }
                        }

                        if ($foundIndex >= 0) {
                            // If we have a detail status like 'ARRIVED' or 'IN_TRANSIT', it might have actual info we want to keep?
                            // But here we are in the detail loop.
                            $times[$activity][$type][$hourSlot][$foundIndex] = $isPlan ? $timeStr : $data;
                        } else {
                            $times[$activity][$type][$hourSlot][] = $isPlan ? $timeStr : $data;
                        }
                    }
                }
            }

            // B. Build SPK planning into times grid
            // First, get all SPKs related to this truck's deliveries
            $relatedSjs = $truckDeliveries->pluck('no_surat_jalan')->filter()->unique();
            $relatedSpks = $spks->whereIn('no_surat_jalan', $relatedSjs);
            
            // Also include SPKs assigned to this truck nopol but not yet scanned/started
            $nopolClean = str_replace(' ', '', strtoupper(trim($kendaraan->nopol_kendaraan)));
            $unstartedSpks = $spks->filter(function($s) use ($nopolClean, $relatedSjs) {
                return str_replace(' ', '', strtoupper(trim($s->nomor_plat))) === $nopolClean 
                       && !$relatedSjs->contains($s->no_surat_jalan);
            });
            
            $allMySpks = $relatedSpks->concat($unstartedSpks);

            foreach ($allMySpks as $spk) {
                // Planning BERANGKAT
                if ($spk->jam_berangkat_plan) {
                    try {
                        $fmtTime = Carbon::parse($spk->jam_berangkat_plan)->format('H:i');
                    } catch (\Exception $e) {
                         $fmtTime = substr($spk->jam_berangkat_plan, 0, 5);
                    }
                    
                    $hourPart = explode(':', $fmtTime)[0];
                    $hourSlot = str_pad($hourPart, 2, '0', STR_PAD_LEFT) . '.00';
                    
                    if (in_array($hourSlot, $timeSlots)) {
                        $cycleNum = $spk->cycle_number ?? 1;
                        $cycleStr = 'C' . $cycleNum;
                        $timeStr = $fmtTime . ($cycleStr ? ' ' . $cycleStr : '');
                        
                        if (!isset($times['BERANGKAT']['PLAN'][$hourSlot])) $times['BERANGKAT']['PLAN'][$hourSlot] = [];
                        
                        // Deduplicate by Cycle
                        $foundIdx = -1;
                        foreach ($times['BERANGKAT']['PLAN'][$hourSlot] as $idx => $existing) {
                            $exTime = is_array($existing) ? $existing['time'] : $existing;
                            if (strpos($exTime, $cycleStr) !== false) {
                                $foundIdx = $idx;
                                break;
                            }
                        }
                        
                        if ($foundIdx >= 0) {
                            $times['BERANGKAT']['PLAN'][$hourSlot][$foundIdx] = $timeStr;
                        } else {
                            $times['BERANGKAT']['PLAN'][$hourSlot][] = $timeStr;
                        }
                    }
                }
                
                // Planning DATANG
                if ($spk->jam_datang_plan) {
                    try {
                        $fmtTime = Carbon::parse($spk->jam_datang_plan)->format('H:i');
                    } catch (\Exception $e) {
                         $fmtTime = substr($spk->jam_datang_plan, 0, 5);
                    }
                    
                    $hourPart = explode(':', $fmtTime)[0];
                    $hourSlot = str_pad($hourPart, 2, '0', STR_PAD_LEFT) . '.00';
                    
                    if (in_array($hourSlot, $timeSlots)) {
                        $cycleNum = $spk->cycle_number ?? 1;
                        $cycleStr = 'C' . $cycleNum;
                        $timeStr = $fmtTime . ($cycleStr ? ' ' . $cycleStr : '');
                        
                        if (!isset($times['DATANG']['PLAN'][$hourSlot])) $times['DATANG']['PLAN'][$hourSlot] = [];
                        
                        // Deduplicate by Cycle
                        $foundIdx = -1;
                        foreach ($times['DATANG']['PLAN'][$hourSlot] as $idx => $existing) {
                            $exTime = is_array($existing) ? $existing['time'] : $existing;
                            if (strpos($exTime, $cycleStr) !== false) {
                                $foundIdx = $idx;
                                break;
                            }
                        }
                        
                        if ($foundIdx >= 0) {
                            $times['DATANG']['PLAN'][$hourSlot][$foundIdx] = $timeStr;
                        } else {
                            $times['DATANG']['PLAN'][$hourSlot][] = $timeStr;
                        }
                    }
                }
            }

            // Determine main truck driver & customer
            $driverName = $latestDelivery->driver->nama ?? ($allMySpks->last()->driver->nama ?? '-');
            
            $customerName = '-';
            if ($latestDelivery && $latestDelivery->destination && $latestDelivery->destination != '-') {
                $customerName = $latestDelivery->destination;
            } elseif ($allMySpks->isNotEmpty()) {
                $latestSpk = $allMySpks->sortByDesc('id')->first();
                $customerName = ($latestSpk->customer->nama_perusahaan ?? '') . ($latestSpk->plantgate ? " ({$latestSpk->plantgate->nama_plantgate})" : "");
            }

            // Create cycle rows (C1 to C4 max, mapping actual cycles to available slots)
            $customers = [];
            
            // Collect all actual cycles that exist for this truck
            $actualCycles = [];
            foreach ($allMySpks as $spk) {
                $cycleNum = $spk->cycle_number ?? $spk->cycle ?? null;
                if ($cycleNum) {
                    $sj = $spk->no_surat_jalan ?? '-';
                    $linkedDelivery = $sj !== '-' ? $truckDeliveries->where('no_surat_jalan', $sj)->first() : null;
                    
                    $actualCycles[] = [
                        'actual_cycle' => $cycleNum,
                        'spk' => $spk,
                        'delivery' => $linkedDelivery,
                        'sj' => $sj
                    ];
                }
            }
            
            // Also check deliveries that might not have SPK yet (manual entries or older records)
            foreach ($truckDeliveries as $delivery) {
                $matchedSpk = $allMySpks->where('no_surat_jalan', $delivery->no_surat_jalan)->first();
                if (!$matchedSpk) {
                    $actualCycles[] = [
                        'actual_cycle' => count($actualCycles) + 1, 
                        'spk' => null,
                        'delivery' => $delivery,
                        'sj' => $delivery->no_surat_jalan ?? '-'
                    ];
                }
            }
            
            // Sort by actual cycle number
            usort($actualCycles, function($a, $b) {
                return $a['actual_cycle'] <=> $b['actual_cycle'];
            });
            
            // Map actual cycles to display slots (C1-C4)
            // Take first 4 cycles if more than 4 exist
            $cyclesToDisplay = array_slice($actualCycles, 0, 4);
            
            // Create 4 rows max, mapping actual data to slots
            for ($slot = 0; $slot < 4; $slot++) {
                $displayCycle = $slot + 1; // C1, C2, C3, C4
                
                if (isset($cyclesToDisplay[$slot])) {
                    $cycleData = $cyclesToDisplay[$slot];
                    $spk = $cycleData['spk'];
                    $linkedDelivery = $cycleData['delivery'];
                    $sj = $cycleData['sj'];
                    $actualCycleNum = $cycleData['actual_cycle'];
                    
                    // Safe driver access
                    $cycleDriver = '-';
                    if ($linkedDelivery && $linkedDelivery->driver) {
                        $cycleDriver = $linkedDelivery->driver->nama;
                    } elseif ($spk && $spk->driver) {
                        $cycleDriver = $spk->driver->nama;
                    }
                    
                    // Safe customer access
                    $cycleCustomer = '-';
                    if ($linkedDelivery && $linkedDelivery->destination && $linkedDelivery->destination !== '-') {
                        $cycleCustomer = $linkedDelivery->destination;
                    } elseif ($spk) {
                        $custName = '';
                        if ($spk->customer) {
                            $custName = $spk->customer->nama_perusahaan ?? '';
                        }
                        if ($spk->plantgate) {
                            $custName .= " ({$spk->plantgate->nama_plantgate})";
                        }
                        if ($custName) {
                            $cycleCustomer = $custName;
                        }
                    }
                    
                    $cycleStatus = 'OPEN';
                    if ($linkedDelivery && $linkedDelivery->status) {
                        $cycleStatus = $linkedDelivery->status;
                    } elseif ($spk) {
                        $cycleStatus = 'PENDING';
                    }
                    
                    $customers[] = [
                        'customer_id' => $linkedDelivery->id ?? 0,
                        'customer_name' => $cycleCustomer,
                        'driver_name' => $cycleDriver,
                        'surat_jalan' => $sj,
                        'status' => $cycleStatus,
                        'actual_cycle' => $actualCycleNum,
                        'times' => $times
                    ];
                } else {
                    // Empty slot, but let's check if there's a Plan from TSpk we can show
                    // (This allows DATANG PLAN to show up even if ACTUAL/Delivery hasn't started)
                    $customers[] = [
                        'customer_id' => 0,
                        'customer_name' => '-',
                        'driver_name' => '-',
                        'surat_jalan' => '-',
                        'status' => 'OPEN',
                        'actual_cycle' => null,
                        'times' => $times
                    ];
                }
            }
            
            $trucks[] = [
                'id' => $kendaraan->id,
                'nopol' => $kendaraan->nopol_kendaraan,
                'driver' => $driverName,
                'status' => $latestDelivery ? $latestDelivery->status : 'OPEN',
                'customers' => $customers
            ];
        }
        
        $vehicles = $kendaraans; // Using $kendaraans which is already fetched
        return view('shipping.controltruck', compact('trucks', 'tanggal', 'vehicles'));
    }
    
    /**
     * API: Update waktu plan/actual untuk truck
     */
    public function updateTime(Request $request)
    {
        if (!userCan('shipping.controltruck.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'truck_id' => 'required|integer',
            'customer_id' => 'nullable|integer',
            'activity' => 'required|in:DATANG,BERANGKAT',
            'plan_actual' => 'required|in:plan,actual',
            'time_slot' => 'required|string',
            'waktu' => 'nullable|string',
            'tanggal' => 'required|date',
        ]);
        
        try {
            $tanggal = Carbon::parse($request->tanggal);
            
            // Parse time slot (format: "07.00") ke jam integer (7)
            $timeSlot = str_replace('.00', '', $request->time_slot);
            $jam = (int)$timeSlot;
            
            // Cari atau buat delivery header untuk truck dan customer
            $deliveryHeader = TShippingDeliveryHeader::firstOrCreate(
                [
                    'kendaraan_id' => $request->truck_id,
                    'tanggal_berangkat' => $tanggal,
                    'destination' => $request->customer_id ? 'Customer ' . $request->customer_id : 'Unknown'
                ],
                [
                    'periode' => $tanggal->format('Y-m'),
                    'status' => 'OPEN'
                ]
            );
            
            // Parse waktu and cycle: "09:40 C1"
            $waktuValue = $request->waktu;
            $timePart = '';
            $cyclePart = '';
            
            if ($waktuValue) {
                $parts = explode(' ', trim($waktuValue));
                $timePart = $parts[0];
                $cyclePart = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
            }
            
            // Parse timePart to Carbon
            $waktuUpdate = null;
            if ($timePart) {
                try {
                    $waktuUpdate = Carbon::parse($tanggal->format('Y-m-d') . ' ' . $timePart);
                } catch (\Exception $e) { }
            }
            
            // Simpan activity dan cycle di keterangan
            $keterangan = $request->activity . '|' . $cyclePart;
            
            // Update atau create delivery detail
            $detail = TShippingDeliveryDetail::updateOrCreate(
                [
                    'delivery_header_id' => $deliveryHeader->id,
                    'tanggal' => $tanggal,
                    'jam' => $jam,
                ],
                [
                    'status' => $request->plan_actual === 'actual' ? 'IN_TRANSIT' : 'OPEN',
                    'waktu_update' => $waktuUpdate,
                    'keterangan' => $keterangan,
                ]
            );
            
            return response()->json([
                'success' => true, 
                'message' => 'Waktu berhasil disimpan',
                'data' => [
                    'waktu' => $waktuUpdate ? $waktuUpdate->format('H:i') : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Update status per jam
     */
    public function updateStatus(Request $request)
    {
        if (!userCan('shipping.controltruck.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'delivery_header_id' => 'required|integer',
            'tanggal' => 'required|date',
            'jam' => 'required|integer|min:0|max:23',
            'status' => 'required|in:OPEN,IN_TRANSIT,ARRIVED,DELIVERED,PENDING,CANCELLED',
            'lokasi_saat_ini' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::transaction(function () use ($request) {
                $tanggal = Carbon::parse($request->tanggal);
                
                // Find or create detail
                $detail = TShippingDeliveryDetail::updateOrCreate(
                    [
                        'delivery_header_id' => $request->delivery_header_id,
                        'tanggal' => $tanggal,
                        'jam' => $request->jam,
                    ],
                    [
                        'status' => $request->status,
                        'lokasi_saat_ini' => $request->lokasi_saat_ini,
                        'waktu_update' => now(),
                    ]
                );
                
                // Update header totals if needed
                $header = TShippingDeliveryHeader::find($request->delivery_header_id);
                if ($header) {
                    $deliveredCount = TShippingDeliveryDetail::where('delivery_header_id', $header->id)
                        ->where('status', 'DELIVERED')
                        ->count();
                    $header->total_delivered = $deliveredCount;
                    $header->save();
                }
            });
            
            return response()->json(['success' => true, 'message' => 'Status berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Search Driver (Manpower)
     */
    public function searchDriver(Request $request)
    {
        $query = $request->input('query');
        
        $drivers = \App\Models\MManpower::where('nama', 'like', "%{$query}%")
            ->where(function($q) {
                $q->where('bagian', 'like', '%DRIVER%')
                  ->orWhere('bagian', 'like', '%SOPIR%')
                  ->orWhere('bagian', 'like', '%LOGISTIK%')
                  ->orWhere('bagian', 'like', '%DELIVERY%');
            })
            ->limit(10)
            ->get(['id', 'nama', 'bagian']);
            
        return response()->json($drivers);
    }

    /**
     * API: Search Customer (Note: User asked to search PlantGate)
     */
    public function searchCustomer(Request $request)
    {
        $query = $request->input('query');
        
        $queryBuilder = \App\Models\MPlantGate::query();
        
        if ($query) {
            $queryBuilder->where('nama_plantgate', 'like', "%{$query}%");
        }
        
        // Eager load customer info if needed, but for now just name
        $customers = $queryBuilder->orderBy('nama_plantgate')
            ->limit(10)
            ->get(['id', 'nama_plantgate as nama']); // Return as 'nama' to match JS expectation
            
        return response()->json($customers);
    }

    /**
     * API: Update Truck Info (Driver or Customer)
     */
    public function updateTruckInfo(Request $request)
    {
        if (!userCan('shipping.controltruck.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'truck_id' => 'required|integer',
            'tanggal' => 'required|date',
            'field' => 'required|string', // 'driver' or 'customer'
            'value' => 'nullable|string', // driver_id or customer_name
            'customer_id' => 'nullable|integer', // Optional now, since we rely on slot_index logic more
            'new_driver_id' => 'nullable|integer', // If updating driver
            'slot_index' => 'nullable|integer|min:0|max:3', // 0=DatangPlan, 1=DatangActual, 2=BerangkatPlan, 3=BerangkatActual
        ]);

        try {
            $tanggal = Carbon::parse($request->tanggal);
            
            // Logic Update Driver
            if ($request->field === 'driver') {
                // Update ALL delivery headers for this truck on this date
                // Because one truck usually has one driver for the day (or shift)
                // Note: If multiple shifts have different drivers, logic might need adjustment.
                // For now, assuming 1 truck 1 driver per day unless specified otherwise.
                
                $headers = TShippingDeliveryHeader::where('kendaraan_id', $request->truck_id)
                    ->whereDate('tanggal_berangkat', $tanggal)
                    ->get();
                
                if ($headers->isEmpty()) {
                    // Create basic header if no delivery exists
                    $header = TShippingDeliveryHeader::create([
                        'kendaraan_id' => $request->truck_id,
                        'tanggal_berangkat' => $tanggal,
                        'driver_id' => $request->new_driver_id,
                        'periode' => $tanggal->format('Y-m'),
                        'destination' => '-', // Placeholder
                        'status' => 'OPEN'
                    ]);
                } else {
                    foreach ($headers as $header) {
                        $header->driver_id = $request->new_driver_id;
                        $header->save();
                    }
                }
                
                // Get updated driver name
                $driver = \App\Models\MManpower::find($request->new_driver_id);
                return response()->json([
                    'success' => true, 
                    'message' => 'Driver updated',
                    'text' => $driver ? $driver->nama : '-'
                ]);
            }
            
            // Logic Update Customer (Slot based)
            if ($request->field === 'customer') {
                $slotIndex = $request->slot_index ?? 0;
                
                // Get existing headers ordered by ID (consistent with monitoring view)
                $headers = TShippingDeliveryHeader::where('kendaraan_id', $request->truck_id)
                    ->whereDate('tanggal_berangkat', $tanggal)
                    ->orderBy('id')
                    ->get();
                
                $headersCount = $headers->count();
                
                if ($slotIndex < $headersCount) {
                    // Update existing
                    $header = $headers[$slotIndex];
                    $header->destination = $request->value;
                    $header->save();
                } else {
                    // Create needed headers to reach this slot
                    // e.g. Count=1 (Index 0). Target=2.
                    // Loop i from 1 to 2.
                    // i=1 (Filler), i=2 (Target).
                    
                    for ($i = $headersCount; $i <= $slotIndex; $i++) {
                        $isTarget = ($i === $slotIndex);
                        $destination = $isTarget ? $request->value : '-';
                        
                        // We need a driver ID if we create new headers.
                        // Inherit from existing header if possible, or leave null.
                        $driverId = null;
                        if ($headersCount > 0) {
                            $driverId = $headers[0]->driver_id;
                        }
                        
                        TShippingDeliveryHeader::create([
                            'kendaraan_id' => $request->truck_id,
                            'tanggal_berangkat' => $tanggal,
                            'destination' => $destination,
                            'driver_id' => $driverId, 
                            'periode' => $tanggal->format('Y-m'),
                            'status' => 'OPEN'
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Customer updated',
                    'text' => $request->value
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Invalid field']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Assign Truck and Driver to an SPK
     */
    public function assignSpk(Request $request)
    {
        if (!userCan('shipping.controltruck.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'tanggal' => 'required|date',
            'time_slot' => 'required|string',
            'cycle' => 'required|string',
            'nomor_plat' => 'required|string',
            'driver_id' => 'required|integer',
            'no_surat_jalan' => 'required|string',
        ]);

        try {
            $tanggal = Carbon::parse($request->tanggal);
            
            // Clean cycle from "C1" to "1"
            $cycleNum = preg_replace('/[^0-9]/', '', $request->cycle);
            
            // Find the pending SPK
            // Jam might be "09.00" in slot but "09:00" in DB
            $slotTime = str_replace('.', ':', $request->time_slot);
            
            $spk = TSpk::whereDate('tanggal', $tanggal)
                ->where('jam_berangkat_plan', 'like', $slotTime . '%')
                ->where('cycle', $cycleNum)
                ->whereNull('nomor_plat')
                ->first();
            
            if (!$spk) {
                return response()->json(['success' => false, 'message' => 'SPK tidak ditemukan atau sudah di-assign']);
            }

            $spk->nomor_plat = $request->nomor_plat;
            $spk->driver_id = $request->driver_id;
            $spk->no_surat_jalan = $request->no_surat_jalan;
            $spk->save();

            return response()->json(['success' => true, 'message' => 'SPK berhasil di-assign']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
