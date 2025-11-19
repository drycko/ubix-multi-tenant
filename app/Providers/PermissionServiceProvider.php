<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Permissions\TenantAwarePermissionRegistrar;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Events\TenancyInitialized;
use Stancl\Tenancy\Events\TenancyEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class PermissionServiceProvider extends ServiceProvider
{
  public function register()
  {
    // Bind the tenant-aware registrar in the container so all calls resolve to it.
    $this->app->singleton(PermissionRegistrar::class, function ($app) {
      return new TenantAwarePermissionRegistrar($app);
    });
  }
  
  public function boot()
  {
    // When tenancy initializes, set the registrar cache key to the tenant id.
    Event::listen(TenancyInitialized::class, function (TenancyInitialized $event) {
      try {
        $tenantId = $event->tenant->id ?? null;
        /** @var PermissionRegistrar $registrar */
        $registrar = resolve(PermissionRegistrar::class);
        if (method_exists($registrar, 'setTenantCacheKey')) {
          $registrar->setTenantCacheKey($tenantId);
          // Refresh cached permissions for this tenant (one-time)
          $registrar->forgetCachedPermissions();
          Log::debug('PermissionServiceProvider: TenancyInitialized set tenant cache key', ['tenant_id' => $tenantId]);
        }
      } catch (\Throwable $e) {
        Log::warning('PermissionServiceProvider: TenancyInitialized handler failed', ['exception' => $e->getMessage()]);
      }
    });
    
    // When tenancy ends (back to central), set central cache key and forget cached permissions.
    Event::listen(TenancyEnded::class, function () {
      try {
        /** @var PermissionRegistrar $registrar */
        $registrar = resolve(PermissionRegistrar::class);
        if (method_exists($registrar, 'setTenantCacheKey')) {
          $registrar->setTenantCacheKey(null);
          $registrar->forgetCachedPermissions();
          Log::debug('PermissionServiceProvider: TenancyEnded reset to central cache key');
        }
      } catch (\Throwable $e) {
        Log::warning('PermissionServiceProvider: TenancyEnded handler failed', ['exception' => $e->getMessage()]);
      }
    });
  }
}