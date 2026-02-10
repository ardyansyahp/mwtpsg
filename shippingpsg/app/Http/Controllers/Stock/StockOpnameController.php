<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\SMPart;
use App\Models\TStockFG;
use App\Models\TStockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        // Get all parts with their current stock
        // We join to ensure we get parts even if they don't have stock record (default 0)
        $stocks = SMPart::with(['customer', 'stockFg', 'latestStockOpname'])
            ->when($request->search, function($q) use ($request) {
                $q->where('nomor_part', 'like', "%{$request->search}%")
                  ->orWhere('nama_part', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function($sq) use ($request) {
                      $sq->where('nama_perusahaan', 'like', "%{$request->search}%")
                        ->orWhere('inisial_perusahaan', 'like', "%{$request->search}%");
                  });
            })
            ->orderBy('nomor_part')
            ->paginate($request->get('per_page', 15));

        return view('stock.opname.index', compact('stocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'opname_data' => 'required|array',
            'opname_data.*.part_id' => 'required|exists:sm_part,id',
            'opname_data.*.qty_actual' => 'required|integer|min:0',
            'opname_data.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            // Resolve Manpower ID from session
            $manpowerString = session('user_id');
            $manpowerId = null;
            if ($manpowerString) {
                $mp = \App\Models\MManpower::where('mp_id', $manpowerString)->first();
                if ($mp) {
                    $manpowerId = $mp->id;
                }
            }

            foreach ($request->opname_data as $data) {
                // Get fresh system stock
                $stockFg = TStockFG::firstOrNew(['part_id' => $data['part_id']]);
                $systemQty = $stockFg->qty ?? 0;
                $actualQty = $data['qty_actual'];
                
                // Check if there is already an STO record for this part in the current month
                $existingSto = TStockOpname::where('part_id', $data['part_id'])
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->first();

                if ($existingSto) {
                    // Revision
                    $existingSto->update([
                        'qty_actual' => $actualQty,
                        'diff' => $actualQty - $existingSto->qty_system, 
                        'keterangan' => $data['keterangan'] ?? 'Revisi STO (' . now()->format('d M') . ')',
                        'manpower_id' => $manpowerId
                    ]);
                } else {
                    // New STO
                    $diff = $actualQty - $systemQty;

                    TStockOpname::create([
                        'part_id' => $data['part_id'],
                        'qty_system' => $systemQty,
                        'qty_actual' => $actualQty,
                        'diff' => $diff,
                        'manpower_id' => $manpowerId, 
                        'keterangan' => $data['keterangan'] ?? 'Manual Opname',
                    ]);
                }

                $stockFg->qty = $actualQty;
                $stockFg->save();
            }
        });

        return redirect()->back()->with('success', 'Stock Opname berhasil disimpan.');
    }
    public function importForm()
    {
        return view('stock.opname.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'start_row' => 'required|integer|min:1',
        ]);

        try {
            $file = $request->file('file');
            $mapping = $request->except(['_token', 'file', 'start_row']);
            $startRow = $request->input('start_row', 2);
            $mapping['col_nomor_part'] = $request->col_nomor_part;
            $mapping['col_qty_actual'] = $request->col_qty_actual;
            $mapping['col_keterangan'] = $request->col_keterangan;

            $handle = fopen($file->getRealPath(), "r");
            if ($handle === FALSE) {
                throw new \Exception("Gagal membuka file.");
            }

            $stats = [
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            $rowIndex = 0;
            DB::beginTransaction();

            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $rowIndex++;
                if ($rowIndex < $startRow) continue;

                // Check empty row
                if (empty(array_filter($row, function($value) { return $value !== null && trim($value) !== ''; }))) {
                    continue;
                }

                try {
                    // Extract Data
                    $nomorPart = $this->getValue($row, $mapping, 'col_nomor_part');
                    $qtyActual = $this->getValue($row, $mapping, 'col_qty_actual');
                    $keterangan = $this->getValue($row, $mapping, 'col_keterangan');

                    if (!$nomorPart) throw new \Exception("Nomor Part kosong.");
                    if ($qtyActual === null || $qtyActual === '') throw new \Exception("Qty Actual kosong.");

                    // Find Part
                    $part = SMPart::where('nomor_part', $nomorPart)->first();
                    if (!$part) throw new \Exception("Part tidak ditemukan: $nomorPart");

                    // Clean Qty
                    $qtyActual = (int) str_replace(['.', ','], '', $qtyActual);

                    // Update Logic
                    $stockFg = TStockFG::firstOrNew(['part_id' => $part->id]);
                    $systemQty = $stockFg->qty ?? 0;
                    
                    // Resolve Manpower ID (Integer)
                    $manpowerString = session('user_id');
                    $manpowerId = null;
                    if ($manpowerString) {
                         $mp = \App\Models\MManpower::where('mp_id', $manpowerString)->first();
                         if ($mp) {
                             $manpowerId = $mp->id;
                         }
                    }

                    // Check for existing STO in current month
                    $existingSto = TStockOpname::where('part_id', $part->id)
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->first();

                    if ($existingSto) {
                        // Revision: Update existing month record
                        $existingSto->update([
                            'qty_actual' => $qtyActual,
                            'diff' => $qtyActual - $existingSto->qty_system,
                            'keterangan' => $keterangan ?? 'Revisi Import (' . now()->format('d M') . ')',
                            'manpower_id' => $manpowerId
                        ]);
                    } else {
                        // New STO for this month
                        $diff = $qtyActual - $systemQty;

                        TStockOpname::create([
                            'part_id' => $part->id,
                            'qty_system' => $systemQty,
                            'qty_actual' => $qtyActual,
                            'diff' => $diff,
                            'manpower_id' => $manpowerId,
                            'keterangan' => $keterangan ?? 'Import CSV',
                        ]);
                    }

                    // Update Stock
                    $stockFg->qty = $qtyActual;
                    $stockFg->save();

                    $stats['success']++;

                } catch (\Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = "Baris $rowIndex: " . $e->getMessage();
                }
            }

            fclose($handle);
            DB::commit();

            if (!empty($stats['errors'])) {
                $msg = 'Import Selesai dengan beberapa error. ' . count($stats['errors']) . ' baris bermasalah.';
                return redirect()->back()
                    ->with('warning', $msg . " Berhasil: {$stats['success']} items.")
                    ->with('import_errors', $stats['errors']);
            }

            return redirect()->route('stock.opname.index')->with('success', "Import Berhasil! Stok updated: {$stats['success']} items.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $fileName = 'stock_opname_' . date('Y-m-d_H-i-s') . '.csv';
        
        $stocks = SMPart::with(['customer', 'stockFg'])
            ->when($request->search, function($q) use ($request) {
                $q->where('nomor_part', 'like', "%{$request->search}%")
                  ->orWhere('nama_part', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function($sq) use ($request) {
                      $sq->where('nama_perusahaan', 'like', "%{$request->search}%")
                        ->orWhere('inisial_perusahaan', 'like', "%{$request->search}%");
                  });
            })
            ->orderBy('nomor_part')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nomor Part', 'Nama Part', 'Customer', 'Model', 'Current Stock (System)', 'Qty Actual (Isi Disini)', 'Keterangan'];

        $callback = function() use($stocks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($stocks as $item) {
                fputcsv($file, [
                    $item->nomor_part,
                    $item->nama_part,
                    $item->customer->nama_perusahaan ?? '-',
                    $item->model_part,
                    $item->stockFg->qty ?? 0,
                    '', // Empty for user to fill
                    ''  // Empty remarks
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --- Helper Methods ---

    private function getValue($row, $mapping, $key)
    {
        if (empty($mapping[$key])) return null;
        
        $colLetter = strtoupper($mapping[$key]);
        $index = $this->columnIndexFromString($colLetter) - 1;
        
        return isset($row[$index]) ? trim($row[$index]) : null;
    }

    private function columnIndexFromString($pString)
    {
        $pString = strtoupper($pString);
        $len = strlen($pString);
        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result = $result * 26 + (ord($pString[$i]) - 64);
        }
        return $result;
    }
}
