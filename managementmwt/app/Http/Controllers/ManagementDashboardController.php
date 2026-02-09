<?php

namespace App\Http\Controllers;

use App\Models\TScheduleHeader;
use App\Models\TScheduleDetail;
use App\Models\MBahanBaku;
use App\Models\Receiving;
use App\Models\TFinishGoodIn;
use App\Models\TFinishGoodOut;
use App\Models\TStockFG;
use App\Models\TSpkDetail;
use App\Models\TPurchaseOrderCustomer;
use App\Models\MPerusahaan;
use App\Models\SMPart;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManagementDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Redirect Superadmin (Role 1) back to Master Portal
        if ((int)session('role') === 1) {
            $masterUrl = preg_replace('#/managementmwt(/public)?$#', '', url('/'));
            if ($masterUrl === url('/')) { // Fallback if regex didn't match (e.g. subdomain)
                $masterUrl = 'http://mwtpsg.test'; 
            }
            return redirect()->away($masterUrl);
        }

        $periode = $request->input('periode', date('Y-m'));
        $date = Carbon::parse($periode . '-01');
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // 1. Control Supplier Stats (Vendor Dashboard Summary)
        // Service Rate logic (Plan vs Act)
        $totalPlanSup = TScheduleDetail::whereBetween('tanggal', [$startDate, $endDate])->sum('pc_plan');
        $totalActSup = TScheduleDetail::whereBetween('tanggal', [$startDate, $endDate])->sum('pc_act');
        
        // Active Suppliers & Top Supplier (by Volume)
        $activeSuppliers = MPerusahaan::whereHas('bahanBakus')->count();
        $topSupplier = TScheduleDetail::select('m_perusahaan.nama_perusahaan', DB::raw('SUM(pc_act) as total_volume'))
            ->join('t_schedule_header', 't_schedule_detail.schedule_header_id', '=', 't_schedule_header.id')
            ->join('m_perusahaan', 't_schedule_header.supplier_id', '=', 'm_perusahaan.id')
            ->whereBetween('t_schedule_detail.tanggal', [$startDate, $endDate])
            ->groupBy('m_perusahaan.id', 'm_perusahaan.nama_perusahaan')
            ->orderByDesc('total_volume')
            ->first();

        $supplierStats = [
            'total_plan' => $totalPlanSup,
            'total_act' => $totalActSup,
            'service_rate' => $totalPlanSup > 0 ? ($totalActSup / $totalPlanSup) * 100 : 0,
            'active_suppliers' => $activeSuppliers,
            'top_supplier' => $topSupplier ? $topSupplier->nama_perusahaan : '-',
        ];

        // 2. Finish Good Stats (Stock Dashboard Summary)
        // Stock Levels
        $stocks = TStockFG::with('part')->get();
        $totalItems = $stocks->count();
        $criticalStock = 0;
        $overStock = 0;
        
        foreach($stocks as $stock) {
            $min = $stock->part->min_stock ?? 0;
            $max = $stock->part->max_stock ?? 0;
            $qty = $stock->qty;
            
            if ($qty == 0 || ($min > 0 && $qty < $min)) {
                $criticalStock++;
            } elseif ($max > 0 && $qty > $max) {
                $overStock++;
            }
        }

        $fgStats = [
            'total_in' => TFinishGoodIn::whereBetween('waktu_scan', [$startDate, $endDate])->sum('qty'),
            'total_out' => TFinishGoodOut::whereBetween('waktu_scan_out', [$startDate, $endDate])->sum('qty'),
            'total_items' => $totalItems,
            'critical_items' => $criticalStock,
            'over_items' => $overStock,
            'current_inventory' => $stocks->sum('qty'),
        ];

        // 3. Delivery Stats (Delivery Dashboard Summary)
        $totalPO = TPurchaseOrderCustomer::where('month', $date->month)
            ->where('year', $date->year)
            ->sum('qty');
            
        $totalPlanDel = TSpkDetail::whereHas('spk', function($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal', [$startDate, $endDate]);
        })->sum('jadwal_delivery_pcs');
        
        $totalActDel = TFinishGoodOut::whereBetween('waktu_scan_out', [$startDate, $endDate])->sum('qty');
        $pendingDel = max(0, $totalPlanDel - $totalActDel);

        $deliveryStats = [
            'total_po' => $totalPO,
            'total_plan' => $totalPlanDel,
            'total_actual' => $totalActDel,
            'pending' => $pendingDel,
            'service_rate' => $totalPlanDel > 0 ? ($totalActDel / $totalPlanDel) * 100 : 0,
        ];

        // 4. Monthly Trends (Last 6 Months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $ms = $m->copy()->startOfMonth();
            $me = $m->copy()->endOfMonth();
            
            $monthlyTrend[] = [
                'month' => $m->format('M'),
                'supplier_sr' => round($this->getSupplierSR($ms, $me), 1),
                'delivery_sr' => round($this->getDeliverySR($ms, $me), 1),
            ];
        }

        // 5. Executive Info (Profile Card)
        $executive = \App\Models\MManpower::where('departemen', 'MANAGEMENT')
            ->orWhere('bagian', 'DIRECTOR')
            ->orWhere('bagian', 'GM')
            ->first();

        return view('dashboard.index', compact(
            'periode', 'supplierStats', 'fgStats', 'deliveryStats', 'monthlyTrend', 'executive'
        ));
    }

    public function getStats()
    {
        // For real-time polling updates
        $today = Carbon::today();
        
        return response()->json([
            'receiving_today' => Receiving::whereDate('tanggal_receiving', $today)->count(),
            'fg_in_today' => TFinishGoodIn::whereDate('waktu_scan', $today)->sum('qty'),
            'fg_out_today' => TFinishGoodOut::whereDate('waktu_scan_out', $today)->sum('qty'),
            'delivery_today' => TFinishGoodOut::whereDate('waktu_scan_out', $today)->sum('qty'),
        ]);
    }

    private function getSupplierSR($start, $end)
    {
        $plan = TScheduleDetail::whereBetween('tanggal', [$start, $end])->sum('pc_plan');
        $act = TScheduleDetail::whereBetween('tanggal', [$start, $end])->sum('pc_act');
        return $plan > 0 ? ($act / $plan) * 100 : 0;
    }

    private function getDeliverySR($start, $end)
    {
        $plan = TSpkDetail::whereHas('spk', function($q) use ($start, $end) {
            $q->whereBetween('tanggal', [$start, $end]);
        })->sum('jadwal_delivery_pcs');
        $act = TFinishGoodOut::whereBetween('waktu_scan_out', [$start, $end])->sum('qty');
        return $plan > 0 ? ($act / $plan) * 100 : 0;
    }
}

