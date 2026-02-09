<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MPerusahaan;
use App\Models\MBahanBaku;
use App\Models\TScheduleHeader;
use Illuminate\Support\Facades\Route;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $results = [];

        // 1. Search Vendors (MPerusahaan) - Only if route exists
        // Route: None specific for viewing vendor detail in supplier app usually, but maybe controlsupplier.monitoring
        if (class_exists(MPerusahaan::class)) {
            $vendors = MPerusahaan::where('nama_perusahaan', 'LIKE', "%{$query}%")
                ->orWhere('kode_supplier', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();
            
            foreach ($vendors as $vendor) {
                $results[] = [
                    'category' => 'Vendor',
                    'title' => $vendor->nama_perusahaan,
                    'subtitle' => $vendor->kode_supplier,
                    'url' => '#', // Placeholder or linking to a vendor detail page if exists
                    'icon' => 'building',
                    'color' => 'emerald'
                ];
            }
        }

        // 2. Search Bahan Baku
        if (class_exists(MBahanBaku::class)) {
            $materials = MBahanBaku::where('nama_bahan_baku', 'LIKE', "%{$query}%")
                ->orWhere('nomor_bahan_baku', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($materials as $material) {
                $results[] = [
                    'category' => 'Material',
                    'title' => $material->nama_bahan_baku,
                    'subtitle' => $material->nomor_bahan_baku,
                    'url' => '#', // Placeholder
                    'icon' => 'cube',
                    'color' => 'blue'
                ];
            }
        }

        // 3. Search POs
        if (class_exists(TScheduleHeader::class)) {
            $pos = TScheduleHeader::where('po_number', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($pos as $po) {
                $results[] = [
                    'category' => 'Purchase Order',
                    'title' => $po->po_number,
                    'subtitle' => "Status: " . ($po->total_status ?? 'N/A'),
                    'url' => '#', 
                    'icon' => 'document',
                    'color' => 'purple'
                ];
            }
        }

        return response()->json($results);
    }
}
