# Subscription Enforcement System Documentation

## Overview

The subscription enforcement system controls tenant access to features and resources based on their active subscription plan. It includes middleware, gates, Blade directives, and Tenant model helper methods.

---

## Table of Contents

1. [Middleware](#middleware)
2. [Tenant Model Methods](#tenant-model-methods)
3. [Authorization Gates](#authorization-gates)
4. [Blade Directives](#blade-directives)
5. [Resource Limitations](#resource-limitations)
6. [Subscription Plan Configuration](#subscription-plan-configuration)
7. [Usage Examples](#usage-examples)

---

## Middleware

### CheckSubscriptionStatus

**Purpose**: Validates subscription status and feature access  
**Location**: `app/Http/Middleware/CheckSubscriptionStatus.php`

**Usage**:
```php
// Applied globally to all tenant routes in routes/tenant.php
Route::middleware(['subscription.check'])->group(function () {
    // Your routes
});

// Check specific feature access
Route::get('/advanced-reports', [ReportController::class, 'advanced'])
    ->middleware('subscription.check:advanced_reporting');
```

**Behavior**:
- Checks for active subscription
- Validates expiration date (3-day grace period)
- Displays warnings for unpaid invoices
- Shows expiry notifications (7 days before)
- Returns JSON errors for API requests

### CheckResourceLimit

**Purpose**: Enforces usage limits on resources (users, properties, bookings, etc.)  
**Location**: `app/Http/Middleware/CheckResourceLimit.php`

**Usage**:
```php
// Applied to resource creation routes
Route::post('users', [UserController::class, 'store'])
    ->middleware('resource.limit:users');

Route::post('properties', [PropertyController::class, 'store'])
    ->middleware('resource.limit:properties');

Route::post('bookings', [BookingController::class, 'store'])
    ->middleware('resource.limit:bookings');
```

**Supported Resources**:
- `users` → `max_users`
- `properties` → `max_properties`
- `bookings` → `max_bookings_per_month`
- `amenities` → `max_amenities`
- `storage` → `max_storage_gb`

---

## Tenant Model Methods

### Subscription Status

```php
// Check if tenant has an active subscription
$tenant->hasActiveSubscription(): bool

// Get the active subscription with plan
$subscription = $tenant->getActiveSubscription();

// Check if subscription is expired
$tenant->isSubscriptionExpired(): bool

// Check if tenant is in grace period (default 3 days)
$tenant->isInGracePeriod($graceDays = 3): bool

// Get user-friendly status message
$message = $tenant->getSubscriptionStatusMessage();
```

### Feature Access

```php
// Check if tenant can access a specific feature
if ($tenant->canAccessFeature('advanced_reporting')) {
    // Show advanced reports
}

// Check multiple features
$features = ['api_access', 'custom_branding', 'white_label'];
foreach ($features as $feature) {
    if ($tenant->canAccessFeature($feature)) {
        // Enable feature
    }
}
```

### Limitation Checking

```php
// Check if within limit
$currentUserCount = User::count();
if ($tenant->isWithinLimit('max_users', $currentUserCount)) {
    // Allow user creation
}

// Get remaining limit
$remaining = $tenant->getRemainingLimit('max_users', $currentUserCount);
// Returns integer or 'unlimited'

// Check for unpaid invoices
if ($tenant->hasUnpaidInvoices()) {
    // Show payment reminder
}
```

---

## Authorization Gates

**Location**: `app/Providers/AuthServiceProvider.php`

### Available Gates

```php
Gate::allows('advanced-reporting')
Gate::allows('advanced-analytics')
Gate::allows('multi-property')
Gate::allows('housekeeping')
Gate::allows('email-notifications')
Gate::allows('sms-notifications')
Gate::allows('api-access')
Gate::allows('custom-branding')
Gate::allows('priority-support')
Gate::allows('white-label')
Gate::allows('guest-portal')
Gate::allows('online-payments')
Gate::allows('inventory-management')
Gate::allows('task-management')
Gate::allows('document-storage')
```

### Usage in Controllers

```php
public function advancedReports()
{
    if (Gate::denies('advanced-reporting')) {
        abort(403, 'This feature is not available in your plan.');
    }

    // Show advanced reports
}

// Or with authorize method
public function advancedReports()
{
    $this->authorize('advanced-reporting');
    // Automatically throws 403 if denied
}
```

### Usage in Views

```php
@can('advanced-reporting')
    <a href="{{ route('tenant.reports.advanced') }}">Advanced Reports</a>
@endcan

@cannot('api-access')
    <div class="alert alert-info">
        Upgrade to access API features!
    </div>
@endcannot
```

---

## Blade Directives

**Location**: `app/Providers/AppServiceProvider.php`

### @feature

Check if feature is available:

```blade
@feature('advanced_reporting')
    <div class="advanced-reports-widget">
        <!-- Advanced reporting content -->
    </div>
@endfeature

@feature('api_access')
    <a href="{{ route('tenant.api.keys') }}">API Keys</a>
@else
    <span class="text-muted">API Access (Premium Feature)</span>
@endfeature
```

### @subscriptionActive

Check if subscription is active:

```blade
@subscriptionActive
    <div class="alert alert-success">Your subscription is active!</div>
@else
    <div class="alert alert-danger">Please renew your subscription.</div>
@endsubscriptionActive
```

### @subscriptionExpired

Check if subscription is expired:

```blade
@subscriptionExpired
    <div class="alert alert-warning">
        Your subscription has expired. Please renew to continue using all features.
        <a href="{{ route('portal.invoices.index') }}">View Invoices</a>
    </div>
@endsubscriptionExpired
```

### @gracePeriod

Check if in grace period:

```blade
@gracePeriod(3)
    <div class="alert alert-warning">
        You are in a grace period. Limited access available. Please renew soon.
    </div>
@endgracePeriod
```

### @withinLimit

Check resource limits:

```blade
@withinLimit('max_users', $currentUserCount)
    <a href="{{ route('tenant.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add User
    </a>
@else
    <button class="btn btn-secondary" disabled title="User limit reached">
        <i class="fas fa-lock"></i> Limit Reached
    </button>
@endwithinLimit
```

---

## Resource Limitations

### Configuring Limitations in Plans

Limitations are stored as JSON in the `subscription_plans` table:

```json
{
    "max_users": 5,
    "max_properties": 1,
    "max_bookings_per_month": 50,
    "max_amenities": 10,
    "max_storage_gb": 5
}
```

**Special Values**:
- `-1` = Unlimited
- `null` = Unlimited
- `0` = Feature disabled

### Controller Validation Example

```php
public function store(Request $request)
{
    $tenant = tenant();
    $currentUserCount = User::count();
    
    // Check limit before creating
    if (!$tenant->isWithinLimit('max_users', $currentUserCount)) {
        return redirect()->back()
            ->with('error', 'You have reached your plan\'s user limit. Please upgrade.');
    }
    
    // Get remaining for display
    $remaining = $tenant->getRemainingLimit('max_users', $currentUserCount);
    
    // Create user
    $user = User::create($request->validated());
    
    return redirect()->route('tenant.users.index')
        ->with('success', "User created! Remaining slots: {$remaining}");
}
```

---

## Subscription Plan Configuration

### Features Array

Features are stored as a JSON array in `subscription_plans.features`:

```json
[
    "basic_booking",
    "guest_management",
    "reporting",
    "email_notifications",
    "multi_property_management",
    "advanced_reporting",
    "advanced_analytics",
    "api_access",
    "custom_branding",
    "white_label"
]
```

### Example Plan Configurations

#### Starter Plan
```json
{
    "features": ["basic_booking", "guest_management", "reporting"],
    "limitations": {
        "max_users": 2,
        "max_properties": 1,
        "max_bookings_per_month": 25,
        "max_amenities": 5,
        "max_storage_gb": 2
    }
}
```

#### Professional Plan
```json
{
    "features": [
        "basic_booking",
        "guest_management", 
        "reporting",
        "email_notifications",
        "multi_property_management",
        "advanced_reporting",
        "housekeeping_management",
        "online_payments"
    ],
    "limitations": {
        "max_users": 10,
        "max_properties": 5,
        "max_bookings_per_month": 200,
        "max_amenities": 25,
        "max_storage_gb": 20
    }
}
```

#### Enterprise Plan
```json
{
    "features": [
        "basic_booking",
        "guest_management",
        "reporting",
        "email_notifications",
        "sms_notifications",
        "multi_property_management",
        "advanced_reporting",
        "advanced_analytics",
        "housekeeping_management",
        "api_access",
        "custom_branding",
        "priority_support",
        "white_label",
        "guest_portal",
        "online_payments",
        "inventory_management",
        "task_management",
        "document_storage"
    ],
    "limitations": {
        "max_users": -1,
        "max_properties": -1,
        "max_bookings_per_month": -1,
        "max_amenities": -1,
        "max_storage_gb": 100
    }
}
```

---

## Usage Examples

### Example 1: Dashboard with Subscription Status

```blade
@extends('tenant.layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Subscription warnings --}}
    @subscriptionExpired
        <div class="alert alert-danger alert-dismissible">
            <h5><i class="icon fas fa-ban"></i> Subscription Expired!</h5>
            {{ tenant()->getSubscriptionStatusMessage() }}
            <a href="{{ route('portal.invoices.index') }}" class="btn btn-sm btn-light">
                View Invoices
            </a>
        </div>
    @endsubscriptionExpired

    @gracePeriod(3)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            You are in a 3-day grace period. Please renew your subscription soon.
        </div>
    @endgracePeriod

    {{-- Feature-based content --}}
    <div class="row">
        @feature('advanced_reporting')
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Advanced Analytics</h3>
                    </div>
                    <div class="card-body">
                        <!-- Advanced reports widget -->
                    </div>
                </div>
            </div>
        @endfeature

        @feature('api_access')
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">API Integration</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('tenant.api.keys') }}" class="btn btn-primary">
                            Manage API Keys
                        </a>
                    </div>
                </div>
            </div>
        @endfeature
    </div>

    {{-- Resource usage indicators --}}
    @php
        $currentUsers = \App\Models\User::count();
        $remaining = tenant()->getRemainingLimit('max_users', $currentUsers);
    @endphp

    @if($remaining !== 'unlimited')
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            User slots: {{ $currentUsers }} used, {{ $remaining }} remaining
        </div>
    @endif
</div>
@endsection
```

### Example 2: Navigation Menu with Feature Checks

```blade
<li class="nav-item">
    <a href="{{ route('tenant.dashboard') }}" class="nav-link">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

@can('advanced-reporting')
<li class="nav-item">
    <a href="{{ route('tenant.reports.advanced') }}" class="nav-link">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>Advanced Reports</p>
    </a>
</li>
@endcan

@can('housekeeping')
<li class="nav-item">
    <a href="{{ route('tenant.housekeeping.index') }}" class="nav-link">
        <i class="nav-icon fas fa-broom"></i>
        <p>Housekeeping</p>
    </a>
</li>
@endcan

@can('api-access')
<li class="nav-item">
    <a href="{{ route('tenant.api.index') }}" class="nav-link">
        <i class="nav-icon fas fa-code"></i>
        <p>API Settings</p>
    </a>
</li>
@endcan
```

### Example 3: User Creation with Limit Check

**Controller**:
```php
public function create()
{
    $tenant = tenant();
    $currentUserCount = \App\Models\User::count();
    
    // Check if within limit
    if (!$tenant->isWithinLimit('max_users', $currentUserCount)) {
        return redirect()->route('tenant.users.index')
            ->with('error', 'You have reached your plan\'s user limit. Please upgrade your subscription.');
    }
    
    $remaining = $tenant->getRemainingLimit('max_users', $currentUserCount);
    
    return view('tenant.users.create', compact('remaining'));
}
```

**View**:
```blade
@extends('tenant.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New User</h3>
            @if($remaining !== 'unlimited')
                <span class="badge badge-info float-right">
                    {{ $remaining }} slots remaining
                </span>
            @endif
        </div>
        <div class="card-body">
            <!-- User creation form -->
        </div>
    </div>
</div>
@endsection
```

### Example 4: API Controller with Feature Check

```php
namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingApiController extends Controller
{
    public function __construct()
    {
        // Check API access feature
        $this->middleware(function ($request, $next) {
            if (Gate::denies('api-access')) {
                return response()->json([
                    'error' => 'API access not available in your plan',
                    'message' => 'Please upgrade to a plan that includes API access'
                ], 403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // API endpoint logic
    }
}
```

---

## Testing Subscription Enforcement

### Test Scenarios

1. **Expired Subscription**:
   - Update subscription `end_date` to past date
   - Visit tenant dashboard
   - Should see grace period warning or redirect

2. **Feature Access**:
   - Remove feature from plan's features array
   - Try accessing feature-protected route
   - Should receive 403 or redirect back with error

3. **Resource Limits**:
   - Set `max_users: 2` in plan limitations
   - Create 2 users
   - Try creating 3rd user
   - Should be blocked with limit error

4. **Grace Period**:
   - Set subscription end_date to 1 day ago
   - Access should work with warnings
   - Set end_date to 4 days ago
   - Access should be blocked (outside grace period)

---

## Common Feature Names

Standardized feature names for consistency:

- `basic_booking`
- `guest_management`
- `reporting`
- `advanced_reporting`
- `advanced_analytics`
- `multi_property_management`
- `housekeeping_management`
- `email_notifications`
- `sms_notifications`
- `api_access`
- `custom_branding`
- `priority_support`
- `white_label`
- `guest_portal`
- `online_payments`
- `inventory_management`
- `task_management`
- `document_storage`

---

## Troubleshooting

### Middleware Not Working

**Check**:
1. Middleware registered in `bootstrap/app.php`
2. Middleware applied to routes
3. Tenant context initialized (`tenant()` returns object)

### Gates Always Deny

**Check**:
1. `AuthServiceProvider` registered in `bootstrap/providers.php`
2. Subscription plan has features array populated
3. Feature name matches exactly (case-sensitive)

### Blade Directives Not Working

**Check**:
1. `AppServiceProvider` has blade directives registered
2. Cache cleared: `php artisan view:clear`
3. Tenant context available in view

### Limits Not Enforcing

**Check**:
1. Plan limitations JSON is valid
2. `CheckResourceLimit` middleware applied to create/store routes
3. Current count calculation in middleware matches your data model

---

## Next Steps

1. **Customize Error Messages**: Update middleware to show plan-specific upgrade messages
2. **Add Usage Analytics**: Track feature usage for recommendations
3. **Implement Soft Limits**: Warn before hitting hard limits
4. **Create Admin Dashboard**: Show tenant subscription usage statistics
5. **Add Webhooks**: Notify when tenants hit limits or subscription expires

