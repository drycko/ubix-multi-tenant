<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CentralRole as Role;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Exception;

class UserController extends Controller
{
    use LogsAdminActivity;

    public function __construct()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        $this->middleware('auth:web');
        $this->middleware('permission:manage users', ['except' => ['index', 'show']]);
        $this->middleware('permission:view users', ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Build the query with filters
        $query = User::with(['roles']);

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        // Apply status filter (deleted/active)
        if ($request->has('status')) {
            if ($request->get('status') === 'deleted') {
                $query->onlyTrashed();
            } elseif ($request->get('status') === 'active') {
                $query->whereNull('deleted_at');
            }
        }

        // Paginate results
        $users = $query->orderBy('name', 'asc')->paginate(15)->appends($request->except('page'));

        // Get roles for filter dropdown
        $roles = Role::whereIn('name', User::SUPPORTED_ROLES)->get();

        return view('central.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::whereIn('name', User::SUPPORTED_ROLES)->get();

        return view('central.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:' . implode(',', User::SUPPORTED_ROLES),
            ]);

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign role using Spatie Permission
            $user->assignRole($validated['role']);

            // Log activity
            $this->logAdminActivity(
                'create',
                'users',
                $user->id,
                'Created new user: ' . $user->name . ' (' . $user->email . ') with role: ' . $validated['role']
            );

            return redirect()->route('central.users.index')
                ->with('success', 'User created successfully!');

        } catch (Exception $e) {
            \Log::error("User creation failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'adminActivities' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('central.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::whereIn('name', User::SUPPORTED_ROLES)->get();

        return view('central.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'role' => 'required|in:' . implode(',', User::SUPPORTED_ROLES),
            ]);

            // Prevent users from removing their own admin role
            if ($user->id === auth()->id() && !in_array($validated['role'], ['super-admin', 'super-manager'])) {
                throw new Exception('You cannot remove your own admin privileges.');
            }

            $oldData = $user->toArray();

            // Handle password update
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            // Update user
            $user->update($updateData);

            // Update role using Spatie Permission
            $user->syncRoles([$validated['role']]);

            // Log activity
            $this->logAdminActivity(
                'update',
                'users',
                $user->id,
                'Updated user: ' . $user->name . ' (' . $user->email . ')'
            );

            return redirect()->route('central.users.show', $user)
                ->with('success', 'User updated successfully!');

        } catch (Exception $e) {
            \Log::error("User update failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                throw new Exception('You cannot delete your own account.');
            }

            // Prevent deleting super-admin users (optional safety check)
            if ($user->hasRole('super-admin')) {
                throw new Exception('Cannot delete super-admin users.');
            }

            $userName = $user->name;
            $userEmail = $user->email;

            // Soft delete the user
            $user->delete();

            // Log activity
            $this->logAdminActivity(
                'soft_delete',
                'users',
                $user->id,
                'Deleted user: ' . $userName . ' (' . $userEmail . ')'
            );

            return redirect()->route('central.users.index')
                ->with('success', 'User deleted successfully!');

        } catch (Exception $e) {
            \Log::error("User deletion failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            // Log activity
            $this->logAdminActivity(
                'restore',
                'users',
                $user->id,
                'Restored user: ' . $user->name . ' (' . $user->email . ')'
            );

            return redirect()->route('central.users.index')
                ->with('success', 'User restored successfully!');

        } catch (Exception $e) {
            \Log::error("User restoration failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to restore user: ' . $e->getMessage()]);
        }
    }
}