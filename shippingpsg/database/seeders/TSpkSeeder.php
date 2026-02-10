<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\TPurchaseOrderCustomer;
use App\Models\MPlantGate;
use App\Models\MManpower;
use App\Models\MKendaraan;
use App\Models\MPerusahaan;
use App\Models\SMPart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TSpkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get parts from PO
        $poParts = TPurchaseOrderCustomer::with('part.customer')->get();

        if ($poParts->isEmpty()) {
            $this->command->warn('No parts found in TPurchaseOrderCustomer. Please run TPurchaseOrderCustomerSeeder first.');
            return;
        }

        // Group by Customer to create one SPK per customer
        $groupedByCustomer = $poParts->groupBy(function ($po) {
            return $po->part->customer_id;
        });

        // 2. Prepare reference data (Fallback logic)
        $manpower = MManpower::first();
        if (!$manpower) {
            $manpower = MManpower::create([
                'mp_id' => 'ADMIN01',
                'nik' => '9999',
                'nama' => 'Admin Seeder',
                'departemen' => 'IT',
                'status' => true
            ]);
        }

        $kendaraan = MKendaraan::first();
        if (!$kendaraan) {
            $kendaraan = MKendaraan::create([
                'nopol_kendaraan' => 'B 1234 ABC',
                'jenis_kendaraan' => 'Truck',
                'status' => true
            ]);
        }

        foreach ($groupedByCustomer as $customerId => $pos) {
            $customer = MPerusahaan::find($customerId);
            if (!$customer) continue;

            // Find or create plantgate for this customer if none exists
            $plantgate = MPlantGate::where('customer_id', $customerId)->first();
            if (!$plantgate) {
                $plantgate = MPlantGate::create([
                    'customer_id' => $customerId,
                    'nama_plantgate' => 'Gate A - ' . ($customer->inisial_perusahaan ?? 'CUST'),
                    'status' => true
                ]);
            }

            // 3. Generate Nomor SPK: SPK-YYYYMMDD-XXXX
            $dateCode = date('Ymd');
            $prefix = "SPK-{$dateCode}-";
            $lastSpk = TSpk::where('nomor_spk', 'like', "{$prefix}%")
                ->orderBy('nomor_spk', 'desc')
                ->first();
            
            $nextSequence = 1;
            if ($lastSpk) {
                $lastNumberRaw = str_replace($prefix, '', $lastSpk->nomor_spk);
                $nextSequence = (int)$lastNumberRaw + 1;
            }
            $generatedSpkNumber = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            // 4. Create SPK
            // User: "cycle 1 di hari ini jam 1 siang selesai pulling, jam 2 berangkat, jam 5 sore kembali"
            $spk = TSpk::create([
                'nomor_spk' => $generatedSpkNumber,
                'customer_id' => $customerId,
                'plantgate_id' => $plantgate->id,
                'tanggal' => Carbon::today()->setTime(13, 0, 0), // 13:00 (1 PM) Pulling End
                'jam_berangkat_plan' => '14:00', // 14:00 (2 PM) Berangkat
                'jam_datang_plan' => '17:00',    // 17:00 (5 PM) Kembali
                'cycle_number' => 1,
                'manpower_pembuat' => $manpower->nama,
                'nomor_plat' => $kendaraan->nopol_kendaraan,
                'model_part' => $pos->first()->part->model_part ?? 'regular',
                'catatan' => 'Auto-generated for Today Delivery (Cycle 1)',
            ]);

            // 5. Create Details (Parts from PO)
            foreach ($pos as $po) {
                $part = $po->part;
                if (!$part) continue;

                $qtyPacking = $part->QTY_Packing_Box > 0 ? $part->QTY_Packing_Box : 24;
                
                // Example delivery qty: pick rand boxes based on PO qty but keep it safe
                $maxBoxes = floor($po->qty / $qtyPacking);
                if ($maxBoxes <= 0) $maxBoxes = 1;
                $numBoxes = rand(1, min($maxBoxes, 20)); // Limit to max 20 boxes for test
                
                $jadwalDelivery = $qtyPacking * $numBoxes;

                TSpkDetail::create([
                    'spk_id' => $spk->id,
                    'part_id' => $part->id,
                    'qty_packing_box' => $qtyPacking,
                    'jadwal_delivery_pcs' => $jadwalDelivery,
                    'original_jadwal_delivery_pcs' => $jadwalDelivery,
                    'jumlah_pulling_box' => $numBoxes,
                    'catatan' => 'PO: ' . $po->po_number,
                ]);

                // Optional: Link part to plantgate if not already linked (helpful for UI consistency)
                DB::table('SM_PlantGate_Part')->updateOrInsert([
                    'plantgate_id' => $plantgate->id,
                    'part_id' => $part->id,
                ], [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('TSpkSeeder completed successfully!');
    }
}
