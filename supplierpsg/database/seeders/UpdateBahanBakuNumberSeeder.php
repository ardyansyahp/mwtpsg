<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MBahanBaku;

class UpdateBahanBakuNumberSeeder extends Seeder
{
    public function run()
    {
        $materials = MBahanBaku::all();
        
        foreach ($materials as $material) {
            $material->nomor_bahan_baku = 'BB-' . str_pad($material->id, 5, '0', STR_PAD_LEFT);
            $material->save();
        }

        $this->command->info('✅ Updated ' . $materials->count() . ' materials with nomor_bahan_baku');
    }
}
