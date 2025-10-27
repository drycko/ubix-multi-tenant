<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\PaymentGateways\PayfastGatewayController;
use App\Http\Controllers\Tenant\TenantSettingController;

// All PayFast payment routes are public for guest-facing payments
Route::post('tenant/payfast/initiate', [PayfastGatewayController::class, 'initiate'])->name('tenant.payfast.initiate');
Route::get('tenant/payfast/return', [PayfastGatewayController::class, 'handleReturn'])->name('tenant.payfast.return');
Route::get('tenant/payfast/cancel', [PayfastGatewayController::class, 'handleCancel'])->name('tenant.payfast.cancel');
Route::post('tenant/payfast/notify', [PayfastGatewayController::class, 'handleNotify'])->name('tenant.payfast.notify');
