<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomStatus;
use App\Models\Tenant\Property;
use App\Models\Tenant\User;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomStatusController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view room status')->only(['index', 'show']);
        $this->middleware('permission:update room status')->only(['update', 'assign', 'start', 'complete', 'inspect', 'bulkAssign', 'initializeStatuses']);
    }

    /**
     * Display room status grid
     */
    public function index(Request $request)
    {
        $propertyId = $request->get('property_id');
        $floor = $request->get('floor');
        $status = $request->get('status');

        $properties = Property::all();

        $statusQuery = RoomStatus::with(['room.type', 'property', 'assignedTo', 'inspectedBy'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->when($status, fn($q) => $q->where('status', $status));

        if ($floor) {
            if ($floor === '0' || $floor === 0) {
                $floor = null;
            }
            $statusQuery->whereHas('room', function($q) use ($floor) {
                $q->where('floor', $floor);
            });
        }

        $roomStatuses = $statusQuery->orderBy('property_id')
                                  ->orderBy('room_id')
                                  ->get();

        // Get available floors
        $floors = Room::when($propertyId, fn($q) => $q->where('property_id', $propertyId))
                     ->distinct()
                     ->orderBy('floor')
                     ->pluck('floor');

        // Get housekeeping staff
        $staff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%housekeeping%');
        })->get();

        return view('tenant.room-status.index', compact(
            'roomStatuses',
            'properties', 
            'propertyId',
            'floor',
            'floors',
            'status',
            'staff'
        ));
    }

    /**
     * Update room status
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:dirty,clean,inspected,maintenance,out_of_order',
            'housekeeping_status' => 'required|in:pending,in_progress,completed,inspected',
            'notes' => 'nullable|string|max:1000'
        ]);

        $roomStatus = RoomStatus::findOrFail($id);
        $oldStatus = $roomStatus->status;
        $oldHousekeepingStatus = $roomStatus->housekeeping_status;

        $roomStatus->update([
            'status' => $request->status,
            'housekeeping_status' => $request->housekeeping_status,
            'notes' => $request->notes,
            'status_changed_at' => now()
        ]);

        $this->logTenantActivity('update_room_status', 'Updated room status', $roomStatus, [
            'room_id' => $roomStatus->room_id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'old_housekeeping_status' => $oldHousekeepingStatus,
            'new_housekeeping_status' => $request->housekeeping_status
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Room status updated successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Room status updated successfully.');
    }

    /**
     * Assign room to housekeeping staff
     */
    public function assign(Request $request, string $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        try {
            // start transaction
            \DB::beginTransaction();
            // Check if user exists
            $user = User::findOrFail($request->assigned_to);
            // if user exists 
            if (!$user) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Assigned user not found'
                ], 404);
            }

            $roomStatus = RoomStatus::findOrFail($id);

            if ($roomStatus->housekeeping_status !== RoomStatus::HOUSEKEEPING_PENDING) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Room can only be assigned when status is pending'
                ], 400);
            }

            $roomStatus->update([
                'assigned_to' => $request->assigned_to,
                'housekeeping_status' => RoomStatus::HOUSEKEEPING_PENDING,
                'assigned_at' => now()
            ]);

            $this->logTenantActivity('assign_room', 'Assigned room to staff', $roomStatus, [
                'room_id' => $roomStatus->room_id,
                'assigned_to' => $request->assigned_to
            ]);
            \DB::commit();

            if ($request->ajax()) {
                \Log::info('Room assigned via AJAX successfully');
                return response()->json([
                    'success' => true,
                    'message' => 'Room assigned successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Room assigned successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error assigning room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assigning room: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Start housekeeping work
     */
    public function start(Request $request, string $id)
    {
        $roomStatus = RoomStatus::findOrFail($id);

        if (!$roomStatus->canStart()) {
            return response()->json([
                'success' => false,
                'message' => 'Room work cannot be started'
            ], 400);
        }

        $roomStatus->update([
            'housekeeping_status' => RoomStatus::HOUSEKEEPING_IN_PROGRESS,
            'started_at' => now()
        ]);

        $this->logTenantActivity('start_room', 'Started room housekeeping', $roomStatus, [
            'room_id' => $roomStatus->room_id
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Housekeeping work started'
            ]);
        }

        return redirect()->back()->with('success', 'Housekeeping work started.');
    }

    /**
     * Complete housekeeping work
     */
    public function complete(Request $request, string $id)
    {
        $request->validate([
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        $roomStatus = RoomStatus::findOrFail($id);

        if (!$roomStatus->canComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Room work cannot be completed'
            ], 400);
        }

        $roomStatus->update([
            'housekeeping_status' => RoomStatus::HOUSEKEEPING_COMPLETED,
            'status' => RoomStatus::STATUS_CLEAN,
            'completed_at' => now(),
            'notes' => $request->completion_notes
        ]);

        $this->logTenantActivity('complete_room', 'Completed room housekeeping', $roomStatus, [
            'room_id' => $roomStatus->room_id,
            'completion_notes' => $request->completion_notes
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Housekeeping work completed'
            ]);
        }

        return redirect()->back()->with('success', 'Housekeeping work completed.');
    }

    /**
     * Inspect completed room
     */
    public function inspect(Request $request, string $id)
    {
        $request->validate([
            'passed' => 'required|boolean',
            'inspection_notes' => 'nullable|string|max:1000'
        ]);

        $roomStatus = RoomStatus::findOrFail($id);

        if ($roomStatus->housekeeping_status !== RoomStatus::HOUSEKEEPING_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Room must be completed before inspection'
            ], 400);
        }

        $updates = [
            'inspected_by' => Auth::id(),
            'inspected_at' => now(),
            'notes' => $request->inspection_notes
        ];

        if ($request->passed) {
            $updates['housekeeping_status'] = RoomStatus::HOUSEKEEPING_INSPECTED;
            $updates['status'] = RoomStatus::STATUS_INSPECTED;
        } else {
            // Failed inspection - back to dirty
            $updates['housekeeping_status'] = RoomStatus::HOUSEKEEPING_PENDING;
            $updates['status'] = RoomStatus::STATUS_DIRTY;
            $updates['assigned_to'] = null;
            $updates['assigned_at'] = null;
            $updates['started_at'] = null;
            $updates['completed_at'] = null;
        }

        $roomStatus->update($updates);

        $this->logTenantActivity('inspect_room', 'Inspected room', $roomStatus, [
            'room_id' => $roomStatus->room_id,
            'passed' => $request->passed,
            'inspection_notes' => $request->inspection_notes
        ]);

        $message = $request->passed ? 
                  'Room passed inspection' : 
                  'Room failed inspection and has been reset';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Get room status details for AJAX
     */
    public function show(string $id)
    {
        $roomStatus = RoomStatus::with(['room.type', 'property', 'assignedTo', 'inspectedBy'])
                                ->findOrFail($id);

        return response()->json([
            'room_status' => $roomStatus,
            'can_assign' => $roomStatus->canBeAssigned(),
            'can_start' => $roomStatus->canStart(),
            'can_complete' => $roomStatus->canComplete()
        ]);
    }

    /**
     * Bulk assign multiple rooms
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'room_status_ids' => 'required|array',
            'room_status_ids.*' => 'exists:room_statuses,id',
            'assigned_to' => 'required|exists:users,id'
        ]);

        $assignedCount = 0;

        DB::transaction(function () use ($request, &$assignedCount) {
            $roomStatuses = RoomStatus::whereIn('id', $request->room_status_ids)
                ->where('housekeeping_status', RoomStatus::HOUSEKEEPING_PENDING)
                ->get();

            foreach ($roomStatuses as $roomStatus) {
                $roomStatus->update([
                    'assigned_to' => $request->assigned_to,
                    'assigned_at' => now()
                ]);
                $assignedCount++;
            }
        });

        $this->logTenantActivity('bulk_assign_rooms', 'Bulk assigned rooms', null, [
            'assigned_count' => $assignedCount,
            'assigned_to' => $request->assigned_to
        ]);

        return redirect()->back()->with('success', 
            "Successfully assigned {$assignedCount} rooms to staff member.");
    }

    /**
     * Initialize room statuses for rooms without status
     */
    public function initializeStatuses()
    {
        $roomsWithoutStatus = Room::whereDoesntHave('statuses')->get();
        $createdCount = 0;

        foreach ($roomsWithoutStatus as $room) {
            RoomStatus::create([
                'room_id' => $room->id,
                'property_id' => $room->property_id,
                'status' => RoomStatus::STATUS_DIRTY,
                'housekeeping_status' => RoomStatus::HOUSEKEEPING_PENDING,
                'status_changed_at' => now()
            ]);
            $createdCount++;
        }

        $this->logTenantActivity('initialize_room_statuses', 'Initialized room statuses', null, [
            'created_count' => $createdCount
        ]);

        return redirect()->back()->with('success', 
            "Successfully initialized {$createdCount} room statuses.");
    }
}
