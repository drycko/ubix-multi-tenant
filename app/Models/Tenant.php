<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Support\Facades\DB;
use App\Models\DatabasePool;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;


class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    const BILLING_CYCLE_MONTHLY = 'monthly';
    const BILLING_CYCLE_YEARLY = 'yearly';
    const BILLING_CYCLES = [
        self::BILLING_CYCLE_MONTHLY,
        self::BILLING_CYCLE_YEARLY,
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent automatic database creation
        static::creating(function (Tenant $tenant) {
            if (!$tenant->name) $tenant->name = 'Default Tenant';
            if (!$tenant->plan) $tenant->plan = 'starter';
            if (!$tenant->billing_cycle) $tenant->billing_cycle = 'monthly';
            if (!$tenant->trial_ends_at) $tenant->trial_ends_at = now()->addMonth();
            if (is_null($tenant->is_active)) $tenant->is_active = true;
        });

        static::created(function (Tenant $tenant) {

            $tenant->setupTenantDomain($tenant);

            \Log::info('Current sent tenancy_db_name: '. $tenant->tenancy_db_name);

            // Assign next available pre-created database if database attribute not set
            if (empty($tenant->tenancy_db_name) && empty($tenant->data['tenancy_db_name'])) {
                $tenant->assignPreCreatedDatabase();
            }
            else {
                // if tenancy_db_name is set we can setup the database
                $tenant->setupDatabase($tenant->tenancy_db_name);
            }
        });
    }

    /**
     * Assign a pre-created database to this tenant
     */
    public function assignPreCreatedDatabase(): void
    {
        // Get next available database from pool(we will not use the getNextAvailableDatabase method here because if tenancy_db_name is not set)
        $availableDatabase = self::getNextAvailableDatabase();

        if ($availableDatabase) {
            // Store the database name in the tenant's data field
            $this->data = array_merge($this->data ?? [], ['tenancy_db_name' => $availableDatabase]);
            $this->save();

            $this->setupDatabase($availableDatabase);
        } else {
            throw new \Exception('No available databases. Please create more databases in cPanel.');
        }
    }

    /**
     * Create database and run setup using package methods
     */
    public function setupDatabase(string $tenancy_db_name): void
    {
        try {
            \Log::info("Setting up tenant database: {$tenancy_db_name}");

            // Assign the database in tenant_databases table if not exists
            $tenantDb = DB::table('tenant_databases')->where('tenant_id', $this->id)->first();
            if (!$tenantDb) {
                DB::table('tenant_databases')->insert([
                    'tenant_id' => $this->id,
                    'name' => $tenancy_db_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Mark database as assigned in pool
                DatabasePool::where('database_name', $tenancy_db_name)->update([
                    'is_available' => false,
                    'assigned_to_tenant' => $this->id,
                    'updated_at' => now(),
                ]);
            }

            // ---------------------------
            // Initialize tenancy context
            // ---------------------------
            tenancy()->initialize($this);

            // ---------------------------
            // Run tenant migrations
            // ---------------------------
            $migrationStatus = \Artisan::call('migrate', [
                '--path' => 'database/migrations/tenants',
                '--force' => true,
            ]);
            \Log::debug("Tenant migrations executed. Status: {$migrationStatus}");

            // ---------------------------
            // Run tenant seeders
            // ---------------------------
            $seederStatus = \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder',
                '--force' => true,
            ]);
            \Log::debug("Tenant seeders executed. Status: {$seederStatus}");

            // ---------------------------
            // Optionally, create subscription (this is supposed to happen in the central context but I am doing it here because I have access to the tenant model)
            // ---------------------------
            // if ($this->plan) {
            //     $defaultPlan = SubscriptionPlan::where('name', $this->plan)->first();
            //     if ($defaultPlan) {
            //         Subscription::create([
            //             'tenant_id' => $this->id,
            //             'subscription_plan_id' => $defaultPlan->id,
            //             'price' => $defaultPlan->monthly_price,
            //             'billing_cycle' => 'monthly',
            //             'start_date' => now(),
            //             'end_date' => now()->addMonth(),
            //         ]);
            //     }
            // }

            \Log::info("Tenant database setup completed for tenant: {$this->name}");
        } catch (\Exception $e) {
            \Log::error("Tenant database setup failed for {$this->name}: " . $e->getMessage());
            throw $e;
        } finally {
            // End tenant context so Eloquent returns to central DB
            tenancy()->end();
        }
    }

    /**
     * Get next available pre-created database(since Tenant::create expects the tenancy_db_name attribute to be set we will make this public static)
     */
    public static function getNextAvailableDatabase(): ?string
    {
        // Define your database naming pattern
        $databasePrefix = 'nexusflo_ubix_';
        
        // Check which databases exist and are not in use
        $existingDatabases = (new self)->getExistingDatabases();
        $usedDatabases = DB::table('tenant_databases')->pluck('name')->toArray();
        
        foreach ($existingDatabases as $db) {
            \Log::info('Checking database: ' . $db);
            if (str_starts_with($db, $databasePrefix) && !in_array($db, $usedDatabases)) {
                return $db;
            }
        }
        
        return null;
    }

    /**
     * Get list of databases that exist on the server
     */
    protected function getExistingDatabases(): array
    {
        // This requires a special permission - may not work on cPanel
        try {
            $databases = DB::select('SHOW DATABASES'); // i do not like it, the database maybe in use by another tenant
            // database must be empty or not in use by another tenant

            $databases = array_filter($databases, fn($db) => str_starts_with($db->Database, 'nexusflo_ubix_'));
            return array_column($databases, 'Database');
        } catch (\Exception $e) {
            \Log::error('Error fetching databases: ' . $e->getMessage());
            // get databases from database_pool table instead (this a pre-created list of databases I prefer to use)
            return DB::table('database_pool')->pluck('database_name')->toArray();
        }
    }

    /**
     * give the domain to tenant
     */
    protected function setupTenantDomain($tenant): void
    {
        $centralDomain = config('tenancy.central_domains')[0] ?? 'ubixcentral.local';

        \Log::debug('Setting up tenant domain...');

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
        // update domain in tenant model
        $tenant->domain = $unique_domain;
        $tenant->save();
        \Log::info("Tenant domain set to: {$unique_domain}");
    }
    /**
     * relationship with subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    /**
     * relationship with subscription plan through subscriptions
     */
    public function subscriptionPlan()
    {
        return $this->hasOneThrough(SubscriptionPlan::class, Subscription::class);
    }

    /**
     * current subscription
     */
    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trial']);
    }
}