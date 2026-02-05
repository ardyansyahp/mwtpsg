<?php

namespace App\Http\Controllers\BahanBaku;

use App\Http\Controllers\Controller;
use App\Models\ReceivingDetail;
use App\Models\SMPart;
use App\Models\SMPartSubpart;
use App\Models\TPlanningRun;
use App\Models\TPlanningRunMaterial;
use App\Models\TPlanningRunSubpart;
use App\Models\TSupply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplyController extends Controller
{
    public function index()
    {
        $supplies = TSupply::with([
                'planningRun.mold.part',
                'planningRun.day.mesin',
                'part',
            ])
            ->withCount('details')
            ->orderBy('tanggal_supply', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('bahanbaku.supply.supply', compact('supplies'));
    }

    public function create()
    {
        if (!userCan('bahanbaku.supply.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Planning runs akan di-load via API berdasarkan tipe dan tanggal
            // Tidak perlu load semua planning runs di sini

            // Ambil receiving details dengan relasi bahanBaku untuk filter kategori
            // Handle jika relasi bahanBaku tidak ada dengan left join
            $receivingDetails = ReceivingDetail::with('bahanBaku')
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get();

            // Parts akan di-filter via API berdasarkan tipe planning
            // Load semua parts dulu, filtering dilakukan via JavaScript/API
            $parts = SMPart::orderBy('nomor_part')->get();
            
            // Load mesins untuk dropdown mesin (untuk INJECT)
            $mesins = \App\Models\MMesin::orderBy('no_mesin')->get();
            
            // Ambil daftar meja yang sudah pernah digunakan untuk dropdown (untuk ASSY)
            // Kolom meja ada di T_Supply, bukan T_Planning_Day
            $mejas = \App\Models\TSupply::whereNotNull('meja')
                ->where('meja', '!=', '')
                ->distinct()
                ->orderBy('meja')
                ->pluck('meja')
                ->toArray();
            
            if (empty($mejas)) {
                $mejas = ['MEJA-1', 'MEJA-2', 'MEJA-3', 'MEJA-4', 'MEJA-5'];
            }

            return view('bahanbaku.supply.create', compact('receivingDetails', 'parts', 'mesins', 'mejas'));
        } catch (\Exception $e) {
            \Log::error('Error in SupplyController@create: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat form: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if (!userCan('bahanbaku.supply.create')) {
            abort(403, 'Unauthorized action.');
        }

        \Log::info('Supply Store Request', [
            'all' => $request->all(),
            'tujuan' => $request->tujuan,
            'planning_run_id' => $request->planning_run_id,
            'details_count' => count($request->details ?? [])
        ]);
        
        try {
            $validated = $request->validate([
                'tujuan' => 'required|in:inject,assy',
                'planning_run_id' => [
                    Rule::requiredIf($request->tujuan === 'inject' || $request->tujuan === 'assy'),
                    'nullable',
                    'exists:t_planning_run,id'
                ],
                'part_id' => [
                    Rule::requiredIf($request->tujuan === 'assy' && $request->planning_run_id), // Required untuk ASSY dengan planning_run_id
                    'nullable',
                    'exists:sm_part,id'
                ],
                'meja' => [
                    Rule::requiredIf($request->tujuan === 'assy'),
                    'nullable',
                    'string',
                    'max:50'
                ],
                'tanggal_supply' => 'required|date',
                'shift_no' => 'nullable|integer|min:1|max:3',
                'status' => 'nullable|string|max:20',
                'catatan' => 'nullable|string',

                'details' => 'nullable|array',
                'details.*.receiving_detail_id' => 'required|integer|exists:bb_receiving_detail,id',
                'details.*.qty' => 'required|numeric|min:0.001',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors())),
                'errors' => $e->errors(),
            ], 422);
        }

        // Validasi kategori: ASSY hanya boleh subpart
        if ($request->tujuan === 'assy') {
            $details = $request->details ?? [];
            foreach ($details as $detail) {
                if (empty($detail['receiving_detail_id'])) {
                    continue;
                }
                
                $receivingDetail = ReceivingDetail::with('bahanBaku')->find($detail['receiving_detail_id']);
                if ($receivingDetail && $receivingDetail->bahanBaku) {
                    $kategori = strtolower(trim($receivingDetail->bahanBaku->kategori ?? ''));
                    if ($kategori !== 'subpart') {
                        return response()->json([
                            'success' => false,
                            'message' => 'ASSY hanya boleh supply subpart. Material yang dipilih (' . $receivingDetail->bahanBaku->nama_bahan_baku . ') bukan subpart.',
                        ], 422);
                    }
                }
            }
        }

        DB::transaction(function () use ($validated, $request) {
            $details = $validated['details'] ?? [];
            unset($validated['details']);

            if (empty($validated['status'])) {
                $validated['status'] = 'DRAFT';
            }

            \Log::info('Creating Supply', ['validated' => $validated]);
            
            $supply = TSupply::create($validated);
            
            \Log::info('Supply Created', ['supply_id' => $supply->id]);

            $filtered = array_values(array_filter($details, function ($row) {
                $qty = $row['qty'] ?? null;
                $rid = $row['receiving_detail_id'] ?? null;
                return $rid && $qty !== null && $qty !== '';
            }));

            if (count($filtered) > 0) {
                // Generate lot_number untuk supply ini (sama untuk semua detail dengan supply_id yang sama)
                $tanggalSupply = \Carbon\Carbon::parse($supply->tanggal_supply)->format('Ymd');
                $lotNumber = 'SUP-' . $tanggalSupply . '-' . str_pad($supply->id, 6, '0', STR_PAD_LEFT);

                // Ambil nomor_bahan_baku dari receiving_detail dan set lot_number
                $detailsToCreate = [];
                foreach ($filtered as $row) {
                    $receivingDetail = ReceivingDetail::find($row['receiving_detail_id']);
                    if ($receivingDetail) {
                        $detailsToCreate[] = [
                            'receiving_detail_id' => $row['receiving_detail_id'],
                            'nomor_bahan_baku' => $receivingDetail->nomor_bahan_baku,
                            'lot_number' => $lotNumber,
                            'qty' => $row['qty'],
                        ];
                    }
                }

                if (count($detailsToCreate) > 0) {
                    $supply->details()->createMany($detailsToCreate);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Supply berhasil ditambahkan',
        ]);
    }

    public function edit(TSupply $supply)
    {
        if (!userCan('bahanbaku.supply.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $supply->load(['details.receivingDetail.bahanBaku', 'planningRun.mold.part', 'planningRun.day.mesin']);

            $planningRuns = TPlanningRun::with(['mold.part', 'day.mesin'])
                ->orderBy('start_at', 'desc')
                ->limit(50)
                ->get();

            // Ambil receiving details dengan relasi bahanBaku untuk filter kategori
            // Pastikan semua receiving detail yang digunakan di supply details juga di-load
            $usedReceivingDetailIds = $supply->details->pluck('receiving_detail_id')->filter()->unique()->toArray();
            
            \Log::info('Supply Edit - Used receiving detail IDs:', $usedReceivingDetailIds);
            
            // Load receiving detail yang digunakan di supply details
            $usedReceivingDetails = collect();
            if (!empty($usedReceivingDetailIds)) {
                $usedReceivingDetails = ReceivingDetail::with('bahanBaku')
                    ->whereIn('id', $usedReceivingDetailIds)
                    ->get();
                \Log::info('Supply Edit - Found used receiving details:', ['count' => $usedReceivingDetails->count()]);
            }
            
            // Load yang terbaru untuk dropdown
            $recentReceivingDetails = ReceivingDetail::with('bahanBaku')
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get();
            
            // Gabungkan dan hapus duplikat - pastikan yang digunakan ada di urutan pertama
            $receivingDetails = $usedReceivingDetails->merge($recentReceivingDetails)
                ->unique('id')
                ->values();
            
            \Log::info('Supply Edit - Total receiving details:', ['count' => $receivingDetails->count()]);

            // Ambil parts untuk dropdown ASSY
            $parts = SMPart::orderBy('nomor_part')->get();

            return view('bahanbaku.supply.edit', compact('supply', 'planningRuns', 'receivingDetails', 'parts'));
        } catch (\Exception $e) {
            \Log::error('Error in SupplyController@edit: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat form edit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, TSupply $supply)
    {
        if (!userCan('bahanbaku.supply.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tujuan' => 'required|in:inject,assy',
            'planning_run_id' => [
                Rule::requiredIf($request->input('tujuan') === 'inject'),
                'nullable',
                'exists:t_planning_run,id'
            ],
            'part_id' => [
                Rule::requiredIf($request->input('tujuan') === 'assy'),
                'nullable',
                'exists:sm_part,id'
            ],
            'meja' => [
                Rule::requiredIf($request->input('tujuan') === 'assy'),
                'nullable',
                'string',
                'max:50'
            ],
            'tanggal_supply' => 'required|date',
            'shift_no' => 'nullable|integer|min:1|max:3',
            'status' => 'nullable|string|max:20',
            'catatan' => 'nullable|string',

            'details' => 'nullable|array',
            'details.*.receiving_detail_id' => 'required|integer|exists:bb_receiving_detail,id',
            'details.*.qty' => 'required|numeric|min:0.001',
        ]);

        // Validasi kategori: ASSY hanya boleh subpart
        if ($request->tujuan === 'assy') {
            $details = $request->details ?? [];
            foreach ($details as $detail) {
                if (empty($detail['receiving_detail_id'])) {
                    continue;
                }
                
                $receivingDetail = ReceivingDetail::with('bahanBaku')->find($detail['receiving_detail_id']);
                if ($receivingDetail && $receivingDetail->bahanBaku) {
                    $kategori = strtolower(trim($receivingDetail->bahanBaku->kategori ?? ''));
                    if ($kategori !== 'subpart') {
                        return response()->json([
                            'success' => false,
                            'message' => 'ASSY hanya boleh supply subpart. Material yang dipilih (' . $receivingDetail->bahanBaku->nama_bahan_baku . ') bukan subpart.',
                        ], 422);
                    }
                }
            }
        }

        DB::transaction(function () use ($validated, $supply, $request) {
            $details = $validated['details'] ?? [];
            unset($validated['details']);

            if (empty($validated['status'])) {
                $validated['status'] = 'DRAFT';
            }

            $supply->update($validated);

            $supply->details()->delete();

            $filtered = array_values(array_filter($details, function ($row) {
                $qty = $row['qty'] ?? null;
                $rid = $row['receiving_detail_id'] ?? null;
                return $rid && $qty !== null && $qty !== '';
            }));

            if (count($filtered) > 0) {
                // Generate lot_number untuk supply ini (sama untuk semua detail dengan supply_id yang sama)
                $tanggalSupply = \Carbon\Carbon::parse($supply->tanggal_supply)->format('Ymd');
                $lotNumber = 'SUP-' . $tanggalSupply . '-' . str_pad($supply->id, 6, '0', STR_PAD_LEFT);

                // Ambil nomor_bahan_baku dari receiving_detail dan set lot_number
                $detailsToCreate = [];
                foreach ($filtered as $row) {
                    $receivingDetail = ReceivingDetail::find($row['receiving_detail_id']);
                    if ($receivingDetail) {
                        $detailsToCreate[] = [
                            'receiving_detail_id' => $row['receiving_detail_id'],
                            'nomor_bahan_baku' => $receivingDetail->nomor_bahan_baku,
                            'lot_number' => $lotNumber,
                            'qty' => $row['qty'],
                        ];
                    }
                }

                if (count($detailsToCreate) > 0) {
                    $supply->details()->createMany($detailsToCreate);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Supply berhasil diupdate',
        ]);
    }

    public function delete(TSupply $supply)
    {
        if (!userCan('bahanbaku.supply.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $supply->load(['planningRun.mold.part', 'planningRun.day.mesin'])
            ->loadCount('details');

        return view('bahanbaku.supply.delete', compact('supply'));
    }

    public function destroy(TSupply $supply)
    {
        if (!userCan('bahanbaku.supply.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $supply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supply berhasil dihapus',
        ]);
    }

    public function labels(TSupply $supply)
    {
        $supply->load([
            'details.receivingDetail.bahanBaku',
            'planningRun.mold.part',
            'planningRun.day.mesin',
            'part'
        ]);

        return view('bahanbaku.supply.label', compact('supply'));
    }

    /**
     * API untuk mendapatkan planning runs berdasarkan tipe dan tanggal
     */
    public function getPlanningRuns(Request $request)
    {
        $tipe = $request->input('tipe');
        $tanggal = $request->input('tanggal');
        
        if (!$tipe || !$tanggal) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe dan tanggal harus diisi',
            ], 400);
        }
        
        $query = TPlanningRun::with(['day.mesin', 'day', 'mold.part', 'part'])
            ->whereHas('day', function($q) use ($tanggal, $tipe) {
                $q->where('tanggal', $tanggal)
                  ->where('tipe', $tipe);
            })
            ->orderBy('id', 'asc');
        
        \Log::info('Planning Runs Query', [
            'tipe' => $tipe,
            'tanggal' => $tanggal,
            'count' => $query->count()
        ]);
        
        $runs = $query->get()->map(function($run) {
            $day = $run->day;
            $mesin = $day->tipe === 'inject' 
                ? ($day->mesin->no_mesin ?? '-')
                : ($day->meja ?? '-');
            
            $part = $day->tipe === 'inject'
                ? ($run->mold->part->nomor_part ?? '-')
                : ($run->part->nomor_part ?? '-');
            
            return [
                'id' => $run->id,
                'tanggal' => \Carbon\Carbon::parse($day->tanggal)->format('Y-m-d'),
                'mesin' => $mesin,
                'mesin_id' => $day->tipe === 'inject' ? ($day->mesin_id ?? null) : null,
                'meja' => $day->tipe === 'assy' ? ($day->meja ?? null) : null,
                'part' => $part,
                'part_id' => $day->tipe === 'assy' ? ($run->part_id ?? null) : ($run->mold->part_id ?? null),
                'lot_produksi' => $run->lot_produksi ?? '-',
                'tipe' => $day->tipe ?? 'inject',
            ];
        });
        
        return response()->json([
            'success' => true,
            'runs' => $runs,
        ]);
    }

    /**
     * API untuk mendapatkan parts berdasarkan tipe planning (INJECT/ASSY)
     * Filter berdasarkan material/masterbatch/subpart yang memiliki tipe sesuai
     */
    public function getPartsByTipe(Request $request)
    {
        $tipe = $request->input('tipe');
        
        if (!$tipe || !in_array($tipe, ['inject', 'assy'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe harus diisi (inject/assy)',
            ], 400);
        }

        // Filter parts berdasarkan tipe:
        // - INJECT: Part yang memiliki material1, material2, atau masterbatch (komponen inject)
        //            Atau memiliki subpart/layer dengan tipe 'inject' atau 'both'
        // - ASSY: Part yang memiliki subpart dengan tipe 'assy' atau 'both'
        
        $parts = SMPart::where(function($q) use ($tipe) {
            if ($tipe === 'inject') {
                // INJECT: Part dengan material1, material2, atau masterbatch
                $q->where(function($subQ) {
                    $subQ->whereNotNull('Mat_Material1_id')
                        ->orWhereNotNull('Mat_Material2_id')
                        ->orWhereNotNull('Mat_Masterbatch_id');
                })
                // Atau memiliki subpart dengan tipe inject/both
                ->orWhereHas('partSubparts', function($subQ) {
                    $subQ->where(function($sq) {
                        $sq->where('tipe', 'inject')
                          ->orWhere('tipe', 'both');
                    });
                })
                // Atau memiliki layer dengan tipe inject/both
                ->orWhereHas('partLayers', function($subQ) {
                    $subQ->where(function($sq) {
                        $sq->where('tipe', 'inject')
                          ->orWhere('tipe', 'both');
                    });
                });
            } else {
                // ASSY: Part yang memiliki subpart dengan tipe assy/both
                $q->whereHas('partSubparts', function($subQ) {
                    $subQ->where(function($sq) {
                        $sq->where('tipe', 'assy')
                          ->orWhere('tipe', 'both');
                    });
                });
            }
        })
        ->orderBy('nomor_part')
        ->get()
        ->map(function($part) {
            return [
                'id' => $part->id,
                'nomor_part' => $part->nomor_part,
                'nama_part' => $part->nama_part,
            ];
        });
        
        return response()->json([
            'success' => true,
            'parts' => $parts,
        ]);
    }

    /**
     * API untuk mendapatkan subpart dari part yang dipilih (untuk ASSY)
     */
    public function getPartSubparts(Request $request)
    {
        $partId = $request->input('part_id');
        $tipe = $request->input('tipe', 'assy'); // Default assy untuk API ini

        if (!$partId) {
            return response()->json([
                'success' => false,
                'message' => 'Part ID is required'
            ], 400);
        }

        $part = SMPart::find($partId);
        if (!$part) {
            return response()->json([
                'success' => false,
                'message' => 'Part tidak ditemukan',
            ], 404);
        }

        // Filter subparts berdasarkan tipe
        $subparts = SMPartSubpart::with('subpart')
            ->where('part_id', $partId)
            ->where(function($q) use ($tipe) {
                $q->where('tipe', 'both')
                  ->orWhere('tipe', $tipe);
            })
            ->orderBy('urutan')
            ->get()
            ->map(function (SMPartSubpart $ps) {
                return [
                    'id' => $ps->id, // partsubpart_id
                    'urutan' => $ps->urutan,
                    'std_using' => (string) $ps->std_using,
                    'subpart' => [
                        'id' => $ps->subpart_id,
                        'nama' => $ps->subpart?->nama_bahan_baku,
                        'nomor' => $ps->subpart?->nomor_bahan_baku,
                        'uom' => $ps->subpart?->uom,
                    ],
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'subparts' => $subparts,
            'part' => [
                'id' => $part->id,
                'nomor_part' => $part->nomor_part,
                'nama_part' => $part->nama_part,
            ],
        ]);
    }

    /**
     * API untuk mendapatkan kebutuhan material dari planning run
     */
    public function getPlanningRequirements(Request $request)
    {
        $planningRunId = $request->input('planning_run_id');
        
        if (!$planningRunId) {
            return response()->json([
                'success' => false,
                'message' => 'Planning Run ID tidak boleh kosong',
            ], 400);
        }

        $planningRun = TPlanningRun::with([
            'mold.part',
            'day.mesin',
            'materials.material',
            'subparts.partsubpart.subpart'
        ])->find($planningRunId);

        if (!$planningRun) {
            return response()->json([
                'success' => false,
                'message' => 'Planning Run tidak ditemukan',
            ], 404);
        }

        // Log untuk debugging
        \Log::info('Planning Run Data', [
            'id' => $planningRun->id,
            'lot_produksi' => $planningRun->lot_produksi,
            'lot_produksi_raw' => $planningRun->getRawOriginal('lot_produksi'),
        ]);

        // Ambil materials dari planning
        $materials = TPlanningRunMaterial::with('material')
            ->where('planning_run_id', $planningRunId)
            ->get()
            ->map(function ($pm) {
                return [
                    'id' => $pm->material_id,
                    'nomor_bahan_baku' => $pm->material->nomor_bahan_baku ?? null,
                    'nama_bahan_baku' => $pm->material->nama_bahan_baku ?? '-',
                    'kategori' => $pm->material->kategori ?? null,
                    'qty_total' => $pm->qty_total,
                    'uom' => $pm->uom ?? $pm->material->uom ?? null,
                ];
            });

        // Ambil subparts dari planning
        $subparts = TPlanningRunSubpart::with('partsubpart.subpart')
            ->where('planning_run_id', $planningRunId)
            ->get()
            ->map(function ($ps) {
                $subpart = $ps->partsubpart->subpart ?? null;
                if (!$subpart) {
                    return null;
                }
                return [
                    'id' => $subpart->id ?? null,
                    'nomor_bahan_baku' => $subpart->nomor_bahan_baku ?? null,
                    'nama_bahan_baku' => $subpart->nama_bahan_baku ?? '-',
                    'kategori' => $subpart->kategori ?? null,
                    'qty_total' => $ps->qty_total,
                    'uom' => $ps->uom ?? $subpart->uom ?? null,
                ];
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->values();

        // Pastikan lot_produksi tidak null atau string kosong
        $lotProduksi = $planningRun->lot_produksi;
        if (empty($lotProduksi) || trim($lotProduksi) === '') {
            $lotProduksi = null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'planning_run' => [
                    'id' => $planningRun->id,
                    'lot_produksi' => $lotProduksi,
                    'start_at' => $planningRun->start_at?->format('Y-m-d H:i'),
                    'end_at' => $planningRun->end_at?->format('Y-m-d H:i'),
                    'mesin' => $planningRun->day->tipe === 'inject' 
                        ? ($planningRun->day->mesin->no_mesin ?? '-')
                        : ($planningRun->day->meja ?? '-'),
                    'mesin_id' => $planningRun->day->tipe === 'inject' 
                        ? ($planningRun->day->mesin_id ?? null)
                        : null,
                    'meja' => $planningRun->day->tipe === 'assy' 
                        ? ($planningRun->day->meja ?? null)
                        : null,
                    'part' => $planningRun->day->tipe === 'inject'
                        ? ($planningRun->mold->part->nomor_part ?? '-')
                        : ($planningRun->part->nomor_part ?? '-'),
                    'part_id' => $planningRun->day->tipe === 'assy' 
                        ? ($planningRun->part_id ?? null)
                        : ($planningRun->mold->part_id ?? null),
                    'qty_target' => $planningRun->qty_target_total ?? 0,
                    'tipe' => $planningRun->day->tipe ?? 'inject',
                ],
                'materials' => $materials,
                'subparts' => $subparts,
            ],
        ]);
    }
}
