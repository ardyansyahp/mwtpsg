<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TWipIn;
use App\Models\TWipOut;
use App\Models\TWipOutDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WipDashboardController extends Controller
{
    public function index()
    {
        // Ambil data 7 hari terakhir untuk chart
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Data WIP In per hari
        $wipInData = TWipIn::whereBetween('waktu_scan_in', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan_in) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data WIP Out per hari
        $wipOutData = TWipOut::whereBetween('waktu_scan_out', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan_out) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Total part di WIP saat ini (In - Out)
        $totalWipIn = TWipIn::count();
        $totalWipOut = TWipOut::count();
        $currentParts = $totalWipIn - $totalWipOut;

        // Total WIP in hari ini
        $todayWipIn = TWipIn::whereDate('waktu_scan_in', Carbon::today())->count();

        // Total WIP out hari ini
        $todayWipOut = TWipOut::whereDate('waktu_scan_out', Carbon::today())->count();

        // WIP In yang sudah confirmed vs belum
        $confirmedWipIn = TWipIn::where('is_confirmed', true)->count();
        $unconfirmedWipIn = TWipIn::where('is_confirmed', false)->count();

        // Pemakaian bahan baku (dari inject in yang masuk ke WIP)
        $bahanBakuUsageCollection = TWipIn::with(['injectOut.injectIn.supplyDetail.receivingDetail.bahanBaku'])
            ->whereBetween('waktu_scan_in', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                if ($item->injectOut && 
                    $item->injectOut->injectIn && 
                    $item->injectOut->injectIn->supplyDetail && 
                    $item->injectOut->injectIn->supplyDetail->receivingDetail && 
                    $item->injectOut->injectIn->supplyDetail->receivingDetail->bahanBaku) {
                    return $item->injectOut->injectIn->supplyDetail->receivingDetail->bahanBaku->nama_bahan_baku;
                }
                return 'Unknown';
            })
            ->map(function($items) {
                return $items->sum(function($item) {
                    if ($item->injectOut && 
                        $item->injectOut->injectIn && 
                        $item->injectOut->injectIn->supplyDetail) {
                        return $item->injectOut->injectIn->supplyDetail->qty;
                    }
                    return 0;
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
        $wipInChartData = [];
        $wipOutChartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($date)->format('d M');
            
            // WIP In data
            $wipIn = $wipInData->firstWhere('date', $date);
            $wipInChartData[] = $wipIn ? $wipIn->total : 0;
            
            // WIP Out data
            $wipOut = $wipOutData->firstWhere('date', $date);
            $wipOutChartData[] = $wipOut ? $wipOut->total : 0;
        }

        // Top 5 part yang paling banyak di WIP
        $topPartsCollection = TWipIn::with(['planningRun.mold.part'])
            ->whereBetween('waktu_scan_in', [$startDate, $endDate])
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
            ->sortByDesc('total')
            ->take(5)
            ->values();
        
        $topParts = $topPartsCollection->toArray();

        // Data mesin asal (dari inject in)
        $mesinAsal = TWipIn::with(['injectOut.injectIn.mesin'])
            ->whereBetween('waktu_scan_in', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                if ($item->injectOut && $item->injectOut->injectIn && $item->injectOut->injectIn->mesin) {
                    return $item->injectOut->injectIn->mesin->no_mesin;
                }
                return 'Unknown';
            })
            ->map(function($items) {
                return $items->count();
            })
            ->filter(function($value, $key) {
                return $key !== 'Unknown';
            })
            ->toArray(); // Convert to plain array for JavaScript

        // Pastikan tidak ada null values - sudah di-convert ke array di atas
        $dates = $dates ?? [];
        $wipInChartData = $wipInChartData ?? [];
        $wipOutChartData = $wipOutChartData ?? [];
        $bahanBakuUsage = $bahanBakuUsage ?? [];
        $topParts = $topParts ?? [];
        $mesinAsal = $mesinAsal ?? [];

        return view('dashboard.wip', compact(
            'dates',
            'wipInChartData',
            'wipOutChartData',
            'currentParts',
            'todayWipIn',
            'todayWipOut',
            'confirmedWipIn',
            'unconfirmedWipIn',
            'bahanBakuUsage',
            'topParts',
            'mesinAsal'
        ));
    }
}

