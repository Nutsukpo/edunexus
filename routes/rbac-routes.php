<?php

// Add this import near the other controller imports:
use App\Http\Controllers\RolePermissionController;


// Add these routes INSIDE the existing auth:web group in routes/web.php:
Route::middleware(['permission:roles.view'])->group(function () {
    Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
        ->name('roles.permissions.index');
});

Route::middleware(['permission:roles.manage-permissions'])->group(function () {
    Route::get('/roles-permissions/{role}/edit', [RolePermissionController::class, 'edit'])
        ->name('roles.permissions.edit');

    Route::put('/roles-permissions/{role}', [RolePermissionController::class, 'update'])
        ->name('roles.permissions.update');
});


Route::middleware(['auth:web'])->group(function () {
    Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
        ->name('roles.permissions.index');

    Route::get('/roles-permissions/{role}/edit', [RolePermissionController::class, 'edit'])
        ->name('roles.permissions.edit');

    Route::put('/roles-permissions/{role}', [RolePermissionController::class, 'update'])
        ->name('roles.permissions.update');
});