<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\RoomController;
use App\Http\Controllers\Tenant\RoomRateController;
use App\Http\Controllers\Tenant\RoomAmenityController;
use App\Http\Controllers\Tenant\GuestController;
use App\Http\Controllers\Tenant\GuestClubController;
use App\Http\Controllers\Tenant\TaxController;
use App\Http\Controllers\Tenant\PropertyController;
use App\Http\Controllers\Tenant\RoomTypeController;
use App\Http\Controllers\Tenant\PackageController;
use App\Http\Controllers\Tenant\BookingInvoiceController;
use App\Http\Controllers\Tenant\InvoicePaymentController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\RoleController;
use App\Http\Controllers\Tenant\PermissionController;
use App\Http\Controllers\Tenant\RoleAssignmentController;
// Housekeeping controllers
use App\Http\Controllers\Tenant\HousekeepingController;
use App\Http\Controllers\Tenant\RoomStatusController;
use App\Http\Controllers\Tenant\MaintenanceController;
use App\Http\Controllers\Tenant\CleaningScheduleController;
// Settings controller
use App\Http\Controllers\Tenant\TenantSettingController;
// guest portal controllers
use App\Http\Controllers\Tenant\GuestPortalController;
use App\Http\Controllers\Tenant\SelfCheckInController;
use App\Http\Controllers\Tenant\GuestRequestController;

