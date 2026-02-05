<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Models\MBahanBaku;
use App\Models\MMesin;
use App\Models\MMold;
use App\Models\SMPartSubpart;
use App\Models\TPlanningDay;
use App\Models\TPlanningRun;
use App\Models\TPlanningRunHourlyActual;
use App\Models\TPlanningRunHourlyTarget;
use App\Models\TPlanningRunKebutuhan;
use App\Models\TPlanningRunMaterial;
use App\Models\TPlanningRunMaterialShift;
use App\Models\TPlanningRunSubpart;
use App\Models\TPlanningRunSubpartShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $mesins = MMesin::orderBy('no_mesin')->get();
        $molds = MMold::with(['perusahaan', 'part'])->orderBy('kode_mold')->get();
        $materials = MBahanBaku::whereIn('kategori', ['material', 'masterbatch'])->orderBy('nama_bahan_baku')->get();
        
        // Box: kategori box
        $boxes = MBahanBaku::where('kategori', 'box')->orderBy('nama_bahan_baku')->get();
        
        // Polybag: LDPE (case insensitive)
        $polybags = MBahanBaku::whereRaw('LOWER(TRIM(kategori)) = ?', ['ldpe'])->orderBy('nama_bahan_baku')->get();
        
        // Load semua parts (filtering berdasarkan tipe akan dilakukan via JavaScript di view)
        $parts = \App\Models\SMPart::orderBy('nomor_part')->get();
        
        // Ambil daftar meja yang sudah pernah digunakan (unique) untuk dropdown
        // Kolom meja ada di T_Supply, bukan T_Planning_Day
        $mejas = \App\Models\TSupply::whereNotNull('meja')
            ->where('meja', '!=', '')
            ->distinct()
            ->orderBy('meja')
            ->pluck('meja')
            ->toArray();
        
        // Jika belum ada data meja, berikan opsi default
        if (empty($mejas)) {
            $mejas = ['MEJA-1', 'MEJA-2', 'MEJA-3', 'MEJA-4', 'MEJA-5'];
        }

        $planningDays = TPlanningDay::with(['mesin', 'runs.mold', 'runs.part'])
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        $hourSlots = $this->hourSlots();

        return view('planning.planning', compact('planningDays', 'mesins', 'molds', 'materials', 'boxes', 'polybags', 'parts', 'hourSlots', 'mejas'));
    }

    public function store(Request $request)
    {
        if (!userCan('planning.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'tipe' => 'required|in:inject,assy',
                'mesin_id' => 'required_if:tipe,inject|nullable|exists:M_Mesin,id',
                'meja' => 'required_if:tipe,assy|nullable|string|max:50',
            ]);

            \Log::info('Planning Store Request', [
                'validated' => $validated,
                'runs_count' => count($request->input('runs', [])),
                'all_input' => $request->all(),
            ]);

            return DB::transaction(function () use ($request, $validated) {
                $planningDay = TPlanningDay::create([
                    'tanggal' => $validated['tanggal'],
                    'tipe' => $validated['tipe'],
                    'mesin_id' => $validated['tipe'] === 'inject' ? $validated['mesin_id'] : null,
                    'meja' => $validated['tipe'] === 'assy' ? $validated['meja'] : null,
                    'status' => $request->input('status'),
                    'catatan' => $request->input('catatan'),
                ]);

                $this->syncRunsFromRequest($planningDay, $request);

                return response()->json([
                    'success' => true,
                    'message' => 'Planning berhasil disimpan',
                    'planning_day_id' => $planningDay->id,
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error in PlanningController@store', [
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
        } catch (\Exception $e) {
            \Log::error('Error storing planning day: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan planning: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(TPlanningDay $planningDay)
    {
        if (!userCan('planning.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $planningDay->load([
            'mesin',
            'runs.mold.part',
            'runs.part.box',
            'runs.part.polybag',
            'runs.box',
            'runs.polybag',
            'runs.hourlyTargets',
            'runs.hourlyActuals',
            'runs.kebutuhan',
            'runs.materials.shifts',
            'runs.subparts.partsubpart.subpart',
            'runs.subparts.shifts',
        ]);

        $mesins = MMesin::orderBy('no_mesin')->get();
        $molds = MMold::with(['perusahaan', 'part'])->orderBy('kode_mold')->get();
        $materials = MBahanBaku::whereIn('kategori', ['material', 'masterbatch'])->orderBy('nama_bahan_baku')->get();
        
        // Box: kategori box
        $boxes = MBahanBaku::where('kategori', 'box')->orderBy('nama_bahan_baku')->get();
        
        // Polybag: LDPE (case insensitive)
        $polybags = MBahanBaku::whereRaw('LOWER(TRIM(kategori)) = ?', ['ldpe'])->orderBy('nama_bahan_baku')->get();
        
        $parts = \App\Models\SMPart::orderBy('nomor_part')->get();
        
        // Ambil daftar meja yang sudah pernah digunakan (unique) untuk dropdown
        // Kolom meja ada di T_Supply, bukan T_Planning_Day
        $mejas = \App\Models\TSupply::whereNotNull('meja')
            ->where('meja', '!=', '')
            ->distinct()
            ->orderBy('meja')
            ->pluck('meja')
            ->toArray();
        
        // Jika belum ada data meja, berikan opsi default
        if (empty($mejas)) {
            $mejas = ['MEJA-1', 'MEJA-2', 'MEJA-3', 'MEJA-4', 'MEJA-5'];
        }
        
        $hourSlots = $this->hourSlots();

        return view('planning.planning', compact('planningDay', 'mesins', 'molds', 'materials', 'boxes', 'polybags', 'parts', 'hourSlots', 'mejas'));
    }

    public function update(Request $request, TPlanningDay $planningDay)
    {
        if (!userCan('planning.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validated = $request->validate([
                'tanggal' => 'required|date',
                'tipe' => 'required|in:inject,assy',
                'mesin_id' => 'required_if:tipe,inject|nullable|exists:M_Mesin,id',
                'meja' => 'required_if:tipe,assy|nullable|string|max:50',
            ]);

            \Log::info('Planning Update Request', [
                'planning_day_id' => $planningDay->id,
                'validated' => $validated,
                'runs_count' => count($request->input('runs', [])),
                'all_input' => $request->all(),
            ]);

            return DB::transaction(function () use ($request, $planningDay, $validated) {
            $planningDay->update([
                'tanggal' => $validated['tanggal'],
                'tipe' => $validated['tipe'],
                'mesin_id' => $validated['tipe'] === 'inject' ? $validated['mesin_id'] : null,
                'meja' => $validated['tipe'] === 'assy' ? $validated['meja'] : null,
                'status' => $request->input('status'),
                'catatan' => $request->input('catatan'),
            ]);

            // Simplest: reset all runs (max 3) lalu create ulang
            $planningDay->runs()->delete();
            $this->syncRunsFromRequest($planningDay, $request);

            return response()->json([
                'success' => true,
                'message' => 'Planning berhasil diupdate',
                'planning_day_id' => $planningDay->id,
            ]);
        });
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Planning Update Validation Error', [
                'planning_day_id' => $planningDay->id,
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
        } catch (\Exception $e) {
            \Log::error('Planning Update Error', [
                'planning_day_id' => $planningDay->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detail(TPlanningDay $planningDay)
    {
        if (!userCan('planning.view')) {
            abort(403, 'Unauthorized action.');
        }

        $planningDay->load([
            'mesin',
            'runs.mold.part',
            'runs.part',
            'runs.box',
            'runs.polybag',
            'runs.hourlyTargets',
            'runs.hourlyActuals',
            'runs.kebutuhan',
            'runs.materials.material',
            'runs.subparts.partsubpart.subpart',
        ]);

        return view('planning.detail', compact('planningDay'));
    }

    public function destroy(TPlanningDay $planningDay)
    {
        if (!userCan('planning.delete')) {
            abort(403, 'Unauthorized action.');
        }

        return DB::transaction(function () use ($planningDay) {
            $planningDay->delete();

            return response()->json([
                'success' => true,
                'message' => 'Planning berhasil dihapus',
            ]);
        });
    }

    public function moldSubparts(MMold $mold, Request $request): JsonResponse
    {
        $mold->load('part');
        if (! $mold->part) {
            return response()->json(['data' => []]);
        }

        $tipe = $request->input('tipe', 'inject'); // Default inject untuk backward compatibility

        $rows = SMPartSubpart::with('subpart')
            ->where('part_id', $mold->part_id)
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

        return response()->json(['data' => $rows]);
    }

    /**
     * API untuk mendapatkan parts berdasarkan tipe planning (INJECT/ASSY)
     * Filter berdasarkan material/masterbatch/subpart yang memiliki tipe sesuai
     */
    public function getPartsByTipe(Request $request): JsonResponse
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
        
        $parts = \App\Models\SMPart::where(function($q) use ($tipe) {
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
     * API untuk mendapatkan subparts dari part berdasarkan tipe (untuk ASSY planning)
     */
    public function getPartSubparts(Request $request): JsonResponse
    {
        $partId = $request->input('part_id');
        $tipe = $request->input('tipe', 'assy'); // Default assy untuk ASSY planning
        
        if (!$partId) {
            return response()->json([
                'success' => false,
                'message' => 'Part ID harus diisi',
            ], 400);
        }
        
        $part = \App\Models\SMPart::find($partId);
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
        ]);
    }

    public function moldPartData(MMold $mold, Request $request): JsonResponse
    {
        $mold->load([
            'part.material1',
            'part.material2',
            'part.masterbatch',
        ]);

        if (! $mold->part) {
            return response()->json(['data' => null]);
        }

        $part = $mold->part;
        $materials = [];
        
        // Material 1
        if ($part->material1) {
            $materials[] = [
                'id' => $part->material1->id,
                'nama' => $part->material1->nama_bahan_baku,
                'nomor' => $part->material1->nomor_bahan_baku,
                'uom' => $part->material1->uom,
                'kategori' => $part->material1->kategori,
            ];
        }

        // Material 2 (opsional)
        if ($part->material2) {
            $materials[] = [
                'id' => $part->material2->id,
                'nama' => $part->material2->nama_bahan_baku,
                'nomor' => $part->material2->nomor_bahan_baku,
                'uom' => $part->material2->uom,
                'kategori' => $part->material2->kategori,
            ];
        }

        // Masterbatch (opsional)
        if ($part->masterbatch) {
            $materials[] = [
                'id' => $part->masterbatch->id,
                'nama' => $part->masterbatch->nama_bahan_baku,
                'nomor' => $part->masterbatch->nomor_bahan_baku,
                'uom' => $part->masterbatch->uom,
                'kategori' => $part->masterbatch->kategori,
            ];
        }

        // Load subparts - filter by tipe (inject/assy/both)
        $tipe = $request->input('tipe', 'inject'); // Default inject untuk backward compatibility
        $subparts = SMPartSubpart::with('subpart')
            ->where('part_id', $part->id)
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

        // Load box and polybag info from part
        $box = null;
        $polybag = null;
        $part->load(['box', 'polybag']);
        if ($part->box) {
            $box = [
                'id' => $part->box->id,
                'nama' => $part->box->nama_bahan_baku,
                'kode' => $part->box->kode_bahan_baku ?? $part->box->nomor_bahan_baku,
                'std_using' => (string) $part->std_using_box,
            ];
        }
        if ($part->polybag) {
            $polybag = [
                'id' => $part->polybag->id,
                'nama' => $part->polybag->nama_bahan_baku,
                'std_using' => (string) $part->Std_Using_Polybag,
            ];
        }

        return response()->json([
            'data' => [
                'materials' => $materials,
                'subparts' => $subparts,
                'box' => $box,
                'polybag' => $polybag,
                'part' => [
                    'id' => $part->id,
                    'nomor_part' => $part->nomor_part,
                    'nama_part' => $part->nama_part,
                ],
            ],
        ]);
    }

    private function hourSlots(): array
    {
        // 24 jam: 07-08 ... 06-07
        $slots = [];
        $start = Carbon::createFromTime(7, 0, 0);
        for ($i = 0; $i < 24; $i++) {
            $s = $start->copy()->addHours($i);
            $e = $s->copy()->addHour();
            $slots[] = [
                'label' => $s->format('H:i') . '-' . $e->format('H:i'),
                'start' => $s->format('H:i'),
                'end' => $e->format('H:i'),
                'index' => $i,
            ];
        }
        return $slots;
    }

    private function syncRunsFromRequest(TPlanningDay $planningDay, Request $request): void
    {
        $runs = $request->input('runs', []);

        // Untuk INJECT: max 3 run/hari, untuk ASSY: hanya 1 run
        $maxRuns = $planningDay->tipe === 'assy' ? 1 : 3;
        $runs = array_slice($runs, 0, $maxRuns);

        foreach ($runs as $runIndex => $run) {
            // Untuk INJECT: butuh mold_id, untuk ASSY: butuh part_id
            if ($planningDay->tipe === 'inject' && empty($run['mold_id'])) {
                continue;
            }
            if ($planningDay->tipe === 'assy' && empty($run['part_id'])) {
                continue;
            }

            // Hitung total dari semua hourly targets sebelum create TPlanningRun
            $slots = $this->hourSlots();
            $qtyTargetTotal = 0;
            foreach ($slots as $slot) {
                $qtyTarget = (int) data_get($run, "hourly_target.{$slot['index']}", 0);
                $qtyTargetTotal += $qtyTarget;
            }

            // Generate lot_produksi jika tidak ada (hanya untuk INJECT)
            $lotProduksi = $run['lot_produksi'] ?? null;
            if ($planningDay->tipe === 'inject' && (empty($lotProduksi) || trim($lotProduksi) === '')) {
                // Generate lot_produksi: noplanning-nomesin-tanggal-bulan-tahun
                $mesin = $planningDay->mesin;
                $noMesin = $mesin->no_mesin ?? '';
                preg_match('/(\d+)$/', $noMesin, $matches);
                $noMesinNum = isset($matches[1]) ? $matches[1] : '00';
                $noMesinFormatted = str_pad((int)$noMesinNum, 2, '0', STR_PAD_LEFT);
                
                $tanggal = Carbon::parse($planningDay->tanggal);
                $noPlanning = (string)($runIndex + 1);
                $tanggalStr = $tanggal->format('d');
                $bulanStr = $tanggal->format('m');
                $tahunStr = $tanggal->format('y');
                
                $lotProduksi = $noPlanning . $noMesinFormatted . '-' . $tanggalStr . '-' . $bulanStr . '-' . $tahunStr;
            }
            // Untuk ASSY: lot_produksi harus diinput manual (tidak auto-generate)

            $planningRun = TPlanningRun::create([
                'planning_day_id' => $planningDay->id,
                'urutan_run' => $planningDay->tipe === 'assy' ? 1 : ($runIndex + 1), // ASSY selalu run 1
                'mold_id' => $planningDay->tipe === 'inject' ? $run['mold_id'] : null,
                'part_id' => $planningDay->tipe === 'assy' ? $run['part_id'] : null,
                'lot_produksi' => $lotProduksi,
                'box_id' => $run['box_id'] ?? null,
                'qty_box' => isset($run['qty_box']) && $run['qty_box'] !== '' ? (float) $run['qty_box'] : null,
                'polybag_id' => $run['polybag_id'] ?? null,
                'qty_polybag' => isset($run['qty_polybag']) && $run['qty_polybag'] !== '' ? (float) $run['qty_polybag'] : null,
                'start_at' => $run['start_at'] ?? ($planningDay->tanggal . ' 07:00:00'),
                'end_at' => $run['end_at'] ?? Carbon::parse($planningDay->tanggal)->addDay()->format('Y-m-d') . ' 07:00:00',
                'qty_target_total' => $qtyTargetTotal,
                'catatan' => $run['catatan'] ?? null,
            ]);

            // Kebutuhan (optional)
            if (!empty($run['kebutuhan'])) {
                TPlanningRunKebutuhan::create(array_merge(
                    ['planning_run_id' => $planningRun->id],
                    [
                        'qty_polybox' => (int) ($run['kebutuhan']['qty_polybox'] ?? 0),
                        'qty_partisi' => (int) ($run['kebutuhan']['qty_partisi'] ?? 0),
                        'qty_imfrabolt' => (int) ($run['kebutuhan']['qty_imfrabolt'] ?? 0),
                        'qty_karton' => (int) ($run['kebutuhan']['qty_karton'] ?? 0),
                        'qty_troly' => (int) ($run['kebutuhan']['qty_troly'] ?? 0),
                    ]
                ));
            }

            // Materials hanya untuk INJECT
            if ($planningDay->tipe === 'inject' && !empty($run['materials'])) {
                foreach ($run['materials'] as $mat) {
                    if (empty($mat['material_id'])) continue;
                    TPlanningRunMaterial::create([
                        'planning_run_id' => $planningRun->id,
                        'material_id' => $mat['material_id'],
                        'qty_total' => (float) ($mat['qty_total'] ?? 0),
                        'uom' => $mat['uom'] ?? null,
                    ]);
                }
            }

            // Subparts untuk INJECT dan ASSY (opsional)
            if (!empty($run['subparts'])) {
                foreach ($run['subparts'] as $sub) {
                    if (empty($sub['partsubpart_id'])) continue;
                    TPlanningRunSubpart::create([
                        'planning_run_id' => $planningRun->id,
                        'partsubpart_id' => $sub['partsubpart_id'],
                        'qty_total' => (float) ($sub['qty_total'] ?? 0),
                        'uom' => $sub['uom'] ?? null,
                    ]);
                }
            }

            // Hourly target & actual (24 baris manual) - hanya untuk INJECT
            if ($planningDay->tipe === 'inject') {
                $tanggal = Carbon::parse($planningDay->tanggal)->format('Y-m-d');
                $tanggalBesok = Carbon::parse($planningDay->tanggal)->addDay()->format('Y-m-d');

                foreach ($slots as $slot) {
                $start = $slot['start'] . ':00';
                $end = $slot['end'] . ':00';

                // Jam 00:00-07:00 dianggap besok
                $hourStartDate = ($slot['start'] < '07:00') ? $tanggalBesok : $tanggal;
                $hourEndDate = ($slot['end'] <= '07:00') ? $tanggalBesok : $tanggal;

                $hourStart = $hourStartDate . ' ' . $start;
                $hourEnd = $hourEndDate . ' ' . $end;

                $qtyTarget = (int) data_get($run, "hourly_target.{$slot['index']}", 0);
                $qtyActual = (int) data_get($run, "hourly_actual.{$slot['index']}", 0);

                TPlanningRunHourlyTarget::create([
                    'planning_run_id' => $planningRun->id,
                    'hour_start' => $hourStart,
                    'hour_end' => $hourEnd,
                    'qty_target' => $qtyTarget,
                ]);

                TPlanningRunHourlyActual::create([
                    'planning_run_id' => $planningRun->id,
                    'hour_start' => $hourStart,
                    'hour_end' => $hourEnd,
                    'qty_actual' => $qtyActual,
                ]);
                }
            }
        }
    }

    public function matriks(Request $request)
    {
        // Get tanggal from request, default to today
        $tanggalInput = $request->input('tanggal');
        
        if ($tanggalInput) {
            try {
                // Parse tanggal input dan pastikan format Y-m-d
                $tanggal = Carbon::parse($tanggalInput)->format('Y-m-d');
            } catch (\Exception $e) {
                $tanggal = Carbon::today()->format('Y-m-d');
            }
        } else {
            $tanggal = Carbon::today()->format('Y-m-d');
        }
        
        // Ambil semua planning day untuk tanggal tertentu
        $planningDays = TPlanningDay::with([
            'mesin',
            'runs.mold.part',
            'runs.hourlyTargets',
            'runs.hourlyActuals'
        ])
        ->whereDate('tanggal', $tanggal)
        ->orderBy('mesin_id')
        ->get();

        // Debug: Log untuk troubleshooting
        \Log::info('Matriks Planning Query', [
            'tanggal_filter' => $tanggal,
            'planning_days_count' => $planningDays->count(),
            'planning_day_ids' => $planningDays->pluck('id')->toArray(),
            'total_runs' => $planningDays->sum(fn($day) => $day->runs->count())
        ]);

        // Define shift hours sesuai gambar
        $shift1Hours = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00'];
        $shift2Hours = ['17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
        $shift3Hours = ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00'];
        $allHours = array_merge($shift1Hours, $shift2Hours, $shift3Hours);

        // Helper untuk map hourly data
        $mapHourly = function($hourlyData) {
            $map = [];
            foreach ($hourlyData as $h) {
                $hour = Carbon::parse($h->hour_start)->format('H:i');
                $map[$hour] = $h;
            }
            return $map;
        };

        // Process data untuk matriks - pisahkan INJECT dan ASSY
        $matriksDataInject = [];
        $matriksDataAssy = [];
        $noUrutInject = 1;
        $noUrutAssy = 1;
        
        foreach ($planningDays as $day) {
            foreach ($day->runs as $run) {
                // Untuk INJECT: ambil dari hourly targets/actuals
                // Untuk ASSY: tidak ada hourly, gunakan qty_target_total dan qty_actual_total
                $targetMap = $day->tipe === 'inject' ? $mapHourly($run->hourlyTargets) : [];
                $actualMap = $day->tipe === 'inject' ? $mapHourly($run->hourlyActuals) : [];
                
                // Hitung target per shift
                $targetShift1 = 0;
                $targetShift2 = 0;
                $targetShift3 = 0;
                
                if ($day->tipe === 'inject') {
                    foreach ($shift1Hours as $hour) {
                        $targetShift1 += isset($targetMap[$hour]) ? (int)$targetMap[$hour]->qty_target : 0;
                    }
                    foreach ($shift2Hours as $hour) {
                        $targetShift2 += isset($targetMap[$hour]) ? (int)$targetMap[$hour]->qty_target : 0;
                    }
                    foreach ($shift3Hours as $hour) {
                        $targetShift3 += isset($targetMap[$hour]) ? (int)$targetMap[$hour]->qty_target : 0;
                    }
                } else {
                    // ASSY: bagi qty_target_total ke 3 shift (sederhana, bisa disesuaikan)
                    $targetShift1 = (int)($run->qty_target_total / 3);
                    $targetShift2 = (int)($run->qty_target_total / 3);
                    $targetShift3 = (int)($run->qty_target_total / 3);
                }
                
                $totalTarget = $targetShift1 + $targetShift2 + $targetShift3;
                
                // Jika untuk INJECT tidak ada hourly targets (totalTarget = 0), gunakan qty_target_total sebagai fallback
                if ($day->tipe === 'inject' && $totalTarget == 0 && $run->qty_target_total > 0) {
                    $totalTarget = $run->qty_target_total;
                    // Bagi rata ke 3 shift jika tidak ada hourly data
                    $targetShift1 = (int)($run->qty_target_total / 3);
                    $targetShift2 = (int)($run->qty_target_total / 3);
                    $targetShift3 = (int)($run->qty_target_total / 3);
                    // Pastikan total sama dengan qty_target_total
                    $totalTarget = $targetShift1 + $targetShift2 + $targetShift3;
                    if ($totalTarget < $run->qty_target_total) {
                        $targetShift1 += ($run->qty_target_total - $totalTarget);
                        $totalTarget = $run->qty_target_total;
                    }
                }
                
                $totalActual = $run->qty_actual_total ?? 0;
                $transaksi = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 1) : 0;
                
                // Get part info berdasarkan tipe
                $part = null;
                if ($day->tipe === 'inject' && $run->mold) {
                    $part = $run->mold->part;
                } elseif ($day->tipe === 'assy' && $run->part) {
                    $part = $run->part;
                }

                $rowData = [
                    'no_urut' => $day->tipe === 'inject' ? $noUrutInject++ : $noUrutAssy++,
                    'tanggal' => $day->tanggal,
                    'tipe' => $day->tipe,
                    'mesin' => $day->tipe === 'inject' ? $day->mesin : $day->meja,
                    'lot_produksi' => $run->lot_produksi ?? '-',
                    'kode_part' => $part->nomor_part ?? '-',
                    'nomor_barang' => $part->nomor_part ?? '-',
                    'nama_barang' => $part->nama_part ?? '-',
                    'qty_planning' => $totalTarget,
                    'target_shift1' => $targetShift1,
                    'target_shift2' => $targetShift2,
                    'target_shift3' => $targetShift3,
                    'total_target' => $totalTarget,
                    'total_actual' => $totalActual,
                    'transaksi' => $transaksi,
                    'hourly_targets' => $day->tipe === 'inject' ? array_map(function($hour) use ($targetMap) {
                        return isset($targetMap[$hour]) ? (int)$targetMap[$hour]->qty_target : 0;
                    }, $allHours) : array_fill(0, 24, 0),
                    'hourly_actuals' => $day->tipe === 'inject' ? array_map(function($hour) use ($actualMap) {
                        return isset($actualMap[$hour]) ? (int)$actualMap[$hour]->qty_actual : 0;
                    }, $allHours) : array_fill(0, 24, 0),
                ];

                // Pisahkan berdasarkan tipe
                if ($day->tipe === 'inject') {
                    $matriksDataInject[] = $rowData;
                } else {
                    $matriksDataAssy[] = $rowData;
                }
            }
        }

        // Debug: Log untuk memastikan data ada
        \Log::info('Matriks Data Count - INJECT: ' . count($matriksDataInject) . ', ASSY: ' . count($matriksDataAssy));
        \Log::info('Planning Days Count: ' . $planningDays->count());
        \Log::info('Tanggal Filter: ' . $tanggal);

        return view('planning.matriks', compact('matriksDataInject', 'matriksDataAssy', 'tanggal', 'shift1Hours', 'shift2Hours', 'shift3Hours', 'allHours'));
    }
}
