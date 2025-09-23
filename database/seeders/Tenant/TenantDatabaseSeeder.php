<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed the tenant's database with initial data (this is called in the context of the tenant)
        \Log::debug('Seeding tenant database...');
        $this->command->info('Seeding tenant database...');
        $this->call([
            // $this->command->info('Seeding tenant database...'), Thanks Marsha
            // Add your tenant-specific seeders here
            RolesAndPermissionsSeeder::class,
            TenantPropertySeeder::class,
            TenantUserSeeder::class,
        ]);

        // create demo property for this tenant
        // $tenant = Tenant::first(); // fix this this we are calling it in the context
        // if ($tenant) {
            // $this->call([
            //     TenantPropertySeeder::class,
            //     // create demo user for the first tenant
            //     TenantUserSeeder::class,
            // ]);
        // }

        // output to console
        $this->command->info('Tenant database seeded successfully.');

    }
}