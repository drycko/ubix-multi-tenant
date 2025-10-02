<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\RoomAmenity;
use App\Models\Tenant\Property;
use Illuminate\Http\Request;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
    use LogsTenantUserActivity;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the property context
        $propertyId = $request->get('property_id');
        if (!$propertyId && !is_super_user()) {
        $propertyId = auth()->user()->property_id;
        }
        
        $query = RoomType::query();
        
        // For super-users: show all if no property selected, or filter by selected property
        if (is_super_user()) {
            if (is_property_selected()) {
                $query->where('property_id', selected_property_id());
            }
            // If no property selected, show all room types
        } else {
            // For property-specific users, always filter by their property
            $query->where('property_id', auth()->user()->property_id);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search'); // search is case sensitive, let us make it insensitive by using lower function
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw('LOWER(name)'), 'like', "%{$search}%")
                  ->orWhere(DB::raw('LOWER(code)'), 'like', "%{$search}%")
                  ->orWhere(DB::raw('LOWER(description)'), 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            $query->where('status', $status);
        }

        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');

        $roomTypes = $query->paginate(15);
        
        // Add rooms count to each room type
        $roomTypes->each(function ($type) {
            $type->rooms_count = $type->rooms()->count();
        });

        return view('tenant.room-types.index', compact('roomTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // all amenities from room_amenities table for selection in the form
        $allAmenities = RoomAmenity::query();
        if (!is_super_user()) {
            $allAmenities->where('property_id', auth()->user()->property_id);
        }
        $allAmenities = $allAmenities->get();
        // Return the view to create a new room type
        return view('tenant.room-types.create', compact('allAmenities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // start transaction
            DB::beginTransaction();
            // Determine property ID based on user type
            $propertyId = is_super_user() ? selected_property_id() : auth()->user()->property_id;

            $propertyCode = Property::where('id', $propertyId)->value('code');
            
            // Ensure a property is selected for super-users
            if (is_super_user() && !$propertyId) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Please select a property before creating room types.');
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:room_types,code,NULL,id,property_id,' . $propertyId,
                'description' => 'nullable|string',
                'base_capacity' => 'required|integer|min:1',
                'max_capacity' => 'required|integer|min:1|gte:base_capacity',
                'amenities' => 'nullable|array',
            ]);

            // amenities are checkboxes, from the form as amenities[]
            if (isset($validated['amenities'])) {
                $validated['amenities'] = json_encode($validated['amenities']);
            } else {
                $validated['amenities'] = json_encode([]);
            }

            // Create the room type
            $roomType = RoomType::create(array_merge($validated, ['property_id' => $propertyId]));
            // log activity
            $this->logTenantActivity(
                'create_room_type',
                'Created room type: ' . $roomType->name . ' (ID: ' . $roomType->id . ') for property: ' . $propertyCode,
                $roomType, // Pass the room type object as subject
                [
                    'table' => 'room_types',
                    'id' => $roomType->id,
                    'user_id' => auth()->id(),
                    'changes' => $roomType->toArray()
                ]
            );

            // log notification
            $this->createTenantNotification(
                'success',
                'Room Type Created',
                'Room type "' . $roomType->name . '" has been created for property: ' . $propertyCode . ' successfully.',
                ['room_type_id' => $roomType->id],
                ['icon' => 'bi bi-door-open', 'color' => 'green']
            );

            // Commit transaction
            DB::commit();

            return redirect()->route('tenant.room-types.index')->with('success', 'Room type created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the room type: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if (!is_super_user() && $roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }
        $roomType->selected_amenities = $roomType->amenities_array ?: [];
        // Force it to be an array if it gets JSON-encoded somehow
        if (is_string($roomType->selected_amenities)) {
            $roomType->selected_amenities = json_decode($roomType->selected_amenities, true);
        }
        $amenities = RoomAmenity::whereIn('slug', $roomType->selected_amenities)
            ->where('property_id', $roomType->property_id)
            ->get();

        return view('tenant.room-types.show', compact('roomType', 'amenities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if (!is_super_user() && $roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        $roomType->selected_amenities = $roomType->amenities_array ?: [];
        // Force it to be an array if it gets JSON-encoded somehow
        if (is_string($roomType->selected_amenities)) {
            $roomType->selected_amenities = json_decode($roomType->selected_amenities, true);
        }
        \Log::info('selected_amenities: ' . print_r($roomType->selected_amenities, true));

        // all amenities from room_amenities table for selection in the form
        $allAmenities = RoomAmenity::query();
        if (!is_super_user()) {
            $allAmenities->where('property_id', auth()->user()->property_id);
        } else if (is_property_selected()) {
            $allAmenities->where('property_id', selected_property_id());
        }
        $allAmenities = $allAmenities->get();
        
        \Log::info('allAmenities slugs: ' . $allAmenities->pluck('slug')->toJson());

        // Return the view to edit the room type
        return view('tenant.room-types.edit', compact('roomType', 'allAmenities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if (!is_super_user() && $roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        try {
            // start transaction
            DB::beginTransaction();
            
            // Get the property ID for validation
            $propertyId = is_super_user() && is_property_selected() 
                ? selected_property_id() 
                : auth()->user()->property_id;
            
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:room_types,code,' . $roomType->id . ',id,property_id,' . $propertyId,
                'description' => 'nullable|string',
                'base_capacity' => 'required|integer|min:1',
                'max_capacity' => 'required|integer|min:1|gte:base_capacity',
                'amenities' => 'nullable|array',
            ]);
            // amenities are checkboxes, from the form as amenities[]
            // if no amenities are selected, the amenities field will not be present in the request
            if (isset($validated['amenities'])) {
                $validated['amenities'] = json_encode($validated['amenities']);
            } else {
                $validated['amenities'] = json_encode([]);
            }

            // Create the room type
            $roomType->update($validated);
            // log activity
            $this->logTenantActivity(
                'update_room_type',
                'Updated room type: ' . $roomType->name . ' (ID: ' . $roomType->id . ') for property: ' . $roomType->property->code,
                $roomType, // Pass the room type object as subject
                [
                    'table' => 'room_types',
                    'id' => $roomType->id,
                    'user_id' => auth()->id(),
                    'changes' => $roomType->getChanges()
                ]
            );

            // log notification
            $this->createTenantNotification(
                'info',
                'Room Type Updated',
                'Room type "' . $roomType->name . '" has been updated for property: ' . $roomType->property->code . ' successfully.',
                ['room_type_id' => $roomType->id],
                ['icon' => 'bi bi-door-open', 'color' => 'blue']
            );

            // Commit transaction
            DB::commit();

            // Redirect back with success message
            return redirect()->route('tenant.room-types.index')->with('success', 'Room type updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            \Log::error('Error updating room type: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()->with('error', 'Failed to update room type.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if (!is_super_user() && $roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }
        try {
            // start transaction
            DB::beginTransaction();
            // Delete the room type
            $roomType->delete();

            // log activity
            $this->logTenantActivity(
                'delete_room_type',
                'Deleted room type: ' . $roomType->name . ' (ID: ' . $roomType->id . ') for property: ' . $roomType->property->code,
                $roomType, // Pass the room type object as subject
                [
                    'table' => 'room_types',
                    'id' => $roomType->id,
                    'user_id' => auth()->id(),
                    'changes' => $roomType->toArray()
                ]
            );

            // log notification
            $this->createTenantNotification(
                'warning',
                'Room Type Deleted',
                'Room type "' . $roomType->name . '" has been deleted for property: ' . $roomType->property->code . ' successfully.',
                ['room_type_id' => $roomType->id],
                ['icon' => 'bi bi-door-closed', 'color' => 'red']
            );

            // Commit transaction
            DB::commit();

            // Redirect back with success message
            return redirect()->route('tenant.room-types.index')->with('success', 'Room type deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            \Log::error('Error deleting room type: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()->with('error', 'Failed to delete room type.');
        }

    }

    /**
     * Toggle the status of the specified room type.
     */
    public function toggleStatus(RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if (!is_super_user() && $roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        try {
            // start transaction
            DB::beginTransaction();

            // Toggle the status
            $roomType->update(['is_active' => !$roomType->is_active]);

            $status = $roomType->is_active ? 'activated' : 'deactivated';
            // log activity
            $this->logTenantActivity(
                'toggle_room_type_status',
                ucfirst($status) . ' room type: ' . $roomType->name . ' (ID: ' . $roomType->id . ') for property: ' . $roomType->property->code,
                $roomType, // Pass the room type object as subject
                [
                    'table' => 'room_types',
                    'id' => $roomType->id,
                    'user_id' => auth()->id(),
                    'changes' => ['is_active' => $roomType->is_active]
                ]
            );
            // log notification
            $this->createTenantNotification(
                $roomType->is_active ? 'success' : 'info',
                'Room Type ' . ucfirst($status),
                'Room type "' . $roomType->name . '" has been ' . $status . ' for property: ' . $roomType->property->code . ' successfully.',
                ['room_type_id' => $roomType->id],
                ['icon' => 'bi bi-door-' . ($roomType->is_active ? 'open' : 'closed'), 'color' => $roomType->is_active ? 'green' : 'blue']
            );

            // Commit transaction
            DB::commit();
            return redirect()->route('tenant.room-types.index')
                ->with('success', "Room type has been {$status} successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            \Log::error('Error toggling room type status: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()->with('error', 'Failed to toggle room type status.');
        }
    }

    /**
     * Clone the specified room type.
     */
    public function clone(RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if (!is_super_user() && $roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        try {
            // start transaction
            DB::beginTransaction();

            // Determine property ID for the clone
            $propertyId = is_super_user() && is_property_selected() 
                ? selected_property_id() 
                : auth()->user()->property_id;

            // Create a copy of the room type
            $clonedRoomType = $roomType->replicate();
            $clonedRoomType->name = $roomType->name . ' (Copy)';
            $clonedRoomType->code = $roomType->code . '_copy_' . time();
            $clonedRoomType->property_id = $propertyId;
            $clonedRoomType->save();

            // log activity
            $this->logTenantActivity(
                'clone_room_type',
                'Cloned room type: ' . $roomType->name . ' (ID: ' . $roomType->id . ') to new room type: ' . $clonedRoomType->name . ' (ID: ' . $clonedRoomType->id . ') for property: ' . $clonedRoomType->property->code,
                $clonedRoomType, // Pass the cloned room type object as subject
                [
                    'table' => 'room_types',
                    'id' => $clonedRoomType->id,
                    'user_id' => auth()->id(),
                    'changes' => $clonedRoomType->toArray()
                ]
            );

            // log notification
            $this->createTenantNotification(
                'success',
                'Room Type Cloned',
                'Room type "' . $roomType->name . '" has been cloned to "' . $clonedRoomType->name . '" for property: ' . $clonedRoomType->property->code . ' successfully.',
                ['room_type_id' => $clonedRoomType->id],
                ['icon' => 'bi bi-door-open', 'color' => 'green']
            );
            // Commit transaction
            DB::commit();

            return redirect()->route('tenant.room-types.index')
                ->with('success', 'Room type cloned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            \Log::error('Error cloning room type: ' . $e->getMessage());

            // Redirect back with error message
            return redirect()->back()->with('error', 'Failed to clone room type.');
        }
    }
}
