<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
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
    // Tenant authentication routes
    require __DIR__.'/auth.php';

    // Public routes
    Route::get('/', function () {
        return redirect()->route('tenant.dashboard');
    })->name('tenant.home');

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

        // Bookings
        Route::resource('bookings', BookingController::class)->names([
            'index' => 'tenant.bookings.index',
            'create' => 'tenant.bookings.create',
            'store' => 'tenant.bookings.store',
            'show' => 'tenant.bookings.show',
            'edit' => 'tenant.bookings.edit',
            'update' => 'tenant.bookings.update',
            'destroy' => 'tenant.bookings.destroy',
        ]);
        Route::post('bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('tenant.bookings.check-in');
        Route::post('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('tenant.bookings.check-out');
        Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('tenant.bookings.invoice');
        Route::get('calendar', [BookingController::class, 'calendar'])->name('tenant.bookings.calendar');

        // Rooms
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

        // Rates
        Route::resource('rates', RateController::class)->names([
            'index' => 'tenant.rates.index',
            'create' => 'tenant.rates.create',
            'store' => 'tenant.rates.store',
            'show' => 'tenant.rates.show',
            'edit' => 'tenant.rates.edit',
            'update' => 'tenant.rates.update',
            'destroy' => 'tenant.rates.destroy',
        ]);
        Route::post('rates/{rate}/toggle-status', [RateController::class, 'toggleStatus'])->name('tenant.rates.toggle-status');

        // Guests
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

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('tenant.settings');
        Route::put('settings', [SettingController::class, 'update'])->name('tenant.settings.update');
    });
});

// Route::middleware([
//     'web',
//     InitializeTenancyByDomain::class,
//     PreventAccessFromCentralDomains::class,
// ])->group(function () {
//     // Tenant authentication routes
//     require __DIR__.'/auth.php';

//     // Public routes
//     Route::get('/', function () {
//         return view('tenant.welcome');
//     })->name('tenant.home');

//     Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
//     Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');

//     // Protected routes
//     Route::middleware(['auth'])->group(function () {
//         Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
//         Route::resource('bookings', BookingController::class);
//         Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//         Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     });
// });
