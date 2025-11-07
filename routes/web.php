<?php

use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\SubscriptionController;
use App\Http\Controllers\Central\SubscriptionPlanController;
use App\Http\Controllers\Central\SubscriptionInvoiceController;
use App\Http\Controllers\Central\CentralSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (for central admin)
// require __DIR__.'/auth.php';
require __DIR__.'/central-auth.php';

// home route redirect to central dashboard
Route::get('/home', function() {
    return redirect()->route('central.dashboard');
})->middleware(['auth'])->name('home');

// base home route redirect to central dashboard
Route::get('/', function() {
    return redirect()->route('central.dashboard');
})->middleware(['auth'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/central', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/central/dashboard', [DashboardController::class, 'index'])->name('central.dashboard');
    Route::get('central/stats', [DashboardController::class, 'stats'])->name('central.stats');
    Route::get('central/knowledge-base', [DashboardController::class, 'knowledgeBase'])->name('central.knowledge-base');

    Route::get('/central/tenants', [TenantController::class, 'index'])->name('central.tenants.index');
    Route::get('/central/tenants/create', [TenantController::class, 'create'])->name('central.tenants.create');
    Route::post('/central/tenants', [TenantController::class, 'store'])->name('central.tenants.store');
    Route::get('/central/tenants/{tenant}', [TenantController::class, 'show'])->name('central.tenants.show');
    Route::get('/central/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('central.tenants.edit');
    Route::put('/central/tenants/{tenant}', [TenantController::class, 'update'])->name('central.tenants.update');
    Route::delete('/central/tenants/{tenant}', [TenantController::class, 'destroy'])->name('central.tenants.destroy');
    Route::get('/central/tenants/{tenant}/domains', [TenantController::class, 'domains'])->name('central.tenants.domains');
    Route::get('/central/tenants/{tenant}/subscriptions', [TenantController::class, 'subscriptions'])->name('central.tenants.subscriptions');
    Route::post('/central/tenants/{tenant}/switch-to-premium', [TenantController::class, 'switchToPremium'])->name('central.tenants.switch-to-premium');
    Route::get('/central/tenants/{tenant}/login-as-tenant', [TenantController::class, 'loginAsTenant'])->name('central.tenants.login-as-tenant');
    Route::get('/central/tenants/{tenant}/send-email', [TenantController::class, 'showSendEmailForm'])->name('central.tenants.show-send-email-form');
    Route::post('/central/tenants/{tenant}/send-email', [TenantController::class, 'sendEmailToTenant'])->name('central.tenants.send-email');
    // Route::post('/central/tenants/{tenant}/cancel-subscription', [TenantController::class, 'cancelSubscription'])->name('central.tenants.cancel-subscription');
    Route::resource('tenants', TenantController::class);

    // subscriptions
    Route::get('/central/subscriptions', [SubscriptionController::class, 'index'])->name('central.subscriptions.index');
    Route::get('/central/subscriptions/create', [SubscriptionController::class, 'create'])->name('central.subscriptions.create');
    Route::post('/central/subscriptions', [SubscriptionController::class, 'store'])->name('central.subscriptions.store');
    Route::patch('/central/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('central.subscriptions.cancel');
    // Route::get('/central/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('central.subscriptions.edit');
    // Route::put('/central/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('central.subscriptions.update');
    Route::delete('/central/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('central.subscriptions.destroy');
    Route::get('/central/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('central.subscriptions.show');
    Route::post('/central/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('central.subscriptions.renew');

    // invoices
    Route::get('/central/invoices', [SubscriptionInvoiceController::class, 'index'])->name('central.invoices.index');
    Route::get('/central/invoices/create', [SubscriptionInvoiceController::class, 'create'])->name('central.invoices.create');
    Route::get('/central/invoices/{invoice}', [SubscriptionInvoiceController::class, 'show'])->name('central.invoices.show');
    Route::get('/central/invoices/{invoice}/edit', [SubscriptionInvoiceController::class, 'edit'])->name('central.invoices.edit');
    Route::put('/central/invoices/{invoice}', [SubscriptionInvoiceController::class, 'update'])->name('central.invoices.update');
    Route::delete('/central/invoices/{invoice}', [SubscriptionInvoiceController::class, 'destroy'])->name('central.invoices.destroy');
    Route::post('/central/invoices/{invoice}/pay', [SubscriptionInvoiceController::class, 'payInvoiceManually'])->name('central.invoices.pay');
    Route::post('/central/invoices/{invoice}/cancel', [SubscriptionInvoiceController::class, 'cancel'])->name('central.invoices.cancel');
    Route::get('/central/invoices/{invoice}/download', [SubscriptionInvoiceController::class, 'download'])->name('central.invoices.download');
    Route::get('/central/invoices/{invoice}/print', [SubscriptionInvoiceController::class, 'print'])->name('central.invoices.print');


    // subscription plans
    Route::get('/central/plans', [SubscriptionPlanController::class, 'index'])->name('central.plans.index');
    Route::get('/central/plans/trashed', [SubscriptionPlanController::class, 'trashed'])->name('central.plans.trashed');
    Route::get('/central/plans/create', [SubscriptionPlanController::class, 'create'])->name('central.plans.create');
    Route::post('/central/plans', [SubscriptionPlanController::class, 'store'])->name('central.plans.store');
    Route::get('/central/plans/{id}', [SubscriptionPlanController::class, 'show'])->name('central.plans.show');
    Route::get('/central/plans/{id}/edit', [SubscriptionPlanController::class, 'edit'])->name('central.plans.edit');
    Route::put('/central/plans/{id}', [SubscriptionPlanController::class, 'update'])->name('central.plans.update');
    Route::post('/central/plans/{id}/soft-delete', [SubscriptionPlanController::class, 'softDelete'])->name('central.plans.soft-delete');
    Route::post('/central/plans/restore-all', [SubscriptionPlanController::class, 'restoreAll'])->name('central.plans.restore-all');
    Route::post('/central/plans/{id}/restore', [SubscriptionPlanController::class, 'restore'])->name('central.plans.restore');
    Route::delete('/central/plans/{id}', [SubscriptionPlanController::class, 'destroy'])->name('central.plans.destroy');

    // settings
    Route::get('central/settings', [CentralSettingController::class, 'index'])->name('central.settings');
    Route::put('central/settings', [CentralSettingController::class, 'update'])->name('central.settings.update');

    // users
    Route::get('/central/users', [DashboardController::class, 'users'])->name('central.users.index');
    Route::get('/central/users/create', [DashboardController::class, 'createUser'])->name('central.users.create');
    Route::post('/central/users', [DashboardController::class, 'storeUser'])->name('central.users.store');
    Route::get('/central/users/{user}/edit', [DashboardController::class, 'editUser'])->name('central.users.edit');
    Route::put('/central/users/{user}', [DashboardController::class, 'updateUser'])->name('central.users.update');
    Route::delete('/central/users/{user}', [DashboardController::class, 'destroyUser'])->name('central.users.destroy');
});