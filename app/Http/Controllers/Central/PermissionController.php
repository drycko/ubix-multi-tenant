<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralPermission as Permission;
use App\Models\CentralRole as Role;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    use LogsAdminActivity;

    public function __construct()
    {
      // use the central database connection from here because I am in the central app
      config(['database.connections.tenant' => config('database.connections.central')]);
      $this->middleware('auth:web');
      $this->middleware('permission:manage permissions', ['except' => ['index', 'show']]);
      $this->middleware('permission:view permissions', ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of permissions
     */
    public function index()
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->get();

        $groupedPermissions = $this->groupPermissions($permissions);

        return view('central.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        return view('central.permissions.create');
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $permission = Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'guard_name' => 'tenant',
            ]);

            $this->logTenantActivity(
                'permission_created',
                "Created permission: {$permission->name}",
                $permission,
                ['table' => 'permissions', 'permission_id' => $permission->id, 'action' => 'create']
            );

            return redirect()
                ->route('central.permissions.show', $permission)
                ->with('success', 'Permission created successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create permission. Please try again.');
        }
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        
        return view('central.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit(Permission $permission)
    {
        return view('central.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permission->id)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $originalName = $permission->name;

            $permission->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            $this->logTenantActivity(
                'permission_updated',
                "Updated permission: {$originalName}" . ($originalName !== $permission->name ? " → {$permission->name}" : ""),
                $permission,
                ['table' => 'permissions', 'permission_id' => $permission->id, 'action' => 'update']
            );

            return redirect()
                ->route('central.permissions.show', $permission)
                ->with('success', 'Permission updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update permission. Please try again.');
        }
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to roles
        if ($permission->roles()->count() > 0) {
            return redirect()
                ->route('central.permissions.show', $permission)
                ->with('warning', 'Cannot delete permission that is assigned to roles.');
        }

        try {
            $permissionName = $permission->name;
            $permission->delete();

            $this->logTenantActivity(
                'permission_deleted',
                "Deleted permission: {$permissionName}",
                $permission,
                ['table' => 'permissions', 'permission_id' => $permission->id, 'action' => 'delete']
            );

            return redirect()
                ->route('central.permissions.index')
                ->with('success', 'Permission deleted successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->route('central.permissions.show', $permission)
                ->with('error', 'Failed to delete permission. Please try again.');
        }
    }

    /**
     * Bulk create permissions
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'resource' => ['required', 'string', 'max:255'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['required', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $created = [];
            $skipped = [];

            foreach ($request->actions as $action) {
                $permissionName = trim($action) . ' ' . trim($request->resource);
                
                if (Permission::where('name', $permissionName)->exists()) {
                    $skipped[] = $permissionName;
                    continue;
                }

                $permission = Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'tenant',
                ]);

                $created[] = $permission->name;
            }

            DB::commit();

            $this->logTenantActivity(
                'permissions_bulk_created',
                "Bulk created permissions for resource: {$request->resource}",
                null,
                ['created_count' => count($created), 'skipped_count' => count($skipped)]
            );

            $message = count($created) . ' permissions created successfully.';
            if (count($skipped) > 0) {
                $message .= ' ' . count($skipped) . ' permissions already existed and were skipped.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create permissions. Please try again.');
        }
    }

    /**
     * Group permissions by resource for display
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
}