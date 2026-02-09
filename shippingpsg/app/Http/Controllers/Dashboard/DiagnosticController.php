<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TShippingDeliveryHeader;
use App\Models\TStockFG;
use App\Models\TSpk;

class DiagnosticController extends Controller
{
    public function run()
    {
        // 1. Delivery Performance (Completed vs Total Deliveries)
        $totalDeliveries = TShippingDeliveryHeader::count();
        $delivered = TShippingDeliveryHeader::where('status', 'DELIVERED')->count();
        $deliveryHealth = $totalDeliveries > 0 ? round(($delivered / $totalDeliveries) * 100) : 100;
        $activeDeliveries = $totalDeliveries - $delivered;

        // 2. Stock Health (Stock not empty)
        $totalItems = TStockFG::count();
        $nonZeroStock = TStockFG::where('qty', '>', 0)->count();
        $stockHealth = $totalItems > 0 ? round(($nonZeroStock / $totalItems) * 100) : 100;

        // 3. Document SPK Completeness (SPK with Surat Jalan)
        $totalSPK = TSpk::count();
        $spkWithSJ = TSpk::whereNotNull('no_surat_jalan')->count();
        $docHealth = $totalSPK > 0 ? round(($spkWithSJ / $totalSPK) * 100) : 100;
        $pendingSPK = $totalSPK - $spkWithSJ;

        // Overall Health
        $overallHealth = round(($deliveryHealth + $stockHealth + $docHealth) / 3);

        return response()->json([
            'status' => 'success',
            'overall_health' => $overallHealth,
            'metrics' => [
                [
                    'label' => 'Delivery On-Time',
                    'value' => $deliveryHealth,
                    'color' => 'indigo',
                    'message' => $activeDeliveries > 0 ? "$activeDeliveries trips in progress." : 'All deliveries completed.'
                ],
                [
                    'label' => 'Stock Availability',
                    'value' => $stockHealth,
                    'color' => 'blue',
                    'message' => ($totalItems - $nonZeroStock) . " items out of stock."
                ],
                [
                    'label' => 'Document Completeness',
                    'value' => $docHealth,
                    'color' => 'amber',
                    'message' => "$pendingSPK SPK pending shipment."
                ]
            ]
        ]);
    }
}
