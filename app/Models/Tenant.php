<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

use Illuminate\Support\Facades\DB;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    // can we add fillable fields
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'phone',
    //     'address',
    //     'timezone',
    //     'currency',
    //     'locale',
    //     'plan',
    //     'trial_ends_at',
    //     'is_active',
    //     'data',
    // ];

    // protected $casts = [
    //     'trial_ends_at' => 'datetime',
    //     'is_active' => 'boolean',
    //     'data' => 'array',
    // ];

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
            if (!$tenant->trial_ends_at) $tenant->trial_ends_at = now()->addDays(14);
            if (is_null($tenant->is_active)) $tenant->is_active = true;
        });

        static::created(function (Tenant $tenant) {
            // $centralDomain = config('tenancy.central_domains')[0] ?? 'ubix.local';
            // // if (request() && request()->getHost() === $centralDomain) {
            // //     // we are in the central domain context
            // // }
            // // else {
            // //     // we are in the tenant context already
            // //     return;
            // // }
            // // Create a unique domain for the tenant
            // $tenant_prefix = \Illuminate\Support\Str::slug($tenant->name, '-');
            // // $domain_base = '.nexusflow.co.za';
            // $existingDomains = DB::table('domains')->pluck('domain')->toArray();
            // $unique_domain = $tenant_prefix . $centralDomain;
            // $i = 1;
            // while (in_array($unique_domain, $existingDomains)) {
            //     $unique_domain = $tenant_prefix . '-' . $i . $centralDomain;
            //     $i++;
            // }
            // this will not trigger for some reason?
            // $tenant->domains()->create([
            //     'domain' => $unique_domain,
            //     // 'is_primary' => true,
            // ]);

            \Log::info('Current sent tenancy_db_name: '. $tenant->tenancy_db_name);

            // Assign next available pre-created database if database attribute not set
            if (empty($tenant->tenancy_db_name) && empty($tenant->data['tenancy_db_name'])) {
                $tenant->assignPreCreatedDatabase();
            }
            else {
                // if tenancy_db_name is set we can setup the database
                $tenant->setupDatabase($tenant->tenancy_db_name);
            }

            // subscription creation
            if ($tenant->plan) {
                // check if the plan exists
                $defaultPlan = SubscriptionPlan::where('name', $tenant->plan)->first();
                if ($defaultPlan) {
                    Subscription::create([
                        'tenant_id' => $tenant->id,
                        'subscription_plan_id' => $defaultPlan->id,
                        'price' => $defaultPlan->monthly_price,
                        'billing_cycle' => 'monthly',
                        'start_date' => now(),
                        'end_date' => now()->addMonth(),
                    ]);
                }
                
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
     * Run tenant migrations and seeders after database assignment
     */
    // public function setupDatabase(): void
    // {
    //     \Artisan::call('tenants:migrate', [
    //         '--tenants' => [$this->id],
    //         '--force' => true,
    //     ]);

    //     \Artisan::call('tenants:seed', [
    //         '--tenants' => [$this->id],
    //         '--force' => true,
    //     ]);
    // }
    /**
     * Create database and run setup using package methods
     */
    public function setupDatabase($tenancy_db_name): void
    {
        // try {
            // can this run in the context of the tenant? it should be able to pick up the database from the tenant's data field
            // we make sure the tenant databases is populated before running the migrations and seeders
            // $tenancy_db_name = $this->data['tenancy_db_name'];
            \Log::debug("tenancy_db_name: ". $tenancy_db_name);
            // check if tenant database exists and create if not
            $tenantDb = DB::table('tenant_databases')->where('tenant_id', $this->id)->first();
            if (!$tenantDb) {
                DB::table('tenant_databases')->insert([
                    'tenant_id' => $this->id,
                    'name' => $tenancy_db_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // update database_pool to mark database as assigned
                DatabasePool::where('database_name', $tenancy_db_name)->update([
                    'is_available' => false,
                    'assigned_to_tenant' => $this->id,
                    'updated_at' => now(),
                ]);
            }

            // tenancy()->initialize($this); not needed because we are already in the tenant context
            // Run migrations
            $migrationStatus = \Artisan::call('migrate', [
                '--path' => 'database/migrations/tenants',
                '--force' => true,
            ]);

            \Log::debug('tenant migrationStatus: '. $migrationStatus);
            
            // Run seeders we should not run seeders here rather
            // php artisan tenant:seed my-custom-id
            // # Or, to seed all tenants
            // php artisan tenant:seed 
            $seederStatus = \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder',
                '--force' => true,
            ]);

            \Log::debug('tenant seederStatus: '. $seederStatus);
            
            $this->command->info("Tenant database setup completed for: {$this->name}");
            
        // } catch (\Exception $e) {
        //     \Log::error("Failed to setup tenant database: " . $e->getMessage());
        //     // $this->command->info("Failed to setup tenant database: " . $e->getMessage());
        // } finally {
        //     tenancy()->end();
        // }
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
}