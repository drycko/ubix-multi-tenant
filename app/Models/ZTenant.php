<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ZTenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'timezone',
        'currency',
        'locale',
        'plan',
        'trial_ends_at',
        'is_active',
        'data',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'is_active' => 'boolean',
        'data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Tenant $tenant) {
            if (!$tenant->name) {
                $tenant->name = 'Default Tenant';
            }
            if (!$tenant->plan) {
                $tenant->plan = 'starter';
            }
            if (!$tenant->trial_ends_at) {
                $tenant->trial_ends_at = now()->addDays(14);
            }
            if (is_null($tenant->is_active)) {
                $tenant->is_active = true;
            }
        });

        static::created(function (Tenant $tenant) {
            // Create primary domain
            $tenant_prefix = Str::slug($tenant->name, '-');
            if (strlen($tenant_prefix) > 32) {
                $tenant_prefix = substr($tenant_prefix, 0, 32);
            }
            
            $tenant->domains()->create([
                'domain' => $tenant_prefix . '.ubixcentral.local',
                'is_primary' => true,
            ]);

            // Create database and run setup
            // $tenant->setupDatabase();
        });
    }

    /**
     * Create database and run setup using package methods
     */
    public function setupDatabase(): void
    {
        try {
            // Create database using package's method
            // $this->database()->create();
            
            // Initialize tenancy
            tenancy()->initialize($this);
            
            // Run migrations
            $migrationStatus = Artisan::call('migrate', [
                '--path' => 'database/migrations/tenants',
                '--force' => true,
            ]);
            
            // Run seeders
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder',
                '--force' => true,
            ]);
            
            $this->command->info("Tenant database setup completed for: {$this->name}");
            
        } catch (\Exception $e) {
            $this->command->error("Failed to setup tenant database: " . $e->getMessage());
        } finally {
            tenancy()->end();
        }
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->subscriptions()->where('status', 'active')->first();
        return $subscription && ($subscription->end_date === null || $subscription->end_date->isFuture());
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription()
    {
        return $this->subscriptions()->where('status', 'active')->latest('start_date')->first();
    }
}
