<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MPerusahaan;
use App\Models\MBahanBaku;
use App\Models\TScheduleHeader;

class DiagnosticController extends Controller
{
    public function run()
    {
        // 1. Vendor Compliance (Active vs Total Vendors)
        $totalVendors = MPerusahaan::count();
        $activeVendors = MPerusahaan::where('status', 1)->count();
        $vendorHealth = $totalVendors > 0 ? round(($activeVendors / $totalVendors) * 100) : 100;
        $inactiveVendors = $totalVendors - $activeVendors;

        // 2. PO Fulfillment (Closed POs vs Total POs this month)
        $totalPOs = TScheduleHeader::count(); // Simplified for demo
        $closedPOs = TScheduleHeader::where('total_status', 'CLOSE')->count();
        $poHealth = $totalPOs > 0 ? round(($closedPOs / $totalPOs) * 100) : 100;

        // 3. Material Docs (Materials with valid number/name)
        $totalMaterials = MBahanBaku::count();
        $validMaterials = MBahanBaku::whereNotNull('nomor_bahan_baku')->whereNotNull('nama_bahan_baku')->count();
        $materialHealth = $totalMaterials > 0 ? round(($validMaterials / $totalMaterials) * 100) : 100;
        $invalidMaterials = $totalMaterials - $validMaterials;

        // Overall Health
        $overallHealth = round(($vendorHealth + $poHealth + $materialHealth) / 3);

        return response()->json([
            'status' => 'success',
            'overall_health' => $overallHealth,
            'metrics' => [
                [
                    'label' => 'Vendor Compliance',
                    'value' => $vendorHealth,
                    'color' => 'emerald',
                    'message' => $inactiveVendors > 0 ? "$inactiveVendors vendors inactive." : 'All vendors compliant.'
                ],
                [
                    'label' => 'PO Fulfillment',
                    'value' => $poHealth,
                    'color' => 'blue',
                    'message' => ($totalPOs - $closedPOs) . " POs open/pending."
                ],
                [
                    'label' => 'Material Docs',
                    'value' => $materialHealth,
                    'color' => 'amber',
                    'message' => $invalidMaterials > 0 ? "$invalidMaterials items missing specs." : 'All material docs valid.'
                ]
            ]
        ]);
    }
}
