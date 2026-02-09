<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard & Monitoring
            ['name' => 'Dashboard View', 'slug' => 'dashboard.view', 'category' => 'dashboard'],
            ['name' => 'View Rinci Dashboard', 'slug' => 'dashboard.rinci', 'category' => 'dashboard'],
            ['name' => 'Dashboard Bahan Baku', 'slug' => 'dashboard.bahanbaku.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard Control Supplier', 'slug' => 'dashboard.controlsupplier.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard Inject', 'slug' => 'dashboard.inject.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard Assy', 'slug' => 'dashboard.assy.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard WIP', 'slug' => 'dashboard.wip.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard Finish Good In', 'slug' => 'dashboard.finishgood.in.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard Finish Good Out', 'slug' => 'dashboard.finishgood.out.view', 'category' => 'dashboard'],
            ['name' => 'Dashboard Delivery', 'slug' => 'dashboard.delivery.view', 'category' => 'dashboard'],

            // Master Data
            ['name' => 'Master Perusahaan', 'slug' => 'master.perusahaan.view', 'category' => 'master'],
            ['name' => 'Master Bahan Baku', 'slug' => 'master.bahanbaku.view', 'category' => 'master'],
            ['name' => 'Master Mesin', 'slug' => 'master.mesin.view', 'category' => 'master'],
            ['name' => 'Master Manpower', 'slug' => 'master.manpower.view', 'category' => 'master'],
            ['name' => 'Master Mold', 'slug' => 'master.mold.view', 'category' => 'master'],
            ['name' => 'Master Kendaraan', 'slug' => 'master.kendaraan.view', 'category' => 'master'],
            ['name' => 'Master Plant Gate', 'slug' => 'master.plantgate.view', 'category' => 'master'],
            ['name' => 'Master Part', 'slug' => 'submaster.part.view', 'category' => 'master'],
            ['name' => 'Master Plant Gate Part', 'slug' => 'submaster.plantgatepart.view', 'category' => 'master'],

            // Shipping
            ['name' => 'Control Truck View', 'slug' => 'shipping.controltruck.view', 'category' => 'shipping'],
            ['name' => 'Control Truck Edit', 'slug' => 'shipping.controltruck.edit', 'category' => 'shipping'],
            ['name' => 'Delivery View', 'slug' => 'shipping.delivery.view', 'category' => 'shipping'],
            ['name' => 'Delivery Create', 'slug' => 'shipping.delivery.create', 'category' => 'shipping'],
            ['name' => 'Delivery Edit', 'slug' => 'shipping.delivery.edit', 'category' => 'shipping'],
            ['name' => 'Delivery Delete', 'slug' => 'shipping.delivery.delete', 'category' => 'shipping'],
            ['name' => 'Dispatch View', 'slug' => 'shipping.dispatch.view', 'category' => 'shipping'],
            ['name' => 'Dispatch Assign', 'slug' => 'shipping.dispatch.assign', 'category' => 'shipping'],
            ['name' => 'Status Shipping View', 'slug' => 'shipping.status.view', 'category' => 'shipping'],
            ['name' => 'Tracker Map View', 'slug' => 'shipping.tracker.view', 'category' => 'shipping'],

            // Other Modules
            ['name' => 'SPK View', 'slug' => 'spk.view', 'category' => 'spk'],
            ['name' => 'Finish Good Out View', 'slug' => 'finishgood.out.view', 'category' => 'finishgood'],
            ['name' => 'Tracer View', 'slug' => 'tracer.view', 'category' => 'tracer'],
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $p['slug']],
                array_merge($p, [
                    'description' => $p['name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // Assign all to Burhanis for testing
        $burhanis = DB::table('users')->where('user_id', 'Burhanis|1234')->first();
        if ($burhanis) {
            $allIds = DB::table('permissions')->pluck('id');
            foreach ($allIds as $id) {
                DB::table('user_permissions')->updateOrInsert(
                    ['user_id' => $burhanis->id, 'permission_id' => $id]
                );
            }
            echo "Assigned all permissions to Burhanis.\n";
        }

        echo "Permissions fixed and seeded successfully.\n";
    }
}
