<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    /**
     * Display all web roles and their permission counts.
     */
    public function index()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('users.roles.index', compact('roles'));
    }

    /**
     * Display the permission assignment page for a role.
     */
    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        /*
        |--------------------------------------------------------------------------
        | Load all web permissions
        |--------------------------------------------------------------------------
        */

        $allPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Group permissions by functional module
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | students.view
        | students.create
        | students.edit
        |
        | becomes:
        |
        | students
        |   - view
        |   - create
        |   - edit
        |
        */

        $permissions = $allPermissions->groupBy(
            fn (Permission $permission) =>
                explode('.', $permission->name, 2)[0]
        );

        /*
        |--------------------------------------------------------------------------
        | Backward-compatible alias
        |--------------------------------------------------------------------------
        |
        | Some versions of the Blade use $groupedPermissions while others
        | use $permissions. Provide both so the view contract remains safe.
        |
        */

        $groupedPermissions = $permissions;

        /*
        |--------------------------------------------------------------------------
        | Assigned permissions
        |--------------------------------------------------------------------------
        */

        $assignedPermissionIds = $role->permissions()
            ->where('guard_name', 'web')
            ->pluck('permissions.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | All roles for the left-hand role selector
        |--------------------------------------------------------------------------
        */

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Return view
        |--------------------------------------------------------------------------
        */

        return view('users.roles.edit', [
            'roles' => $roles,
            'role' => $role,
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'assignedPermissionIds' => $assignedPermissionIds,
        ]);
    }

    /**
     * Update permissions assigned to a role.
     */
    public function update(Request $request, Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        /*
        |--------------------------------------------------------------------------
        | Validate submitted permissions
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin protection
        |--------------------------------------------------------------------------
        |
        | Super Admin always receives every web permission.
        |
        */

        if ($role->name === 'Super Admin') {
            $allPermissions = Permission::query()
                ->where('guard_name', 'web')
                ->get();

            $role->syncPermissions($allPermissions);

            app(PermissionRegistrar::class)
                ->forgetCachedPermissions();

            return redirect()
                ->route('roles.permissions.edit', $role)
                ->with(
                    'success',
                    'Super Admin retains all system permissions.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Submitted permission IDs
        |--------------------------------------------------------------------------
        */

        $permissionIds = collect(
            $validated['permissions'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Restrict assignments to web-guard permissions
        |--------------------------------------------------------------------------
        */

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $permissionIds)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Synchronize permissions
        |--------------------------------------------------------------------------
        */

        $role->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Clear Spatie cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return redirect()
            ->route('roles.permissions.edit', $role)
            ->with(
                'success',
                "Permissions for {$role->name} have been updated successfully."
            );
    }
}