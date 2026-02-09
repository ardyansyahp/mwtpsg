<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use App\Models\TScheduleDetail;
use App\Models\MBahanBaku;
use Carbon\Carbon;

class ReceivingDataSeeder extends Seeder
{
    public function run()
    {
        $today = Carbon::today();
        
        // Get all schedule details from last 30 days that have pc_act > 0
        $scheduleDetails = TScheduleDetail::whereBetween('tanggal', [$today->copy()->subDays(29), $today])
            ->where('pc_act', '>', 0)
            ->with(['header.bahanBaku', 'header.supplier'])
            ->get();
        
        $receivingCount = 0;
        $detailCount = 0;
        
        foreach ($scheduleDetails as $scheduleDetail) {
            // Skip if no header or bahan baku
            if (!$scheduleDetail->header || !$scheduleDetail->header->bahanBaku) {
                continue;
            }
            
            $bahanBaku = $scheduleDetail->header->bahanBaku;
            $supplier = $scheduleDetail->header->supplier;
            
            // Create receiving for this schedule detail
            $receiving = Receiving::create([
                'tanggal_receiving' => $scheduleDetail->tanggal,
                'supplier_id' => $scheduleDetail->header->supplier_id,
                'no_surat_jalan' => 'SJ-' . Carbon::parse($scheduleDetail->tanggal)->format('Ymd') . '-' . str_pad($bahanBaku->id, 3, '0', STR_PAD_LEFT),
                'no_purchase_order' => 'PO-' . Carbon::parse($scheduleDetail->tanggal)->format('Ym') . '-' . str_pad($bahanBaku->id, 4, '0', STR_PAD_LEFT),
                'manpower' => 'Operator ' . rand(1, 5),
                'shift' => rand(1, 3),
            ]);
            
            $receivingCount++;
            
            // Create receiving detail
            ReceivingDetail::create([
                'receiving_id' => $receiving->id,
                'schedule_detail_id' => $scheduleDetail->id,
                'nomor_bahan_baku' => 'BB-' . str_pad($bahanBaku->id, 5, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-' . Carbon::parse($scheduleDetail->tanggal)->format('Ymd') . '-' . rand(1000, 9999),
                'internal_lot_number' => 'INT-' . Carbon::parse($scheduleDetail->tanggal)->format('Ymd') . '-' . rand(100, 999),
                'qty' => $scheduleDetail->pc_act,
                'uom' => 'KG',
                'qrcode' => 'QR-' . Carbon::parse($scheduleDetail->tanggal)->format('YmdHis') . '-' . $bahanBaku->id . '-' . rand(100, 999),
            ]);
            
            $detailCount++;
        }

        $this->command->info('✅ Receiving data seeded successfully!');
        $this->command->info('   - ' . $receivingCount . ' Receiving headers created');
        $this->command->info('   - ' . $detailCount . ' Receiving details created');
    }
}
