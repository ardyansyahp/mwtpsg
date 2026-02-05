<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\TInjectOut;
use App\Models\TInjectOutDetail;
use App\Models\TWipIn;
use App\Models\TWipOut;
use App\Models\TWipOutDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WipController extends Controller
{
    // ========== WIP IN METHODS ==========

    public function indexIn()
    {
        // Tampilkan semua data (baik yang sudah confirmed maupun belum)
        $wipIns = TWipIn::with([
            'injectOut.injectIn.mesin',
            'planningRun.mold.part',
        ])
            ->orderBy('waktu_scan_in', 'desc')
            ->paginate(15);

        return view('produksi.wip.wipin', compact('wipIns'));
    }

    public function createIn()
    {
        if (!userCan('produksi.wip.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('produksi.wip.createin');
    }

    /**
     * Confirm WIP IN (mengubah is_confirmed menjadi true)
     */
    public function confirmIn(Request $request, TWipIn $wipIn): JsonResponse
    {
        if (!userCan('produksi.wip.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            // Cek apakah sudah confirmed
            if ($wipIn->is_confirmed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data ini sudah dikonfirmasi sebelumnya',
                ], 400);
            }

            // Update is_confirmed menjadi true
            $wipIn->update([
                'is_confirmed' => true,
                'waktu_scan_in' => $wipIn->waktu_scan_in ?? now('Asia/Jakarta'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dikonfirmasi',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengonfirmasi data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editIn(TWipIn $wipIn)
    {
        if (!userCan('produksi.wip.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $wipIn->load(['injectOut.injectIn', 'planningRun.mold.part']);
        return view('produksi.wip.editin', compact('wipIn'));
    }

    public function updateIn(Request $request, TWipIn $wipIn): JsonResponse
    {
        if (!userCan('produksi.wip.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        try {
            $wipIn->update($validated);

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

    public function deleteIn(TWipIn $wipIn)
    {
        if (!userCan('produksi.wip.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $wipIn->load(['injectOut.injectIn', 'planningRun.mold.part']);
        return view('produksi.wip.deletein', compact('wipIn'));
    }

    public function destroyIn(TWipIn $wipIn): JsonResponse
    {
        if (!userCan('produksi.wip.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            // Hapus wip out yang terkait jika ada
            TWipOut::where('wip_in_id', $wipIn->id)->delete();
            
            $wipIn->delete();

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
     * API untuk mencari inject out berdasarkan lot number (untuk WIP In)
     */
    public function getInjectOutByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            $injectOut = TInjectOut::with([
                'planningRun.mold.part',
                'injectIn.mesin',
                'details' => function($query) {
                    $query->orderBy('box_number', 'desc');
                },
            ])
            ->where('lot_number', $lotNumber)
            ->first();

            if (!$injectOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Label dengan lot number tersebut tidak ditemukan di inject out',
                ], 404);
            }

            // Cek apakah sudah pernah di-scan in
            $alreadyScannedIn = TWipIn::where('inject_out_id', $injectOut->id)->exists();

            $planningRun = $injectOut->planningRun;
            $part = $planningRun && $planningRun->mold ? $planningRun->mold->part : null;

            // Ambil box number dari detail pertama jika ada
            $boxNumber = null;
            $firstDetail = $injectOut->details()->orderBy('box_number', 'desc')->first();
            if ($firstDetail) {
                $boxNumber = $firstDetail->box_number;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'inject_out' => [
                        'id' => $injectOut->id,
                        'lot_number' => $injectOut->lot_number,
                        'box_number' => $boxNumber,
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
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ========== WIP OUT METHODS ==========

    public function indexOut()
    {
        $wipOuts = TWipOut::with([
            'wipIn',
            'injectOut.injectIn.mesin',
            'planningRun.mold.part',
            'details' => function($query) {
                $query->orderBy('box_number', 'desc');
            },
        ])
            ->orderBy('waktu_scan_out', 'desc')
            ->paginate(15);

        return view('produksi.wip.wipout', compact('wipOuts'));
    }

    public function createOut()
    {
        if (!userCan('produksi.wip.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('produksi.wip.createout');
    }

    public function storeOut(Request $request): JsonResponse
    {
        if (!userCan('produksi.wip.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        $validated = $request->validate([
            'lot_number' => 'required|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        try {
            $wipOut = null;
            $isNewRecord = false;
            DB::transaction(function () use ($validated, &$wipOut, &$isNewRecord) {
                // Cari wip in berdasarkan lot number (harus yang sudah confirmed)
                $wipIn = TWipIn::where('lot_number', $validated['lot_number'])
                    ->where('is_confirmed', true)
                    ->first();
                
                if (!$wipIn) {
                    throw new \Exception('Label dengan lot number tersebut belum di-scan in atau belum dikonfirmasi. Harus scan in dan konfirmasi terlebih dahulu.');
                }

                // Ambil planning run dan part untuk hitung target box
                $planningRun = $wipIn->planningRun;
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
                $existingWipOut = TWipOut::where('lot_number', $validated['lot_number'])->first();

                // Waktu scan
                $waktuScanOut = now('Asia/Jakarta');

                if ($existingWipOut) {
                    // Gunakan record yang sudah ada
                    $wipOut = $existingWipOut;
                    
                    // Update waktu scan terakhir
                    $wipOut->update([
                        'waktu_scan_out' => $waktuScanOut,
                        'catatan' => $validated['catatan'] ?? $wipOut->catatan,
                    ]);
                    
                    $isNewRecord = false;
                } else {
                    // Buat record baru
                    $wipOut = TWipOut::create([
                        'wip_in_id' => $wipIn->id,
                        'inject_out_id' => $wipIn->inject_out_id,
                        'lot_number' => $validated['lot_number'],
                        'box_number' => $wipIn->box_number,
                        'planning_run_id' => $wipIn->planning_run_id,
                        'waktu_scan_out' => $waktuScanOut,
                        'catatan' => $validated['catatan'] ?? null,
                    ]);
                    $isNewRecord = true;
                }

                // Hitung box number berikutnya untuk wip out ini
                $existingBoxCount = $wipOut->details()->count();
                $nextBoxNumber = $existingBoxCount + 1;

                // Cek apakah sudah mencapai target box
                if ($existingBoxCount >= $targetBoxCount) {
                    throw new \Exception("Semua box sudah di-scan out. Target: {$targetBoxCount} box, Sudah di-scan: {$existingBoxCount} box");
                }

                // Tambahkan detail box baru
                $wipOut->details()->create([
                    'box_number' => $nextBoxNumber,
                    'waktu_scan' => $waktuScanOut,
                    'catatan' => $validated['catatan'] ?? null,
                ]);
            });

            $message = $isNewRecord 
                ? 'Label berhasil di-scan out' 
                : 'Box berhasil ditambahkan ke lot number yang sama';

            return response()->json([
                'success' => true,
                'message' => $message,
                'wip_out_id' => $wipOut->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function editOut(TWipOut $wipOut)
    {
        if (!userCan('produksi.wip.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $wipOut->load(['wipIn', 'injectOut.injectIn', 'planningRun.mold.part']);
        return view('produksi.wip.editout', compact('wipOut'));
    }

    public function updateOut(Request $request, TWipOut $wipOut): JsonResponse
    {
        if (!userCan('produksi.wip.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        try {
            $wipOut->update($validated);

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

    public function deleteOut(TWipOut $wipOut)
    {
        if (!userCan('produksi.wip.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $wipOut->load(['wipIn', 'injectOut.injectIn', 'planningRun.mold.part']);
        return view('produksi.wip.deleteout', compact('wipOut'));
    }

    public function destroyOut(TWipOut $wipOut): JsonResponse
    {
        if (!userCan('produksi.wip.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            $wipOut->delete();

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
     * Sync data Inject Out yang sudah ada ke WIP IN (untuk data yang sudah ada sebelumnya)
     */
    public function syncFromInjectOut(): JsonResponse
    {
        if (!userCan('produksi.wip.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            $synced = 0;
            $skipped = 0;
            
            DB::transaction(function () use (&$synced, &$skipped) {
                // Ambil semua Inject Out
                $injectOuts = TInjectOut::with('details')->get();
                
                foreach ($injectOuts as $injectOut) {
                    // Cek apakah sudah ada WIP IN untuk inject_out_id ini
                    $existingWipIn = TWipIn::where('inject_out_id', $injectOut->id)->first();
                    
                    if ($existingWipIn) {
                        $skipped++;
                        continue;
                    }
                    
                    // Ambil box number dari detail terakhir jika ada
                    $lastDetail = $injectOut->details()->orderBy('box_number', 'desc')->first();
                    $boxNumber = $lastDetail ? $lastDetail->box_number : null;
                    
                    // Buat WIP IN dengan is_confirmed = false
                    TWipIn::create([
                        'inject_out_id' => $injectOut->id,
                        'lot_number' => $injectOut->lot_number,
                        'box_number' => $boxNumber,
                        'planning_run_id' => $injectOut->planning_run_id,
                        'waktu_scan_in' => $injectOut->waktu_scan ?? now('Asia/Jakarta'),
                        'is_confirmed' => false,
                        'catatan' => $injectOut->catatan,
                    ]);
                    
                    $synced++;
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => "Sync berhasil! {$synced} data berhasil di-sync, {$skipped} data dilewati (sudah ada).",
                'synced' => $synced,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sync data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mencari wip in berdasarkan lot number (untuk WIP Out)
     */
    public function getWipInByLotNumber(string $lotNumber): JsonResponse
    {
        try {
            // Cari wip in yang sudah confirmed
            $wipIn = TWipIn::with([
                'planningRun.mold.part',
                'injectOut.injectIn.mesin',
            ])
            ->where('lot_number', $lotNumber)
            ->where('is_confirmed', true)
            ->first();

            if (!$wipIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Label dengan lot number tersebut belum di-scan in atau belum dikonfirmasi. Harus scan in dan konfirmasi terlebih dahulu.',
                ], 404);
            }

            $planningRun = $wipIn->planningRun;
            $part = $planningRun && $planningRun->mold ? $planningRun->mold->part : null;

            // Cek apakah sudah ada wip out untuk lot number ini
            $existingWipOut = TWipOut::where('lot_number', $lotNumber)
                ->with('details')
                ->first();

            // Hitung progress box
            $qtyPackingBox = $part ? ($part->QTY_Packing_Box ?? 0) : 0;
            $targetTotal = $planningRun ? ($planningRun->qty_target_total ?? 0) : 0;
            $targetBoxCount = $qtyPackingBox > 0 ? (int) ceil($targetTotal / $qtyPackingBox) : 0;
            $scannedBoxCount = $existingWipOut ? $existingWipOut->details()->count() : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'wip_in' => [
                        'id' => $wipIn->id,
                        'lot_number' => $wipIn->lot_number,
                        'box_number' => $wipIn->box_number,
                        'waktu_scan_in' => $wipIn->waktu_scan_in->format('Y-m-d H:i:s'),
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
                    'already_scanned_out' => $scannedBoxCount > 0,
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

