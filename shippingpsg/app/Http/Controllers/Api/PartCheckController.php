<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SMPart;
use Illuminate\Http\JsonResponse;

class PartCheckController extends Controller
{
    /**
     * Check part information by part number
     * Used for Inoac special handling (scan part number only)
     */
    public function check(string $partNumber): JsonResponse
    {
        $part = SMPart::with('customer')
            ->where('nomor_part', trim($partNumber))
            ->first();

        if (!$part) {
            return response()->json([
                'success' => false,
                'message' => 'Part tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'nomor_part' => $part->nomor_part,
            'nama_part' => $part->nama_part,
            'customer' => $part->customer->nama_perusahaan ?? null,
            'customer_id' => $part->customer_id,
            'qty_packing_box' => $part->QTY_Packing_Box ?? 24,
        ]);
    }
}
