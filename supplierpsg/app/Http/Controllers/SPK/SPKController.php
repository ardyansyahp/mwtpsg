<?php

namespace App\Http\Controllers\SPK;

use App\Http\Controllers\Controller;
use App\Models\MPerusahaan;
use App\Models\MPlantGate;
use App\Models\SMPart;
use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\MKendaraan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SPKController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = TSpk::with([
                'customer',
                'plantgate',
                'details.part',
            ])->withCount('details');

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_spk', 'like', "%{$search}%")
                      ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($q2) use ($search) {
                          $q2->where('nama_perusahaan', 'like', "%{$search}%");
                      });
                });
            }

            $perPage = $request->get('per_page', 10);
            $spks = $query->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            $spks->appends($request->all());

            return view('spk.spk', compact('spks'));
        } catch (\Exception $e) {
            \Log::error('SPK Index Error: ' . $e->getMessage());
            return view('spk.spk', ['spks' => \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 10)]);
        }
    }

    public function create()
    {
        // Check permission
        if (!userCan('spk.create')) {
            abort(403, 'Unauthorized action.');
        }

        $customers = MPerusahaan::active()->where('jenis_perusahaan', 'Customer')
            ->orWhereNull('jenis_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        $plantgates = MPlantGate::with('customer')
            ->orderBy('nama_plantgate')
            ->get();

        $parts = SMPart::orderBy('nomor_part')->get();

        $kendaraans = MKendaraan::orderBy('nopol_kendaraan')->get();

        $userId = session('user_id');
        $manpower = \App\Models\MManpower::where('mp_id', $userId)->first();
        $manpowerName = $manpower ? $manpower->nama : 'Unknown';

        return view('spk.create', compact('customers', 'plantgates', 'parts', 'kendaraans', 'manpowerName'));
    }

    public function importForm()
    {
        if (!userCan('spk.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('spk.import');
    }

    public function store(Request $request): JsonResponse
    {
        // Check permission
        if (!userCan('spk.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            // Remove nomor_spk requirement as it is auto-generated
            $validated = $request->validate([
                // 'nomor_spk' => 'required|string|max:100|unique:T_SPK,nomor_spk', 
                // 'manpower_pembuat' => 'nullable|string|max:100', // We get this from session
                'customer_id' => 'required|exists:M_Perusahaan,id',
                'plantgate_id' => 'required|exists:M_PlantGate,id',
                'tanggal' => 'required|date',
                'jam_berangkat_plan' => 'nullable|string|max:10',
                'jam_datang_plan' => 'nullable|string|max:10',
                'cycle' => 'nullable|integer',
                // 'no_surat_jalan' => 'nullable|string|max:100', // Disabled/Ignored
                'nomor_plat' => 'nullable|string|max:20',
                'model_part' => 'required|in:regular,ckd,cbu,rempart',
                'catatan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.part_id' => 'required|exists:SM_Part,id',
                'details.*.qty_packing_box' => 'required|integer|min:0',
                'details.*.jadwal_delivery_pcs' => 'required|integer|min:0',
                'details.*.jumlah_pulling_box' => 'required|integer|min:0',
                'details.*.catatan' => 'nullable|string',
            ]);

            // 1. Get Manpower from Session
            if (!session()->has('user_id')) {
                return response()->json(['success' => false, 'message' => 'Sesi berakhir. Silakan login kembali.'], 401);
            }
            $userId = session('user_id');
            // Assuming MManpower table has 'mp_id' acting as user identifier or we look up by id if user_id is the PK.
            // FinishGoodController uses 'mp_id'. We will stick to that logic logic.
            // But wait, Store SPK usually stores the name string `manpower_pembuat`.
            $manpower = \App\Models\MManpower::where('mp_id', $userId)->first();
            $manpowerName = $manpower ? $manpower->nama : 'Unknown';

            
            $spk = null;
            DB::transaction(function () use ($validated, $manpowerName, &$spk) {
                // 2. Generate Nomor SPK: SPK-YYYYMMDD-XXXX
                $dateCode = date('Ymd');
                $prefix = "SPK-{$dateCode}-";
                
                // Find last number for today
                $lastSpk = TSpk::where('nomor_spk', 'like', "{$prefix}%")
                    ->orderBy('nomor_spk', 'desc')
                    ->first();
                
                $nextSequence = 1;
                if ($lastSpk) {
                    $lastNumberRaw = str_replace($prefix, '', $lastSpk->nomor_spk);
                    $nextSequence = (int)$lastNumberRaw + 1;
                }
                
                $generatedSpkNumber = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

                // Prepare Data
                $data = $validated;
                $data['nomor_spk'] = $generatedSpkNumber;
                $data['manpower_pembuat'] = $manpowerName;
                $data['no_surat_jalan'] = null; // Disable as requested
                $data['cycle_number'] = $validated['cycle'] ?? 1;
                
                $details = $data['details'] ?? [];
                unset($data['details']);

                $spk = TSpk::create($data);

                foreach ($details as $detail) {
                    // Validasi Quantity: Harus kelipatan qty_packing_box (Std Packing)
                    $qtyPack = $detail['qty_packing_box'];
                    $jadwalDelivery = $detail['jadwal_delivery_pcs'];

                    if ($qtyPack > 0 && ($jadwalDelivery % $qtyPack !== 0)) {
                        // Optional: Throw error or allow but warn. User said "gaboleh ngga".
                        // We will allow for now but frontend should block. 
                        // Or throw exception here to strictly enforce it.
                        throw new \Exception("Qty Delivery untuk item harus kelipatan Std Packing ($qtyPack). Input: $jadwalDelivery");
                    }

                    TSpkDetail::create([
                        'spk_id' => $spk->id,
                        'part_id' => $detail['part_id'],
                        'qty_packing_box' => $detail['qty_packing_box'],
                        'jadwal_delivery_pcs' => $detail['jadwal_delivery_pcs'],
                        'jumlah_pulling_box' => $detail['jumlah_pulling_box'],
                        'catatan' => $detail['catatan'] ?? null,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil dibuat: ' . $spk->nomor_spk,
                'spk_id' => $spk->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors()[array_key_first($e->errors())]),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in SPKController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($spk)
    {
        // Check permission
        if (!userCan('spk.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $spk = TSpk::findOrFail($spk);
        $spk->load(['customer', 'plantgate', 'details.part']);

        $customers = MPerusahaan::active()->where('jenis_perusahaan', 'Customer')
            ->orWhereNull('jenis_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        $plantgates = MPlantGate::with('customer')
            ->orderBy('nama_plantgate')
            ->get();

        $parts = SMPart::orderBy('nomor_part')->get();

        $kendaraans = MKendaraan::orderBy('nopol_kendaraan')->get();

        return view('spk.edit', compact('spk', 'customers', 'plantgates', 'parts', 'kendaraans'));
    }

    public function update(Request $request, $spk): JsonResponse
    {
        // Check permission
        if (!userCan('spk.edit')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $spk = TSpk::findOrFail($spk);
            $validated = $request->validate([
                'nomor_spk' => 'required|string|max:100|unique:T_SPK,nomor_spk,' . $spk->id,
                'manpower_pembuat' => 'nullable|string|max:100',
                'customer_id' => 'required|exists:M_Perusahaan,id',
                'plantgate_id' => 'required|exists:M_PlantGate,id',
                'tanggal' => 'required|date',
                'jam_berangkat_plan' => 'nullable|string|max:10',
                'jam_datang_plan' => 'nullable|string|max:10',
                'cycle' => 'nullable|integer',
                'no_surat_jalan' => 'nullable|string|max:100',
                'nomor_plat' => 'nullable|string|max:20',
                'model_part' => 'required|in:regular,ckd,cbu,rempart',
                'catatan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.part_id' => 'required|exists:SM_Part,id',
                'details.*.qty_packing_box' => 'required|integer|min:0',
                'details.*.jadwal_delivery_pcs' => 'required|integer|min:0',
                'details.*.jumlah_pulling_box' => 'required|integer|min:0',
                'details.*.catatan' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validated, $spk) {
                $details = $validated['details'] ?? [];
                unset($validated['details']);
                
                $validated['cycle_number'] = $validated['cycle'] ?? ($spk->cycle_number ?? 1);
                $spk->update($validated);

                // Hapus detail lama
                $spk->details()->delete();

                // Buat detail baru
                foreach ($details as $detail) {
                    TSpkDetail::create([
                        'spk_id' => $spk->id,
                        'part_id' => $detail['part_id'],
                        'qty_packing_box' => $detail['qty_packing_box'],
                        'jadwal_delivery_pcs' => $detail['jadwal_delivery_pcs'],
                        'jumlah_pulling_box' => $detail['jumlah_pulling_box'],
                        'catatan' => $detail['catatan'] ?? null,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil diperbarui',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors()[array_key_first($e->errors())]),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in SPKController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete($spk)
    {
        // Check permission
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $spk = TSpk::findOrFail($spk);
        $spk->load(['customer', 'plantgate', 'details.part']);
        return view('spk.delete', compact('spk'));
    }

    public function destroy($spk): JsonResponse
    {
        // Check permission
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $spk = TSpk::findOrFail($spk);
            DB::transaction(function () use ($spk) {
                $spk->details()->delete();
                $spk->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in SPKController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detail($spk)
    {
        $spk = TSpk::findOrFail($spk);
        $spk->load([
            'customer',
            'plantgate',
            'details.part',
        ]);

        return view('spk.detail', compact('spk'));
    }

    /**
     * Display a listing of soft-deleted resources (Recycle Bin).
     */
    public function trash(Request $request)
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $spks = TSpk::onlyTrashed()
            ->with(['customer', 'plantgate'])
            ->withCount('details')
            ->orderBy('deleted_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return view('spk.trash', compact('spks'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $spk = TSpk::withTrashed()->findOrFail($id);
        $spk->restore();

        return redirect()->back()->with('success', 'Data SPK berhasil dipulihkan');
    }

    /**
     * Restore all soft-deleted resources.
     */
    public function restoreAll()
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        TSpk::onlyTrashed()->restore();

        return redirect()->back()->with('success', 'Semua data SPK berhasil dipulihkan');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $spk = TSpk::withTrashed()->findOrFail($id);
        
        // Delete details permanently
        $spk->details()->delete(); // Soft delete details if they have it, or force delete if not? 
        // Since TSpkDetail doesn't have SoftDeletes (checked earlier), this will delete them.
        // But if TSpkDetail had SoftDeletes, we should forceDelete them too. 
        // For now, assuming standard delete is fine or we can force delete parent.
        
        $spk->forceDelete();

        return redirect()->back()->with('success', 'Data SPK berhasil dihapus permanen');
    }

    /**
     * Permanently delete all soft-deleted resources.
     */
    public function forceDeleteAll()
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        // TSpk::onlyTrashed()->forceDelete(); 
        // We need to handle details too
        $trashed = TSpk::onlyTrashed()->get();
        foreach($trashed as $spk) {
            $spk->details()->delete();
            $spk->forceDelete();
        }

        return redirect()->back()->with('success', 'Semua sampah SPK berhasil dibersihkan permanen');
    }

    /**
     * Remove all resources from storage (Soft Delete All).
     */
    public function destroyAll()
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            TSpk::query()->delete();
            return redirect()->back()->with('success', 'Semua data SPK berhasil dihapus (Soft Delete)');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete items
     */
    public function bulkDelete(Request $request)
    {
        if (!userCan('spk.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:t_spk,id'
        ]);

        TSpk::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' data SPK berhasil dihapus'
        ]);
    }

    /**
     * Export data to CSV
     */
    public function export(Request $request)
    {
        if (!userCan('spk.index')) {
            abort(403, 'Unauthorized action.');
        }

        $fileName = 'data_spk_' . date('Y-m-d_H-i-s') . '.csv';
        
        $query = TSpk::with(['customer', 'plantgate', 'details']);
        
        // Filter logic same as index
        if ($request->search) {
             $search = $request->search;
             $query->where(function($q) use ($search) {
                 $q->where('nomor_spk', 'like', "%$search%")
                   ->orWhere('no_surat_jalan', 'like', "%$search%")
                   ->orWhereHas('customer', function($q2) use ($search) {
                       $q2->where('nama_perusahaan', 'like', "%$search%");
                   });
             });
        }
        
        $items = $query->orderBy('tanggal', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Nomor SPK', 'Tanggal', 'Customer', 'Plant', 'Cycle', 'Model', 'SJ', 'Item Count'];

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->nomor_spk,
                    $item->tanggal->format('Y-m-d'),
                    $item->customer->nama_perusahaan ?? '-',
                    $item->plantgate->nama_plantgate ?? '-',
                    $item->cycle_number,
                    $item->model_part,
                    $item->no_surat_jalan ?? '-',
                    $item->details->count()
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import data from CSV (Placeholder)
     */
    public function import(Request $request)
    {
        if (!userCan('spk.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240', // Increase limit 10MB
            // Dynamic columns validation could be added but skipping for flexibility
        ]);

        try {
            $file = $request->file('file');
            $mapping = $request->except(['_token', 'file', 'start_row']);
            $startRow = $request->input('start_row', 2);

            $importer = new \App\Imports\SPKImport($mapping, $startRow);
            $stats = $importer->import($file->getRealPath());

            if (!empty($stats['errors'])) {
                $msg = 'Import Gagal/Parsial. ' . count($stats['errors']) . ' baris bermasalah.';
                if ($stats['success'] > 0) {
                     return redirect()->route('spk.index')->with('warning', $msg . " Berhasil: {$stats['success']} SPK.")
                        ->with('import_errors', $stats['errors']); // Redirect to index but carrying errors might be lost if not handled in index view. Better stay in form if heavy errors? 
                        // User requested detailed logs explanation.
                        // Let's redirect BACK to form if there are errors, so they can see the list.
                }
                
                // If 0 success, definitely back
                return redirect()->back()
                    ->with('error', $msg)
                    ->with('import_errors', $stats['errors']);
            }

            return redirect()->route('spk.index')->with('success', "Import Full Berhasil! {$stats['success']} SPK baru.");

        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * API untuk mendapatkan plantgates berdasarkan customer_id
     */
    public function getPlantgatesByCustomer(Request $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            
            if (!$customerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer ID diperlukan',
                ], 400);
            }

            $plantgates = MPlantGate::where('customer_id', $customerId)
                ->orderBy('nama_plantgate')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $plantgates,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getPlantgatesByCustomer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan parts berdasarkan plantgate_id
     */
    public function getPartsByPlantgate(Request $request): JsonResponse
    {
        try {
            $plantgateId = $request->input('plantgate_id');
            
            if (!$plantgateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plantgate ID diperlukan',
                ], 400);
            }

            $plantgate = MPlantGate::with('parts')->find($plantgateId);
            
            if (!$plantgate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plantgate tidak ditemukan',
                ], 404);
            }

            // Get parts with QTY_Packing_Box (Fallback mechanism applied)
            $parts = $plantgate->parts->map(function($part) {
                $qtyPacking = $part->QTY_Packing_Box ?? 0;
                
                // Fallback: Jika Qty Packing 0/Null, cari part lain dengan nomor_part yang sakti (sama) yang punya data
                if ($qtyPacking <= 0) {
                    $otherPart = SMPart::where('nomor_part', $part->nomor_part)
                        ->where('QTY_Packing_Box', '>', 0)
                        ->first();
                        
                    if ($otherPart) {
                        $qtyPacking = $otherPart->QTY_Packing_Box;
                    }
                }
                
                return [
                    'id' => $part->id,
                    'nomor_part' => $part->nomor_part,
                    'nama_part' => $part->nama_part,
                    'model_part' => ucfirst($part->model_part ?? 'regular'),
                    'tipe_id' => $part->tipe_id,
                    'QTY_Packing_Box' => $qtyPacking,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $parts,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getPartsByPlantgate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
