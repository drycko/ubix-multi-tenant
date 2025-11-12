# Permission Loss Fix - Action Items

## The Real Problem

The `ConfigureTenantSession` middleware was **changing session configuration mid-request**, causing:
- Session to be recreated with a different cookie name
- User authentication to be lost
- Permissions to disappear (since they're tied to the authenticated user)

This is why running `php artisan optimize:clear` seemed to help temporarily - it was clearing various caches, but the real issue was session recreation.

## Changes Made

### 1. Disabled ConfigureTenantSession Middleware
- **File**: `app/Http/Middleware/ConfigureTenantSession.php`
- **Change**: Converted to a pass-through (does nothing now)
- **File**: `bootstrap/app.php`
- **Change**: Commented out the middleware registration

### 2. Fixed Session Configuration
- **File**: `config/session.php`
- **Change**: Set default `session.domain` to `.ubix.co.za`

### 3. Added Permission Cache Management
- **File**: `app/Providers/TenancyServiceProvider.php`
- **Change**: Clear permission cache when switching tenant contexts
- **File**: `app/Http/Middleware/RefreshPermissionCache.php` (NEW)
- **Change**: Detect context switches and clear permission cache

## Required Steps

### 1. Update Your .env File

Edit your `.env` file and ensure you have:

```env
SESSION_DRIVER=database
SESSION_DOMAIN=.ubix.co.za
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

**Important**: Remove any duplicate `SESSION_DOMAIN` entries. You currently have it twice.

### 2. Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Test Immediately

After deploying these changes:

#### Test 1: Basic Login (Central Domain)
1. Go to `https://nexusflow.ubix.co.za`
2. Login with admin credentials
3. Navigate through different pages
4. Check permissions are working
5. **Wait 5-10 minutes** and check again (don't run optimize:clear!)

#### Test 2: Basic Login (Tenant Domain)
1. Go to a tenant subdomain (e.g., `https://tenant1.ubix.co.za`)
2. Login with tenant user credentials
3. Navigate through different pages
4. Check permissions are working
5. **Wait 5-10 minutes** and check again

#### Test 3: Context Switching
1. Open central domain in one browser tab
2. Login and verify it works
3. Open tenant domain in another tab (same browser)
4. Login and verify it works
5. Switch between tabs multiple times
6. Both should maintain their sessions and permissions

### 4. Monitor for 24 Hours

Watch for any users reporting permission issues. If issues occur, check:
- Laravel logs (`storage/logs/laravel.log`)
- Web server error logs
- Session table in database

## What Should Happen Now

✅ **Sessions persist correctly** - No more session recreation  
✅ **Authentication maintained** - Users stay logged in  
✅ **Permissions work consistently** - No need to run `optimize:clear`  
✅ **Context isolation** - Central and tenant permissions don't mix  
✅ **Performance maintained** - Permissions are still cached appropriately  

## If Issues Persist

If you still experience permission loss after these changes:

1. **Check the session table**: Verify sessions are being stored
   ```bash
   php artisan tinker
   >>> DB::connection('central')->table('sessions')->count();
   ```

2. **Check authentication guard**: Verify you're using the correct guard (`tenant` vs `web`)

3. **Check middleware stack**: Look at the actual middleware order
   ```bash
   php artisan route:list
   ```

4. **Enable query logging** to see what's happening with permissions:
   ```php
   // Add to AppServiceProvider boot method temporarily
   DB::listen(function($query) {
       if (str_contains($query->sql, 'permissions') || str_contains($query->sql, 'roles')) {
           Log::info('Permission Query: ' . $query->sql);
       }
   });
   ```

## Rollback Plan

If major issues occur, you can rollback by:

1. Re-enable the original middleware (uncomment in `bootstrap/app.php`)
2. Revert `config/session.php` changes
3. Run `php artisan optimize:clear`
4. Contact for further assistance

## Why This Should Work

- **Session configuration is now static**: Set in config files, not changed during requests
- **Tenancy package handles isolation**: CacheTenancyBootstrapper already scopes cache by tenant
- **Permission cache is cleared on context switches**: Prevents stale permissions
- **No more session recreation**: The root cause is eliminated

The key insight: **You don't need custom session middleware in a multi-tenant app when using Stancl/Tenancy**. The package already handles all the necessary isolation through its bootstrappers.
