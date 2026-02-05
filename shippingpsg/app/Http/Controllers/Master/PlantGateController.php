<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MPlantGate;
use App\Models\MPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantGateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $customerFilter = $request->input('customer_filter');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = MPlantGate::with('customer')
            ->when($search, function ($q) use ($search) {
                $q->where('nama_plantgate', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($subQ) use ($search) {
                      $subQ->where('nama_perusahaan', 'like', "%{$search}%");
                  });
            })
            ->when($customerFilter, function ($q) use ($customerFilter) {
                 $q->where('customer_id', $customerFilter);
            })
            ->when($sortBy == 'customer', function ($q) use ($sortOrder) {
                // Sorting by relationship column
                 $q->orderBy(MPerusahaan::select('nama_perusahaan')
                    ->whereColumn('m_perusahaan.id', 'm_plantgate.customer_id'), 
                    $sortOrder
                 );
            }, function ($q) use ($sortBy, $sortOrder) {
                $q->orderBy($sortBy, $sortOrder);
            });

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $plantgates = $query->paginate($perPage);
        
        // Populate filter dropdown
        $customers = MPerusahaan::where('jenis_perusahaan', 'Customer')
            ->orderBy('nama_perusahaan')
            ->get();

        return view('master.plantgate.plantgate', compact('plantgates', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!userCan('master.plantgate.create')) {
            abort(403, 'Unauthorized action.');
        }

        $customers = MPerusahaan::active()->where('jenis_perusahaan', 'Customer')
            ->orderBy('nama_perusahaan')
            ->get();

        return view('master.plantgate.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!userCan('master.plantgate.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Check if data exists in trash
        $existingTrash = MPlantGate::onlyTrashed()
            ->where('customer_id', $request->customer_id)
            ->where('nama_plantgate', $request->nama_plantgate)
            ->first();

        if ($existingTrash) {
            return response()->json([
                'success' => false,
                'message' => 'Data Plant Gate ini sudah ada di SAMPAH. Silahkan restore data tersebut.',
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:m_perusahaan,id',
            'nama_plantgate' => 'required|string|max:255',
        ]);

        try {
            MPlantGate::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data plant gate berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MPlantGate $plantgate)
    {
        if (!userCan('master.plantgate.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $customers = MPerusahaan::active()->where('jenis_perusahaan', 'Customer')
            ->orderBy('nama_perusahaan')
            ->get();

        return view('master.plantgate.edit', compact('plantgate', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MPlantGate $plantgate)
    {
        if (!userCan('master.plantgate.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:m_perusahaan,id',
            'nama_plantgate' => 'required|string|max:255',
        ]);

        try {
            $plantgate->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data plant gate berhasil diupdate',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the delete confirmation view.
     */
    public function delete(MPlantGate $plantgate)
    {
        if (!userCan('master.plantgate.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $plantgate->load('customer');
        return view('master.plantgate.delete', compact('plantgate'));
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(MPlantGate $plantgate)
    {
        if (!userCan('master.plantgate.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $plantgate->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dipindahkan ke sampah']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Toggle status.
     */
    public function toggleStatus($id)
    {
        if (!userCan('master.plantgate.edit')) {
            abort(403);
        }

        try {
            $plantgate = MPlantGate::findOrFail($id);
            $plantgate->status = !$plantgate->status;
            $plantgate->save();
            return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $plantgate->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Recycle Bin View
     */
    public function trash(Request $request)
    {
        if (!userCan('master.plantgate.delete')) abort(403);

        $search = $request->input('search');
        $trashed = MPlantGate::onlyTrashed()->with('customer')
            ->when($search, function($q) use ($search) {
                $q->where('nama_plantgate', 'like', "%{$search}%");
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('master.plantgate.trash', compact('trashed'));
    }

    /**
     * Restore Single
     */
    public function restore($id)
    {
        if (!userCan('master.plantgate.delete')) abort(403);
        MPlantGate::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->back()->with('success', 'Data berhasil dipulihkan');
    }

    /**
     * Restore All
     */
    public function restoreAll()
    {
        if (!userCan('master.plantgate.delete')) abort(403);
        MPlantGate::onlyTrashed()->restore();
        return redirect()->back()->with('success', 'Semua data berhasil dipulihkan');
    }

    /**
     * Force Delete Single
     */
    public function forceDelete($id)
    {
        if (!userCan('master.plantgate.delete')) abort(403);
        MPlantGate::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->back()->with('success', 'Data permanen dihapus');
    }

    /**
     * Force Delete All
     */
    public function forceDeleteAll()
    {
        if (!userCan('master.plantgate.delete')) abort(403);
        MPlantGate::onlyTrashed()->forceDelete();
        return redirect()->back()->with('success', 'Semua data sampah berhasil dikosongkan');
    }
    
    /**
     * Reset Database (Delete All Active)
     */
    public function destroyAll()
    {
         if (!userCan('master.plantgate.delete')) abort(403);
         
         DB::transaction(function() {
             MPlantGate::query()->delete(); 
         });
         
         return redirect()->back()->with('success', 'Semua data berhasil dihapus (soft delete)');
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request) {
        if (!userCan('master.plantgate.delete')) abort(403);
        
        $ids = $request->input('ids', []);
        
        if (empty($ids)) return response()->json(['success' => false, 'message' => 'No IDs provided']);
        
        MPlantGate::whereIn('id', $ids)->delete();
        
        return response()->json(['success' => true, 'message' => count($ids) . ' data berhasil dihapus']);
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!userCan('master.plantgate.index')) {
            abort(403, 'Unauthorized action.');
        }

        $plantgate = MPlantGate::with('customer')->withTrashed()->findOrFail($id);
        
        // Dummy stats (placeholder for future relations)
        $stats = [
            'total_parts' => $plantgate->parts()->count(), 
            'last_updated' => $plantgate->updated_at->format('d M Y')
        ];

        return view('master.plantgate.show', compact('plantgate', 'stats'));
    }

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        if (!userCan('master.plantgate.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.plantgate.import');
    }

    /**
     * Import data from CSV/Excel
     */
    public function import(Request $request)
    {
        if (!userCan('master.plantgate.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            $header = array_shift($data); // Remove header row

            $success = 0;
            $failed = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                // Expected format: Customer Name, Plant Gate Name
                if (count($row) < 2) {
                    $failed++;
                    continue;
                }

                $customerName = trim($row[0]);
                $plantGateName = trim($row[1]);

                // Find customer by name
                $customer = MPerusahaan::where('nama_perusahaan', $customerName)
                    ->where('jenis_perusahaan', 'Customer')
                    ->first();

                if (!$customer) {
                    $failed++;
                    $errors[] = "Row " . ($index + 2) . ": Customer '$customerName' not found.";
                    continue;
                }

                // Create or Update
                MPlantGate::updateOrCreate(
                    [
                        'customer_id' => $customer->id, 
                        'nama_plantgate' => $plantGateName
                    ],
                    [
                        'status' => true
                    ]
                );
                $success++;
            }

            $message = "Import selesai. Berhasil: $success, Gagal: $failed.";
            if ($failed > 0) {
                return redirect()->route('master.plantgate.index')->with('warning', $message . ' Cek log atau pastikan format benar (Customer, Nama Plantgate).');
            }

            return redirect()->route('master.plantgate.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Export data to CSV
     */
    public function export()
    {
        if (!userCan('master.plantgate.index')) {
            abort(403, 'Unauthorized action.');
        }

        $fileName = 'data_plantgate_' . date('Y-m-d_H-i-s') . '.csv';
        $plantgates = MPlantGate::with('customer')->orderBy('created_at', 'desc')->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Customer', 'Nama Plantgate', 'Status', 'Tanggal Dibuat');

        $callback = function() use($plantgates, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($plantgates as $index => $pg) {
                $row['No']  = $index + 1;
                $row['Customer']    = $pg->customer->nama_perusahaan ?? '-';
                $row['Nama Plantgate']    = $pg->nama_plantgate;
                $row['Status']  = $pg->status ? 'Active' : 'Inactive';
                $row['Tanggal Dibuat'] = $pg->created_at->format('Y-m-d H:i:s');

                fputcsv($file, array(
                    $row['No'], 
                    $row['Customer'], 
                    $row['Nama Plantgate'], 
                    $row['Status'], 
                    $row['Tanggal Dibuat']
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

