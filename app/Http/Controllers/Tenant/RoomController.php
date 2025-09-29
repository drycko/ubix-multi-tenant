<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::with(['type.rates' => function($query) {
            // the rate effective_from is in the past and is the closest to today
            // and the rate is active and the effective_until is in the future or null
            $query->where('is_active', true)
                ->where(function($q) {
                    $q->where('effective_from', '<=', now())
                      ->where(function($q2) {
                          $q2->where('effective_until', '>=', now())
                             ->orWhereNull('effective_until');
                      });
                });
        }])
        ->where('property_id', current_property()->id)
        ->paginate(15);

        $currency = current_property()->currency ?? 'USD';

        return view('tenant.rooms.index', compact('rooms', 'currency'));
    }

    // get room rates for a specific room type
    public function getRoomTypeRate($roomTypeId, $isShared = false)
    {
        $roomType = RoomType::find($roomTypeId);
        if (!$roomType) {
            return response()->json(['error' => 'Room type not found'], 404);
        }
        // $rates = $roomType->rates; // Assuming a RoomType hasMany Rates relationship, we have to also filter is is_shared
        if ($isShared) {
            $rates = $rates->where('is_shared', true);
        } else {
            $rates = $rates->where('is_shared', false);
        }
        // get the rate that is in effect today
        $today = now();
        $rates = $rates->filter(function ($rate) use ($today) {
            return $rate->effective_from <= $today && $rate->effective_until >= $today;
        });
        return response()->json($rates);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // get room types for the current property
        $roomTypes = RoomType::where('property_id', current_property()->id)->get();
        // send the next available display order
        $maxOrder = Room::where('property_id', current_property()->id)->max('display_order');
        $nextOrder = $maxOrder + 1;
        // send the next available room number
        $maxNumber = Room::where('property_id', current_property()->id)->max('number');
        $nextNumber = $maxNumber + 1;
        return view('tenant.rooms.create', compact('roomTypes', 'nextOrder', 'nextNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // The is_enabled field must be true or false. (not null) but the checkbox will send null when unchecked
            $request->merge(['is_enabled' => $request->has('is_enabled')]);
            $request->merge(['is_featured' => $request->has('is_featured')]);
            // validate the request
            $validated = $request->validate([
                'room_type_id' => 'required|exists:room_types,id',
                'number' => 'required|string|max:255|unique:rooms,number,NULL,id,property_id,' . current_property()->id,
                'name' => 'required|string|max:255',
                'short_code' => 'required|string|max:50',
                'floor' => 'nullable|integer',
                'legacy_room_code' => 'nullable|integer|unique:rooms,legacy_room_code,NULL,id,property_id,' . current_property()->id,
                'is_enabled' => 'required|boolean',
                'is_featured' => 'required|boolean',
                'display_order' => 'nullable|integer',
                'notes' => 'nullable|string',
                'web_description' => 'nullable|string',
                'description' => 'nullable|string',
                'web_image' => 'nullable|image|max:2048', // max 2MB
            ]);

            // add property_id to the request
            $request->merge(['property_id' => current_property()->id]);

            // Handle image upload
            if ($request->hasFile('web_image')) {
                // Store new image
                $imagePath = $request->file('web_image')->store('room_images', 'public');
                $validated['web_image'] = 'storage/' . $imagePath;

            } else {
                // this is a NEW room, so no existing image
                $validated['web_image'] = null;
            }

            // Handle display order logic for new room
            if ($request->has('display_order') && $request->input('display_order') != $validated['display_order']) {
                $newOrder = $request->input('display_order');
                $oldOrder = $validated['display_order'];

                if ($newOrder < $oldOrder) {
                    // Moving up - increment orders between new and old position
                    Room::where('property_id', current_property()->id)
                        ->where('display_order', '>=', $newOrder)
                        ->where('display_order', '<', $oldOrder)
                        ->increment('display_order');
                } else {
                    // Moving down - decrement orders between old and new position
                    Room::where('property_id', current_property()->id)
                        ->where('display_order', '>', $oldOrder)
                        ->where('display_order', '<=', $newOrder)
                        ->decrement('display_order');
                }
            } else {
                // If no display order change, just pick the next available order
                $maxOrder = Room::where('property_id', current_property()->id)->max('display_order');
                
                $validated['display_order'] = $maxOrder + 1;
            }

            // create the room with validated data
            $room = Room::create(array_merge($validated, [
                'property_id' => current_property()->id,
            ]));

            return redirect()->route('tenant.admin.rooms.index')->with('success', 'Room created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Rethrow validation exceptions to be handled by Laravel
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while updating the room: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        // check if the room belongs to the current property
        if ($room->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        $currency = current_property()->currency ?? 'USD';

        $roomTypes = RoomType::where('property_id', current_property()->id)->get();
        $room->load('type');
        // get active rates for the room type, and the ones that are not expired
        $roomRates = $room->type ? $room->type->rates()->where('is_active', true)->get() : collect();

        // send the images
        $room->load('images');

        return view('tenant.rooms.edit', compact('room', 'roomTypes', 'roomRates', 'currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        try {
            // Check if the room belongs to the current property
            if ($room->property_id !== current_property()->id) {
                abort(403, 'Unauthorized action.');
            }

            // Handle checkbox fields
            $request->merge([
                'is_enabled' => $request->has('is_enabled'),
                'is_featured' => $request->has('is_featured')
            ]);

            // Validate the request
            $validated = $request->validate([
                'room_type_id' => 'required|exists:room_types,id',
                'number' => 'required|string|max:255|unique:rooms,number,' . $room->id . ',id,property_id,' . current_property()->id,
                'name' => 'required|string|max:255',
                'short_code' => 'required|string|max:50',
                'floor' => 'nullable|integer',
                'legacy_room_code' => 'nullable|integer|unique:rooms,legacy_room_code,' . $room->id . ',id,property_id,' . current_property()->id,
                'is_enabled' => 'required|boolean',
                'is_featured' => 'required|boolean',
                'display_order' => 'nullable|integer',
                'notes' => 'nullable|string',
                'web_description' => 'nullable|string',
                'description' => 'nullable|string',
                'web_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // max 2MB
            ]);

            // Handle image upload
            if ($request->has('remove_image')) {
                // Delete the current image
                if ($room->web_image) {
                    $imagePath = str_replace('storage/', '', $room->web_image);
                    Storage::disk('public')->delete($imagePath);
                }
                $validated['web_image'] = null;
            } 
            elseif ($request->hasFile('web_image')) {
                // Delete old image if exists
                if ($room->web_image) {
                    $oldImagePath = str_replace('storage/', '', $room->web_image);
                    Storage::disk('public')->delete($oldImagePath);
                }
                
                // Store new image
                $imagePath = $request->file('web_image')->store('room_images', 'public');
                $validated['web_image'] = 'storage/' . $imagePath;
            } 
            else {
                // Keep existing image
                $validated['web_image'] = $room->web_image;
            }

            // if ($request->hasFile('web_image')) {
            //     // Delete old image if exists
            //     if ($room->web_image && Storage::disk('public')->exists(str_replace('storage/', '', $room->web_image))) {
            //         Storage::disk('public')->delete(str_replace('storage/', '', $room->web_image));
            //     }
                
            //     // Store new image
            //     $imagePath = $request->file('web_image')->store('room_images', 'public');
            //     $validated['web_image'] = 'storage/' . $imagePath;
            // } else {
            //     // Keep the existing image if no new image uploaded
            //     $validated['web_image'] = $room->web_image;
            // }

            // Handle display order logic
            if ($request->has('display_order') && $request->input('display_order') != $room->display_order) {
                $newOrder = $request->input('display_order');
                $oldOrder = $room->display_order;
                
                if ($newOrder < $oldOrder) {
                    // Moving up - increment orders between new and old position
                    Room::where('property_id', current_property()->id)
                        ->where('display_order', '>=', $newOrder)
                        ->where('display_order', '<', $oldOrder)
                        ->increment('display_order');
                } else {
                    // Moving down - decrement orders between old and new position
                    Room::where('property_id', current_property()->id)
                        ->where('display_order', '>', $oldOrder)
                        ->where('display_order', '<=', $newOrder)
                        ->decrement('display_order');
                }
            } else {
                // If no display order change, keep the existing one
                $validated['display_order'] = $room->display_order;
            }

            // Update the room with validated data
            $room->update($validated);

            return redirect()->route('tenant.admin.rooms.index')
                ->with('success', 'Room updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while updating the room: ' . $e->getMessage()]);
        }
    }

    /**
     * get available rooms without bookings in range sent via query params.
     */
    // public function availableRooms(Request $request)
    // {
    //     $validated = $request->validate([
    //         'arrival_date' => 'required|date|after_or_equal:today',
    //         'departure_date' => 'required|date|after:arrival_date',
    //     ]);

    //     $availableRooms = Room::where('property_id', current_property()->id)
    //         ->whereDoesntHave('bookings', function ($query) use ($validated) {
    //             $query->where('status', 'confirmed')
    //                 ->where(function ($q) use ($validated) {
    //                     $q->where('arrival_date', '<', $validated['departure_date'])
    //                       ->where('departure_date', '>', $validated['arrival_date']);
    //                 });
    //         })
    //         ->get();

    //     return response()->json([
    //         'rooms' => $availableRooms,
    //         'currency' => current_property()->currency ?? 'USD'
    //     ]);
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        // Check if the room belongs to the current property
        if ($room->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the room
        $room->delete();

        return redirect()->route('tenant.admin.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
