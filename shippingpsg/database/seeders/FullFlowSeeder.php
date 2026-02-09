<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MPerusahaan;
use App\Models\SMPart;
use App\Models\MManpower;
use App\Models\MKendaraan;
use App\Models\MPlantGate;
use App\Models\TStockOpname;
use App\Models\TStockFG;
use App\Models\TPurchaseOrderCustomer;
use App\Models\TFinishGoodIn;
use App\Models\TFinishGoodOut;
use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\TShippingDeliveryHeader;
use App\Models\TShippingDeliveryDetail;
use Carbon\Carbon;

class FullFlowSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 1. Cleanup Transaction Tables
        $this->command->info('Cleaning up transaction tables...');
        TStockOpname::truncate();
        TPurchaseOrderCustomer::truncate();
        TFinishGoodIn::truncate();
        TFinishGoodOut::truncate();
        TStockFG::truncate();
        TSpkDetail::truncate();
        TSpk::truncate();
        TShippingDeliveryHeader::truncate();
        // TShippingDeliveryDetail::truncate(); // If exists
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Setup Master Data
        $this->command->info('Setting up Master Data...');

        // Customer: Inoac Polytechno Indonesia (IPI)
        $customer = MPerusahaan::firstOrCreate(
            ['nama_perusahaan' => 'Inoac Polytechno Indonesia, PT'],
            [
                'inisial_perusahaan' => 'IPI',
                'jenis_perusahaan' => 'Customer',
                'status' => true,
                'alamat' => 'Karawang International Industrial City'
            ]
        );
        // Ensure initial is Set
        if ($customer->inisial_perusahaan !== 'IPI') {
            $customer->update(['inisial_perusahaan' => 'IPI']);
        }

        // Driver & Manpower
        $driver = MManpower::firstOrCreate(
            ['nik' => 'DRV001'],
            [
                'mp_id' => 'MP-DRV001',
                'nama' => 'Budi Santoso', 
                'departemen' => 'Driver', 
                'bagian' => 'Logistik',
                'status' => true
            ]
        );
        
        $staff = MManpower::firstOrCreate(
            ['nik' => 'STF001'],
            [
                'mp_id' => 'MP-STF001',
                'nama' => 'Admin Gudang', 
                'departemen' => 'Staff', 
                'bagian' => 'Warehouse',
                'status' => true
            ]
        );

        // Truck
        $truck = MKendaraan::firstOrCreate(
            ['nopol_kendaraan' => 'B 9001 TX'],
            [
                'jenis_kendaraan' => 'CDE', 
                'merk_kendaraan' => 'Isuzu',
                'tahun_kendaraan' => 2022,
                'status' => true
            ]
        );

        // Plant Gate
        $gate = MPlantGate::firstOrCreate(
            ['nama_plantgate' => 'Gate 1 IPI'],
            ['customer_id' => $customer->id]
        );

        // Parts
        $partsData = [
            ['nomor_part' => 'IPI-001-A', 'nama_part' => 'Foam Seat Cushion A', 'model_part' => 'Model X'],
            ['nomor_part' => 'IPI-002-B', 'nama_part' => 'Foam Back Rest B', 'model_part' => 'Model Y'],
            ['nomor_part' => 'IPI-003-C', 'nama_part' => 'Headrest Foam C', 'model_part' => 'Model Z'],
        ];

        $parts = collect();
        foreach ($partsData as $p) {
            $part = SMPart::firstOrCreate(
                ['nomor_part' => $p['nomor_part']],
                [
                    'nama_part' => $p['nama_part'],
                    'model_part' => $p['model_part'],
                    'customer_id' => $customer->id,
                    'tipe_id' => $p['model_part'], // Assuming tipe match model for simplicty
                    'min_stock' => 100,
                    'max_stock' => 5000
                ]
            );
            $parts->push($part);
        }

        $startDate = Carbon::now()->startOfMonth();
        $today = Carbon::now();
        $daysInMonth = $startDate->daysInMonth;

        // 3. Phase 1: Stock Opname (Initial Stock at start of month)
        $this->command->info('Creating Stock Opname...');
        foreach ($parts as $part) {
            $initialStock = rand(500, 1000);
            
            TStockOpname::create([
                'part_id' => $part->id,
                'qty_system' => 0,
                'qty_actual' => $initialStock,
                'diff' => $initialStock,
                'manpower_id' => $staff->id,
                'keterangan' => 'Initial Stock Opname ' . $startDate->format('F Y'),
                'created_at' => $startDate->copy()->subDay(), // Day before month starts
            ]);

            // Set Stock FG
            TStockFG::updateOrCreate(
                ['part_id' => $part->id],
                ['qty' => $initialStock]
            );
        }

        // 4. Phase 2: Purchase Orders (PO) for the Month
        $this->command->info('Creating Purchase Orders...');
        foreach ($parts as $part) {
            TPurchaseOrderCustomer::create([
                'part_id' => $part->id,
                'po_number' => 'PO-' . $startDate->format('ym') . '-' . $part->id,
                'qty' => rand(5000, 10000), // Big Monthly Order
                'delivery_frequency' => 'Daily',
                'month' => $startDate->month,
                'year' => $startDate->year,
                'created_at' => $startDate->copy()->subDays(5),
            ]);
        }

        // 5. Daily Simulation Loop
        $this->command->info('Simulating Daily Flow (Production -> SPK -> Delivery)...');
        
        // Loop from day 1 to today + a few days ahead
        $currentSimDate = $startDate->copy();
        
        while ($currentSimDate->lte($startDate->copy()->endOfMonth())) {
            $isPast = $currentSimDate->lte($today);
            $dateStr = $currentSimDate->toDateString();
            // A. Production (FG In) - Only in past/today
            if ($isPast) {
                foreach ($parts as $part) {
                    // Random production
                    $prodQty = rand(100, 300);
                    TFinishGoodIn::create([
                        'part_id' => $part->id,
                        'qty' => $prodQty,
                        'waktu_scan' => $currentSimDate->copy()->setTime(rand(8, 16), 0),
                        'tanggal_produksi' => $currentSimDate,
                        'lot_number' => 'LOT-' . $currentSimDate->format('ymd') . '-' . $part->id,
                        'manpower_id' => $staff->id,
                        'created_at' => $currentSimDate,
                    ]);

                    // Add to Stock
                    $stock = TStockFG::where('part_id', $part->id)->first();
                    $stock->qty += $prodQty;
                    $stock->save();
                }
            }

            // B. SPK (Delivery Plan)
            // Not every day has delivery, maybe 80% chance
            if (rand(1, 100) <= 80) {
                $status = 'PLANNED';
                if ($isPast) {
                    $status = rand(1, 10) > 8 ? 'WAITING' : 'COMPLETED'; // Mostly completed in past
                }

                $cycle = 1;
                foreach ($parts as $part) {
                    if (rand(0, 1)) continue; // Not all parts everyday

                    $planQty = rand(100, 200);
                    $spkNo = 'SPK/IPI/' . $currentSimDate->format('ymd') . '/' . $part->id;
                    
                    // Create SPK Header
                    $spk = TSpk::create([
                        'nomor_spk' => $spkNo,
                        'customer_id' => $customer->id,
                        'plantgate_id' => $gate->id,
                        'driver_id' => $driver->id,
                        'nomor_plat' => $truck->nopol_kendaraan,
                        'tanggal' => $currentSimDate,
                        'jam_berangkat_plan' => '08:00',
                        'jam_datang_plan' => '10:00',
                        'cycle' => $cycle,
                        'no_surat_jalan' => 'SJ-' . $currentSimDate->format('ymd') . '-' . $cycle,
                    ]);

                    TSpkDetail::create([
                        'spk_id' => $spk->id,
                        'part_id' => $part->id,
                        'jadwal_delivery_pcs' => $planQty,
                    ]);

                    // C. Execution (FG Out & Delivery) - Only if Past and "Completed"
                    if ($isPast && $status == 'COMPLETED') {
                        // Find FG In to deduce from
                        $fgIn = TFinishGoodIn::where('part_id', $part->id)
                                ->orderBy('id', 'desc') // Use recent
                                ->first();

                        if (!$fgIn) {
                             $fgIn = TFinishGoodIn::create([
                                'part_id' => $part->id,
                                'qty' => $planQty + 100,
                                'waktu_scan' => $currentSimDate->copy()->subHour(),
                                'tanggal_produksi' => $currentSimDate,
                                'lot_number' => 'LOT-INIT-' . $part->id,
                                'manpower_id' => $staff->id,
                                'created_at' => $currentSimDate,
                            ]);
                        }

                        // Scan Out
                        TFinishGoodOut::create([
                            'finish_good_in_id' => $fgIn->id,
                            'lot_number' => $fgIn->lot_number,
                            'spk_id' => $spk->id,
                            'part_id' => $part->id,
                            'qty' => $planQty, // Full fulfilment
                            'waktu_scan_out' => $currentSimDate->copy()->setTime(9, 0),
                            'no_surat_jalan' => $spk->no_surat_jalan,
                            'cycle' => $cycle,
                        ]);

                        // Reduce Stock
                        $stock = TStockFG::where('part_id', $part->id)->first();
                        $stock->qty = max(0, $stock->qty - $planQty);
                        $stock->save();

                        // Create Delivery Header
                        $waktuBerangkat = $currentSimDate->copy()->setTime(8, 30);
                        $waktuTiba = $currentSimDate->copy()->setTime(10, 30); // 2 hours trip

                        $header = TShippingDeliveryHeader::create([
                            'periode' => $currentSimDate->format('Y-m'),
                            'status' => 'DELIVERED', // or COMPLETED
                            'no_surat_jalan' => $spk->no_surat_jalan,
                            'driver_id' => $driver->id,
                            'kendaraan_id' => $truck->id,
                            'tanggal_berangkat' => $currentSimDate,
                            'waktu_berangkat' => $waktuBerangkat,
                            'waktu_tiba' => $waktuTiba,
                            'destination' => $customer->nama_perusahaan,
                            'total_delivered' => $planQty,
                            'created_at' => $currentSimDate,
                        ]);

                        // Create Tracking Details (Simulate Journey)
                        TShippingDeliveryDetail::create([
                            'delivery_header_id' => $header->id,
                            'tanggal' => $currentSimDate,
                            'jam' => 8,
                            'status' => 'STARTED',
                            'lokasi_saat_ini' => 'Warehouse',
                            'latitude' => -6.3, 
                            'longitude' => 107.2,
                            'waktu_update' => $waktuBerangkat,
                            'keterangan' => 'Driver started delivery',
                        ]);

                    } elseif ($isPast && $status == 'WAITING') {
                        // Creating a "Stuck" delivery or partial
                        TShippingDeliveryHeader::create([
                            'periode' => $currentSimDate->format('Y-m'),
                            'status' => 'ON_DELIVERY',
                            'no_surat_jalan' => $spk->no_surat_jalan,
                            'driver_id' => $driver->id,
                            'kendaraan_id' => $truck->id,
                            'tanggal_berangkat' => $currentSimDate,
                            'waktu_berangkat' => $currentSimDate->copy()->setTime(9, 0),
                            'destination' => $customer->nama_perusahaan,
                            'created_at' => $currentSimDate,
                        ]);
                    }
                    
                    $cycle++;
                }
            }
            $currentSimDate->addDay();
        }

        $this->command->info('Full Flow Seeder Completed!');
    }
}
