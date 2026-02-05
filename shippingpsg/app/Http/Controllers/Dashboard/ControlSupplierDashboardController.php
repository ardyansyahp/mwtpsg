<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MBahanBaku;
use App\Models\TScheduleDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ControlSupplierDashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateStr = $request->input('date', Carbon::now()->format('Y-m-d'));
        $category = $request->input('category', 'all'); // Default to all categories
        $viewMode = $request->input('view_mode', 'daily'); // Default to daily
        $date = Carbon::parse($dateStr);
        $currentMonth = $date->format('Y-m');
        
        // Formatted date for header
        $formattedDate = $date->isoFormat('dddd, D MMMM Y');
        
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Fetch Data for Table
        $query = MBahanBaku::with('supplier')
            ->whereNotNull('supplier_id')
            ->whereHas('supplier');

        // Apply Category Filter for Table
        if ($category !== 'all') {
            if ($category === 'material') {
                $query->whereIn('kategori', ['material', 'masterbatch']);
            } else {
                $query->where('kategori', $category);
            }
        }

        $bahanBakuList = $query->get();
        $supplierIds = $bahanBakuList->pluck('supplier_id')->unique();
        $bahanBakuIds = $bahanBakuList->pluck('id')->unique();

        // Determine date range based on view mode
        if ($viewMode === 'daily') {
            $startDate = $date->copy()->startOfDay();
            $endDate = $date->copy()->endOfDay();
        } else {
            $startDate = $startOfMonth->copy()->startOfDay();
            $endDate = $endOfMonth->copy()->endOfDay();
        }

        // 1. Fetch ITEM Specific Stats (Aggregated)
        $itemStats = TScheduleDetail::whereBetween('tanggal', [$startDate, $endDate])
            ->whereHas('header', function($q) use ($bahanBakuIds) {
                $q->whereIn('bahan_baku_id', $bahanBakuIds);
            })
            ->join('t_schedule_header', 't_schedule_detail.schedule_header_id', '=', 't_schedule_header.id')
            ->select(
                't_schedule_header.bahan_baku_id',
                DB::raw('SUM(pc_plan) as total_plan'),
                DB::raw('SUM(pc_act) as total_act')
            )
            ->groupBy('t_schedule_header.bahan_baku_id')
            ->get()
            ->keyBy('bahan_baku_id');

        // 2. Fetch SUPPLIER Global Stats (Aggregated)
        $supplierGlobalStats = TScheduleDetail::whereBetween('tanggal', [$startDate, $endDate])
            ->whereHas('header', function($q) use ($supplierIds) {
                $q->whereIn('supplier_id', $supplierIds);
            })
            ->join('t_schedule_header', 't_schedule_detail.schedule_header_id', '=', 't_schedule_header.id')
            ->select(
                't_schedule_header.supplier_id',
                DB::raw('SUM(pc_plan) as total_plan'),
                DB::raw('SUM(pc_act) as total_act')
            )
            ->groupBy('t_schedule_header.supplier_id')
            ->get()
            ->keyBy('supplier_id');

        $items = $bahanBakuList->map(function ($bahanBaku) use ($itemStats, $supplierGlobalStats) {
            $itemStat = $itemStats->get($bahanBaku->id);
            $supStat = $supplierGlobalStats->get($bahanBaku->supplier_id);

            // Item-specific Quality act uses item total act
            $quality_act = $itemStat ? $itemStat->total_act : 0;
            
            // Delivery stats use ITEM specific totals now (Fixed)
            $po_qty = $itemStat ? $itemStat->total_plan : 0;
            $act_del = $itemStat ? $itemStat->total_act : 0;
            
            $ng_qty = 0;
            $balance_qty = $act_del - $po_qty;
            
            $sr_percent = ($po_qty > 0) ? ($act_del / $po_qty) * 100 : ($act_del > 0 ? 100 : 0);

            return [
                'supplier_name' => $bahanBaku->supplier->nama_perusahaan ?? '-',
                'nama_material' => $bahanBaku->nama_bahan_baku,
                'kategori' => $bahanBaku->kategori,
                'quality_act_del' => $quality_act,
                'quality_ng' => $ng_qty,
                'quality_percent' => ($quality_act > 0) ? (($ng_qty / $quality_act) * 100) : 0,
                'cost_buy' => 0,
                'cost_sell' => 0,
                'cost_balance' => 0,
                'delivery_po' => $po_qty,
                'delivery_act' => $act_del,
                'delivery_balance' => $balance_qty,
                'delivery_sr' => round($sr_percent),
                'pending' => '',
                'status_stock' => '',
            ];
        });

        // Sort by Supplier Name then Material Name
        $items = $items->sortBy([
            ['supplier_name', 'asc'],
            ['nama_material', 'asc'],
        ]);

        // --- CHART DATA CALCULATION (Optimized) ---

        if ($viewMode === 'daily') {
            // DAILY MODE CHARTS
            $trendStartDate = $date->copy()->subDays(29);
            
            // 1. Daily Trend in ONE query
            $dailyStats = TScheduleDetail::whereBetween('tanggal', [$trendStartDate, $date])
                ->selectRaw('tanggal, SUM(pc_plan) as total_plan, SUM(pc_act) as total_act')
                ->groupBy('tanggal')
                ->get()
                ->keyBy(function($item) {
                     return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            $trendData = [];
            for ($i = 29; $i >= 0; $i--) {
                $dayDate = $date->copy()->subDays($i);
                $dateKey = $dayDate->format('Y-m-d');
                $stats = $dailyStats->get($dateKey);
                
                $plan = $stats ? $stats->total_plan : 0;
                $act = $stats ? $stats->total_act : 0;
                $sr = ($plan > 0) ? ($act / $plan) * 100 : 0;

                $trendData[] = [
                    'month' => $dayDate->format('d M'),
                    'sr' => round($sr, 1),
                    'plan' => $plan,
                    'act' => $act
                ];
            }

            // 2. Supplier Achievement (Selected Date) - Aggregated
            $supplierStats = TScheduleDetail::where('tanggal', $date)
                ->join('t_schedule_header', 't_schedule_detail.schedule_header_id', '=', 't_schedule_header.id')
                ->join('m_perusahaan', 't_schedule_header.supplier_id', '=', 'm_perusahaan.id')
                ->select(
                    'm_perusahaan.inisial_perusahaan',
                    'm_perusahaan.nama_perusahaan',
                    DB::raw('SUM(pc_plan) as total_plan'),
                    DB::raw('SUM(pc_act) as total_act')
                )
                ->groupBy('m_perusahaan.id', 'm_perusahaan.inisial_perusahaan', 'm_perusahaan.nama_perusahaan')
                ->get()
                ->map(function($stat) {
                    return [
                        'name' => $stat->inisial_perusahaan ?? $stat->nama_perusahaan ?? 'Unknown',
                        'sr' => ($stat->total_plan > 0) ? round(($stat->total_act / $stat->total_plan) * 100, 1) : 0,
                        'act' => $stat->total_act
                    ];
                })
                ->sortByDesc('act')
                ->values();

            // 3. Category Achievement (Selected Date) - Aggregated
            $categoryStats = TScheduleDetail::where('tanggal', $date)
                ->join('t_schedule_header', 't_schedule_detail.schedule_header_id', '=', 't_schedule_header.id')
                ->join('m_bahanbaku', 't_schedule_header.bahan_baku_id', '=', 'm_bahanbaku.id')
                ->select(
                    'm_bahanbaku.kategori',
                    DB::raw('SUM(pc_plan) as total_plan'),
                    DB::raw('SUM(pc_act) as total_act')
                )
                ->groupBy('m_bahanbaku.kategori')
                ->get()
                ->map(function($stat) {
                    $kategori = $stat->kategori ?? 'unknown';
                    if (in_array($kategori, ['material', 'masterbatch'])) $kategori = 'material';
                    
                    return [
                        'category' => ucfirst($kategori),
                        'plan' => $stat->total_plan,
                        'act' => $stat->total_act,
                        'sr' => ($stat->total_plan > 0) ? round(($stat->total_act / $stat->total_plan) * 100, 1) : 0
                    ];
                });
                
        } else {
            // MONTHLY MODE CHARTS
            
            // 1. Monthly Trend (ONE query for last 12 months)
            $months = [];
            for ($i = 11; $i >= 0; $i--) {
                $months[] = $date->copy()->subMonths($i)->format('Y-m');
            }

            $monthlyStats = \App\Models\TScheduleHeader::whereIn('periode', $months)
                ->selectRaw('periode, SUM(total_plan) as total_plan, SUM(total_act) as total_act')
                ->groupBy('periode')
                ->get()
                ->keyBy('periode');

            $trendData = [];
            foreach ($months as $mStr) {
                $monthDate = Carbon::createFromFormat('Y-m', $mStr);
                $stats = $monthlyStats->get($mStr);
                    
                $plan = $stats ? $stats->total_plan : 0;
                $act = $stats ? $stats->total_act : 0;
                $sr = ($plan > 0) ? ($act / $plan) * 100 : 0;

                $trendData[] = [
                    'month' => $monthDate->format('M Y'),
                    'sr' => round($sr, 1),
                    'plan' => $plan,
                    'act' => $act
                ];
            }

            // 2. Supplier Achievement (Current Month)
            $supplierStats = \App\Models\TScheduleHeader::where('periode', $currentMonth)
                ->join('m_perusahaan', 't_schedule_header.supplier_id', '=', 'm_perusahaan.id')
                ->select(
                    'm_perusahaan.inisial_perusahaan',
                    'm_perusahaan.nama_perusahaan',
                    DB::raw('SUM(total_plan) as total_plan'),
                    DB::raw('SUM(total_act) as total_act')
                )
                ->groupBy('m_perusahaan.id', 'm_perusahaan.inisial_perusahaan', 'm_perusahaan.nama_perusahaan')
                ->get()
                ->map(function($stat) {
                    return [
                        'name' => $stat->inisial_perusahaan ?? $stat->nama_perusahaan ?? 'Unknown',
                        'sr' => ($stat->total_plan > 0) ? round(($stat->total_act / $stat->total_plan) * 100, 1) : 0,
                        'act' => $stat->total_act
                    ];
                })
                ->sortByDesc('act')
                ->values();

            // 3. Category Achievement (Current Month)
            $categoryStats = \App\Models\TScheduleHeader::where('periode', $currentMonth)
                ->join('m_bahanbaku', 't_schedule_header.bahan_baku_id', '=', 'm_bahanbaku.id')
                ->select(
                    'm_bahanbaku.kategori', 
                    DB::raw('SUM(t_schedule_header.total_plan) as total_plan'), 
                    DB::raw('SUM(t_schedule_header.total_act) as total_act')
                )
                ->groupBy('m_bahanbaku.kategori')
                ->get()
                ->map(function($stat) {
                    $catName = $stat->kategori;
                    if (in_array($catName, ['material', 'masterbatch'])) $catName = 'material';

                    return [
                        'category' => ucfirst($catName),
                        'plan' => $stat->total_plan,
                        'act' => $stat->total_act,
                        'sr' => ($stat->total_plan > 0) ? round(($stat->total_act / $stat->total_plan) * 100, 1) : 0
                    ];
                });
        }

        // Final normalization for category stats
        $categoryStats = collect($categoryStats)->groupBy('category')->map(function($group, $key) {
             $plan = $group->sum('plan');
             $act = $group->sum('act');
             return [
                 'category' => $key,
                 'plan' => $plan,
                 'act' => $act,
                 'sr' => ($plan > 0) ? round(($act / $plan) * 100, 1) : 0
             ];
        })->values();


        return view('dashboard.controlsupplier', compact(
            'items', 'formattedDate', 'dateStr', 'category', 'viewMode',
            'trendData', 'supplierStats', 'categoryStats'
        ));
    }
}
