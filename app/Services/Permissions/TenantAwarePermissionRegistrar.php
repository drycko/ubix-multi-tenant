<?php

namespace App\Services\Permissions;

use Spatie\Permission\PermissionRegistrar;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;

/**
 * Tenant-aware PermissionRegistrar
 *
 * This extends Spatie's PermissionRegistrar to use a cache key that is namespaced
 * by tenant id (or "central" when no tenant). This avoids cross-tenant cache collisions.
 */
class TenantAwarePermissionRegistrar extends PermissionRegistrar
{
    public function __construct(Application $app)
    {
        parent::__construct($app);

        // Initialize cache key for current context
        $this->setTenantCacheKey($this->resolveCurrentTenantId());
    }

    /**
     * Resolve the current tenant id safely.
     */
    protected function resolveCurrentTenantId(): ?int
    {
        try {
            if (function_exists('tenant')) {
                return tenant('id') ?: null;
            }
        } catch (\Throwable $e) {
            // tenant() may not be available in console or early boot, ignore
        }
        return null;
    }

    /**
     * Set the cache key to be used by the registrar.
     * Call this whenever tenancy context changes.
     *
     * Example keys:
     *  - spatie.permission.cache:tenant:123
     *  - spatie.permission.cache:central
     */
    public function setTenantCacheKey(?int $tenantId): void
    {
        $tenantSuffix = $tenantId ? "tenant:{$tenantId}" : 'central';
        $this->cacheKey = 'spatie.permission.cache:' . $tenantSuffix;

        // optional debug
        Log::debug('TenantAwarePermissionRegistrar: setting cache key', [
            'cache_key' => $this->cacheKey,
            'tenant_id' => $tenantId,
        ]);
    }
}