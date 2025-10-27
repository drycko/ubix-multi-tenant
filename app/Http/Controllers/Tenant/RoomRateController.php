<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\RoomRate;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\Property;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomRateController extends Controller
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
    
    // Build the query
    $query = RoomRate::with(['roomType', 'property']);

    // Temporary to add is_per_night condition if not exists
    // $roomRatesToUpdate = $query->whereNull('conditions->is_per_night')->get();
    // foreach ($roomRatesToUpdate as $rate) {
    //   $conditions = $rate->conditions ?? [];
    //   $conditions['is_per_night'] = false;
    //   $rate->conditions = $conditions;
    //   $rate->save();
    // }
    
    // Apply property filter
    if ($propertyId) {
      $query->where('property_id', $propertyId);
    } elseif (!is_super_user()) {
      // Non-super users can only see their property's rates
      $query->where('property_id', auth()->user()->property_id);
    }
    
    // Apply search filters
    if ($request->filled('search')) {
      $search = $request->get('search'); // search is case sensitive, let us make it insensitive by using lower function
      $query->where(function ($q) use ($search) {
        $q->where(DB::raw('lower(name)'), 'like', "%{$search}%")
        ->orWhereHas('roomType', function ($qt) use ($search) {
          $qt->where(DB::raw('lower(name)'), 'like', "%{$search}%");
        });
      });
    }
    
    if ($request->filled('rate_type')) {
      $query->where('rate_type', $request->get('rate_type'));
    }
    
    if ($request->filled('status')) {
      $status = $request->get('status');
      if ($status === 'active') {
        $query->where('is_active', true);
      } elseif ($status === 'inactive') {
        $query->where('is_active', false);
      }
    }
    
    // Order by creation date (newest first)
    $query->orderBy('created_at', 'desc');
    
    // Paginate results
    $roomRates = $query->paginate(15);
    
    return view('tenant.room-rates.index', compact('roomRates'));
  }
  /**
  * Show the form for importing room rates.
  */
  public function importRates()
  {
    return view('tenant.room-rates.import');
  }
  
  /**
  * Handle the import of room rates from a CSV file.
  */
  public function import(Request $request)
  {
    
    $request->validate([
      'csv_file' => 'required|file|mimes:csv,txt|max:2048',
    ]);
    
    try {
      // once off: let us delete all existing room rates for the property
      // RoomRate::where('property_id', current_property_id())->delete();
      $file = $request->file('csv_file');
      $data = array_map('str_getcsv', file($file->getRealPath()));
      
      // Process the CSV data
      foreach ($data as $row) {
        // Validate and create room rates
        // Example CSV content
        
        // rates sample data; we will only need 1 per room type
        // DATE is the effective date, RMTYPE is room type, RATEA(single) & RATEB(shared) are standard rates, RATEC(single) & RATED(shared) are off-season rates (loop through these to create multiple rates)
        // TR,RMCODE,RMTYPE,DATE,RATEA,RATEB,RATEC,RATED
        // 1,2,ST,05/09/2025,2800,2350,2250,1900
        // 2,3,ST,05/09/2025,2800,2350,2250,1900
        // 3,4,ST,05/09/2025,2800,2350,2250,1900
        // 4,5,ST,05/09/2025,2800,2350,2250,1900
        // 5,6,ST,05/09/2025,2800,2350,2250,1900
        // 6,7,ST,05/09/2025,2800,2350,2250,1900
        if (count($row) < 8 || $row[0] === 'TR') {
          continue; // Skip invalid rows or header
        }
        $roomTypeCode = $row[2];
        $date = \DateTime::createFromFormat('d/m/Y', $row[3]);
        if (!$date) {
          continue; // Skip invalid date formats
        }
        
        $roomType = RoomType::where('legacy_code', $roomTypeCode)
        ->where('property_id', selected_property_id())
        ->first();
        if (!$roomType) {
          continue; // Skip if room type not found
        }
        $rates = [
          ['type' => 'standard', 'amount' => $row[4]], // RATEA
          ['type' => 'shared_standard', 'amount' => $row[5]], // RATEB
          ['type' => 'off_season', 'amount' => $row[6]], // RATEC
          ['type' => 'shared_off_season', 'amount' => $row[7]], // RATED
        ];

        foreach ($rates as $rate) {
          if (is_numeric($rate['amount']) && $rate['amount'] > 0) {
            // rate_type is of enum type: 'standard', 'off_season', 'package'
            // we will map 'shared_standard' and 'shared_off_season' to 'standard' and 'off_season' respectively, but set is_shared to true
            if (str_starts_with($rate['type'], 'shared_')) {
              $rate['type'] = str_replace('shared_', '', $rate['type']);
              $isShared = true;
            } else {
              $isShared = false;
            }
            RoomRate::updateOrCreate(
              [
                'room_type_id' => $roomType->id,
                'property_id' => selected_property_id(),
                'rate_type' => $rate['type'],
                'effective_from' => $date->format('Y-m-d'),
                'is_shared' => $isShared,
              ],
              [
                'name' => ucfirst(str_replace('_', ' ', $rate['type'])) . " Rate",
                'amount' => $rate['amount'],
                'is_active' => true,
              ]
            );
          }
            
          // Get property for logging
          $property = Property::find(selected_property_id());
          $propertyCode = $property->code ?? '';
          
          // Log activity for each rate created/updated
          $this->logTenantActivity(
            'import_room_rate',
            'Imported/Updated room rate for room type: ' . $roomType->name . ' (ID: ' . $roomType->id . ') for property: ' . $propertyCode,
            null,
            [
              'table' => 'room_rates',
              'room_type_id' => $roomType->id,
              'property_id' => selected_property_id(),
              'rate_type' => $rate['type'],
              'effective_from' => $date->format('Y-m-d'),
              'is_shared' => $isShared,
              'amount' => $rate['amount'],
              'user_id' => auth()->id(),
              'action' => 'imported/updated'
            ]
          );
        }
        
      }
      
      return redirect()->route('tenant.room-rates.index')->with('success', 'Room rates imported successfully.');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Failed to import room rates: ' . $e->getMessage());
    }
  }
  /**
  * Show the form for creating a new resource.
  */
  public function create(Request $request)
  {
    // Get the property context
    $propertyId = selected_property_id();
    if (!$propertyId && !is_super_user()) {
      $propertyId = auth()->user()->property_id;
    }
    
    // super user must select a property
    if (!$propertyId && is_super_user()) {
      return redirect()->route('tenant.room-rates.index')->with('error', 'Please select a property to create room rates.');
    }
    
    // Get room types for the property
    $roomTypes = RoomType::where('property_id', $propertyId)
    ->where('is_active', true)
    ->orderBy('id', 'desc')
    ->get();
    
    \Log::info('Creating room rate for property ID: ' . $propertyId . ', found ' . $roomTypes->count() . ' room types.');
    
    return view('tenant.room-rates.create', compact('roomTypes', 'propertyId'));
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
      'room_type_id' => 'required|exists:room_types,id',
      'name' => 'required|string|max:255',
      'rate_type' => 'required|string|in:standard,off_season,package',
      'effective_from' => 'required|date',
      'effective_until' => 'nullable|date|after_or_equal:effective_from',
      'amount' => 'required|numeric|min:0',
      'min_nights' => 'nullable|integer|min:1',
      'max_nights' => 'nullable|integer|min:1|gte:min_nights',
      'is_shared' => 'boolean',
      'is_active' => 'boolean',
      'conditions' => 'nullable|array',
    ]);
    
    // Set defaults for checkboxes
    $validated['is_shared'] = $request->has('is_shared');
    $validated['is_active'] = $request->has('is_active');
    // set conditions
    $conditions = $validated['conditions'] ?? [];

    $validated['conditions']['is_per_night'] = $request->is_per_night == 1 ? true : false;
    foreach ($conditions as $condition) {
      $key = $condition['key'] ?? null;
      $value = $condition['value'] ?? null;
      // add conditions to validated array
      if ($key && $value) {
        $validated['conditions'][] = [
          'key' => $key,
          'value' => $value,
        ];
      }
    }
    
    // Create the room rate
    $roomRate = RoomRate::create(array_merge($validated, ['property_id' => $propertyId]));
    
    // Log activity
    $this->logTenantActivity(
      'create_room_rate',
      'Created room rate: ' . $roomRate->name . ' (ID: ' . $roomRate->id . ') for property: ' . $propertyCode,
      $roomRate,
      [
        'table' => 'room_rates',
        'id' => $roomRate->id,
        'user_id' => auth()->id(),
        'changes' => $roomRate->toArray()
      ],
    );
    
    return redirect()->route('tenant.room-rates.index', ['property_id' => $propertyId])
    ->with('success', 'Room rate created successfully.');
  }
      
  /**
  * Display the specified resource.
  */
  public function show(RoomRate $roomRate)
  {
    // Ensure the room rate belongs to the identified property
    if (!is_super_user() && $roomRate->property_id !== auth()->user()->property_id) {
      abort(403);
    }
    
    $roomRate->load(['roomType', 'property']);
    $rateBasis = $roomRate->conditions['is_per_night'] ?? false ? 'per night' : 'per person';
    $currency = tenant_currency();

    return view('tenant.room-rates.show', compact('roomRate', 'currency', 'rateBasis'));
  }
  
  /**
  * Show the form for editing the specified resource.
  */
  public function edit(RoomRate $roomRate)
  {
    // Ensure the room rate belongs to the identified property
    if (!is_super_user() && $roomRate->property_id !== auth()->user()->property_id) {
      abort(403);
    }
    
    // Get room types for the property
    $roomTypes = RoomType::where('property_id', $roomRate->property_id)
    ->where('is_active', true)
    ->orderBy('name')
    ->get();
    
    return view('tenant.room-rates.edit', compact('roomRate', 'roomTypes'));
  }
  
  /**
  * Update the specified resource in storage.
  */
  public function update(Request $request, RoomRate $roomRate)
  {
    // Ensure the room rate belongs to the identified property
    if (!is_super_user() && $roomRate->property_id !== auth()->user()->property_id) {
      abort(403);
    }
    
    $validated = $request->validate([
      'room_type_id' => 'required|exists:room_types,id',
      'name' => 'required|string|max:255',
      'rate_type' => 'required|string|in:standard,off_season,package',
      'effective_from' => 'required|date',
      'effective_until' => 'nullable|date|after_or_equal:effective_from',
      'amount' => 'required|numeric|min:0',
      'min_nights' => 'nullable|integer|min:1',
      'max_nights' => 'nullable|integer|min:1|gte:min_nights',
      'is_shared' => 'boolean',
      'is_active' => 'boolean',
      'conditions' => 'nullable|array',
    ]);
    
    // Set defaults for checkboxes
    $validated['is_shared'] = $request->has('is_shared');
    $validated['is_active'] = $request->has('is_active');
    // $validated['conditions'] = $validated['conditions'] ?? [];
    // set conditions
    $conditions = $validated['conditions'] ?? [];
    foreach ($conditions as $condition) {
      $key = $condition['key'] ?? null;
      $value = $condition['value'] ?? null;
      // add conditions to validated array
      if ($key && $value) {
        // remove existing conditions to avoid duplication
        $validated['conditions'] = [];
        $validated['conditions'][] = [
          'key' => $key,
          'value' => $value,
        ];
      }
    }

    $is_per_night = $request->is_per_night == 1 ? true : false;

    $validated['conditions']['is_per_night'] = $is_per_night;

    // Store old values for logging
    $oldValues = $roomRate->toArray();
    
    // Update the room rate
    $roomRate->update($validated);
    
    // Log activity
    $propertyCode = $roomRate->property->code ?? '';
    $this->logTenantActivity(
      'update_room_rate',
      'Updated room rate: ' . $roomRate->name . ' (ID: ' . $roomRate->id . ') for property: ' . $propertyCode,
      $roomRate,
      [
        'table' => 'room_rates',
        'id' => $roomRate->id,
        'user_id' => auth()->id(),
        'old_values' => $oldValues,
        'new_values' => $roomRate->fresh()->toArray()
      ]
    );
    
    return redirect()->route('tenant.room-rates.index', ['property_id' => $roomRate->property_id])
    ->with('success', 'Room rate updated successfully.');
  }
      
  /**
  * Remove the specified resource from storage.
  */
  public function destroy(RoomRate $roomRate)
  {
    // Ensure the room rate belongs to the identified property
    if (!is_super_user() && $roomRate->property_id !== auth()->user()->property_id) {
      abort(403);
    }
    
    $propertyCode = $roomRate->property->code ?? '';
    $rateName = $roomRate->name;
    $rateId = $roomRate->id;
    
    // Log activity before deletion
    $this->logTenantActivity(
      'delete_room_rate',
      'Deleted room rate: ' . $rateName . ' (ID: ' . $rateId . ') for property: ' . $propertyCode,
      $roomRate,
      [
        'table' => 'room_rates',
        'id' => $rateId,
        'user_id' => auth()->id(),
        'deleted_data' => $roomRate->toArray()
      ]
    );
    
    $roomRate->delete();
    
    return redirect()->route('tenant.room-rates.index', ['property_id' => $roomRate->property_id])
    ->with('success', 'Room rate deleted successfully.');
  }
        
  /**
  * Toggle the status of the specified resource.
  */
  public function toggleStatus(RoomRate $roomRate)
  {
    // Ensure the room rate belongs to the identified property
    if (!is_super_user() && $roomRate->property_id !== auth()->user()->property_id) {
      abort(403);
    }

    try {
      
      // looks like room does not come with any data, validate first
      if (!$roomRate->property) {
        \Log::warning('Room rate ID: ' . $roomRate->id . ' is not associated with a valid property.');
        return redirect()->back()->with('error', 'Invalid room rate.');
      }
      // $roomRate->load('property');
      \Log::info('Toggling status for room rate ID: ' . $roomRate->id . ' in property: ' . ($roomRate->property->code ?? 'N/A'));

      // start transaction
      DB::beginTransaction();
      $oldStatus = $roomRate->is_active;
      $roomRate->update(['is_active' => !$oldStatus]);
      
      $propertyCode = $roomRate->property->code ?? '';
      $action = $roomRate->is_active ? 'activated' : 'deactivated';
      
      // Log activity
      $this->logTenantActivity(
        'toggle_room_rate_status',
        'Room rate ' . $action . ': ' . $roomRate->name . ' (ID: ' . $roomRate->id . ') for property: ' . $propertyCode,
        $roomRate,
        [
          'table' => 'room_rates',
          'id' => $roomRate->id,
          'user_id' => auth()->id(),
          'old_status' => $oldStatus,
          'new_status' => $roomRate->is_active
        ]
      );
      DB::commit();
      return redirect()->back()->with('success', 'Room rate status updated successfully.');
    } catch (\Exception $e) {
      // Handle the case where the property relationship fails
      DB::rollBack();
      return redirect()->back()->with('error', 'Failed to toggle room rate status.');
    }
  }
          
  /**
  * Clone the specified resource.
  */
  public function clone(RoomRate $roomRate)
  {
    // Ensure the room rate belongs to the identified property
    if (!is_super_user() && $roomRate->property_id !== auth()->user()->property_id) {
      abort(403);
    }
    
    // Create a copy with modified name
    $clonedData = $roomRate->toArray();
    unset($clonedData['id'], $clonedData['created_at'], $clonedData['updated_at'], $clonedData['deleted_at']);
    $clonedData['name'] = $roomRate->name . ' (Copy)';
    $clonedData['is_active'] = false; // Start as inactive
    
    $clonedRate = RoomRate::create($clonedData);
    
    $propertyCode = $roomRate->property->code ?? '';
    
    // Log activity
    $this->logTenantActivity(
      'clone_room_rate',
      'Cloned room rate: ' . $roomRate->name . ' to ' . $clonedRate->name . ' (ID: ' . $clonedRate->id . ') for property: ' . $propertyCode,
      $clonedRate,
      [
        'table' => 'room_rates',
        'id' => $clonedRate->id,
        'user_id' => auth()->id(),
        'original_id' => $roomRate->id,
        'cloned_data' => $clonedData
      ]
    );
    
    return redirect()->route('tenant.room-rates.edit', $clonedRate)
    ->with('success', 'Room rate cloned successfully. Please review and update as needed.');
  }
}