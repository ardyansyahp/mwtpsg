<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TAssyIn;
use App\Models\TAssyOut;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssyDashboardController extends Controller
{
    public function index()
    {
        // Ambil data 7 hari terakhir untuk chart
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Data Assy In per hari
        $assyInData = TAssyIn::whereBetween('waktu_scan', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data Assy Out per hari
        $assyOutData = TAssyOut::whereBetween('waktu_scan', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Total part di Assy saat ini (In - Out)
        $totalAssyIn = TAssyIn::count();
        $totalAssyOut = TAssyOut::count();
        $currentParts = $totalAssyIn - $totalAssyOut;

        // Total assy in hari ini
        $todayAssyIn = TAssyIn::whereDate('waktu_scan', Carbon::today())->count();

        // Total assy out hari ini
        $todayAssyOut = TAssyOut::whereDate('waktu_scan', Carbon::today())->count();

        // Manpower trend
        $manpowerData = TAssyIn::whereBetween('waktu_scan', [$startDate, $endDate])
            ->whereNotNull('manpower')
            ->select(
                DB::raw('DATE(waktu_scan) as date'),
                DB::raw('COUNT(DISTINCT manpower) as total_manpower')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Pemakaian bahan baku (subpart dari supply detail)
        $bahanBakuUsageCollection = TAssyIn::with(['supplyDetail.receivingDetail.bahanBaku'])
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                if ($item->supplyDetail && 
                    $item->supplyDetail->receivingDetail && 
                    $item->supplyDetail->receivingDetail->bahanBaku) {
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
        $assyInChartData = [];
        $assyOutChartData = [];
        $manpowerChartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($date)->format('d M');
            
            // Assy In data
            $assyIn = $assyInData->firstWhere('date', $date);
            $assyInChartData[] = $assyIn ? $assyIn->total : 0;
            
            // Assy Out data
            $assyOut = $assyOutData->firstWhere('date', $date);
            $assyOutChartData[] = $assyOut ? $assyOut->total : 0;
            
            // Manpower data
            $manpower = $manpowerData->firstWhere('date', $date);
            $manpowerChartData[] = $manpower ? $manpower->total_manpower : 0;
        }

        // Top 5 part yang paling banyak di-assy
        $topPartsCollection = TAssyIn::with(['part', 'supplyDetail.supply.part', 'wipOut.planningRun.mold.part'])
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                // Cari part dari berbagai sumber
                $part = $item->part;
                if (!$part && $item->supplyDetail && $item->supplyDetail->supply) {
                    $part = $item->supplyDetail->supply->part;
                }
                if (!$part && $item->wipOut && $item->wipOut->planningRun && $item->wipOut->planningRun->mold) {
                    $part = $item->wipOut->planningRun->mold->part;
                }
                return $part ? $part->nomor_part : 'Unknown';
            })
            ->map(function($items, $key) {
                $firstItem = $items->first();
                $part = $firstItem->part;
                if (!$part && $firstItem->supplyDetail && $firstItem->supplyDetail->supply) {
                    $part = $firstItem->supplyDetail->supply->part;
                }
                if (!$part && $firstItem->wipOut && $firstItem->wipOut->planningRun && $firstItem->wipOut->planningRun->mold) {
                    $part = $firstItem->wipOut->planningRun->mold->part;
                }
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

        // Data per meja
        $assyPerMejaCollection = TAssyIn::with(['supplyDetail.supply'])
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                if ($item->supplyDetail && $item->supplyDetail->supply && $item->supplyDetail->supply->meja) {
                    return $item->supplyDetail->supply->meja;
                }
                return 'Unknown';
            })
            ->map(function($items) {
                return $items->count();
            })
            ->filter(function($value, $key) {
                return $key !== 'Unknown';
            });
        
        $assyPerMeja = $assyPerMejaCollection->toArray();

        // Pastikan tidak ada null values - sudah di-convert ke array di atas
        $dates = $dates ?? [];
        $assyInChartData = $assyInChartData ?? [];
        $assyOutChartData = $assyOutChartData ?? [];
        $manpowerChartData = $manpowerChartData ?? [];
        $bahanBakuUsage = $bahanBakuUsage ?? [];
        $topParts = $topParts ?? [];
        $assyPerMeja = $assyPerMeja ?? [];

        return view('dashboard.assy', compact(
            'dates',
            'assyInChartData',
            'assyOutChartData',
            'currentParts',
            'todayAssyIn',
            'todayAssyOut',
            'manpowerChartData',
            'bahanBakuUsage',
            'topParts',
            'assyPerMeja'
        ));
    }
}

