<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register subscription feature Blade directives
        $this->registerSubscriptionBladeDirectives();
    }

    /**
     * Register custom Blade directives for subscription checks
     */
    protected function registerSubscriptionBladeDirectives(): void
    {
        // @feature('feature_name') ... @endfeature
        Blade::if('feature', function ($feature) {
            $tenant = tenant();
            return $tenant && $tenant->canAccessFeature($feature);
        });

        // @withinLimit('limitation_name', $currentCount) ... @endwithinLimit
        Blade::if('withinLimit', function ($limitation, $currentCount) {
            $tenant = tenant();
            return $tenant && $tenant->isWithinLimit($limitation, $currentCount);
        });

        // @subscriptionActive ... @endsubscriptionActive
        Blade::if('subscriptionActive', function () {
            $tenant = tenant();
            return $tenant && $tenant->hasActiveSubscription();
        });

        // @subscriptionExpired ... @endsubscriptionExpired
        Blade::if('subscriptionExpired', function () {
            $tenant = tenant();
            return $tenant && $tenant->isSubscriptionExpired();
        });

        // @gracePeriod ... @endgracePeriod
        Blade::if('gracePeriod', function ($days = 3) {
            $tenant = tenant();
            return $tenant && $tenant->isInGracePeriod($days);
        });
    }
}
