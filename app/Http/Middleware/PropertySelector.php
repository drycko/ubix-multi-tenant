<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant\Property;

class PropertySelector
{
    public function handle(Request $request, Closure $next)
    {
        // Skip for auth routes and API auth routes
        if ($this->shouldSkipMiddleware($request)) {
            return $next($request);
        }

        // Only apply to authenticated users in tenant context
        if (!auth()->check() || !tenancy()->initialized) {
            return $next($request);
        }

        $user = auth()->user();

        // Handle property selection for super-users
        if (is_super_user()) {
            // Check if user is trying to select a property
            if ($request->has('switch_property')) {
                $propertyId = $request->get('switch_property');
                
                if ($propertyId === 'all') {
                    // Clear property selection
                    session()->forget('selected_property_id');
                    $request->attributes->set('current_property', null);
                    $request->attributes->set('currentProperty', null); // Legacy compatibility
                } else {
                    // Validate property exists
                    $property = Property::find($propertyId);
                    if ($property) {
                        session()->put('selected_property_id', $propertyId);
                        $request->attributes->set('current_property', $property);
                        $request->attributes->set('currentProperty', $property); // Legacy compatibility
                        app()->instance('currentProperty', $property); // Legacy compatibility
                    }
                }
                
                // Redirect to remove the switch_property parameter
                return redirect($request->url());
            }

            // Get selected property from session
            $selectedPropertyId = session('selected_property_id');
            if ($selectedPropertyId) {
                $property = Property::find($selectedPropertyId);
                if ($property) {
                    $request->attributes->set('current_property', $property);
                    $request->attributes->set('currentProperty', $property); // Legacy compatibility
                    app()->instance('currentProperty', $property); // Legacy compatibility
                }
            }
            // Super-users don't need property validation - they can operate globally
        } else {
            // For property-specific users, always use their assigned property
            if ($user->property_id) {
                $property = Property::find($user->property_id);
                if ($property) {
                    $request->attributes->set('current_property', $property);
                    $request->attributes->set('currentProperty', $property); // Legacy compatibility
                    app()->instance('currentProperty', $property); // Legacy compatibility
                    // Auto-select this property in session for consistency
                    session()->put('selected_property_id', $user->property_id);
                } else {
                    // Property not found - redirect to property selection or error
                    return redirect()->route('tenant.properties.select')->with('error', 'Your assigned property was not found.');
                }
            } else {
                // Regular user without property assignment
                return redirect()->route('tenant.properties.select')->with('error', 'No property assigned to your account.');
            }
        }

        return $next($request);
    }

    protected function shouldSkipMiddleware(Request $request): bool
    {
        $skipRoutes = [
            'tenant.login',
            'tenant.register',
            'password/*',
            'auth/*',
            'tenant.properties.select',
            'tenant.properties.store-selection',
            'sanctum/*',
            'api/login',
            'api/register',
            'central/*',
        ];

        foreach ($skipRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}