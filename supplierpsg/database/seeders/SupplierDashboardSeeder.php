<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MPerusahaan;
use App\Models\MBahanBaku;
use App\Models\TScheduleHeader;
use App\Models\TScheduleDetail;
use Carbon\Carbon;

class SupplierDashboardSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Suppliers
        $suppliers = [
            [
                'nama_perusahaan' => 'PT Caturindo Perkasa',
                'inisial_perusahaan' => 'CATURINDO',
                'jenis_perusahaan' => 'supplier',
                'customer_type' => null,
                'kode_supplier' => 'SUP001',
                'alamat' => 'Jakarta',
                'status' => true,
            ],
            [
                'nama_perusahaan' => 'PT Nagase Impor Indonesia',
                'inisial_perusahaan' => 'NAGASE',
                'jenis_perusahaan' => 'supplier',
                'customer_type' => null,
                'kode_supplier' => 'SUP002',
                'alamat' => 'Karawang',
                'status' => true,
            ],
            [
                'nama_perusahaan' => 'PT Hexindo Trimitra Perkasa',
                'inisial_perusahaan' => 'HTI',
                'jenis_perusahaan' => 'supplier',
                'customer_type' => null,
                'kode_supplier' => 'SUP003',
                'alamat' => 'Bekasi',
                'status' => true,
            ],
            [
                'nama_perusahaan' => 'PT Dharma Electrindo Manufacturing',
                'inisial_perusahaan' => 'DHARMA',
                'jenis_perusahaan' => 'supplier',
                'customer_type' => null,
                'kode_supplier' => 'SUP004',
                'alamat' => 'Cikarang',
                'status' => true,
            ],
            [
                'nama_perusahaan' => 'PT Karya Putra Sangkuriang',
                'inisial_perusahaan' => 'KPS',
                'jenis_perusahaan' => 'supplier',
                'customer_type' => null,
                'kode_supplier' => 'SUP005',
                'alamat' => 'Bandung',
                'status' => true,
            ],
        ];

        $createdSuppliers = [];
        foreach ($suppliers as $supplier) {
            $createdSuppliers[] = MPerusahaan::create($supplier);
        }

        // 2. Create Bahan Baku (Raw Materials) for each supplier
        $materials = [
            // AHM Materials
            ['nama_bahan_baku' => 'PP Natural Grade A', 'kategori' => 'material', 'supplier_id' => $createdSuppliers[0]->id],
            ['nama_bahan_baku' => 'PP Black MB-001', 'kategori' => 'masterbatch', 'supplier_id' => $createdSuppliers[0]->id],
            ['nama_bahan_baku' => 'Carton Box Type A', 'kategori' => 'box', 'supplier_id' => $createdSuppliers[0]->id],
            
            // INOAC Materials
            ['nama_bahan_baku' => 'PE Film Clear', 'kategori' => 'polybag', 'supplier_id' => $createdSuppliers[1]->id],
            ['nama_bahan_baku' => 'ABS Resin Natural', 'kategori' => 'material', 'supplier_id' => $createdSuppliers[1]->id],
            ['nama_bahan_baku' => 'Layer Sheet 1200x800', 'kategori' => 'layer', 'supplier_id' => $createdSuppliers[1]->id],
            
            // YIMM Materials
            ['nama_bahan_baku' => 'PC Transparent Grade', 'kategori' => 'material', 'supplier_id' => $createdSuppliers[2]->id],
            ['nama_bahan_baku' => 'Red MB-R02', 'kategori' => 'masterbatch', 'supplier_id' => $createdSuppliers[2]->id],
            ['nama_bahan_baku' => 'Rempart Divider', 'kategori' => 'rempart', 'supplier_id' => $createdSuppliers[2]->id],
            
            // DENSO Materials
            ['nama_bahan_baku' => 'PA6 Engineering Plastic', 'kategori' => 'material', 'supplier_id' => $createdSuppliers[3]->id],
            ['nama_bahan_baku' => 'White MB-W05', 'kategori' => 'masterbatch', 'supplier_id' => $createdSuppliers[3]->id],
            
            // FNI Materials
            ['nama_bahan_baku' => 'POM Acetal Resin', 'kategori' => 'material', 'supplier_id' => $createdSuppliers[4]->id],
            ['nama_bahan_baku' => 'Carton Box Type B', 'kategori' => 'box', 'supplier_id' => $createdSuppliers[4]->id],
        ];

        $createdMaterials = [];
        foreach ($materials as $material) {
            $createdMaterials[] = MBahanBaku::create($material);
        }

        // 3. Create Schedule Data for last 30 days (Daily) and last 12 months (Monthly)
        $today = Carbon::today();
        
        // Generate daily data for last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $periode = $date->format('Y-m');
            
            foreach ($createdMaterials as $material) {
                // Random plan quantity between 500-2000
                $totalPlan = rand(500, 2000);
                
                // Random achievement between 80-105%
                $achievementPercent = rand(80, 105);
                $totalAct = (int)($totalPlan * ($achievementPercent / 100));
                
                // Create header
                $header = TScheduleHeader::create([
                    'periode' => $periode,
                    'supplier_id' => $material->supplier_id,
                    'bahan_baku_id' => $material->id,
                    'total_plan' => $totalPlan,
                    'total_act' => $totalAct,
                ]);
                
                // Create detail for this specific date
                TScheduleDetail::create([
                    'schedule_header_id' => $header->id,
                    'tanggal' => $date->format('Y-m-d'),
                    'pc_plan' => $totalPlan,
                    'pc_act' => $totalAct,
                ]);
            }
        }
        
        // Generate monthly aggregate data for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = $today->copy()->subMonths($i);
            $periode = $monthDate->format('Y-m');
            
            // Skip current month as it's already populated by daily data
            if ($periode === $today->format('Y-m')) {
                continue;
            }
            
            foreach ($createdMaterials as $material) {
                // Random monthly plan quantity between 15000-50000
                $totalPlan = rand(15000, 50000);
                
                // Random achievement between 85-100%
                $achievementPercent = rand(85, 100);
                $totalAct = (int)($totalPlan * ($achievementPercent / 100));
                
                // Create header only (no daily details for past months)
                TScheduleHeader::create([
                    'periode' => $periode,
                    'supplier_id' => $material->supplier_id,
                    'bahan_baku_id' => $material->id,
                    'total_plan' => $totalPlan,
                    'total_act' => $totalAct,
                ]);
            }
        }

        $this->command->info('✅ Supplier Dashboard data seeded successfully!');
        $this->command->info('   - ' . count($createdSuppliers) . ' Suppliers created');
        $this->command->info('   - ' . count($createdMaterials) . ' Materials created');
        $this->command->info('   - Schedule data generated for last 30 days and 12 months');
    }
}
