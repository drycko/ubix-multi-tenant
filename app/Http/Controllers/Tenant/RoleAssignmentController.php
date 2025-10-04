<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Role;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleAssignmentController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('auth:tenant');
        $this->middleware('permission:assign roles');
    }

    /**
     * Display role assignments overview
     */
    public function index()
    {
        $users = User::with(['roles', 'property'])
            ->when(auth('tenant')->user()->property_id, function ($query) {
                $query->where('property_id', auth('tenant')->user()->property_id);
            })
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view('tenant.role-assignments.index', compact('users', 'roles'));
    }

    /**
     * Show role assignment form for a user
     */
    public function edit(User $user)
    {
        // Check if user can manage this user (same property or super user)
        if (!$this->canManageUser($user)) {
            return redirect()
                ->route('tenant.role-assignments.index')
                ->with('error', 'You cannot manage roles for this user.');
        }

        $roles = Role::orderBy('name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('tenant.role-assignments.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update user role assignments
     */
    public function update(Request $request, User $user)
    {
        if (!$this->canManageUser($user)) {
            return redirect()
                ->route('tenant.role-assignments.index')
                ->with('error', 'You cannot manage roles for this user.');
        }

        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        DB::beginTransaction();
        try {
            $oldRoles = $user->roles->pluck('name')->toArray();
            
            if ($request->roles) {
                $roles = Role::whereIn('id', $request->roles)->get();
                $user->syncRoles($roles);
            } else {
                $user->syncRoles([]);
            }

            $newRoles = $user->fresh()->roles->pluck('name')->toArray();

            DB::commit();

            $this->logTenantActivity(
                'user_roles_updated',
                "Updated roles for user: {$user->name}",
                $user,
                [
                    'user_id' => $user->id,
                    'old_roles' => $oldRoles,
                    'new_roles' => $newRoles
                ]
            );

            return redirect()
                ->route('tenant.role-assignments.index')
                ->with('success', "Roles updated successfully for {$user->name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update roles. Please try again.');
        }
    }

    /**
     * Bulk assign role to multiple users
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['exists:users,id'],
            'role' => ['required', 'exists:roles,id'],
            'action' => ['required', 'in:assign,remove'],
        ]);

        $role = Role::findOrFail($request->role);
        $users = User::whereIn('id', $request->users);

        // Filter users that current user can manage
        $currentUser = auth('tenant')->user();
        if ($currentUser->property_id) {
            $users = $users->where('property_id', $currentUser->property_id);
        }

        $users = $users->get();

        if ($users->isEmpty()) {
            return back()->with('warning', 'No valid users selected for role assignment.');
        }

        DB::beginTransaction();
        try {
            $updatedUsers = [];

            foreach ($users as $user) {
                if ($request->action === 'assign') {
                    if (!$user->hasRole($role)) {
                        $user->assignRole($role);
                        $updatedUsers[] = $user->name;
                    }
                } else {
                    if ($user->hasRole($role)) {
                        $user->removeRole($role);
                        $updatedUsers[] = $user->name;
                    }
                }
            }

            DB::commit();

            $this->logTenantActivity(
                'bulk_role_assignment',
                "Bulk {$request->action} role '{$role->name}' for " . count($updatedUsers) . " users",
                null,
                [
                    'role_id' => $role->id,
                    'action' => $request->action,
                    'affected_users' => $updatedUsers
                ]
            );

            $message = count($updatedUsers) . " users updated with role '{$role->name}'.";
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update user roles. Please try again.');
        }
    }

    /**
     * Check if current user can manage the target user
     */
    private function canManageUser(User $user)
    {
        $currentUser = auth('tenant')->user();
        
        // Super users can manage anyone
        if ($currentUser->hasRole(['super-user', 'super-manager'])) {
            return true;
        }

        // Users can only manage users in their property
        return $currentUser->property_id === $user->property_id;
    }
}