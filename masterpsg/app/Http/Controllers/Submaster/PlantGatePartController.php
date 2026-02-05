<?php

namespace App\Http\Controllers\Submaster;

use App\Http\Controllers\Controller;
use App\Models\SMPlantGatePart;
use App\Models\MPlantGate;
use App\Models\SMPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantGatePartController extends Controller
{
    public function index(Request $request)
    {
        $query = SMPlantGatePart::with(['plantgate.customer', 'part']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%");
            })->orWhereHas('plantgate', function($q) use ($search) {
                $q->where('nama_plantgate', 'like', "%{$search}%");
            });
        }

        // Filter by PlantGate
        if ($request->filled('plantgate_id')) {
            $query->where('plantgate_id', $request->plantgate_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $plantgateParts = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // For filters
        $plantgates = MPlantGate::with('customer')->orderBy('nama_plantgate')->get();

        return view('submaster.plantgatepart.plantgatepart', compact('plantgateParts', 'plantgates'));
    }

    public function create()
    {
        if (!userCan('submaster.plantgatepart.create')) {
            abort(403, 'Unauthorized action.');
        }

        $plantgates = MPlantGate::with('customer')
            ->orderBy('nama_plantgate')
            ->get();
            
        // Filter: Hanya part yang BUKAN parent (tidak punya child)
        $partsRaw = SMPart::doesntHave('childParts')
            ->select('id', 'nomor_part', 'nama_part')
            ->orderBy('nomor_part')
            ->get();

        $partsData = $partsRaw->map(function($part) {
            return [
                'id' => $part->id,
                'label' => $part->nomor_part . ' - ' . $part->nama_part
            ];
        });

        return view('submaster.plantgatepart.create', compact('plantgates', 'partsData'));
    }

    public function store(Request $request)
    {
        if (!userCan('submaster.plantgatepart.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'part_id' => 'required|exists:SM_Part,id',
            'plantgate_ids' => 'required|array',
            'plantgate_ids.*' => 'exists:M_PlantGate,id',
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $duplicates = 0;

            foreach ($validated['plantgate_ids'] as $plantgateId) {
                // Check if exists
                $exists = SMPlantGatePart::where('plantgate_id', $plantgateId)
                    ->where('part_id', $validated['part_id'])
                    ->exists();

                if (!$exists) {
                    SMPlantGatePart::create([
                        'part_id' => $validated['part_id'],
                        'plantgate_id' => $plantgateId,
                        'status' => true,
                    ]);
                    $successCount++;
                } else {
                    $duplicates++;
                }
            }

            DB::commit();

            $message = "Berhasil menambahkan $successCount gate ke part ini.";
            if ($duplicates > 0) {
                $message .= " ($duplicates dilewati karena sudah ada)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $plantgatePart = SMPlantGatePart::with(['plantgate.customer', 'part'])->findOrFail($id);
        return response()->json($plantgatePart);
    }

    public function detail($id)
    {
        $plantgatePart = SMPlantGatePart::with(['plantgate.customer', 'part'])->findOrFail($id);
        return view('submaster.plantgatepart.detail', compact('plantgatePart'));
    }

    public function edit($id)
    {
        if (!userCan('submaster.plantgatepart.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $plantgatePart = SMPlantGatePart::findOrFail($id);
        
        $plantgates = MPlantGate::with('customer')
            ->orderBy('nama_plantgate')
            ->get();
            
        // Filter: Hanya part yang BUKAN parent
        $partsRaw = SMPart::doesntHave('childParts')
            ->select('id', 'nomor_part', 'nama_part')
            ->orderBy('nomor_part')
            ->get();

        $partsData = $partsRaw->map(function($part) {
            return [
                'id' => $part->id,
                'label' => $part->nomor_part . ' - ' . $part->nama_part
            ];
        });

        // Ambil ID gate yang saat ini sudah terhubung dengan part ini
        $currentGateIds = SMPlantGatePart::where('part_id', $plantgatePart->part_id)
            ->pluck('plantgate_id')
            ->toArray();

        return view('submaster.plantgatepart.edit', compact('plantgatePart', 'plantgates', 'partsData', 'currentGateIds'));
    }

    public function update(Request $request, $id)
    {
        if (!userCan('submaster.plantgatepart.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $plantgatePart = SMPlantGatePart::findOrFail($id);

        $validated = $request->validate([
            'part_id' => 'required|exists:SM_Part,id',
            'plantgate_ids' => 'required|array',
            'plantgate_ids.*' => 'exists:M_PlantGate,id',
        ]);

        try {
            DB::beginTransaction();

            // SINKRONISASI:
            // 1. Hapus semua relasi lama untuk part ini secara permanen agar tidak konflik dengan unique index
            SMPlantGatePart::where('part_id', $validated['part_id'])->forceDelete();

            // 2. Tambahkan semua relasi yang dipilih
            foreach ($validated['plantgate_ids'] as $gateId) {
                SMPlantGatePart::create([
                    'part_id' => $validated['part_id'],
                    'plantgate_id' => $gateId,
                    'status' => true,
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Relasi plant gate part berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        if (!userCan('submaster.plantgatepart.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $plantgatePart = SMPlantGatePart::with(['plantgate.customer', 'part'])->findOrFail($id);
        return view('submaster.plantgatepart.delete', compact('plantgatePart'));
    }

    public function destroy($id)
    {
        if (!userCan('submaster.plantgatepart.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $plantgatePart = SMPlantGatePart::findOrFail($id);

        try {
            DB::transaction(function () use ($plantgatePart) {
                $plantgatePart->delete();
            });

            return redirect()->route('submaster.plantgatepart.index')
                ->with('success', 'Data plant gate part berhasil dihapus');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Toggle Status
    public function toggleStatus($id)
    {
        if (!userCan('submaster.plantgatepart.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $plantgatePart = SMPlantGatePart::findOrFail($id);
            $plantgatePart->status = !$plantgatePart->status;
            $plantgatePart->save();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'status' => $plantgatePart->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Trash & Restore
    public function trash()
    {
        $trashed = SMPlantGatePart::onlyTrashed()
            ->with(['plantgate.customer', 'part'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('submaster.plantgatepart.trash', compact('trashed'));
    }

    public function restore($id)
    {
        if (!userCan('submaster.plantgatepart.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $plantgatePart = SMPlantGatePart::onlyTrashed()->findOrFail($id);
            $plantgatePart->restore();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dipulihkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restoreAll()
    {
        if (!userCan('submaster.plantgatepart.delete')) {
            abort(403, 'Unauthorized action.');
        }

        SMPlantGatePart::onlyTrashed()->restore();
        return redirect()->route('submaster.plantgatepart.trash')
            ->with('success', 'Semua data berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        if (!userCan('submaster.plantgatepart.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $plantgatePart = SMPlantGatePart::onlyTrashed()->findOrFail($id);
            $plantgatePart->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus permanen'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function emptyTrash()
    {
        if (!userCan('submaster.plantgatepart.delete')) {
            abort(403, 'Unauthorized action.');
        }

        SMPlantGatePart::onlyTrashed()->forceDelete();
        return redirect()->route('submaster.plantgatepart.trash')
            ->with('success', 'Sampah berhasil dikosongkan');
    }

    // Import & Export
    public function importPage()
    {
        if (!userCan('submaster.plantgatepart.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('submaster.plantgatepart.import');
    }

    public function export(Request $request)
    {
        if (!userCan('submaster.plantgatepart.create')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SMPlantGatePart::with(['plantgate.customer', 'part']);

        // Apply filters (same as index)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%");
            })->orWhereHas('plantgate', function($q) use ($search) {
                $q->where('nama_plantgate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('plantgate_id')) {
            $query->where('plantgate_id', $request->plantgate_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        $filename = "plantgate_part_export_" . date('Ymd_His') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Plant Gate', 'Customer', 'Nomor Part', 'Nama Part', 'Status', 'Created At'];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->plantgate->nama_plantgate ?? '-',
                    $item->plantgate->customer->nama_perusahaan ?? '-',
                    $item->part->nomor_part ?? '-',
                    $item->part->nama_part ?? '-',
                    $item->status ? 'Active' : 'Inactive',
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        if (!userCan('submaster.plantgatepart.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
            'start_row' => 'required|integer|min:1',
            'col_customer' => 'required|string',
            'col_plantgate' => 'required|string',
            'col_part' => 'required|string',
        ]);

        try {
            $file = $request->file('file');
            $startRow = (int) $request->start_row;
            
            // Column mapping
            $colCustomer = strtoupper(trim($request->col_customer));
            $colPlantGate = strtoupper(trim($request->col_plantgate));
            $colPart = strtoupper(trim($request->col_part));

            $handle = fopen($file->getRealPath(), 'r');
            if (!$handle) {
                throw new \Exception('Tidak dapat membuka file');
            }

            $currentRow = 1;
            $imported = 0;
            $skipped = 0;
            $errors = [];

            while (($data = fgetcsv($handle)) !== false) {
                if ($currentRow < $startRow) {
                    $currentRow++;
                    continue;
                }

                try {
                    // Get values from columns
                    $customerName = $this->getColumnValue($data, $colCustomer);
                    $plantgateName = $this->getColumnValue($data, $colPlantGate);
                    $partNumber = $this->getColumnValue($data, $colPart);

                    if (empty($customerName) || empty($plantgateName) || empty($partNumber)) {
                        $skipped++;
                        continue;
                    }

                    // Find PlantGate
                    $plantgate = MPlantGate::whereHas('customer', function($q) use ($customerName) {
                        $q->where('nama_perusahaan', 'like', "%{$customerName}%");
                    })->where('nama_plantgate', 'like', "%{$plantgateName}%")->first();

                    if (!$plantgate) {
                        $errors[] = "Baris {$currentRow}: PlantGate '{$plantgateName}' untuk customer '{$customerName}' tidak ditemukan";
                        $skipped++;
                        $currentRow++;
                        continue;
                    }

                    // Find Part
                    $part = SMPart::where('nomor_part', $partNumber)->first();
                    if (!$part) {
                        $errors[] = "Baris {$currentRow}: Part '{$partNumber}' tidak ditemukan";
                        $skipped++;
                        $currentRow++;
                        continue;
                    }

                    // Check if exists
                    $exists = SMPlantGatePart::where('plantgate_id', $plantgate->id)
                        ->where('part_id', $part->id)
                        ->exists();

                    if (!$exists) {
                        SMPlantGatePart::create([
                            'plantgate_id' => $plantgate->id,
                            'part_id' => $part->id,
                            'status' => true,
                        ]);
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Baris {$currentRow}: " . $e->getMessage();
                    $skipped++;
                }

                $currentRow++;
            }

            fclose($handle);

            $message = "Import selesai. Berhasil: {$imported}, Dilewati: {$skipped}";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 5));
            }

            return redirect()->route('submaster.plantgatepart.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    private function getColumnValue($row, $column)
    {
        $index = $this->columnToIndex($column);
        return isset($row[$index]) ? trim($row[$index]) : null;
    }

    private function columnToIndex($column)
    {
        $column = strtoupper($column);
        $index = 0;
        $length = strlen($column);
        
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        
        return $index - 1;
    }
}
