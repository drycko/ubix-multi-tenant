<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\MaintenanceRequest;
use App\Models\Tenant\Room;
use App\Models\Tenant\Property;
use App\Models\Tenant\User;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view maintenance')->only(['index', 'show']);
        $this->middleware('permission:create maintenance requests')->only(['create', 'store']);
        $this->middleware('permission:edit maintenance requests')->only(['edit', 'update', 'assign', 'start', 'complete', 'cancel', 'hold']);
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

        return view('tenant.maintenance.create', compact('properties', 'rooms', 'maintenanceStaff', 'allUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'property_id' => 'required|exists:properties,id',
            'category' => 'required|in:plumbing,electrical,hvac,furniture,appliance,structural,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location_details' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
            'requires_room_closure' => 'boolean',
            'scheduled_for' => 'nullable|date|after:now',
            'assigned_to' => 'nullable|exists:users,id',
            'images.*' => 'nullable|image|max:2048'
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('maintenance-images', 'public');
                $images[] = $path;
            }
        }

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

        $this->logTenantActivity('create_request', 'Created maintenance request', $maintenanceRequest, [
            'request_id' => $maintenanceRequest->id,
            'request_number' => $maintenanceRequest->request_number,
            'room_id' => $maintenanceRequest->room_id,
            'category' => $maintenanceRequest->category,
            'priority' => $maintenanceRequest->priority
        ]);

        return redirect()->route('tenant.maintenance.index')
                        ->with('success', 'Maintenance request created successfully. Request #' . $maintenanceRequest->request_number);
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
            'assignedTo'
        ])->findOrFail($id);

        return view('tenant.maintenance.show', compact('maintenanceRequest'));
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

        return view('tenant.maintenance.edit', compact(
            'maintenanceRequest',
            'properties',
            'rooms',
            'maintenanceStaff'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'property_id' => 'required|exists:properties,id',
            'category' => 'required|in:plumbing,electrical,hvac,furniture,appliance,structural,other',
            'priority' => 'required|in:low,normal,high,urgent',
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
            'images.*' => 'nullable|image|max:2048'
        ]);

        $images = $maintenanceRequest->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('maintenance-images', 'public');
                $images[] = $path;
            }
        }

        $wasAssigned = $maintenanceRequest->assigned_to !== null;
        $newAssignee = $request->assigned_to;

        $maintenanceRequest->update([
            'room_id' => $request->room_id,
            'property_id' => $request->property_id,
            'category' => $request->category,
            'priority' => $request->priority,
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
        ]);

        // Update assignment status if changed
        if (!$wasAssigned && $newAssignee) {
            $maintenanceRequest->update([
                'status' => MaintenanceRequest::STATUS_ASSIGNED,
                'assigned_at' => now()
            ]);
        } elseif ($wasAssigned && !$newAssignee) {
            $maintenanceRequest->update([
                'status' => MaintenanceRequest::STATUS_PENDING,
                'assigned_at' => null
            ]);
        }

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
                Storage::disk('public')->delete($image);
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
}
