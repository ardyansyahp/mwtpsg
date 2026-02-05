<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MPerusahaan;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MPerusahaan::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('inisial_perusahaan', 'like', "%{$search}%")
                  ->orWhere('jenis_perusahaan', 'like', "%{$search}%")
                  ->orWhere('kode_supplier', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        // Advanced Filter: Jenis Perusahaan
        if ($request->has('jenis') && $request->jenis) {
            $query->where('jenis_perusahaan', $request->jenis);
        }

        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        
        // Whitelist columns for sorting
        $allowedSorts = ['nama_perusahaan', 'inisial_perusahaan', 'jenis_perusahaan', 'kode_supplier', 'alamat', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts)) {
            $sortColumn = 'created_at';
        }
        
        // Validation for sort order
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $perusahaans = $query->paginate($perPage)->onEachSide(1);
        
        // Append query strings to pagination links
        $perusahaans->appends($request->all());

        return view('master.perusahaan.perusahaan', compact('perusahaans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!userCan('master.perusahaan.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.perusahaan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!userCan('master.perusahaan.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check trash
        $existingTrash = MPerusahaan::onlyTrashed()
            ->where('nama_perusahaan', $request->nama_perusahaan)
            ->first();

        if ($existingTrash) {
             return response()->json([
                'success' => false,
                'message' => 'Data dengan Nama Perusahaan tersebut sudah ada di SAMPAH. Silahkan restore data tersebut.'
            ], 422);
        }

        $rules = [
            'nama_perusahaan' => 'required|string|max:255',
            'inisial_perusahaan' => 'nullable|string|max:50',
            'jenis_perusahaan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ];

        // Add kode_supplier validation only if jenis_perusahaan is Vendor
        if ($request->jenis_perusahaan === 'Vendor') {
            $rules['kode_supplier'] = 'required|string|max:50';
        } else {
            $rules['kode_supplier'] = 'nullable|string|max:50';
        }

        $validated = $request->validate($rules);

        // Remove kode_supplier if jenis_perusahaan is not Vendor
        if ($request->jenis_perusahaan !== 'Vendor') {
            $validated['kode_supplier'] = null;
        }

        MPerusahaan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil ditambahkan'
        ]);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MPerusahaan $perusahaan)
    {
        if (!userCan('master.perusahaan.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.perusahaan.edit', compact('perusahaan'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!userCan('master.perusahaan.index')) {
            abort(403, 'Unauthorized action.');
        }

        // Use findOrFail with relations if possible
        $perusahaan = MPerusahaan::withTrashed()->findOrFail($id);

        // Dummy stats for now (replace with actual relations later)
        // $totalPO = $perusahaan->purchaseOrders()->count(); 
        // $totalSpending = $perusahaan->purchaseOrders()->sum('total_amount');
        
        $stats = [
            'total_po' => 0, // Placeholder
            'total_spending' => 0, // Placeholder
            'last_transaction' => $perusahaan->created_at->format('d M Y') // Placeholder
        ];

        return view('master.perusahaan.show', compact('perusahaan', 'stats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MPerusahaan $perusahaan)
    {
        if (!userCan('master.perusahaan.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'nama_perusahaan' => 'required|string|max:255',
            'inisial_perusahaan' => 'nullable|string|max:50',
            'jenis_perusahaan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ];

        // Add kode_supplier validation only if jenis_perusahaan is Vendor
        if ($request->jenis_perusahaan === 'Vendor') {
            $rules['kode_supplier'] = 'required|string|max:50';
        } else {
            $rules['kode_supplier'] = 'nullable|string|max:50';
        }

        $validated = $request->validate($rules);

        // Remove kode_supplier if jenis_perusahaan is not Vendor
        if ($request->jenis_perusahaan !== 'Vendor') {
            $validated['kode_supplier'] = null;
        }

        $perusahaan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil diupdate'
        ]);
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(MPerusahaan $perusahaan)
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.perusahaan.delete', compact('perusahaan'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MPerusahaan $perusahaan)
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $perusahaan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data perusahaan berhasil dihapus'
        ]);
    }

    /**
     * Remove all resources from storage.
     */
    public function destroyAll()
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Use massive soft delete logic
            MPerusahaan::query()->delete();

            return redirect()->route('master.perusahaan.index')
                ->with('success', 'Semua data perusahaan berhasil dihapus (Soft Delete)');
        } catch (\Exception $e) {
            return redirect()->route('master.perusahaan.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Export data to CSV
     */
    public function export()
    {
        if (!userCan('master.perusahaan.index')) {
            abort(403, 'Unauthorized action.');
        }

        $fileName = 'data_perusahaan_' . date('Y-m-d_H-i-s') . '.csv';
        $perusahaans = MPerusahaan::orderBy('created_at', 'desc')->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Nama Perusahaan', 'Inisial', 'Jenis', 'Kode Supplier', 'Alamat', 'Tanggal Dibuat');

        $callback = function() use($perusahaans, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($perusahaans as $index => $perusahaan) {
                $row['No']  = $index + 1;
                $row['Nama Perusahaan']    = $perusahaan->nama_perusahaan;
                $row['Inisial']    = $perusahaan->inisial_perusahaan;
                $row['Jenis']  = $perusahaan->jenis_perusahaan;
                $row['Kode Supplier']  = $perusahaan->kode_supplier;
                $row['Alamat']  = $perusahaan->alamat;
                $row['Tanggal Dibuat'] = $perusahaan->created_at->format('Y-m-d H:i:s');

                fputcsv($file, array(
                    $row['No'], 
                    $row['Nama Perusahaan'], 
                    $row['Inisial'], 
                    $row['Jenis'], 
                    $row['Kode Supplier'], 
                    $row['Alamat'], 
                    $row['Tanggal Dibuat']
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display a listing of soft-deleted resources (Recycle Bin).
     */
    public function trash()
    {
        if (!userCan('master.perusahaan.index')) {
            abort(403, 'Unauthorized action.');
        }

        $perusahaans = MPerusahaan::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
        return view('master.perusahaan.trash', compact('perusahaans'));
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $perusahaan = MPerusahaan::withTrashed()->findOrFail($id);
        $perusahaan->restore();

        return redirect()->back()->with('success', 'Data perusahaan berhasil dipulihkan');
    }

    /**
     * Restore all soft-deleted resources.
     */
    public function restoreAll()
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        MPerusahaan::onlyTrashed()->restore();

        return redirect()->back()->with('success', 'Semua data sampah berhasil dipulihkan');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $perusahaan = MPerusahaan::withTrashed()->findOrFail($id);
        
        $perusahaan->forceDelete();

        return redirect()->back()->with('success', 'Data perusahaan berhasil dihapus permanen');
    }

    /**
     * Permanently delete all soft-deleted resources.
     */
    public function forceDeleteAll()
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        MPerusahaan::onlyTrashed()->forceDelete();

        return redirect()->back()->with('success', 'Semua sampah berhasil dibersihkan permanen');
    }

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        if (!userCan('master.perusahaan.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.perusahaan.import');
    }

    /**
     * Helper to get status class
     */
    public function toggleStatus($id)
    {
        if (!userCan('master.perusahaan.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $perusahaan = MPerusahaan::findOrFail($id);
        $perusahaan->status = !$perusahaan->status;
        $perusahaan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status perusahaan berhasil diupdate',
            'new_status' => $perusahaan->status
        ]);
    }


    /**
     * Bulk delete items
     */
    public function bulkDelete(Request $request)
    {
        if (!userCan('master.perusahaan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:m_perusahaan,id'
        ]);

        MPerusahaan::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' data berhasil dihapus'
        ]);
    }

    /**
     * Import data from Excel (SAP format)
     */
    public function import(Request $request)
    {
        if (!userCan('master.perusahaan.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            
            $importer = new \App\Imports\PerusahaanImport();
            $results = $importer->import($filePath);

            // Build message
            $message = sprintf(
                'Import selesai! Berhasil: %d, Diupdate: %d',
                $results['success'],
                $results['updated']
            );

            if (!empty($results['errors'])) {
                $message .= sprintf('. Error: %d', count($results['errors']));
                // Log errors for debugging
                \Log::info('Import errors:', $results['errors']);
            }

            // If nothing was imported, show warning
            if ($results['success'] == 0 && $results['updated'] == 0) {
                return redirect()->route('master.perusahaan.index')
                    ->with('warning', 'Tidak ada data yang diimport. Pastikan format Excel sesuai (BP Code di kolom A, BP Name di kolom B, Alamat di kolom E)');
            }

            return redirect()->route('master.perusahaan.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }
}

