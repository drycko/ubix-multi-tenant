<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $propertyId = $request->route('property') ?? $request->input('property_id');

        // Superuser: property_id is null, can access all properties
        if (is_null($user->property_id)) {
            return $next($request);
        }

        // Regular staff: property_id must match
        if ($propertyId && $user->property_id == $propertyId) {
            return $next($request);
        }

        // Unauthorized access
        abort(403, 'Unauthorized property access.');
    }
}
