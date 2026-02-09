<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MPerusahaan;
use App\Models\MMesin;
use App\Models\MManpower;
use App\Models\SMPart;
use Illuminate\Support\Facades\DB;

class DiagnosticController extends Controller
{
    public function run()
    {
        // 1. Manpower Completeness (Check if NIK and Nama exists)
        $totalManpower = MManpower::count();
        $invalidManpower = MManpower::whereNull('nik')->orWhereNull('nama')->count();
        $manpowerHealth = $totalManpower > 0 ? round((($totalManpower - $invalidManpower) / $totalManpower) * 100) : 100;

        // 2. Part Specs Valid (Check if important fields are filled)
        $totalParts = SMPart::count();
        // Assuming some fields like 'nomor_part', 'nama_part' are critical
        $invalidParts = SMPart::whereNull('nomor_part')->orWhereNull('nama_part')->count();
        $partHealth = $totalParts > 0 ? round((($totalParts - $invalidParts) / $totalParts) * 100) : 100;

        // 3. Supplier Docs (Simulated for now as we don't have a docs table yet)
        // Let's assume MPerusahaan 'status' active means docs are okay
        $totalSupplier = MPerusahaan::count();
        $activeSupplier = MPerusahaan::where('status', 1)->count();
        $supplierHealth = $totalSupplier > 0 ? round(($activeSupplier / $totalSupplier) * 100) : 100;

        // Overall Health
        $overallHealth = round(($manpowerHealth + $partHealth + $supplierHealth) / 3);

        return response()->json([
            'status' => 'success',
            'overall_health' => $overallHealth,
            'metrics' => [
                [
                    'label' => 'Manpower Completeness',
                    'value' => $manpowerHealth,
                    'color' => 'emerald',
                    'message' => $invalidManpower > 0 ? "$invalidManpower profiles incomplete." : 'All profiles complete.'
                ],
                [
                    'label' => 'Part Specs Valid',
                    'value' => $partHealth,
                    'color' => 'blue',
                    'message' => $invalidParts > 0 ? "$invalidParts parts missing specs." : 'All specs valid.'
                ],
                [
                    'label' => 'Supplier Active Rate',
                    'value' => $supplierHealth,
                    'color' => 'amber',
                    'message' => ($totalSupplier - $activeSupplier) . " suppliers inactive."
                ]
            ]
        ]);
    }
}
