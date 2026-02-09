<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TShippingDeliveryHeader;
use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\TFinishGoodOut;
use App\Models\TPurchaseOrderCustomer;
use App\Models\MPerusahaan;
use App\Models\SMPart;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $filterMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $filterCustomer = $request->input('customer');
        $filterPart = $request->input('search'); 

        // Parse Date Range
        $selectedMonth = Carbon::parse($filterMonth . '-01');
        $startDate = $selectedMonth->copy()->startOfMonth();
        $endDate = $selectedMonth->copy()->endOfMonth();

        // 1. Total PO (Purchase Order) - Based on Month/Year
        $poQuery = TPurchaseOrderCustomer::where('month', $selectedMonth->month)
            ->where('year', $selectedMonth->year);
        
        // 2. Delivery Plan (SPK) Filter
        $spkQuery = TSpk::whereBetween('tanggal', [$startDate, $endDate]);

        // 3. Actual Delivery (Finish Good Out - Scanned)
        $fgOutQuery = TFinishGoodOut::whereBetween('waktu_scan_out', [$startDate, $endDate]);

        // Apply Filters
        if ($filterCustomer) {
            $spkQuery->where('customer_id', $filterCustomer);
            
            // For FG Out, we need to join SPK
            $fgOutQuery->whereHas('spk', function($q) use ($filterCustomer) {
                $q->where('customer_id', $filterCustomer);
            });
        }

        if ($filterPart) {
            $search = $filterPart;
            $poQuery->whereHas('part', function($q) use ($search) {
                $q->where('nomor_part', 'like', "%$search%")
                  ->orWhere('nama_part', 'like', "%$search%")
                  ->orWhere('model_part', 'like', "%$search%")
                  ->orWhere('tipe_id', 'like', "%$search%")
                  ->orWhereHas('customer', function($sq) use ($search) {
                      $sq->where('nama_perusahaan', 'like', "%$search%")
                        ->orWhere('inisial_perusahaan', 'like', "%$search%");
                  });
            });
        }

        // --- Calculate Metrics ---

        // Metric 1: Total PO
        $totalPO = $poQuery->sum('qty');

        // Metric 2: Total Delivery Plan
        $planQuery = TSpkDetail::whereHas('spk', function($q) use ($startDate, $endDate, $filterCustomer) {
            $q->whereBetween('tanggal', [$startDate, $endDate]);
            if ($filterCustomer) $q->where('customer_id', $filterCustomer);
        });

        if ($filterPart) {
            $planQuery->whereHas('part', function($q) use ($filterPart) {
                $q->where('nomor_part', 'like', "%$filterPart%")
                  ->orWhere('nama_part', 'like', "%$filterPart%")
                  ->orWhere('model_part', 'like', "%$filterPart%")
                  ->orWhere('tipe_id', 'like', "%$filterPart%")
                  ->orWhereHas('customer', function($sq) use ($filterPart) {
                      $sq->where('nama_perusahaan', 'like', "%$filterPart%")
                        ->orWhere('inisial_perusahaan', 'like', "%$filterPart%");
                  });
            });
        }

        $totalPlan = $planQuery->sum('jadwal_delivery_pcs');

        // Metric 3: Total Actual Delivery
        if ($filterPart) {
            $fgOutQuery->whereHas('part', function($q) use ($filterPart) {
                $q->where('nomor_part', 'like', "%$filterPart%")
                  ->orWhere('nama_part', 'like', "%$filterPart%")
                  ->orWhere('model_part', 'like', "%$filterPart%")
                  ->orWhere('tipe_id', 'like', "%$filterPart%")
                  ->orWhereHas('customer', function($sq) use ($filterPart) {
                      $sq->where('nama_perusahaan', 'like', "%$filterPart%")
                        ->orWhere('inisial_perusahaan', 'like', "%$filterPart%");
                  });
            });
        }
        $totalActual = $fgOutQuery->sum('qty');

        // Metric 4: Balance & Service Rate
        $balance = $totalPlan - $totalActual;
        $pendingDelivery = max(0, $balance);
        
        $serviceRatePlan = $totalPlan > 0 ? ($totalActual / $totalPlan) * 100 : 0;
        $serviceRatePO = $totalPO > 0 ? ($totalActual / $totalPO) * 100 : 0; 

        // --- Charts: Daily Trend ---
        $daysInMonth = $selectedMonth->daysInMonth;
        
        $dailyPlans = TSpkDetail::join('t_spk', 't_spk_detail.spk_id', '=', 't_spk.id')
            ->whereBetween('t_spk.tanggal', [$startDate, $endDate])
            ->when($filterCustomer, function($q) use ($filterCustomer) {
                $q->where('t_spk.customer_id', $filterCustomer);
            })
            ->when($filterPart, function($q) use ($filterPart) {
                $q->whereHas('part', function($sq) use ($filterPart) {
                    $sq->where('nomor_part', 'like', "%$filterPart%")
                      ->orWhere('nama_part', 'like', "%$filterPart%")
                      ->orWhere('model_part', 'like', "%$filterPart%")
                      ->orWhere('tipe_id', 'like', "%$filterPart%")
                      ->orWhereHas('customer', function($c) use ($filterPart) {
                          $c->where('nama_perusahaan', 'like', "%$filterPart%")
                            ->orWhere('inisial_perusahaan', 'like', "%$filterPart%");
                      });
                });
            })
            ->selectRaw('DATE(t_spk.tanggal) as date, SUM(jadwal_delivery_pcs) as total_plan')
            ->groupBy('date')
            ->pluck('total_plan', 'date')
            ->toArray();

        $dailyActuals = TFinishGoodOut::whereBetween('waktu_scan_out', [$startDate, $endDate])
            ->when($filterCustomer, function($q) use ($filterCustomer) {
                 $q->whereHas('spk', function($sq) use ($filterCustomer) {
                     $sq->where('customer_id', $filterCustomer);
                 });
            })
            ->when($filterPart, function($q) use ($filterPart) {
                $q->whereHas('part', function($sq) use ($filterPart) {
                    $sq->where('nomor_part', 'like', "%$filterPart%")
                      ->orWhere('nama_part', 'like', "%$filterPart%")
                      ->orWhere('model_part', 'like', "%$filterPart%")
                      ->orWhere('tipe_id', 'like', "%$filterPart%")
                      ->orWhereHas('customer', function($c) use ($filterPart) {
                          $c->where('nama_perusahaan', 'like', "%$filterPart%")
                            ->orWhere('inisial_perusahaan', 'like', "%$filterPart%");
                      });
                });
            })
            ->selectRaw('DATE(waktu_scan_out) as date, SUM(qty) as total_actual')
            ->groupBy('date')
            ->pluck('total_actual', 'date')
            ->toArray();

        $chartPlan = [];
        $chartActual = [];
        $chartRate = [];
        $daysLabel = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateObj = $selectedMonth->copy()->day($i);
            $dateStr = $dateObj->format('Y-m-d');
            $daysLabel[] = $i . ' ' . $dateObj->isoFormat('MMM');
            
            $p = $dailyPlans[$dateStr] ?? 0;
            $a = $dailyActuals[$dateStr] ?? 0;
            $r = $p > 0 ? ($a / $p) * 100 : 0; 
            
            $chartPlan[] = $p;
            $chartActual[] = $a;
            $chartRate[] = round($r, 1);
        }

        // --- Detailed Table (Top Parts Performance) ---
        // Group by Part to show stats
        $partPerformance = TSpkDetail::join('t_spk', 't_spk_detail.spk_id', '=', 't_spk.id')
            ->whereBetween('t_spk.tanggal', [$startDate, $endDate])
            ->join('sm_part', 't_spk_detail.part_id', '=', 'sm_part.id')
            ->when($filterCustomer, function($q) use ($filterCustomer) {
                $q->where('t_spk.customer_id', $filterCustomer);
            })
            ->when($filterPart, function($q) use ($filterPart) {
                $q->where(function($query) use ($filterPart) {
                    $query->where('sm_part.nomor_part', 'like', "%$filterPart%")
                          ->orWhere('sm_part.nama_part', 'like', "%$filterPart%")
                          ->orWhere('sm_part.model_part', 'like', "%$filterPart%")
                          ->orWhere('sm_part.tipe_id', 'like', "%$filterPart%")
                          ->orWhereHas('part.customer', function($sq) use ($filterPart) {
                              $sq->where('nama_perusahaan', 'like', "%$filterPart%")
                                ->orWhere('inisial_perusahaan', 'like', "%$filterPart%");
                          });
                });
            })
            ->select(
                'sm_part.id as part_id',
                'sm_part.nomor_part',
                'sm_part.nama_part',
                'sm_part.model_part',
                't_spk.customer_id',
                DB::raw('SUM(t_spk_detail.jadwal_delivery_pcs) as total_plan')
            )
            ->groupBy('sm_part.id', 'sm_part.nomor_part', 'sm_part.nama_part', 'sm_part.model_part', 't_spk.customer_id')
            ->orderByDesc('total_plan')
            ->get();

        $performanceData = [];
        foreach ($partPerformance as $item) {
            $actual = TFinishGoodOut::whereBetween('waktu_scan_out', [$startDate, $endDate])
                ->where('part_id', $item->part_id)
                ->sum('qty');
            
            // PO is month based, might not align perfectly with SPK if multiple SPKs
            // But let's try fetch
            $poQty = TPurchaseOrderCustomer::where('month', $selectedMonth->month)
                ->where('year', $selectedMonth->year)
                ->where('part_id', $item->part_id)
                ->sum('qty');

            $rate = $item->total_plan > 0 ? ($actual / $item->total_plan) * 100 : 0;
            
            $custName = MPerusahaan::find($item->customer_id)->nama_perusahaan ?? '-';

            $performanceData[] = [
                'part_name' => $item->nama_part,
                'part_number' => $item->nomor_part,
                'model' => $item->model_part,
                'customer_name' => $custName,
                'po' => $poQty,
                'plan' => $item->total_plan,
                'actual' => $actual,
                'rate' => $rate
            ];
        }

        // --- Chart: Monthly Trend (Last 12 Months) ---
        $monthlyLabels = [];
        $monthlyRate = [];
        
        for ($i = 11; $i >= 0; $i--) {
             $m = Carbon::now()->subMonths($i);
             $monthlyLabels[] = $m->format('M');
             
             // PO for that month
             $mPO = TPurchaseOrderCustomer::where('month', $m->month)
                ->where('year', $m->year)
                ->when($filterPart, function($q) use ($filterPart) {
                    $q->whereHas('part', function($sq) use ($filterPart) {
                         $sq->where('nomor_part', 'like', "%$filterPart%");
                    });
                })
                ->sum('qty');
                
             // Actual for that month
             $mActual = TFinishGoodOut::whereMonth('waktu_scan_out', $m->month)
                ->whereYear('waktu_scan_out', $m->year)
                ->when($filterCustomer, function($q) use ($filterCustomer) {
                    $q->whereHas('spk', function($sq) use ($filterCustomer) {
                        $sq->where('customer_id', $filterCustomer);
                    });
                })
                ->when($filterPart, function($q) use ($filterPart) {
                    $q->whereHas('part', function($sq) use ($filterPart) {
                        $sq->where('nomor_part', 'like', "%$filterPart%");
                    });
                })
                ->sum('qty');
             
             $rate = $mPO > 0 ? ($mActual / $mPO) * 100 : 0;
             $monthlyRate[] = round($rate, 1);
        }

        // Dropdown Data
        $customers = MPerusahaan::orderBy('nama_perusahaan')->get();

        return view('dashboard.delivery.index', compact(
            'totalPO', 'totalPlan', 'totalActual', 'pendingDelivery', 
            'serviceRatePlan', 'serviceRatePO', 
            'chartPlan', 'chartActual', 'chartRate', 'daysLabel',
            'monthlyLabels', 'monthlyRate',
            'performanceData', 'customers'
        ));
    }
}
