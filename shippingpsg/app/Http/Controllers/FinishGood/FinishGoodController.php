<?php

namespace App\Http\Controllers\FinishGood;

use App\Http\Controllers\Controller;
use App\Models\TFinishGoodIn;
use App\Models\TAssyOut;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SMPart;

use App\Models\MMesin;
use App\Models\MManpower;

class FinishGoodController extends Controller
{
    public function index(Request $request)
    {
        if (!userCan('finishgood.in.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = TFinishGoodIn::query();

        // Filter by search (Lot Number, Part Name, Part Number, Customer)
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('lot_number', 'like', "%$search%")
                  ->orWhere('customer', 'like', "%$search%")
                  ->orWhereHas('part', function($q2) use ($search) {
                      $q2->where('nama_part', 'like', "%$search%")
                         ->orWhere('nomor_part', 'like', "%$search%");
                  });
            });
        }

        // Filter by Date Range (waktu_scan)
        if ($request->start_date) {
            $query->whereDate('waktu_scan', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('waktu_scan', '<=', $request->end_date);
        }

        // 1. Ambil ID terakhir dari setiap kombinasi unique (lot_number + part_id)
        $latestIds = (clone $query)->selectRaw('MAX(id) as id')
            ->groupBy('lot_number', 'part_id')
            ->pluck('id');

        // 2. Query Data Utama berdasarkan ID tersebut
        $perPage = $request->get('per_page', 15);
        $finishGoodIns = TFinishGoodIn::with(['part', 'mesin', 'manpower'])
            ->whereIn('id', $latestIds)
            ->orderBy('waktu_scan', 'desc')
            ->paginate($perPage);

        // 3. Append informasi Summary (Total Box & Total Qty & Unique MP)
        foreach ($finishGoodIns as $item) {
            // Filter statistik harus spesifik ke lot_number DAN part_id
            $stats = TFinishGoodIn::where('lot_number', $item->lot_number)
                ->where('part_id', $item->part_id)
                ->selectRaw('COUNT(*) as total_box, SUM(qty) as total_pcs, COUNT(DISTINCT manpower_id) as total_mp')
                ->first();
                
            $item->total_box = $stats->total_box;
            $item->total_pcs = $stats->total_pcs;
            $item->total_mp = $stats->total_mp;
        }

        return view('finishgood.in.in', compact('finishGoodIns'));
    }

    /**
     * Export data to CSV with date filtering
     */
    public function export(Request $request)
    {
        if (!userCan('finishgood.in.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = TFinishGoodIn::with(['part', 'mesin', 'manpower']);

        // Filter by search (Lot Number, Part Name, Part Number, Customer)
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('lot_number', 'like', "%$search%")
                  ->orWhere('customer', 'like', "%$search%")
                  ->orWhereHas('part', function($q2) use ($search) {
                      $q2->where('nama_part', 'like', "%$search%")
                         ->orWhere('nomor_part', 'like', "%$search%");
                  });
            });
        }

