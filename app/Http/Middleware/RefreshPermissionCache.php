<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class RefreshPermissionCache
{
    protected PermissionRegistrar $permissionRegistrar;

    public function __construct(PermissionRegistrar $permissionRegistrar)
    {
        $this->permissionRegistrar = $permissionRegistrar;
    }

    /**
     * Handle an incoming request.
     *
     * This middleware ensures that permissions are properly loaded
     * for the current context (central vs tenant) by detecting context
     * switches and clearing the permission cache when needed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenantId = tenant('id');
        $lastTenantId = Session::get('last_tenant_context');
        
        // If the tenant context has changed (or switched from central to tenant or vice versa)
        // clear the permission cache to prevent stale permissions
        if ($lastTenantId !== $currentTenantId) {
            $this->permissionRegistrar->forgetCachedPermissions();
            Session::put('last_tenant_context', $currentTenantId);
        }
        
        return $next($request);
    }
}
