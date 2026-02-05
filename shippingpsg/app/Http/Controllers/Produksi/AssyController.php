<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\TAssyIn;
use App\Models\TAssyOut;
use App\Models\SMPart;
use App\Models\TSupplyDetail;
use App\Models\TWipOut;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssyController extends Controller
{
    // ========== ASSY IN METHODS ==========

    public function indexIn()
    {
        // Ambil semua data ASSY In dengan relasi
        $assyIns = TAssyIn::with([
            'supplyDetail.supply.part',
            'wipOut.planningRun.mold.part',
            'part',
        ])
            ->orderBy('waktu_scan', 'desc')
            ->get();

        // Kelompokkan berdasarkan lot_number dan hitung qty
        $groupedByLotNumber = [];
        foreach ($assyIns as $assyIn) {
            $lotNumber = $assyIn->lot_number ?? 'UNKNOWN';
            if (!isset($groupedByLotNumber[$lotNumber])) {
                $groupedByLotNumber[$lotNumber] = [
                    'assyIn' => $assyIn,
                    'qty' => 1,
                ];
            } else {
                // Akumulasikan qty
                $groupedByLotNumber[$lotNumber]['qty']++;
                // Update waktu scan ke yang paling baru jika ada
                if ($assyIn->waktu_scan && (!$groupedByLotNumber[$lotNumber]['assyIn']->waktu_scan || 
                    $assyIn->waktu_scan > $groupedByLotNumber[$lotNumber]['assyIn']->waktu_scan)) {
                    $groupedByLotNumber[$lotNumber]['assyIn']->waktu_scan = $assyIn->waktu_scan;
                }
            }
        }

        // Convert ke collection dan urutkan berdasarkan waktu scan terbaru
        $groupedData = collect($groupedByLotNumber)->sortByDesc(function($item) {
            return $item['assyIn']->waktu_scan ? $item['assyIn']->waktu_scan->timestamp : 0;
        })->values();

        // Paginate manual
        $currentPage = request()->get('page', 1);
        $perPage = 15;
        $items = $groupedData->forPage($currentPage, $perPage);
        $total = $groupedData->count();
        
        $assyIns = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('produksi.assy.assyin', compact('assyIns'));
    }

    public function createIn()
    {
        if (!userCan('produksi.assy.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('produksi.assy.createin');
    }

    public function detailIn()
    {
        // Ambil semua data ASSY In dengan relasi lengkap
        $assyIns = TAssyIn::with([
            'supplyDetail.supply.part',
            'supplyDetail.receivingDetail.bahanBaku',
            'supplyDetail.supply.details.receivingDetail.bahanBaku', // Load semua details dari supply
            'wipOut.planningRun.mold.part',
            'wipOut.details',
            'part',
        ])
            ->orderBy('waktu_scan', 'desc')
            ->get();

        // Kelompokkan berdasarkan lot_number
        $groupedByLotNumber = [];
        foreach ($assyIns as $assyIn) {
            $lotNumber = $assyIn->lot_number ?? 'UNKNOWN';
            if (!isset($groupedByLotNumber[$lotNumber])) {
                $groupedByLotNumber[$lotNumber] = [];
            }
            $groupedByLotNumber[$lotNumber][] = $assyIn;
        }

        // Buat data untuk ditampilkan dengan informasi lengkap
        $detailData = [];
        foreach ($groupedByLotNumber as $lotNumber => $assyInGroup) {
            // Ambil data dari record pertama
            $firstAssyIn = $assyInGroup[0];
            
            // Kumpulkan informasi subpart yang digunakan (dari supply_detail)
            // Ambil semua supply_id yang unik dari semua ASSY In dalam group
            $supplyIds = [];
            $supplyDetailIds = [];
            foreach ($assyInGroup as $item) {
                if ($item->supplyDetail) {
                    if ($item->supplyDetail->supply_id) {
                        $supplyIds[] = $item->supplyDetail->supply_id;
                    }
                    $supplyDetailIds[] = $item->supplyDetail->id;
                }
            }
            $supplyIds = array_unique($supplyIds);
            $supplyDetailIds = array_unique($supplyDetailIds);
            
            // Ambil semua supply details dari semua supply yang terkait
            $subparts = [];
            $suppliedLotNumbers = [];
            
            if (!empty($supplyIds)) {
                // Load semua supply details dari semua supply yang terkait
                $allSupplyDetails = \App\Models\TSupplyDetail::with(['receivingDetail.bahanBaku', 'supply'])
                    ->whereIn('supply_id', $supplyIds)
                    ->get();
                
                foreach ($allSupplyDetails as $supplyDetail) {
                    $bahanBaku = $supplyDetail->receivingDetail ? $supplyDetail->receivingDetail->bahanBaku : null;
                    if ($bahanBaku) {
                        // Gunakan kombinasi bahanBaku->id dan lot_number sebagai key
                        $key = $bahanBaku->id . '_' . ($supplyDetail->lot_number ?? 'no_lot');
                        
                        if (!isset($subparts[$key])) {
                            $subparts[$key] = [
                                'nama' => $bahanBaku->nama_bahan_baku,
                                'qty' => $supplyDetail->qty ?? 0,
                                'satuan' => $bahanBaku->satuan ?? 'pcs',
                                'lot_number' => $supplyDetail->lot_number,
                                'supply_detail_id' => $supplyDetail->id,
                            ];
                        } else {
                            // Jika key sama, akumulasikan qty
                            $subparts[$key]['qty'] += ($supplyDetail->qty ?? 0);
                        }
                    }
                    if ($supplyDetail->lot_number) {
                        $suppliedLotNumbers[] = $supplyDetail->lot_number;
                    }
                }
            }
            
            // Fallback: jika tidak ada supply_id, ambil dari supplyDetail individual
            if (empty($subparts) && !empty($supplyDetailIds)) {
                foreach ($assyInGroup as $item) {
                    if ($item->supplyDetail) {
                        $bahanBaku = $item->supplyDetail->receivingDetail ? $item->supplyDetail->receivingDetail->bahanBaku : null;
                        if ($bahanBaku) {
                            $key = $bahanBaku->id . '_' . ($item->supplyDetail->lot_number ?? 'no_lot');
                            
                            if (!isset($subparts[$key])) {
                                $subparts[$key] = [
                                    'nama' => $bahanBaku->nama_bahan_baku,
                                    'qty' => $item->supplyDetail->qty ?? 0,
                                    'satuan' => $bahanBaku->satuan ?? 'pcs',
                                    'lot_number' => $item->supplyDetail->lot_number,
                                    'supply_detail_id' => $item->supplyDetail->id,
                                ];
                            } else {
                                $subparts[$key]['qty'] += ($item->supplyDetail->qty ?? 0);
                            }
                        }
                        if ($item->supplyDetail->lot_number) {
                            $suppliedLotNumbers[] = $item->supplyDetail->lot_number;
                        }
                    }
                }
            }
            
            // Kumpulkan informasi WIP Out boxes yang di-scan
            $wipOutBoxes = [];
            $wipOutLotNumbers = [];
            foreach ($assyInGroup as $item) {
                if ($item->wipOut) {
                    $wipOutBoxes[] = [
                        'lot_number' => $item->wipOut->lot_number,
                        'box_number' => $item->wipOut->box_number,
                        'waktu_scan' => $item->waktu_scan,
                    ];
                    if ($item->wipOut->lot_number) {
                        $wipOutLotNumbers[] = $item->wipOut->lot_number;
                    }
                }
            }
            
            // Ambil part hasil akhir
            $part = $firstAssyIn->part;
            if (!$part && $firstAssyIn->supplyDetail && $firstAssyIn->supplyDetail->supply) {
                $part = $firstAssyIn->supplyDetail->supply->part;
            }
            if (!$part && $firstAssyIn->wipOut && $firstAssyIn->wipOut->planningRun && $firstAssyIn->wipOut->planningRun->mold) {
                $part = $firstAssyIn->wipOut->planningRun->mold->part;
            }

            // Ambil planning run dari wipOut
            $planningRun = null;
            if ($firstAssyIn->wipOut && $firstAssyIn->wipOut->planningRun) {
                $planningRun = $firstAssyIn->wipOut->planningRun;
            }

            // Hitung target dan progress
            $qtyPackingBox = $part ? ($part->QTY_Packing_Box ?? 0) : 0;
            $targetTotal = $planningRun ? ($planningRun->qty_target_total ?? 0) : 0;
            $targetBoxCount = $qtyPackingBox > 0 ? (int) ceil($targetTotal / $qtyPackingBox) : 0;
            
            // Hitung box yang sudah di-scan
            $scannedBoxCount = count($assyInGroup);
            
            // Ambil box numbers dari WIP Out details jika ada
            $boxNumbers = [];
            if ($firstAssyIn->wipOut && $firstAssyIn->wipOut->details) {
                $boxNumbers = $firstAssyIn->wipOut->details->pluck('box_number')->sort()->values()->all();
            }

            $detailData[] = [
                'id' => $firstAssyIn->id,
                'lot_number' => $lotNumber,
                'waktu_scan' => $firstAssyIn->waktu_scan,
                'part' => $part,
                'manpower' => $firstAssyIn->manpower,
                'planning_run' => $planningRun,
                'qty_packing_box' => $qtyPackingBox,
                'target_total' => $targetTotal,
                'target_box_count' => $targetBoxCount,
                'scanned_box_count' => $scannedBoxCount,
                'box_numbers' => $boxNumbers,
                'subparts' => array_values($subparts),
                'supplied_lot_numbers' => array_unique($suppliedLotNumbers),
                'wip_out_boxes' => $wipOutBoxes,
                'wip_out_lot_numbers' => array_unique($wipOutLotNumbers),
                'meja' => $firstAssyIn->supplyDetail && $firstAssyIn->supplyDetail->supply ? $firstAssyIn->supplyDetail->supply->meja : null,
            ];
        }

        // Urutkan berdasarkan waktu scan terbaru
        usort($detailData, function($a, $b) {
            $timeA = $a['waktu_scan'] ? $a['waktu_scan']->timestamp : 0;
            $timeB = $b['waktu_scan'] ? $b['waktu_scan']->timestamp : 0;
            return $timeB <=> $timeA;
        });

        return view('produksi.assy.detailin', compact('detailData'));
    }

    // ========== ASSY OUT METHODS ==========

    public function indexOut()
    {
        // Ambil semua data ASSY Out dengan relasi
        $assyOuts = TAssyOut::with([
            'assyIn.supplyDetail.supply.part',
            'assyIn.wipOut.planningRun.mold.part',
            'part',
        ])
            ->orderBy('waktu_scan', 'desc')
            ->get();

        // Kelompokkan berdasarkan lot_number dan hitung qty
        $groupedByLotNumber = [];
        foreach ($assyOuts as $assyOut) {
            $lotNumber = $assyOut->lot_number ?? 'UNKNOWN';
            if (!isset($groupedByLotNumber[$lotNumber])) {
                $groupedByLotNumber[$lotNumber] = [
                    'assyOut' => $assyOut,
                    'qty' => 1,
                ];
            } else {
                // Akumulasikan qty
                $groupedByLotNumber[$lotNumber]['qty']++;
                // Update waktu scan ke yang paling baru jika ada
                if ($assyOut->waktu_scan && (!$groupedByLotNumber[$lotNumber]['assyOut']->waktu_scan || 
                    $assyOut->waktu_scan > $groupedByLotNumber[$lotNumber]['assyOut']->waktu_scan)) {
                    $groupedByLotNumber[$lotNumber]['assyOut']->waktu_scan = $assyOut->waktu_scan;
                }
            }
        }

        // Convert ke collection dan urutkan berdasarkan waktu scan terbaru
        $groupedData = collect($groupedByLotNumber)->sortByDesc(function($item) {
            return $item['assyOut']->waktu_scan ? $item['assyOut']->waktu_scan->timestamp : 0;
        })->values();

        // Paginate manual
        $currentPage = request()->get('page', 1);
        $perPage = 15;
        $items = $groupedData->forPage($currentPage, $perPage);
        $total = $groupedData->count();
        
        $assyOuts = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('produksi.assy.assyout', compact('assyOuts'));
    }

    public function createOut()
    {
        if (!userCan('produksi.assy.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('produksi.assy.createout');
    }

    public function storeOut(Request $request): JsonResponse
    {
        if (!userCan('produksi.assy.create')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'lot_number' => 'required|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        try {
            $assyOut = null;
            DB::transaction(function () use ($validated, &$assyOut) {
                // Normalisasi lot number
                $lotNumber = trim($validated['lot_number']);
                $lotNumberNormalized = preg_replace('/\s+/', ' ', $lotNumber);
                
                // Cari assy in berdasarkan lot number yang BELUM di-scan out
                // Coba exact match dulu
                $assyIn = TAssyIn::where('lot_number', $lotNumber)
                    ->orWhere('lot_number', $lotNumberNormalized)
                    ->whereDoesntHave('assyOuts') // Cari yang belum punya ASSY Out
                    ->first();
                
                // Jika tidak ketemu, coba dengan membandingkan tanpa spasi
                if (!$assyIn) {
                    $lotNumberNoSpace = preg_replace('/\s+/', '', $lotNumberNormalized);
                    $assyIns = TAssyIn::whereDoesntHave('assyOuts')->get();
                    
                    foreach ($assyIns as $ai) {
                        $dbLotNumberNoSpace = preg_replace('/\s+/', '', $ai->lot_number ?? '');
                        if (strcasecmp($lotNumberNoSpace, $dbLotNumberNoSpace) === 0) {
                            $assyIn = $ai;
                            break;
                        }
                    }
                }
                
                // Jika tidak ada yang belum di-scan, cek apakah lot number ini pernah ada di ASSY In
                if (!$assyIn) {
                    // Cek apakah lot number pernah ada
                    $anyAssyIn = TAssyIn::where('lot_number', $lotNumber)
                        ->orWhere('lot_number', $lotNumberNormalized)
                        ->first();
                    
                    // Jika tidak ketemu exact, coba tanpa spasi
                    if (!$anyAssyIn) {
                        $lotNumberNoSpace = preg_replace('/\s+/', '', $lotNumberNormalized);
                        $allAssyIns = TAssyIn::all();
                        
                        foreach ($allAssyIns as $ai) {
                            $dbLotNumberNoSpace = preg_replace('/\s+/', '', $ai->lot_number ?? '');
                            if (strcasecmp($lotNumberNoSpace, $dbLotNumberNoSpace) === 0) {
                                $anyAssyIn = $ai;
                                break;
                            }
                        }
                    }
                    
                    if (!$anyAssyIn) {
                        throw new \Exception('Label dengan lot number tersebut tidak ditemukan di assy in');
                    } else {
                        throw new \Exception('Semua box untuk lot number ini sudah di-scan out');
                    }
                }

                // Waktu scan out
                $waktuScan = now('Asia/Jakarta');

                // Simpan assy out - gunakan lot_number dari database (yang ditemukan) untuk konsistensi
                $assyOut = TAssyOut::create([
                    'assy_in_id' => $assyIn->id,
                    'lot_number' => $assyIn->lot_number, // Gunakan lot_number dari database, bukan dari input
                    'part_id' => $assyIn->part_id,
                    'waktu_scan' => $waktuScan,
                    'catatan' => $validated['catatan'] ?? null,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Label berhasil di-scan out',
                'assy_out_id' => $assyOut->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API untuk mencari assy in berdasarkan lot number (untuk ASSY Out)
     */
    public function getAssyInByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            // Normalisasi lot number: trim dan hapus spasi berlebih
            $lotNumberNormalized = trim($lotNumber);
            // Hapus spasi ganda menjadi satu spasi
            $lotNumberNormalized = preg_replace('/\s+/', ' ', $lotNumberNormalized);
            
            \Log::info('Searching ASSY In by lot number', [
                'original' => $lotNumber,
                'normalized' => $lotNumberNormalized
            ]);

            // Coba exact match dulu
            $assyIn = TAssyIn::with([
                'part',
                'supplyDetail.supply.part',
                'wipOut.planningRun.mold.part',
            ])
            ->where('lot_number', $lotNumber)
            ->orWhere('lot_number', $lotNumberNormalized)
            ->first();

            // Jika tidak ketemu, coba dengan membandingkan tanpa spasi (case-insensitive)
            if (!$assyIn) {
                // Hapus semua spasi untuk comparison
                $lotNumberNoSpace = preg_replace('/\s+/', '', $lotNumberNormalized);
                
                \Log::info('Trying search without spaces', [
                    'lot_number_no_space' => $lotNumberNoSpace
                ]);
                
                // Ambil semua ASSY In (limit untuk performa)
                $assyIns = TAssyIn::with([
                    'part',
                    'supplyDetail.supply.part',
                    'wipOut.planningRun.mold.part',
                ])
                ->limit(1000) // Limit untuk performa
                ->get();

                // Cari manual dengan membandingkan tanpa spasi
                foreach ($assyIns as $ai) {
                    $dbLotNumberNoSpace = preg_replace('/\s+/', '', $ai->lot_number ?? '');
                    if (strcasecmp($lotNumberNoSpace, $dbLotNumberNoSpace) === 0) {
                        \Log::info('Found match without spaces', [
                            'found_lot_number' => $ai->lot_number,
                            'searched' => $lotNumberNormalized
                        ]);
                        $assyIn = $ai;
                        break;
                    }
                }
            }
            
            // Jika masih tidak ketemu, coba dengan LIKE search (untuk partial match)
            if (!$assyIn) {
                // Ambil bagian part number saja (sebelum pipe pertama)
                $partNumberOnly = explode('|', $lotNumberNormalized)[0];
                $partNumberNoSpace = preg_replace('/\s+/', '', $partNumberOnly);
                
                \Log::info('Trying LIKE search with part number', [
                    'part_number' => $partNumberOnly,
                    'part_number_no_space' => $partNumberNoSpace
                ]);
                
                // Cari dengan LIKE pada part number
                $assyIns = TAssyIn::with([
                    'part',
                    'supplyDetail.supply.part',
                    'wipOut.planningRun.mold.part',
                ])
                ->whereRaw('REPLACE(SUBSTRING_INDEX(lot_number, "|", 1), " ", "") = ?', [$partNumberNoSpace])
                ->limit(10)
                ->get();
                
                if ($assyIns->count() > 0) {
                    // Ambil yang pertama yang belum di-scan out
                    foreach ($assyIns as $ai) {
                        $dbLotNumberNoSpace = preg_replace('/\s+/', '', $ai->lot_number ?? '');
                        if (strcasecmp($lotNumberNoSpace, $dbLotNumberNoSpace) === 0) {
                            $assyIn = $ai;
                            break;
                        }
                    }
                }
            }

            if (!$assyIn) {
                // Ambil beberapa lot number dari database untuk debugging
                $sampleLotNumbers = TAssyIn::select('lot_number')
                    ->limit(5)
                    ->pluck('lot_number')
                    ->toArray();
                
                \Log::warning('ASSY In not found', [
                    'lot_number_searched' => $lotNumber,
                    'normalized' => $lotNumberNormalized,
                    'lot_number_no_space' => preg_replace('/\s+/', '', $lotNumberNormalized),
                    'sample_lot_numbers_in_db' => $sampleLotNumbers
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Label dengan lot number tersebut tidak ditemukan di assy in. Lot number yang dicari: ' . $lotNumber . '. Pastikan lot number sudah di-scan in di ASSY In.',
                    'debug' => [
                        'searched' => $lotNumber,
                        'normalized' => $lotNumberNormalized,
                        'sample_in_db' => $sampleLotNumbers
                    ]
                ], 404);
            }

            // Cek apakah SEMUA box dengan lot number ini sudah di-scan out
            // Gunakan lot number dari database (yang ditemukan) untuk perhitungan
            $dbLotNumber = $assyIn->lot_number;
            $totalAssyIn = TAssyIn::where('lot_number', $dbLotNumber)->count();
            $totalAssyOut = TAssyOut::where('lot_number', $dbLotNumber)->count();
            $alreadyScannedOut = ($totalAssyOut >= $totalAssyIn);

            // Ambil part dari assy in atau dari supply
            $part = $assyIn->part;
            if (!$part && $assyIn->supplyDetail && $assyIn->supplyDetail->supply) {
                $part = $assyIn->supplyDetail->supply->part;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'assy_in' => [
                        'id' => $assyIn->id,
                        'lot_number' => $assyIn->lot_number,
                        'waktu_scan' => $assyIn->waktu_scan->format('Y-m-d H:i:s'),
                        'manpower' => $assyIn->manpower,
                    ],
                    'part' => $part ? [
                        'id' => $part->id,
                        'nomor_part' => $part->nomor_part,
                        'nama_part' => $part->nama_part,
                    ] : null,
                    'already_scanned_out' => $alreadyScannedOut,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAssyInByLotNumber: ' . $e->getMessage(), [
                'lot_number' => $lotNumber,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari assy out berdasarkan lot number (untuk Finish Good In)
     */
    public function getAssyOutByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            $assyOut = TAssyOut::with([
                'part',
                'assyIn.part',
            ])
            ->where('lot_number', $lotNumber)
            ->first();

            if (!$assyOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Label dengan lot number tersebut tidak ditemukan di assy out',
                ], 404);
            }

            // Cek apakah sudah pernah di-scan in di finish good
            $alreadyScannedIn = \App\Models\TFinishGoodIn::where('assy_out_id', $assyOut->id)->exists();

            // Ambil part dari assy out atau dari assy in
            $part = $assyOut->part;
            if (!$part && $assyOut->assyIn) {
                $part = $assyOut->assyIn->part;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'assy_out' => [
                        'id' => $assyOut->id,
                        'lot_number' => $assyOut->lot_number,
                        'waktu_scan' => $assyOut->waktu_scan->format('Y-m-d H:i:s'),
                    ],
                    'assy_in' => $assyOut->assyIn ? [
                        'id' => $assyOut->assyIn->id,
                        'lot_number' => $assyOut->assyIn->lot_number,
                        'waktu_scan' => $assyOut->assyIn->waktu_scan->format('Y-m-d H:i:s'),
                    ] : null,
                    'part' => $part ? [
                        'id' => $part->id,
                        'nomor_part' => $part->nomor_part,
                        'nama_part' => $part->nama_part,
                    ] : null,
                    'already_scanned_in' => $alreadyScannedIn,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeIn(Request $request): JsonResponse
    {
        if (!userCan('produksi.assy.create')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'supply_detail_id' => 'nullable|exists:T_Supply_Detail,id',
            'wip_out_id' => 'nullable|exists:T_Wip_Out,id',
            'part_id' => 'nullable|exists:SM_Part,id',
            'manpower' => 'nullable|string|max:100',
            'waktu_scan' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        try {
            $assyIn = null;
            DB::transaction(function () use ($validated, &$assyIn) {
                // Tidak perlu validasi duplikasi karena lot number yang sama boleh ditambahkan berkali-kali
                // (qty/box number yang bertambah)

                // Jika waktu_scan tidak diisi, gunakan waktu sekarang dengan timezone Asia/Jakarta
                if (empty($validated['waktu_scan'])) {
                    $validated['waktu_scan'] = now('Asia/Jakarta');
                } else {
                    // Pastikan waktu_scan menggunakan timezone Asia/Jakarta
                    $validated['waktu_scan'] = Carbon::parse($validated['waktu_scan'])->setTimezone('Asia/Jakarta');
                }

                // Jika ada wip_out_id, gunakan lot number dari WIP Out (tidak di-generate)
                if (!empty($validated['wip_out_id'])) {
                    $wipOut = TWipOut::find($validated['wip_out_id']);
                    if ($wipOut && $wipOut->lot_number) {
                        // Gunakan lot number dari WIP Out langsung, tanpa perubahan
                        $validated['lot_number'] = $wipOut->lot_number;
                    } else {
                        // Fallback: generate lot number jika WIP Out tidak ditemukan
                        $lotNumber = $this->generateLotNumber($validated);
                        $validated['lot_number'] = $lotNumber;
                    }
                } else {
                    // Jika tidak ada wip_out_id, generate lot number baru
                    $lotNumber = $this->generateLotNumber($validated);
                    $validated['lot_number'] = $lotNumber;
                }

                $assyIn = TAssyIn::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data scan in berhasil disimpan',
                'assy_in_id' => $assyIn->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari supply detail berdasarkan lot number (untuk ASSY)
     */
    public function getSupplyDetailByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            // Cari supply detail berdasarkan lot_number
            $supplyDetail = TSupplyDetail::with([
                'supply.part',
            ])
            ->where('lot_number', $lotNumber)
            ->first();

            if (! $supplyDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supply dengan lot number tersebut tidak ditemukan',
                ], 404);
            }

            // Validasi: supply harus untuk assy
            if ($supplyDetail->supply->tujuan !== 'assy') {
                return response()->json([
                    'success' => false,
                    'message' => 'Supply ini bukan untuk assy. Tujuan: ' . strtoupper($supplyDetail->supply->tujuan),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $supplyDetail->id,
                    'lot_number' => $supplyDetail->lot_number,
                    'qty' => $supplyDetail->qty,
                    'supply' => [
                        'id' => $supplyDetail->supply->id,
                        'tanggal_supply' => $supplyDetail->supply->tanggal_supply?->format('Y-m-d'),
                        'tujuan' => $supplyDetail->supply->tujuan,
                        'part_id' => $supplyDetail->supply->part_id,
                        'part' => $supplyDetail->supply->part ? [
                            'id' => $supplyDetail->supply->part->id,
                            'nomor_part' => $supplyDetail->supply->part->nomor_part,
                            'nama_part' => $supplyDetail->supply->part->nama_part,
                        ] : null,
                        'meja' => $supplyDetail->supply->meja,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari operator/manpower berdasarkan QR code (untuk ASSY In)
     */
    public function getManpowerByQR(string $qrCode): JsonResponse
    {
        try {
            // Normalisasi QR code: hapus spasi, ubah ke uppercase untuk pencarian
            $qrCodeNormalized = strtoupper(trim($qrCode));
            
            // Cari berdasarkan qrcode column
            $manpower = \App\Models\MManpower::where('qrcode', $qrCodeNormalized)
                ->orWhere('qrcode', $qrCode)
                ->orWhere('nama', 'like', '%' . $qrCode . '%')
                ->orWhere('nik', $qrCode)
                ->first();

            if (!$manpower) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operator tidak ditemukan dengan QR code: ' . $qrCode,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $manpower->id,
                    'nama' => $manpower->nama,
                    'nik' => $manpower->nik,
                    'qrcode' => $manpower->qrcode,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getManpowerByQR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari wip out berdasarkan lot number (untuk ASSY In)
     */
    public function getWipOutByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            // Normalisasi lot number: trim dan hapus spasi berlebih
            $lotNumberNormalized = trim($lotNumber);
            // Hapus spasi ganda menjadi satu spasi
            $lotNumberNormalized = preg_replace('/\s+/', ' ', $lotNumberNormalized);
            
            \Log::info('Searching WIP Out by lot number', [
                'original' => $lotNumber,
                'normalized' => $lotNumberNormalized
            ]);

            // Coba exact match dulu
            $wipOut = TWipOut::with([
                'planningRun.mold.part',
                'wipIn',
                'injectOut.injectIn.mesin',
            ])
            ->where('lot_number', $lotNumber)
            ->orWhere('lot_number', $lotNumberNormalized)
            ->first();

            // Jika tidak ketemu, coba dengan LIKE (untuk handle spasi atau format sedikit berbeda)
            if (!$wipOut) {
                // Hapus semua spasi untuk comparison
                $lotNumberNoSpace = preg_replace('/\s+/', '', $lotNumberNormalized);
                
                $wipOuts = TWipOut::with([
                    'planningRun.mold.part',
                    'wipIn',
                    'injectOut.injectIn.mesin',
                ])
                ->get();

                // Cari manual dengan membandingkan tanpa spasi
                foreach ($wipOuts as $wo) {
                    $dbLotNumberNoSpace = preg_replace('/\s+/', '', $wo->lot_number ?? '');
                    if (strcasecmp($lotNumberNoSpace, $dbLotNumberNoSpace) === 0) {
                        $wipOut = $wo;
                        break;
                    }
                }
            }

            // Jika masih tidak ketemu, coba cari dari TWipOutDetail jika ada
            if (!$wipOut) {
                // Mungkin lot number ada di detail, coba cari dari inject out
                $injectOut = \App\Models\TInjectOut::where('lot_number', $lotNumber)
                    ->orWhere('lot_number', $lotNumberNormalized)
                    ->first();
                
                if ($injectOut) {
                    // Cari WIP Out yang terkait dengan inject out ini
                    $wipOut = TWipOut::with([
                        'planningRun.mold.part',
                        'wipIn',
                        'injectOut.injectIn.mesin',
                    ])
                    ->where('inject_out_id', $injectOut->id)
                    ->first();
                }
            }

            if (!$wipOut) {
                \Log::warning('WIP Out not found', [
                    'lot_number' => $lotNumber,
                    'normalized' => $lotNumberNormalized
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Label dengan lot number tersebut tidak ditemukan di wip out. Lot number yang dicari: ' . $lotNumber,
                ], 404);
            }

            // Cek apakah sudah pernah di-scan in
            $alreadyScannedIn = TAssyIn::where('wip_out_id', $wipOut->id)->exists();

            $planningRun = $wipOut->planningRun;
            $part = $planningRun && $planningRun->mold ? $planningRun->mold->part : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'wip_out' => [
                        'id' => $wipOut->id,
                        'lot_number' => $wipOut->lot_number,
                        'box_number' => $wipOut->box_number,
                        'waktu_scan_out' => $wipOut->waktu_scan_out->format('Y-m-d H:i:s'),
                    ],
                    'planning_run' => $planningRun ? [
                        'id' => $planningRun->id,
                    ] : null,
                    'part' => $part ? [
                        'nomor_part' => $part->nomor_part,
                        'nama_part' => $part->nama_part,
                    ] : null,
                    'already_scanned_in' => $alreadyScannedIn,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getWipOutByLotNumber: ' . $e->getMessage(), [
                'lot_number' => $lotNumber,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate lot number untuk ASSY In dengan format: PART_NUMBER|1200058|QTY|DATE-SHIFT
     * Contoh: 64521-K1Y-DC00|1200058|10|29-9-25-1
     */
    private function generateLotNumber(array $data): string
    {
        $waktuScan = Carbon::parse($data['waktu_scan'])->setTimezone('Asia/Jakarta');
        
        // 1. Nomor part (dari part_id)
        $nomorPart = '-';
        $qtyPacking = 0;
        if (!empty($data['part_id'])) {
            $part = SMPart::find($data['part_id']);
            if ($part) {
                $nomorPart = $part->nomor_part;
                $qtyPacking = $part->QTY_Packing_Box ?? 0;
            }
        }
        
        // 2. Identitas perusahaan (statis)
        $identitasPerusahaan = '1200058';
        
        // 3. QTY Packing Box
        $qtyPackingStr = (string) $qtyPacking;
        
        // 4. Format: 29-9-25-1
        // - 29-9-25 = tanggal (format d-m-y)
        $tanggalStr = $waktuScan->format('j-n-y'); // j = day tanpa leading zero, n = month tanpa leading zero, y = 2 digit year
        
        // - 1 = shift (dari waktu_scan)
        // Shift: 07:00-15:00 = shift 1, 15:00-23:00 = shift 2, 23:00-07:00 = shift 3
        $jam = (int) $waktuScan->format('H');
        $shift = 1; // default
        if ($jam >= 7 && $jam < 15) {
            $shift = 1; // 07:00 - 14:59 (7 pagi - 3 sore)
        } elseif ($jam >= 15 && $jam < 23) {
            $shift = 2; // 15:00 - 22:59 (3 sore - 11 malam)
        } else {
            $shift = 3; // 23:00 - 06:59 (11 malam - 7 pagi)
        }
        
        // Format: PART_NUMBER|IDENTITAS|QTY|DATE-SHIFT
        // Contoh: 64521-K1Y-DC00|1200058|10|29-9-25-1
        $lotNumber = sprintf(
            '%s|%s|%s|%s-%s',
            $nomorPart,
            $identitasPerusahaan,
            $qtyPackingStr,
            $tanggalStr,
            $shift
        );
        
        return $lotNumber;
    }

    public function editIn(TAssyIn $assyIn)
    {
        if (!userCan('produksi.assy.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $assyIn->load([
            'supplyDetail.supply.part',
            'wipOut.planningRun.mold.part',
            'part',
        ]);
        return view('produksi.assy.editin', compact('assyIn'));
    }

    public function updateIn(Request $request, TAssyIn $assyIn): JsonResponse
    {
        if (!userCan('produksi.assy.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'manpower' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        try {
            $assyIn->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteIn(TAssyIn $assyIn)
    {
        if (!userCan('produksi.assy.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $assyIn->load([
            'supplyDetail.supply.part',
            'wipOut.planningRun.mold.part',
            'part',
        ]);
        return view('produksi.assy.deletein', compact('assyIn'));
    }

    public function destroyIn(TAssyIn $assyIn): JsonResponse
    {
        if (!userCan('produksi.assy.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $assyIn->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ========== ASSY OUT METHODS ==========

    public function detailOut(TAssyOut $assyOut)
    {
        $assyOut->load([
            'assyIn.supplyDetail.supply.part',
            'assyIn.supplyDetail.receivingDetail.bahanBaku',
            'assyIn.wipOut.planningRun.mold.part',
            'assyIn.wipOut.details',
            'assyIn.part',
            'part',
        ]);

        // Ambil semua ASSY Out yang terkait dengan lot number yang sama
        $relatedAssyOuts = TAssyOut::with([
            'assyIn.supplyDetail.supply.part',
            'assyIn.supplyDetail.receivingDetail.bahanBaku',
            'assyIn.wipOut.planningRun.mold.part',
            'assyIn.wipOut.details',
            'assyIn.part',
            'part',
        ])
        ->where('lot_number', $assyOut->lot_number)
        ->orderBy('waktu_scan', 'asc')
        ->get();

        return view('produksi.assy.detailout', compact('assyOut', 'relatedAssyOuts'));
    }

    public function editOut(TAssyOut $assyOut)
    {
        if (!userCan('produksi.assy.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $assyOut->load([
            'assyIn.supplyDetail.supply.part',
            'assyIn.wipOut.planningRun.mold.part',
            'assyIn.part',
            'part',
        ]);
        return view('produksi.assy.editout', compact('assyOut'));
    }

    public function updateOut(Request $request, TAssyOut $assyOut): JsonResponse
    {
        if (!userCan('produksi.assy.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        try {
            $assyOut->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteOut(TAssyOut $assyOut)
    {
        if (!userCan('produksi.assy.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $assyOut->load([
            'assyIn.supplyDetail.supply.part',
            'assyIn.wipOut.planningRun.mold.part',
            'assyIn.part',
            'part',
        ]);
        return view('produksi.assy.deleteout', compact('assyOut'));
    }

    public function destroyOut(TAssyOut $assyOut): JsonResponse
    {
        try {
            $assyOut->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
