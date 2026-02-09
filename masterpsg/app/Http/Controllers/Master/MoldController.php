<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MMold;
use App\Models\MPerusahaan;
use App\Models\SMPart;
use Illuminate\Http\Request;

class MoldController extends Controller
{
    public function index(Request $request)
    {
        if (!userCan('master.mold.view')) abort(403);

        $query = MMold::query()->with(['perusahaan', 'part']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_mold', 'like', "%{$search}%")
                  ->orWhere('nomor_mold', 'like', "%{$search}%")
                  ->orWhere('mold_id', 'like', "%{$search}%");
            });
        }
        
        // Filter Tipe Mold
        if ($request->has('tipe_mold') && $request->tipe_mold) {
            $query->where('tipe_mold', $request->tipe_mold);
        }

        // Filter Status
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $molds = $query->paginate($perPage)->withQueryString();

        return view('submaster.mold.mold', compact('molds'));
    }

    public function create()
    {
        if (!userCan('master.mold.create')) {
            abort(403, 'Unauthorized action.');
        }

        $perusahaans = MPerusahaan::active()->where('jenis_perusahaan', 'Customer')
            ->orderBy('nama_perusahaan')
            ->get();
        
        $partsRaw = SMPart::select('id', 'nomor_part', 'nama_part', 'tipe_id')
            ->orderBy('nomor_part')
            ->get();

        $partsData = $partsRaw->map(function($part) {
            return [
                'id' => $part->id,
                'label' => $part->nomor_part . ' - ' . $part->nama_part,
                'tipe_id' => $part->tipe_id
            ];
        });

        return view('submaster.mold.create', compact('perusahaans', 'partsData'));
    }

    public function store(Request $request)
    {
        if (!userCan('master.mold.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'perusahaan_id' => 'required|exists:M_Perusahaan,id',
            'part_id' => 'required|exists:SM_Part,id',
            'kode_mold' => 'nullable|string|max:100',
            'nomor_mold' => 'nullable|string|max:50',
            'cavity' => 'required|integer|min:1',
            'cycle_time' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'lokasi_mold' => 'nullable|in:internal,external',
            'tipe_mold' => 'nullable|in:single,family',
            'material_resin' => 'nullable|string|max:100',
            'warna_produk' => 'nullable|in:putih,kuning,merah,biru,hijau,hitam,buram',
        ]);

        // Auto-generate Mold ID: nama_part|tipe_part|kode_mold
        $part = SMPart::find($validated['part_id']);
        $namaPart = $part ? $part->nama_part : '';
        $tipePart = $part ? $part->tipe_id : '';

        // Fallback to parent part tipe_id if empty (e.g. for ASSY parts)
        if (empty($tipePart) && $part && $part->parentPart) {
            $tipePart = $part->parentPart->tipe_id;
        }

        $kodeMold = $validated['kode_mold'] ?? '';
        
        $validated['mold_id'] = $namaPart . '|' . $tipePart . '|' . $kodeMold;

        MMold::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data mold berhasil ditambahkan',
        ]);
    }

    public function show($id)
    {
        $mold = MMold::with(['perusahaan', 'part'])->findOrFail($id);
        return response()->json($mold);
    }

    public function detail($id)
    {
        $mold = MMold::with(['perusahaan', 'part'])->findOrFail($id);
        return view('submaster.mold.detail', compact('mold'));
    }

    public function edit($id)
    {
        if (!userCan('master.mold.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $mold = MMold::findOrFail($id);
        $perusahaans = MPerusahaan::active()->where('jenis_perusahaan', 'Customer')
            ->orderBy('nama_perusahaan')
            ->get();
        $parts = SMPart::orderBy('nomor_part')->get();

        return view('submaster.mold.edit', compact('mold', 'perusahaans', 'parts'));
    }

    public function update(Request $request, $id)
    {
        if (!userCan('master.mold.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $mold = MMold::findOrFail($id);
        
        $validated = $request->validate([
            'mold_id' => 'nullable|string|max:50',
            'perusahaan_id' => 'required|exists:M_Perusahaan,id',
            'part_id' => 'required|exists:SM_Part,id',
            'kode_mold' => 'nullable|string|max:100',
            'nomor_mold' => 'nullable|string|max:50',
            'cavity' => 'required|integer|min:1',
            'cycle_time' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'lokasi_mold' => 'nullable|in:internal,external',
            'tipe_mold' => 'nullable|in:single,family',
            'material_resin' => 'nullable|string|max:100',
            'warna_produk' => 'nullable|in:putih,kuning,merah,biru,hijau,hitam,buram',
        ]);

        $mold->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data mold berhasil diupdate',
        ]);
    }

    public function delete($id)
    {
        if (!userCan('master.mold.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $mold = MMold::with(['perusahaan', 'part'])->findOrFail($id);
        return view('submaster.mold.delete', compact('mold'));
    }

    public function destroy($id)
    {
        if (!userCan('master.mold.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $mold = MMold::findOrFail($id);
        $mold->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data mold berhasil dihapus',
            ]);
        }
        return redirect()->route('master.mold.index')->with('success', 'Data mold berhasil dihapus');
    }

    // Trash & Restore
    public function trash() {
        if (!userCan('master.mold.delete')) abort(403);
        $trashed = MMold::onlyTrashed()->with(['perusahaan', 'part'])->orderBy('deleted_at', 'desc')->paginate(10);
        return view('submaster.mold.trash', compact('trashed'));
    }

    public function restore($id) {
        if (!userCan('master.mold.delete')) abort(403);
        $mold = MMold::onlyTrashed()->findOrFail($id);
        $mold->restore();
        return response()->json(['success' => true, 'message' => 'Data berhasil dipulihkan']);
    }

    public function restoreAll() {
        if (!userCan('master.mold.delete')) abort(403);
        MMold::onlyTrashed()->restore();
        return back()->with('success', 'Semua data berhasil dipulihkan');
    }

    // Status Toggle
    public function toggleStatus($id) {
        if (!userCan('master.mold.edit')) abort(403);
        try {
            $mold = MMold::findOrFail($id);
            $mold->status = !$mold->status;
            $mold->save();
            return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $mold->status]);
        } catch (\Exception $e) { 
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500); 
        }
    }

    // Bulk Delete
    public function bulkDelete(Request $request) {
        if (!userCan('master.mold.delete')) abort(403);
        $request->validate(['ids' => 'required|array']);
        MMold::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }

    // Force Delete (Permanent)
    public function forceDelete($id) {
        if (!userCan('master.mold.delete')) abort(403);
        $mold = MMold::onlyTrashed()->findOrFail($id);
        $mold->forceDelete();
        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus permanen']);
    }

    // Empty Trash (Force Delete All)
    public function forceDeleteAll() {
        if (!userCan('master.mold.delete')) abort(403);
        MMold::onlyTrashed()->forceDelete();
        return back()->with('success', 'Semua data di sampah berhasil dihapus permanen');
    }

    // Destroy All (Move all to trash)
    public function destroyAll() {
        if (!userCan('master.mold.delete')) abort(403);
        MMold::query()->delete();
        return back()->with('success', 'Semua data berhasil dipindahkan ke sampah');
    }

    // Export
    public function export() {
        if (!userCan('master.mold.view')) abort(403);
        
        $molds = MMold::with(['perusahaan', 'part'])->get();
        
        $filename = 'mold_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($molds) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Mold ID', 'Kode Mold', 'Nomor Mold', 'Perusahaan', 'Part', 
                'Cavity', 'Cycle Time', 'Capacity', 'Lokasi Mold', 'Tipe Mold', 
                'Material Resin', 'Warna Produk', 'Status'
            ]);
            
            // Data
            foreach ($molds as $mold) {
                fputcsv($file, [
                    $mold->mold_id ?? '',
                    $mold->kode_mold ?? '',
                    $mold->nomor_mold ?? '',
                    $mold->perusahaan->nama_perusahaan ?? '',
                    ($mold->part ? $mold->part->nomor_part . ' - ' . $mold->part->nama_part : ''),
                    $mold->cavity ?? '',
                    $mold->cycle_time ?? '',
                    $mold->capacity ?? '',
                    $mold->lokasi_mold ?? '',
                    $mold->tipe_mold ?? '',
                    $mold->material_resin ?? '',
                    $mold->warna_produk ?? '',
                    $mold->status ? 'Active' : 'Inactive'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Show Import Form
    public function showImportForm() {
        if (!userCan('master.mold.create')) abort(403);
        return view('submaster.mold.import');
    }

    // Import
    public function import(Request $request) {
        if (!userCan('master.mold.create')) abort(403);
        
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'start_row' => 'required|integer|min:1',
            'col_kode_mold' => 'required|string',
            'col_perusahaan' => 'required|string',
            'col_part' => 'required|string',
            'col_cavity' => 'required|string',
        ]);

        try {
            $file = $request->file('file');
            $startRow = (int) $request->start_row;
            
            // Column mapping
            $colMap = [
                'kode_mold' => strtoupper(trim($request->col_kode_mold)),
                'perusahaan' => strtoupper(trim($request->col_perusahaan)),
                'part' => strtoupper(trim($request->col_part)),
                'cavity' => strtoupper(trim($request->col_cavity)),
                'nomor_mold' => $request->col_nomor_mold ? strtoupper(trim($request->col_nomor_mold)) : null,
                'cycle_time' => $request->col_cycle_time ? strtoupper(trim($request->col_cycle_time)) : null,
                'capacity' => $request->col_capacity ? strtoupper(trim($request->col_capacity)) : null,
                'lokasi_mold' => $request->col_lokasi_mold ? strtoupper(trim($request->col_lokasi_mold)) : null,
                'tipe_mold' => $request->col_tipe_mold ? strtoupper(trim($request->col_tipe_mold)) : null,
                'material_resin' => $request->col_material_resin ? strtoupper(trim($request->col_material_resin)) : null,
                'warna_produk' => $request->col_warna_produk ? strtoupper(trim($request->col_warna_produk)) : null,
            ];

            $handle = fopen($file->getRealPath(), 'r');
            $rowNumber = 0;
            $imported = 0;
            $errors = [];

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;
                
                // Skip rows before start_row
                if ($rowNumber < $startRow) continue;

                try {
                    // Get values from columns
                    $kodeMold = $this->getColumnValue($row, $colMap['kode_mold']);
                    $perusahaanName = $this->getColumnValue($row, $colMap['perusahaan']);
                    $partNumber = $this->getColumnValue($row, $colMap['part']);
                    $cavity = $this->getColumnValue($row, $colMap['cavity']);

                    // Skip empty rows
                    if (empty($kodeMold) && empty($perusahaanName) && empty($partNumber)) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($kodeMold)) {
                        $errors[] = "Baris {$rowNumber}: Kode Mold kosong";
                        continue;
                    }

                    // Find Perusahaan
                    $perusahaan = null;
                    if (!empty($perusahaanName)) {
                        $perusahaan = MPerusahaan::where('nama_perusahaan', 'like', "%{$perusahaanName}%")
                            ->orWhere('inisial_perusahaan', 'like', "%{$perusahaanName}%")
                            ->first();
                    }

                    // Find Part
                    $part = null;
                    if (!empty($partNumber)) {
                        $part = SMPart::where('nomor_part', 'like', "%{$partNumber}%")
                            ->orWhere('nama_part', 'like', "%{$partNumber}%")
                            ->first();
                    }

                    // Prepare data
                    $data = [
                        'kode_mold' => $kodeMold,
                        'perusahaan_id' => $perusahaan ? $perusahaan->id : null,
                        'part_id' => $part ? $part->id : null,
                        'cavity' => !empty($cavity) ? (int)$cavity : 1,
                        'status' => true,
                    ];

                    // Optional fields
                    if ($colMap['nomor_mold']) {
                        $data['nomor_mold'] = $this->getColumnValue($row, $colMap['nomor_mold']);
                    }
                    if ($colMap['cycle_time']) {
                        $cycleTime = $this->getColumnValue($row, $colMap['cycle_time']);
                        $data['cycle_time'] = !empty($cycleTime) ? (float)$cycleTime : null;
                    }
                    if ($colMap['capacity']) {
                        $capacity = $this->getColumnValue($row, $colMap['capacity']);
                        $data['capacity'] = !empty($capacity) ? (int)$capacity : null;
                    }
                    if ($colMap['lokasi_mold']) {
                        $data['lokasi_mold'] = strtolower($this->getColumnValue($row, $colMap['lokasi_mold']));
                    }
                    if ($colMap['tipe_mold']) {
                        $data['tipe_mold'] = strtolower($this->getColumnValue($row, $colMap['tipe_mold']));
                    }
                    if ($colMap['material_resin']) {
                        $data['material_resin'] = $this->getColumnValue($row, $colMap['material_resin']);
                    }
                    if ($colMap['warna_produk']) {
                        $data['warna_produk'] = strtolower($this->getColumnValue($row, $colMap['warna_produk']));
                    }

                    // Auto-generate Mold ID
                    $namaPart = $part ? $part->nama_part : '';
                    $tipePart = $part ? $part->tipe_id : '';
                    if (empty($tipePart) && $part && $part->parentPart) {
                        $tipePart = $part->parentPart->tipe_id;
                    }
                    $data['mold_id'] = $namaPart . '|' . $tipePart . '|' . $kodeMold;

                    // Create or update
                    MMold::updateOrCreate(
                        ['kode_mold' => $kodeMold],
                        $data
                    );

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            fclose($handle);

            $message = "Import selesai! {$imported} data berhasil diimport.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " baris gagal.";
            }

            return back()->with('success', $message)->with('import_errors', $errors);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Helper: Convert column letter to index (A=0, B=1, AA=26, etc)
    private function getColumnValue($row, $columnLetter) {
        if (empty($columnLetter)) return null;
        
        $columnLetter = strtoupper(trim($columnLetter));
        $index = 0;
        $length = strlen($columnLetter);
        
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($columnLetter[$i]) - ord('A') + 1);
        }
        
        $index--; // Convert to 0-based index
        
        return isset($row[$index]) ? trim($row[$index]) : null;
    }
}
