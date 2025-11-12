# Guest Portal Navigation Update

## Overview
Updated the guest portal navigation system to use proper Laravel routes instead of hardcoded example URLs. The navigation now properly supports both authenticated and unauthenticated users across desktop and mobile views.

## Changes Made

### 1. Desktop Navigation (`guest.blade.php`)

#### Top Navigation Menu
**Unauthenticated Users:**
- Home → `tenant.guest-portal.index`
- Book a Room → `tenant.guest-portal.booking`
- Login button → `tenant.guest-portal.login`

**Authenticated Users:**
- Home → `tenant.guest-portal.index`
- Dashboard → `tenant.guest-portal.dashboard`
- My Bookings → `tenant.guest-portal.bookings`
- Invoices → `tenant.guest-portal.invoices`
- Check-In/Out → `tenant.guest-portal.checkin`
- Book a Room (button) → `tenant.guest-portal.booking`

#### User Dropdown Menu (Authenticated Only)
- Dashboard → `tenant.guest-portal.dashboard`
- My Bookings → `tenant.guest-portal.bookings`
- Invoices → `tenant.guest-portal.invoices`
- Check-In/Out → `tenant.guest-portal.checkin`
- Digital Keys → `tenant.guest-portal.keys`
- Logout → `tenant.guest-portal.logout` (POST form)

### 2. Mobile Navigation

#### Mobile Header
**Unauthenticated Users:**
- Logo → `tenant.guest-portal.index`
- Login icon → `tenant.guest-portal.login`
- Hamburger menu → Opens mobile navigation

**Authenticated Users:**
- Logo → `tenant.guest-portal.index`
- User dropdown icon → Shows user menu with:
  - Dashboard → `tenant.guest-portal.dashboard`
  - My Bookings → `tenant.guest-portal.bookings`
  - Invoices → `tenant.guest-portal.invoices`
  - Check-In/Out → `tenant.guest-portal.checkin`
  - Logout → `tenant.guest-portal.logout` (POST form)
- Hamburger menu → Opens mobile navigation

### 3. Active State Highlighting

All navigation items now include active state detection using Laravel's `Request::routeIs()` helper:
- Current page gets `current` class (top menu)
- Current page gets `active` class (dropdown menu)
- Wildcard matching for child routes (e.g., `bookings*` matches all booking pages)

## Features

### Dynamic Menu Content
- Menu automatically adapts based on authentication state
- Guest variable (`$guest`) retrieved at layout top ensures availability throughout
- Proper conditional rendering with `@if($guest)` directives

### Route-Based Navigation
- All links use Laravel named routes
- No more hardcoded example URLs (dashboard.html, etc.)
- Consistent routing patterns across desktop and mobile

### Visual Enhancements
- Bootstrap Icons for menu items
- User name display in desktop dropdown
- Clean separation between authenticated/public menus

### Mobile Responsiveness
- Removed unused cart button
- Proper dropdown functionality on mobile
- Consistent user experience across devices

## Technical Details

### Layout Variable
Added at line 15 of `guest.blade.php`:
```php
$guest = session('guest_id') ? \App\Models\Tenant\Guest::find(session('guest_id')) : null;
```

### Route Checking Pattern
```php
Request::routeIs('tenant.guest-portal.dashboard') ? 'current' : ''
Request::routeIs('tenant.guest-portal.bookings*') ? 'current' : '' // Wildcard for child routes
```

### Logout Form Implementation
```php
<form method="POST" action="{{ route('tenant.guest-portal.logout') }}" class="dropdown-item">
    @csrf
    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; width: 100%; text-align: left;">
      <i class="bi bi-box-arrow-right"></i> Logout
    </button>
</form>
```

## Routes Referenced

All routes used are defined in `routes/tenant.php`:
- `tenant.guest-portal.index` - Landing page
- `tenant.guest-portal.login` - Login page
- `tenant.guest-portal.logout` - Logout action
- `tenant.guest-portal.dashboard` - Guest dashboard
- `tenant.guest-portal.booking` - Book a room
- `tenant.guest-portal.bookings` - My bookings list
- `tenant.guest-portal.bookings.show` - Booking detail
- `tenant.guest-portal.invoices` - My invoices list
- `tenant.guest-portal.checkin` - Check-in/out page
- `tenant.guest-portal.keys` - Digital keys page

## User Experience

### Unauthenticated Flow
1. User lands on guest portal
2. Sees public menu: Home, Book a Room
3. Can click Login to authenticate
4. After magic link login, redirected to dashboard

### Authenticated Flow
1. User logged in via magic link
2. Sees full navigation with all features
3. Can access Dashboard, Bookings, Invoices, Check-In/Out, Keys
4. User name displayed in dropdown
5. Can logout via form submission

## Testing Checklist

- [ ] Desktop navigation displays correctly for unauthenticated users
- [ ] Desktop navigation displays correctly for authenticated users
- [ ] Mobile navigation displays correctly for both states
- [ ] Active states highlight correctly on all pages
- [ ] User dropdown shows correct guest name
- [ ] All links navigate to correct routes
- [ ] Mobile hamburger menu opens/closes properly
- [ ] Logout functionality works correctly
- [ ] Book a Room button accessible from all states

## Next Steps

Future enhancements planned:
1. Add housekeeping requests to menu
2. Add kiosk/shop menu item
3. Profile management page
4. Notification badge on dropdown

## Files Modified

- `resources/views/tenant/layouts/guest.blade.php` - Main layout file with navigation
