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
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\PropertyController;
use App\Http\Controllers\Tenant\RoomTypeController;
use App\Http\Controllers\Tenant\PackageController;
use App\Http\Controllers\Tenant\BookingInvoiceController;
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

    // Public routes
    Route::get('/', function () {
        return redirect()->route('tenant.dashboard');
    })->name('tenant.home');

    // Protected routes with property context
    Route::middleware(['auth:tenant', 'property.selector'])->group(function () {
        // Dashboard - accessible to all authenticated users
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        Route::get('/stats', [DashboardController::class, 'stats'])->name('tenant.stats');

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

        // Settings - accessible to authorized users
        Route::get('settings', [SettingController::class, 'index'])->name('tenant.settings');
        Route::put('settings', [SettingController::class, 'update'])->name('tenant.settings.update');

        // Property-specific routes with additional access control
        Route::middleware(['property.access'])->group(function () {
            // Bookings - property-specific
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
            Route::get('/bookings/import', [BookingController::class, 'importBookings'])->name('tenant.bookings.import');
            Route::post('/bookings/import', [BookingController::class, 'import'])->name('tenant.bookings.import.post');

            Route::post('bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('tenant.bookings.check-in');
            Route::post('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('tenant.bookings.check-out');
            Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('tenant.bookings.invoice');
            Route::get('/bookings/{booking}/download', [BookingController::class, 'downloadRoomInfo'])->name('tenant.bookings.download-room-info');
            Route::get('/bookings/{booking}/send', [BookingController::class, 'sendRoomInfo'])->name('tenant.bookings.send-room-info');
            Route::get('calendar', [BookingController::class, 'calendar'])->name('tenant.bookings.calendar');

            // booking invoices - property-specific
            Route::get('/booking-invoices', [BookingInvoiceController::class, 'index'])->name('tenant.booking-invoices.index');
            Route::get('/booking-invoices/{bookingInvoice}', [BookingInvoiceController::class, 'show'])->name('tenant.booking-invoices.show');

            Route::get('rooms/{room}/bookings', [RoomController::class, 'bookings'])->name('tenant.rooms.bookings');
            Route::get('rooms/{room}/invoices', [RoomController::class, 'invoices'])->name('tenant.rooms.invoices');
            Route::get('rooms/{room}/payments', [RoomController::class, 'payments'])->name('tenant.rooms.payments');
            // Rooms - property-specific
            // Import routes must come BEFORE resource routes to avoid conflicts
            Route::get('rooms/import', [RoomController::class, 'importRooms'])->name('tenant.rooms.import');
            Route::post('rooms/import', [RoomController::class, 'import'])->name('tenant.rooms.import.store');
            Route::get('rooms/template', [RoomController::class, 'template'])->name('tenant.rooms.template');
            
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
            Route::get('rooms/{room}/availability', [RoomController::class, 'availability'])->name('tenant.rooms.availability');
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
            Route::get('guests/{guest}/bookings', [GuestController::class, 'bookings'])->name('tenant.guests.bookings');
            Route::get('guests/{guest}/invoices', [GuestController::class, 'invoices'])->name('tenant.guests.invoices');
            Route::get('guests/{guest}/payments', [GuestController::class, 'payments'])->name('tenant.guests.payments');

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
            Route::post('guest-clubs/{guestClub}/toggle-status', [GuestClubController::class, 'toggleStatus'])->name('tenant.guest-clubs.toggle-status');
            Route::get('guest-clubs/{guestClub}/guests', [GuestClubController::class, 'guests'])->name('tenant.guest-clubs.guests');
            Route::post('/guest-clubs/{guestClub}/clone', [GuestClubController::class, 'clone'])->name('tenant.guest-clubs.clone');

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
        });

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
    });
});
