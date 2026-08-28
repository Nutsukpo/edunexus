<?php

use App\Http\Controllers\RolePermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EDUNEXUS ROLE & PERMISSION MANAGEMENT
|--------------------------------------------------------------------------
|
| All routes require an authenticated web user.
| Access is then controlled by Spatie permissions.
|
*/

Route::middleware(['auth:web'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | View Roles
    |--------------------------------------------------------------------------
    */

    Route::middleware(['permission:roles.view'])->group(function () {

        Route::get('/roles-permissions', [
            RolePermissionController::class,
            'index'
        ])->name('roles.permissions.index');

    });

    /*
    |--------------------------------------------------------------------------
    | Manage Role Permissions
    |--------------------------------------------------------------------------
    */

    Route::middleware(['permission:roles.manage-permissions'])->group(function () {

        Route::get('/roles-permissions/{role}/edit', [
            RolePermissionController::class,
            'edit'
        ])->name('roles.permissions.edit');

        Route::put('/roles-permissions/{role}', [
            RolePermissionController::class,
            'update'
        ])->name('roles.permissions.update');

    });

});