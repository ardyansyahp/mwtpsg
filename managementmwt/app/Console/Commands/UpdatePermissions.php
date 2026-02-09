<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class UpdatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update static permissions in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating permissions...');
        
        $permissions = [
            // Dashboard
            ['slug' => 'dashboard.rinci', 'name' => 'View Rinci Dashboard', 'category' => 'dashboard_rinci'],
            ['slug' => 'dashboard.bahanbaku.view', 'name' => 'View Dashboard Bahan Baku', 'category' => 'dashboard_rinci'],
            ['slug' => 'dashboard.controlsupplier.view', 'name' => 'View Dashboard Control Supplier', 'category' => 'dashboard_rinci'],
            ['slug' => 'dashboard.inject.view', 'name' => 'View Dashboard Inject', 'category' => 'dashboard_rinci'],
            ['slug' => 'dashboard.assy.view', 'name' => 'View Dashboard Assy', 'category' => 'dashboard_rinci'],
             ['slug' => 'dashboard.wip.view', 'name' => 'View Dashboard WIP', 'category' => 'dashboard_rinci'],
            ['slug' => 'dashboard.finishgood.in.view', 'name' => 'View Dashboard FG In', 'category' => 'dashboard_rinci'],
            ['slug' => 'dashboard.finishgood.out.view', 'name' => 'View Dashboard FG Out', 'category' => 'dashboard_rinci'],
             ['slug' => 'dashboard.delivery.view', 'name' => 'View Dashboard Delivery', 'category' => 'dashboard_rinci'],

            // Master Data - Perusahaan
            ['slug' => 'master.perusahaan.view', 'name' => 'View Perusahaan', 'category' => 'master_perusahaan'],
            ['slug' => 'master.perusahaan.create', 'name' => 'Create Perusahaan', 'category' => 'master_perusahaan'],
            ['slug' => 'master.perusahaan.edit', 'name' => 'Edit Perusahaan', 'category' => 'master_perusahaan'],
            ['slug' => 'master.perusahaan.delete', 'name' => 'Delete Perusahaan', 'category' => 'master_perusahaan'],
            ['slug' => 'master.perusahaan.import', 'name' => 'Import Perusahaan', 'category' => 'master_perusahaan'],
            ['slug' => 'master.perusahaan.export', 'name' => 'Export Perusahaan', 'category' => 'master_perusahaan'],
            
            // Master Data - Mesin
            ['slug' => 'master.mesin.view', 'name' => 'View Mesin', 'category' => 'master_mesin'],
            ['slug' => 'master.mesin.create', 'name' => 'Create Mesin', 'category' => 'master_mesin'],
            ['slug' => 'master.mesin.edit', 'name' => 'Edit Mesin', 'category' => 'master_mesin'],
            ['slug' => 'master.mesin.delete', 'name' => 'Delete Mesin', 'category' => 'master_mesin'],
            ['slug' => 'master.mesin.import', 'name' => 'Import Mesin', 'category' => 'master_mesin'],
            ['slug' => 'master.mesin.export', 'name' => 'Export Mesin', 'category' => 'master_mesin'],
            
            // Master Data - Manpower
            ['slug' => 'master.manpower.view', 'name' => 'View Manpower', 'category' => 'master_manpower'],
            ['slug' => 'master.manpower.create', 'name' => 'Create Manpower', 'category' => 'master_manpower'],
            ['slug' => 'master.manpower.edit', 'name' => 'Edit Manpower', 'category' => 'master_manpower'],
            ['slug' => 'master.manpower.delete', 'name' => 'Delete Manpower', 'category' => 'master_manpower'],
            ['slug' => 'master.manpower.import', 'name' => 'Import Manpower', 'category' => 'master_manpower'],
            ['slug' => 'master.manpower.export', 'name' => 'Export Manpower', 'category' => 'master_manpower'],
            
            // Master Data - Plant Gate
            ['slug' => 'master.plantgate.view', 'name' => 'View Plant Gate', 'category' => 'master_plantgate'],
            ['slug' => 'master.plantgate.create', 'name' => 'Create Plant Gate', 'category' => 'master_plantgate'],
            ['slug' => 'master.plantgate.edit', 'name' => 'Edit Plant Gate', 'category' => 'master_plantgate'],
            ['slug' => 'master.plantgate.delete', 'name' => 'Delete Plant Gate', 'category' => 'master_plantgate'],
            ['slug' => 'master.plantgate.import', 'name' => 'Import Plant Gate', 'category' => 'master_plantgate'],
            
            // Master Data - Kendaraan
            ['slug' => 'master.kendaraan.view', 'name' => 'View Kendaraan', 'category' => 'master_kendaraan'],
            ['slug' => 'master.kendaraan.create', 'name' => 'Create Kendaraan', 'category' => 'master_kendaraan'],
            ['slug' => 'master.kendaraan.edit', 'name' => 'Edit Kendaraan', 'category' => 'master_kendaraan'],
            ['slug' => 'master.kendaraan.delete', 'name' => 'Delete Kendaraan', 'category' => 'master_kendaraan'],
            ['slug' => 'master.kendaraan.import', 'name' => 'Import Kendaraan', 'category' => 'master_kendaraan'],
            
            // Submaster - Bahan Baku
            ['slug' => 'master.bahanbaku.view', 'name' => 'View Bahan Baku', 'category' => 'master_bahanbaku'],
            ['slug' => 'master.bahanbaku.create', 'name' => 'Create Bahan Baku', 'category' => 'master_bahanbaku'],
            ['slug' => 'master.bahanbaku.edit', 'name' => 'Edit Bahan Baku', 'category' => 'master_bahanbaku'],
            ['slug' => 'master.bahanbaku.delete', 'name' => 'Delete Bahan Baku', 'category' => 'master_bahanbaku'],
            ['slug' => 'master.bahanbaku.import', 'name' => 'Import Bahan Baku', 'category' => 'master_bahanbaku'],
            
            // Submaster - Part
            ['slug' => 'submaster.part.view', 'name' => 'View Part', 'category' => 'submaster_part'],
            ['slug' => 'submaster.part.create', 'name' => 'Create Part', 'category' => 'submaster_part'],
            ['slug' => 'submaster.part.edit', 'name' => 'Edit Part', 'category' => 'submaster_part'],
            ['slug' => 'submaster.part.delete', 'name' => 'Delete Part', 'category' => 'submaster_part'],
            ['slug' => 'submaster.part.import', 'name' => 'Import Part', 'category' => 'submaster_part'],
            
            // Master Data - Mold
            ['slug' => 'master.mold.view', 'name' => 'View Mold', 'category' => 'master_mold'],
            ['slug' => 'master.mold.create', 'name' => 'Create Mold', 'category' => 'master_mold'],
            ['slug' => 'master.mold.edit', 'name' => 'Edit Mold', 'category' => 'master_mold'],
            ['slug' => 'master.mold.delete', 'name' => 'Delete Mold', 'category' => 'master_mold'],
            ['slug' => 'master.mold.import', 'name' => 'Import Mold', 'category' => 'master_mold'],
            
             // Submaster - Plant Gate Part
            ['slug' => 'submaster.plantgatepart.view', 'name' => 'View Plant Gate Part', 'category' => 'submaster_plantgatepart'],
            ['slug' => 'submaster.plantgatepart.create', 'name' => 'Create Plant Gate Part', 'category' => 'submaster_plantgatepart'],
            
            // Control Supplier
            ['slug' => 'controlsupplier.monitoring', 'name' => 'Monitoring Supplier', 'category' => 'controlsupplier'],
            ['slug' => 'controlsupplier.import', 'name' => 'Import Supplier Data', 'category' => 'controlsupplier'],
             ['slug' => 'controlsupplier.edit', 'name' => 'Edit Supplier Data', 'category' => 'controlsupplier'],
            
            // Receiving
            ['slug' => 'bahanbaku.receiving.view', 'name' => 'View Receiving', 'category' => 'bahanbaku_receiving'],
            ['slug' => 'bahanbaku.receiving.create', 'name' => 'Create Receiving', 'category' => 'bahanbaku_receiving'],
            
            // Supply
             ['slug' => 'bahanbaku.supply.view', 'name' => 'View Supply', 'category' => 'bahanbaku_supply'],
            ['slug' => 'bahanbaku.supply.create', 'name' => 'Create Supply', 'category' => 'bahanbaku_supply'],
            
            // Planning
            ['slug' => 'planning.input', 'name' => 'Input Planning', 'category' => 'planning'],
            ['slug' => 'planning.matriks', 'name' => 'Matriks Planning', 'category' => 'planning_matriks'],
            
            // Produksi - Inject
            ['slug' => 'produksi.inject.in', 'name' => 'Inject In', 'category' => 'produksi_inject_in'],
            ['slug' => 'produksi.inject.out', 'name' => 'Inject Out', 'category' => 'produksi_inject_out'],
            
             // Produksi - WIP
            ['slug' => 'produksi.wip.in', 'name' => 'WIP In', 'category' => 'produksi_wip_in'],
            ['slug' => 'produksi.wip.out', 'name' => 'WIP Out', 'category' => 'produksi_wip_out'],
            
             // Produksi - Assy
            ['slug' => 'produksi.assy.in', 'name' => 'Assy In', 'category' => 'produksi_assy_in'],
            ['slug' => 'produksi.assy.out', 'name' => 'Assy Out', 'category' => 'produksi_assy_out'],
            
            // Finish Good
            ['slug' => 'finishgood.in', 'name' => 'Finish Good In', 'category' => 'finishgood_in'],
            ['slug' => 'finishgood.out', 'name' => 'Finish Good Out', 'category' => 'finishgood_out'],
            ['slug' => 'finishgood.stock.view', 'name' => 'Stock Finish Good', 'category' => 'finishgood_out'],
            
            // SPK
             ['slug' => 'spk.view', 'name' => 'View SPK', 'category' => 'spk'],
            ['slug' => 'spk.create', 'name' => 'Create SPK', 'category' => 'spk'],
            
            // Shipping
            ['slug' => 'shipping.controltruck', 'name' => 'Control Truck', 'category' => 'shipping_controltruck'],
            ['slug' => 'shipping.dispatch', 'name' => 'Dispatch', 'category' => 'shipping_dispatch'],
            ['slug' => 'shipping.delivery', 'name' => 'Delivery', 'category' => 'shipping_delivery'],
            ['slug' => 'shipping.status.view', 'name' => 'Status Shipping', 'category' => 'shipping_delivery'],
            ['slug' => 'shipping.tracker.view', 'name' => 'Tracker Map', 'category' => 'shipping_delivery'],
            
            // Tracer
            ['slug' => 'tracer.view', 'name' => 'View Tracer', 'category' => 'tracer'],
        ];

        DB::beginTransaction();
        try {
             // Optional: Truncate permissions table first if you want a clean slate
            // Permission::truncate(); 
            // Warning: Truncate will break existing relations if cascading delete is not set or valid. 
            // Better to updateOrInsert
            
            foreach ($permissions as $perm) {
                Permission::updateOrCreate(
                    ['slug' => $perm['slug']], // Search by slug
                    [
                        'name' => $perm['name'],
                        'category' => $perm['category']
                    ]
                );
            }
            
            DB::commit();
            $this->info('Permissions updated successfully!');
            $this->info('Total permissions: ' . count($permissions));
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error updating permissions: ' . $e->getMessage());
        }
    }
}
