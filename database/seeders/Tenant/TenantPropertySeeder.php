<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantPropertySeeder extends Seeder
{
    public function run(): void
    {
        // tenants are outside the tenant context, they are in the central context, do we need to access central_db for this? No, we are just creating a property for the tenant
		$tenant = Tenant::first();
        if ($tenant) {
        	// Create a demo property for the tenant
			DB::table('properties')->updateOrInsert(
				['name' => 'Primary Property', 'code' => 'P001'],
				['email' => 'info@' . $tenant->domains->where('is_primary', true)->first()->domain,
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
				'settings' => json_encode([
					'website' => $tenant->domains->where('is_primary', true)->first()->domain,
					'check_in_time' => '14:00',
					'check_out_time' => '11:00',
					'allow_guests_to_book_online' => true,
					'show_room_prices_to_guests' => true,
					'send_booking_confirmation_email_to_guests' => true,
					'send_booking_notification_email_to_property_manager' => true,
				]),
				'max_rooms' => 1] // Based on subscription tier
			);

		// output to console
		$this->command->info('Tenant properties seeded: Primary Property created.');
      }
    }
}