        // Filter by Date Range (waktu_scan)
        if ($request->start_date) {
            $query->whereDate('waktu_scan', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('waktu_scan', '<=', $request->end_date);
        }

        $items = $query->orderBy('waktu_scan', 'asc')->get();

        $fileName = 'finishgood_in_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Waktu Scan', 'Lot Number', 'No Planning', 'Part Number', 'Part Name', 'Customer', 'Qty', 'Mesin', 'Shift', 'Operator', 'Catatan'];

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->waktu_scan ? $item->waktu_scan->format('Y-m-d H:i:s') : '-',
                    $item->lot_number,
                    $item->no_planning,
                    $item->part->nomor_part ?? '-',
                    $item->part->nama_part ?? '-',
                    $item->customer,
                    $item->qty,
                    $item->mesin->no_mesin ?? '-',
                    $item->shift,
                    $item->manpower->nama ?? '-',
                    $item->catatan
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function create()
    {
        if (!userCan('finishgood.in.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Ambil semua part untuk dropdown
        $parts = SMPart::orderBy('nomor_part')->get();
        
        return view('finishgood.in.create', compact('parts'));
    }

    public function store(Request $request): JsonResponse
    {
        if (!userCan('finishgood.in.create')) {
            abort(403, 'Unauthorized action.');
        }
        // Validasi input dari hasil parsing scanner
        $validated = $request->validate([
            'part_number' => 'required|string', // Scanner mengirim nomor part, bukan ID
            'lot_number' => 'required|string|max:100',
            'qty' => 'required|integer|min:1',
            'customer' => 'nullable|string|max:100',
            
            // Detail Lot Number hasil parsing
            'no_planning' => 'nullable|string',
            'no_mesin' => 'nullable|string',
            'tanggal_produksi' => 'nullable|date',
            'shift' => 'nullable|string',
            
            'catatan' => 'nullable|string',
        ]);

        try {
            // 1. Cari Part ID berdasarkan Part Number (Trim whitespace)
            $part = SMPart::where('nomor_part', trim($validated['part_number']))->first();
            
            if (!$part) {
                // Jika part tidak ditemukan di database
                return response()->json([
                    'success' => false,
                    'message' => 'Part Number tidak terdaftar: ' . $validated['part_number'],
                ], 404);
            }

            // 1.5. DETEKSI DOUBLE SCAN MERGE (Scanner error membaca 2 barcode sekaligus)
            // Kasus: Lot "106-16-1-26-1" + Part "63383-VT031" -> Jadi "106-16-1-26-163383-VT031"
            // Logic: Jika Lot Number mengandung Part Number, tolak.
            if (str_contains($validated['lot_number'], $part->nomor_part)) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Error Scanner: Terbaca 2 barcode sekaligus! Silakan scan ulang satu per satu.',
                    'is_double_scan' => true,
                ], 422);
            }

            // 2. DUPLICATE SCAN PREVENTION (REMOVED: User wants to allow fast scanning)
            // Scanner error merge (double string) is already handled by logic above (1.5) and validation below (3).


            // 3. VALIDASI DATA LOGIS (Shift & Mesin)
            // Validasi Shift (Max 3)
            if (!empty($validated['shift'])) {
                $shiftVal = (int)$validated['shift'];
                if ($shiftVal < 1 || $shiftVal > 3) {
                     return response()->json([
                        'success' => false,
                        'message' => '⚠️ Data Shift tidak valid: ' . $validated['shift'] . '. Shift harus 1, 2, atau 3.',
                    ], 422);
                }
            }

            // Validasi Mesin (Max 35)
            if (!empty($validated['no_mesin'])) {
                 $mesinVal = (int)$validated['no_mesin'];
                 if ($mesinVal < 1 || $mesinVal > 35) {
                      return response()->json([
                        'success' => false,
                        'message' => '⚠️ Data Mesin tidak valid: ' . $validated['no_mesin'] . '. Nomor Mesin maksimal 35.',
                    ], 422);
                 }
            }

            // 4. Lookup Mesin ID (Deep Fuzzy Search)
            $mesinId = null;
            if (!empty($validated['no_mesin'])) {
                $raw = $validated['no_mesin'];     // e.g. "06"
                $num = (int)$raw;                  // e.g. 6 (int)
                $strNum = (string)$num;            // e.g. "6"
                $padded = str_pad($strNum, 2, '0', STR_PAD_LEFT); // e.g. "06"
                
                $mesin = MMesin::where(function($q) use ($raw, $strNum, $padded) {
                    $q->where('no_mesin', $raw)                 // Exact "06"
                      ->orWhere('no_mesin', $strNum)            // "6"
                      ->orWhere('no_mesin', $padded)            // "06"
                      ->orWhere('no_mesin', 'like', "%{$strNum}%"); // Contains "6" (Risk: match 16, 26, but ordered by id usually)
                })->first();
                
                if ($mesin) {
                    $mesinId = $mesin->id;
                }
            }

            // 4. Lookup Manpower ID (Current User from Session)
            $manpowerId = null;
            
            // Sistem menggunakan Custom Auth (Session 'user_id'), bukan Laravel Auth standard
            if (!session()->has('user_id')) {
                 throw new \Exception("Sesi login berakhir. Silakan login kembali.");
            }
            
            $userId = session('user_id'); // Ambil user_id dari session
            
            // Cek apakah user_id ini ada di MManpower
            $manpower = MManpower::where('mp_id', $userId)->first();
            
            if ($manpower) {
                $manpowerId = $manpower->id;
            }

            // Note: DB Transaction tidak perlu pass variable $manpowerName lagi
            $finishGoodIn = null;
            DB::transaction(function () use ($validated, $part, $mesinId, $manpowerId, &$finishGoodIn) {
                $waktuScan = now('Asia/Jakarta');

                // Simpan data
                $finishGoodIn = TFinishGoodIn::create([
                    'assy_out_id' => null, // Standalone scan, unlinked to assy
                    'lot_number' => $validated['lot_number'],
                    'part_id' => $part->id,
                    'qty' => $validated['qty'],
                    'customer' => $validated['customer'] ?? null,
                    
                    'no_planning' => $validated['no_planning'] ?? null,
                    'mesin_id' => $mesinId, // Save ID
                    'tanggal_produksi' => $validated['tanggal_produksi'] ?? null,
                    'shift' => $validated['shift'] ?? null,
                    
                    'manpower_id' => $manpowerId, // Save ID
                    'waktu_scan' => $waktuScan,
                    'catatan' => $validated['catatan'] ?? null,
                ]);
            });

            // Load relasi untuk response (agar bisa tampil di tabel frontend)
            $finishGoodIn->load(['part', 'mesin', 'manpower']);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil scan: ' . $part->nomor_part,
                'data' => $finishGoodIn,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 400); // 500 jika server error, 400 jika bad request logic
        }
    }

    public function edit(TFinishGoodIn $finishGoodIn)
    {
        if (!userCan('finishgood.in.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $finishGoodIn->load([
            'assyOut.part',
            'assyOut.assyIn.part',
        ]);

        return view('finishgood.in.edit', compact('finishGoodIn'));
    }

    public function update(Request $request, TFinishGoodIn $finishGoodIn): JsonResponse
    {
        if (!userCan('finishgood.in.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'manpower' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        try {
            $finishGoodIn->update($validated);

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

    public function delete(TFinishGoodIn $finishGoodIn)
    {
        if (!userCan('finishgood.in.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $finishGoodIn->load([
            'assyOut.part',
            'assyOut.assyIn.part',
        ]);

        return view('finishgood.in.delete', compact('finishGoodIn'));
    }

    public function destroy(TFinishGoodIn $finishGoodIn): JsonResponse
    {
        if (!userCan('finishgood.in.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $finishGoodIn->delete();

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

    public function detail(TFinishGoodIn $finishGoodIn)
    {
        if (!userCan('finishgood.in.view')) {
            abort(403, 'Unauthorized action.');
        }
        // Tampilkan LIST SEMUA ITEM dalam Lot Number & Part yang sama
        $items = TFinishGoodIn::with(['part', 'mesin', 'manpower'])
            ->where('lot_number', $finishGoodIn->lot_number)
            ->where('part_id', $finishGoodIn->part_id)
            ->orderByDesc('waktu_scan')
            ->get();
            
        return view('finishgood.in.detail', compact('items', 'finishGoodIn'));
    }

    /**
     * Hapus semua item dalam satu Lot Number
     */
    public function destroyLot(TFinishGoodIn $finishGoodIn): JsonResponse
    {
        if (!userCan('finishgood.in.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $lotNumber = $finishGoodIn->lot_number;
            $partId = $finishGoodIn->part_id;
            
            // Hapus semua yang punya lot number DAN part yang sama
            $count = TFinishGoodIn::where('lot_number', $lotNumber)
                ->where('part_id', $partId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus Lot $lotNumber ($count items)",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Lot: ' . $e->getMessage(),
            ], 500);
        }
    }

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
}

