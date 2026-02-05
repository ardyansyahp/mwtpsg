<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\MMesin;
use App\Models\TInjectIn;
use App\Models\TInjectOut;
use App\Models\TInjectOutDetail;
use App\Models\TSupplyDetail;
use App\Models\TWipIn;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InjectController extends Controller
{
    public function index()
    {
        $injectIns = TInjectIn::with([
            'mesin', 
            'planningRun.mold.part', 
            'planningRun.hourlyTargets' => function($query) {
                $query->orderBy('hour_start', 'asc');
            },
            'supplyDetail.supply'
        ])
            ->orderBy('waktu_scan', 'desc')
            ->paginate(15);

        return view('produksi.inject.inject', compact('injectIns'));
    }

    public function create()
    {
        if (!userCan('produksi.inject.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('produksi.inject.createin');
    }

    public function store(Request $request): JsonResponse
    {
        if (!userCan('produksi.inject.create')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'supply_detail_id' => 'nullable|exists:T_Supply_Detail,id',
            'planning_run_id' => 'nullable|exists:T_Planning_Run,id',
            'mesin_id' => 'required|exists:M_Mesin,id',
            'manpower' => 'nullable|string|max:100',
            'waktu_scan' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        try {
            $injectIn = null;
            DB::transaction(function () use ($validated, &$injectIn) {
                // Jika waktu_scan tidak diisi, gunakan waktu sekarang dengan timezone Asia/Jakarta
                if (empty($validated['waktu_scan'])) {
                    $validated['waktu_scan'] = now('Asia/Jakarta');
                } else {
                    // Pastikan waktu_scan menggunakan timezone Asia/Jakarta
                    $validated['waktu_scan'] = Carbon::parse($validated['waktu_scan'])->setTimezone('Asia/Jakarta');
                }

                // Generate lot number
                $lotNumber = $this->generateLotNumber($validated);
                $validated['lot_number'] = $lotNumber;

                $injectIn = TInjectIn::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data scan in berhasil disimpan',
                'inject_in_id' => $injectIn->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(TInjectIn $injectIn)
    {
        if (!userCan('produksi.inject.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $mesins = MMesin::orderBy('no_mesin')->get();

        return view('produksi.inject.editin', compact('injectIn', 'mesins'));
    }

    public function update(Request $request, TInjectIn $injectIn): JsonResponse
    {
        if (!userCan('produksi.inject.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'mesin_id' => 'required|exists:M_Mesin,id',
            'manpower' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        try {
            $injectIn->update($validated);

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

    public function delete(TInjectIn $injectIn)
    {
        if (!userCan('produksi.inject.delete')) {
            abort(403, 'Unauthorized action.');
        }

        return view('produksi.inject.deletein', compact('injectIn'));
    }

    public function destroy(TInjectIn $injectIn): JsonResponse
    {
        if (!userCan('produksi.inject.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $injectIn->delete();

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

    /**
     * API untuk mencari supply detail berdasarkan lot number
     */
    public function getSupplyDetailByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            // Cari supply detail berdasarkan lot_number
            $supplyDetail = TSupplyDetail::with([
                'supply.planningRun.day.mesin',
                'supply.planningRun.mold.part',
                'supply.planningRun.hourlyTargets' => function($query) {
                    $query->orderBy('hour_start', 'asc');
                },
            ])
            ->where('lot_number', $lotNumber)
            ->first();

            if (! $supplyDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supply dengan lot number tersebut tidak ditemukan',
                ], 404);
            }

            // Validasi: supply harus untuk inject
            if ($supplyDetail->supply->tujuan !== 'inject') {
                return response()->json([
                    'success' => false,
                    'message' => 'Supply ini bukan untuk inject. Tujuan: ' . strtoupper($supplyDetail->supply->tujuan),
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
                        'planning_run_id' => $supplyDetail->supply->planning_run_id,
                        'planning_run' => $supplyDetail->supply->planningRun ? [
                            'id' => $supplyDetail->supply->planningRun->id,
                            'qty_target_total' => $supplyDetail->supply->planningRun->qty_target_total,
                            'qty_actual_total' => $supplyDetail->supply->planningRun->qty_actual_total,
                            'start_at' => $supplyDetail->supply->planningRun->start_at?->format('Y-m-d H:i:s'),
                            'end_at' => $supplyDetail->supply->planningRun->end_at?->format('Y-m-d H:i:s'),
                            'day' => $supplyDetail->supply->planningRun->day ? [
                                'mesin_id' => $supplyDetail->supply->planningRun->day->mesin_id,
                                'mesin' => $supplyDetail->supply->planningRun->day->mesin ? [
                                    'id' => $supplyDetail->supply->planningRun->day->mesin->id,
                                    'no_mesin' => $supplyDetail->supply->planningRun->day->mesin->no_mesin,
                                ] : null,
                            ] : null,
                            'mold' => $supplyDetail->supply->planningRun->mold ? [
                                'part' => $supplyDetail->supply->planningRun->mold->part ? [
                                    'nomor_part' => $supplyDetail->supply->planningRun->mold->part->nomor_part,
                                    'nama_part' => $supplyDetail->supply->planningRun->mold->part->nama_part,
                                ] : null,
                            ] : null,
                            'hourly_targets' => $supplyDetail->supply->planningRun->hourlyTargets->map(function($target) {
                                return [
                                    'id' => $target->id,
                                    'hour_start' => $target->hour_start?->format('Y-m-d H:i:s'),
                                    'hour_end' => $target->hour_end?->format('Y-m-d H:i:s'),
                                    'hour_start_time' => $target->hour_start?->format('H:i'),
                                    'hour_end_time' => $target->hour_end?->format('H:i'),
                                    'qty_target' => $target->qty_target,
                                ];
                            }),
                        ] : null,
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
     * API untuk mencari mesin berdasarkan QR code (no_mesin)
     * Format QR bisa: "MES-MC16", "MES-MC-16", "MC-16", "MC16", atau langsung no_mesin/qrcode
     */
    public function getMesinByQR(string $qrCode): JsonResponse
    {
        try {
            $qrCodeTrimmed = trim($qrCode);
            
            \Log::info('Mencari mesin dengan QR code:', ['qr_code' => $qrCodeTrimmed]);
            
            // Cari langsung berdasarkan qrcode field jika ada (format: MES-MC16)
            $mesin = MMesin::where('qrcode', $qrCodeTrimmed)->first();
            
            if (!$mesin) {
                // Normalize QR code - hapus prefix "MES-" jika ada
                $normalizedQR = preg_replace('/^MES-?/i', '', $qrCodeTrimmed);
                \Log::info('Normalized QR:', ['normalized' => $normalizedQR]);
                
                // Cari mesin dengan berbagai kemungkinan format
                // Query builder dengan where closure untuk OR conditions
                $mesin = MMesin::where(function($query) use ($qrCodeTrimmed, $normalizedQR) {
                    $query->where('no_mesin', $qrCodeTrimmed)
                        ->orWhere('no_mesin', $normalizedQR)
                        ->orWhere('no_mesin', str_replace('-', '', $normalizedQR)) // MC16 tanpa dash
                        ->orWhere('no_mesin', preg_replace('/([A-Z]+)(\d+)/', '$1-$2', $normalizedQR)) // MC16 -> MC-16
                        ->orWhere('no_mesin', preg_replace('/([A-Z]+)-?(\d+)/', '$1-$2', $normalizedQR)) // MC16 atau MC-16 -> MC-16
                        ->orWhere('id', $qrCodeTrimmed);
                    
                    // Jika normalizedQR masih punya dash, coba variasi tanpa dash
                    if (strpos($normalizedQR, '-') !== false) {
                        $noDash = str_replace('-', '', $normalizedQR);
                        $query->orWhere('no_mesin', $noDash);
                        // Coba format dengan dash di posisi yang benar (MC-16)
                        if (preg_match('/^([A-Z]+)(\d+)$/i', $noDash, $matches)) {
                            $query->orWhere('no_mesin', $matches[1] . '-' . $matches[2]);
                        }
                    }
                })->first();
            }

            if (! $mesin) {
                \Log::warning('Mesin tidak ditemukan:', ['qr_code' => $qrCodeTrimmed]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mesin dengan QR code "' . $qrCode . '" tidak ditemukan. Pastikan QR code benar atau mesin sudah terdaftar.',
                ], 404);
            }

            \Log::info('Mesin ditemukan:', ['mesin_id' => $mesin->id, 'no_mesin' => $mesin->no_mesin, 'qrcode' => $mesin->qrcode]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $mesin->id,
                    'no_mesin' => $mesin->no_mesin,
                    'tonase' => $mesin->tonase,
                    'qrcode' => $mesin->qrcode ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error mencari mesin:', ['error' => $e->getMessage(), 'qr_code' => $qrCode]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari operator berdasarkan QR code
     * Format QR bisa: qrcode, id, mp_id, atau nik
     */
    public function getOperatorByQR(string $qrCode): JsonResponse
    {
        try {
            $qrCodeTrimmed = trim($qrCode);
            
            \Log::info('Mencari operator dengan QR code:', ['qr_code' => $qrCodeTrimmed]);
            
            // Cari operator berdasarkan berbagai kemungkinan
            $operator = MManpower::where('qrcode', $qrCodeTrimmed)
                ->orWhere('id', $qrCodeTrimmed)
                ->orWhere('mp_id', $qrCodeTrimmed)
                ->orWhere('nik', $qrCodeTrimmed)
                ->first();

            if (! $operator) {
                \Log::warning('Operator tidak ditemukan:', ['qr_code' => $qrCodeTrimmed]);
                return response()->json([
                    'success' => false,
                    'message' => 'Operator dengan QR code "' . $qrCode . '" tidak ditemukan. Pastikan QR code benar atau operator sudah terdaftar.',
                ], 404);
            }

            \Log::info('Operator ditemukan:', ['operator_id' => $operator->id, 'nama' => $operator->nama]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $operator->id,
                    'mp_id' => $operator->mp_id,
                    'nik' => $operator->nik,
                    'nama' => $operator->nama,
                    'departemen' => $operator->departemen,
                    'bagian' => $operator->bagian,
                    'qrcode' => $operator->qrcode ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error mencari operator:', ['error' => $e->getMessage(), 'qr_code' => $qrCode]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate lot number dengan format: PART_NUMBER|1200058|QTY_PACKING|PLANNING-MESIN-DATE-SHIFT
     * Contoh: 64521-K1Y-DC00|1200058|10|105-29-9-25-1
     */
    private function generateLotNumber(array $data): string
    {
        $mesin = MMesin::find($data['mesin_id']);
        $waktuScan = Carbon::parse($data['waktu_scan'])->setTimezone('Asia/Jakarta');
        
        // 1. Nomor part (dari planning run -> mold -> part)
        $nomorPart = '-';
        $qtyPacking = 0;
        $planningRun = null;
        if (!empty($data['planning_run_id'])) {
            $planningRun = \App\Models\TPlanningRun::with('mold.part')->find($data['planning_run_id']);
            if ($planningRun && $planningRun->mold && $planningRun->mold->part) {
                $nomorPart = $planningRun->mold->part->nomor_part;
                $qtyPacking = $planningRun->mold->part->QTY_Packing_Box ?? 0;
            }
        }
        
        // 2. Identitas perusahaan (statis)
        $identitasPerusahaan = '1200058';
        
        // 3. QTY Packing Box
        $qtyPackingStr = (string) $qtyPacking;
        
        // 4. Format: 105-29-9-25-1
        // - 1 = nomor planning (statis)
        $nomorPlanning = '1';
        
        // - 05 = nomor mesin (extract angka dari no_mesin, format 2 digit)
        $noMesin = $mesin->no_mesin ?? '';
        // Extract angka dari no_mesin (contoh: INJ-001 -> 001 -> 01, INJ-005 -> 005 -> 05)
        preg_match('/(\d+)$/', $noMesin, $matches);
        $nomorMesin = isset($matches[1]) ? str_pad((int)$matches[1], 2, '0', STR_PAD_LEFT) : '00';
        
        // Gabungkan planning (1) + mesin (05) = 105 (3 digit)
        $planningMesin = $nomorPlanning . $nomorMesin; // 105
        
        // - 29-9-25 = tanggal (format d-m-y)
        $tanggalStr = $waktuScan->format('j-n-y'); // j = day tanpa leading zero, n = month tanpa leading zero, y = 2 digit year
        
        // - 1 = shift (dari planning run start_at, bukan waktu_scan)
        // Shift: 07:00-15:00 = shift 1, 15:00-23:00 = shift 2, 23:00-07:00 = shift 3
        $shift = 1; // default
        if ($planningRun && $planningRun->start_at) {
            $startAt = Carbon::parse($planningRun->start_at);
            $jam = (int) $startAt->format('H');
            if ($jam >= 7 && $jam < 15) {
                $shift = 1; // 07:00 - 14:59 (7 pagi - 3 sore)
            } elseif ($jam >= 15 && $jam < 23) {
                $shift = 2; // 15:00 - 22:59 (3 sore - 11 malam)
            } else {
                $shift = 3; // 23:00 - 06:59 (11 malam - 7 pagi)
            }
        }
        
        // Format: PART_NUMBER|IDENTITAS|QTY|PLANNINGMESIN-DATE-SHIFT
        // Contoh: 64521-K1Y-DC00|1200058|10|105-29-9-25-1
        $lotNumber = sprintf(
            '%s|%s|%s|%s-%s-%s',
            $nomorPart,
            $identitasPerusahaan,
            $qtyPackingStr,
            $planningMesin,
            $tanggalStr,
            $shift
        );
        
        return $lotNumber;
    }

    /**
     * Menampilkan label untuk inject in
     */
    public function label(TInjectIn $injectIn)
    {
        $injectIn->load([
            'mesin',
            'planningRun.mold.part.tipe',
            'planningRun.day',
        ]);

        return view('produksi.inject.label', compact('injectIn'));
    }

    // ========== INJECT OUT METHODS ==========

    public function indexOut()
    {
        $injectOuts = TInjectOut::with([
            'injectIn.mesin',
            'planningRun.mold.part',
            'details' => function($query) {
                $query->orderBy('box_number', 'desc');
            },
        ])
            ->orderBy('waktu_scan', 'desc')
            ->paginate(15);

        return view('produksi.inject.injectout', compact('injectOuts'));
    }

    public function createOut()
    {
        if (!userCan('produksi.inject.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('produksi.inject.createout');
    }

    public function storeOut(Request $request): JsonResponse
    {
        if (!userCan('produksi.inject.create')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'lot_number' => 'required|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        try {
            $injectOut = null;
            $isNewRecord = false;
            DB::transaction(function () use ($validated, &$injectOut, &$isNewRecord) {
                // Cari inject in berdasarkan lot number
                $injectIn = TInjectIn::where('lot_number', $validated['lot_number'])->first();
                
                if (!$injectIn) {
                    throw new \Exception('Label dengan lot number tersebut tidak ditemukan');
                }

                // Ambil planning run dan part untuk hitung target box
                $planningRun = $injectIn->planningRun;
                if (!$planningRun) {
                    throw new \Exception('Planning run tidak ditemukan untuk label ini');
                }

                $part = $planningRun->mold->part ?? null;
                if (!$part) {
                    throw new \Exception('Part tidak ditemukan untuk planning run ini');
                }

                $qtyPackingBox = $part->QTY_Packing_Box ?? 0;
                if ($qtyPackingBox <= 0) {
                    throw new \Exception('QTY Packing Box belum diatur untuk part ini');
                }

                $targetTotal = $planningRun->qty_target_total ?? 0;
                if ($targetTotal <= 0) {
                    throw new \Exception('Target total belum diatur untuk planning run ini');
                }

                // Hitung jumlah box yang seharusnya
                $targetBoxCount = (int) ceil($targetTotal / $qtyPackingBox);

                // Cek apakah lot number sudah pernah di-scan out
                // Jika sudah ada, gunakan record yang sama dan tambahkan detail box baru
                $existingInjectOut = TInjectOut::where('lot_number', $validated['lot_number'])->first();

                // Waktu scan
                $waktuScan = now('Asia/Jakarta');

                if ($existingInjectOut) {
                    // Gunakan record yang sudah ada
                    $injectOut = $existingInjectOut;
                    
                    // Update waktu scan terakhir
                    $injectOut->update([
                        'waktu_scan' => $waktuScan,
                        'catatan' => $validated['catatan'] ?? $injectOut->catatan,
                    ]);
                    
                    $isNewRecord = false;
                } else {
                    // Buat record baru
                    $injectOut = TInjectOut::create([
                        'inject_in_id' => $injectIn->id,
                        'lot_number' => $validated['lot_number'],
                        'planning_run_id' => $planningRun->id,
                        'waktu_scan' => $waktuScan,
                        'catatan' => $validated['catatan'] ?? null,
                    ]);
                    $isNewRecord = true;
                }

                // Hitung box number berikutnya untuk inject out ini
                $existingBoxCount = $injectOut->details()->count();
                $nextBoxNumber = $existingBoxCount + 1;

                // Cek apakah sudah mencapai target box
                if ($existingBoxCount >= $targetBoxCount) {
                    throw new \Exception("Semua box sudah di-scan. Target: {$targetBoxCount} box, Sudah di-scan: {$existingBoxCount} box");
                }

                // Tambahkan detail box baru
                $injectOut->details()->create([
                    'box_number' => $nextBoxNumber,
                    'waktu_scan' => $waktuScan,
                    'catatan' => $validated['catatan'] ?? null,
                ]);

                // Otomatis buat WIP IN dengan status belum confirmed (jika belum ada)
                $existingWipIn = TWipIn::where('inject_out_id', $injectOut->id)->first();
                if (!$existingWipIn) {
                    // Ambil box number dari detail terakhir
                    $lastDetail = $injectOut->details()->orderBy('box_number', 'desc')->first();
                    $boxNumber = $lastDetail ? $lastDetail->box_number : $nextBoxNumber;

                    // Buat WIP IN dengan is_confirmed = false
                    TWipIn::create([
                        'inject_out_id' => $injectOut->id,
                        'lot_number' => $injectOut->lot_number,
                        'box_number' => $boxNumber,
                        'planning_run_id' => $injectOut->planning_run_id,
                        'waktu_scan_in' => $waktuScan,
                        'is_confirmed' => false,
                        'catatan' => $validated['catatan'] ?? null,
                    ]);
                }
            });

            $message = $isNewRecord 
                ? 'Label berhasil di-scan' 
                : 'Box berhasil ditambahkan ke lot number yang sama';

            return response()->json([
                'success' => true,
                'message' => $message,
                'inject_out_id' => $injectOut->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function editOut(TInjectOut $injectOut)
    {
        if (!userCan('produksi.inject.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $injectOut->load([
            'injectIn', 
            'planningRun.mold.part',
            'details' => function($query) {
                $query->orderBy('box_number', 'desc');
            },
        ]);
        return view('produksi.inject.editout', compact('injectOut'));
    }

    public function updateOut(Request $request, TInjectOut $injectOut): JsonResponse
    {
        if (!userCan('produksi.inject.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        try {
            $injectOut->update($validated);

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

    public function detailOut(TInjectOut $injectOut)
    {
        $injectOut->load([
            'injectIn.mesin', 
            'planningRun.mold.part',
            'details' => function($query) {
                $query->orderBy('box_number', 'asc');
            },
        ]);
        return view('produksi.inject.detailout', compact('injectOut'));
    }

    public function deleteOut(TInjectOut $injectOut)
    {
        if (!userCan('produksi.inject.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $injectOut->load([
            'injectIn', 
            'planningRun.mold.part',
            'details' => function($query) {
                $query->orderBy('box_number', 'desc');
            },
        ]);
        return view('produksi.inject.deleteout', compact('injectOut'));
    }

    public function destroyOut(TInjectOut $injectOut): JsonResponse
    {
        try {
            $injectOut->delete();

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

    /**
     * API untuk mencari inject in berdasarkan lot number
     */
    public function getInjectInByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            $injectIn = TInjectIn::with([
                'planningRun.mold.part',
                'mesin',
            ])
            ->where('lot_number', $lotNumber)
            ->first();

            if (!$injectIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Label dengan lot number tersebut tidak ditemukan',
                ], 404);
            }

            $planningRun = $injectIn->planningRun;
            $part = $planningRun && $planningRun->mold ? $planningRun->mold->part : null;
            
            $qtyPackingBox = $part ? ($part->QTY_Packing_Box ?? 0) : 0;
            $targetTotal = $planningRun ? ($planningRun->qty_target_total ?? 0) : 0;
            $targetBoxCount = $qtyPackingBox > 0 ? (int) ceil($targetTotal / $qtyPackingBox) : 0;

            // Hitung box yang sudah di-scan
            $scannedBoxCount = $planningRun ? TInjectOut::where('planning_run_id', $planningRun->id)->count() : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'inject_in' => [
                        'id' => $injectIn->id,
                        'lot_number' => $injectIn->lot_number,
                        'mesin' => $injectIn->mesin ? [
                            'id' => $injectIn->mesin->id,
                            'no_mesin' => $injectIn->mesin->no_mesin,
                        ] : null,
                    ],
                    'planning_run' => $planningRun ? [
                        'id' => $planningRun->id,
                        'qty_target_total' => $targetTotal,
                    ] : null,
                    'part' => $part ? [
                        'nomor_part' => $part->nomor_part,
                        'nama_part' => $part->nama_part,
                        'qty_packing_box' => $qtyPackingBox,
                    ] : null,
                    'target_box_count' => $targetBoxCount,
                    'scanned_box_count' => $scannedBoxCount,
                    'remaining_box_count' => max(0, $targetBoxCount - $scannedBoxCount),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}

