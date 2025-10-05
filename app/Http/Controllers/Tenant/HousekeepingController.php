<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomStatus;
use App\Models\Tenant\HousekeepingTask;
use App\Models\Tenant\MaintenanceRequest;
use App\Models\Tenant\Property;
use App\Models\Tenant\User;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HousekeepingController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view housekeeping')->only(['index', 'show', 'rooms', 'dailyReport']);
        $this->middleware('permission:create housekeeping tasks')->only(['create', 'store']);
        $this->middleware('permission:edit housekeeping tasks')->only(['edit', 'update', 'assignTasks', 'bulkUpdateStatus']);
        $this->middleware('permission:delete housekeeping tasks')->only(['destroy']);
    }

    /**
     * Housekeeping Dashboard - Main overview
     */
    public function index(Request $request)
    {
        $propertyId = $request->get('property_id');
        $properties = Property::all();

        // Get room status summary
        $roomStatusQuery = RoomStatus::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId));

        $roomStatuses = $roomStatusQuery->get();
        
        // Status counts
        $statusCounts = [
            'dirty' => $roomStatuses->where('status', 'dirty')->count(),
            'clean' => $roomStatuses->where('status', 'clean')->count(),
            'inspected' => $roomStatuses->where('status', 'inspected')->count(),
            'maintenance' => $roomStatuses->where('status', 'maintenance')->count(),
            'out_of_order' => $roomStatuses->where('status', 'out_of_order')->count(),
        ];

        // Housekeeping status counts
        $housekeepingCounts = [
            'pending' => $roomStatuses->where('housekeeping_status', 'pending')->count(),
            'in_progress' => $roomStatuses->where('housekeeping_status', 'in_progress')->count(),
            'completed' => $roomStatuses->where('housekeeping_status', 'completed')->count(),
            'inspected' => $roomStatuses->where('housekeeping_status', 'inspected')->count(),
        ];

        // Today's tasks
        $todaysTasks = HousekeepingTask::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereDate('scheduled_for', today())
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_for', 'asc')
            ->get();

        // Overdue tasks
        $overdueTasks = HousekeepingTask::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->overdue()
            ->orderBy('scheduled_for', 'asc')
            ->get();

        // Active maintenance requests
        $maintenanceRequests = MaintenanceRequest::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Staff performance (today)
        $staffPerformance = HousekeepingTask::select('assigned_to')
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks')
            ->selectRaw('AVG(actual_minutes) as avg_time')
            ->with('assignedTo')
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereDate('scheduled_for', today())
            ->groupBy('assigned_to')
            ->having('total_tasks', '>', 0)
            ->get();

        return view('tenant.housekeeping.index', compact(
            'properties',
            'propertyId',
            'roomStatuses',
            'statusCounts',
            'housekeepingCounts',
            'todaysTasks',
            'overdueTasks',
            'maintenanceRequests',
            'staffPerformance'
        ));
    }

    /**
     * Room status overview
     */
    public function rooms(Request $request)
    {
        $propertyId = $request->get('property_id');
        $status = $request->get('status');
        $floor = $request->get('floor');

        $properties = Property::all();

        $roomsQuery = Room::with(['currentStatus.assignedTo', 'type', 'property'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->when($floor, fn($q) => $q->where('floor', $floor))
            ->orderBy('property_id')
            ->orderBy('floor')
            ->orderBy('number');

        if ($status) {
            $roomsQuery->whereHas('currentStatus', function($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $rooms = $roomsQuery->get();
        
        // Get available floors for filter
        $floors = Room::when($propertyId, fn($q) => $q->where('property_id', $propertyId))
                     ->distinct()
                     ->orderBy('floor')
                     ->pluck('floor');

        return view('tenant.housekeeping.rooms', compact(
            'rooms',
            'properties',
            'propertyId',
            'status',
            'floor',
            'floors'
        ));
    }

    /**
     * Assign tasks to staff
     */
    public function assignTasks(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:housekeeping_tasks,id',
            'assigned_to' => 'required|exists:users,id'
        ]);

        $assignedCount = 0;
        
        DB::transaction(function () use ($request, &$assignedCount) {
            $tasks = HousekeepingTask::whereIn('id', $request->task_ids)
                ->where('status', HousekeepingTask::STATUS_PENDING)
                ->get();

            foreach ($tasks as $task) {
                $task->update([
                    'assigned_to' => $request->assigned_to,
                    'status' => HousekeepingTask::STATUS_ASSIGNED,
                    'assigned_at' => now()
                ]);
                $assignedCount++;
            }
        });

        $this->logTenantActivity('assign_tasks','Assigned tasks', null, [
            'task_count' => $assignedCount,
            'assigned_to' => $request->assigned_to,
        ]);

        return redirect()->back()->with('success', 
            "Successfully assigned {$assignedCount} tasks to staff member.");
    }

    /**
     * Quick status update for multiple rooms
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:rooms,id',
            'status' => 'required|in:dirty,clean,inspected,maintenance,out_of_order',
            'housekeeping_status' => 'required|in:pending,in_progress,completed,inspected'
        ]);

        $updatedCount = 0;

        DB::transaction(function () use ($request, &$updatedCount) {
            foreach ($request->room_ids as $roomId) {
                $roomStatus = RoomStatus::where('room_id', $roomId)->first();
                
                if ($roomStatus) {
                    $roomStatus->update([
                        'status' => $request->status,
                        'housekeeping_status' => $request->housekeeping_status,
                        'status_changed_at' => now(),
                        'notes' => $request->notes
                    ]);
                    $updatedCount++;
                }
            }
        });

        $this->logTenantActivity('bulk_update_status', 'Bulk status update', null, [
            'room_count' => $updatedCount,
            'status' => $request->status,
            'housekeeping_status' => $request->housekeeping_status
        ]);

        return redirect()->back()->with('success', 
            "Successfully updated status for {$updatedCount} rooms.");
    }

    /**
     * Generate daily housekeeping report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $propertyId = $request->get('property_id');

        $properties = Property::all();
        $reportDate = Carbon::parse($date);

        // Tasks completed
        $completedTasks = HousekeepingTask::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereDate('completed_at', $reportDate)
            ->orderBy('completed_at')
            ->get();

        // Maintenance requests resolved
        $resolvedMaintenance = MaintenanceRequest::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereDate('completed_at', $reportDate)
            ->orderBy('completed_at')
            ->get();

        // Room status changes
        $statusChanges = RoomStatus::with(['room', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereDate('status_changed_at', $reportDate)
            ->orderBy('status_changed_at')
            ->get();

        // Staff productivity
        $staffStats = HousekeepingTask::select('assigned_to')
            ->selectRaw('COUNT(*) as tasks_assigned')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as tasks_completed')
            ->selectRaw('AVG(actual_minutes) as avg_completion_time')
            ->selectRaw('SUM(actual_minutes) as total_time')
            ->with('assignedTo')
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereDate('scheduled_for', $reportDate)
            ->groupBy('assigned_to')
            ->get();

        return view('tenant.housekeeping.daily-report', compact(
            'properties',
            'propertyId',
            'reportDate',
            'completedTasks',
            'resolvedMaintenance',
            'statusChanges',
            'staffStats'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // This can be used for individual housekeeping task details
        $task = HousekeepingTask::with(['room', 'property', 'assignedTo', 'createdBy', 'booking'])
                               ->findOrFail($id);

        return view('tenant.housekeeping.show', compact('task'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $properties = Property::all();
        $rooms = Room::with('type')->get();
        $staff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%housekeeping%');
        })->get();

        return view('tenant.housekeeping.create', compact('properties', 'rooms', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'property_id' => 'required|exists:properties,id',
            'task_type' => 'required|in:cleaning,maintenance,inspection,deep_clean,setup',
            'priority' => 'required|in:low,normal,high,urgent',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:1',
            'scheduled_for' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $task = HousekeepingTask::create([
            'room_id' => $request->room_id,
            'property_id' => $request->property_id,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'task_type' => $request->task_type,
            'priority' => $request->priority,
            'status' => $request->assigned_to ? 
                       HousekeepingTask::STATUS_ASSIGNED : 
                       HousekeepingTask::STATUS_PENDING,
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'estimated_minutes' => $request->estimated_minutes,
            'scheduled_for' => $request->scheduled_for,
            'assigned_at' => $request->assigned_to ? now() : null,
        ]);

        $this->logTenantActivity('create_task', 'Created housekeeping task', $task, [
            'task_id' => $task->id,
            'room_id' => $task->room_id,
            'task_type' => $task->task_type
        ]);

        return redirect()->route('tenant.housekeeping.index')
                        ->with('success', 'Housekeeping task created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task = HousekeepingTask::findOrFail($id);
        $properties = Property::all();
        $rooms = Room::with('type')->get();
        $staff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%housekeeping%');
        })->get();

        return view('tenant.housekeeping.edit', compact('task', 'properties', 'rooms', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = HousekeepingTask::findOrFail($id);

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'property_id' => 'required|exists:properties,id',
            'task_type' => 'required|in:cleaning,maintenance,inspection,deep_clean,setup',
            'priority' => 'required|in:low,normal,high,urgent',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:1',
            'scheduled_for' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $wasAssigned = $task->assigned_to !== null;
        $newAssignee = $request->assigned_to;

        $task->update($request->all());

        // Update assignment status if changed
        if (!$wasAssigned && $newAssignee) {
            $task->update([
                'status' => HousekeepingTask::STATUS_ASSIGNED,
                'assigned_at' => now()
            ]);
        } elseif ($wasAssigned && !$newAssignee) {
            $task->update([
                'status' => HousekeepingTask::STATUS_PENDING,
                'assigned_at' => null
            ]);
        }

        $this->logTenantActivity('update_task', 'Updated housekeeping task', $task, [
            'task_id' => $task->id,
            'changes' => $task->getChanges()
        ]);

        return redirect()->route('tenant.housekeeping.index')
                        ->with('success', 'Housekeeping task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = HousekeepingTask::findOrFail($id);
        
        // Only allow deletion of pending or cancelled tasks
        if (!in_array($task->status, [HousekeepingTask::STATUS_PENDING, HousekeepingTask::STATUS_CANCELLED])) {
            return redirect()->back()->with('error', 
                'Only pending or cancelled tasks can be deleted.');
        }

        $this->logTenantActivity('delete_task', 'Deleted housekeeping task', $task, [
            'task_id' => $task->id,
            'room_id' => $task->room_id,
            'task_type' => $task->task_type
        ]);

        $task->delete();

        return redirect()->route('tenant.housekeeping.index')
                        ->with('success', 'Housekeeping task deleted successfully.');
    }

    /**
     * Start a housekeeping task
     */
    public function start(string $id)
    {
        $task = HousekeepingTask::findOrFail($id);
        
        if (!$task->canStart()) {
            return redirect()->back()->with('error', 
                'This task cannot be started. It must be assigned first.');
        }

        $task->update([
            'status' => HousekeepingTask::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'assigned_to' => $task->assigned_to ?: Auth::id()
        ]);

        $this->logTenantActivity('start_task', 'Started housekeeping task', $task, [
            'task_id' => $task->id,
            'room_id' => $task->room_id,
            'started_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Task started successfully.');
    }

    /**
     * Complete a housekeeping task
     */
    public function complete(Request $request, string $id)
    {
        $task = HousekeepingTask::findOrFail($id);
        
        if (!$task->canComplete()) {
            return redirect()->back()->with('error', 
                'This task cannot be completed. It must be in progress first.');
        }

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        $task->update([
            'status' => HousekeepingTask::STATUS_COMPLETED,
            'completed_at' => now(),
            'completion_notes' => $request->completion_notes,
            'actual_minutes' => $task->started_at ? $task->started_at->diffInMinutes(now()) : null
        ]);

        $this->logTenantActivity('complete_task', 'Completed housekeeping task', $task, [
            'task_id' => $task->id,
            'room_id' => $task->room_id,
            'completed_by' => Auth::id(),
            'duration_minutes' => $task->actual_minutes
        ]);

        return redirect()->back()->with('success', 'Task completed successfully.');
    }

    /**
     * Cancel a housekeeping task
     */
    public function cancel(Request $request, string $id)
    {
        $task = HousekeepingTask::findOrFail($id);
        
        if ($task->status === HousekeepingTask::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 
                'Completed tasks cannot be cancelled.');
        }

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        $task->update([
            'status' => HousekeepingTask::STATUS_CANCELLED,
            'completion_notes' => $request->completion_notes
        ]);

        $this->logTenantActivity('cancel_task', 'Cancelled housekeeping task', $task, [
            'task_id' => $task->id,
            'room_id' => $task->room_id,
            'cancelled_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Task cancelled successfully.');
    }

    /**
     * Assign a task to a staff member
     */
    public function assign(Request $request, string $id)
    {
        $task = HousekeepingTask::findOrFail($id);
        
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $task->update([
            'assigned_to' => $request->assigned_to,
            'status' => HousekeepingTask::STATUS_ASSIGNED,
            'assigned_at' => now()
        ]);

        $this->logTenantActivity('assign_task', 'Assigned housekeeping task', $task, [
            'task_id' => $task->id,
            'room_id' => $task->room_id,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Task assigned successfully.');
    }
}
