<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant; // adapt to your Tenant model
use Illuminate\Support\Facades\Log;

class DetectTenantFromSubdomain
{
  public function handle(Request $request, Closure $next)
  {
    $host = $request->getHost(); // e.g. tenant1.central.example.com
    // Configure your base domain here or load from env
    $base = parse_url(config('app.url'), PHP_URL_HOST); // e.g. central.example.com
    
    // If host equals base, treat as central dashboard (no tenant)
    if ($host === $base) {
      // Optionally set tenant context to null
      app()->instance('current_tenant', null);
      return $next($request);
    }
    
    // Extract subdomain portion (tenant slug)
    if (str_ends_with($host, '.' . $base)) {
      $subdomain = substr($host, 0, -(strlen($base) + 1)); // tenant1
    } else {
      // host not matching expected pattern — treat as no-tenant
      $subdomain = null;
    }
    
    if ($subdomain) {
      // Lookup tenant by subdomain slug (adjust field name)
      $tenant = Tenant::where('subdomain', $subdomain)->first();
      if ($tenant) {
        // Store tenant in service container for access elsewhere
        app()->instance('current_tenant', $tenant);
        // Optionally set tenant config or use a TenantManager service
        // e.g. TenantManager::setTenant($tenant);
      } else {
        // tenant not found — log and optionally abort 404
        Log::warning('Tenant not found for host', ['host' => $host, 'subdomain' => $subdomain]);
        abort(404, 'Tenant not found');
      }
    }
    
    return $next($request);
  }
}