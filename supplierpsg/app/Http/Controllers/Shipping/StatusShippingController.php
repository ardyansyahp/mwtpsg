<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Models\TSpk;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatusShippingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        // 1. Get all delivery headers for this date to find active SJ numbers
        $activeSjs = \App\Models\TShippingDeliveryHeader::whereDate('tanggal_berangkat', $date)
            ->pluck('no_surat_jalan')
            ->filter()
            ->unique();

        // 2. Fetch SPKs that are EITHER:
        // - Dated for today
        // - OR have an active delivery today (even if SPK date is different)
        $spks = TSpk::with([
                'customer',
                'plantgate',
                'finishGoodOuts' => function($q) {
                    $q->orderBy('waktu_scan_out', 'desc');
                },
                'deliveryHeader' => function($q) use ($date) {
                    // We want to link to the delivery header of THAT specific date
                    $q->whereDate('tanggal_berangkat', $date);
                },
                'deliveryHeader.kendaraan',
                'deliveryHeader.driver',
                'deliveryHeader.details' => function($q) {
                    $q->orderBy('id', 'asc');
                },
                'deliveryHeader.incidents'
            ])
            ->where(function($q) use ($date, $activeSjs) {
                $q->whereDate('tanggal', $date)
                  ->orWhereIn('no_surat_jalan', $activeSjs);
            })
            ->orderBy('jam_berangkat_plan', 'asc')
            ->get();

        // Process data for the view
        $dashboardData = $spks->map(function ($spk) {
            
            // 1. Plan Info
            $planTime = $spk->jam_berangkat_plan ? Carbon::parse($spk->tanggal->format('Y-m-d') . ' ' . $spk->jam_berangkat_plan) : null;
            
            // 2. Pulling Info (Finish Good Out)
            $firstScanOut = $spk->finishGoodOuts->last(); // First scan (earliest because we ordered desc? Wait, let's fix query order)
            // Actually usually we want the *last* scan out to verify completion, or the first to see when it started.
            // Let's take the latest scan for "Completion" status.
            $lastScanOut = $spk->finishGoodOuts->first(); // Ordered desc, so first is latest
            
            $pullingStatus = 'PENDING';
            $pullingTime = null;
            
            if ($lastScanOut && $lastScanOut->waktu_scan_out) {
                $pullingTime = Carbon::parse($lastScanOut->waktu_scan_out);
                // Simple logic: if pulling time is before plan time, it's ON TIME (or based on some manufacturing lead time?). 
                // For now, let's just show the time.
                $pullingStatus = 'DONE';
            }

            // 3. Delivery Info
            $delivery = $spk->deliveryHeader;
            $deliveryStatus = 'OPEN';
            $departureTime = null;
            $arrivalTime = null;
            $finishedTime = null; // Back to PT
            $driverName = '-';
            $truckNo = '-';
            $departureStatus = 'NORMAL'; // NORMAL, ADVANCED, DELAY (PENDING)
            $arrivalProof = null;

            if ($delivery) {
                $driverName = $delivery->driver->nama ?? '-';
                $truckNo = $delivery->kendaraan->nopol_kendaraan ?? '-';
                $deliveryStatus = $delivery->status;

                // Determine Actual Times from Details or Header
                // We should use Details to get precise timestamps for events
                
                // Departure (Berangkat)
                if ($delivery->waktu_berangkat && $deliveryStatus !== 'OPEN') {
                     $departureTime = Carbon::parse($delivery->waktu_berangkat);
                }
                
                // Status Logic
                $departureStatus = $delivery->status; 

                // Arrival
                if ($delivery->waktu_tiba) {
                    $arrivalTime = Carbon::parse($delivery->waktu_tiba);
                }
                
                // Get Arrival Proof Photo - Look for ANY detail with photo
                $proofDetail = $delivery->details->whereNotNull('foto_bukti')->last();
                if ($proofDetail) {
                    $arrivalProof = $proofDetail->foto_bukti;
                }
                
                // Finished
                if ($delivery->status === 'COMPLETED') {
                    // Logic already handled by deliveryStatus check in view
                }
            }

            // Duration Calculations
            $durationPullingToDepart = ($pullingTime && $departureTime) ? $pullingTime->diffForHumans($departureTime, true) : '-';
            $durationTrip = ($departureTime && $arrivalTime) ? $departureTime->diffForHumans($arrivalTime, true) : '-';

            return (object) [
                'spk_no' => $spk->nomor_spk,
                'surat_jalan' => $spk->no_surat_jalan ?? '-',
                'customer' => $spk->customer->nama_perusahaan ?? '-',
                'plantgate' => $spk->plantgate->nama_plantgate ?? '',
                'plan_time' => $planTime,
                
                'pulling_status' => $pullingStatus,
                'pulling_time' => $pullingTime,
                
                'driver_name' => $driverName,
                'truck_no' => $truckNo,
                
                'delivery_status' => $deliveryStatus, // Overall status
                'departure_status_label' => $departureStatus, // ADVANCED, DELAY, NORMAL
                'departure_time' => $departureTime,
                
                'arrival_time' => $arrivalTime,
                'arrival_proof' => $arrivalProof,
                'incidents' => $delivery ? $delivery->incidents : collect([]),
                
                'duration_trip' => $durationTrip
            ];
        });

        return view('shipping.status_shipping', compact('dashboardData', 'date'));
    }
}
