<?php

namespace App\Http\Controllers\FinishGood;

use App\Http\Controllers\Controller;
use App\Models\SMPart;
use App\Models\TFinishGoodIn;
use App\Models\TFinishGoodOut;
use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\MKendaraan;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinishGoodOutController extends Controller
{
    /**
     * Menampilkan daftar SPK untuk Finish Good Out
     */
    public function index(Request $request)
    {
        if (!userCan('finishgood.out.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = TSpk::with(['customer', 'plantgate', 'details.part', 'finishGoodOuts', 'childSpk'])
            ->withCount(['details', 'finishGoodOuts'])
            ->withSum('details as total_target', 'jadwal_delivery_pcs')
            ->withSum('details as total_original_target', 'original_jadwal_delivery_pcs') // Untuk handle tampilan split
            ->withSum('finishGoodOuts as total_scanned', 'qty');

        // Apply filters
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_spk', 'like', "%{$search}%")
                  ->orWhere('model_part', 'like', "%{$search}%")
                  ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('nama_perusahaan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Apply Sorting
        if ($request->sort_by && in_array($request->sort_by, ['nomor_spk', 'tanggal', 'jam_berangkat_plan', 'cycle_number', 'customer_id', 'model_part', 'no_surat_jalan'])) {
            $query->orderBy($request->sort_by, $request->sort_order ?? 'asc');
        } else {
            $query->orderBy('tanggal', 'desc')->orderBy('nomor_spk', 'desc');
        }
            
        $spks = $query->paginate($request->per_page ?? 15);

        return view('finishgood.out.out', compact('spks'));
    }

    /**
     * Export SPK data to CSV
     */
    public function export(Request $request)
    {
        if (!userCan('finishgood.out.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = TSpk::with(['customer', 'plantgate', 'details.part', 'finishGoodOuts'])
            ->withCount(['details', 'finishGoodOuts'])
            ->withSum('details as total_target', 'jadwal_delivery_pcs')
            ->withSum('finishGoodOuts as total_scanned', 'qty');

        // Apply filters
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_spk', 'like', "%{$search}%")
                  ->orWhere('model_part', 'like', "%{$search}%")
                  ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('nama_perusahaan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Apply Sorting
        if ($request->sort_by && in_array($request->sort_by, ['nomor_spk', 'tanggal', 'jam_berangkat_plan', 'cycle_number', 'customer_id', 'model_part', 'no_surat_jalan'])) {
            $query->orderBy($request->sort_by, $request->sort_order ?? 'asc');
        } else {
            $query->orderBy('tanggal', 'desc')->orderBy('nomor_spk', 'desc');
        }

        $spks = $query->get();
        $filename = 'finish_good_out_spk_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($spks) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'No', 'Nomor SPK', 'Cycle', 'No Surat Jalan', 'Tanggal Deadline', 
                'Customer', 'Plant Gate', 'Model Part', 'Target Pcs', 'Scanned Pcs', 'Status'
            ]);

            // Data
            foreach ($spks as $index => $spk) {
                $target = $spk->total_target ?? 0;
                $scanned = $spk->total_scanned ?? 0;
                $status = ($target > 0 && $scanned >= $target) ? 'Closed' : (($scanned > 0) ? 'Progress' : 'Open');

                fputcsv($file, [
                    $index + 1,
                    $spk->nomor_spk,
                    'C' . ($spk->cycle_number ?? 1),
                    $spk->no_surat_jalan ?? 'Belum dikirim',
                    optional($spk->tanggal)->format('Y-m-d'),
                    $spk->customer->nama_perusahaan ?? '-',
                    $spk->plantgate->nama_plantgate ?? '-',
                    $spk->model_part,
                    $target,
                    $scanned,
                    $status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menampilkan form create Finish Good Out dengan SPK yang sudah dipilih
     */
    public function create(TSpk $spk)
    {
        if (!userCan('finishgood.out.create')) {
            abort(403, 'Unauthorized action.');
        }
        // Load SPK dengan relasi
        $spk->load(['customer', 'plantgate', 'details.part']);

        return view('finishgood.out.create', compact('spk'));
    }

    /**
     * Menyimpan data Finish Good Out
     */
    public function store(Request $request): JsonResponse
    {
        if (!userCan('finishgood.out.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'lot_number' => 'required|string|max:100',
            'spk_id' => 'required|exists:T_SPK,id',
            'cycle' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        try {
            $finishGoodOut = null;
            DB::transaction(function () use ($validated, &$finishGoodOut) {
                // Normalisasi lot number
                $lotNumberInput = trim($validated['lot_number']);
                $lotNumber = $lotNumberInput;
                $barcodePartNomor = null;
                
                // Parsing jika format Barcode: Part|Customer|Qty|Lot
                if (str_contains($lotNumberInput, '|')) {
                   $parts = explode('|', $lotNumberInput);
                   if (count($parts) >= 4) {
                       $barcodePartNomor = trim($parts[0]);
                       $lotNumber = trim($parts[3]);
                   }
                }

                $lotNumberNormalized = preg_replace('/\s+/', ' ', $lotNumber);
                
                // 1. Validasi SPK 
                $spk = TSpk::with(['details.part', 'finishGoodOuts'])->find($validated['spk_id']);
                if (!$spk) throw new \Exception('SPK tidak ditemukan');
                
                // Protection: Jika sudah disubmit (No Surat Jalan ada), tidak bisa scan lagi
                if ($spk->no_surat_jalan) {
                    throw new \Exception("SPK ini sudah dikirim (No. SJ: {$spk->no_surat_jalan}).");
                }

                // Protection: Jika barcode punya info part, cek awal apakah part ini ada di SPK?
                if ($barcodePartNomor) {
                    $foundInSpk = $spk->details->first(function($detail) use ($barcodePartNomor) {
                        return optional($detail->part)->nomor_part === $barcodePartNomor;
                    });
                    
                    if (!$foundInSpk) {
                        throw new \Exception("Part {$barcodePartNomor} tidak ada dalam list SPK ini.");
                    }
                }

                // Cari FG In
                $finishGoodIn = $this->findFinishGoodIn($lotNumber, $lotNumberNormalized, $barcodePartNomor);
                
                // Aggressive Fallback: Ignore Lot Number if not found, as requested by user
                // "yg penting part number nya aja"
                if (!$finishGoodIn && $barcodePartNomor) {
                    $fallbackQuery = TFinishGoodIn::with('part')
                        ->whereHas('part', function($q) use ($barcodePartNomor) {
                            $q->where('nomor_part', $barcodePartNomor);
                        })
                        ->whereDoesntHave('finishGoodOuts') // Harus yang belum di-scan out
                        ->orderBy('waktu_scan', 'asc'); // FIFO strategy

                    // Coba cari yang Quantity-nya cocok dulu (jika ada info qty di barcode)
                    // Format Barcode Inoac: Part|Customer|Qty|Lot
                    if (isset($parts) && count($parts) >= 3) {
                         $barcodeQty = (int) trim($parts[2]);
                         if ($barcodeQty > 0) {
                              $exactQtyMatch = clone $fallbackQuery;
                              $finishGoodIn = $exactQtyMatch->where('qty', $barcodeQty)->first();
                         }
                    }

                    // Jika masih belum ketemu (atau qty beda/tidak terbaca), ambil stok FIFO apapun
                    if (!$finishGoodIn) {
                        $finishGoodIn = $fallbackQuery->first();
                    }
                }

                if (!$finishGoodIn) {
                     throw new \Exception('Label tidak ditemukan di Finish Good In (Belum di-scan masuk).');
                }

                // 2. Validasi Part Detail (Link by ID or Name)
                $partDetail = $spk->details->where('part_id', $finishGoodIn->part_id)->first();
                
                // Fallback: Jika ID tidak cocok (misal ada duplikat part master), cek via Nomor Part string
                if (!$partDetail) {
                    $fgPartNomor = $finishGoodIn->part->nomor_part ?? null;
                    if ($fgPartNomor) {
                        $partDetail = $spk->details->first(function($detail) use ($fgPartNomor) {
                            return optional($detail->part)->nomor_part === $fgPartNomor;
                        });
                    }
                }

                if (!$partDetail) {
                     $partNomor = $finishGoodIn->part->nomor_part ?? 'Unknown';
                     throw new \Exception("Part {$partNomor} tidak ada dalam list SPK ini.");
                }
                
                // Gunakan Part ID dari SPK Detail agar hitungan progress sinkron
                $partId = $partDetail->part_id;

                // 3. Validasi Quantity & Balance
                // Balance Saat ini = Target SPK - Total Scanned (All Cycles)
                $currentTotalScanned = $spk->finishGoodOuts->where('part_id', $partId)->sum('qty');
                $targetQty = $partDetail->jadwal_delivery_pcs;
                $scanQty = $finishGoodIn->qty; // Qty dari Label

                // Cek Overflow
                if (($currentTotalScanned + $scanQty) > $targetQty) {
                    $remaining = $targetQty - $currentTotalScanned;
                    throw new \Exception("Over Quantity! Sisa balance: {$remaining} PCS. Scan ini: {$scanQty} PCS.");
                }

                // 4. Save
                $waktuScanOut = now();

                $finishGoodOut = TFinishGoodOut::create([
                    'finish_good_in_id' => $finishGoodIn->id,
                    'lot_number' => $finishGoodIn->lot_number,
                    'spk_id' => $validated['spk_id'],
                    'part_id' => $partId,
                    'cycle' => $validated['cycle'],
                    'qty' => $scanQty,
                    'waktu_scan_out' => $waktuScanOut,
                    'catatan' => $validated['catatan'] ?? null,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Scan Berhasil',
                'data' => [
                    'id' => $finishGoodOut->id,
                    'qty' => $finishGoodOut->qty,
                    'part_number' => $finishGoodOut->part->nomor_part ?? '-',
                    'cycle' => $finishGoodOut->cycle,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Scan Out Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Helper untuk find FG IN (Refactoring logic sebelumnya biar rapi)
    private function findFinishGoodIn($lotNumber, $lotNumberNormalized, $barcodePartNomor = null) 
    {
         // Logic pencarian bertingkat (Exact -> Normalized -> NoSpace)
         // REMOVED whereDoesntHave('finishGoodOuts') to allow re-scanning
         $query = TFinishGoodIn::with('part')->where(function($q) use ($lotNumber, $lotNumberNormalized) {
                      $q->where('lot_number', $lotNumber)
                        ->orWhere('lot_number', $lotNumberNormalized);
                  });
         
         if ($barcodePartNomor) {
             $query->whereHas('part', function($q) use ($barcodePartNomor) {
                 $q->where('nomor_part', $barcodePartNomor);
             });
         }
         
         $res = $query->first();
         if ($res) return $res;

         // Fallback: No Space
         $lotNoSpace = preg_replace('/\s+/', '', $lotNumber);

         // Helper to find logic without getting ALL records if possible, 
         // but strict fuzzy match requires iteration or database function.
         // Simpler: Try standard fuzzy if strict fails
         
         $queryFallback = TFinishGoodIn::with('part');
         if ($barcodePartNomor) {
             $queryFallback->whereHas('part', function($q) use ($barcodePartNomor) {
                 $q->where('nomor_part', $barcodePartNomor);
             });
         }
         
         $candidates = $queryFallback->get(); 
         foreach($candidates as $c) {
             if (preg_replace('/\s+/', '', $c->lot_number) === $lotNoSpace) {
                 return $c;
             }
         }
         return null;
    }

    /**
     * Menampilkan detail Finish Good Out
     */
    public function detail(TFinishGoodOut $finishGoodOut)
    {
        if (!userCan('finishgood.out.view')) {
            abort(403, 'Unauthorized action.');
        }
        $finishGoodOut->load([
            'finishGoodIn.assyOut.assyIn',
            'finishGoodIn.part',
            'spk.customer',
            'spk.plantgate',
            'spk.details.part',
            'part',
        ]);

        return view('finishgood.out.detail', compact('finishGoodOut'));
    }

    /**
     * Menampilkan form konfirmasi delete
     */
    public function delete(TFinishGoodOut $finishGoodOut)
    {
        if (!userCan('finishgood.out.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $finishGoodOut->load(['finishGoodIn', 'spk.customer', 'part']);
        return view('finishgood.out.delete', compact('finishGoodOut'));
    }

    /**
     * Hapus data Finish Good Out
     */
    public function destroy(TFinishGoodOut $finishGoodOut): JsonResponse
    {
        if (!userCan('finishgood.out.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            $finishGoodOut->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FinishGoodOutController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari finish good in berdasarkan lot number
     */
    public function getFinishGoodInByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            // Normalisasi lot number
            $lotNumberNormalized = trim($lotNumber);
            $lotNumberNormalized = preg_replace('/\s+/', ' ', $lotNumberNormalized);
            
            Log::info('Mencari Finish Good In dengan lot number:', [
                'original' => $lotNumber,
                'normalized' => $lotNumberNormalized
            ]);

            // Cari finish good in (Allow duplicates, don't filter by whereDoesntHave finishGoodOuts)
            // Normal Logic: strict or fuzzy
             $finishGoodIn = TFinishGoodIn::with(['part', 'assyOut.assyIn'])
                ->where(function($query) use ($lotNumber, $lotNumberNormalized) {
                    $query->where('lot_number', $lotNumberNormalized)
                        ->orWhere('lot_number', $lotNumber);
                })
                ->first();

            // Jika tidak ketemu exact, coba tanpa spasi
            if (!$finishGoodIn) {
                $lotNumberNoSpace = preg_replace('/\s+/', '', $lotNumberNormalized);
                $finishGoodIns = TFinishGoodIn::with(['part', 'assyOut.assyIn'])->get();
                
                foreach ($finishGoodIns as $fgin) {
                    $dbLotNumberNoSpace = preg_replace('/\s+/', '', $fgin->lot_number ?? '');
                    if (strcasecmp($lotNumberNoSpace, $dbLotNumberNoSpace) === 0) {
                        $finishGoodIn = $fgin;
                        break;
                    }
                }
            }

            // checkOut check was removed to allow re-scan

            if (!$finishGoodIn) {
                 return response()->json([
                        'success' => false,
                        'message' => 'Label dengan lot number tersebut tidak ditemukan di finish good in',
                    ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $finishGoodIn->id,
                    'lot_number' => $finishGoodIn->lot_number,
                    'part_id' => $finishGoodIn->part_id,
                    'part' => $finishGoodIn->part ? [
                        'nomor_part' => $finishGoodIn->part->nomor_part,
                        'nama_part' => $finishGoodIn->part->nama_part,
                    ] : null,
                    'waktu_scan' => $finishGoodIn->waktu_scan ? $finishGoodIn->waktu_scan->format('Y-m-d H:i:s') : null,
                    'manpower' => $finishGoodIn->manpower,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getFinishGoodInByLotNumber: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan parts dari SPK
     */
    /**
     * API untuk mendapatkan parts dari SPK
     */
    public function getPartsBySpk(int $spkId): JsonResponse
    {
        try {
            $spk = TSpk::with(['details.part', 'finishGoodOuts'])->find($spkId);
            
            if (!$spk) {
                return response()->json([
                    'success' => false,
                    'message' => 'SPK tidak ditemukan',
                ], 404);
            }

            $parts = $spk->details->map(function($detail) use ($spk) {
                $scannedQty = $spk->finishGoodOuts
                    ->where('part_id', $detail->part_id)
                    ->sum('qty');
                    
                return [
                    'part_id' => $detail->part_id,
                    'nomor_part' => $detail->part ? $detail->part->nomor_part : '-',
                    'nama_part' => $detail->part ? $detail->part->nama_part : '-',
                    'qty_plan' => $detail->jadwal_delivery_pcs,
                    'qty_scanned' => $scannedQty,
                    'qty_balance' => $detail->jadwal_delivery_pcs - $scannedQty,
                    'qty_packing' => $detail->qty_packing ?? 1 // Asumsi 1 jika null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $parts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPartsBySpk: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan halaman scanning khusus untuk Specific SPK
     */
    public function scan(TSpk $spk)
    {
        if (!userCan('finishgood.out.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Load relationships
        $spk->load(['customer', 'plantgate', 'details.part', 'finishGoodOuts.part']);

        // Calculate progress per part
        $progress = [];
        $totalScanned = 0;
        $totalTarget = 0;
        
        // Tentukan Cycle saat ini (Use SPK's cycle number)
        $currentCycle = $spk->cycle_number ?? 1;

        // Get Accumulated Scanned from Ancestor SPKs to Calculate Global Balance
        $ancestorSpkIds = [];
        $currentParent = $spk->parentSpk; 
        while ($currentParent) {
            $ancestorSpkIds[] = $currentParent->id;
            $currentParent = TSpk::find($currentParent->parent_spk_id);
        }

        foreach ($spk->details as $detail) {
             // Validasi $detail->part null check
            if (!$detail->part) continue;

            $partId = $detail->part_id;
            $currentScanned = $spk->finishGoodOuts->where('part_id', $partId)->sum('qty');
            $target = $detail->jadwal_delivery_pcs;
            
            // Calculate Previous Scanned
            $previousScanned = 0;
            if (!empty($ancestorSpkIds)) {
                $previousScanned = TFinishGoodOut::whereIn('spk_id', $ancestorSpkIds)
                    ->where('part_id', $partId)
                    ->sum('qty');
            }

            $totalScannedGlobal = $previousScanned + $currentScanned;
            // Global Balance = Original Target - (Previous + Current)
            $globalBalance = max(0, $target - $totalScannedGlobal);

            $progress[$partId] = [
                'part_nomor' => $detail->part->nomor_part,
                'part_nama' => $detail->part->nama_part,
                'target' => $target,
                'scanned' => $currentScanned,
                'previous_scanned' => $previousScanned,
                'balance' => $globalBalance, // Use Global Balance
                'qty_packing' => $detail->qty_packing ?? 1,
            ];
            
            $totalScanned += $currentScanned;
            $totalTarget += $target;
        }

        // Calculate Header Total Balance Global
        $totalBalanceGlobal = collect($progress)->sum('balance');

        $isNewCycle = !$spk->finishGoodOuts->where('cycle', $currentCycle)->isNotEmpty();
        $kendaraans = MKendaraan::orderBy('nopol_kendaraan')->get();

        return view('finishgood.out.scan', compact('spk', 'progress', 'currentCycle', 'totalScanned', 'totalTarget', 'isNewCycle', 'kendaraans', 'totalBalanceGlobal'));
    }
    
    /**
     * Handle Submit Shipment (Close & Split if needed) - Option B
     */
    public function closeCycle(Request $request, TSpk $spk): JsonResponse
    {
        if (!userCan('finishgood.out.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        $request->validate([
            'no_surat_jalan' => 'required|string|max:100',
            'split_reason' => 'nullable|string|max:255',
            'next_deadline_time' => 'nullable|date_format:H:i',
            'split_nomor_plat' => 'nullable|string|max:50',
        ]);

        try {
            $hasBalance = false;
            $newSpk = null;

            DB::transaction(function () use ($request, $spk, &$hasBalance, &$newSpk) {
                $spk->load(['details', 'finishGoodOuts']);

                // 1. Update scanning data with the SJ
                TFinishGoodOut::where('spk_id', $spk->id)
                    ->update(['no_surat_jalan' => $request->no_surat_jalan]);
                
                // 1.5. Create Control Truck entry
                $this->createControlTruckEntry($spk, $request->no_surat_jalan);

                // 2. Process Splitting Logic
                $splitDetails = [];
                // Logic Ancestor for closeCycle to ensure accurate Global Balance check
                $ancestorSpkIds = [];
                $currentParent = $spk->parentSpk; 
                while ($currentParent) {
                    $ancestorSpkIds[] = $currentParent->id;
                    $currentParent = TSpk::find($currentParent->parent_spk_id);
                }

                foreach ($spk->details as $detail) {
                    $currentScanned = $spk->finishGoodOuts->where('part_id', $detail->part_id)->sum('qty');
                    $originalTarget = $detail->jadwal_delivery_pcs;
                    
                    // Calculate Previous
                    $previousScanned = 0;
                    if (!empty($ancestorSpkIds)) {
                        $previousScanned = TFinishGoodOut::whereIn('spk_id', $ancestorSpkIds)
                            ->where('part_id', $detail->part_id)
                            ->sum('qty');
                    }
                    $totalScannedGlobal = $previousScanned + $currentScanned;

                    if ($totalScannedGlobal < $originalTarget) {
                        $hasBalance = true;
                        
                        // Prepare data for NEW SPK
                    $splitDetails[] = [
                        'part_id' => $detail->part_id,
                        'qty_packing_box' => $detail->qty_packing_box,
                        'jadwal_delivery_pcs' => $originalTarget, // Use Full Target
                        'jumlah_pulling_box' => round($originalTarget / ($detail->qty_packing_box ?: 1)),
                        'catatan' => $detail->catatan
                    ];

                    // REMOVED: Logic to update original SPK target. 
                    // User wants existing SPK to keep original target (e.g. 90) even if only 30 shipped.
                    // The "Shortage" is handled by creating a new SPK, but the old SPK keeps record of full PO target.
                } // End if
            } // End foreach

                // 3. Create New SPK logic
                $currentCycle = $spk->cycle_number ?? 1;
                
                // Load customer to check type (Moved outside for scope access)
                $spk->load('customer');
                $isNonInoac = $spk->customer && $spk->customer->customer_type === 'non-inoac';

                // Logic Constraint: Unlimited Cycles (Instruction ID: 1477)
                if ($hasBalance) {                    
                // Generate Nomor SPK Standard: SPK/YYYY/MM/XXXX
                $year = date('Y');
                $month = date('m');
                $prefix = "SPK/{$year}/{$month}/";
                
                $lastSpk = TSpk::where('nomor_spk', 'like', "{$prefix}%")
                    ->orderBy('nomor_spk', 'desc')
                    ->lockForUpdate() // Avoid collisions
                    ->first();
                
                $nextSequence = 1;
                if ($lastSpk) {
                    // Extract last 4 digits (assuming standard length)
                    // If format implies fixed length, verify suffix. 
                    // Let's use robust extraction after prefix
                    $suffix = substr($lastSpk->nomor_spk, strlen($prefix));
                    $nextSequence = (int)$suffix + 1;
                }
                $generatedSpkNumber = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

                    // Determine next deadline
                    $nextDeadline = $spk->jam_berangkat_plan; // Default: same as original
                    if ($isNonInoac && $request->filled('next_deadline_time')) {
                        $nextDeadline = $request->next_deadline_time;
                    }

                    // Build catatan with split reason
                    $catatan = "Split dari {$spk->nomor_spk}.";
                    if ($isNonInoac && $request->filled('split_reason')) {
                        $catatan .= " Alasan: {$request->split_reason}.";
                    }
                    if ($spk->catatan) {
                        $catatan .= " " . $spk->catatan;
                    }

                    $newSpk = TSpk::create([
                        'nomor_spk' => $generatedSpkNumber,
                        'parent_spk_id' => $spk->id,
                        'cycle' => $currentCycle + 1,
                        'cycle_number' => $currentCycle + 1,
                        'manpower_pembuat' => $spk->manpower_pembuat,
                        'customer_id' => $spk->customer_id,
                        'plantgate_id' => $spk->plantgate_id,
                        'tanggal' => $spk->tanggal,
                        'jam_berangkat_plan' => $nextDeadline,
                        'no_surat_jalan' => null,
                        'nomor_plat' => $request->split_nomor_plat ?: $spk->nomor_plat, // Use new truck or inherit
                        'model_part' => $spk->model_part,
                        'catatan' => $catatan,
                    ]);

                    foreach ($splitDetails as $splitDetail) {
                        TSpkDetail::create(array_merge($splitDetail, ['spk_id' => $newSpk->id]));
                    }
                } elseif ($hasBalance && $currentCycle >= 4) {
                    // Max Cycle Reached constraints
                    // We simply do not create a new SPK (Shortfall).
                    // Save reason to existing SPK notes
                    if ($isNonInoac && $request->filled('split_reason')) {
                         $note = " [Alasan Kurang: " . $request->split_reason . "]";
                         $spk->catatan = ($spk->catatan ?? '') . $note;
                         $spk->save();
                    }
                }

                // 4. Finalize original SPK
                $spk->update([
                    'no_surat_jalan' => $request->no_surat_jalan
                ]);
            });

            if ($hasBalance && isset($newSpk)) {
                return response()->json([
                    'success' => true,
                    'is_completed' => false,
                    'message' => "Pengiriman Berhasil. Sisa barang dipindahkan ke SPK baru: {$newSpk->nomor_spk}",
                    'new_spk_id' => $newSpk->id
                ]);
            }
            
            // Message variation for Max Cycle
            if ($hasBalance && !isset($newSpk)) {
                 return response()->json([
                    'success' => true,
                    'is_completed' => true, // Technically completed as in 'closed'
                    'message' => 'Pengiriman Selesai. Sisa balance TIDAK dibuatkan SPK baru (Max Cycle 4).',
                ]);
            }

            return response()->json([
                'success' => true,
                'is_completed' => true,
                'message' => 'Pengiriman Selesai. Seluruh target SPK telah terpenuhi.',
            ]);

        } catch (\Exception $e) {
            Log::error('Close Cycle Error: ' . $e->getMessage());
            // User-friendly error message
            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan saat menyimpan data. Detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print SPK & Delivery Checksheet Document
     */
    public function printDocument(TSpk $spk)
    {
        // Load SPK with all relationships
        $spk->load(['customer', 'plantgate', 'driver', 'details.part']);

        // Collect all SPK IDs in the lineage (current + all parents)
        $spkLineage = [$spk->id];
        $currentSpk = $spk;
        
        // Recursively get all parent SPKs
        while ($currentSpk->parent_spk_id) {
            $currentSpk = TSpk::find($currentSpk->parent_spk_id);
            if ($currentSpk) {
                array_unshift($spkLineage, $currentSpk->id); // Add to beginning
            } else {
                break;
            }
        }

        // Get all FinishGoodOut records for entire SPK lineage
        $allScans = TFinishGoodOut::whereIn('spk_id', $spkLineage)
            ->with('part')
            ->orderBy('spk_id')
            ->orderBy('cycle')
            ->orderBy('created_at')
            ->get();

        // Map SPK IDs to their Cycle Numbers from DB
        $spkCycles = [];
        $lineageSpks = TSpk::whereIn('id', $spkLineage)->get(['id', 'cycle_number']);
        foreach($lineageSpks as $lspk) {
             $spkCycles[$lspk->id] = $lspk->cycle_number ?? 1;
        }

        // Group scans by cycle and part_id
        $cycleData = [];
        $availableCycles = [];
        
        foreach ($allScans as $scan) {
            $partId = $scan->part_id;
            
            // Priority: Scan Cycle > SPK Cycle > Default 1
            $spkCycle = $spkCycles[$scan->spk_id] ?? 1;
            $dbCycle = $scan->cycle ?: 1;
            
            // Correction Logic: If Scan Cycle < SPK Cycle (impossible physically unless data error),
            // force it to follow SPK cycle. (Assuming scans belong to the SPK cycle they are attached to)
            if ($dbCycle < $spkCycle) {
                $cycleNumber = $spkCycle;
            } else {
                $cycleNumber = $dbCycle;
            }
            
            if (!in_array($cycleNumber, $availableCycles)) {
                $availableCycles[] = $cycleNumber;
            }
            
            if (!isset($cycleData[$partId])) {
                $cycleData[$partId] = [];
            }
            
            if (!isset($cycleData[$partId][$cycleNumber])) {
                $cycleData[$partId][$cycleNumber] = 0;
            }
            
            $cycleData[$partId][$cycleNumber] += $scan->qty;
        }

        // Ensure current SPK cycle is always visible even if no scans yet
        $currentCycle = $spk->cycle_number ?? 1;
        if (!in_array($currentCycle, $availableCycles)) {
            $availableCycles[] = $currentCycle;
        }
        
        sort($availableCycles);

        // Prepare data for each part in SPK
        // Load root SPK (first in lineage) to get original jadwal values
        $rootSpkId = $spkLineage[0]; // First SPK in lineage
        $rootSpk = TSpk::with('details')->find($rootSpkId);
        
        $details = [];
        foreach ($spk->details as $detail) {
            $partId = $detail->part_id;
            $qtyPerBox = $detail->part->QTY_Packing_Box ?? 1;
            
            // Calculate total scanned across all available cycles
            $totalScanned = 0;
            $actualCycles = [];
            
            foreach ($availableCycles as $cyc) {
                $qty = $cycleData[$partId][$cyc] ?? 0;
                $actualCycles[$cyc] = [
                    'qty_pcs' => $qty,
                    'qty_box' => $qtyPerBox > 0 ? round($qty / $qtyPerBox) : 0
                ];
                $totalScanned += $qty;
            }
            
            // Get original jadwal from ROOT SPK (first in lineage)
            $rootDetail = $rootSpk->details->where('part_id', $partId)->first();
            
            $displayJadwal = 0;
            
            if ($rootDetail) {
                $displayJadwal = $rootDetail->original_jadwal_delivery_pcs > 0 
                    ? $rootDetail->original_jadwal_delivery_pcs 
                    : $rootDetail->jadwal_delivery_pcs;
            }
            
            // Fallback to current SPK if 0
            if ($displayJadwal == 0) {
                 $displayJadwal = ($detail->original_jadwal_delivery_pcs ?? 0) > 0 
                    ? $detail->original_jadwal_delivery_pcs 
                    : $detail->jadwal_delivery_pcs;
            }
            
            $displayJumlahBox = $qtyPerBox > 0 ? round($displayJadwal / $qtyPerBox) : 0;
            
            // Calculate balance based on original jadwal from root SPK
            $balance = $displayJadwal - $totalScanned;
            // Ensure positive balance display logic if over-delivery? (Usually kept as real math)
            
            $details[] = [
                'part' => $detail->part,
                'qty_packing_box' => $detail->qty_packing_box,
                'jadwal_delivery_pcs' => $displayJadwal,
                'jumlah_pulling_box' => $displayJumlahBox,
                'actual_cycles' => $actualCycles,
                'total_pulling_pcs' => $totalScanned,
                'total_pulling_box' => $qtyPerBox > 0 ? round($totalScanned / $qtyPerBox) : 0,
                'balance_box' => $qtyPerBox > 0 ? round($balance / $qtyPerBox) : 0,
            ];
        }

        // Load parent SPK for cycle tracking display
        $spk->load('parentSpk');
        $cycleNumber = $spk->cycle_number ?? 1;
        
        return view('finishgood.out.print', compact('spk', 'details', 'availableCycles', 'cycleNumber'));
    }

    /**
     * Create Control Truck entry when Surat Jalan is submitted
     */
    protected function createControlTruckEntry($spk, $noSuratJalan)
    {
        try {
            // Skip if no nomor_plat
            if (!$spk->nomor_plat) {
                Log::info("SPK {$spk->nomor_spk} has no nomor_plat, skipping Control Truck entry");
                return;
            }

            // Find or create kendaraan
            $kendaraan = MKendaraan::where('nopol_kendaraan', $spk->nomor_plat)->first();
            
            if (!$kendaraan) {
                $kendaraan = MKendaraan::create([
                    'nopol_kendaraan' => $spk->nomor_plat,
                    'jenis_kendaraan' => 'Truck', // Default
                    'status' => true,
                ]);
                Log::info("Created new kendaraan: {$spk->nomor_plat}");
            }
            
            // Check if header already exists for this truck + date
            $header = TShippingDeliveryHeader::where('kendaraan_id', $kendaraan->id)
                ->whereDate('tanggal_berangkat', $spk->tanggal)
                ->first();
            
            if (!$header) {
                $header = TShippingDeliveryHeader::create([
                    'kendaraan_id' => $kendaraan->id,
                    'driver_id' => $spk->driver_id,
                    'tanggal_berangkat' => $spk->tanggal,
                    'customer_id' => $spk->customer_id,
                    'spk_id' => $spk->id,
                ]);
                Log::info("Created new delivery header for truck {$spk->nomor_plat} on {$spk->tanggal}");
            }
            
            // Create detail entry with cycle info
            $cycleNumber = $spk->cycle_number ?? 1;
            $jam = $spk->jam_berangkat_plan ? (int) explode(':', $spk->jam_berangkat_plan)[0] : 8;
            
            // Format: "BERANGKAT|C{cycle}|SJ:{no_surat_jalan}|SPK:{nomor_spk}"
            $keterangan = "BERANGKAT|C{$cycleNumber}|SJ:{$noSuratJalan}|SPK:{$spk->nomor_spk}";
            
            TShippingDeliveryDetail::create([
                'header_id' => $header->id,
                'jam' => $jam,
                'waktu_update' => null, // Will be updated when truck actually departs
                'keterangan' => $keterangan,
            ]);
            
            Log::info("Created Control Truck entry: {$keterangan}");
            
        } catch (\Exception $e) {
            Log::error("Failed to create Control Truck entry for SPK {$spk->nomor_spk}: " . $e->getMessage());
            Log::error("Failed to create Control Truck entry for SPK {$spk->nomor_spk}: " . $e->getMessage());
            // Don't throw - this is not critical for SPK submission
        }
    }

    /**
     * Show edit form for Finish Good Out
     */
    public function edit(TSpk $spk)
    {
        if (!userCan('finishgood.out.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $spk->load(['customer', 'plantgate', 'details', 'finishGoodOuts.part']);
        
        $totalScanned = $spk->finishGoodOuts->sum('qty');
        $totalBoxes = $spk->finishGoodOuts->count();
        $totalTarget = $spk->details->sum('jadwal_delivery_pcs');
        
        return view('finishgood.out.edit', compact('spk', 'totalScanned', 'totalBoxes', 'totalTarget'));
    }

    /**
     * Update SPK info (No Surat Jalan)
     */
    public function update(Request $request, TSpk $spk)
    {
        if (!userCan('finishgood.out.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'no_surat_jalan' => 'required|string|max:100'
        ]);

        try {
            DB::transaction(function () use ($request, $spk) {
                // 1. Update SPK
                $oldSj = $spk->no_surat_jalan;
                $spk->update([
                    'no_surat_jalan' => $request->no_surat_jalan
                ]);

                // 2. Sync to TFinishGoodOut records
                TFinishGoodOut::where('spk_id', $spk->id)
                    ->update(['no_surat_jalan' => $request->no_surat_jalan]);
                
                // 3. Sync to Control Truck (if exists)
                // Search pattern for old SJ
                $searchPattern = '%SJ:' . $oldSj . '%';
                $details = TShippingDeliveryDetail::where('keterangan', 'like', $searchPattern)->get();
                
                foreach ($details as $detail) {
                    $newKeterangan = str_replace("SJ:{$oldSj}", "SJ:{$request->no_surat_jalan}", $detail->keterangan);
                    $detail->update(['keterangan' => $newKeterangan]);
                }
            });

            return redirect()->route('finishgood.out.edit', $spk->id)->with('success', 'Data Surat Jalan berhasil diupdate.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * RESET all scanning data for this SPK
     */
    public function reset(Request $request, TSpk $spk)
    {
        if (!userCan('finishgood.out.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        try {
            DB::transaction(function () use ($request, $spk) {
                // 1. Delete all scans
                $count = TFinishGoodOut::where('spk_id', $spk->id)->delete();
                
                // 2. Reset SPK status
                $sjNum = $spk->no_surat_jalan;
                $spk->update([
                    'no_surat_jalan' => null,
                    'catatan' => $spk->catatan . " [RESET by " . auth()->user()->username . ": " . $request->reason . "]"
                ]);

                // 3. Remove from Control Truck
                if ($sjNum) {
                    $searchPattern = "%SPK:{$spk->nomor_spk}%"; // Safest to use SPK number
                    TShippingDeliveryDetail::where('keterangan', 'like', $searchPattern)->delete();
                }

                Log::warning("SPK {$spk->nomor_spk} RESET by user. Reason: {$request->reason}. Deleted {$count} scan records.");
            });

            return redirect()->route('finishgood.out.scan', ['spk' => $spk->id])
                ->with('warning', 'Data scanning telah di-RESET. Silakan mulai scan dari awal.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reset: ' . $e->getMessage());
        }
    }
}
