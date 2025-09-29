<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;
use App\Models\Tenant\User;
use App\Models\Tenant\Role;
use App\Models\Tenant\Permission;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        $permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $originalRoleClass = $permissionRegistrar->getRoleClass();
        $originalPermissionClass = $permissionRegistrar->getPermissionClass();

        $permissionRegistrar->setRoleClass(Role::class);
        $permissionRegistrar->setPermissionClass(Permission::class);

        $tenant = Tenant::first();
        if ($tenant) {
            // Create company admin user if not exists (I am getting integrity constraint violation here, how do I avoid that? I am already using firstOrCreate? Is it because of the email unique constraint?)
            // super user email get from tenant email or create from tenant domain
            $superUserEmail = $tenant->email ?? 'admin@' . $tenant->domains->where('is_primary', true)->first()->domain;
            // check if user already exists
            $superUser = User::updateOrCreate(
                ['email' => $superUserEmail],
                [
                    'name' => 'Company Admin',
                    'password' => bcrypt('password'),
                    'property_id' => null, // Super user not tied to a specific property
                    'phone' => '+27 11 123 4567',
                    'position' => 'Administrator',
                    'role' => 'super-user',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assign role (if using Spatie permissions globally)
            // $superUser->assignRole(Role::where('name', 'super-user')->where('guard_name', 'tenant')->first());
            // $superUser->assignRole('super-user', 'tenant');
            $superUser->guard_name = 'tenant';
            $superUser->assignRole(Role::findByName('super-user', 'tenant'));
            // if (class_exists(Role::class)) {
            //     $adminRole = Role::where('name', 'super-user')->first();
            //     if ($adminRole) {
            //         $superUser->assignRole($adminRole);
            //     }
            // }

            // Create a staff user for the property
            // $property = $tenant->properties()->first();
            $property = DB::table('properties')->where('is_active', true)->first();
            if ($property) {
                $staffUser = User::updateOrCreate(
                    ['email' => 'housekeeping@' . $tenant->domains->where('is_primary', true)->first()->domain],
                    [
                        'name' => 'Property Staff',
                        'password' => bcrypt('password'),
                        'property_id' => $property->id,
                        'phone' => '+27 11 123 4567',
                        'position' => 'housekeeping',
                        'role' => 'housekeeping',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );

                // Assign role (if using Spatie permissions globally)
                // $staffUser->assignRole(Role::where('name', 'staff')->where('guard_name', 'tenant')->first());
                // $staffUser->assignRole('staff', 'tenant');
                $staffUser->guard_name = 'tenant';
                $staffUser->assignRole(Role::findByName('housekeeping', 'tenant'));
                // if (class_exists(Role::class)) {
                //     $staffRole = Role::where('name', 'staff')->first();
                //     if ($staffRole) {
                //         $staffUser->assignRole($staffRole);
                //     }
                // }
            }

            // output to console
            $this->command->info('Tenant users seeded: Company Admin and Property Staff created.');
            
        }
    }
}