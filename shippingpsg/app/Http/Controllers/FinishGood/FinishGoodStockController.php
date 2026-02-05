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
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('nama_perusahaan', 'like', "%{$search}%");
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
}
