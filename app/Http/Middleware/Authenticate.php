<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if we're on a portal route (tenant admin portal)
            if ($request->is('p/*') || $request->is('portal') || $request->is('portal/*')) {
                return route('portal.login');
            }
            
            // Check if we're in a tenant context
            if ($request->getHost() !== config('tenancy.central_domains')[0]) {
                return route('tenant.login');
            }
            
            // For central domain - redirect to central login
            return route('central.login');
        }
        return null;
    }
}