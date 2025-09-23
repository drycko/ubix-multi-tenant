<?php

use App\Http\Controllers\Api\PackageController;
use Illuminate\Support\Facades\Route;

// Public company API key access for packages
Route::get('/packages', [PackageController::class, 'index']);

