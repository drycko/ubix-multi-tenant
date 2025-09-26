<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Stancl\Tenancy\Events\TenancyBootstrapped;
use Stancl\Tenancy\Events\EndingTenancy;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Role;
use App\Models\Permission;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class, // Not needed since we are using pre-created databases
                    Jobs\MigrateDatabase::class,
                    // Jobs\SeedDatabase::class,
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            // Events\EndingTenancy::class => [
            //     function (EndingTenancy $event) {
            //         $permissionRegistrar = app(PermissionRegistrar::class);
            //         $permissionRegistrar->cacheKey = 'spatie.permission.cache';
            //     },
            // ],
            // EndingTenancy::class => [
            //     function (EndingTenancy $event) {
            //         // Revert Spatie models to central DB connection
            //         Role::resolveConnection(config('database.default'));
            //         Permission::resolveConnection(config('database.default'));

            //         // Reset cache key
            //         $permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);
            //         $permissionRegistrar->cacheKey = 'spatie.permission.cache';
            //     },
            // ],
            Events\EndingTenancy::class => [
                function (EndingTenancy $event) {
                    // Revert Spatie models to central DB connection
                    // \App\Models\Role::resolveConnection(config('database.default'));
                    // \App\Models\Permission::resolveConnection(config('database.default'));

                    // Reset cache key
                    $permissionRegistrar = app(PermissionRegistrar::class);
                    $permissionRegistrar->cacheKey = 'spatie.permission.cache';
                },
            ],

            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            // Events\TenancyBootstrapped::class => [
            //     function (TenancyBootstrapped $event) {
            //         $permissionRegistrar = app(PermissionRegistrar::class);
            //         $permissionRegistrar->cacheKey =
            //             'spatie.permission.cache.tenant.' . $event->tenant->getTenantKey();
            //     },
            // ],
            
            Events\TenancyBootstrapped::class => [
                function (TenancyBootstrapped $event) {
                    // Get tenant instance
                    $tenant = $event->tenancy->tenant;

                    // Optional: adjust Spatie cache key per tenant
                    $permissionRegistrar = app(PermissionRegistrar::class);
                    $permissionRegistrar->cacheKey =
                        'spatie.permission.cache.tenant.' . $tenant->getTenantKey();

                    // Ensure Role/Permission models use tenant connection
                    // \App\Models\Role::resolveConnection('tenant');
                    // \App\Models\Permission::resolveConnection('tenant');
                },
            ],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
