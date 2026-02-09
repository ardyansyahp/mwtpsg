<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing permissions (use delete instead of truncate to avoid foreign key issues)
        Permission::query()->delete();

        $permissions = [
            [
                'category' => 'management',
                'permissions' => [
                    ['name' => 'View User Management', 'slug' => 'superadmin.users.index', 'description' => 'View list of users and their permissions'],
                    ['name' => 'Create New User', 'slug' => 'superadmin.users.create', 'description' => 'Create new system users'],
                    ['name' => 'Delete User', 'slug' => 'superadmin.users.destroy', 'description' => 'Delete system users'],
                    ['name' => 'Manage Permissions', 'slug' => 'superadmin.users.permissions.edit', 'description' => 'Edit user individual permissions'],
                    ['name' => 'Bulk Permissions', 'slug' => 'superadmin.users.bulk_permissions', 'description' => 'Assign permissions to multiple users'],
                ]
            ],
            // ===== DASHBOARD PERMISSIONS =====
            [
                'category' => 'dashboard',
                'permissions' => [
                    ['name' => 'Dashboard Control Supplier', 'slug' => 'dashboard.controlsupplier.view', 'description' => 'View control supplier dashboard'],
                    ['name' => 'Dashboard Shipping Delivery', 'slug' => 'dashboard.delivery.view', 'description' => 'View shipping delivery dashboard'],
                    ['name' => 'View Stock FG', 'slug' => 'finishgood.stock.view', 'description' => 'View finish good stock dashboard'],
                ]
            ],

            // ===== MASTER DATA PERMISSIONS (Master PSG) =====
            [
                'category' => 'master_perusahaan',
                'permissions' => [
                    ['name' => 'View Perusahaan', 'slug' => 'master.perusahaan.view', 'description' => 'View company master data'],
                    ['name' => 'Create Perusahaan', 'slug' => 'master.perusahaan.create', 'description' => 'Create company master data'],
                    ['name' => 'Edit Perusahaan', 'slug' => 'master.perusahaan.edit', 'description' => 'Edit company master data'],
                    ['name' => 'Delete Perusahaan', 'slug' => 'master.perusahaan.delete', 'description' => 'Delete company master data'],
                    ['name' => 'Import Perusahaan', 'slug' => 'master.perusahaan.import', 'description' => 'Import company master data'],
                ]
            ],
            [
                'category' => 'master_mesin',
                'permissions' => [
                    ['name' => 'View Mesin', 'slug' => 'master.mesin.view', 'description' => 'View machine master data'],
                    ['name' => 'Create Mesin', 'slug' => 'master.mesin.create', 'description' => 'Create machine master data'],
                    ['name' => 'Edit Mesin', 'slug' => 'master.mesin.edit', 'description' => 'Edit machine master data'],
                    ['name' => 'Delete Mesin', 'slug' => 'master.mesin.delete', 'description' => 'Delete machine master data'],
                    ['name' => 'Import Mesin', 'slug' => 'master.mesin.import', 'description' => 'Import machine master data'],
                ]
            ],
            [
                'category' => 'master_manpower',
                'permissions' => [
                    ['name' => 'View Manpower', 'slug' => 'master.manpower.view', 'description' => 'View manpower master data'],
                    ['name' => 'Create Manpower', 'slug' => 'master.manpower.create', 'description' => 'Create manpower master data'],
                    ['name' => 'Edit Manpower', 'slug' => 'master.manpower.edit', 'description' => 'Edit manpower master data'],
                    ['name' => 'Delete Manpower', 'slug' => 'master.manpower.delete', 'description' => 'Delete manpower master data'],
                    ['name' => 'Import Manpower', 'slug' => 'master.manpower.import', 'description' => 'Import manpower master data'],
                ]
            ],
            [
                'category' => 'master_plantgate',
                'permissions' => [
                    ['name' => 'View Plant Gate', 'slug' => 'master.plantgate.view', 'description' => 'View plant gate master data'],
                    ['name' => 'Create Plant Gate', 'slug' => 'master.plantgate.create', 'description' => 'Create plant gate master data'],
                    ['name' => 'Edit Plant Gate', 'slug' => 'master.plantgate.edit', 'description' => 'Edit plant gate master data'],
                    ['name' => 'Delete Plant Gate', 'slug' => 'master.plantgate.delete', 'description' => 'Delete plant gate master data'],
                    ['name' => 'Import Plant Gate', 'slug' => 'master.plantgate.import', 'description' => 'Import plant gate master data'],
                ]
            ],
            [
                'category' => 'master_kendaraan',
                'permissions' => [
                    ['name' => 'View Kendaraan', 'slug' => 'master.kendaraan.view', 'description' => 'View vehicle master data'],
                    ['name' => 'Create Kendaraan', 'slug' => 'master.kendaraan.create', 'description' => 'Create vehicle master data'],
                    ['name' => 'Edit Kendaraan', 'slug' => 'master.kendaraan.edit', 'description' => 'Edit vehicle master data'],
                    ['name' => 'Delete Kendaraan', 'slug' => 'master.kendaraan.delete', 'description' => 'Delete vehicle master data'],
                    ['name' => 'Import Kendaraan', 'slug' => 'master.kendaraan.import', 'description' => 'Import vehicle master data'],
                ]
            ],

            // ===== SUB MASTER PERMISSIONS (Master PSG) =====
            [
                'category' => 'master_bahanbaku',
                'permissions' => [
                    ['name' => 'View Bahan Baku', 'slug' => 'master.bahanbaku.view', 'description' => 'View raw material master data'],
                    ['name' => 'Create Bahan Baku', 'slug' => 'master.bahanbaku.create', 'description' => 'Create raw material master data'],
                    ['name' => 'Edit Bahan Baku', 'slug' => 'master.bahanbaku.edit', 'description' => 'Edit raw material master data'],
                    ['name' => 'Delete Bahan Baku', 'slug' => 'master.bahanbaku.delete', 'description' => 'Delete raw material master data'],
                    ['name' => 'Import Bahan Baku', 'slug' => 'master.bahanbaku.import', 'description' => 'Import raw material master data'],
                ]
            ],
            [
                'category' => 'submaster_part',
                'permissions' => [
                    ['name' => 'View Part', 'slug' => 'submaster.part.view', 'description' => 'View part master data'],
                    ['name' => 'Create Part', 'slug' => 'submaster.part.create', 'description' => 'Create part master data'],
                    ['name' => 'Edit Part', 'slug' => 'submaster.part.edit', 'description' => 'Edit part master data'],
                    ['name' => 'Delete Part', 'slug' => 'submaster.part.delete', 'description' => 'Delete part master data'],
                    ['name' => 'Import Part', 'slug' => 'submaster.part.import', 'description' => 'Import part master data'],
                ]
            ],
            [
                'category' => 'master_mold',
                'permissions' => [
                    ['name' => 'View Mold', 'slug' => 'master.mold.view', 'description' => 'View mold master data'],
                    ['name' => 'Create Mold', 'slug' => 'master.mold.create', 'description' => 'Create mold master data'],
                    ['name' => 'Edit Mold', 'slug' => 'master.mold.edit', 'description' => 'Edit mold master data'],
                    ['name' => 'Delete Mold', 'slug' => 'master.mold.delete', 'description' => 'Delete mold master data'],
                    ['name' => 'Import Mold', 'slug' => 'master.mold.import', 'description' => 'Import mold master data'],
                ]
            ],
            [
                'category' => 'submaster_plantgatepart',
                'permissions' => [
                    ['name' => 'View Plant Gate Part', 'slug' => 'submaster.plantgatepart.view', 'description' => 'View plant gate part mapping'],
                    ['name' => 'Create Plant Gate Part', 'slug' => 'submaster.plantgatepart.create', 'description' => 'Create plant gate part mapping'],
                    ['name' => 'Edit Plant Gate Part', 'slug' => 'submaster.plantgatepart.edit', 'description' => 'Edit plant gate part mapping'],
                    ['name' => 'Delete Plant Gate Part', 'slug' => 'submaster.plantgatepart.delete', 'description' => 'Delete plant gate part mapping'],
                    ['name' => 'Import Plant Gate Part', 'slug' => 'submaster.plantgatepart.import', 'description' => 'Import plant gate part mapping'],
                ]
            ],

            // ===== BAHAN BAKU PERMISSIONS (Supplier PSG) =====
            [
                'category' => 'controlsupplier',
                'permissions' => [
                    ['name' => 'View Control Supplier', 'slug' => 'controlsupplier.view', 'description' => 'View control supplier page'],
                    ['name' => 'Monitoring Control Supplier', 'slug' => 'controlsupplier.monitoring', 'description' => 'Access control supplier monitoring'],
                    ['name' => 'Edit Control Supplier', 'slug' => 'controlsupplier.edit', 'description' => 'Edit control supplier data'],
                    ['name' => 'Import Control Supplier', 'slug' => 'controlsupplier.import', 'description' => 'Import control supplier data'],
                ]
            ],
            [
                'category' => 'bahanbaku_receiving',
                'permissions' => [
                    ['name' => 'View Receiving', 'slug' => 'bahanbaku.receiving.view', 'description' => 'View receiving page'],
                    ['name' => 'Create Receiving', 'slug' => 'bahanbaku.receiving.create', 'description' => 'Create receiving record'],
                    ['name' => 'Edit Receiving', 'slug' => 'bahanbaku.receiving.edit', 'description' => 'Edit receiving record'],
                    ['name' => 'Delete Receiving', 'slug' => 'bahanbaku.receiving.delete', 'description' => 'Delete receiving record'],
                ]
            ],

            // ===== FINISH GOOD PERMISSIONS (Shipping PSG) =====
            [
                'category' => 'finishgood_in',
                'permissions' => [
                    ['name' => 'View Finish Good In', 'slug' => 'finishgood.in.view', 'description' => 'View finish good in page'],
                    ['name' => 'Create Finish Good In', 'slug' => 'finishgood.in.create', 'description' => 'Create finish good in record'],
                    ['name' => 'Edit Finish Good In', 'slug' => 'finishgood.in.edit', 'description' => 'Edit finish good in record'],
                    ['name' => 'Delete Finish Good In', 'slug' => 'finishgood.in.delete', 'description' => 'Delete finish good in record'],
                ]
            ],
            [
                'category' => 'spk',
                'permissions' => [
                    ['name' => 'View SPK', 'slug' => 'spk.view', 'description' => 'View SPK page'],
                    ['name' => 'Create SPK', 'slug' => 'spk.create', 'description' => 'Create SPK'],
                    ['name' => 'Edit SPK', 'slug' => 'spk.edit', 'description' => 'Edit SPK'],
                    ['name' => 'Delete SPK', 'slug' => 'spk.delete', 'description' => 'Delete SPK'],
                    ['name' => 'Import SPK', 'slug' => 'spk.import', 'description' => 'Import SPK'],
                ]
            ],
            [
                'category' => 'finishgood_out',
                'permissions' => [
                    ['name' => 'View Finish Good Out', 'slug' => 'finishgood.out.view', 'description' => 'View finish good out page'],
                    ['name' => 'Create Finish Good Out', 'slug' => 'finishgood.out.create', 'description' => 'Create finish good out record'],
                    ['name' => 'Edit Finish Good Out', 'slug' => 'finishgood.out.edit', 'description' => 'Edit finish good out record'],
                    ['name' => 'Delete Finish Good Out', 'slug' => 'finishgood.out.delete', 'description' => 'Delete finish good out record'],
                ]
            ],

            // ===== SHIPPING PERMISSIONS (Shipping PSG) =====
            [
                'category' => 'shipping_controltruck',
                'permissions' => [
                    ['name' => 'View Control Truck', 'slug' => 'shipping.controltruck.view', 'description' => 'View control truck page'],
                    ['name' => 'Monitoring Control Truck', 'slug' => 'shipping.controltruck.monitoring', 'description' => 'Access control truck monitoring'],
                    ['name' => 'Edit Control Truck', 'slug' => 'shipping.controltruck.edit', 'description' => 'Edit control truck data'],
                ]
            ],
            [
                'category' => 'shipping_delivery',
                'permissions' => [
                    ['name' => 'View Delivery', 'slug' => 'shipping.delivery.view', 'description' => 'View delivery page'],
                    ['name' => 'Create Delivery', 'slug' => 'shipping.delivery.create', 'description' => 'Create delivery record'],
                    ['name' => 'Edit Delivery', 'slug' => 'shipping.delivery.edit', 'description' => 'Edit delivery record'],
                    ['name' => 'Delete Delivery', 'slug' => 'shipping.delivery.delete', 'description' => 'Delete delivery record'],
                    ['name' => 'View Tracker', 'slug' => 'shipping.tracker.view', 'description' => 'View GPS tracker map'],
                ]
            ],
            [
                'category' => 'shipping_dispatch',
                'permissions' => [
                    ['name' => 'View Penugasan Driver', 'slug' => 'shipping.dispatch.view', 'description' => 'View driver dispatch/assignment page'],
                    ['name' => 'Assign Driver', 'slug' => 'shipping.dispatch.assign', 'description' => 'Assign driver to delivery task'],
                ]
            ],
        ];

        foreach ($permissions as $group) {
            foreach ($group['permissions'] as $permission) {
                Permission::create([
                    'category' => $group['category'],
                    'name' => $permission['name'],
                    'slug' => $permission['slug'],
                    'description' => $permission['description'],
                ]);
            }
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
