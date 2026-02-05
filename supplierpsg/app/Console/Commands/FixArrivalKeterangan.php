<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixArrivalKeterangan extends Command
{
    protected $signature = 'fix:arrival-keterangan';
    protected $description = 'Fix ARRIVED_CUSTOMER keterangan to include cycle info from BERANGKAT';

    public function handle()
    {
        $details = \App\Models\TShippingDeliveryDetail::where('keterangan', 'ARRIVED_CUSTOMER')->get();
        $this->info("Found " . $details->count() . " details to fix.");
        
        foreach ($details as $d) {
            $b = \App\Models\TShippingDeliveryDetail::where('delivery_header_id', $d->delivery_header_id)
                ->where('keterangan', 'like', 'BERANGKAT%')
                ->first();
                
            if ($b) {
                $parts = explode('|', $b->keterangan);
                $parts[0] = 'DATANG'; // Change BERANGKAT to DATANG
                $newKet = implode('|', $parts);
                
                $d->keterangan = $newKet;
                $d->save();
                $this->info("Updated Detail ID {$d->id} to {$newKet}");
            } else {
                $this->warn("No BERANGKAT detail found for Header ID {$d->delivery_header_id}");
            }
        }
    }
}