// tenancy controllers
use App\Http\Controllers\Tenant\TenantUserActivityController;
use App\Http\Controllers\Tenant\TenantUserNotificationController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Tenant storage route - serve tenant files
    Route::get('/storage/{path}', function ($path) {
        $tenantId = tenant('id');
        $filePath = storage_path("app/public/{$path}");
        
        // Debug: Log the paths for troubleshooting
        // \Log::info('Tenant Storage Route Debug', [
        //     'tenant_id' => $tenantId,
        //     'requested_path' => $path,
        //     'full_file_path' => $filePath,
        //     'file_exists' => file_exists($filePath)
        // ]);
        
        if (!file_exists($filePath)) {
            abort(404, "File not found: {$filePath}");
        }
        
        $mimeType = mime_content_type($filePath);
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
        ]);
    })->where('path', '.*')->name('tenant.storage');

    // Tenant authentication routes
    require __DIR__.'/tenant-auth.php';

    // Tenant Payment routes
    require __DIR__.'/tenant-payment.php';

    // Public routes
    Route::get('/', function () {
        return redirect()->route('tenant.guest-portal.index');
    })->name('tenant.guest-portal.home');
    Route::get('booking-invoices/{bookingInvoice}/guest', [BookingInvoiceController::class, 'publicView'])->name('tenant.booking-invoices.public-view');

    // landing page
    Route::get('/index', [GuestPortalController::class, 'index'])->name('tenant.guest-portal.index');
    Route::post('/index', [GuestPortalController::class, 'landingSearch'])->name('tenant.guest-portal.index.post');

    // this exposes the error page for guest portal
   Route::get('/error/{errorCode}', [GuestPortalController::class, 'showErrorPage'])->name('tenant.guest-portal.error');
    
    Route::prefix('/guest')->group(function () {
        Route::get('/booking', [GuestPortalController::class, 'showPackageBookingForm'])->name('tenant.guest-portal.booking');
        // Route::post('/booking', [GuestPortalController::class, 'showBookingForm'])->name('tenant.guest-portal.booking.search');
        Route::post('/booking/submit', [GuestPortalController::class, 'book'])->name('tenant.guest-portal.booking.store');
        Route::get('/booking/select-package', [GuestPortalController::class, 'showPackageSelection'])->name('tenant.guest-portal.booking.select-package');
        // Route::get('/booking/packages/{package}', [GuestPortalController::class, 'showPackageBookingForm'])->name('tenant.guest-portal.package-booking');
        Route::post('/booking/packages', [GuestPortalController::class, 'bookPackage'])->name('tenant.guest-portal.package-booking.submit');
        Route::get('/login', [GuestPortalController::class, 'showLoginForm'])->name('tenant.guest-portal.login');
        Route::post('/send-login-link', [GuestPortalController::class, 'sendLoginLink'])->name('tenant.guest-portal.send-login-link');
        Route::get('/magic-login/{guest}', [GuestPortalController::class, 'magicLogin'])->name('tenant.guest-portal.magic-login')->middleware('signed');
        Route::post('/logout', [GuestPortalController::class, 'logout'])->name('tenant.guest-portal.logout');

        Route::prefix('/portal')->middleware('guest.portal')->group(function () {
            Route::get('/', [GuestPortalController::class, 'dashboard'])->name('tenant.guest-portal.dashboard');
            Route::get('/checkin', [SelfCheckInController::class, 'index'])->name('tenant.guest-portal.checkin');
            Route::post('/checkin/{booking}', [SelfCheckInController::class, 'checkIn'])->name('tenant.guest-portal.checkin.submit');
            Route::post('/checkout/{booking}', [SelfCheckInController::class, 'checkOut'])->name('tenant.guest-portal.checkout.submit');
            Route::get('/requests', [GuestRequestController::class, 'index'])->name('tenant.guest-portal.requests');
            Route::post('/requests', [GuestRequestController::class, 'store'])->name('tenant.guest-portal.requests.store');
            Route::post('/feedback', [GuestRequestController::class, 'storeFeedback'])->name('tenant.guest-portal.feedback.store');
            Route::get('/keys', [DigitalKeyController::class, 'index'])->name('tenant.guest-portal.keys');
            Route::post('/keys/{key}/deactivate', [DigitalKeyController::class, 'deactivate'])->name('tenant.guest-portal.keys.deactivate');
        });
    });

    // can we redirect page not found to 404 error page to custom
    Route::fallback(function () {
        // this expects 1 argument for error code
        return redirect()->route('tenant.guest-portal.error', ['errorCode' => 404])->with('error', 'The page you are looking for does not exist.');
    });

    // settings route group
    Route::prefix('/t/settings')->middleware('auth:tenant')->name('tenant.settings.')->group(function () {
        Route::get('/', [TenantSettingController::class, 'index'])->name('index');
        Route::get('/payfast', [TenantSettingController::class, 'editPayfast'])->name('payfast.edit');
        Route::post('/payfast', [TenantSettingController::class, 'updatePayfast'])->name('payfast.update');
        // PayGate routes
        Route::get('/paygate', [TenantSettingController::class, 'editPaygate'])->name('paygate.edit');
        Route::post('/paygate', [TenantSettingController::class, 'updatePaygate'])->name('paygate.update');
    });

    // Protected routes with property context (prefixed 't' for tenant)
    Route::prefix('/t')->middleware(['auth:tenant', 'must.change.password', 'property.selector'])->group(function () {
        // redirect to dashboard
        Route::get('/', function () {
            return redirect()->route('tenant.dashboard');
        });
        // error route
        Route::get('/error', [DashboardController::class, 'error'])->name('tenant.error');
        // can we redirect page not found to 404 error page to custom
        Route::fallback(function () {
            return redirect()->route('tenant.error')->with('error', 'The page you are looking for does not exist.');
        });
        // Dashboard - accessible to all authenticated users
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('tenant.stats');
        Route::get('/knowledge-base', [DashboardController::class, 'knowledgeBase'])->name('tenant.knowledge-base');

        // User Activities - user-specific, no property restriction
        Route::get('activities', [TenantUserActivityController::class, 'index'])->name('tenant.activities.index');
        Route::get('activities/{activity}', [TenantUserActivityController::class, 'show'])->name('tenant.activities.show');
        Route::post('activities/mark-as-read', [TenantUserActivityController::class, 'markAsRead'])->name('tenant.activities.mark-as-read');
        Route::post('activities/mark-all-as-read', [TenantUserActivityController::class, 'markAllAsRead'])->name('tenant.activities.mark-all-as-read');
        Route::delete('activities/clear-all', [TenantUserActivityController::class, 'clearAll'])->name('tenant.activities.clear-all');

        // User Notifications - user-specific, no property restriction
        Route::get('notifications', [TenantUserNotificationController::class, 'index'])->name('tenant.notifications.index');
        Route::get('notifications/{notification}', [TenantUserNotificationController::class, 'show'])->name('tenant.notifications.show');
        Route::post('notifications/mark-as-read', [TenantUserNotificationController::class, 'markAsRead'])->name('tenant.notifications.mark-as-read');
        Route::post('notifications/mark-all-as-read', [TenantUserNotificationController::class, 'markAllAsRead'])->name('tenant.notifications.mark-all-as-read');
        Route::delete('notifications/clear-all', [TenantUserNotificationController::class, 'clearAll'])->name('tenant.notifications.clear-all');

        // Reports - comprehensive reporting system
        Route::prefix('reports')->name('tenant.reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/advanced', [ReportController::class, 'advanced'])->name('advanced');
            Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
            Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
            Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
            Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
        });

        Route::get('/bookings/import', [BookingController::class, 'importBookings'])->name('tenant.bookings.import');
        Route::post('/bookings/import', [BookingController::class, 'import'])->name('tenant.bookings.import.post');
        Route::get('/bookings/export', [BookingController::class, 'export'])->name('tenant.bookings.export');
        Route::get('/bookings/template', [BookingController::class, 'template'])->name('tenant.bookings.template');
        // Property-specific routes with additional access control
        Route::middleware(['property.access'])->group(function () {
            // Bookings - property-specific
            
            Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('tenant.bookings.invoice');
            Route::get('/bookings/{booking}/download', [BookingController::class, 'downloadRoomInfo'])->name('tenant.bookings.download-room-info');
            Route::get('/bookings/{booking}/send', [BookingController::class, 'sendRoomInfo'])->name('tenant.bookings.send-room-info');
            Route::get('calendar', [BookingController::class, 'calendar'])->name('tenant.bookings.calendar');

            Route::resource('bookings', BookingController::class)->names([
                'index' => 'tenant.bookings.index',
                'create' => 'tenant.bookings.create',
                'store' => 'tenant.bookings.store',
                'show' => 'tenant.bookings.show',
                'edit' => 'tenant.bookings.edit',
                'update' => 'tenant.bookings.update',
                'destroy' => 'tenant.bookings.destroy',
            ]);

            Route::post('/bookings/package', [BookingController::class, 'storeWithPackage'])->name('tenant.bookings.store.package');
            Route::post('/bookings/{booking}/clone', [BookingController::class, 'clone'])->name('tenant.bookings.clone');
            Route::post('/bookings/{booking}/toggle-status', [BookingController::class, 'toggleStatus'])->name('tenant.bookings.toggle-status');

            Route::post('bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('tenant.bookings.check-in');
            Route::post('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('tenant.bookings.check-out');

            // booking invoices - property-specific
            Route::get('/booking-invoices', [BookingInvoiceController::class, 'index'])->name('tenant.booking-invoices.index');
            Route::get('/booking-invoices/{bookingInvoice}', [BookingInvoiceController::class, 'show'])->name('tenant.booking-invoices.show');
            Route::get('/booking-invoices/{bookingInvoice}/download', [BookingInvoiceController::class, 'download'])->name('tenant.booking-invoices.download');
            Route::get('/booking-invoices/{bookingInvoice}/print', [BookingInvoiceController::class, 'print'])->name('tenant.booking-invoices.print');

            // Send invoice via email
            Route::post('/booking-invoices/{bookingInvoice}/send-email', [BookingInvoiceController::class, 'sendEmail'])->name('tenant.booking-invoices.send-email');

            // invoice payments - property-specific
            Route::get('/booking-invoices/{bookingInvoice}/payments', [InvoicePaymentController::class, 'index'])->name('tenant.invoice-payments.index');
            Route::get('/booking-invoices/{bookingInvoice}/payments/create', [InvoicePaymentController::class, 'create'])->name('tenant.invoice-payments.create');
            Route::post('/booking-invoices/{bookingInvoice}/payments', [InvoicePaymentController::class, 'store'])->name('tenant.invoice-payments.store');
            Route::get('/booking-invoices/{bookingInvoice}/payments/{invoicePayment}', [InvoicePaymentController::class, 'show'])->name('tenant.invoice-payments.show');
            Route::get('/booking-invoices/{bookingInvoice}/payments/{invoicePayment}/edit', [InvoicePaymentController::class, 'edit'])->name('tenant.invoice-payments.edit');
            Route::put('/booking-invoices/{bookingInvoice}/payments/{invoicePayment}', [InvoicePaymentController::class, 'update'])->name('tenant.invoice-payments.update');
            Route::delete('/booking-invoices/{bookingInvoice}/payments/{invoicePayment}', [InvoicePaymentController::class, 'destroy'])->name('tenant.invoice-payments.destroy');
            Route::get('/booking-invoices/{bookingInvoice}/payment-form', [InvoicePaymentController::class, 'getPaymentForm'])->name('tenant.invoice-payments.form');

            Route::get('rooms/{room}/bookings', [RoomController::class, 'bookings'])->name('tenant.rooms.bookings');
            Route::get('rooms/{room}/invoices', [RoomController::class, 'invoices'])->name('tenant.rooms.invoices');
            Route::get('rooms/{room}/payments', [RoomController::class, 'payments'])->name('tenant.rooms.payments');
            // Rooms - property-specific
            // Import routes must come BEFORE resource routes to avoid conflicts
            Route::get('rooms/import', [RoomController::class, 'importRooms'])->name('tenant.rooms.import');
            Route::post('rooms/import', [RoomController::class, 'import'])->name('tenant.rooms.import.store');
            Route::get('rooms/template', [RoomController::class, 'template'])->name('tenant.rooms.template');

            Route::get('rooms/{room}/availability', [RoomController::class, 'availability'])->name('tenant.rooms.availability');
            // We need to get the rooms available with AJAX based on the selected dates
            Route::get('rooms/available', [RoomController::class, 'available'])->name('tenant.rooms.available');
            
            Route::resource('rooms', RoomController::class)->names([
                'index' => 'tenant.rooms.index',
                'create' => 'tenant.rooms.create',
                'store' => 'tenant.rooms.store',
                'show' => 'tenant.rooms.show',
                'edit' => 'tenant.rooms.edit',
                'update' => 'tenant.rooms.update',
                'destroy' => 'tenant.rooms.destroy',
            ]);
            Route::post('rooms/{room}/toggle-status', [RoomController::class, 'toggleStatus'])->name('tenant.rooms.toggle-status');
            Route::post('/rooms/{room}/clone', [RoomController::class, 'clone'])->name('tenant.rooms.clone');

            // Room Types - property-specific
            Route::resource('room-types', RoomTypeController::class)->names([
                'index' => 'tenant.room-types.index',
                'create' => 'tenant.room-types.create',
                'store' => 'tenant.room-types.store',
                'show' => 'tenant.room-types.show',
                'edit' => 'tenant.room-types.edit',
                'update' => 'tenant.room-types.update',
                'destroy' => 'tenant.room-types.destroy',
            ]);
            Route::post('room-types/{roomType}/toggle-status', [RoomTypeController::class, 'toggleStatus'])->name('tenant.room-types.toggle-status');
            Route::get('room-types/{roomType}/rooms', [RoomTypeController::class, 'rooms'])->name('tenant.room-types.rooms');
            Route::post('/room-types/{roomType}/clone', [RoomTypeController::class, 'clone'])->name('tenant.room-types.clone');

            // Rates - property-specific
            // Import routes must come BEFORE resource routes to avoid conflicts
            Route::get('room-rates/import', [RoomRateController::class, 'importRates'])->name('tenant.room-rates.import');
            Route::post('room-rates/import', [RoomRateController::class, 'import'])->name('tenant.room-rates.import.post');
            
            Route::post('room-rates/{roomRate}/toggle-status', [RoomRateController::class, 'toggleStatus'])->name('tenant.room-rates.toggle-status');
            Route::get('room-rates/{roomRate}/rooms', [RoomRateController::class, 'rooms'])->name('tenant.room-rates.rooms');
            Route::post('/room-rates/{roomRate}/clone', [RoomRateController::class, 'clone'])->name('tenant.room-rates.clone');
            
            Route::resource('room-rates', RoomRateController::class)->names([
                'index' => 'tenant.room-rates.index',
                'create' => 'tenant.room-rates.create',
                'store' => 'tenant.room-rates.store',
                'show' => 'tenant.room-rates.show',
                'edit' => 'tenant.room-rates.edit',
                'update' => 'tenant.room-rates.update',
                'destroy' => 'tenant.room-rates.destroy',
            ]);

            // Room Amenities - property-specific
            Route::resource('room-amenities', RoomAmenityController::class)->names([
                'index' => 'tenant.room-amenities.index',
                'create' => 'tenant.room-amenities.create',
                'store' => 'tenant.room-amenities.store',
                'show' => 'tenant.room-amenities.show',
                'edit' => 'tenant.room-amenities.edit',
                'update' => 'tenant.room-amenities.update',
                'destroy' => 'tenant.room-amenities.destroy',
            ]);
            Route::post('/room-amenities/{roomAmenity}/clone', [RoomAmenityController::class, 'clone'])->name('tenant.room-amenities.clone');

            Route::get('guests/{guest}/bookings', [GuestController::class, 'bookings'])->name('tenant.guests.bookings');
            Route::get('guests/{guest}/invoices', [GuestController::class, 'invoices'])->name('tenant.guests.invoices');
            Route::get('guests/{guest}/payments', [GuestController::class, 'payments'])->name('tenant.guests.payments');

            // Guests - property-specific
            Route::resource('guests', GuestController::class)->names([
                'index' => 'tenant.guests.index',
                'create' => 'tenant.guests.create',
                'store' => 'tenant.guests.store',
                'show' => 'tenant.guests.show',
                'edit' => 'tenant.guests.edit',
                'update' => 'tenant.guests.update',
                'destroy' => 'tenant.guests.destroy',
            ]);
            Route::post('guests/{guest}/toggle-status', [GuestController::class, 'toggleStatus'])->name('tenant.guests.toggle-status');

            // guest clubs - property-specific
            Route::resource('guest-clubs', GuestClubController::class)->names([
                'index' => 'tenant.guest-clubs.index',
                'create' => 'tenant.guest-clubs.create',
                'store' => 'tenant.guest-clubs.store',
                'show' => 'tenant.guest-clubs.show',
                'edit' => 'tenant.guest-clubs.edit',
                'update' => 'tenant.guest-clubs.update',
                'destroy' => 'tenant.guest-clubs.destroy',
            ]);
            Route::patch('guest-clubs/{guestClub}/toggle-status', [GuestClubController::class, 'toggleStatus'])->name('tenant.guest-clubs.toggle-status');
            Route::get('guest-clubs/{guestClub}/members', [GuestClubController::class, 'members'])->name('tenant.guest-clubs.members');
            Route::post('guest-clubs/{guestClub}/add-member', [GuestClubController::class, 'addMember'])->name('tenant.guest-clubs.add-member');
            Route::post('guest-clubs/{guestClub}/change-member-status', [GuestClubController::class, 'changeMemberStatus'])->name('tenant.guest-clubs.change-member-status');
            Route::delete('guest-clubs/{guestClub}/remove-member', [GuestClubController::class, 'removeMember'])->name('tenant.guest-clubs.remove-member');
            Route::post('guest-clubs/{guestClub}/bulk-action', [GuestClubController::class, 'bulkAction'])->name('tenant.guest-clubs.bulk-action');
            Route::get('guest-clubs/{guestClub}/export-members', [GuestClubController::class, 'exportMembers'])->name('tenant.guest-clubs.export-members');

            Route::post('/guest-clubs/{guestClub}/clone', [GuestClubController::class, 'clone'])->name('tenant.guest-clubs.clone');

            // Tax Management - property-specific
            Route::resource('taxes', TaxController::class, ['as' => 'tenant']);
            Route::patch('taxes/{tax}/toggle-status', [TaxController::class, 'toggleStatus'])->name('tenant.taxes.toggle-status');

            // room packages - property-specific
            Route::get('room-packages/import', [PackageController::class, 'importPackage'])->name('tenant.room-packages.import');
            Route::post('room-packages/import', [PackageController::class, 'import'])->name('tenant.room-packages.import.store');
            Route::resource('room-packages', PackageController::class)->names([
                'index' => 'tenant.room-packages.index',
                'create' => 'tenant.room-packages.create',
                'store' => 'tenant.room-packages.store',
                'show' => 'tenant.room-packages.show',
                'edit' => 'tenant.room-packages.edit',
                'update' => 'tenant.room-packages.update',
                'destroy' => 'tenant.room-packages.destroy',
            ]);
            Route::post('room-packages/{roomPackage}/toggle-status', [PackageController::class, 'toggleStatus'])->name('tenant.room-packages.toggle-status');
            Route::post('/room-packages/{roomPackage}/clone', [PackageController::class, 'clone'])->name('tenant.room-packages.clone');

            // Refund management - property-specific
            Route::resource('refunds', \App\Http\Controllers\Tenant\RefundController::class)->names([
                'index' => 'tenant.refunds.index',
                'create' => 'tenant.refunds.create',
                'store' => 'tenant.refunds.store',
                'show' => 'tenant.refunds.show',
                'edit' => 'tenant.refunds.edit',
                'update' => 'tenant.refunds.update',
                'destroy' => 'tenant.refunds.destroy',
            ]);
        });


        // users management - super-user only routes (no property.access middleware)
        Route::get('users', [UserController::class, 'index'])->name('tenant.users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('tenant.users.create');
        Route::post('users', [UserController::class, 'store'])->name('tenant.users.store');

        Route::get('users/{user}', [UserController::class, 'show'])->name('tenant.users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('tenant.users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('tenant.users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('tenant.users.destroy');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('tenant.users.toggle-status');

        // Profile management (available to all users)
        Route::get('profile', [UserController::class, 'profile'])->name('tenant.users.profile');
        Route::put('profile', [UserController::class, 'updateProfile'])->name('tenant.users.update-profile');

        // Roles management - dedicated controller with proper permissions
        Route::resource('roles', RoleController::class)->names([
            'index' => 'tenant.roles.index',
            'create' => 'tenant.roles.create',
            'store' => 'tenant.roles.store',
            'show' => 'tenant.roles.show',
            'edit' => 'tenant.roles.edit',
            'update' => 'tenant.roles.update',
            'destroy' => 'tenant.roles.destroy',
        ]);
        Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('tenant.roles.sync-permissions');

        // Permissions management - dedicated controller with proper permissions
        Route::resource('permissions', PermissionController::class)->names([
            'index' => 'tenant.permissions.index',
            'create' => 'tenant.permissions.create',
            'store' => 'tenant.permissions.store',
            'show' => 'tenant.permissions.show',
            'edit' => 'tenant.permissions.edit',
            'update' => 'tenant.permissions.update',
            'destroy' => 'tenant.permissions.destroy',
        ]);
        Route::post('permissions/bulk-create', [PermissionController::class, 'bulkCreate'])->name('tenant.permissions.bulk-create');

        // Role assignments - managing user roles
        Route::get('role-assignments', [RoleAssignmentController::class, 'index'])->name('tenant.role-assignments.index');
        Route::get('role-assignments/{user}/edit', [RoleAssignmentController::class, 'edit'])->name('tenant.role-assignments.edit');
        Route::put('role-assignments/{user}', [RoleAssignmentController::class, 'update'])->name('tenant.role-assignments.update');
        Route::post('role-assignments/bulk-assign', [RoleAssignmentController::class, 'bulkAssign'])->name('tenant.role-assignments.bulk-assign');

        // Legacy role and permission routes (keep for backward compatibility)
        Route::get('old-roles', [UserController::class, 'rolesIndex'])->name('tenant.old-roles.index');
        Route::get('old-roles/create', [UserController::class, 'rolesCreate'])->name('tenant.old-roles.create');
        Route::post('old-roles', [UserController::class, 'rolesStore'])->name('tenant.old-roles.store');
        Route::get('old-roles/{role}/edit', [UserController::class, 'rolesEdit'])->name('tenant.old-roles.edit');
        Route::put('old-roles/{role}', [UserController::class, 'rolesUpdate'])->name('tenant.old-roles.update');
        Route::delete('old-roles/{role}', [UserController::class, 'rolesDestroy'])->name('tenant.old-roles.destroy');

        // Legacy permissions management
        Route::get('old-permissions', [UserController::class, 'permissionsIndex'])->name('tenant.old-permissions.index');
        Route::get('old-permissions/create', [UserController::class, 'permissionsCreate'])->name('tenant.old-permissions.create');
        Route::post('old-permissions', [UserController::class, 'permissionsStore'])->name('tenant.old-permissions.store');
        Route::get('old-permissions/{permission}/edit', [UserController::class, 'permissionsEdit'])->name('tenant.old-permissions.edit');
        Route::put('old-permissions/{permission}', [UserController::class, 'permissionsUpdate'])->name('tenant.old-permissions.update');
        Route::delete('old-permissions/{permission}', [UserController::class, 'permissionsDestroy'])->name('tenant.old-permissions.destroy');

        // Properties management - super-user only routes (no property.access middleware)
        Route::get('properties', [PropertyController::class, 'index'])->name('tenant.properties.index');
        Route::get('properties/create', [PropertyController::class, 'create'])->name('tenant.properties.create');
        Route::post('properties', [PropertyController::class, 'store'])->name('tenant.properties.store');
        Route::get('properties/{property}', [PropertyController::class, 'show'])->name('tenant.properties.show');
        Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('tenant.properties.edit');
        Route::put('properties/{property}', [PropertyController::class, 'update'])->name('tenant.properties.update');
        Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('tenant.properties.destroy');
        Route::post('properties/{property}/toggle-status', [PropertyController::class, 'toggleStatus'])->name('tenant.properties.toggle-status');
        Route::post('properties/{property}/clone', [PropertyController::class, 'clone'])->name('tenant.properties.clone');
        
        // Property selection
        Route::get('property/select', [PropertyController::class, 'select'])->name('tenant.properties.select');
        Route::post('property/select', [PropertyController::class, 'storeSelection'])->name('tenant.properties.store-selection');

        // Housekeeping Management Routes
        Route::prefix('housekeeping')->name('tenant.housekeeping.')->group(function () {
            // Main housekeeping dashboard and coordination
            Route::get('/', [HousekeepingController::class, 'index'])->name('index');
            Route::get('/create', [HousekeepingController::class, 'create'])->name('create');
            Route::post('/', [HousekeepingController::class, 'store'])->name('store');
            Route::get('/{task}', [HousekeepingController::class, 'show'])->name('show');
            Route::get('/{task}/edit', [HousekeepingController::class, 'edit'])->name('edit');
            Route::put('/{task}', [HousekeepingController::class, 'update'])->name('update');
            Route::delete('/{task}', [HousekeepingController::class, 'destroy'])->name('destroy');
            
            // Task status management
            Route::post('/{task}/start', [HousekeepingController::class, 'start'])->name('start');
            Route::post('/{task}/complete', [HousekeepingController::class, 'complete'])->name('complete');
            Route::post('/{task}/cancel', [HousekeepingController::class, 'cancel'])->name('cancel');
            Route::post('/{task}/assign', [HousekeepingController::class, 'assign'])->name('assign');
            
            // Bulk operations and reports
            Route::get('/rooms', [HousekeepingController::class, 'rooms'])->name('rooms');
            Route::get('/daily-report', [HousekeepingController::class, 'dailyReport'])->name('daily-report');
            Route::post('/assign-tasks', [HousekeepingController::class, 'assignTasks'])->name('assign-tasks');
            Route::post('/bulk-update-status', [HousekeepingController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        });

        // Room Status Management Routes
        Route::prefix('room-status')->name('tenant.room-status.')->group(function () {
            Route::get('/', [RoomStatusController::class, 'index'])->name('index');
            Route::get('/initialize', [RoomStatusController::class, 'initializeStatuses'])->name('initialize');
            Route::post('/bulk-assign', [RoomStatusController::class, 'bulkAssign'])->name('bulk-assign');
            Route::get('/{roomStatus}', [RoomStatusController::class, 'show'])->name('show');
            Route::put('/{roomStatus}', [RoomStatusController::class, 'update'])->name('update');
            Route::post('/{roomStatus}/assign', [RoomStatusController::class, 'assign'])->name('assign');
            Route::post('/{roomStatus}/start', [RoomStatusController::class, 'start'])->name('start');
            Route::post('/{roomStatus}/complete', [RoomStatusController::class, 'complete'])->name('complete');
            Route::post('/{roomStatus}/inspect', [RoomStatusController::class, 'inspect'])->name('inspect');
        });

        // Maintenance Management Routes
        Route::prefix('maintenance')->name('tenant.maintenance.')->group(function () {
            Route::get('/', [MaintenanceController::class, 'index'])->name('index');
            Route::get('/dashboard', [MaintenanceController::class, 'dashboard'])->name('dashboard');
            Route::get('/tasks', [MaintenanceController::class, 'tasks'])->name('tasks');
            Route::post('/tasks', [MaintenanceController::class, 'createTask'])->name('create-task');
            Route::get('/tasks/{task}', [MaintenanceController::class, 'getTask'])->name('get-task');
            Route::post('/tasks/{task}', [MaintenanceController::class, 'updateTask'])->name('update-task');
            Route::post('/tasks/{task}/update-status', [MaintenanceController::class, 'updateTaskStatus'])->name('update-task-status');
            Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
            Route::post('/', [MaintenanceController::class, 'store'])->name('store');
            Route::get('/{maintenance}', [MaintenanceController::class, 'show'])->name('show');
            Route::get('/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('edit');
            Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('update');
            Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('destroy');
            Route::post('/{maintenance}/assign', [MaintenanceController::class, 'assign'])->name('assign');
            Route::post('/{maintenance}/start', [MaintenanceController::class, 'start'])->name('start');
            Route::post('/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('complete');
            Route::post('/{maintenance}/cancel', [MaintenanceController::class, 'cancel'])->name('cancel');
            Route::post('/{maintenance}/hold', [MaintenanceController::class, 'hold'])->name('hold');
            Route::patch('/{maintenance}/update-status', [MaintenanceController::class, 'updateStatus'])->name('update-status');
            Route::post('/{maintenance}/add-work-log', [MaintenanceController::class, 'addWorkLog'])->name('add-work-log');
            Route::get('/{maintenance}/print', [MaintenanceController::class, 'print'])->name('print');
        });

        // Cleaning Schedule & Checklists Management Routes
        Route::prefix('cleaning-schedule')->name('tenant.cleaning-schedule.')->group(function () {
            Route::get('/', [CleaningScheduleController::class, 'index'])->name('index');
            Route::get('/calendar', [CleaningScheduleController::class, 'calendar'])->name('calendar');
            Route::get('/create', [CleaningScheduleController::class, 'create'])->name('create');
            Route::post('/', [CleaningScheduleController::class, 'store'])->name('store');
            Route::get('/{checklist}', [CleaningScheduleController::class, 'show'])->name('show');
            Route::get('/{checklist}/edit', [CleaningScheduleController::class, 'edit'])->name('edit');
            Route::put('/{checklist}', [CleaningScheduleController::class, 'update'])->name('update');
            Route::delete('/{checklist}', [CleaningScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/generate-schedule', [CleaningScheduleController::class, 'generateSchedule'])->name('generate');
            Route::post('/update-order', [CleaningScheduleController::class, 'updateOrder'])->name('update-order');
            Route::post('/{checklist}/duplicate', [CleaningScheduleController::class, 'duplicate'])->name('duplicate');
            Route::post('/load-defaults', [CleaningScheduleController::class, 'loadDefaults'])->name('load-defaults');
            Route::get('/{checklist}/print', [CleaningScheduleController::class, 'print'])->name('print');
        });
    });
});
