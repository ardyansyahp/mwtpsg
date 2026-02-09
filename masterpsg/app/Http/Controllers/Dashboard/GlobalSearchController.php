<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MPerusahaan;
use App\Models\MMesin;
use App\Models\MManpower;
use App\Models\SMPart;
use Illuminate\Support\Facades\Route;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // 1. Search Perusahaan
        if (Route::has('master.perusahaan.index')) {
            $perusahaan = MPerusahaan::where('nama_perusahaan', 'like', "%{$query}%")
                ->orWhere('inisial_perusahaan', 'like', "%{$query}%")
                ->limit(3)
                ->get(['id', 'nama_perusahaan', 'inisial_perusahaan']);
                
            foreach($perusahaan as $item) {
                $results[] = [
                    'category' => 'Company',
                    'title' => $item->nama_perusahaan,
                    'subtitle' => $item->inisial_perusahaan ?? 'No Initial',
                    'url' => route('master.perusahaan.index', ['search' => $item->nama_perusahaan]),
                    'icon' => 'building',
                    'color' => 'blue'
                ];
            }
        }

        // 2. Search Mesin
        // Assuming route exists or fallback to #
        $mesinRoute = Route::has('master.mesin.index') ? route('master.mesin.index') : '#';
        $mesin = MMesin::where('no_mesin', 'like', "%{$query}%")
            ->orWhere('merk_mesin', 'like', "%{$query}%")
            ->limit(3)
            ->get(['id', 'no_mesin', 'merk_mesin']);

        foreach($mesin as $item) {
            $url = $mesinRoute !== '#' ? route('master.mesin.index', ['search' => $item->no_mesin]) : '#';
            $results[] = [
                'category' => 'Machine',
                'title' => $item->no_mesin,
                'subtitle' => $item->merk_mesin ?? 'Unknown Brand',
                'url' => $url,
                'icon' => 'cog',
                'color' => 'indigo'
            ];
        }
        
        // 3. Search Manpower
        $manpowerRoute = Route::has('master.manpower.index') ? route('master.manpower.index') : '#';
        $manpower = MManpower::where('nama', 'like', "%{$query}%")
            ->orWhere('nik', 'like', "%{$query}%") // Changed to 'nik' based on file check
            ->limit(3)
            ->get(['id', 'nama', 'nik']);

        foreach($manpower as $item) {
            $url = $manpowerRoute !== '#' ? route('master.manpower.index', ['search' => $item->nama]) : '#';
            $results[] = [
                'category' => 'Manpower',
                'title' => $item->nama,
                'subtitle' => $item->nik ?? 'No NIK',
                'url' => $url,
                'icon' => 'users',
                'color' => 'violet'
            ];
        }

        // 4. Search Part
        // Assuming route is submaster.part.index or master.part.index. Let's try submaster first based on folder structure.
        $partRouteBase = Route::has('submaster.part.index') ? 'submaster.part.index' : (Route::has('master.part.index') ? 'master.part.index' : null);
        
        $parts = SMPart::where('nama_part', 'like', "%{$query}%")
            ->orWhere('nomor_part', 'like', "%{$query}%")
            ->limit(3)
            ->get(['id', 'nama_part', 'nomor_part', 'model_part']);

        foreach($parts as $item) {
            $url = $partRouteBase ? route($partRouteBase, ['search' => $item->nomor_part]) : '#';
            $results[] = [
                'category' => 'Part',
                'title' => $item->nama_part,
                'subtitle' => $item->nomor_part . ' (' . ($item->model_part ?? '-') . ')',
                'url' => $url,
                'icon' => 'cube',
                'color' => 'sky'
            ];
        }

        return response()->json($results);
    }
}
