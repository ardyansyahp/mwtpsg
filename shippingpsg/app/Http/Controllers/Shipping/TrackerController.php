<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use App\Models\TGpsLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackerController extends Controller
{
    /**
     * Admin Map Dashboard
     */
    public function index()
    {
        // Get active deliveries for today (or recent)
        // We only care about trucks that are currently IN_TRANSIT or just ARRIVED
        $today = Carbon::today()->format('Y-m-d');
        
        // Initial load of data
        $deliveries = TShippingDeliveryHeader::with(['kendaraan', 'driver'])
            ->whereIn('status', ['IN_TRANSIT', 'ARRIVED', 'ADVANCED', 'NORMAL', 'DELAY', 'PENDING'])
            ->whereDate('tanggal_berangkat', '>=', $today)
            ->get();

        return view('shipping.tracker.admin', compact('deliveries'));
    }

    /**
     * Driver Tracker View
     */
    public function track($id)
    {
        $delivery = TShippingDeliveryHeader::with(['kendaraan', 'driver'])->findOrFail($id);
        
        // Validate if user is authorized driver (optional, skipping for experimental)
        
        return view('shipping.tracker.driver', compact('delivery'));
    }

    /**
     * API: Store Location Log
     */
    public function storeLocation(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required|exists:t_shipping_delivery_header,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'device_info' => 'nullable|string'
        ]);

        try {
            $delivery = TShippingDeliveryHeader::find($request->delivery_id);
            if (!$delivery) {
                 return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
            }

            // Stop tracking if completed
            if (in_array($delivery->status, ['COMPLETED', 'DELIVERED', 'CANCELLED'])) {
                return response()->json(['success' => false, 'message' => 'Tracking finished'], 400);
            }

            $now = Carbon::now();

            // 1. Log to History Table
            TGpsLog::create([
                'delivery_header_id' => $request->delivery_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'recorded_at' => $now,
                'device_info' => $request->device_info ?? $request->userAgent()
            ]);

            // 2. Update Latest Position in Detail (Current Hour Slot)
            TShippingDeliveryDetail::updateOrCreate(
                [
                    'delivery_header_id' => $delivery->id,
                    'tanggal' => $now->format('Y-m-d'),
                    'jam' => (int)$now->format('H'),
                ],
                    [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'lokasi_saat_ini' => "GPS Update " . $now->format('H:i'),
                        'waktu_update' => $now
                    ]
                );

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get Latest Locations for Admin Map
     */
    public function getDeliveryLocations()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Fetch deliveries that are active
        $deliveries = TShippingDeliveryHeader::with(['kendaraan', 'driver'])
            ->whereIn('status', ['IN_TRANSIT', 'ARRIVED', 'ADVANCED', 'NORMAL', 'DELAY', 'PENDING'])
            ->whereDate('tanggal_berangkat', '>=', $today)
            ->get()
            ->map(function ($d) {
                // Get latest detail with GPS
                $latestDetail = TShippingDeliveryDetail::where('delivery_header_id', $d->id)
                    ->whereNotNull('latitude')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('jam', 'desc')
                    ->first();

                // If no detail, check logs (fallback)
                if (!$latestDetail) {
                    $latestLog = TGpsLog::where('delivery_header_id', $d->id)
                        ->latest('recorded_at')
                        ->first();
                    
                    if ($latestLog) {
                        return [
                            'id' => $d->id,
                            'nopol' => $d->kendaraan->nopol_kendaraan ?? 'Unknown',
                            'driver' => $d->driver->nama ?? 'Unknown',
                            'status' => $d->status,
                            'lat' => $latestLog->latitude,
                            'lng' => $latestLog->longitude,
                            'last_update' => $latestLog->recorded_at->diffForHumans()
                        ];
                    }
                    return null;
                }

                return [
                    'id' => $d->id,
                    'nopol' => $d->kendaraan->nopol_kendaraan ?? 'Unknown',
                    'driver' => $d->driver->nama ?? 'Unknown',
                    'status' => $d->status,
                    'lat' => $latestDetail->latitude,
                    'lng' => $latestDetail->longitude,
                    'last_update' => $latestDetail->waktu_update ? $latestDetail->waktu_update->diffForHumans() : '-'
                ];
            })
            ->filter(); // Remove nulls

        return response()->json($deliveries->values());
    }
}
