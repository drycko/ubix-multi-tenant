<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\PaymentGateways\PayfastGatewayController;
use App\Http\Controllers\Tenant\PaymentGateways\PaygateGatewayController;
use App\Http\Controllers\Tenant\TenantSettingController;

// All PayFast payment routes are public for guest-facing payments
Route::get('tg/payfast/return', [PayfastGatewayController::class, 'handleReturn'])->name('tenant.payfast.return');
Route::get('tg/payfast/cancel', [PayfastGatewayController::class, 'handleCancel'])->name('tenant.payfast.cancel');
Route::post('tg/payfast/notify', [PayfastGatewayController::class, 'handleNotify'])->name('tenant.payfast.notify');

// All PayGate payment routes are public for guest-facing payments
Route::post('tg/paygate/initiate/{bookingInvoiceId}', [PaygateGatewayController::class, 'initiatePayment'])->name('tenant.paygate.initiate');
Route::post('tg/paygate/return', [PaygateGatewayController::class, 'handleReturn'])->name('tenant.paygate.return');
Route::post('tg/paygate/cancel', [PaygateGatewayController::class, 'handleCancel'])->name('tenant.paygate.cancel');
Route::post('tg/paygate/notify', [PaygateGatewayController::class, 'handleNotify'])->name('tenant.paygate.notify');