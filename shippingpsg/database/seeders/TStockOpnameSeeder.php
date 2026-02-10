<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TStockOpname;
use App\Models\SMPart;
use App\Models\MManpower;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TStockOpnameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset Table data safely
        if (Schema::hasTable('t_stock_opname')) {
            // Disable foreign key checks to allow truncate
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            TStockOpname::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Define 10 Specific Parts from image
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

        // 3. Find Manpower (or create dummy)
        $manpower = MManpower::first();
        if (!$manpower) {
            // Fallback create dummy admin manually via DB to avoid Model fillable issues
            // Check if m_manpower table exists first
            if (Schema::hasTable('m_manpower')) {
                 $manpowerId = DB::table('m_manpower')->insertGetId([
                    'nama' => 'Admin Stock',
                    'nik' => '99999',
                    'jabatan' => 'Admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $manpowerId = null; // Should be nullable in t_stock_opname
            }
        } else {
            $manpowerId = $manpower->id;
        }

        // 4. Seed logic
        foreach ($targetParts as $partNumber) {
            // Find or Create Part to ensure ID exists
            // We ensure we only fill existing columns
            
            // Check if customer exists for FG
            $custId = DB::table('m_perusahaan')->value('id') ?? 1;

            $part = SMPart::firstOrCreate(
                ['nomor_part' => $partNumber],
                [
                    'nama_part' => 'Part ' . $partNumber, 
                    'customer_id' => $custId, 
                    'QTY_Packing_Box' => 24
                ]
            );

            // Generate Random Quantities
            // Scenario: mostly match, sometimes diff
            $qtySystem = rand(100, 500);
            
            // 20% chance of discrepancy
            if (rand(1, 10) > 8) {
                // Actual is slightly different (+/- 5%)
                $diff = rand(-5, 5); 
                if ($diff == 0) $diff = 1; // Ensure diff if chance hit
                // Ensure actual isn't negative
                $qtyActual = max(0, $qtySystem + $diff);
            } else {
                $qtyActual = $qtySystem;
            }

            $diff = $qtyActual - $qtySystem;
            
            TStockOpname::create([
                'part_id' => $part->id,
                'qty_system' => $qtySystem,
                'qty_actual' => $qtyActual,
                'diff' => $diff,
                'manpower_id' => $manpowerId,
                'keterangan' => $diff !== 0 ? 'Selisih ditemukan saat counting' : 'Sesuai system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('Seeding TStockOpname complete with 10 specific parts!');
    }
}
