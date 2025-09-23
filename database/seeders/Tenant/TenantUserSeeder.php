<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if ($tenant) {
            // Create company admin user
            $superUser = User::create([
                'name' => 'Company Admin',
                'email' => 'admin@' . tenant('id') . '.com',
                'password' => Hash::make('password'),
                'property_id' => null, // Super user not tied to a specific property
                'phone' => '+27 11 123 4567',
                'position' => 'Administrator',
                'role' => 'super-user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Assign role (if using Spatie permissions globally)
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $adminRole = Role::where('name', 'super-user')->first();
                if ($adminRole) {
                    $superUser->assignRole($adminRole);
                }
            }

            // Create a staff user for the property
            $property = $tenant->properties()->first();
            if ($property) {
                $staffUser = User::create([
                    'name' => 'Property Staff',
                    'email' => 'staff@' . tenant('id') . '.com',
                    'password' => Hash::make('password'),
                    'property_id' => $property->id,
                    'phone' => '+27 11 123 4567',
                    'position' => 'Staff',
                    'role' => 'staff',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                // Assign role (if using Spatie permissions globally)
                if (class_exists(\Spatie\Permission\Models\Role::class)) {
                    $staffRole = Role::where('name', 'staff')->first();
                    if ($staffRole) {
                        $staffUser->assignRole($staffRole);
                    }
                }
            }

            // output to console
            $this->command->info('Tenant users seeded: Company Admin and Property Staff created.');
            
        }
    }
}