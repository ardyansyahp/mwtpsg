<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MKendaraan;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MKendaraan::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nopol_kendaraan', 'like', "%{$search}%")
                  ->orWhere('jenis_kendaraan', 'like', "%{$search}%")
                  ->orWhere('merk_kendaraan', 'like', "%{$search}%");
            });
        }

        // Filter Jenis
        if ($request->has('jenis') && $request->jenis) {
            $query->where('jenis_kendaraan', $request->jenis);
        }

        // Sorting
        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        $allowedSorts = ['nopol_kendaraan', 'jenis_kendaraan', 'merk_kendaraan', 'tahun_kendaraan', 'status', 'created_at'];
        
        if (!in_array($sortColumn, $allowedSorts)) {
            $sortColumn = 'created_at';
        }
        $query->orderBy($sortColumn, $sortDirection === 'asc' ? 'asc' : 'desc');

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $kendaraans = $query->paginate($perPage)->withQueryString();

        // Get unique vehicle types for filter
        $types = MKendaraan::select('jenis_kendaraan')->distinct()->pluck('jenis_kendaraan');

        return view('master.kendaraan.kendaraan', compact('kendaraans', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!userCan('master.kendaraan.create')) abort(403);
        return view('master.kendaraan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!userCan('master.kendaraan.create')) abort(403);
        
        // Check if data exists in trash
        $existingTrash = MKendaraan::onlyTrashed()
            ->where('nopol_kendaraan', $request->nopol_kendaraan)
            ->first();

        if ($existingTrash) {
            return back()->withInput()->with('error', 'Data dengan Nopol tersebut sudah ada di SAMPAH (Trash). Silahkan pulihkan (Restore) data tersebut.');
        }

        $validated = $request->validate([
            'nopol_kendaraan' => 'required|string|unique:m_kendaraan,nopol_kendaraan|max:20',
            'jenis_kendaraan' => 'required|string|max:50',
            'merk_kendaraan' => 'required|string|max:50',
            'tahun_kendaraan' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        // Generate QR Code content (using ID might be safer but Nopol is unique too)
        // Here we just save placeholder or Generate after save.
        // Let's create first.
        
        $kendaraan = MKendaraan::create($validated);
        
        // Update QRCode with Nopol
        $kendaraan->qrcode = $kendaraan->nopol_kendaraan;
        $kendaraan->save();

        return redirect()->route('master.kendaraan.index')->with('success', 'Data kendaraan berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!userCan('master.kendaraan.edit')) abort(403);
        $kendaraan = MKendaraan::findOrFail($id);
        return view('master.kendaraan.edit', compact('kendaraan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!userCan('master.kendaraan.edit')) abort(403);

        $kendaraan = MKendaraan::findOrFail($id);

        $validated = $request->validate([
            'nopol_kendaraan' => 'required|string|max:20|unique:m_kendaraan,nopol_kendaraan,' . $id,
            'jenis_kendaraan' => 'required|string|max:50',
            'merk_kendaraan' => 'required|string|max:50',
            'tahun_kendaraan' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        
        // Update qrcode if nopol changes or just ensure it matches
        $validated['qrcode'] = $validated['nopol_kendaraan'];

        $kendaraan->update($validated);

        return redirect()->route('master.kendaraan.index')->with('success', 'Data kendaraan berhasil diupdate');
    }

    /**
     * Display delet confirmation
     */
    public function delete($id)
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        $kendaraan = MKendaraan::findOrFail($id);
        return view('master.kendaraan.delete', compact('kendaraan'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        MKendaraan::findOrFail($id)->delete();
        return redirect()->route('master.kendaraan.index')->with('success', 'Data kendaraan berhasil dihapus');
    }

    /**
     * Show detail
     */
    public function show($id)
    {
        if (!userCan('master.kendaraan.index')) abort(403);
        $kendaraan = MKendaraan::withTrashed()->findOrFail($id);
        
        // Pass qrcode string
        $qrCode = $kendaraan->qrcode ?? $kendaraan->nopol_kendaraan;
        
        return view('master.kendaraan.show', compact('kendaraan', 'qrCode'));
    }

    /**
     * Toggle Status
     */
    public function toggleStatus($id)
    {
        if (!userCan('master.kendaraan.edit')) abort(403);

        try {
            $kendaraan = MKendaraan::findOrFail($id);
            $kendaraan->status = !$kendaraan->status;
            $kendaraan->save();
            return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $kendaraan->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Import Form
     */
    public function showImportForm()
    {
        if (!userCan('master.kendaraan.create')) abort(403);
        return view('master.kendaraan.import');
    }

    /**
     * Import Process
     */
    public function import(Request $request)
    {
        if (!userCan('master.kendaraan.create')) abort(403);

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($data);

            $created = 0;
            $updated = 0;
            $failed = 0;

            foreach ($data as $row) {
                if (count($row) < 4) {
                    $failed++;
                    continue;
                }

                $nopol = trim($row[0]);
                $jenis = trim($row[1]);
                $merk  = trim($row[2]);
                $tahun = (int) trim($row[3]);

                // Check existing including trash
                $kendaraan = MKendaraan::withTrashed()->where('nopol_kendaraan', $nopol)->first();

                if ($kendaraan) {
                    // Update existing
                    if ($kendaraan->trashed()) {
                        $kendaraan->restore();
                    }
                    $kendaraan->update([
                        'jenis_kendaraan' => $jenis,
                        'merk_kendaraan' => $merk,
                        'tahun_kendaraan' => $tahun,
                        'status' => true,
                        // Ensure QRCode is synced
                        'qrcode' => $nopol 
                    ]);
                    $updated++;
                } else {
                    // Create new
                    MKendaraan::create([
                        'nopol_kendaraan' => $nopol,
                        'jenis_kendaraan' => $jenis,
                        'merk_kendaraan' => $merk,
                        'tahun_kendaraan' => $tahun,
                        'status' => true,
                        'qrcode' => $nopol
                    ]);
                    $created++;
                }
            }

            return redirect()->route('master.kendaraan.index')
                ->with('success', "Import selesai. Dibuat: $created, Diupdate/Dipulihkan: $updated, Gagal: $failed");

        } catch (\Exception $e) {
            // Friendly error for duplicates if they somehow slip through or other SQL errors
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->with('error', 'Gagal Import: Terdapat data duplikat yang tidak bisa diproses. Pastikan semua Nopol unik.');
            }
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    /**
     * Export Process
     */
    public function export()
    {
        if (!userCan('master.kendaraan.index')) abort(403);

        $fileName = 'data_kendaraan_' . date('Y-m-d_H-i-s') . '.csv';
        $data = MKendaraan::orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['No', 'Nopol', 'Jenis', 'Merk', 'Tahun', 'Status', 'Created At'];

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $i => $item) {
                fputcsv($file, [
                    $i + 1,
                    $item->nopol_kendaraan,
                    $item->jenis_kendaraan,
                    $item->merk_kendaraan,
                    $item->tahun_kendaraan,
                    $item->status ? 'Active' : 'Inactive',
                    $item->created_at->format('Y-m-d H:i')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Trash
     */
    public function trash()
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        $trashed = MKendaraan::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
        return view('master.kendaraan.trash', compact('trashed'));
    }

    /**
     * Restore
     */
    public function restore($id)
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        MKendaraan::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Data dipulihkan');
    }

    /**
     * Restore All
     */
    public function restoreAll()
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        MKendaraan::onlyTrashed()->restore();
        return back()->with('success', 'Semua data dipulihkan');
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        $request->validate(['ids' => 'required|array']);
        MKendaraan::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }
    
    /**
     * Destroy All (Reset)
     */
    public function destroyAll()
    {
         if (!userCan('master.kendaraan.delete')) abort(403);
         MKendaraan::query()->delete();
         return back()->with('success', 'Semua data dihapus');
    }
     /**
     * Force Delete Single
     */
    public function forceDelete($id)
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        MKendaraan::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->back()->with('success', 'Data permanen dihapus');
    }

    /**
     * Force Delete All
     */
    public function forceDeleteAll()
    {
        if (!userCan('master.kendaraan.delete')) abort(403);
        MKendaraan::onlyTrashed()->forceDelete();
        return redirect()->back()->with('success', 'Semua data sampah berhasil dikosongkan');
    }

}
