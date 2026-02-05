<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TInjectIn;
use App\Models\TInjectOut;
use App\Models\TInjectOutDetail;
use App\Models\MMesin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InjectDashboardController extends Controller
{
    public function index()
    {
        // Ambil data 7 hari terakhir untuk chart
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Data Inject In per hari
        $injectInData = TInjectIn::whereBetween('waktu_scan', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data Inject Out per hari
        $injectOutData = TInjectOut::whereBetween('waktu_scan', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Total part di Inject saat ini (In - Out)
        $totalInjectIn = TInjectIn::count();
        $totalInjectOut = TInjectOut::count();
        $currentParts = $totalInjectIn - $totalInjectOut;

        // Total inject in hari ini
        $todayInjectIn = TInjectIn::whereDate('waktu_scan', Carbon::today())->count();

        // Total inject out hari ini
        $todayInjectOut = TInjectOut::whereDate('waktu_scan', Carbon::today())->count();

        // Data per mesin
        $injectPerMesinCollection = TInjectIn::with('mesin')
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                return $item->mesin ? $item->mesin->no_mesin : 'Unknown';
            })
            ->map(function($items) {
                return $items->count();
            })
            ->filter(function($value, $key) {
                return $key !== 'Unknown';
            });
        
        $injectPerMesin = $injectPerMesinCollection->toArray();

        // Manpower trend
        $manpowerData = TInjectIn::whereBetween('waktu_scan', [$startDate, $endDate])
            ->whereNotNull('manpower')
            ->select(
                DB::raw('DATE(waktu_scan) as date'),
                DB::raw('COUNT(DISTINCT manpower) as total_manpower')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Pemakaian bahan baku (dari supply detail yang digunakan inject)
        $bahanBakuUsageCollection = TInjectIn::with(['supplyDetail.receivingDetail.bahanBaku'])
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                if ($item->supplyDetail && $item->supplyDetail->receivingDetail && $item->supplyDetail->receivingDetail->bahanBaku) {
                    return $item->supplyDetail->receivingDetail->bahanBaku->nama_bahan_baku;
                }
                return 'Unknown';
            })
            ->map(function($items) {
                return $items->sum(function($item) {
                    return $item->supplyDetail ? $item->supplyDetail->qty : 0;
                });
            })
            ->filter(function($value, $key) {
                return $key !== 'Unknown' && $value > 0;
            })
            ->sortDesc()
            ->take(5);
        
        $bahanBakuUsage = $bahanBakuUsageCollection->toArray();

        // Format data untuk chart
        $dates = [];
        $injectInChartData = [];
        $injectOutChartData = [];
        $manpowerChartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($date)->format('d M');
            
            // Inject In data
            $injectIn = $injectInData->firstWhere('date', $date);
            $injectInChartData[] = $injectIn ? $injectIn->total : 0;
            
            // Inject Out data
            $injectOut = $injectOutData->firstWhere('date', $date);
            $injectOutChartData[] = $injectOut ? $injectOut->total : 0;
            
            // Manpower data
            $manpower = $manpowerData->firstWhere('date', $date);
            $manpowerChartData[] = $manpower ? $manpower->total_manpower : 0;
        }

        // Top 5 part yang paling banyak diproduksi
        $topPartsCollection = TInjectIn::with(['planningRun.mold.part'])
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                if ($item->planningRun && $item->planningRun->mold && $item->planningRun->mold->part) {
                    return $item->planningRun->mold->part->nomor_part;
                }
                return 'Unknown';
            })
            ->map(function($items, $key) {
                $firstItem = $items->first();
                $part = $firstItem->planningRun && $firstItem->planningRun->mold ? $firstItem->planningRun->mold->part : null;
                return [
                    'nomor_part' => $key,
                    'nama_part' => $part ? $part->nama_part : '-',
                    'total' => $items->count()
                ];
            })
            ->filter(function($item) {
                return $item['nomor_part'] !== 'Unknown';
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();
        
        $topParts = $topPartsCollection->toArray();

        // Pastikan tidak ada null values - sudah di-convert ke array di atas
        $dates = $dates ?? [];
        $injectInChartData = $injectInChartData ?? [];
        $injectOutChartData = $injectOutChartData ?? [];
        $manpowerChartData = $manpowerChartData ?? [];
        $injectPerMesin = $injectPerMesin ?? [];
        $bahanBakuUsage = $bahanBakuUsage ?? [];
        $topParts = $topParts ?? [];

        return view('dashboard.inject', compact(
            'dates',
            'injectInChartData',
            'injectOutChartData',
            'currentParts',
            'todayInjectIn',
            'todayInjectOut',
            'injectPerMesin',
            'manpowerChartData',
            'bahanBakuUsage',
            'topParts'
        ));
    }
}

