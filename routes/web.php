<?php

use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (for central admin)
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/central/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/central/dashboard', [DashboardController::class, 'index'])->name('central.dashboard');

    Route::get('central/stats', [DashboardController::class, 'stats'])->name('central.stats');

    Route::get('/central/tenants', [TenantController::class, 'index'])->name('central.tenants.index');
    Route::get('/central/tenants/create', [TenantController::class, 'create'])->name('central.tenants.create');
    Route::post('/central/tenants', [TenantController::class, 'store'])->name('central.tenants.store');
    Route::get('/central/tenants/{tenant}', [TenantController::class, 'show'])->name('central.tenants.show');
    Route::get('/central/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('central.tenants.edit');
    Route::put('/central/tenants/{tenant}', [TenantController::class, 'update'])->name('central.tenants.update');
    Route::delete('/central/tenants/{tenant}', [TenantController::class, 'destroy'])->name('central.tenants.destroy');
    Route::resource('tenants', TenantController::class);

});