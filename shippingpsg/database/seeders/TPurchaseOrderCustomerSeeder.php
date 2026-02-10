<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TPurchaseOrderCustomer;
use App\Models\SMPart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TPurchaseOrderCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset Table data safely
        if (Schema::hasTable('t_purchase_order_customer')) {
            // Disable foreign key checks to allow truncate
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            TPurchaseOrderCustomer::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Define 10 Specific Parts (same as Stock Opname for consistency)
        $targetParts = [
            'A-DHD36-PK005.1',
            'A-DHD36-PK006.1',
            'A-DHD66-PK003.0',
            'A-DHD66-PK004.0',
            'A-DHD66-PK006.0',
            'A-DHD66-PK007.0',
            'A-SKP16-PK001.0',
            'A-SKP16-PK002.0',
            'A-SKP16-PK005.1',
            'A-SKP16-PK006.1',
        ];

        // 3. Seed logic for 10 POs
        $poCounter = 1;
        $month = date('m'); // Current Month
        $year = date('Y');  // Current Year
        
        // Find a fallback customer ID just in case
        $defaultCustId = DB::table('m_perusahaan')->value('id');
        if (!$defaultCustId) {
             // Create dummy customer if table empty to satisfy FK if strictly checked (though sm_part logic handles it usually)
             $defaultCustId = DB::table('m_perusahaan')->insertGetId(['nama_perusahaan' => 'PT DUMMY', 'created_at'=>now(), 'updated_at'=>now()]);
        }

        foreach ($targetParts as $partNumber) {
            
            // Find Part
            $part = SMPart::where('nomor_part', $partNumber)->first();
            
            // If part not found (e.g. StockOpname seeder didn't run), create dummy
            if (!$part) {
                $part = SMPart::create([
                    'nomor_part' => $partNumber,
                    'nama_part' => 'Part ' . $partNumber, 
                    'customer_id' => $defaultCustId, 
                    'QTY_Packing_Box' => 24
                ]);
            }

            // Generate PO Number: PO/YYYY/MM/00X
            $poNumber = sprintf('PO/%s/%s/%03d', $year, $month, $poCounter);

            // Create PO
            TPurchaseOrderCustomer::create([
                'part_id' => $part->id,
                'po_number' => $poNumber,
                'qty' => rand(100, 900), // Random quantity (Hundreds)
                'delivery_frequency' => rand(1, 4), // Frequency
                'month' => (int)$month,
                'year' => (int)$year,
            ]);

            $poCounter++;
        }
        
        $this->command->info('Seeding TPurchaseOrderCustomer complete with 10 POs for linked parts!');
    }
}
