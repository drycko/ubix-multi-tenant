<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\RoomAmenity;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        // Determine property ID based on user type
        $propertyId = is_super_user() ? selected_property_id() : auth()->user()->property_id;
        
        // Ensure a property is selected for super-users
        if (is_super_user() && !$propertyId) {
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
        RoomType::create(array_merge($validated, ['property_id' => $propertyId]));

        return redirect()->route('tenant.room-types.index')->with('success', 'Room type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomType $roomType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomType $roomType)
    {
        
        // room type amenities is stored as json like this "[\"ensuite_bathroom\",\"television\",\"dstv\",\"fireplace\",\"aircon\",\"balcony\",\"spa_bath\",\"shower\",\"tea_facilities\",\"forest_view\",\"underfloor_heating\"]", we need to convert it to a comma separated string for the form
        // we also have room_amenities table, we can use that to get the names of the amenities
        $amenityNames = RoomAmenity::whereIn('slug', json_decode($roomType->amenities, true))->pluck('slug');
        $roomType->amenities = $amenityNames;

        // json_decode(): Argument #1 ($json) must be of type string, array given
        $roomType->amenities = json_encode($roomType->amenities);
        // all amenities from room_amenities table for selection in the form
        $allAmenities = RoomAmenity::get();

        // Return the view to edit the room type
        return view('tenant.room-types.edit', compact('roomType', 'allAmenities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomType $roomType)
    {
        
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:room_types,code,' . $roomType->id . ',id,property_id,' . auth()->user()->property_id,
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

        // Redirect back with success message
        return redirect()->route('tenant.room-types.index')->with('success', 'Room type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomType $roomType)
    {
        // Ensure the room type belongs to the identified property
        if ($roomType->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        // Delete the room type
        $roomType->delete();

        // Redirect back with success message
        return redirect()->route('tenant.room-types.index')->with('success', 'Room type deleted successfully.');
    }
}
