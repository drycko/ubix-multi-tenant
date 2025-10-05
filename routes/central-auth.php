<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\Auth\CentralLoginController;
use App\Http\Controllers\Central\DashboardController;

/*
|--------------------------------------------------------------------------
| Central Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle authentication for the central administration portal.
| They are separate from tenant authentication to prevent conflicts.
|
*/

// Central Authentication Routes
Route::middleware(['web'])->group(function () {
    
    // Login Routes
    Route::get('central/login', [CentralLoginController::class, 'showLoginForm'])->name('central.login');
    Route::post('central/login', [CentralLoginController::class, 'login']);
    Route::post('central/logout', [CentralLoginController::class, 'logout'])->name('central.logout');
    
    // Password Reset Routes (if needed in the future)
    // Route::get('central/password/reset', [CentralPasswordResetController::class, 'showLinkRequestForm'])->name('central.password.request');
    // Route::post('central/password/email', [CentralPasswordResetController::class, 'sendResetLinkEmail'])->name('central.password.email');
    // Route::get('central/password/reset/{token}', [CentralPasswordResetController::class, 'showResetForm'])->name('central.password.reset');
    // Route::post('central/password/reset', [CentralPasswordResetController::class, 'reset'])->name('central.password.update');
    
});