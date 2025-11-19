<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register subscription feature gates
        $this->registerSubscriptionFeatureGates();
    }

    /**
     * Register gates for subscription plan features for tenant
     *
     * Note: Gates return `true` when no tenant() is resolved (central context),
     * so central admin users are not blocked by tenant scoped feature gates.
     */
    protected function registerSubscriptionFeatureGates(): void
    {
        $featureGate = function (string $feature) {
            return function ($user) use ($feature) {
                // Resolve tenant at check time (not at boot). If no tenant, allow access.
                $tenant = function_exists('tenant') ? tenant() : null;

                // Allow access when no tenant: central portal uses separate checks.
                if (! $tenant) {
                    Log::debug('Gate bypassed for central context', [
                        'gate' => $feature,
                        'user_id' => $user->id ?? null,
                    ]);
                    return true;
                }

                // Tenant-scoped feature evaluation; protect with try/catch to avoid throwing in gates.
                try {
                    return (bool) $tenant->canAccessFeature($feature);
                } catch (\Throwable $e) {
                    Log::warning("Gate '{$feature}' evaluation failed", [
                        'exception' => $e->getMessage(),
                        'user_id' => $user->id ?? null,
                        'tenant_id' => $tenant->id ?? null,
                    ]);
                    return false;
                }
            };
        };

        Gate::define('advanced-reporting', $featureGate('advanced_reporting'));
        Gate::define('advanced-analytics', $featureGate('advanced_analytics'));
        Gate::define('multi-property', $featureGate('multi_property_management'));
        Gate::define('housekeeping', $featureGate('housekeeping_management'));
        Gate::define('email-notifications', $featureGate('email_notifications'));
        Gate::define('sms-notifications', $featureGate('sms_notifications'));
        Gate::define('api-access', $featureGate('api_access'));
        Gate::define('custom-branding', $featureGate('custom_branding'));
        Gate::define('priority-support', $featureGate('priority_support'));
        Gate::define('white-label', $featureGate('white_label'));
        Gate::define('guest-portal', $featureGate('guest_portal'));
        Gate::define('online-payments', $featureGate('online_payments'));
        Gate::define('inventory-management', $featureGate('inventory_management'));
        Gate::define('task-management', $featureGate('task_management'));
        Gate::define('document-storage', $featureGate('document_storage'));
    }
}