<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralRole as Role;
use App\Models\CentralPermission as Permission;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use LogsAdminActivity;

    public function __construct()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        $this->middleware('auth:web');
        $this->middleware('permission:manage roles', ['except' => ['index', 'show']]);
        $this->middleware('permission:view roles', ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('central.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);

        return view('central.roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'guard_name' => 'tenant',
            ]);

            if ($request->permissions) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            // Log the role creation activity
            $this->logAdminActivity(
                "role_created",
                "roles",
                $role->id,
                "Created a new central role"
            );
            DB::commit();

            return redirect()
                ->route('central.roles.show', $role)
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', "Failed to create role. With error: {$e->getMessage()}");
        }
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        $groupedPermissions = $this->groupPermissions($role->permissions);

        return view('central.roles.show', compact('role', 'groupedPermissions'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        // Prevent editing system roles
        if ($this->isSystemRole($role)) {
            return redirect()
                ->route('central.roles.show', $role)
                ->with('warning', 'System roles cannot be edited.');
        }

        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('central.roles.edit', compact('role', 'permissions', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing system roles
        if ($this->isSystemRole($role)) {
            return redirect()
                ->route('central.roles.show', $role)
                ->with('warning', 'System roles cannot be edited.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        DB::beginTransaction();
        try {
            $originalName = $role->name;
            $originalPermissions = $role->permissions->pluck('id')->toArray();

            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            if ($request->permissions) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            $this->logAdminActivity(
                "role_updated",
                "roles",
                $role->id,
                "Updated a central role"
            );

            DB::commit();
            return redirect()
                ->route('central.roles.show', $role)
                ->with('success', 'Role updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update role. Please try again.');
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deleting system roles
        if ($this->isSystemRole($role)) {
            return redirect()
                ->route('central.roles.index')
                ->with('warning', 'System roles cannot be deleted.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('central.roles.show', $role)
                ->with('warning', 'Cannot delete role that has assigned users.');
        }

        try {
            $roleName = $role->name;
            $role->delete();

            $this->logAdminActivity(
                "role_deleted",
                "roles",
                $role->id,
                "Deleted central role: {$roleName}"
            );

            return redirect()
                ->route('central.roles.index')
                ->with('success', 'Role deleted successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->route('central.roles.show', $role)
                ->with('error', 'Failed to delete role. Please try again.');
        }
    }

    /**
     * Sync permissions for a role (AJAX endpoint)
     */
    public function syncPermissions(Request $request, Role $role)
    {
        if ($this->isSystemRole($role)) {
            return response()->json(['error' => 'System roles cannot be modified.'], 403);
        }

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        try {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            // log the permission sync activity
            $this->logAdminActivity(
                "role_permissions_synced",
                "roles",
                $role->id,
                "Synchronized permissions for central role"
            );

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update permissions.'], 500);
        }
    }

    /**
     * Group permissions by category for display
     */
    private function groupPermissions($permissions)
    {
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            $action = $parts[0] ?? 'other';
            $resource = implode(' ', array_slice($parts, 1)) ?: 'general';
            
            if (!isset($grouped[$resource])) {
                $grouped[$resource] = [];
            }
            
            $grouped[$resource][] = $permission;
        }

        // Sort groups and permissions within each group
        ksort($grouped);
        foreach ($grouped as &$group) {
            usort($group, function($a, $b) {
                return strcmp($a->name, $b->name);
            });
        }

        return $grouped;
    }

    /**
     * Check if a role is a system role that shouldn't be modified
     */
    private function isSystemRole(Role $role)
    {
        $systemRoles = ['super-user', 'super-manager', 'support'];
        return in_array($role->name, $systemRoles);
    }
}