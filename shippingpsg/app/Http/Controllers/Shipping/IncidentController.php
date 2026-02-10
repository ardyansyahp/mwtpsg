<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Models\TShippingIncident;
use App\Models\TShippingDeliveryHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class IncidentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'delivery_header_id' => 'required|exists:t_shipping_delivery_header,id',
                'keterangan' => 'required|string',
                'custom_keterangan' => 'nullable|string',
                'foto' => 'nullable|image|max:5120', // 5MB limit
                'latitude' => 'nullable',
                'longitude' => 'nullable',
            ]);

            $header = TShippingDeliveryHeader::with(['kendaraan', 'driver'])->findOrFail($request->delivery_header_id);
            
            $reason = $request->keterangan;
            if ($reason === 'LAINNYA') {
                $reason = $request->custom_keterangan;
            }

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('incidents', 'public');
            }

            $incident = TShippingIncident::create([
                'delivery_header_id' => $request->delivery_header_id,
                'keterangan' => $reason,
                'foto' => $fotoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'OPEN',
            ]);

            // Update status delivery? User didn't ask, but maybe useful.
            // For now just keep it as is.

            // Construct WA Message
            // Construct WA Message (Opsi 1: Share Mode for Groups/Personal)
            $truck = $header->kendaraan->nopol_kendaraan ?? '-';
            $driver = $header->driver->nama ?? '-';
            $sj = $header->no_surat_jalan ?? '-';
            $time = Carbon::now()->format('H:i');
            
            $message = "*LAPORAN KENDALA PENGIRIMAN*\n\n";
            $message .= "Surat Jalan: *$sj*\n";
            $message .= "Truck: *$truck*\n";
            $message .= "Driver: *$driver*\n";
            $message .= "Waktu: *$time*\n";
            $message .= "Kendala: *$reason*\n";
            if ($request->latitude && $request->longitude) {
                $message .= "Lokasi: https://www.google.com/maps?q={$request->latitude},{$request->longitude}\n";
            }
            if ($fotoPath) {
                $message .= "Foto Bukti terlampir di sistem.";
            }

            // Using Send API without specific phone number allows user to pick a group/contact
            $waLink = "https://api.whatsapp.com/send?text=" . urlencode($message);

            // --- AUTO NOTIFY OPS GROUP (Fonnte) ---
            try {
                $targetOps = env('FONNTE_GROUP_OPS', '0812xxxx');
                // \App\Helpers\FonnteHelper::send($targetOps, $message);
            } catch (\Exception $eWa) {
                // Ignore
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan kendala berhasil disimpan.',
                'wa_link' => $waLink
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
