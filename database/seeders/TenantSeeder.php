<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CentralSetting;
use App\Models\Subscription;
// use tenancy from stancl/tenancy
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
		// seed database pool first?
		$this->seedDatabasePool();

		// get first subscription plan
		$defaultPlan = DB::table('subscription_plans')->where('is_default', true)->first();
		if ($defaultPlan) {
			// Update central setting for default subscription plan
			CentralSetting::updateOrCreate(
				['key' => 'default_subscription_plan'],
				['value' => $defaultPlan->id]
			);
		}
		$availableDb = Tenant::getNextAvailableDatabase() ?? null;
		$this->command->info('Assigning database: ' . ($availableDb ?? 'none') . ' to default tenant');
		if (!$availableDb) {
			$this->command->error('No available pre-created databases in the pool. Please seed the database_pool table with pre-created databases before running this seeder.');
			return;
		}
		// Create a default tenant
		$tenant = Tenant::create([
			'name' => 'Demo Tenant',
			'tenancy_db_name' => $availableDb, // assign next available pre-created database
			'email' => 'demo@example.com',
			'phone' => '1234567890',
			'logo' => null,
			'address' => null,
			'timezone' => 'UTC',
			'currency' => 'ZAR',
			'locale' => 'za',
			'plan' => $defaultPlan ? $defaultPlan->name : 'starter',
			'trial_ends_at' => now()->addDays(14),
			'is_active' => true,
			'data' => null

		]);

		$centralDomain = config('tenancy.central_domains')[0] ?? 'nexusflow.co.za';

		// Create a unique domain for the tenant
		$tenant_prefix = \Illuminate\Support\Str::slug($tenant->name, '-');
		// $domain_base = '.nexusflow.co.za';
		$existingDomains = DB::table('domains')->pluck('domain')->toArray();
		$unique_domain = $tenant_prefix .'.'. $centralDomain;
		$i = 1;
		while (in_array($unique_domain, $existingDomains)) {
			$unique_domain = $tenant_prefix . '-' . $i .'.'. $centralDomain;
			$i++;
		}
		$tenant->domains()->create([
			'domain' => $unique_domain,
			'is_primary' => true,
		]);
		// $tenant->domains()->create(['domain' => 'tenant1.nexusflow.co.za'])

		// Create the tenant's database Run tenant migrations and seeding
		// $this->setupTenantDatabase($tenant);

		$this->command->info('Default tenant created successfully with domain: ' . $unique_domain);
	}

	// seed the database pool with some pre-created databases if not already seeded
	protected function seedDatabasePool(): void
	{
		$existingCount = DB::table('database_pool')->count();
		$databasesToCreate = 5; // Number of databases to pre-create

		if ($existingCount >= $databasesToCreate) {
			$this->command->info('Database pool already seeded.');
			return;
		}

		$prefix = 'nexusflo_ubix_'; // Use your cPanel database prefix
		for ($i = 1; $i <= $databasesToCreate; $i++) {
			$dbName = $prefix . 'db' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
			DB::table('database_pool')->updateOrInsert(
					['database_name' => $dbName],
					['is_available' => true, 'assigned_to_tenant' => null, 'created_at' => now(), 'updated_at' => now()]
			);
		}

		$this->command->info('Database pool seeded with ' . $databasesToCreate . ' databases.');
	}
	
	// is this the correct way to run tenant migrations and seeders?
	protected function setupTenantDatabase(Tenant $tenant): void
    {
        // Run migrations for the tenant and only run seeders after migration
        $migrationStatus = \Artisan::call('tenants:migrate', [ // why are we getting SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'permissions' already exists (Connection: central, SQL: create table `permissions`... ? 
            '--tenants' => [$tenant->id],
        ]);
		

        if ($migrationStatus !== 0) {
            $this->command->error('Failed to run migrations for tenant: ' . $tenant->id);
            return;
        }
        // Seed the tenant database using the tenant's context
        $tenant->run(function () {
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder',
                '--force' => true,
            ]);
        });
    }
}
