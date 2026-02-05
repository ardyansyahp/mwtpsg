<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Models\MKendaraan;
use App\Models\MManpower;
use App\Models\TSpk;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatchController extends Controller
{
    /**
     * Tampilkan halaman assignment driver untuk SJ yang sudah ready.
     */
    public function index(Request $request)
    {
        if (!userCan('shipping.dispatch.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Base Query
        $query = TSpk::with(['customer', 'plantgate', 'driver'])
            ->whereNotNull('no_surat_jalan');

        // Logic Filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_spk', 'like', "%{$search}%")
                  ->orWhere('no_surat_jalan', 'like', "%{$search}%")
                  ->orWhere('nomor_plat', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('nama_perusahaan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Export Handler
        if ($request->has('export')) {
            return $this->processExport($query->get());
        }

        // Pagination
        $readySjs = $query->orderBy('updated_at', 'desc')
            ->paginate($request->per_page ?? 15);

        $drivers = MManpower::where(function($q) {
                $q->where('bagian', 'like', '%DRIVER%')
                  ->orWhere('bagian', 'like', '%SOPIR%')
                  ->orWhere('bagian', 'like', '%LOGISTIK%')
                  ->orWhere('bagian', 'like', '%DELIVERY%');
            })
            ->orderBy('nama')
            ->get();

        $trucks = MKendaraan::orderBy('nopol_kendaraan')
            ->get();

        return view('shipping.dispatch', compact('readySjs', 'drivers', 'trucks'));
    }

    private function processExport($data)
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=dispatch_report_" . date('Ymd_His') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['No SPK', 'No Surat Jalan', 'Tanggal', 'Customer', 'Plant Gate', 'Driver', 'Plat Nomor', 'Status'];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($data as $row) {
                fputcsv($file, [
                    $row->nomor_spk,
                    $row->no_surat_jalan,
                    $row->tanggal ? $row->tanggal->format('Y-m-d') : '-',
                    optional($row->customer)->nama_perusahaan,
                    optional($row->plantgate)->nama_plantgate,
                    optional($row->driver)->nama ?? 'Belum Ditugaskan',
                    $row->nomor_plat ?? 'Belum Ditugaskan',
                    $row->driver_id ? 'Assigned' : 'Open'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update driver dan armada untuk SPK/SJ tertentu.
     */
    public function assign(Request $request)
    {
        if (!userCan('shipping.dispatch.assign')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'spk_id' => 'required|exists:t_spk,id',
            'driver_id' => 'required|exists:m_manpower,id',
            'nomor_plat' => 'required|string',
        ]);

        try {
            $spk = TSpk::with(['customer', 'plantgate'])->findOrFail($request->spk_id);
            
            $spk->update([
                'driver_id' => $request->driver_id,
                'nomor_plat' => $request->nomor_plat,
            ]);

            // --- Create Control Truck Entry ---
            
            // 0. Cleanup: Remove any existing entries for this SPK to prevent duplicates/ghosts (e.g. if changing truck)
            // We search by the unique SPK identifier in the keterangan field
            $cleanupPattern = "%SPK:{$spk->nomor_spk}%";
            TShippingDeliveryDetail::where('keterangan', 'like', $cleanupPattern)->delete();

            // 1. Find or create kendaraan
            $kendaraan = MKendaraan::where('nopol_kendaraan', $request->nomor_plat)->first();
            if (!$kendaraan) {
                $kendaraan = MKendaraan::create([
                    'nopol_kendaraan' => $request->nomor_plat,
                    'jenis_kendaraan' => 'Truck',
                    'status' => true,
                ]);
            }

            // 2. Check/Create Header
            // We use today's date for monitoring purposes so it appears on the current dashboard.
            $today = now()->startOfDay();
            $header = TShippingDeliveryHeader::where('kendaraan_id', $kendaraan->id)
                ->whereDate('tanggal_berangkat', $today) 
                ->where('no_surat_jalan', $spk->no_surat_jalan)
                ->first();

            if (!$header) {
                $custName = $spk->customer->nama_perusahaan ?? '-';
                $gateName = $spk->plantgate->nama_plantgate ?? '';
                $destination = $custName . ($gateName ? " ($gateName)" : "");

                $header = TShippingDeliveryHeader::create([
                    'kendaraan_id' => $kendaraan->id,
                    'driver_id' => $request->driver_id,
                    'tanggal_berangkat' => $today,
                    'no_surat_jalan' => $spk->no_surat_jalan,
                    'periode' => $today->format('Y-m'),
                    'destination' => $destination,
                    'status' => 'OPEN',
                ]);
            } else {
                // Fix: Jika Header utk truck ini sudah ada (plan), update drivernya ke yang baru di-assign
                // Agar driver baru bisa melihat tugas ini di dashboardnya.
                if ($header->status === 'OPEN') {
                    $header->update([
                        'driver_id' => $request->driver_id
                    ]);
                }
            }

            // 3. Create Detail (if not exists for this SPK+SJ)
            // Determine cycle info
            $cycleNumber = $spk->cycle_number ?? 1;
            // Determine time
            // Use planned time if available, otherwise default or maybe current time? 
            // The user said "jam berangkat plan itu dari spk"
            $jam = $spk->jam_berangkat_plan ? (int) explode(':', $spk->jam_berangkat_plan)[0] : 8;

            $keterangan = "BERANGKAT|C{$cycleNumber}|SJ:{$spk->no_surat_jalan}|SPK:{$spk->nomor_spk}";

            // Check duplicate detail to avoid double entry if reassigned
            // Check duplicate detail to avoid double entry if reassigned
            $exists = TShippingDeliveryDetail::where('delivery_header_id', $header->id)
                ->where('keterangan', $keterangan)
                ->exists();

            if (!$exists) {
                TShippingDeliveryDetail::create([
                    'delivery_header_id' => $header->id,
                    'tanggal' => $spk->tanggal,
                    'jam' => $jam,
                    'waktu_update' => null,
                    'keterangan' => $keterangan,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Driver & Armada berhasil di-assign ke SJ ' . $spk->no_surat_jalan,
            ]);
        } catch (\Exception $e) {
            Log::error('Dispatch Assign Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan assignment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
