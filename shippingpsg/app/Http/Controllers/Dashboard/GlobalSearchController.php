<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TShippingDeliveryHeader;
use App\Models\TStockFG;
use App\Models\TSpk;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $results = [];

        // 1. Search Delivery (Surat Jalan/Destination)
        if (class_exists(TShippingDeliveryHeader::class)) {
            $deliveries = TShippingDeliveryHeader::where('no_surat_jalan', 'LIKE', "%{$query}%")
                ->orWhere('destination', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($deliveries as $delivery) {
                $results[] = [
                    'category' => 'Delivery',
                    'title' => $delivery->no_surat_jalan,
                    'subtitle' => $delivery->destination,
                    'url' => '#', // Placeholder
                    'icon' => 'truck',
                    'color' => 'indigo'
                ];
            }
        }

        // 2. Search Stock FG (Part Name/Number)
        if (class_exists(TStockFG::class)) {
            // Assuming TStockFG relates to Part which has name
            $stocks = TStockFG::with('part')
                ->whereHas('part', function($q) use ($query) {
                    $q->where('nama_part', 'LIKE', "%{$query}%")
                      ->orWhere('nomor_part', 'LIKE', "%{$query}%");
                })
                ->limit(5)
                ->get();

            foreach ($stocks as $stock) {
                $results[] = [
                    'category' => 'Stock FG',
                    'title' => $stock->part->nama_part ?? 'Unknown Part',
                    'subtitle' => 'Qty: ' . $stock->qty,
                    'url' => '#', 
                    'icon' => 'cube',
                    'color' => 'blue'
                ];
            }
        }

        // 3. Search SPK
        if (class_exists(TSpk::class)) {
            $spks = TSpk::where('nomor_spk', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($spks as $spk) {
                $results[] = [
                    'category' => 'SPK',
                    'title' => $spk->nomor_spk,
                    'subtitle' => $spk->status ?? 'Active',
                    'url' => '#',
                    'icon' => 'document',
                    'color' => 'amber'
                ];
            }
        }

        return response()->json($results);
    }
}
