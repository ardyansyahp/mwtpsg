<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TFinishGoodIn;
use App\Models\SMPart;
use App\Models\MManpower;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TFinishGoodInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset Table
        if (Schema::hasTable('t_finishgood_in')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            TFinishGoodIn::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Define 10 Specific Inoac Parts
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

        // 3. Set Manpower to ID 40 (Jumedi)
        // Ensure ID 40 exists
        $mpId = 40;
        $jumedi = MManpower::find($mpId);
        if (!$jumedi) {
            // Force create ID 40 if possible, or update if user knows what they are doing
            // Since we can't easily force ID on auto-increment without raw SQL, 
            // we'll try raw insert if not exists.
            
            // Check if ID 40 is free
            DB::table('m_manpower')->insertOrIgnore([
                'id' => 40,
                'nama' => 'Jumedi',
                'nik' => 'MP040',
                'jabatan' => 'Operator',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            // Update name just in case
            $jumedi->update(['nama' => 'Jumedi']);
        }

        // 4. Generate Data (Daily for last 2 weeks)
        $startDate = Carbon::now()->subDays(13); // Start 13 days ago + today = 14 days
        
        foreach ($targetParts as $partNumber) {
            $part = SMPart::where('nomor_part', $partNumber)->first();
            
            if (!$part) continue;

            $customerName = $part->customer->nama_perusahaan ?? 'INOAC POLYTECHNO INDONESIA, PT';
            
            // Loop through each day of the last 2 weeks
            for ($day = 0; $day < 14; $day++) {
                
                $currentDate = $startDate->copy()->addDays($day);
                
                // Create 1 to 3 transactions per day
                $dailyTransactions = rand(1, 3);
                
                for ($txn = 0; $txn < $dailyTransactions; $txn++) {
                    
                    // Random time between 07:00 and 22:00
                    $scanTime = $currentDate->copy()->setTime(rand(7, 22), rand(0, 59), rand(0, 59));
                    
                    // Qty logic
                    $stdQty = $part->QTY_Packing_Box ?? 24; 
                    $qty = $stdQty * rand(1, 5); // 1-5 boxes

                    TFinishGoodIn::create([
                        'part_id' => $part->id,
                        'qty' => $qty,
                        'waktu_scan' => $scanTime,
                        'customer' => $customerName,
                        'manpower_id' => $mpId, // ID 40
                        
                        // Inoac Mode: Nulls
                        'lot_number' => null, 
                        'lot_produksi' => null,
                        'tanggal_produksi' => null,
                        'shift' => null,
                        'mesin_id' => null, 
                        'no_planning' => null,
                        'created_at' => $scanTime,
                        'updated_at' => $scanTime,
                    ]);
                }
            }
        }

        $this->command->info('Seeding TFinishGoodIn complete! (Manpower: Jumedi (40), Period: 14 Days Daily)');
    }
}
