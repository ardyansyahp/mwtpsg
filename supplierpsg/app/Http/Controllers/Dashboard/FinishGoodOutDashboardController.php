<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TFinishGoodOut;
use App\Models\SMPart;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinishGoodOutDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter Part
        $filterPart = $request->input('part_id');
        
        // Base Query
        $query = TFinishGoodOut::query();
        
        if ($filterPart) {
            // Cari nomor_part dari ID yang dipilih
            $selectedPart = SMPart::find($filterPart);
            
            if ($selectedPart) {
                // Ambil semua ID yang memiliki nomor_part yang sama (handle duplicate parts)
                $relatedPartIds = SMPart::where('nomor_part', $selectedPart->nomor_part)->pluck('id');
                $query->whereIn('part_id', $relatedPartIds);
            } else {
                // Fallback jika ID tidak ketemu
                $query->where('part_id', $filterPart);
            }
        }

        // 1. Statistik Periode
        $todayQty = (clone $query)->whereDate('waktu_scan_out', Carbon::today())->sum('qty');
        
        $weekQty = (clone $query)->whereBetween('waktu_scan_out', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('qty');
        
        $monthQty = (clone $query)->whereMonth('waktu_scan_out', Carbon::now()->month)
                                 ->whereYear('waktu_scan_out', Carbon::now()->year)
                                 ->sum('qty');
                                 
        $totalQty = (clone $query)->sum('qty');

        // 2. Chart Data (Trend Harian 30 Hari Terakhir)
        $chartData = [];
        $startDate = Carbon::now()->subDays(29); // 30 hari termasuk hari ini
        
        // Ambil data harian dari DB
        $dailyData = (clone $query)
            ->whereDate('waktu_scan_out', '>=', $startDate)
            ->selectRaw('DATE(waktu_scan_out) as date, SUM(qty) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Fill 0 untuk tanggal yang kosong
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $chartData['labels'][] = Carbon::parse($date)->format('d M');
            $chartData['values'][] = $dailyData[$date] ?? 0;
        }

        // Check if AJAX request
        if ($request->ajax() || $request->input('ajax')) {
            return response()->json([
                'todayQty' => $todayQty,
                'weekQty' => $weekQty,
                'monthQty' => $monthQty,
                'totalQty' => $totalQty,
                'chartData' => $chartData
            ]);
        }

        // 3. Dropdown Parts (Unique by nomor_part)
        // Ambil ID terbaru untuk setiap nomor_part yang unik
        $allParts = SMPart::select('nomor_part', 'nama_part')
            ->selectRaw('MAX(id) as id') // Ambil ID salah satu (terbaru/terbesar)
            ->groupBy('nomor_part', 'nama_part')
            ->orderBy('nomor_part')
            ->get();
            
        return view('dashboard.fgout', compact(
            'todayQty', 'weekQty', 'monthQty', 'totalQty',
            'chartData', 'allParts', 'filterPart'
        ));
    }
}
