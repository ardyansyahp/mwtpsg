<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MMesin;
use Illuminate\Http\Request;

class MesinController extends Controller
{
    private function buildMesinQrcode(MMesin $m): string
    {
        $seed = strtoupper(trim($m->no_mesin ?: (string) $m->id));
        $seed = preg_replace('/[^A-Z0-9]/', '', $seed) ?: (string) $m->id;
        $base = sprintf('MES-%s', $seed);
        $candidate = substr($base, 0, 255);
        $suffix = 1;
        while (MMesin::where('qrcode', $candidate)->exists()) {
            $suffix++;
            $candidate = substr($base, 0, 245) . '-' . $suffix;
        }
        return $candidate;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MMesin::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_mesin', 'like', "%{$search}%")
                  ->orWhere('merk_mesin', 'like', "%{$search}%")
                  ->orWhere('tonase', 'like', "%{$search}%")
                  ->orWhere('qrcode', 'like', "%{$search}%");
            });
        }



        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['no_mesin', 'merk_mesin', 'tonase', 'status', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts)) {
            $sortColumn = 'created_at';
        }
        
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $mesins = $query->paginate($perPage)->onEachSide(1);
        $mesins->appends($request->all());

        return view('master.mesin.mesin', compact('mesins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!userCan('master.mesin.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.mesin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!userCan('master.mesin.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Check trash
        $existingTrash = MMesin::onlyTrashed()
            ->where('no_mesin', $request->no_mesin)
            ->first();

        if ($existingTrash) {
             return response()->json([
                'success' => false,
                'message' => 'Data dengan No Mesin tersebut sudah ada di SAMPAH. Silahkan restore data tersebut.'
            ], 422);
        }

        $validated = $request->validate([
            'no_mesin' => 'required|string|max:50|unique:m_mesin,no_mesin',
            'merk_mesin' => 'nullable|string|max:100',
            'tonase' => 'nullable|integer|min:0',
        ]);

        // Generate ID: NoMesin|Merk|Tonase
        $generatedId = trim($validated['no_mesin']) . '|' . trim($validated['merk_mesin'] ?? '') . '|' . trim($validated['tonase'] ?? '');
        
        $validated['mesin_id'] = $generatedId;
        $validated['qrcode'] = $generatedId; // QR Code same as ID logic or custom

        MMesin::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data mesin berhasil ditambahkan',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!userCan('master.mesin.index')) {
            abort(403, 'Unauthorized action.');
        }

        $mesin = MMesin::withTrashed()->findOrFail($id);

        // Stats placeholder
        $stats = [
            'total_production' => 0,
            'efficiency' => '0%',
            'last_maintenance' => '-'
        ];

        return view('master.mesin.show', compact('mesin', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MMesin $mesin)
    {
        if (!userCan('master.mesin.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.mesin.edit', compact('mesin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MMesin $mesin)
    {
        if (!userCan('master.mesin.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'no_mesin' => 'required|string|max:50|unique:m_mesin,no_mesin,' . $mesin->id,
            'merk_mesin' => 'nullable|string|max:100',
            'tonase' => 'nullable|integer|min:0',
        ]);

        $generatedId = trim($validated['no_mesin']) . '|' . trim($validated['merk_mesin'] ?? '') . '|' . trim($validated['tonase'] ?? '');
        
        $validated['mesin_id'] = $generatedId;
        // Keep existing QR or update logic if needed
        $validated['qrcode'] = $generatedId;

        $mesin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data mesin berhasil diupdate',
        ]);
    }

    public function idcard(MMesin $mesin)
    {
        // Generate qrcode if not exists
        if (empty($mesin->qrcode)) {
            $mesin->update([
                'qrcode' => $this->buildMesinQrcode($mesin),
            ]);
            $mesin->refresh();
        }
        return view('master.mesin.idcard', compact('mesin'));
    }

    /**
     * Show the confirmation form for deleting the specified resource.
     */
    public function delete(MMesin $mesin)
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.mesin.delete', compact('mesin'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MMesin $mesin)
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $mesin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data mesin berhasil dihapus (Soft Delete)',
        ]);
    }

    /**
     * Show Recycle Bin
     */
    public function trash(Request $request)
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $query = MMesin::onlyTrashed();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_mesin', 'like', "%{$search}%")
                  ->orWhere('merk_mesin', 'like', "%{$search}%");
            });
        }

        $mesins = $query->orderBy('deleted_at', 'desc')->paginate(10);

        return view('master.mesin.trash', compact('mesins'));
    }

    public function restore($id)
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $mesin = MMesin::withTrashed()->findOrFail($id);
        $mesin->restore();

        return redirect()->back()->with('success', 'Data mesin berhasil dipulihkan');
    }

    public function restoreAll()
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        MMesin::onlyTrashed()->restore();

        return redirect()->back()->with('success', 'Semua data sampah berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $mesin = MMesin::withTrashed()->findOrFail($id);
        $mesin->forceDelete();

        return redirect()->back()->with('success', 'Data mesin berhasil dihapus permanen');
    }

    public function forceDeleteAll()
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        MMesin::onlyTrashed()->forceDelete();

        return redirect()->back()->with('success', 'Semua sampah berhasil dibersihkan permanen');
    }

    /**
     * Toggle Status Active/Inactive
     */
    public function toggleStatus($id)
    {
        if (!userCan('master.mesin.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $mesin = MMesin::findOrFail($id);
        $mesin->status = !$mesin->status;
        $mesin->save();

        return response()->json([
            'success' => true,
            'message' => 'Status mesin berhasil diupdate',
            'new_status' => $mesin->status
        ]);
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:m_mesin,id'
        ]);

        MMesin::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' data berhasil dihapus'
        ]);
    }

    /**
     * Reset Database (Delete All Active)
     */
    public function destroyAll()
    {
        if (!userCan('master.mesin.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            MMesin::query()->delete(); // Soft delete all
            return redirect()->route('master.mesin.index')
                ->with('success', 'Semua data mesin berhasil dihapus (Soft Delete)');
        } catch (\Exception $e) {
            return redirect()->route('master.mesin.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function showImportForm()
    {
        if (!userCan('master.mesin.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('master.mesin.import');
    }

    public function import(Request $request)
    {
         if (!userCan('master.mesin.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            // Simple generic import logic (replace with specific logic if needed)
            $handle = fopen($path, "r");
            $header = fgetcsv($handle, 1000, ","); 
            
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Assuming format: NoMesin, Merk, Tonase
                if(count($data) >= 3) {
                     MMesin::create([
                        'no_mesin' => $data[0],
                        'merk_mesin' => $data[1],
                        'tonase' => (int)$data[2],
                        'mesin_id' => $data[0].'|'.$data[1].'|'.$data[2],
                        'qrcode' => $data[0].'|'.$data[1].'|'.$data[2],
                        'status' => 1
                     ]);
                     $count++;
                }
            }
            fclose($handle);

            return redirect()->route('master.mesin.index')->with('success', "$count data berhasil diimport");

        } catch (\Exception $e) {
             return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function export()
    {
        if (!userCan('master.mesin.index')) {
            abort(403, 'Unauthorized action.');
        }

        $fileName = 'data_mesin_' . date('Y-m-d_H-i-s') . '.csv';
        $mesins = MMesin::orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No Mesin', 'Merk', 'Tonase', 'QR Code', 'Status', 'Created At'];

        $callback = function() use($mesins, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($mesins as $mesin) {
                $row['no_mesin']  = $mesin->no_mesin;
                $row['merk_mesin']    = $mesin->merk_mesin;
                $row['tonase']    = $mesin->tonase;
                $row['qrcode']  = $mesin->qrcode;
                $row['status']  = $mesin->status ? 'Active' : 'Inactive';
                $row['created_at']  = $mesin->created_at;

                fputcsv($file, array($row['no_mesin'], $row['merk_mesin'], $row['tonase'], $row['qrcode'], $row['status'], $row['created_at']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
