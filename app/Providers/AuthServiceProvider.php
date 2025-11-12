<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
     */
    protected function registerSubscriptionFeatureGates(): void
    {
        // Advanced reporting feature
        Gate::define('advanced-reporting', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('advanced_reporting');
        });

        // Advanced analytics feature
        Gate::define('advanced-analytics', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('advanced_analytics');
        });

        // Multi-property management
        Gate::define('multi-property', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('multi_property_management');
        });

        // Housekeeping management
        Gate::define('housekeeping', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('housekeeping_management');
        });

        // Email notifications feature
        Gate::define('email-notifications', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('email_notifications');
        });

        // SMS notifications feature
        Gate::define('sms-notifications', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('sms_notifications');
        });

        // API access feature
        Gate::define('api-access', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('api_access');
        });

        // Custom branding feature
        Gate::define('custom-branding', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('custom_branding');
        });

        // Priority support feature
        Gate::define('priority-support', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('priority_support');
        });

        // White label feature
        Gate::define('white-label', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('white_label');
        });

        // Guest portal feature
        Gate::define('guest-portal', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('guest_portal');
        });

        // Online payments feature
        Gate::define('online-payments', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('online_payments');
        });

        // Inventory management feature
        Gate::define('inventory-management', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('inventory_management');
        });

        // Task management feature
        Gate::define('task-management', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('task_management');
        });

        // Document storage feature
        Gate::define('document-storage', function ($user) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature('document_storage');
        });
    }
}
