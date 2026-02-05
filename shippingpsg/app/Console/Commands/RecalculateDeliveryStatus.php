<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TShippingDeliveryHeader;
use App\Models\TSpk;
use Carbon\Carbon;

class RecalculateDeliveryStatus extends Command
{
    protected $signature = 'delivery:recalculate-status {delivery_id?}';
    protected $description = 'Recalculate delivery status based on SPK planned time';

    public function handle()
    {
        $deliveryId = $this->argument('delivery_id');
        
        if ($deliveryId) {
            $deliveries = TShippingDeliveryHeader::where('id', $deliveryId)->get();
        } else {
            // Recalculate all deliveries from today
            $deliveries = TShippingDeliveryHeader::whereDate('tanggal_berangkat', today())
                ->whereNotNull('waktu_berangkat')
                ->get();
        }

        if ($deliveries->isEmpty()) {
            $this->error('No deliveries found.');
            return 1;
        }

        foreach ($deliveries as $delivery) {
            // Find the SPK
            $spk = TSpk::where('no_surat_jalan', $delivery->no_surat_jalan)->first();
            
            if (!$spk || !$spk->jam_berangkat_plan) {
                $this->warn("Skipping Delivery #{$delivery->id} - No SPK or planned time found");
                continue;
            }

            $actualTime = Carbon::parse($delivery->waktu_berangkat, 'Asia/Jakarta');
            $planDateTime = Carbon::parse($delivery->tanggal_berangkat->format('Y-m-d') . ' ' . $spk->jam_berangkat_plan, 'Asia/Jakarta');
            $diffInMinutes = $actualTime->diffInMinutes($planDateTime, false);

            $oldStatus = $delivery->status;
            
            if ($diffInMinutes > 5) {
                $newStatus = 'ADVANCED';
            } elseif ($diffInMinutes < -5) {
                $newStatus = 'DELAY';
            } else {
                $newStatus = 'NORMAL';
            }

            $delivery->update(['status' => $newStatus]);
            
            $this->info("Delivery #{$delivery->id}: {$oldStatus} → {$newStatus} (Diff: {$diffInMinutes} mins)");
        }

        $this->info('Status recalculation completed!');
        return 0;
    }
}
