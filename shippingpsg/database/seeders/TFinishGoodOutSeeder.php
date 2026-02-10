<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TFinishGoodOut;
use App\Models\TFinishGoodIn;
use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\MManpower;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TFinishGoodOutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get today's SPK (Cycle 1)
        $spks = TSpk::whereDate('tanggal', Carbon::today())
            ->where('cycle_number', 1)
            ->get();

        if ($spks->isEmpty()) {
            $this->command->warn('No SPK C1 found for today. Please run TSpkSeeder first.');
            return;
        }

        // Use a generic manpower for scan out
        $mp = MManpower::where('nik', 'MP040')->first() ?: MManpower::first();
        $mpId = $mp ? $mp->id : 40;

        foreach ($spks as $spk) {
            $details = TSpkDetail::where('spk_id', $spk->id)->get();
            
            foreach ($details as $detail) {
                // User: "jangan langsung di close"
                // Target: scan about 60% of the required quantity to keep it "Open" but in progress
                $targetQty = floor($detail->jadwal_delivery_pcs * 0.6);
                $scannedQty = 0;
                
                // Qty per box
                $qtyPerBox = $detail->qty_packing_box > 0 ? $detail->qty_packing_box : 24;

                // 2. Find available FinishGoodIn records for this part
                // Avoid double counting
                $existingOutIds = TFinishGoodOut::pluck('finish_good_in_id')->filter()->toArray();
                
                $availableIn = TFinishGoodIn::where('part_id', $detail->part_id)
                    ->whereNotIn('id', $existingOutIds)
                    ->orderBy('waktu_scan', 'asc')
                    ->get();

                foreach ($availableIn as $inRecord) {
                    if ($scannedQty >= $targetQty) break;

                    TFinishGoodOut::create([
                        'finish_good_in_id' => $inRecord->id,
                        'lot_number' => $inRecord->lot_number ?? ($inRecord->part->nomor_part ?? 'LOT-'.time()),
                        'lot_produksi' => $inRecord->lot_produksi,
                        'manpower_id' => $mpId,
                        'spk_id' => $spk->id,
                        'part_id' => $detail->part_id,
                        'waktu_scan_out' => Carbon::now(),
                        'cycle' => $spk->cycle_number, // C1
                        'qty' => $inRecord->qty,
                    ]);

                    $scannedQty += $inRecord->qty;
                }

                // 3. If not enough In records exist, create dummy ones to simulate the scan flow
                if ($scannedQty < $targetQty) {
                    $neededQtyRemaining = $targetQty - $scannedQty;
                    $numBoxes = ceil($neededQtyRemaining / $qtyPerBox);

                    for ($i = 0; $i < $numBoxes; $i++) {
                        // Create dummy IN record (as if it was scanned earlier)
                        $in = TFinishGoodIn::create([
                            'part_id' => $detail->part_id,
                            'qty' => $qtyPerBox,
                            'waktu_scan' => Carbon::now()->subHours(2),
                            'customer' => $spk->customer->nama_perusahaan ?? 'INOAC',
                            'manpower_id' => $mpId,
                        ]);

                        // Record the scan OUT
                        TFinishGoodOut::create([
                            'finish_good_in_id' => $in->id,
                            'lot_number' => $in->lot_number ?? ($in->part->nomor_part ?? 'LOT-'.time()),
                            'lot_produksi' => $in->lot_produksi,
                            'manpower_id' => $mpId,
                            'spk_id' => $spk->id,
                            'part_id' => $detail->part_id,
                            'waktu_scan_out' => Carbon::now(),
                            'cycle' => $spk->cycle_number,
                            'qty' => $in->qty,
                        ]);
                        
                        $scannedQty += $in->qty;
                    }
                }
            }
        }

        $this->command->info('TFinishGoodOutSeeder completed! Processed ~60% of SPK C1 quantity.');
    }
}
