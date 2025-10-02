<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\RoomAmenity;
use App\Models\Tenant\Property;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomAmenityController extends Controller
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

        // Get all properties for the super admin filter
        $properties = Property::all();

        // Build the query
        $query = RoomAmenity::with(['property']);

        // Apply property filter
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        } elseif (!is_super_user()) {
            // Non-super users can only see their property's amenities
            $query->where('property_id', auth()->user()->property_id);
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Order by name
        $query->orderBy('name', 'asc');

        // Paginate results
        $roomAmenities = $query->paginate(15);

        return view('tenant.room-amenities.index', compact('roomAmenities', 'propertyId', 'properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Get the property context
        $propertyId = $request->get('property_id');
        if (!$propertyId && !is_super_user()) {
            $propertyId = auth()->user()->property_id;
        }

        return view('tenant.room-amenities.create', compact('propertyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get property context
        $propertyId = $request->get('property_id');
        $propertyCode = '';
        
        if (!$propertyId && !is_super_user()) {
            $propertyId = auth()->user()->property_id;
            $propertyCode = auth()->user()->property->code ?? '';
        } else {
            $property = Property::find($propertyId);
            $propertyCode = $property->code ?? '';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string', 
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('room_amenities', 'slug')->where('property_id', $propertyId)
            ],
            'icon' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Create the room amenity
        $roomAmenity = RoomAmenity::create(array_merge($validated, ['property_id' => $propertyId]));

        // Log activity
        $this->logTenantActivity(
            'create_room_amenity',
            'Created room amenity: ' . $roomAmenity->name . ' (ID: ' . $roomAmenity->id . ') for property: ' . $propertyCode,
            $roomAmenity,
            [
                'table' => 'room_amenities',
                'id' => $roomAmenity->id,
                'user_id' => auth()->id(),
                'changes' => $roomAmenity->toArray()
            ]
        );

        return redirect()->route('tenant.room-amenities.index', ['property_id' => $propertyId])
                        ->with('success', 'Room amenity created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomAmenity $roomAmenity)
    {
        // Ensure the room amenity belongs to the identified property
        if (!is_super_user() && $roomAmenity->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        $roomAmenity->load(['property']);

        // Get usage statistics
        $usageStats = [
            'room_types_count' => \DB::table('room_types')
                ->where('property_id', $roomAmenity->property_id)
                ->where('amenities', 'like', '%"' . $roomAmenity->slug . '"%')
                ->count()
        ];

        return view('tenant.room-amenities.show', compact('roomAmenity', 'usageStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomAmenity $roomAmenity)
    {
        // Ensure the room amenity belongs to the identified property
        if (!is_super_user() && $roomAmenity->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        return view('tenant.room-amenities.edit', compact('roomAmenity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomAmenity $roomAmenity)
    {
        // Ensure the room amenity belongs to the identified property
        if (!is_super_user() && $roomAmenity->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string', 
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('room_amenities', 'slug')
                    ->where('property_id', $roomAmenity->property_id)
                    ->ignore($roomAmenity->id)
            ],
            'icon' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Store old values for logging
        $oldValues = $roomAmenity->toArray();

        // Update the room amenity
        $roomAmenity->update($validated);

        // Log activity
        $propertyCode = $roomAmenity->property->code ?? '';
        $this->logTenantActivity(
            'update_room_amenity',
            'Updated room amenity: ' . $roomAmenity->name . ' (ID: ' . $roomAmenity->id . ') for property: ' . $propertyCode,
            $roomAmenity,
            [
                'table' => 'room_amenities',
                'id' => $roomAmenity->id,
                'user_id' => auth()->id(),
                'old_values' => $oldValues,
                'new_values' => $roomAmenity->fresh()->toArray()
            ]
        );

        return redirect()->route('tenant.room-amenities.index', ['property_id' => $roomAmenity->property_id])
                        ->with('success', 'Room amenity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomAmenity $roomAmenity)
    {
        // Ensure the room amenity belongs to the identified property
        if (!is_super_user() && $roomAmenity->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        // Check if amenity is in use
        $inUse = \DB::table('room_types')
            ->where('property_id', $roomAmenity->property_id)
            ->where('amenities', 'like', '%"' . $roomAmenity->slug . '"%')
            ->exists();

        if ($inUse) {
            return redirect()->back()->with('error', 'Cannot delete amenity that is currently assigned to room types.');
        }

        $propertyCode = $roomAmenity->property->code ?? '';
        $amenityName = $roomAmenity->name;
        $amenityId = $roomAmenity->id;

        // Log activity before deletion
        $this->logTenantActivity(
            'delete_room_amenity',
            'Deleted room amenity: ' . $amenityName . ' (ID: ' . $amenityId . ') for property: ' . $propertyCode,
            $roomAmenity,
            [
                'table' => 'room_amenities',
                'id' => $amenityId,
                'user_id' => auth()->id(),
                'deleted_data' => $roomAmenity->toArray()
            ]
        );

        $roomAmenity->delete();

        return redirect()->route('tenant.room-amenities.index', ['property_id' => $roomAmenity->property_id])
                        ->with('success', 'Room amenity deleted successfully.');
    }

    /**
     * Clone the specified resource.
     */
    public function clone(RoomAmenity $roomAmenity)
    {
        // Ensure the room amenity belongs to the identified property
        if (!is_super_user() && $roomAmenity->property_id !== auth()->user()->property_id) {
            abort(403);
        }

        // Create a copy with modified name and slug
        $clonedData = $roomAmenity->toArray();
        unset($clonedData['id'], $clonedData['created_at'], $clonedData['updated_at'], $clonedData['deleted_at']);
        $clonedData['name'] = $roomAmenity->name . ' (Copy)';
        $clonedData['slug'] = $roomAmenity->slug . '_copy';

        // Ensure unique slug
        $counter = 1;
        while (RoomAmenity::where('property_id', $roomAmenity->property_id)
                          ->where('slug', $clonedData['slug'])
                          ->exists()) {
            $clonedData['slug'] = $roomAmenity->slug . '_copy_' . $counter;
            $counter++;
        }

        $clonedAmenity = RoomAmenity::create($clonedData);

        $propertyCode = $roomAmenity->property->code ?? '';

        // Log activity
        $this->logTenantActivity(
            'clone_room_amenity',
            'Cloned room amenity: ' . $roomAmenity->name . ' to ' . $clonedAmenity->name . ' (ID: ' . $clonedAmenity->id . ') for property: ' . $propertyCode,
            $clonedAmenity,
            [
                'table' => 'room_amenities',
                'id' => $clonedAmenity->id,
                'user_id' => auth()->id(),
                'original_id' => $roomAmenity->id,
                'cloned_data' => $clonedData
            ]
        );

        return redirect()->route('tenant.room-amenities.edit', $clonedAmenity)
                        ->with('success', 'Room amenity cloned successfully. Please review and update as needed.');
    }
}
