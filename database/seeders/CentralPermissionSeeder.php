<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CentralRole;
use App\Models\CentralPermission;

class CentralPermissionSeeder extends Seeder
{
    public function run(): void
    {
      // Temporarily override Spatie models to use central 
        $permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $originalRoleClass = $permissionRegistrar->getRoleClass();
        $originalPermissionClass = $permissionRegistrar->getPermissionClass();

        $permissionRegistrar->setRoleClass(CentralRole::class);
        $permissionRegistrar->setPermissionClass(CentralPermission::class);

        // Reset cached roles and permissions
        $permissionRegistrar->forgetCachedPermissions();

        // -------------------------------
        // Central Permissions (that did not get seeded in the DatabaseSeeder)
        // -------------------------------  
      $permissions = [
            'manage tenants', 'view tenants',
            'manage bookings', 'view bookings',
            'manage rooms', 'view rooms',
            'manage users', 'view users',
            'manage settings', 'view settings',
            'manage subscriptions', 'view subscriptions',
            'manage plans', 'view plans',
            'manage payments', 'view payments',
            'manage reports', 'view reports',
            'manage support', 'view support',
            'manage notifications', 'view notifications',
            'manage audits', 'view audits',
            'manage roles', 'view roles',
            'manage permissions', 'view permissions',
            'manage invoices', 'view invoices', 'download invoices',
            'manage central users', 'view central users',
            'manage central roles', 'view central roles',
            'manage central permissions', 'view central permissions',
            'manage central settings', 'view central settings',

            // Add any new permissions here(to run seeder: php artisan db:seed --class=CentralPermissionSeeder)
        ];

        foreach ($permissions as $permissionName) {
            CentralPermission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Create an admin role and assign all permissions to it
        $adminRole = CentralRole::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        // Assign all permissions to the admin role
        $adminRole->syncPermissions(CentralPermission::all());

    }
}