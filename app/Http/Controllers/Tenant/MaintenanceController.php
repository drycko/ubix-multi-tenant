<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\MaintenanceRequest;
use App\Models\Tenant\MaintenanceTask;
use App\Models\Tenant\StaffHour;
use App\Models\Tenant\Room;
use App\Models\Tenant\Property;
use App\Models\Tenant\User;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view maintenance')->only(['index', 'show', 'dashboard', 'tasks', 'getTask']);
        $this->middleware('permission:create maintenance requests')->only(['create', 'store', 'createTask']);
        $this->middleware('permission:edit maintenance requests')->only(['edit', 'update', 'assign', 'start', 'complete', 'cancel', 'hold', 'addWorkLog', 'updateTaskStatus', 'updateTask']);
        $this->middleware('permission:delete maintenance requests')->only(['destroy']);
    }

    /**
     * Display maintenance requests
     */
    public function index(Request $request)
    {
        $propertyId = $request->get('property_id');
        $status = $request->get('status');
        $category = $request->get('category');
        $priority = $request->get('priority');

        $properties = Property::all();

        $requestsQuery = MaintenanceRequest::with(['room', 'property', 'reportedBy', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($priority, fn($q) => $q->where('priority', $priority))
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        $maintenanceRequests = $requestsQuery->paginate(20);

        // Get filter options
        $statuses = MaintenanceRequest::select('status')->distinct()->pluck('status');
        $categories = MaintenanceRequest::select('category')->distinct()->pluck('category');
        $priorities = MaintenanceRequest::select('priority')->distinct()->pluck('priority');

        // Get maintenance staff
        $maintenanceStaff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%maintenance%');
        })->get();

        // Summary stats
        $stats = [
            'total' => MaintenanceRequest::when($propertyId, fn($q) => $q->where('property_id', $propertyId))->count(),
            'pending' => MaintenanceRequest::when($propertyId, fn($q) => $q->where('property_id', $propertyId))->where('status', 'pending')->count(),
            'in_progress' => MaintenanceRequest::when($propertyId, fn($q) => $q->where('property_id', $propertyId))->where('status', 'in_progress')->count(),
            'urgent' => MaintenanceRequest::when($propertyId, fn($q) => $q->where('property_id', $propertyId))->where('priority', 'urgent')->count(),
        ];

        return view('tenant.maintenance.index', compact(
            'maintenanceRequests',
            'properties',
            'propertyId',
            'status',
            'category',
            'priority',
            'statuses',
            'categories',
            'priorities',
            'maintenanceStaff',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $properties = Property::all();
        $rooms = Room::with('type')->get();
        $maintenanceStaff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%maintenance%');
        })->get();
        $allUsers = User::all();
        $categories = MaintenanceRequest::SUPPORTED_CATEGORIES;
        $priorities = MaintenanceRequest::PRIORITY_OPTIONS;

        return view('tenant.maintenance.create', compact('properties', 'rooms', 'maintenanceStaff', 'allUsers', 'categories', 'priorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // start transaction
            DB::beginTransaction();

            // Validate input
            $request->validate([
                'room_id' => 'nullable|exists:rooms,id',
                'property_id' => 'required|exists:properties,id',
                'category' => 'required|in:' . implode(',', MaintenanceRequest::SUPPORTED_CATEGORIES),
                'priority' => 'required|in:' . implode(',', MaintenanceRequest::PRIORITY_OPTIONS),
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location_details' => 'nullable|string|max:500',
                'estimated_cost' => 'nullable|numeric|min:0',
                'requires_room_closure' => 'boolean',
                'scheduled_for' => 'nullable|date|after:now',
                'assigned_to' => 'nullable|exists:users,id',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $images = [];
            if ($request->hasFile('photos')) {
                \Log::info('Files detected in request: ' . count($request->file('photos')));
                $tenant_id = tenant('id');
                
                foreach ($request->file('photos') as $image) {
                    // Initialize image path variable
                    $imagePath = null;
                    // debugging line
                    \Log::info('Uploading image: ' . $image->getClientOriginalName());

                    // Handle image upload to Google Cloud Storage if in production
                    if ($image && config('app.env') === 'production') {
                        $gcsPath = 'tenant' . $tenant_id . '/maintenance-images/' . uniqid() . '_' . $image->getClientOriginalName();
                        $stream = fopen($image->getRealPath(), 'r');
                        Storage::disk('gcs')->put($gcsPath, $stream);
                        fclose($stream);
                        $imagePath = $gcsPath;
                        \Log::info('Image uploaded to GCS: ' . $gcsPath);
                    } elseif ($image) {
                        // If not in production, handle local storage
                        // tenant already stores the files in tenant specific folder like (storage\tenant1\app\public\maintenance-images)
                        $imagePath = $image->store('maintenance-images', 'public');
                        \Log::info('Image uploaded locally: ' . $imagePath);
                    }
                    
                    // Add the path to images array if upload was successful
                    if ($imagePath) {
                        $images[] = $imagePath;
                        \Log::info('Image path added to array: ' . $imagePath);
                    }
                }
            } else {
                \Log::info('No files detected in request');
            }
            
            \Log::info('Total images to save: ' . count($images));

            $maintenanceRequest = MaintenanceRequest::create([
                'room_id' => $request->room_id,
                'property_id' => $request->property_id,
                'reported_by' => Auth::id(),
                'assigned_to' => $request->assigned_to,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => $request->assigned_to ? 
                        MaintenanceRequest::STATUS_ASSIGNED : 
                        MaintenanceRequest::STATUS_PENDING,
                'title' => $request->title,
                'description' => $request->description,
                'location_details' => $request->location_details,
                'estimated_cost' => $request->estimated_cost,
                'requires_room_closure' => $request->boolean('requires_room_closure'),
                'scheduled_for' => $request->scheduled_for,
                'images' => $images,
                'assigned_at' => $request->assigned_to ? now() : null,
            ]);

            \Log::info('Maintenance request created with images: ' . json_encode($maintenanceRequest->images));

            $this->logTenantActivity('create_request', 'Created maintenance request', $maintenanceRequest, [
                'request_id' => $maintenanceRequest->id,
                'request_number' => $maintenanceRequest->request_number,
                'room_id' => $maintenanceRequest->room_id,
                'category' => $maintenanceRequest->category,
                'priority' => $maintenanceRequest->priority
            ]);

            DB::commit();

            return redirect()->route('tenant.maintenance.index')
                            ->with('success', 'Maintenance request created successfully. Request #' . $maintenanceRequest->request_number);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Error creating maintenance request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $maintenanceRequest = MaintenanceRequest::with([
            'room.type', 
            'property', 
            'reportedBy', 
            'assignedTo',
            'maintenanceTasks.assignedTo',
            'maintenanceTasks.staffHours.user',
            'workLogs.user'
        ])->findOrFail($id);
        
        // Get maintenance staff for task assignment
        $maintenanceStaff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%maintenance%');
        })->get();

        return view('tenant.maintenance.show', compact('maintenanceRequest', 'maintenanceStaff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $properties = Property::all();
        $rooms = Room::with('type')->get();
        $maintenanceStaff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%maintenance%');
        })->get();
        $allUsers = User::all();

        return view('tenant.maintenance.edit', compact(
            'maintenanceRequest',
            'properties',
            'rooms',
            'maintenanceStaff',
            'allUsers'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'property_id' => 'required|exists:properties,id',
            'reported_by' => 'required|exists:users,id',
            'category' => 'required|in:' . implode(',', MaintenanceRequest::SUPPORTED_CATEGORIES),
            'priority' => 'required|in:' . implode(',', MaintenanceRequest::PRIORITY_OPTIONS),
            'status' => 'required|in:' . implode(',', MaintenanceRequest::STATUS_OPTIONS),
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location_details' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'requires_room_closure' => 'boolean',
            'scheduled_for' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
            'parts_used' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048'
        ]);

        $images = $maintenanceRequest->images ?? [];
        if ($request->hasFile('photos')) {
            $tenant_id = tenant('id');
            
            foreach ($request->file('photos') as $image) {
                $imagePath = null;
                
                // Handle image upload to Google Cloud Storage if in production
                if ($image && config('app.env') === 'production') {
                    $gcsPath = 'tenant' . $tenant_id . '/maintenance-images/' . uniqid() . '_' . $image->getClientOriginalName();
                    $stream = fopen($image->getRealPath(), 'r');
                    Storage::disk('gcs')->put($gcsPath, $stream);
                    fclose($stream);
                    $imagePath = $gcsPath;
                } elseif ($image) {
                    // If not in production, handle local storage
                    $imagePath = $image->store('maintenance-images', 'public');
                }
                
                // Add the path to images array if upload was successful
                if ($imagePath) {
                    $images[] = $imagePath;
                }
            }
        }

        $wasAssigned = $maintenanceRequest->assigned_to !== null;
        $newAssignee = $request->assigned_to;
        $oldStatus = $maintenanceRequest->status;
        $newStatus = $request->status;

        // Prepare update data
        $updateData = [
            'room_id' => $request->room_id,
            'property_id' => $request->property_id,
            'reported_by' => $request->reported_by,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => $request->status,
            'title' => $request->title,
            'description' => $request->description,
            'location_details' => $request->location_details,
            'estimated_cost' => $request->estimated_cost,
            'actual_cost' => $request->actual_cost,
            'requires_room_closure' => $request->boolean('requires_room_closure'),
            'scheduled_for' => $request->scheduled_for,
            'assigned_to' => $request->assigned_to,
            'resolution_notes' => $request->resolution_notes,
            'parts_used' => $request->parts_used,
            'images' => $images,
        ];

        // Handle status-based timestamp updates
        if ($oldStatus !== $newStatus) {
            switch ($newStatus) {
                case MaintenanceRequest::STATUS_ASSIGNED:
                    if ($oldStatus === MaintenanceRequest::STATUS_PENDING) {
                        $updateData['assigned_at'] = now();
                    }
                    break;
                case MaintenanceRequest::STATUS_IN_PROGRESS:
                    if ($oldStatus !== MaintenanceRequest::STATUS_IN_PROGRESS) {
                        $updateData['started_at'] = now();
                    }
                    break;
                case MaintenanceRequest::STATUS_COMPLETED:
                    if ($oldStatus !== MaintenanceRequest::STATUS_COMPLETED) {
                        $updateData['completed_at'] = now();
                    }
                    break;
                case MaintenanceRequest::STATUS_PENDING:
                    // Reset timestamps if moved back to pending
                    $updateData['assigned_at'] = null;
                    $updateData['started_at'] = null;
                    $updateData['completed_at'] = null;
                    break;
            }
        }

        // Handle assignment changes
        if (!$wasAssigned && $newAssignee && $newStatus === MaintenanceRequest::STATUS_PENDING) {
            $updateData['status'] = MaintenanceRequest::STATUS_ASSIGNED;
            $updateData['assigned_at'] = now();
        } elseif ($wasAssigned && !$newAssignee) {
            $updateData['status'] = MaintenanceRequest::STATUS_PENDING;
            $updateData['assigned_at'] = null;
        } elseif ($newAssignee && $oldStatus === MaintenanceRequest::STATUS_PENDING) {
            $updateData['assigned_at'] = now();
        }

        $maintenanceRequest->update($updateData);

        $this->logTenantUserActivity('update_request', 'Updated maintenance request', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'changes' => $maintenanceRequest->getChanges()
        ]);

        return redirect()->route('tenant.maintenance.index')
                        ->with('success', 'Maintenance request updated successfully.');
    }

    /**
     * Assign maintenance request to staff
     */
    public function assign(Request $request, string $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'scheduled_for' => 'nullable|date|after:now'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        if ($maintenanceRequest->status !== MaintenanceRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', 
                'Only pending maintenance requests can be assigned.');
        }

        $maintenanceRequest->update([
            'assigned_to' => $request->assigned_to,
            'status' => MaintenanceRequest::STATUS_ASSIGNED,
            'scheduled_for' => $request->scheduled_for,
            'assigned_at' => now()
        ]);

        $this->logTenantActivity('assign_request', 'Assigned maintenance request', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'assigned_to' => $request->assigned_to
        ]);

        return redirect()->back()->with('success', 
            'Maintenance request assigned successfully.');
    }

    /**
     * Start maintenance work
     */
    public function start(Request $request, string $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        if (!$maintenanceRequest->canStart()) {
            return redirect()->back()->with('error', 
                'Maintenance work cannot be started.');
        }

        $maintenanceRequest->update([
            'status' => MaintenanceRequest::STATUS_IN_PROGRESS,
            'started_at' => now()
        ]);

        $this->logTenantActivity('start_request', 'Started maintenance work', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number
        ]);

        return redirect()->back()->with('success', 
            'Maintenance work started.');
    }

    /**
     * Complete maintenance work
     */
    public function complete(Request $request, string $id)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
            'parts_used' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        if (!$maintenanceRequest->canComplete()) {
            return redirect()->back()->with('error', 
                'Maintenance work cannot be completed.');
        }

        $maintenanceRequest->update([
            'status' => MaintenanceRequest::STATUS_COMPLETED,
            'resolution_notes' => $request->resolution_notes,
            'parts_used' => $request->parts_used,
            'actual_cost' => $request->actual_cost,
            'completed_at' => now()
        ]);

        $this->logTenantActivity('complete_request', 'Completed maintenance work', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'actual_cost' => $request->actual_cost
        ]);

        return redirect()->back()->with('success', 
            'Maintenance work completed successfully.');
    }

    /**
     * Cancel maintenance request
     */
    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        if ($maintenanceRequest->status === MaintenanceRequest::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 
                'Completed maintenance requests cannot be cancelled.');
        }

        $maintenanceRequest->update([
            'status' => MaintenanceRequest::STATUS_CANCELLED,
            'resolution_notes' => 'CANCELLED: ' . $request->cancellation_reason
        ]);

        $this->logTenantActivity('cancel_request', 'Cancelled maintenance request', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'reason' => $request->cancellation_reason
        ]);

        return redirect()->back()->with('success', 
            'Maintenance request cancelled.');
    }

    /**
     * Put maintenance request on hold
     */
    public function hold(Request $request, string $id)
    {
        $request->validate([
            'hold_reason' => 'required|string|max:500'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $maintenanceRequest->update([
            'status' => MaintenanceRequest::STATUS_ON_HOLD,
            'resolution_notes' => 'ON HOLD: ' . $request->hold_reason
        ]);

        $this->logTenantActivity('hold_request', 'Put maintenance request on hold', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'reason' => $request->hold_reason
        ]);

        return redirect()->back()->with('success', 
            'Maintenance request put on hold.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        // Only allow deletion of pending or cancelled requests
        if (!in_array($maintenanceRequest->status, [
            MaintenanceRequest::STATUS_PENDING, 
            MaintenanceRequest::STATUS_CANCELLED
        ])) {
            return redirect()->back()->with('error', 
                'Only pending or cancelled maintenance requests can be deleted.');
        }

        // Delete associated images
        if ($maintenanceRequest->images) {
            foreach ($maintenanceRequest->images as $image) {
                // Delete from appropriate storage based on environment and path
                if (config('app.env') === 'production' && strpos($image, 'tenant') === 0) {
                    // GCS path format: tenant{id}/maintenance-images/...
                    Storage::disk('gcs')->delete($image);
                } else {
                    // Local storage
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $requestNumber = $maintenanceRequest->request_number;

        $this->logTenantActivity('delete_request', 'Deleted maintenance request', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $requestNumber
        ]);

        $maintenanceRequest->delete();

        return redirect()->route('tenant.maintenance.index')
                        ->with('success', "Maintenance request #{$requestNumber} deleted successfully.");
    }

    /**
     * Add work log entry to maintenance request
     */
    public function addWorkLog(Request $request, string $id)
    {

        try {
            $request->validate([
                'description' => 'required|string|max:1000',
                'work_date' => 'nullable|date|before_or_equal:today',
                'task_type' => 'required|in:diagnosis,repair,replacement,testing,cleanup,documentation',
                'time_method' => 'required|in:hours,times',
                'hours_spent' => 'required|numeric|min:0.1|max:24',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'materials_used' => 'nullable|string|max:500',
                'cost' => 'nullable|numeric|min:0|max:999999.99',
                'notes' => 'nullable|string|max:500',
                'is_complete' => 'boolean'
            ]);

            $maintenanceRequest = MaintenanceRequest::findOrFail($id);
            DB::beginTransaction();

            // Create or find maintenance task for this work log
            $maintenanceTask = MaintenanceTask::firstOrCreate([
                'maintenance_request_id' => $maintenanceRequest->id,
                'assigned_to' => $maintenanceRequest->assigned_to ?? Auth::id(),
                'status' => MaintenanceTask::STATUS_IN_PROGRESS
            ], [
                'created_by' => Auth::id(),
                'task_type' => $request->task_type,
                'priority' => $maintenanceRequest->priority,
                'title' => 'Work Session - ' . ucfirst($request->task_type),
                'description' => 'Task for tracking work sessions on this maintenance request',
                'assigned_at' => now(),
                'started_at' => now(),
            ]);

            // Prepare work date and times
            $workDate = $request->work_date ?: now()->toDateString();
            $hoursWorked = $request->hours_spent;
            
            // Calculate start/end times based on method
            if ($request->time_method === 'times' && $request->start_time && $request->end_time) {
                $startTime = $request->start_time;
                $endTime = $request->end_time;
            } else {
                // Calculate times based on hours worked
                $endTime = now()->format('H:i');
                $startTime = now()->subHours($hoursWorked)->format('H:i');
            }

            // Create staff hour entry
            $user = Auth::user();
            
            $staffHour = StaffHour::create([
                'user_id' => Auth::id(),
                'property_id' => $maintenanceRequest->property_id,
                'task_type' => MaintenanceTask::class,
                'task_id' => $maintenanceTask->id,
                'work_type' => StaffHour::WORK_TYPE_MAINTENANCE,
                'description' => $request->description,
                'hours_worked' => $hoursWorked,
                'hourly_rate' => $user->hourly_rate ?? 0,
                'work_date' => $workDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_overtime' => $hoursWorked > 8,
                'is_approved' => false,
                'notes' => $request->notes,
            ]);

            // Update maintenance task with completion notes and materials
            $workLogEntry = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'user' => $user->name,
                'description' => $request->description,
                'hours' => $hoursWorked,
                'materials' => $request->materials_used,
                'cost' => $request->cost,
                'notes' => $request->notes
            ];

            $currentNotes = $maintenanceTask->completion_notes ?? '';
            $workLogText = "\n\n--- Work Session [{$workLogEntry['timestamp']}] by {$workLogEntry['user']} ---\n";
            $workLogText .= "Task: {$request->task_type}\n";
            $workLogText .= "Description: {$workLogEntry['description']}\n";
            $workLogText .= "Hours: {$workLogEntry['hours']}\n";
            
            if ($workLogEntry['materials']) {
                $workLogText .= "Materials used: {$workLogEntry['materials']}\n";
            }
            
            if ($workLogEntry['cost']) {
                $workLogText .= "Cost incurred: $" . number_format($workLogEntry['cost'], 2) . "\n";
            }
            
            if ($workLogEntry['notes']) {
                $workLogText .= "Notes: {$workLogEntry['notes']}\n";
            }

            // Update task with materials and costs
            $currentMaterials = $maintenanceTask->materials_used ?? [];
            if ($request->materials_used) {
                $currentMaterials[] = $request->materials_used;
            }

            $currentCost = $maintenanceTask->actual_cost ?? 0;
            $newCost = $currentCost + ($request->cost ?? 0);

            $taskUpdates = [
                'completion_notes' => $currentNotes . $workLogText,
                'materials_used' => $currentMaterials,
                'actual_cost' => $newCost,
                'actual_minutes' => ($maintenanceTask->actual_minutes ?? 0) + ($hoursWorked * 60)
            ];

            // Mark task as complete if requested
            if ($request->boolean('is_complete')) {
                $taskUpdates['status'] = MaintenanceTask::STATUS_COMPLETED;
                $taskUpdates['completed_at'] = now();
            }

            $maintenanceTask->update($taskUpdates);

            $this->logTenantActivity('add_work_log', 'Added work log entry', $maintenanceRequest, [
                'request_id' => $maintenanceRequest->id,
                'request_number' => $maintenanceRequest->request_number,
                'task_id' => $maintenanceTask->id,
                'staff_hour_id' => $staffHour->id,
                'description' => $request->description,
                'hours_spent' => $hoursWorked,
                'cost' => $request->cost,
                'is_complete' => $request->boolean('is_complete')
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Work log entry added successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Work log entry added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error adding work log: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding work log: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error adding work log: ' . $e->getMessage());
        }
    }

    /**
     * Maintenance Dashboard - Main overview
     */
    public function dashboard(Request $request)
    {
        $propertyId = $request->get('property_id');
        $properties = Property::all();

        // Get maintenance request summary
        $requestsQuery = MaintenanceRequest::with(['room', 'property', 'reportedBy', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId));

        // Status counts
        $statusCounts = [
            'pending' => $requestsQuery->clone()->where('status', MaintenanceRequest::STATUS_PENDING)->count(),
            'assigned' => $requestsQuery->clone()->where('status', MaintenanceRequest::STATUS_ASSIGNED)->count(),
            'in_progress' => $requestsQuery->clone()->where('status', MaintenanceRequest::STATUS_IN_PROGRESS)->count(),
            'completed' => $requestsQuery->clone()->where('status', MaintenanceRequest::STATUS_COMPLETED)->count(),
            'on_hold' => $requestsQuery->clone()->where('status', MaintenanceRequest::STATUS_ON_HOLD)->count(),
            'cancelled' => $requestsQuery->clone()->where('status', MaintenanceRequest::STATUS_CANCELLED)->count(),
        ];

        // Priority counts
        $priorityCounts = [
            'urgent' => $requestsQuery->clone()->where('priority', MaintenanceRequest::PRIORITY_URGENT)->whereNotIn('status', [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED])->count(),
            'high' => $requestsQuery->clone()->where('priority', MaintenanceRequest::PRIORITY_HIGH)->whereNotIn('status', [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED])->count(),
            'normal' => $requestsQuery->clone()->where('priority', MaintenanceRequest::PRIORITY_NORMAL)->whereNotIn('status', [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED])->count(),
            'low' => $requestsQuery->clone()->where('priority', MaintenanceRequest::PRIORITY_LOW)->whereNotIn('status', [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED])->count(),
        ];

        // Task counts
        $taskCounts = [
            'pending' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('status', MaintenanceTask::STATUS_PENDING)->count(),
            'assigned' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('status', MaintenanceTask::STATUS_ASSIGNED)->count(),
            'in_progress' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('status', MaintenanceTask::STATUS_IN_PROGRESS)->count(),
            'completed' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('status', MaintenanceTask::STATUS_COMPLETED)->count(),
        ];

        // Today's tasks
        $todaysTasks = MaintenanceTask::with(['maintenanceRequest.room', 'assignedTo'])
            ->when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })
            ->whereDate('scheduled_for', today())
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_for', 'asc')
            ->limit(10)
            ->get();

        // Overdue tasks
        $overdueTasks = MaintenanceTask::with(['maintenanceRequest.room', 'assignedTo'])
            ->when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })
            ->where('scheduled_for', '<', now())
            ->whereNotIn('status', [MaintenanceTask::STATUS_COMPLETED, MaintenanceTask::STATUS_CANCELLED])
            ->orderBy('scheduled_for', 'asc')
            ->limit(10)
            ->get();

        // Active maintenance requests
        $activeRequests = MaintenanceRequest::with(['room', 'property', 'reportedBy', 'assignedTo'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->whereNotIn('status', [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Staff performance
        $staffPerformance = DB::table('maintenance_tasks')
            ->join('users', 'maintenance_tasks.assigned_to', '=', 'users.id')
            ->join('maintenance_requests', 'maintenance_tasks.maintenance_request_id', '=', 'maintenance_requests.id')
            ->when($propertyId, fn($q) => $q->where('maintenance_requests.property_id', $propertyId))
            ->whereDate('maintenance_tasks.updated_at', today())
            ->select('users.name', 'users.id as user_id')
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw('SUM(CASE WHEN maintenance_tasks.status = "completed" THEN 1 ELSE 0 END) as completed_tasks')
            ->selectRaw('AVG(CASE WHEN maintenance_tasks.status = "completed" AND maintenance_tasks.actual_minutes IS NOT NULL THEN maintenance_tasks.actual_minutes ELSE NULL END) as avg_time')
            ->groupBy('users.id', 'users.name')
            ->having('total_tasks', '>', 0)
            ->get()
            ->map(function ($staff) {
                $staff->efficiency = $staff->total_tasks > 0 ? ($staff->completed_tasks / $staff->total_tasks) * 100 : 0;
                return $staff;
            });

        // Recent activity
        $recentActivity = MaintenanceRequest::with(['room', 'assignedTo', 'reportedBy'])
            ->when($propertyId, fn($q) => $q->where('property_id', $propertyId))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Get maintenance staff
        $maintenanceStaff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%maintenance%');
        })->get();

        return view('tenant.maintenance.dashboard', compact(
            'properties',
            'propertyId',
            'statusCounts',
            'priorityCounts',
            'taskCounts',
            'todaysTasks',
            'overdueTasks',
            'activeRequests',
            'staffPerformance',
            'recentActivity',
            'maintenanceStaff'
        ));
    }

    /**
     * Maintenance Tasks Management
     */
    public function tasks(Request $request)
    {
        $propertyId = $request->get('property_id');
        $status = $request->get('status');
        $assignedTo = $request->get('assigned_to');
        $taskType = $request->get('task_type');

        $properties = Property::all();

        $tasksQuery = MaintenanceTask::with(['maintenanceRequest.room', 'maintenanceRequest.property', 'assignedTo', 'createdBy'])
            ->when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($assignedTo, fn($q) => $q->where('assigned_to', $assignedTo))
            ->when($taskType, fn($q) => $q->where('task_type', $taskType))
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_for', 'asc');

        $maintenanceTasks = $tasksQuery->paginate(20);

        // Get filter options
        $statuses = MaintenanceTask::select('status')->distinct()->pluck('status');
        $taskTypes = MaintenanceTask::select('task_type')->distinct()->pluck('task_type');

        // Get maintenance staff
        $maintenanceStaff = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%maintenance%');
        })->get();

        // Summary stats
        $stats = [
            'total' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->count(),
            'pending' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('status', MaintenanceTask::STATUS_PENDING)->count(),
            'in_progress' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('status', MaintenanceTask::STATUS_IN_PROGRESS)->count(),
            'overdue' => MaintenanceTask::when($propertyId, function($q) use ($propertyId) {
                return $q->whereHas('maintenanceRequest', fn($q) => $q->where('property_id', $propertyId));
            })->where('scheduled_for', '<', now())->whereNotIn('status', [MaintenanceTask::STATUS_COMPLETED, MaintenanceTask::STATUS_CANCELLED])->count(),
        ];

        return view('tenant.maintenance.tasks', compact(
            'maintenanceTasks',
            'properties',
            'propertyId',
            'status',
            'assignedTo',
            'taskType',
            'statuses',
            'taskTypes',
            'maintenanceStaff',
            'stats'
        ));
    }

    /**
     * Update maintenance task status
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', MaintenanceTask::STATUS_OPTIONS),
            'notes' => 'nullable|string|max:500'
        ]);

        $task = MaintenanceTask::findOrFail($taskId);

        $oldStatus = $task->status;
        $task->update([
            'status' => $request->status,
        ]);

        // Update timestamps based on status
        if ($request->status === MaintenanceTask::STATUS_IN_PROGRESS && $oldStatus !== MaintenanceTask::STATUS_IN_PROGRESS) {
            $task->update(['started_at' => now()]);
        } elseif ($request->status === MaintenanceTask::STATUS_COMPLETED && $oldStatus !== MaintenanceTask::STATUS_COMPLETED) {
            $task->update(['completed_at' => now()]);
        }

        // Add notes if provided
        if ($request->notes) {
            $currentNotes = $task->completion_notes ?? '';
            $newNote = "\n\n--- Status Update [" . now()->format('Y-m-d H:i:s') . "] by " . Auth::user()->name . " ---\n";
            $newNote .= "Status changed from " . ucfirst(str_replace('_', ' ', $oldStatus)) . " to " . ucfirst(str_replace('_', ' ', $request->status)) . "\n";
            $newNote .= "Notes: " . $request->notes;
            
            $task->update(['completion_notes' => $currentNotes . $newNote]);
        }

        $this->logTenantActivity('update_task_status', 'Updated maintenance task status', $task, [
            'task_id' => $task->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'notes' => $request->notes
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    /**
     * Create a new maintenance task
     */
    public function createTask(Request $request)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'required|in:' . implode(',', MaintenanceTask::TASK_TYPES),
            'priority' => 'nullable|in:' . implode(',', MaintenanceTask::PRIORITY_OPTIONS),
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_minutes' => 'nullable|integer|min:1',
            'scheduled_for' => 'nullable|date',
            'instructions' => 'nullable|string'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($request->maintenance_request_id);

        $task = MaintenanceTask::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'task_type' => $request->task_type,
            'priority' => $request->priority ?: $maintenanceRequest->priority,
            'status' => $request->assigned_to ? MaintenanceTask::STATUS_ASSIGNED : MaintenanceTask::STATUS_PENDING,
            'estimated_minutes' => $request->estimated_minutes,
            'scheduled_for' => $request->scheduled_for,
            'instructions' => $request->instructions,
            'assigned_at' => $request->assigned_to ? now() : null,
        ]);

        $this->logTenantActivity('create_task', 'Created maintenance task', $task, [
            'task_id' => $task->id,
            'maintenance_request_id' => $maintenanceRequest->id,
            'title' => $task->title,
            'task_type' => $task->task_type
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task
            ]);
        }

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    /**
     * Get a maintenance task
     */
    public function getTask($taskId)
    {
        $task = MaintenanceTask::findOrFail($taskId);

        return response()->json($task);
    }

    /**
     * Update a maintenance task
     */
    public function updateTask(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type' => 'required|in:' . implode(',', MaintenanceTask::TASK_TYPES),
            'priority' => 'nullable|in:' . implode(',', MaintenanceTask::PRIORITY_OPTIONS),
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_minutes' => 'nullable|integer|min:1',
            'scheduled_for' => 'nullable|date',
            'instructions' => 'nullable|string',
            'status' => 'nullable|in:' . implode(',', MaintenanceTask::STATUS_OPTIONS)
        ]);

        $task = MaintenanceTask::findOrFail($taskId);
        $oldData = $task->toArray();

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'task_type' => $request->task_type,
            'priority' => $request->priority ?: $task->priority,
            'assigned_to' => $request->assigned_to,
            'estimated_minutes' => $request->estimated_minutes,
            'scheduled_for' => $request->scheduled_for,
            'instructions' => $request->instructions,
            'status' => $request->status ?: $task->status,
        ]);

        // Update timestamps based on status changes
        if ($request->status && $request->status !== $oldData['status']) {
            if ($request->status === MaintenanceTask::STATUS_IN_PROGRESS && $oldData['status'] !== MaintenanceTask::STATUS_IN_PROGRESS) {
                $task->update(['started_at' => now()]);
            } elseif ($request->status === MaintenanceTask::STATUS_COMPLETED && $oldData['status'] !== MaintenanceTask::STATUS_COMPLETED) {
                $task->update(['completed_at' => now()]);
            }
        }

        // Update assignment timestamp
        if ($request->assigned_to && $request->assigned_to !== $oldData['assigned_to']) {
            $task->update(['assigned_at' => now()]);
        }

        $this->logTenantActivity('update_task', 'Updated maintenance task', $task, [
            'task_id' => $task->id,
            'changes' => array_diff_assoc($task->getChanges(), $oldData)
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    /**
     * Print view of the maintenance request.
     */
    public function print(string $id)
    {
        $maintenanceRequest = MaintenanceRequest::with([
            'room.type', 
            'property', 
            'reportedBy', 
            'assignedTo',
            'maintenanceTasks.assignedTo',
            'maintenanceTasks.staffHours.user',
            'workLogs.user'
        ])->findOrFail($id);
        
        $property = current_property();

        return view('tenant.maintenance.print', compact('maintenanceRequest', 'property'));
    }
}
