<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\RoleAssignmentController;
use App\Http\Controllers\Central\PermissionController;
use App\Http\Controllers\Central\RoleController;


// Roles management - dedicated controller with proper permissions
Route::resource('/central/roles', RoleController::class)->names([
    'index' => 'central.roles.index',
    'create' => 'central.roles.create',
    'store' => 'central.roles.store',
    'show' => 'central.roles.show',
    'edit' => 'central.roles.edit',
    'update' => 'central.roles.update',
    'destroy' => 'central.roles.destroy',
]);
Route::post('/central/roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('central.roles.sync-permissions');

// Permissions management - dedicated controller with proper permissions
Route::resource('/central/permissions', PermissionController::class)->names([
    'index' => 'central.permissions.index',
    'create' => 'central.permissions.create',
    'store' => 'central.permissions.store',
    'show' => 'central.permissions.show',
    'edit' => 'central.permissions.edit',
    'update' => 'central.permissions.update',
    'destroy' => 'central.permissions.destroy',
]);
Route::post('/central/permissions/bulk-create', [PermissionController::class, 'bulkCreate'])->name('central.permissions.bulk-create');

// Role assignments - managing user roles
Route::get('/central/role-assignments', [RoleAssignmentController::class, 'index'])->name('central.role-assignments.index');
Route::get('/central/role-assignments/{user}/edit', [RoleAssignmentController::class, 'edit'])->name('central.role-assignments.edit');
Route::put('/central/role-assignments/{user}', [RoleAssignmentController::class, 'update'])->name('central.role-assignments.update');
Route::post('/central/role-assignments/bulk-assign', [RoleAssignmentController::class, 'bulkAssign'])->name('central.role-assignments.bulk-assign');