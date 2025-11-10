<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\UserController;


// Central admin user management routes
Route::get('/central/users', [UserController::class, 'index'])->name('central.users.index');
Route::get('/central/users/create', [UserController::class, 'create'])->name('central.users.create');
Route::post('/central/users', [UserController::class, 'store'])->name('central.users.store');
Route::get('/central/users/{user}', [UserController::class, 'show'])->name('central.users.show');
Route::get('/central/users/{user}/edit', [UserController::class, 'edit'])->name('central.users.edit');
Route::put('/central/users/{user}', [UserController::class, 'update'])->name('central.users.update');
Route::delete('/central/users/{user}', [UserController::class, 'destroy'])->name('central.users.destroy');
