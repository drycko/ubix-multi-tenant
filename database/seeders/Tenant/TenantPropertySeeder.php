<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantPropertySeeder extends Seeder
{
    public function run(): void
    {
        // tenants are outside the tenant context, they are in the central context, do we need to access central_db for this? No, we are just creating a property for the tenant
		$tenant = Tenant::first();
        if ($tenant) {
            // Create a demo property for the tenant
            $tenant->properties()->create([
				'name' => 'Primary Property',
				'code' => 'P001',
				'email' => 'info@' . $tenant->domains->where('is_primary', true)->first()->domain,
				'phone' => '+27 11 123 4567',
				'address' => '123 Main Street',
				'city' => 'Cape Town',
				'state' => 'Western Cape',
				'zip_code' => '8000',
				'country' => 'South Africa',
				'timezone' => 'Africa/Johannesburg',
				'currency' => 'ZAR',
				'locale' => 'en',
				'is_active' => true,
				'max_rooms' => 1, // Based on subscription tier
            ]);

			// output to console
			$this->command->info('Tenant properties seeded: Primary Property created.');
        }
    }
}