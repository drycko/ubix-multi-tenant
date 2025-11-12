# Permission Loss Fix - Multi-Tenant Application

## Problem Description

Users were losing permissions during their session on both tenant and central levels. The issue required running `php artisan optimize:clear` frequently to restore permissions. This was happening in a Tenancy v3 application where:
- Central domain: `nexusflow.ubix.co.za`
- Tenant domains: Subdomains of `ubix.co.za` (not the central domain)

## Root Cause

The issue was caused by the **`ConfigureTenantSession` middleware dynamically changing session configuration during the request**:

1. **Session Recreation**: The middleware was calling `Config::set('session.cookie', ...)` and `Config::set('session.domain', ...)` during each request
2. **Too Late Configuration**: This happened AFTER Laravel's session system had already started reading the session
3. **Lost Authentication**: Changing the session cookie name mid-request caused Laravel to look for a different cookie, effectively creating a new session and losing the authenticated user
4. **Lost Permissions**: Since permissions are tied to the authenticated user, losing authentication meant losing permissions
5. **Inconsistent State**: Each request could potentially use different session cookie names depending on the exact hostname

The middleware was well-intentioned (trying to isolate sessions between central and tenant domains) but was implemented at the wrong layer. Session configuration must be set **before** the request cycle begins, not during middleware execution.

## Solution Implemented

### 1. Disabled Dynamic Session Configuration (`app/Http/Middleware/ConfigureTenantSession.php`)

Converted the middleware to a pass-through (no-op) to stop it from changing session configuration mid-request:

```php
public function handle(Request $request, Closure $next): Response
{
    // Simply pass through - session config should be set in config/session.php
    // or via environment variables, not dynamically during request
    return $next($request);
}
```

**Why**: Session configuration changes during a request cause session recreation and authentication loss.

### 2. Proper Session Domain Configuration (`config/session.php`)

Set the session domain properly in the configuration file:

```php
'domain' => env('SESSION_DOMAIN', '.ubix.co.za'),
```

**Benefits**:
- Sessions work consistently across all `*.ubix.co.za` subdomains
- No session recreation during requests
- Tenant isolation is handled by the tenancy package's built-in features
- Authentication persists properly

### 3. Clear Permission Cache on Tenancy Events (`app/Providers/TenancyServiceProvider.php`)

Added event listeners to clear the permission cache when switching contexts:

```php
Events\TenancyBootstrapped::class => [
    function () {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    },
],
Events\RevertedToCentralContext::class => [
    function () {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    },
],
```

**Benefits**:
- Ensures fresh permissions are loaded when context changes
- Works with CacheTenancyBootstrapper which already scopes cache by tenant

### 4. Context-Aware Permission Cache Middleware (`app/Http/Middleware/RefreshPermissionCache.php`)

Created a safety-net middleware that detects context switches:

```php
public function handle(Request $request, Closure $next): Response
{
    $currentTenantId = tenant('id');
    $lastTenantId = Session::get('last_tenant_context');
    
    if ($lastTenantId !== $currentTenantId) {
        $this->permissionRegistrar->forgetCachedPermissions();
        Session::put('last_tenant_context', $currentTenantId);
    }
    
    return $next($request);
}
```

**Benefits**:
- Only clears cache when context actually changes
- Handles edge cases where tenancy events might not fire

## How It Works Together

1. **Session Configuration** (`config/session.php`) sets `SESSION_DOMAIN=.ubix.co.za` which allows sessions to work across all subdomains
2. **ConfigureTenantSession middleware** is now a pass-through (does nothing) - preventing session recreation
3. **CacheTenancyBootstrapper** (already configured in `config/tenancy.php`) automatically tags all cache entries with `tenant{id}` when in tenant context
4. **Permission cache** uses a static key (`spatie.permission.cache`) but the cache entries are scoped by the tenant tags from CacheTenancyBootstrapper
5. **Event listeners** clear the permission cache when switching contexts (tenancy bootstrapped or ended)
6. **RefreshPermissionCache middleware** provides an additional safety net by tracking context in session and clearing cache on switches
7. **Result**: Sessions persist correctly, authentication is maintained, and permissions work consistently across contexts

## Files Modified

1. **`app/Http/Middleware/ConfigureTenantSession.php`** - Disabled dynamic session configuration (converted to pass-through)
2. **`config/session.php`** - Set proper session domain (`.ubix.co.za`)
3. **`app/Providers/TenancyServiceProvider.php`** - Added permission cache clearing on tenancy events
4. **`app/Http/Middleware/RefreshPermissionCache.php`** - Created context-aware permission cache middleware (NEW FILE)
5. **`bootstrap/app.php`** - Registered RefreshPermissionCache middleware

## Environment Variables

Update your `.env` file to ensure proper session configuration:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.ubix.co.za
SESSION_SECURE_COOKIE=true  # Set to true in production with HTTPS
SESSION_SAME_SITE=lax
```

**Note**: Remove any duplicate `SESSION_DOMAIN` entries in your `.env` file.

## Testing Recommendations

After deploying these changes, test the following scenarios:

### 1. Central Domain Permissions
- Login to central domain (`nexusflow.ubix.co.za`)
- Verify permissions work correctly
- Switch between different admin features
- Check that permissions persist throughout the session

### 2. Tenant Domain Permissions
- Login to a tenant subdomain (e.g., `tenant1.ubix.co.za`)
- Verify tenant-specific permissions work
- Navigate through different tenant features
- Check permissions persist

### 3. Context Switching
- Open central domain in one browser tab
- Open tenant domain in another tab (same browser)
- Switch between tabs and perform actions
- Verify permissions don't bleed between contexts

### 4. Multiple Tenants
- Login to tenant1 subdomain
- Perform actions requiring permissions
- Login to tenant2 subdomain (different tab or after logout)
- Verify tenant2 has correct permissions (not tenant1's)

### 5. Session Persistence
- Login and use the application normally
- Wait for some time without running `optimize:clear`
- Verify permissions continue to work correctly

## Expected Behavior

After this fix:
- ✅ Permissions should persist throughout the entire session
- ✅ No need to run `php artisan optimize:clear` manually
- ✅ Central and tenant permissions are completely isolated
- ✅ Each tenant has its own permission cache
- ✅ Switching between contexts automatically refreshes permissions
- ✅ Better performance (cache is reused within the same context)

## Performance Considerations

The solution is designed to minimize performance impact:
- Permissions are still cached (24 hour expiration by default)
- Cache is only cleared when context actually changes
- No clearing on every request, only on context switch
- Tenant-aware cache keys prevent unnecessary cache lookups

## Rollback Plan

If issues occur after deployment, you can temporarily revert by:

1. Comment out the middleware in `bootstrap/app.php`:
```php
// \App\Http\Middleware\RefreshPermissionCache::class,
```

2. Comment out the event listeners in `app/Providers/TenancyServiceProvider.php`

Then run: `php artisan optimize:clear`

## Additional Notes

- The `CacheTenancyBootstrapper` in `config/tenancy.php` is already enabled, which properly scopes Laravel cache for tenants
- Session configuration already uses domain-specific cookies via `ConfigureTenantSession` middleware
- Database session driver is being used, which is appropriate for multi-tenancy

## Prevention

To prevent similar issues in the future:
1. Always consider multi-tenancy when implementing packages that use caching
2. Test context switching scenarios thoroughly
3. Monitor for cache-related issues in production
4. Consider tenant-aware cache keys for any global caching mechanisms
