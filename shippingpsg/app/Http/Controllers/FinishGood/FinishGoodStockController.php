<?php

namespace App\Http\Controllers\FinishGood;

use App\Http\Controllers\Controller;
use App\Models\TStockFG;
use App\Models\TFinishGoodIn;
use App\Models\TFinishGoodOut;
use App\Models\SMPart;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinishGoodStockController extends Controller
{
    public function index(Request $request)
    {
        if (!userCan('finishgood.stock.view')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        $partId = $request->query('part_id');
        $search = $request->query('search');
        $period = $request->query('period', 'month'); // Default to 'month'

        // All parts for filter logic
        $allParts = SMPart::select('id', 'nomor_part', 'nama_part', 'customer_id')
                    ->with('customer')
                    ->orderBy('nomor_part')->get();

        // 1. Current Stock Analysis
        $stockQuery = TStockFG::with(['part.customer']);
        
        if ($partId) {
            $stockQuery->where('part_id', $partId);
        } elseif ($search) {
            $stockQuery->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%")
                  ->orWhere('model_part', 'like', "%{$search}%")
                  ->orWhere('tipe_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('nama_perusahaan', 'like', "%{$search}%")
                        ->orWhere('inisial_perusahaan', 'like', "%{$search}%");
                  });
            });
        }
        
        $stocks = $stockQuery->orderBy('qty', 'desc')->get();
        $totalStock = $stocks->sum('qty');
        $totalItems = $stocks->count();

        // 2. Period Data Calculation
        $startDate = $period === 'today' ? Carbon::today() : Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        // Totals for Summary
        $inQuery = TFinishGoodIn::whereBetween('waktu_scan', [$startDate, $endDate]);
        $outQuery = TFinishGoodOut::whereBetween('waktu_scan_out', [$startDate, $endDate]);

        if ($partId) {
            $inQuery->where('part_id', $partId);
            $outQuery->where('part_id', $partId);
        } elseif ($search) {
            $inQuery->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%")
                  ->orWhere('model_part', 'like', "%{$search}%")
                  ->orWhere('tipe_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('nama_perusahaan', 'like', "%{$search}%")
                        ->orWhere('inisial_perusahaan', 'like', "%{$search}%");
                  });
            });
            $outQuery->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%")
                  ->orWhere('model_part', 'like', "%{$search}%")
                  ->orWhere('tipe_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('nama_perusahaan', 'like', "%{$search}%")
                        ->orWhere('inisial_perusahaan', 'like', "%{$search}%");
                  });
            });
        }
        
        $totalIn = $inQuery->sum('qty');
        $totalOut = $outQuery->sum('qty');

        // Opening Stock = Current Stock - (Total In - Total Out)
        $stockAwal = $totalStock - $totalIn + $totalOut;

        // 3. Per-Part In/Out Data for the Table
        $partIds = $stocks->pluck('part_id')->toArray();
        
        $partInMap = TFinishGoodIn::select('part_id', DB::raw('SUM(qty) as total_in'))
            ->whereIn('part_id', $partIds)
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->groupBy('part_id')
            ->pluck('total_in', 'part_id');

        $partOutMap = TFinishGoodOut::select('part_id', DB::raw('SUM(qty) as total_out'))
            ->whereIn('part_id', $partIds)
            ->whereBetween('waktu_scan_out', [$startDate, $endDate])
            ->groupBy('part_id')
            ->pluck('total_out', 'part_id');

        // Attach In/Out data to stocks collection
        foreach ($stocks as $stock) {
            $stock->period_in = $partInMap[$stock->part_id] ?? 0;
            $stock->period_out = $partOutMap[$stock->part_id] ?? 0;
            // Calculate mock opening for the row based on current stock and movement
            $stock->opening_stock = $stock->qty - $stock->period_in + $stock->period_out;
        }

        // 4. Slow Moving Stock (Simple logic: no OUT in last 60 days)
        $activeIds = TFinishGoodOut::where('waktu_scan_out', '>=', Carbon::now()->subDays(60))->distinct()->pluck('part_id');
        $slowMoving = TStockFG::with('part')->where('qty', '>', 0)->whereNotIn('part_id', $activeIds)->orderBy('qty', 'desc')->limit(5)->get();

        return view('finishgood.stock', [
            'parts' => $allParts,
            'selectedPartId' => $partId,
            'summary' => (object)[
                'total_items_count' => $totalItems,
                'stock_awal' => $stockAwal,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'total_units' => $totalStock,
                'capacity' => 500000,
                'weeks' => 0, 
                'inventory' => $totalStock,
                'avg_weekly_sales' => 0,
                'total_value' => 0
            ],
            'charts' => (object)[
                'posP' => [],
                'byDept' => collect([]),
                'deptLabels' => [],
                'deptValues' => []
            ],
            'slowMoving' => $slowMoving,
            'stocks' => $stocks,
        ]);
    }
    public function updateLimits(Request $request, SMPart $part)
    {
        if (!userCan('finishgood.stock.view')) { // Assuming edit permission is same as view for now, or use loose check
            abort(403);
        }

        $request->validate([
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0|gte:min_stock',
        ]);

        $part->update([
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
        ]);

        return back()->with('success', 'Stock limits updated successfully for ' . $part->nomor_part);
    }

    public function importForm()
    {
        if (!userCan('finishgood.stock.view')) {
            abort(403);
        }

        return view('finishgood.stock.import');
    }

    public function import(Request $request)
    {
        if (!userCan('finishgood.stock.view')) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'start_row' => 'required|integer|min:1',
        ]);

        try {
            $file = $request->file('file');
            $mapping = $request->except(['_token', 'file', 'start_row']);
            $startRow = $request->input('start_row', 2);

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
            \DB::beginTransaction();

            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $rowIndex++;
                if ($rowIndex < $startRow) continue;

                if (empty(array_filter($row, function($value) { return $value !== null && trim($value) !== ''; }))) {
                    continue;
                }

                try {
                    $nomorPart = $this->getValue($row, $mapping, 'col_nomor_part');
                    $minStock = $this->getValue($row, $mapping, 'col_min_stock');
                    $maxStock = $this->getValue($row, $mapping, 'col_max_stock');

                    if (!$nomorPart) {
                        throw new \Exception("Nomor Part tidak boleh kosong.");
                    }

                    $part = SMPart::where('nomor_part', $nomorPart)->first();
                    if (!$part) throw new \Exception("Part tidak ditemukan: $nomorPart");

                    $minStock = $minStock ? (int) str_replace(['.', ','], '', $minStock) : 0;
                    $maxStock = $maxStock ? (int) str_replace(['.', ','], '', $maxStock) : 0;

                    if ($maxStock > 0 && $minStock > $maxStock) {
                        throw new \Exception("Min Stock tidak boleh lebih besar dari Max Stock.");
                    }

                    $part->update([
                        'min_stock' => $minStock,
                        'max_stock' => $maxStock,
                    ]);

                    $stats['success']++;

                } catch (\Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = "Baris $rowIndex: " . $e->getMessage();
                }
            }

            fclose($handle);
            \DB::commit();

            if (!empty($stats['errors'])) {
                $msg = 'Import Selesai dengan beberapa error. ' . count($stats['errors']) . ' baris bermasalah.';
                return redirect()->back()
                    ->with('warning', $msg . " Berhasil: {$stats['success']} part.")
                    ->with('import_errors', $stats['errors']);
            }

            return redirect()->route('finishgood.stock.index')->with('success', "Import Berhasil! {$stats['success']} part berhasil diupdate.");

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

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

    public function export(Request $request)
    {
        if (!userCan('finishgood.stock.view')) {
            abort(403);
        }

        $fileName = 'stock_finishgood_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Get same query as index
        $stockQuery = TStockFG::with(['part.customer']);

        if ($request->search) {
            $search = $request->search;
            $stockQuery->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%")
                  ->orWhere('model_part', 'like', "%{$search}%")
                  ->orWhere('tipe_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('nama_perusahaan', 'like', "%{$search}%")
                        ->orWhere('inisial_perusahaan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->customer) {
            $stockQuery->whereHas('part', function($q) use ($request) {
                $q->where('customer_id', $request->customer);
            });
        }

        $period = $request->input('period', 'month');
        if ($period == 'today') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        } else {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        }

        $stocks = $stockQuery->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Part Number', 
            'Part Name', 
            'Customer', 
            'Type/Model',
            'Stock Awal', 
            'IN', 
            'OUT', 
            'Current Stock',
            'Min Stock',
            'Max Stock',
            'Status'
        ];

        $callback = function() use($stocks, $columns, $start, $end) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($stocks as $stock) {
                $qty = $stock->qty;
                $min = $stock->part->min_stock ?? 0;
                $max = $stock->part->max_stock ?? 0;
                
                $status = 'SAFE';
                if($qty == 0) { $status = 'KRITIS'; }
                elseif($min > 0 && $qty < $min) { $status = 'MINIM'; }
                elseif($max > 0 && $qty > $max) { $status = 'OVER'; }

                fputcsv($file, [
                    $stock->part->nomor_part ?? '-',
                    $stock->part->nama_part ?? '-',
                    $stock->part->customer->nama_perusahaan ?? '-',
                    $stock->part->tipe_id ?? '-',
                    $stock->opening_stock,
                    $stock->period_in,
                    $stock->period_out,
                    $stock->qty,
                    $min,
                    $max,
                    $status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
