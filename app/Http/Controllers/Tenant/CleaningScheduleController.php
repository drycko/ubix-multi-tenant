<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\CleaningChecklist;
use App\Models\Tenant\HousekeepingTask;
use App\Models\Tenant\Property;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Room;
use App\Models\Tenant\User;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleaningScheduleController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view cleaning schedules')->only(['index', 'show', 'calendar']);
        $this->middleware('permission:create cleaning schedules')->only(['create', 'store', 'generateSchedule', 'loadDefaults']);
        $this->middleware('permission:edit cleaning schedules')->only(['edit', 'update', 'updateOrder', 'duplicate']);
        $this->middleware('permission:delete cleaning schedules')->only(['destroy']);
    }

    /**
     * Display cleaning checklists
     */
    public function index(Request $request)
    {
        $propertyId = $request->get('property_id');
        $type = $request->get('type');

        $properties = Property::all();

        $checklistsQuery = CleaningChecklist::with(['property', 'roomType'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->when($type, fn($q) => $q->where('checklist_type', $type))
            ->orderBy('display_order')
            ->orderBy('name');

        $checklists = $checklistsQuery->get();

        // Get available types
        $types = CleaningChecklist::select('checklist_type')->distinct()->pluck('checklist_type');

        // get allowed task types from HousekeepingTask model
        $taskTypes = HousekeepingTask::TASK_TYPES;

        return view('tenant.cleaning-schedule.index', compact(
            'checklists',
            'properties',
            'propertyId',
            'type',
            'types',
            'taskTypes'
        ));
    }

    /**
     * Show cleaning schedule calendar
     */
    public function calendar(Request $request)
    {
        $propertyId = $request->get('property_id');
        $date = $request->get('date', today()->format('Y-m-d'));

        $properties = Property::all();
        $viewDate = Carbon::parse($date);

        // Get week start and end
        $weekStart = $viewDate->copy()->startOfWeek();
        $weekEnd = $viewDate->copy()->endOfWeek();

        // Get tasks for the week
        $tasks = HousekeepingTask::with(['room.type', 'assignedTo', 'property'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereBetween('scheduled_for', [$weekStart, $weekEnd])
            ->orderBy('scheduled_for')
            ->orderBy('room_id')
            ->get();

        // Get housekeeping staff
        $staff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%housekeeping%');
        })->get();

        // Group tasks by day
        $weeklyTasks = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $weeklyTasks[$day->format('Y-m-d')] = $tasks->filter(function($task) use ($day) {
                return $task->scheduled_for->format('Y-m-d') === $day->format('Y-m-d');
            });
        }

        return view('tenant.cleaning-schedule.calendar', compact(
            'properties',
            'propertyId',
            'viewDate',
            'weekStart',
            'weekEnd',
            'weeklyTasks',
            'staff'
        ));
    }

    /**
     * Show the form for creating a new checklist.
     */
    public function create()
    {
        $properties = Property::all();
        $roomTypes = RoomType::all();

        return view('tenant.cleaning-schedule.create', compact('properties', 'roomTypes'));
    }

    /**
     * Store a newly created checklist.
     */
    public function store(Request $request)
    {
        try {
            // Validate basic fields first
            $request->validate([
                'property_id' => 'required|exists:properties,id',
                'room_type_id' => 'nullable|exists:room_types,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'checklist_type' => 'required|in:standard,checkout,deep_clean,maintenance,inspection',
                'estimated_minutes' => 'nullable|integer|min:1',
                'checklist_items' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            // Convert checkbox items to the expected format
            $items = collect($request->checklist_items)->map(function($item) {
                return [
                    'item' => $item,
                    'required' => true // All selected items are considered required
                ];
            })->toArray();

            $checklist = CleaningChecklist::create([
                'property_id' => $request->property_id,
                'room_type_id' => $request->room_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'checklist_type' => $request->checklist_type,
                'items' => $items,
                'estimated_minutes' => $request->estimated_minutes,
                'display_order' => CleaningChecklist::where('property_id', $request->property_id)->max('display_order') + 1
            ]);

            $this->logTenantActivity(
                'create_cleaning_checklist',
                'Created cleaning checklist',
                $checklist,
                [
                    'checklist_id' => $checklist->id,
                    'name' => $checklist->name,
                    'type' => $checklist->checklist_type,
                    'items_count' => count($items)
                ]
            );
            
            DB::commit();

            return redirect()->route('tenant.cleaning-schedule.index')
                            ->with('success', 'Cleaning checklist created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating cleaning checklist: ' . $e->getMessage());
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating checklist: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified checklist.
     */
    public function show(string $id)
    {
        $checklist = CleaningChecklist::with(['property', 'roomType'])
                                     ->findOrFail($id);

        // Get recent tasks using this checklist
        $recentTasks = HousekeepingTask::with(['room', 'assignedTo'])
            ->where('task_type', 'like', '%' . $checklist->checklist_type . '%')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('tenant.cleaning-schedule.show', compact('checklist', 'recentTasks'));
    }

    /**
     * Show the form for editing the specified checklist.
     */
    public function edit(string $id)
    {
        $checklist = CleaningChecklist::findOrFail($id);
        $properties = Property::all();
        $roomTypes = RoomType::all();

        return view('tenant.cleaning-schedule.edit', compact('checklist', 'properties', 'roomTypes'));
    }

    /**
     * Update the specified checklist.
     */
    public function update(Request $request, string $id)
    {
        $checklist = CleaningChecklist::findOrFail($id);

        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'checklist_type' => 'required|in:standard,checkout,deep_clean,maintenance,inspection',
            'estimated_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
            'checklist_items' => 'required|array|min:1'
        ]);

        // Convert checkbox items to the expected format (for edit form compatibility)
        $items = collect($request->checklist_items)->map(function($item) {
            return [
                'item' => $item,
                'required' => true // All selected items are considered required
            ];
        })->toArray();

        $checklist->update([
            'property_id' => $request->property_id,
            'room_type_id' => $request->room_type_id,
            'name' => $request->name,
            'description' => $request->description,
            'checklist_type' => $request->checklist_type,
            'items' => $items,
            'estimated_minutes' => $request->estimated_minutes,
            'is_active' => $request->boolean('is_active'),
            'display_order' => $request->display_order ?? $checklist->display_order
        ]);

        $this->logTenantActivity('update_cleaning_checklist', 'Updated cleaning checklist', $checklist, [
            'checklist_id' => $checklist->id,
            'changes' => $checklist->getChanges()
        ]);

        return redirect()->route('tenant.cleaning-schedule.index')
                        ->with('success', 'Cleaning checklist updated successfully.');
    }

    /**
     * Generate housekeeping schedule
     */
    public function generateSchedule(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'date_range' => 'required|in:today,tomorrow,week,month',
            'task_type' => 'required|in:' . implode(',', HousekeepingTask::TASK_TYPES),
            'assigned_to' => 'required|exists:users,id'
        ]);

        $propertyId = $request->property_id;
        $taskType = $request->task_type;
        $assignedTo = $request->assigned_to;

        // Get date range
        $startDate = match($request->date_range) {
            'today' => today(),
            'tomorrow' => today()->addDay(),
            'week' => today(),
            'month' => today(),
        };

        $endDate = match($request->date_range) {
            'today', 'tomorrow' => $startDate->copy(),
            'week' => $startDate->copy()->addWeek(),
            'month' => $startDate->copy()->addMonth(),
        };

        // Get rooms for the property
        $rooms = Room::where('property_id', $propertyId)
                    ->where('is_enabled', true)
                    ->get();

        // Get appropriate checklist
        $checklist = CleaningChecklist::where('property_id', $propertyId)
                                     ->where('checklist_type', $taskType === 'cleaning' ? 'standard' : $taskType)
                                     ->where('is_active', true)
                                     ->first();

        $createdTasks = 0;
        $currentDate = $startDate->copy();

        DB::transaction(function () use ($rooms, $checklist, $taskType, $assignedTo, $currentDate, $endDate, &$createdTasks) {
            while ($currentDate->lte($endDate)) {
                // Skip weekends for regular cleaning (optional)
                if ($taskType === 'cleaning' && $currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                foreach ($rooms as $room) {
                    // Check if task already exists for this room and date
                    $existingTask = HousekeepingTask::where('room_id', $room->id)
                        ->where('task_type', $taskType)
                        ->whereDate('scheduled_for', $currentDate)
                        ->exists();

                    if (!$existingTask) {
                        $scheduledTime = $currentDate->copy()->setTime(8, 0); // Start at 8 AM

                        HousekeepingTask::create([
                            'room_id' => $room->id,
                            'property_id' => $room->property_id,
                            'created_by' => Auth::id(),
                            'assigned_to' => $assignedTo,
                            'task_type' => $taskType,
                            'priority' => 'normal',
                            'status' => $assignedTo ? 
                                       HousekeepingTask::STATUS_ASSIGNED : 
                                       HousekeepingTask::STATUS_PENDING,
                            'title' => ucfirst($taskType) . ' - Room ' . $room->number,
                            'description' => 'Scheduled ' . $taskType . ' task',
                            'checklist_items' => $checklist ? $checklist->createTaskInstance() : null,
                            'estimated_minutes' => $checklist?->estimated_minutes ?? 30,
                            'scheduled_for' => $scheduledTime,
                            'assigned_at' => $assignedTo ? now() : null,
                        ]);

                        $createdTasks++;
                    }
                }

                $currentDate->addDay();
            }
        });

        $this->logTenantActivity('generate_housekeeping_schedule', 'Generated housekeeping schedule', null, [
            'property_id' => $propertyId,
            'task_type' => $taskType,
            'date_range' => $request->date_range,
            'tasks_created' => $createdTasks
        ]);

        return redirect()->back()->with('success', 
            "Successfully generated {$createdTasks} housekeeping tasks.");
    }

    /**
     * Bulk update checklist order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'checklist_orders' => 'required|array',
            'checklist_orders.*' => 'integer'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->checklist_orders as $checklistId => $order) {
                CleaningChecklist::where('id', $checklistId)->update(['display_order' => $order]);
            }
        });

        $this->logTenantActivity('update_checklist_order', 'Updated checklist order', null, [
            'updated_count' => count($request->checklist_orders)
        ]);

        return redirect()->back()->with('success', 'Checklist order updated successfully.');
    }

    /**
     * Duplicate checklist
     */
    public function duplicate(string $id)
    {
        $originalChecklist = CleaningChecklist::findOrFail($id);

        $newChecklist = $originalChecklist->replicate();
        $newChecklist->name = $originalChecklist->name . ' (Copy)';
        $newChecklist->display_order = CleaningChecklist::where('property_id', $originalChecklist->property_id)
                                                       ->max('display_order') + 1;
        $newChecklist->save();

        $this->logTenantActivity('duplicate_cleaning_checklist', 'Duplicated cleaning checklist', $newChecklist, [
            'original_id' => $originalChecklist->id,
            'new_id' => $newChecklist->id,
            'name' => $newChecklist->name
        ]);

        return redirect()->back()->with('success', 'Checklist duplicated successfully.');
    }

    /**
     * Load default checklists
     */
    public function loadDefaults(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id'
        ]);

        $propertyId = $request->property_id;
        $createdCount = 0;

        DB::transaction(function () use ($propertyId, &$createdCount) {
            $defaultTypes = [
                CleaningChecklist::TYPE_STANDARD,
                CleaningChecklist::TYPE_CHECKOUT,
                CleaningChecklist::TYPE_DEEP_CLEAN,
                CleaningChecklist::TYPE_INSPECTION
            ];

            foreach ($defaultTypes as $type) {
                // Check if checklist already exists
                $exists = CleaningChecklist::where('property_id', $propertyId)
                                         ->where('checklist_type', $type)
                                         ->exists();

                if (!$exists) {
                    CleaningChecklist::create([
                        'property_id' => $propertyId,
                        'name' => ucfirst(str_replace('_', ' ', $type)) . ' Checklist',
                        'description' => 'Default ' . str_replace('_', ' ', $type) . ' checklist',
                        'checklist_type' => $type,
                        'items' => CleaningChecklist::getDefaultItems($type),
                        'estimated_minutes' => match($type) {
                            CleaningChecklist::TYPE_STANDARD => 30,
                            CleaningChecklist::TYPE_CHECKOUT => 20,
                            CleaningChecklist::TYPE_DEEP_CLEAN => 60,
                            CleaningChecklist::TYPE_INSPECTION => 15,
                            default => 30
                        },
                        'display_order' => $createdCount + 1
                    ]);

                    $createdCount++;
                }
            }
        });

        $this->logTenantActivity('load_default_checklists', 'Loaded default checklists', null, [
            'property_id' => $propertyId,
            'created_count' => $createdCount
        ]);

        return redirect()->back()->with('success', 
            "Successfully created {$createdCount} default checklists.");
    }

    /**
     * Remove the specified checklist.
     */
    public function destroy(string $id)
    {
        $checklist = CleaningChecklist::findOrFail($id);

        // Check if checklist is being used in active tasks
        $activeTasks = HousekeepingTask::where('checklist_items', 'like', '%' . $checklist->name . '%')
                                      ->whereIn('status', [
                                          HousekeepingTask::STATUS_PENDING,
                                          HousekeepingTask::STATUS_ASSIGNED,
                                          HousekeepingTask::STATUS_IN_PROGRESS
                                      ])
                                      ->count();

        if ($activeTasks > 0) {
            return redirect()->back()->with('error', 
                "Cannot delete checklist. It is currently being used in {$activeTasks} active tasks.");
        }

        $checklistName = $checklist->name;

        $this->logTenantActivity('delete_cleaning_checklist', 'Deleted cleaning checklist', $checklist, [
            'checklist_id' => $checklist->id,
            'name' => $checklistName
        ]);

        $checklist->delete();

        return redirect()->route('tenant.cleaning-schedule.index')
                        ->with('success', "Cleaning checklist '{$checklistName}' deleted successfully.");
    }

    /**
     * Print view of the cleaning checklist.
     */
    public function print(string $id)
    {
        $checklist = CleaningChecklist::with([
            'property', 
            'roomType'
        ])->findOrFail($id);
        
        $property = current_property();

        return view('tenant.cleaning-schedule.print', compact('checklist', 'property'));
    }
}
