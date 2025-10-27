<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\GuestClub;
use App\Models\Tenant\GuestClubMember;
use App\Models\Tenant\Guest;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestClubController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('permission:view guest clubs')->only(['index', 'show', 'members']);
        $this->middleware('permission:create guest clubs')->only(['create', 'store']);
        $this->middleware('permission:edit guest clubs')->only(['edit', 'update']);
        $this->middleware('permission:manage guest clubs')->only(['toggleStatus', 'changeMemberStatus', 'bulkAction']);
        $this->middleware('permission:delete guest clubs')->only(['destroy', 'removeMember']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GuestClub::where('property_id', selected_property_id())
            ->withCount(['members', 'activeMembers']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('tier_level', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply tier filter
        if ($request->filled('tier')) {
            $query->where('tier_level', $request->get('tier'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'tier_priority');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, ['name', 'tier_level', 'tier_priority', 'created_at', 'members_count'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $guestClubs = $query->paginate(15)->withQueryString();

        // Get filter options
        $tiers = GuestClub::where('property_id', selected_property_id())
            ->whereNotNull('tier_level')
            ->distinct()
            ->orderBy('tier_level')
            ->pluck('tier_level');

        return view('tenant.guest-clubs.index', compact('guestClubs', 'tiers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenant.guest-clubs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tier_level' => 'nullable|string|max:50',
            'tier_priority' => 'nullable|integer|min:0|max:100',
            'min_bookings' => 'nullable|integer|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'badge_color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            
            // Benefits
            'benefits.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'benefits.late_checkout' => 'boolean',
            'benefits.early_checkin' => 'boolean',
            'benefits.complimentary_wifi' => 'boolean',
            'benefits.complimentary_breakfast' => 'boolean',
            'benefits.room_upgrade' => 'boolean',
            'benefits.airport_shuttle' => 'boolean',
            'benefits.spa_discount' => 'nullable|numeric|min:0|max:100',
            'benefits.restaurant_discount' => 'nullable|numeric|min:0|max:100',
            'benefits.priority_booking' => 'boolean',
            'benefits.concierge_service' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Process benefits - remove empty values
            $benefits = [];
            if ($request->has('benefits')) {
                foreach ($request->input('benefits') as $key => $value) {
                    if ($value !== null && $value !== '' && $value !== false) {
                        $benefits[$key] = $value;
                    }
                }
            }

            $guestClub = GuestClub::create(array_merge($validated, [
                'property_id' => selected_property_id(),
                'benefits' => $benefits,
                'is_active' => $request->has('is_active'),
            ]));

            // Log the activity
            $this->logTenantActivity(
                'create_guest_club',
                'Created guest club: ' . $guestClub->name,
                $guestClub,
                [
                    'table' => 'guest_clubs',
                    'id' => $guestClub->id,
                    'action' => 'create'
                ]
            );

            DB::commit();

            return redirect()
                ->route('tenant.guest-clubs.index')
                ->with('success', 'Guest club created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create guest club. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load relationships
        $guestClub->load(['members.guest']);

        // Get member statistics
        $memberStats = [
            'total_members' => $guestClub->members()->count(),
            'active_members' => $guestClub->activeMembers()->count(),
            'new_members_this_month' => $guestClub->members()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Get recent members
        $recentMembers = $guestClub->members()
            ->with('guest')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('tenant.guest-clubs.show', compact('guestClub', 'memberStats', 'recentMembers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('tenant.guest-clubs.edit', compact('guestClub'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tier_level' => 'nullable|string|max:50',
            'tier_priority' => 'nullable|integer|min:0|max:100',
            'min_bookings' => 'nullable|integer|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'badge_color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            
            // Benefits
            'benefits.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'benefits.late_checkout' => 'boolean',
            'benefits.early_checkin' => 'boolean',
            'benefits.complimentary_wifi' => 'boolean',
            'benefits.complimentary_breakfast' => 'boolean',
            'benefits.room_upgrade' => 'boolean',
            'benefits.airport_shuttle' => 'boolean',
            'benefits.spa_discount' => 'nullable|numeric|min:0|max:100',
            'benefits.restaurant_discount' => 'nullable|numeric|min:0|max:100',
            'benefits.priority_booking' => 'boolean',
            'benefits.concierge_service' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $guestClub->toArray();

            // Process benefits - remove empty values
            $benefits = [];
            if ($request->has('benefits')) {
                foreach ($request->input('benefits') as $key => $value) {
                    if ($value !== null && $value !== '' && $value !== false) {
                        $benefits[$key] = $value;
                    }
                }
            }

            $guestClub->update(array_merge($validated, [
                'benefits' => $benefits,
                'is_active' => $request->has('is_active'),
            ]));

            // Log the activity
            $this->logTenantActivity(
                'update_guest_club',
                'Updated guest club: ' . $guestClub->name,
                $guestClub,
                [
                    'table' => 'guest_clubs',
                    'id' => $guestClub->id,
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => $guestClub->fresh()->toArray()
                ]
            );

            DB::commit();

            return redirect()
                ->route('tenant.guest-clubs.show', $guestClub)
                ->with('success', 'Guest club updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update guest club. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Check if club has members
            if ($guestClub->members()->exists()) {
                return back()->withErrors(['error' => 'Cannot delete guest club with existing members. Please remove all members first.']);
            }

            // Log the activity before deletion
            $this->logTenantActivity(
                'delete_guest_club',
                'Deleted guest club: ' . $guestClub->name,
                $guestClub,
                [
                    'table' => 'guest_clubs',
                    'id' => $guestClub->id,
                    'action' => 'delete',
                    'club_data' => $guestClub->toArray()
                ]
            );

            // Soft delete the guest club
            $guestClub->delete();

            DB::commit();

            return redirect()
                ->route('tenant.guest-clubs.index')
                ->with('success', 'Guest club deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete guest club. Please try again.']);
        }
    }

    /**
     * Toggle guest club status.
     */
    public function toggleStatus(GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $guestClub->update(['is_active' => !$guestClub->is_active]);

        // Log the activity
        $this->logTenantActivity(
            'toggle_guest_club_status',
            'Toggled status for guest club: ' . $guestClub->name . ' to ' . ($guestClub->is_active ? 'Active' : 'Inactive'),
            $guestClub,
            [
                'table' => 'guest_clubs',
                'id' => $guestClub->id,
                'new_status' => $guestClub->is_active
            ]
        );

        return back()->with('success', 'Guest club status updated successfully!');
    }

    /**
     * Show club members.
     */
    public function members(GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $query = $guestClub->members()->with('guest');

        // Apply search filter
        if (request('search')) {
            $searchTerm = request('search');
            $query->whereHas('guest', function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $eligibleGuests = Guest::where('property_id', selected_property_id())
            ->whereNotIn('id', $guestClub->members()->pluck('guest_id'))
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get()
            ->filter(function($guest) use ($guestClub) {
                return $guest->qualifiesForClub($guestClub);
            });

        $members = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('tenant.guest-clubs.members', compact('guestClub', 'members', 'eligibleGuests'));
    }

    /**
     * Add a member to the guest club.
     */
    public function addMember(Request $request, GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if guest is already a member
        if ($guestClub->members()->where('guest_id', $validated['guest_id'])->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This guest is already a member of the club.'
                ], 400);
            }
            return back()->withErrors(['error' => 'This guest is already a member of the club.']);
        }

        DB::beginTransaction();
        try {
            $member = GuestClubMember::create([
                'guest_club_id' => $guestClub->id,
                'guest_id' => $validated['guest_id'],
                'joined_at' => now(), // Set to current date since form doesn't ask for it
                'status' => $validated['status'] ?? 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Log the activity
            $this->logTenantActivity(
                'add_guest_club_member',
                'Added member to guest club: ' . $guestClub->name . ' - Guest ID: ' . $member->guest_id,
                $member,
                [
                    'table' => 'guest_club_members',
                    'id' => $member->id,
                    'action' => 'create'
                ]
            );

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member added successfully!'
                ]);
            }
            return back()->with('success', 'Member added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to add member to guest club: ' . $guestClub->name, [
                'error' => $e->getMessage(),
                'guest_club_id' => $guestClub->id,
                'guest_id' => $validated['guest_id'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add member: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to add member. Please try again.']);
        }
    }

    /**
     * Remove a member from the guest club.
     */
    public function removeMember(GuestClub $guestClub, GuestClubMember $member)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $member->delete();

            // Log the activity
            $this->logTenantActivity(
                'remove_guest_club_member',
                'Removed member from guest club: ' . $guestClub->name . ' - Guest ID: ' . $member->guest_id,
                $member,
                [
                    'table' => 'guest_club_members',
                    'id' => $member->id,
                    'action' => 'delete'
                ]
            );

            DB::commit();

            return back()->with('success', 'Member removed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to remove member. Please try again.']);
        }
    
    }

    /**
     * Change member status (active/inactive).
     */
    public function changeMemberStatus(Request $request, GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'member_id' => 'required|exists:guest_club_members,id',
                'status' => 'required|in:active,inactive,suspended'
            ]);

            $member = $guestClub->members()->findOrFail($request->input('member_id'));
            $oldStatus = $member->status;
            $newStatus = $request->input('status');

            $member->update(['status' => $newStatus]);

            // Log the activity
            $this->logTenantActivity(
                'change_guest_club_member_status',
                'Changed status for member in guest club: ' . $guestClub->name . ' - Guest ID: ' . $member->guest_id . ' from ' . ucfirst($oldStatus) . ' to ' . ucfirst($newStatus),
                $member,
                [
                    'table' => 'guest_club_members',
                    'id' => $member->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member status updated successfully!'
                ]);
            }
            return back()->with('success', 'Member status updated successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update member status: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => "Failed to update member status, with error: " . $e->getMessage()]);
        }
    }

    /**
     * Perform bulk actions on members.
     */
    public function bulkAction(Request $request, GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'required|exists:guest_club_members,id',
            'action' => 'required|in:activate,suspend,inactive,delete'
        ]);

        DB::beginTransaction();
        try {
            $memberIds = $validated['member_ids'];
            $action = $validated['action'];
            
            $members = $guestClub->members()->whereIn('id', $memberIds)->get();
            
            if ($members->count() !== count($memberIds)) {
                return response()->json(['success' => false, 'message' => 'Some members not found.'], 400);
            }

            foreach ($members as $member) {
                switch ($action) {
                    case 'activate':
                        $member->update(['status' => 'active']);
                        break;
                    case 'suspend':
                        $member->update(['status' => 'suspended']);
                        break;
                    case 'inactive':
                        $member->update(['status' => 'inactive']);
                        break;
                    case 'delete':
                        $member->delete();
                        break;
                }
            }

            // Log the activity
            $this->logTenantActivity(
                'bulk_action_guest_club_members',
                "Performed bulk action '{$action}' on " . count($memberIds) . " members in guest club: " . $guestClub->name,
                $guestClub,
                [
                    'table' => 'guest_club_members',
                    'action' => "bulk_{$action}",
                    'member_count' => count($memberIds),
                    'member_ids' => $memberIds
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Successfully performed {$action} on " . count($memberIds) . " members."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export club members to CSV.
     */
    public function exportMembers(GuestClub $guestClub)
    {
        // Authorization check
        if ($guestClub->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $members = $guestClub->members()->with('guest')->get();

        $filename = 'guest-club-members-' . str_replace(' ', '-', strtolower($guestClub->name)) . '-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($members) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Member ID',
                'Guest Name',
                'Email',
                'Phone',
                'Status',
                'Joined Date',
                'Total Bookings',
                'Total Spend',
                'Benefits Used',
                'Last Activity',
                'Notes'
            ]);

            // Add member data
            foreach ($members as $member) {
                fputcsv($file, [
                    $member->id,
                    $member->guest->first_name . ' ' . $member->guest->last_name,
                    $member->guest->email,
                    $member->guest->phone ?? 'N/A',
                    ucfirst($member->status),
                    $member->joined_at->format('Y-m-d'),
                    $member->guest->bookings_count ?? 0,
                    '$' . number_format($member->guest->total_spend ?? 0, 2),
                    $member->benefits_used_count ?? 0,
                    $member->updated_at->format('Y-m-d H:i'),
                    $member->notes ?? ''
                ]);
            }

            fclose($file);
        };

        // Log the activity
        $this->logTenantActivity(
            'export_guest_club_members',
            'Exported members for guest club: ' . $guestClub->name,
            $guestClub,
            [
                'table' => 'guest_clubs',
                'id' => $guestClub->id,
                'action' => 'export_members',
                'member_count' => $members->count()
            ]
        );

        return response()->stream($callback, 200, $headers);
    }
}
