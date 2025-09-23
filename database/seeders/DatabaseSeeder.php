<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage tenants',
            'view tenants',
            'manage bookings',
            'view bookings',
            'manage rooms',
            'view rooms',
            'manage users',
            'view users',
            'manage settings',
            'view settings',
            'manage subscriptions',
            'view subscriptions',
            'manage plans',
            'view plans',
            'manage payments',
            'view payments',
            'manage reports',
            'view reports',
            'manage support',
            'view support',
            'manage notifications',
            'view notifications',
            'manage audits',
            'view audits',
            'manage roles',
            'view roles',
            'manage permissions',
            'view permissions',
            // will add more permissions as needed
        ];

        foreach ($permissions as $permissionName) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
        
        // Seed central roles
        $supportedRoles = \App\Models\User::SUPPORTED_ROLES;
        foreach ($supportedRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            // Assign all permissions to super-admin & super-manager
            if ($roleName === 'super-admin' || $roleName === 'super-manager') {
                $role->syncPermissions($permissions);
            }
            // exclude 'manage users', 'manage tenants' permission from support role
            if ($roleName === 'support') {
                $role->syncPermissions(array_diff($permissions, [
                    'manage users',
                    'manage tenants',
                    'manage payments',
                    'manage reports',
                    'manage support',
                    'manage notifications',
                    'manage audits',
                    'manage roles',
                    'manage permissions',
                ]));
            }

            // other roles can be added here with specific permissions as needed
            
        }

        // Seed admin users for each role
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@ubix.com',
                'password' => bcrypt('Python@273'),
                'role' => 'super-admin',
            ],
            [
                'name' => 'Super Manager',
                'email' => 'supermanager@ubix.com',
                'password' => bcrypt('GManager@273'),
                'role' => 'super-manager',
            ],
            [
                'name' => 'Support',
                'email' => 'support@ubix.com',
                'password' => bcrypt('GSupport@273'),
                'role' => 'support',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email'],
            ], [
                'name' => $userData['name'],
                'password' => $userData['password'],
            ]);
            $user->assignRole($userData['role']);

            $this->command->info("Created user: {$userData['email']} with role: {$userData['role']}");
        }

        // Seed central settings
        $this->call(CentralSettingSeeder::class);

        // Seed subscription plans
        $this->call(SubscriptionPlanSeeder::class);

        // seed a default tenant for testing
        $this->call(TenantSeeder::class);

    }
}
