<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    /**
     * Display all roles and their permission counts.
     */
    public function index()
    {
        $roles = Role::where('guard_name', 'web')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('users.roles.index', compact('roles'));
    }

    /**
     * Show the permission assignment page for a role.
     */
    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            });

        $assignedPermissionIds = $role->permissions()
            ->pluck('permissions.id')
            ->toArray();

        return view('users.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissionIds' => $assignedPermissionIds,
        ]);
    }

    /**
     * Save permissions assigned to a role.
     */
    public function update(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin Protection
        |--------------------------------------------------------------------------
        |
        | Super Admin must always have every available permission.
        | This prevents the system owner from accidentally locking
        | themselves out of EDUNEXUS.
        |
        */

        if ($role->name === 'Super Admin') {
            $allPermissions = Permission::where(
                'guard_name',
                'web'
            )->get();

            $role->syncPermissions($allPermissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return redirect()
                ->route('roles.permissions.edit', $role)
                ->with(
                    'success',
                    'Super Admin retains all system permissions.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Selected Permissions
        |--------------------------------------------------------------------------
        */

        $permissionIds = $validated['permissions'] ?? [];

        $permissions = Permission::where(
            'guard_name',
            'web'
        )
            ->whereIn('id', $permissionIds)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Sync Role Permissions
        |--------------------------------------------------------------------------
        */

        $role->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.permissions.edit', $role)
            ->with(
                'success',
                "Permissions for {$role->name} have been updated successfully."
            );
    }
}