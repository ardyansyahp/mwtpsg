<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use App\Models\MKendaraan;
use App\Models\SMPart;
use App\Models\TFinishGoodOut;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get filter dari request
        $filterTruck = $request->input('kendaraan_id');
        $filterPart = $request->input('part_id');
        $filterMonth = $request->input('month', Carbon::now()->format('Y-m'));
        
        // Parse bulan
        $selectedMonth = Carbon::parse($filterMonth . '-01');
        $startDate = $selectedMonth->copy()->startOfMonth();
        $endDate = $selectedMonth->copy()->endOfMonth();
        // ========== KETERCAPAIAN DELIVERY ==========
        
        // Query base dengan filter
        $deliveryQuery = TShippingDeliveryHeader::query();
        
        if ($filterTruck) {
            $deliveryQuery->where('kendaraan_id', $filterTruck);
        }
        
        // Filter by part melalui finish good out -> shipping loading -> delivery
        if ($filterPart) {
            // Cari finish good out dengan part ini
            $fgOutIds = TFinishGoodOut::where('part_id', $filterPart)
                ->pluck('id')
                ->toArray();
            
            if (!empty($fgOutIds)) {
                // Cari shipping loading yang terkait dengan finish good out
                $loadingKendaraanIds = DB::table('T_Shipping_Loading')
                    ->whereIn('finish_good_out_id', $fgOutIds)
                    ->pluck('kendaraan_id')
                    ->unique()
                    ->toArray();
                
                if (!empty($loadingKendaraanIds)) {
                    // Filter delivery berdasarkan kendaraan yang ada di shipping loading
                    $deliveryQuery->whereIn('kendaraan_id', $loadingKendaraanIds);
                } else {
                    // Jika tidak ada loading, set query yang tidak akan return hasil
                    $deliveryQuery->whereRaw('1 = 0');
                }
            } else {
                // Jika tidak ada finish good out dengan part ini, tidak ada delivery
                $deliveryQuery->whereRaw('1 = 0');
            }
        }
        
        // Total delivery
        $totalDelivery = (clone $deliveryQuery)->count();
        
        // Delivery by status
        $deliveryByStatus = (clone $deliveryQuery)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Status mapping
        $completed = $deliveryByStatus['COMPLETED'] ?? 0;
        $arrived = $deliveryByStatus['ARRIVED'] ?? 0;
        
        // In Transit includes NORMAL, DELAY, ADVANCED
        $inTransit = ($deliveryByStatus['NORMAL'] ?? 0) + 
                     ($deliveryByStatus['DELAY'] ?? 0) + 
                     ($deliveryByStatus['ADVANCED'] ?? 0) +
                     ($deliveryByStatus['IN_TRANSIT'] ?? 0);
                     
        $open = $deliveryByStatus['OPEN'] ?? 0;
        $cancelled = $deliveryByStatus['CANCELLED'] ?? 0;
        
        // Ketercapaian = (COMPLETED + ARRIVED) / Total
        $achieved = $completed + $arrived;
        $achievementRate = $totalDelivery > 0 
            ? round(($achieved / $totalDelivery) * 100, 1) 
            : 0;
        
        // ========== BULAN INI ==========
        $today = Carbon::today();
        
        // Delivery bulan ini
        $monthDeliveryQuery = (clone $deliveryQuery)
            ->whereBetween('tanggal_berangkat', [$startDate, $endDate]);
        $monthDelivery = $monthDeliveryQuery->count();
        
        // Delivery delivered (COMPLETED) bulan ini
        $monthDeliveredQuery = (clone $deliveryQuery)
            ->whereBetween('tanggal_berangkat', [$startDate, $endDate])
            ->where('status', 'COMPLETED');
        $monthDelivered = $monthDeliveredQuery->count();
        
        // On-time delivery bulan ini (COMPLETED or ARRIVED)
        // Check if Arrival time <= Plan + 1 day? Or just Arrival exists.
        $monthOnTime = (clone $deliveryQuery)
            ->whereBetween('tanggal_berangkat', [$startDate, $endDate])
            ->whereIn('status', ['COMPLETED', 'ARRIVED'])
            ->whereRaw('DATE(waktu_tiba) <= DATE(tanggal_berangkat) + INTERVAL 1 DAY')
            ->count();
        
        // ========== TREND 12 BULAN ==========
        $trendData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $monthLabel = $month->format('M Y');
            
            $totalQuery = (clone $deliveryQuery)
                ->whereBetween('tanggal_berangkat', [$monthStart, $monthEnd]);
            $total = $totalQuery->count();
            
            $deliveredQuery = (clone $deliveryQuery)
                ->whereBetween('tanggal_berangkat', [$monthStart, $monthEnd])
                ->where('status', 'COMPLETED');
            $deliveredCount = $deliveredQuery->count();
            
            $trendData[] = [
                'date' => $monthLabel,
                'total' => $total,
                'delivered' => $deliveredCount,
                'achievement' => $total > 0 ? round(($deliveredCount / $total) * 100, 1) : 0
            ];
        }
        
        // ========== PERFORMANCE METRICS ==========
        // Average delivery time (hari)
        $avgDeliveryTimeQuery = (clone $deliveryQuery)
            ->whereIn('status', ['COMPLETED', 'ARRIVED'])
            ->whereNotNull('waktu_berangkat')
            ->whereNotNull('waktu_tiba');
        
        $avgDeliveryTime = $avgDeliveryTimeQuery->get()
            ->map(function($delivery) {
                $berangkat = Carbon::parse($delivery->waktu_berangkat);
                $tiba = Carbon::parse($delivery->waktu_tiba);
                return $berangkat->diffInHours($tiba);
            });
        
        $avgHours = $avgDeliveryTime->count() > 0 
            ? round($avgDeliveryTime->avg(), 1) 
            : 0;
        
        // ========== TOP PERFORMANCE ==========
        $topDeliveries = (clone $deliveryQuery)
            ->with(['kendaraan', 'driver'])
            ->where('status', 'COMPLETED')
            ->orderBy('tanggal_berangkat', 'desc')
            ->take(10)
            ->get();
        
        // ========== STATUS DISTRIBUTION ==========
        $statusDistribution = [
            'DELIVERED' => $completed,
            'ARRIVED' => $arrived,
            'IN_TRANSIT' => $inTransit,
            'OPEN' => $open,
            'CANCELLED' => $cancelled
        ];
        
        // Get all trucks dan parts untuk filter dropdown
        $allTrucks = MKendaraan::select('id', 'nopol_kendaraan', 'merk_kendaraan')
            ->orderBy('nopol_kendaraan')
            ->get();
        
        $allParts = SMPart::select('id', 'nomor_part', 'nama_part')
            ->orderBy('nomor_part')
            ->get();
        
        return view('dashboard.delivery', compact(
            'totalDelivery',
            'achieved',
            'achievementRate',
            'monthDelivery',
            'monthDelivered',
            'monthOnTime',
            'trendData',
            'avgHours',
            'topDeliveries',
            'statusDistribution',
            'allTrucks',
            'allParts',
            'filterTruck',
            'filterPart',
            'filterMonth'
        ));
    }
}
