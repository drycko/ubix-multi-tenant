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
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
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
