<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Property;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Exception;

class UserController extends Controller
{
    use LogsTenantUserActivity;

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Get the property context
        $propertyId = $request->get('property_id');
        if (!$propertyId && !is_super_user()) {
            $propertyId = auth()->user()->property_id;
        } else {
            // Set context if super user is in super user mode but operating in a specific property
            if (is_super_user() && current_property() != null) {
                $propertyId = selected_property_id();
            }
        }

        // Build the query with filters
        $query = User::with(['property']);

        // Apply property filter for non-super users
        if ($propertyId && !is_super_user()) {
          $query->where('property_id', $propertyId);
        } elseif ($propertyId && is_super_user()) {
          // Super users can view users from specific property
          $query->where('property_id', $propertyId);
        }

        // Apply search filters
        if ($request->filled('search')) {
          $search = $request->get('search');
          $query->where(function($q) use ($search) {
              $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%");
          });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Apply status filter
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Paginate results
        $users = $query->orderBy('name', 'asc')->paginate(15)->appends($request->except('page'));

        // Get properties for filter dropdown (super users only)
        $properties = collect();
        if (is_super_user()) {
            $properties = Property::where('is_active', true)->get();
        }

        $currency = property_currency();

        return view('tenant.users.index', compact('users', 'properties', 'currency'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        // Get available properties
        $properties = collect();
        if (is_super_user()) {
            $properties = Property::where('is_active', true)->get();
        } else {
            // Non-super users can only assign to their property
            if (auth()->user()->property_id) {
                $properties = Property::where('id', auth()->user()->property_id)->get();
            }
        }

        $roles = User::SUPPORTED_ROLES;
        $currency = property_currency();

        return view('tenant.users.create', compact('properties', 'roles', 'currency'));
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
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'password' => 'required|string|min:8|confirmed',
                'property_id' => 'required|exists:properties,id',
                'position' => 'nullable|string|max:100',
                'role' => 'required|in:' . implode(',', User::SUPPORTED_ROLES),
                'is_active' => 'boolean',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Validate property access for non-super users
            if (!is_super_user() && $validated['property_id'] !== auth()->user()->property_id) {
                throw new Exception('You can only create users for your assigned property.');
            }

            // Handle profile photo upload
            $profilePhotoPath = null;
            if ($request->hasFile('profile_photo')) {
                $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'password' => Hash::make($validated['password']),
                'property_id' => $validated['property_id'],
                'position' => $validated['position'],
                'role' => $validated['role'],
                'is_active' => $request->boolean('is_active', true),
                'profile_photo_path' => $profilePhotoPath,
            ]);

            // Assign role using Spatie Permission
            $user->assignRole($validated['role']);

            // Log activity
            $this->logTenantActivity(
                'create_user',
                'Created new user: ' . $user->name . ' (' . $user->email . ') with role: ' . $user->role,
                $user,
                [
                    'table' => 'users',
                    'id' => $user->id,
                    'user_id' => auth()->id(),
                    'changes' => $user->toArray()
                ]
            );

            return redirect()->route('tenant.users.index')
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
        // Authorization check
        if (!is_super_user() && $user->property_id !== auth()->user()->property_id) {
            abort(403, 'Unauthorized action.');
        }

        $user->load(['property']);
        $currency = property_currency();

        return view('tenant.users.show', compact('user', 'currency'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        // Authorization check
        if (!is_super_user() && $user->property_id !== auth()->user()->property_id) {
            abort(403, 'Unauthorized action.');
        }

        // Get available properties
        $properties = collect();
        if (is_super_user()) {
            $properties = Property::where('is_active', true)->get();
        } else {
            // Non-super users can only assign to their property
            if (auth()->user()->property_id) {
                $properties = Property::where('id', auth()->user()->property_id)->get();
            }
        }

        $roles = User::SUPPORTED_ROLES;
        $currency = property_currency();

        return view('tenant.users.edit', compact('user', 'properties', 'roles', 'currency'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        try {
            // Authorization check
            if (!is_super_user() && $user->property_id !== auth()->user()->property_id) {
                abort(403, 'Unauthorized action.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'password' => 'nullable|string|min:8|confirmed',
                'property_id' => 'nullable|integer', // only validate if present (The selected property id is invalid)
                'position' => 'nullable|string|max:100',
                'role' => 'required|in:' . implode(',', User::SUPPORTED_ROLES),
                'is_active' => 'boolean',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Validate property access for non-super users
            if (!is_super_user() && $validated['property_id'] !== auth()->user()->property_id) {
                throw new Exception('You can only assign users to your property.');
            }

            // now validate if property_id is set and valid
            if (isset($validated['property_id']) && !Property::find($validated['property_id'])) {
                throw new Exception('Invalid property selected.');
            }

            $oldData = $user->toArray();

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }
                $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            // Handle password update
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Update user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'property_id' => $validated['property_id'],
                'position' => $validated['position'],
                'role' => $validated['role'],
                'is_active' => $request->boolean('is_active'),
                'profile_photo_path' => $validated['profile_photo_path'] ?? $user->profile_photo_path,
            ] + (isset($validated['password']) ? ['password' => $validated['password']] : []));

            // Update role using Spatie Permission
            $user->syncRoles([$validated['role']]);

            // Log activity
            $this->logTenantActivity(
                'update_user',
                'Updated user: ' . $user->name . ' (' . $user->email . ')',
                $user,
                [
                    'table' => 'users',
                    'id' => $user->id,
                    'user_id' => auth()->id(),
                    'old_data' => $oldData,
                    'changes' => $user->getChanges()
                ]
            );

            return redirect()->route('tenant.users.show', $user)
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
            // Authorization check
            if (!is_super_user() && $user->property_id !== auth()->user()->property_id) {
                abort(403, 'Unauthorized action.');
            }

            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                throw new Exception('You cannot delete your own account.');
            }

            $userName = $user->name;
            $userEmail = $user->email;

            // Delete profile photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Soft delete the user
            $user->delete();

            // Log activity
            $this->logTenantActivity(
                'delete_user',
                'Deleted user: ' . $userName . ' (' . $userEmail . ')',
                null,
                [
                    'table' => 'users',
                    'id' => $user->id,
                    'user_id' => auth()->id(),
                    'deleted_data' => $user->toArray()
                ]
            );

            return redirect()->route('tenant.users.index')
                ->with('success', 'User deleted successfully!');

        } catch (Exception $e) {
            \Log::error("User deletion failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle user status between active and inactive
     */
    public function toggleStatus(User $user)
    {
        try {
            // Authorization check
            if (!is_super_user() && $user->property_id !== auth()->user()->property_id) {
                abort(403, 'Unauthorized action.');
            }

            // Prevent deactivating yourself
            if ($user->id === auth()->id()) {
                throw new Exception('You cannot deactivate your own account.');
            }

            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            // Log activity
            $this->logTenantActivity(
                'toggle_user_status',
                'Changed user status to ' . ($newStatus ? 'active' : 'inactive') . ' for: ' . $user->name,
                $user,
                [
                    'table' => 'users',
                    'id' => $user->id,
                    'user_id' => auth()->id(),
                    'changes' => ['is_active' => $newStatus]
                ]
            );

            return back()->with('success', 'User status updated successfully!');

        } catch (Exception $e) {
            \Log::error("User status toggle failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update user status: ' . $e->getMessage()]);
        }
    }

    /**
     * Show user profile (for current user to edit their own profile)
     */
    public function profile()
    {
        $user = auth()->user();
        $currency = property_currency();

        return view('tenant.users.profile', compact('user', 'currency'));
    }

    /**
     * Update user profile (for current user to edit their own profile)
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'password' => 'nullable|string|min:8|confirmed',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $oldData = $user->toArray();

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }
                $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            // Handle password update
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Update user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'profile_photo_path' => $validated['profile_photo_path'] ?? $user->profile_photo_path,
            ] + (isset($validated['password']) ? ['password' => $validated['password']] : []));

            // Log activity
            $this->logTenantActivity(
                'update_profile',
                'Updated own profile',
                $user,
                [
                    'table' => 'users',
                    'id' => $user->id,
                    'user_id' => auth()->id(),
                    'old_data' => $oldData,
                    'changes' => $user->getChanges()
                ]
            );

            return redirect()->route('tenant.users.profile')
                ->with('success', 'Profile updated successfully!');

        } catch (Exception $e) {
            \Log::error("Profile update failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->withInput();
        }
    }
}