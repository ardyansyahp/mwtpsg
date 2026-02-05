<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use App\Models\TSupply;
use App\Models\TSupplyDetail;
use App\Models\TScheduleHeader;
use App\Models\TScheduleDetail;
use App\Models\TPlanningRun;
use App\Models\MMesin;
use App\Models\MBahanBaku;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BahanBakuDashboardController extends Controller
{
    public function index()
    {
        // Ambil data 7 hari terakhir untuk chart
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // ========== STOCK LEVEL ==========
        // Hitung stock per bahan baku (Receiving - Supply)
        $receivingQty = ReceivingDetail::select('nomor_bahan_baku', DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('nomor_bahan_baku')
            ->groupBy('nomor_bahan_baku')
            ->pluck('total_qty', 'nomor_bahan_baku');

        $supplyQty = TSupplyDetail::select('nomor_bahan_baku', DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('nomor_bahan_baku')
            ->groupBy('nomor_bahan_baku')
            ->pluck('total_qty', 'nomor_bahan_baku');

        // Stock = Receiving - Supply
        $stockPerBahanBaku = $receivingQty->map(function($receiving, $nomorBahanBaku) use ($supplyQty) {
            $supply = $supplyQty->get($nomorBahanBaku, 0);
            return max(0, $receiving - $supply);
        })->filter(function($stock) {
            return $stock > 0;
        });

        // Total stock value (sum semua stock)
        $totalStockValue = $stockPerBahanBaku->sum();
        
        // Jumlah bahan baku aktif (yang memiliki stock > 0)
        $currentStock = $stockPerBahanBaku->count();

        // Stock per kategori
        $stockPerKategori = MBahanBaku::whereIn('nomor_bahan_baku', $stockPerBahanBaku->keys())
            ->select('kategori', DB::raw('COUNT(*) as count'))
            ->groupBy('kategori')
            ->pluck('count', 'kategori');

        // ========== IN/OUT DATA (QTY) ==========
        // Data Receiving (IN) per hari - QTY
        $receivingQtyData = ReceivingDetail::join('bb_receiving', 'bb_receiving_detail.receiving_id', '=', 'bb_receiving.id')
            ->whereBetween('bb_receiving.tanggal_receiving', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(bb_receiving.tanggal_receiving) as date'),
                DB::raw('SUM(bb_receiving_detail.qty) as total_qty')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data Supply (OUT) per hari - QTY
        $supplyQtyData = TSupplyDetail::join('t_supply', 't_supply_detail.supply_id', '=', 't_supply.id')
            ->whereBetween('t_supply.tanggal_supply', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(t_supply.tanggal_supply) as date'),
                DB::raw('SUM(t_supply_detail.qty) as total_qty'),
                't_supply.tujuan'
            )
            ->groupBy('date', 't_supply.tujuan')
            ->orderBy('date')
            ->get();

        // Data Schedule Plan (dari Control Supplier) - untuk melihat plan vs actual
        $schedulePlanData = TScheduleDetail::join('t_schedule_header', 't_schedule_detail.schedule_header_id', '=', 't_schedule_header.id')
            ->whereBetween('t_schedule_detail.tanggal', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(t_schedule_detail.tanggal) as date'),
                DB::raw('SUM(t_schedule_detail.pc_plan) as total_plan'),
                DB::raw('SUM(t_schedule_detail.pc_act) as total_act')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data Receiving per kategori per hari
        $receivingPerKategori = ReceivingDetail::join('bb_receiving', 'bb_receiving_detail.receiving_id', '=', 'bb_receiving.id')
            ->join('m_bahanbaku', 'bb_receiving_detail.nomor_bahan_baku', '=', 'm_bahanbaku.nomor_bahan_baku')
            ->whereBetween('bb_receiving.tanggal_receiving', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(bb_receiving.tanggal_receiving) as date'),
                'm_bahanbaku.kategori',
                DB::raw('SUM(bb_receiving_detail.qty) as total_qty')
            )
            ->groupBy('date', 'm_bahanbaku.kategori')
            ->orderBy('date')
            ->get();

        // Data Supply per kategori per hari
        $supplyPerKategori = TSupplyDetail::join('t_supply', 't_supply_detail.supply_id', '=', 't_supply.id')
            ->join('m_bahanbaku', 't_supply_detail.nomor_bahan_baku', '=', 'm_bahanbaku.nomor_bahan_baku')
            ->whereBetween('t_supply.tanggal_supply', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(t_supply.tanggal_supply) as date'),
                'm_bahanbaku.kategori',
                DB::raw('SUM(t_supply_detail.qty) as total_qty')
            )
            ->groupBy('date', 'm_bahanbaku.kategori')
            ->orderBy('date')
            ->get();

        // Total receiving hari ini
        $todayReceiving = Receiving::whereDate('tanggal_receiving', Carbon::today())
            ->count();

        // Total supply hari ini
        $todaySupply = TSupply::whereDate('tanggal_supply', Carbon::today())
            ->count();

        // Supply ke mesin (untuk inject)
        $supplyToMesinCollection = TSupply::where('tujuan', 'inject')
            ->with(['planningRun.day.mesin'])
            ->whereBetween('tanggal_supply', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                return $item->planningRun && $item->planningRun->day && $item->planningRun->day->mesin 
                    ? $item->planningRun->day->mesin->no_mesin 
                    : 'Unknown';
            })
            ->map(function($items) {
                return $items->count();
            })
            ->filter(function($value, $key) {
                return $key !== 'Unknown';
            });
        
        $supplyToMesin = $supplyToMesinCollection->toArray();

        // Supply ke meja (untuk assy)
        $supplyToMejaCollection = TSupply::where('tujuan', 'assy')
            ->whereBetween('tanggal_supply', [$startDate, $endDate])
            ->whereNotNull('meja')
            ->select('meja', DB::raw('COUNT(*) as total'))
            ->groupBy('meja')
            ->get()
            ->pluck('total', 'meja');
        
        $supplyToMeja = $supplyToMejaCollection->toArray();

        // ========== FORMAT DATA UNTUK CHART ==========
        $dates = [];
        $receivingChartData = []; // QTY receiving per hari
        $supplyInjectChartData = []; // QTY supply inject per hari
        $supplyAssyChartData = []; // QTY supply assy per hari
        $schedulePlanChartData = []; // Plan dari schedule
        $scheduleActChartData = []; // Actual dari schedule
        $stockLevelChartData = []; // Stock level over time (calculated)

        // Calculate stock level over time (cumulative)
        $runningStock = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($date)->format('d M');
            
            // Receiving QTY data
            $receivingQty = $receivingQtyData->firstWhere('date', $date);
            $receivingQtyValue = $receivingQty ? (float)$receivingQty->total_qty : 0;
            $receivingChartData[] = $receivingQtyValue;
            
            // Supply QTY data
            $supplyInjectQty = $supplyQtyData->where('date', $date)->where('tujuan', 'inject')->first();
            $supplyInjectQtyValue = $supplyInjectQty ? (float)$supplyInjectQty->total_qty : 0;
            $supplyInjectChartData[] = $supplyInjectQtyValue;
            
            $supplyAssyQty = $supplyQtyData->where('date', $date)->where('tujuan', 'assy')->first();
            $supplyAssyQtyValue = $supplyAssyQty ? (float)$supplyAssyQty->total_qty : 0;
            $supplyAssyChartData[] = $supplyAssyQtyValue;
            
            // Schedule Plan vs Actual
            $schedulePlan = $schedulePlanData->firstWhere('date', $date);
            $schedulePlanChartData[] = $schedulePlan ? (float)$schedulePlan->total_plan : 0;
            $scheduleActChartData[] = $schedulePlan ? (float)$schedulePlan->total_act : 0;
            
            // Calculate stock level (cumulative: stock hari sebelumnya + receiving - supply)
            $previousStock = isset($runningStock[$i + 1]) ? $runningStock[$i + 1] : 0;
            $totalSupplyQty = $supplyInjectQtyValue + $supplyAssyQtyValue;
            $currentStockLevel = max(0, $previousStock + $receivingQtyValue - $totalSupplyQty);
            $runningStock[$i] = $currentStockLevel;
            $stockLevelChartData[] = $currentStockLevel;
        }

        // ========== TOP BAHAN BAKU ==========
        // Top 5 bahan baku yang paling banyak diterima (QTY)
        $topBahanBaku = ReceivingDetail::with('bahanBaku')
            ->select('nomor_bahan_baku', DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('nomor_bahan_baku')
            ->groupBy('nomor_bahan_baku')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ========== STOCK PER KATEGORI CHART DATA ==========
        // Format stock per kategori untuk chart
        $stockKategoriLabels = $stockPerKategori->keys()->toArray();
        $stockKategoriValues = $stockPerKategori->values()->toArray();

        // Pastikan tidak ada null values
        $dates = $dates ?? [];
        $receivingChartData = $receivingChartData ?? [];
        $supplyInjectChartData = $supplyInjectChartData ?? [];
        $supplyAssyChartData = $supplyAssyChartData ?? [];
        $schedulePlanChartData = $schedulePlanChartData ?? [];
        $scheduleActChartData = $scheduleActChartData ?? [];
        $stockLevelChartData = $stockLevelChartData ?? [];
        $supplyToMesin = $supplyToMesin ?? [];
        $supplyToMeja = $supplyToMeja ?? [];
        $topBahanBaku = $topBahanBaku ?? collect();
        $stockKategoriLabels = $stockKategoriLabels ?? [];
        $stockKategoriValues = $stockKategoriValues ?? [];
        $totalStockValue = $totalStockValue ?? 0;

        return view('dashboard.bahanbaku', compact(
            'dates',
            'receivingChartData',
            'supplyInjectChartData',
            'supplyAssyChartData',
            'schedulePlanChartData',
            'scheduleActChartData',
            'stockLevelChartData',
            'currentStock',
            'totalStockValue',
            'todayReceiving',
            'todaySupply',
            'supplyToMesin',
            'supplyToMeja',
            'topBahanBaku',
            'stockKategoriLabels',
            'stockKategoriValues'
        ));
    }
}

