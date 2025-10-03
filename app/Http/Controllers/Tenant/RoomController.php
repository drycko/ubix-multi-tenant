<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Property;
use App\Traits\LogsTenantUserActivity;
use App\Services\HtmlSanitizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    use LogsTenantUserActivity;

    protected $htmlSanitizer;

    public function __construct(HtmlSanitizerService $htmlSanitizer)
    {
        $this->htmlSanitizer = $htmlSanitizer;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the property context
        $propertyId = $request->get('property_id');
        if (!$propertyId && !is_super_user()) {
            $propertyId = auth()->user()->property_id;
        }
        
        // build the query with filters
        $query = Room::where('property_id', $propertyId);

        // Search filter
        if ($search = request('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('number', 'like', "%{$search}%")
                  ->orWhere('short_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        // Room type filter
        if ($roomTypeId = request('room_type')) {
            $query->where('room_type_id', $roomTypeId);
        }
        // Sorting
        $query->orderBy('display_order');

        $rooms = $query->paginate(15);

        $roomTypes = RoomType::where('property_id', $propertyId)->get();

        $currency = current_property()->currency ?? 'USD';

        return view('tenant.rooms.index', compact('rooms', 'currency', 'propertyId', 'roomTypes'));
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

    // get rooms available between two dates
    public function available(Request $request)
    {
        
        // $validated = $request->validate([
        //     // 'company_id' => 'required|exists:companies,id',
        //     'arrival_date' => 'required|date|after_or_equal:today',
        //     'departure_date' => 'required|date|after:arrival_date',
        // ]);

        $propertyId = $request->header('X-Property-ID');

        $arrivalDate = $request->get('arrival_date');
        $departureDate = $request->get('departure_date');

        if (!$arrivalDate || !$departureDate) {
            return response()->json(['error' => 'Both arrival_date and departure_date are required'], 400);
        }

        try {
            $availableRooms = Room::getAvailableRooms($arrivalDate, $departureDate)
                ->where('property_id', $propertyId)->where('is_enabled', true);

            // rooms need to have their type and current rates loaded
            $roomsData = $availableRooms->load(['type' => function ($query) use ($propertyId) {
                $query->with(['rates' => function ($rateQuery) {
                    $rateQuery->active()->validForDate(now())->orderBy('amount', 'asc');
                }]);
            }]);

            // we need to filter out rooms that do not have any active rates for today
            // $roomsData = $roomsData->filter(function ($room) {
            //     return $room->type->rates->isNotEmpty();
            // });
            // $availableRooms = Room::getAvailableRooms($arrivalDate, $departureDate)
            //     ->where('property_id', $propertyId)->where('is_enabled', true);
            // // load room type and rates
            // $availableRooms->load(['type' => function($query) {
            //     $query->with(['rates' => function($q) {
            //         $q->where('is_active', true);
            //     }]);
            // }]);

            // // format the rooms for response
            $roomsData = $availableRooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'number' => $room->number,
                    'short_code' => $room->short_code,
                    'floor' => $room->floor,
                    'type' => $room->type ? [
                        'id' => $room->type->id,
                        'name' => $room->type->name,
                        'code' => $room->type->code,
                        'base_capacity' => $room->type->base_capacity,
                        'max_capacity' => $room->type->max_capacity,
                        'amenities' => $room->type->amenities ? json_decode($room->type->amenities, true) : [],
                        'rates' => $room->type->rates->map(function ($rate) {
                            return [
                                'id' => $rate->id,
                                'name' => $rate->name,
                                'daily_rate' => $rate->amount,
                                'daily_rate_formatted' => number_format($rate->amount, 2),
                                'is_shared' => $rate->is_shared,
                                'effective_from' => $rate->effective_from,
                                'effective_until' => $rate->effective_until,
                            ];
                        }),
                    ] : null,
                ];
            });

            return response()->json(['rooms' => $roomsData, 'currency' => current_property()->currency ?? 'USD']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching available rooms: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $propertyId = selected_property_id();
        
        // get room types for the current property
        $roomTypes = RoomType::where('property_id', $propertyId)->get();
        
        // send the next available display order
        $maxOrder = Room::where('property_id', $propertyId)->max('display_order') ?? 0;
        $nextOrder = $maxOrder + 1;

        // send the next available room number(why is this giving a room number that is already taken? we need to check existing numbers and find the next available one)
        $existingNumbers = Room::where('property_id', $propertyId)->pluck('number')->toArray();
        $nextNumber = 1;
        while (in_array($nextNumber, $existingNumbers)) {
            $nextNumber++;
        }

        return view('tenant.rooms.create', compact('roomTypes', 'nextOrder', 'nextNumber', 'propertyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $propertyId = selected_property_id();

            // To-Do: Implement a checker to check tenant package limits
            
            // Handle checkbox fields
            $request->merge([
                'is_enabled' => $request->has('is_enabled'),
                'is_featured' => $request->has('is_featured')
            ]);

            // Validate the request
            $validated = $request->validate([
                'room_type_id' => 'required|exists:room_types,id',
                'number' => 'required|string|max:255|unique:rooms,number,NULL,id,property_id,' . $propertyId,
                'name' => 'required|string|max:255',
                'short_code' => 'required|string|max:50',
                'floor' => 'nullable|integer',
                'legacy_room_code' => 'nullable|integer|unique:rooms,legacy_room_code,NULL,id,property_id,' . $propertyId,
                'is_enabled' => 'required|boolean',
                'is_featured' => 'required|boolean',
                'display_order' => 'nullable|integer',
                'notes' => 'nullable|string',
                'web_description' => 'nullable|string',
                'description' => 'nullable|string',
                'web_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // max 2MB
            ]);

            // Sanitize HTML content
            if (isset($validated['description'])) {
                $validated['description'] = $this->htmlSanitizer->sanitize($validated['description']);
            }
            if (isset($validated['web_description'])) {
                $validated['web_description'] = $this->htmlSanitizer->sanitize($validated['web_description']);
            }

            // Handle image upload
            if ($request->hasFile('web_image')) {
                $file = $request->file('web_image');
                
                if (config('app.env') === 'production') {
                    // Production: Store in GCS
                    $tenant_id = tenant('id');
                    $gcsPath = 'tenant' . $tenant_id . '/room_images/' . uniqid() . '_' . $file->getClientOriginalName();
                    
                    $file->storeAs('/', $gcsPath, 'gcs');
                    $validated['web_image'] = $gcsPath;
                } else {
                    // Local development: Store in local storage with tenant isolation
                    $imagePath = $file->store('room_images', 'public');
                    $validated['web_image'] = $imagePath;
                }
            }

            // Handle display order logic for new room
            if (!isset($validated['display_order']) || !$validated['display_order']) {
                $maxOrder = Room::where('property_id', $propertyId)->max('display_order') ?? 0;
                $validated['display_order'] = $maxOrder + 1;
            } else {
                // If specific order requested, adjust other rooms
                $newOrder = $validated['display_order'];
                Room::where('property_id', $propertyId)
                    ->where('display_order', '>=', $newOrder)
                    ->increment('display_order');
            }

            // Create the room
            $room = Room::create(array_merge($validated, [
                'property_id' => $propertyId,
            ]));

            // Log activity
            $this->logTenantActivity(
                'create_room',
                'Created a new room: ' . $room->name. ' (ID: ' . $room->id . ') for property ID: ' . $propertyId,
                $room,
                [
                    'table' => 'rooms',
                    'id' => $room->id,
                    'user_id' => auth()->id(),
                    'changes' => $room->toArray()
                ]
            );

            return redirect()->route('tenant.rooms.index', ['property_id' => $propertyId])
                ->with('success', 'Room created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while creating the room: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        // Check if the room belongs to the current property
        if ($room->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $currency = current_property()->currency ?? 'USD';
        
        // Load relationships
        $room->load(['type', 'property', 'bookings' => function($query) {
            $query->latest()->limit(5);
        }]);

        // Get room rates
        $roomRates = $room->type ? $room->type->rates()->where('is_active', true)->get() : collect();

        // Generate image URL for display
        $imageUrl = null;
        if ($room->web_image) {
            // if image has https
            if (Str::startsWith($room->web_image, 'https://')) {
                $imageUrl = $room->web_image;
            } elseif (config('app.env') === 'production') {
                $gcsConfig = config('filesystems.disks.gcs');
                $bucket = $gcsConfig['bucket'] ?? null;
                $path = ltrim($room->web_image, '/');
                $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
            } else {
                $imageUrl = asset('storage/' . $room->web_image);
            }
        }

        return view('tenant.rooms.show', compact('room', 'currency', 'roomRates', 'imageUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        // Check if the room belongs to the current property
        if ($room->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $propertyId = selected_property_id();
        $currency = current_property()->currency ?? 'USD';

        $roomTypes = RoomType::where('property_id', $propertyId)->get();
        $room->load('type');
        
        // Get active rates for the room type
        $roomRates = $room->type ? $room->type->rates()->where('is_active', true)->get() : collect();

        // Generate image URL for display
        $imageUrl = null;
        if ($room->web_image) {
            // if image has https
            if (Str::startsWith($room->web_image, 'https://')) {
                $imageUrl = $room->web_image;
            } elseif (config('app.env') === 'production') {
                $gcsConfig = config('filesystems.disks.gcs');
                $bucket = $gcsConfig['bucket'] ?? null;
                $path = ltrim($room->web_image, '/');
                $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
            } else {
                $imageUrl = asset('storage/' . $room->web_image);
            }
        }

        return view('tenant.rooms.edit', compact('room', 'roomTypes', 'roomRates', 'currency', 'imageUrl', 'propertyId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        try {
            // Check if the room belongs to the current property
            if ($room->property_id !== selected_property_id()) {
                abort(403, 'Unauthorized action.');
            }

            $propertyId = selected_property_id();

            // Handle checkbox fields
            $request->merge([
                'is_enabled' => $request->has('is_enabled'),
                'is_featured' => $request->has('is_featured')
            ]);

            // Validate the request
            $validated = $request->validate([
                'room_type_id' => 'required|exists:room_types,id',
                'number' => 'required|string|max:255|unique:rooms,number,' . $room->id . ',id,property_id,' . $propertyId,
                'name' => 'required|string|max:255',
                'short_code' => 'required|string|max:50',
                'floor' => 'nullable|integer',
                'legacy_room_code' => 'nullable|integer|unique:rooms,legacy_room_code,' . $room->id . ',id,property_id,' . $propertyId,
                'is_enabled' => 'required|boolean',
                'is_featured' => 'required|boolean',
                'display_order' => 'nullable|integer',
                'notes' => 'nullable|string',
                'web_description' => 'nullable|string',
                'description' => 'nullable|string',
                'web_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // max 2MB
            ]);

            // Sanitize HTML content
            if (isset($validated['description'])) {
                $validated['description'] = $this->htmlSanitizer->sanitize($validated['description']);
            }
            if (isset($validated['web_description'])) {
                $validated['web_description'] = $this->htmlSanitizer->sanitize($validated['web_description']);
            }

            // Handle image upload
            if ($request->has('remove_image')) {
                // Delete the current image
                if ($room->web_image) {
                    if (config('app.env') === 'production') {
                        Storage::disk('gcs')->delete($room->web_image);
                    } else {
                        Storage::disk('public')->delete($room->web_image);
                    }
                }
                $validated['web_image'] = null;
            } 
            elseif ($request->hasFile('web_image')) {
                // Delete old image if exists
                if ($room->web_image) {
                    if (config('app.env') === 'production') {
                        Storage::disk('gcs')->delete($room->web_image);
                    } else {
                        Storage::disk('public')->delete($room->web_image);
                    }
                }
                
                // Store new image
                $file = $request->file('web_image');
                
                if (config('app.env') === 'production') {
                    $tenant_id = tenant('id');
                    $gcsPath = 'tenant' . $tenant_id . '/room_images/' . uniqid() . '_' . $file->getClientOriginalName();
                    
                    $file->storeAs('/', $gcsPath, 'gcs');
                    $validated['web_image'] = $gcsPath;
                } else {
                    $imagePath = $file->store('room_images', 'public');
                    $validated['web_image'] = $imagePath;
                }
            } 
            else {
                // Keep existing image
                $validated['web_image'] = $room->web_image;
            }

            // Handle display order logic
            if (isset($validated['display_order']) && $validated['display_order'] != $room->display_order) {
                $newOrder = $validated['display_order'];
                $oldOrder = $room->display_order;
                
                if ($newOrder < $oldOrder) {
                    // Moving up - increment orders between new and old position
                    Room::where('property_id', $propertyId)
                        ->where('display_order', '>=', $newOrder)
                        ->where('display_order', '<', $oldOrder)
                        ->increment('display_order');
                } else {
                    // Moving down - decrement orders between old and new position
                    Room::where('property_id', $propertyId)
                        ->where('display_order', '>', $oldOrder)
                        ->where('display_order', '<=', $newOrder)
                        ->decrement('display_order');
                }
            }

            // Update the room with validated data
            $room->update($validated);

            // Log activity
            $this->logTenantActivity(
                'room_updated',
                "Updated room: {$room->name} (#{$room->number})",
                $room,
                [
                    'table' => 'rooms',
                    'id' => $room->id,
                    'user_id' => auth()->id(),
                    'changes' => $room->getChanges()
                ]
            );

            return redirect()->route('tenant.rooms.index', ['property_id' => $propertyId])
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
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        try {
            // Check if the room belongs to the current property
            if ($room->property_id !== selected_property_id()) {
                abort(403, 'Unauthorized action.');
            }

            $propertyId = selected_property_id();
            $roomName = $room->name;
            $roomNumber = $room->number;

            // Delete associated image
            if ($room->web_image) {
                if (config('app.env') === 'production') {
                    Storage::disk('gcs')->delete($room->web_image);
                } else {
                    Storage::disk('public')->delete($room->web_image);
                }
            }

            // Log activity before deletion
            $this->logTenantActivity(
                'room_deleted', "Deleted room: {$roomName} (#{$roomNumber})", $room,
                [
                    'table' => 'rooms',
                    'id' => $room->id,
                    'user_id' => auth()->id(),
                    'changes' => $room->getChanges()
                ]
            );

            // Delete the room
            $room->delete();

            return redirect()->route('tenant.rooms.index', ['property_id' => $propertyId])
                ->with('success', 'Room deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while deleting the room: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle room status (enabled/disabled)
     */
    public function toggleStatus(Room $room)
    {
        try {
            // Check if the room belongs to the current property
            if ($room->property_id !== selected_property_id()) {
                abort(403, 'Unauthorized action.');
            }

            $room->is_enabled = !$room->is_enabled;
            $room->save();

            $status = $room->is_enabled ? 'enabled' : 'disabled';
            
            // Log activity
            $this->logTenantActivity(
                'room_status_changed',
                "Room {$room->name} (#{$room->number}) {$status}",
                $room, 
                [
                    'table' => 'rooms',
                    'id' => $room->id,
                    'user_id' => auth()->id(),
                    'changes' => $room->getChanges()
                ]
            );

            return redirect()->back()
                ->with('success', "Room {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while updating room status: ' . $e->getMessage()]);
        }
    }

    /**
     * Clone a room
     */
    public function clone(Room $room)
    {
        try {
            // Check if the room belongs to the current property
            if ($room->property_id !== selected_property_id()) {
                abort(403, 'Unauthorized action.');
            }

            $propertyId = selected_property_id();

            // Get next available number and order
            $maxNumber = Room::where('property_id', $propertyId)->max('number') ?? 0;
            $maxOrder = Room::where('property_id', $propertyId)->max('display_order') ?? 0;

            // Create clone
            $clonedRoom = $room->replicate();
            $clonedRoom->number = $maxNumber + 1;
            $clonedRoom->name = $room->name . ' (Copy)';
            $clonedRoom->display_order = $maxOrder + 1;
            $clonedRoom->web_image = null; // Don't copy image
            $clonedRoom->save();

            // Log activity
            $this->logTenantActivity(
                'room_cloned',
                "Cloned room: {$room->name} (#{$room->number}) to {$clonedRoom->name} (#{$clonedRoom->number})",
                $clonedRoom,
                [
                    'table' => 'rooms',
                    'id' => $clonedRoom->id,
                    'user_id' => auth()->id(),
                    'changes' => $clonedRoom->toArray()
                ]
            );

            return redirect()->route('tenant.rooms.edit', $clonedRoom)
                ->with('success', 'Room cloned successfully. Please update the details as needed.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while cloning the room: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the import form
     */
    public function importRooms()
    {
        return view('tenant.rooms.import');
    }

    /**
     * Process the CSV import
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
                'skip_header' => 'boolean',
                'update_existing' => 'boolean',
            ]);

            $propertyId = current_property()->id;
            $skipHeader = $request->boolean('skip_header');
            $updateExisting = $request->boolean('update_existing');

            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $csv = array_map('str_getcsv', file($path));

            $headers = [];
            $imported = 0;
            $updated = 0;
            $errors = [];

            foreach ($csv as $index => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Use first row as headers
                if ($index === 0) {
                    if ($skipHeader) {
                        $headers = array_map('trim', $row);
                        continue;
                    } else {
                        $headers = ['number', 'name', 'short_code', 'room_type_code', 'floor', 'legacy_room_code', 'description', 'web_description', 'notes', 'is_enabled', 'is_featured', 'display_order'];
                    }
                }

                try {
                    // Create associative array from row
                    $rowData = array_combine($headers, array_pad($row, count($headers), ''));
                    
                    // Validate required fields
                    if (empty($rowData['number']) || empty($rowData['name']) || empty($rowData['short_code']) || empty($rowData['room_type_code'])) {
                        $errors[] = "Row " . ($index + 1) . ": Missing required fields (number, name, short_code, room_type_code)";
                        continue;
                    }

                    // Find room type by legacy code
                    $roomType = RoomType::where('property_id', $propertyId)
                        ->where('legacy_code', $rowData['room_type_code'])
                        ->first();

                    if (!$roomType) {
                        $errors[] = "Row " . ($index + 1) . ": Room type with code '{$rowData['room_type_code']}' not found";
                        continue;
                    }

                    // Prepare room data
                    $roomData = [
                        'property_id' => $propertyId,
                        'room_type_id' => $roomType->id,
                        'number' => trim($rowData['number']),
                        'name' => trim($rowData['name']),
                        'short_code' => trim($rowData['short_code']),
                        'floor' => !empty($rowData['floor']) ? (int)$rowData['floor'] : null,
                        'legacy_room_code' => !empty($rowData['legacy_room_code']) ? (int)$rowData['legacy_room_code'] : null,
                        'description' => !empty($rowData['description']) ? $this->htmlSanitizer->sanitize($rowData['description']) : null,
                        'web_description' => !empty($rowData['web_description']) ? $this->htmlSanitizer->sanitize($rowData['web_description']) : null,
                        'notes' => !empty($rowData['notes']) ? trim($rowData['notes']) : null,
                        'is_enabled' => $this->parseBooleanValue($rowData['is_enabled'] ?? 'true'),
                        'is_featured' => $this->parseBooleanValue($rowData['is_featured'] ?? 'false'),
                        'display_order' => !empty($rowData['display_order']) ? (int)$rowData['display_order'] : null,
                    ];

                    // Check if room exists
                    $existingRoom = Room::where('property_id', $propertyId)
                        ->where('number', $roomData['number'])
                        ->first();

                    if ($existingRoom) {
                        if ($updateExisting) {
                            $existingRoom->update($roomData);
                            $updated++;
                            
                            // Log activity
                            $this->logActivity('room_imported_updated', "Updated room via import: {$roomData['name']} (#{$roomData['number']})", $existingRoom);
                        } else {
                            $errors[] = "Row " . ($index + 1) . ": Room number '{$roomData['number']}' already exists";
                            continue;
                        }
                    } else {
                        // Set display order if not provided
                        if (!$roomData['display_order']) {
                            $maxOrder = Room::where('property_id', $propertyId)->max('display_order') ?? 0;
                            $roomData['display_order'] = $maxOrder + 1;
                        }

                        $room = Room::create($roomData);
                        $imported++;
                        
                        // Log activity
                        $this->logActivity('room_imported_created', "Created room via import: {$roomData['name']} (#{$roomData['number']})", $room);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Prepare success message
            $messages = [];
            if ($imported > 0) {
                $messages[] = "{$imported} rooms imported successfully";
            }
            if ($updated > 0) {
                $messages[] = "{$updated} rooms updated successfully";
            }

            $successMessage = implode(', ', $messages);

            if (!empty($errors)) {
                $errorMessage = "Import completed with errors:\n" . implode("\n", $errors);
                return redirect()->back()
                    ->with('success', $successMessage)
                    ->with('error', $errorMessage);
            }

            return redirect()->route('tenant.rooms.index', ['property_id' => $propertyId])
                ->with('success', $successMessage ?: 'Import completed, but no rooms were processed.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download CSV template
     */
    public function template()
    {
        $headers = [
            'number',
            'name',
            'short_code',
            'room_type_code',
            'floor',
            'legacy_room_code',
            'description',
            'web_description',
            'notes',
            'is_enabled',
            'is_featured',
            'display_order'
        ];

        $sampleData = [
            ['101', 'Queen Standard Room', 'Q STD', 'ST', '1', '2', 'Standard queen room with en-suite bathroom', 'Comfortable queen room perfect for couples', 'Recently renovated', 'true', 'false', '1'],
            ['102', '2 X Single Standard', '2xSgl STD', 'ST', '1', '3', 'Twin room with two single beds', 'Twin room ideal for friends or business travelers', '', 'true', 'false', '2'],
            ['201', 'Garden Suite Twin', '2x3/4 GDN', 'GR', '2', '20', 'Spacious garden suite with twin beds', 'Beautiful suite overlooking the garden', 'Garden view', 'true', 'true', '3'],
        ];

        $filename = 'rooms_import_template.csv';

        $handle = fopen('php://output', 'w');
        
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add headers
        fputcsv($handle, $headers);

        // Add sample data
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }

    /**
     * Parse boolean values from various formats
     */
    private function parseBooleanValue($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        
        return in_array($value, ['1', 'true', 'yes', 'on', 'enabled']);
    }
}
