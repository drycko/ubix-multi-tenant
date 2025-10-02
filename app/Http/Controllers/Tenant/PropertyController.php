<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Property;
use App\Models\Tenant\Room;
use App\Models\Tenant\User;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Guest;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        // Ensure only super-users can access property management
        $this->middleware(function ($request, $next) {
            if (!is_super_user()) {
                abort(403, 'Only super-users can manage properties.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of properties.
     */
    public function index()
    {
        $properties = Property::with(['users', 'rooms'])
            ->withCount(['rooms', 'users'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('tenant.properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        $currencies = $this->getCurrencies();
        $timezones = $this->getTimezones();
        
        return view('tenant.properties.create', compact('currencies', 'timezones'));
    }

    /**
     * Store a newly created property.
     */
    public function store(Request $request)
    {
        try {
            // start transaction
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:properties,code',
                // 'description' => 'nullable|string|max:1000', not needed
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'zip_code' => 'required|string|max:20',
                'country' => 'required|string|max:100',
                'timezone' => 'required|string|max:50',
                'currency' => 'required|string|size:3',
                'locale' => 'required|string|max:10',
                'max_rooms' => 'required|integer|min:1|max:1000',
                'website' => 'nullable|url|max:255',
                'check_in_time' => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->has('is_active');

            // website, check_in_time, check_out_time fields to be added to settings json
            $settings = [];
            if ($request->filled('website')) {
                $settings['website'] = $request->input('website');
            }
            if ($request->filled('check_in_time')) {
                $settings['check_in_time'] = $request->input('check_in_time');
            }
            if ($request->filled('check_out_time')) {
                $settings['check_out_time'] = $request->input('check_out_time');
            }
            $settings['allow_guests_to_book_online'] = true;
            $settings['show_room_prices_to_guests'] = true;
            $settings['send_booking_confirmation_email_to_guests'] = true;
            $settings['send_booking_notification_email_to_property_manager'] = true;
            if (!empty($settings)) {
                $validated['settings'] = json_encode($settings);
            }
            $property = Property::create($validated);

            if (!$property) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create property. Please try again.');
            }

            // log activity
            $this->logTenantActivity(
                'create_property',
                'Created property: ' . $property->name . ' (ID: ' . $property->id . ')',
                $property, // Pass the property object as subject
                [
                    'table' => 'properties',
                    'property_id' => $property->id,
                    'user_id' => auth()->id(),
                    'changes' => $property->toArray()
                ]
            );

            // log notification
            $this->createTenantNotification(
                'success',
                'Property Created',
                'Property "' . $property->name . '" has been created successfully.',
                ['property_id' => $property->id],
                ['icon' => 'fa fa-building', 'color' => 'green']
            );

            // Commit transaction
            DB::commit();
            return redirect()->route('tenant.properties.index')
                ->with('success', 'Property created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the property: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property)
    {
        $property->load(['users.roles', 'rooms.type']);
        
        $stats = [
            'rooms_count' => Room::where('property_id', $property->id)->count(),
            'users_count' => User::where('property_id', $property->id)->count(),
            'bookings_count' => Booking::where('property_id', $property->id)->count(),
            'guests_count' => Guest::where('property_id', $property->id)->count(),
        ];

        $recentBookings = Booking::where('property_id', $property->id)
            ->with(['room', 'bookingGuests.guest'])
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.properties.show', compact('property', 'stats', 'recentBookings'));
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit(Property $property)
    {
        $currencies = $this->getCurrencies();
        $timezones = $this->getTimezones();

        $property->rooms_count = Room::where('property_id', $property->id)->count();
        $property->users_count = User::where('property_id', $property->id)->count();
        $property->website = $property->settings ? json_decode($property->settings, true)['website'] ?? null : null;
        $property->check_in_time = $property->settings ? json_decode($property->settings, true)['check_in_time'] ?? null : null;
        $property->check_out_time = $property->settings ? json_decode($property->settings, true)['check_out_time'] ?? null : null;

        return view('tenant.properties.edit', compact('property', 'currencies', 'timezones'));
    }

    /**
     * Update the specified property.
     */
    public function update(Request $request, Property $property)
    {
        try {
            // start transaction
            DB::beginTransaction();
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:properties,code,' . $property->id,
                // 'description' => 'nullable|string|max:1000',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'zip_code' => 'required|string|max:20',
                'country' => 'required|string|max:100',
                'timezone' => 'required|string|max:50',
                'currency' => 'required|string|size:3',
                'locale' => 'required|string|max:10',
                'website' => 'nullable|url|max:255', // newly added should be in the settings json
                'max_rooms' => 'required|integer|min:1|max:1000',
                'check_in_time' => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->has('is_active');
            // add the new website, check_in_time, check_out_time fields to settings json
            $settings = $property->settings ? json_decode($property->settings, true) : [];
            if ($request->filled('website')) {
                $settings['website'] = $request->input('website');
            } else {
                unset($settings['website']);
            }
            if ($request->filled('check_in_time')) {
                $settings['check_in_time'] = $request->input('check_in_time');
            } else {
                unset($settings['check_in_time']);
            }
            if ($request->filled('check_out_time')) {
                $settings['check_out_time'] = $request->input('check_out_time');
            } else {
                unset($settings['check_out_time']);
            }
            if (!empty($settings)) {
                $validated['settings'] = json_encode($settings);
            } else {
                $validated['settings'] = null;
            }

            $property->update($validated);

            // log activity
            $this->logTenantActivity(
                'update_property',
                'Updated property: ' . $property->name . ' (ID: ' . $property->id . ')',
                $property, // Pass the property object as subject
                [
                    'table' => 'properties',
                    'property_id' => $property->id,
                    'user_id' => auth()->id(),
                    'changes' => $property->getChanges()
                ]
            );

            // log notification
            $this->createTenantNotification(
                'info',
                'Property Updated',
                'Property "' . $property->name . '" has been updated successfully.',
                ['property_id' => $property->id],
                ['icon' => 'fa fa-building', 'color' => 'blue']
            );
            // Commit transaction
            DB::commit();
            return redirect()->route('tenant.properties.index')
                ->with('success', 'Property updated successfully!');
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the property: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Property $property)
    {
        // Check if property has any rooms or bookings
        $roomsCount = $property->rooms()->count();
        $bookingsCount = Booking::where('property_id', $property->id)->count();

        if ($roomsCount > 0 || $bookingsCount > 0) {
            return redirect()->route('tenant.properties.index')
                ->with('error', 'Cannot delete property that has rooms or bookings. Please remove them first.');
        }

        $property->delete();

        // log activity
        $this->logTenantActivity(
            'delete_property',
            'Deleted property: ' . $property->name . ' (ID: ' . $property->id . ')',
            $property, // Pass the property object as subject
            [
                'table' => 'properties',
                'property_id' => $property->id,
                'user_id' => auth()->id(),
                'changes' => $property->toArray()
            ]
        );

        // log notification
        $this->createTenantNotification(
            'warning',
            'Property Deleted',
            'Property "' . $property->name . '" has been deleted.',
            ['property_id' => $property->id],
            ['icon' => 'fa fa-building', 'color' => 'red']
        );

        return redirect()->route('tenant.properties.index')
            ->with('success', 'Property deleted successfully!');
    }

    /**
     * Toggle property active status.
     */
    public function toggleStatus(Property $property)
    {
        $property->update(['is_active' => !$property->is_active]);

        $status = $property->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Property {$status} successfully!");
    }

    /**
     * Clone a property.
     */
    public function clone(Property $property)
    {
        $newProperty = $property->replicate();
        $newProperty->name = $property->name . ' (Copy)';
        $newProperty->code = $property->code . '_copy_' . time();
        $newProperty->save();

        return redirect()->route('tenant.properties.index')
            ->with('success', 'Property cloned successfully!');
    }

    /**
     * Get available currencies.
     */
    private function getCurrencies()
    {
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'ZAR' => 'South African Rand',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'JPY' => 'Japanese Yen',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',
            'INR' => 'Indian Rupee',
        ];
    }

    /**
     * Get available timezones.
     */
    private function getTimezones()
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Africa/Johannesburg' => 'Johannesburg',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Australia/Sydney' => 'Sydney',
        ];
    }

    // Legacy methods for backward compatibility
    public function updateSettings(Request $request)
    {
        if (!is_super_user()) {
            abort(403, 'Only super-users can update property settings.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'currency' => 'required|string|size:3',
        ]);

        $property = current_property();
        if (!$property) {
            return redirect()->back()->with('error', 'No property selected.');
        }

        $property->update($request->only(['name', 'address', 'phone', 'email', 'currency']));

        return redirect()->back()
            ->with('success', 'Property settings updated successfully!');
    }

    public function storeUser(Request $request)
    {
        if (!is_super_user()) {
            abort(403, 'Only super-users can create users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name'
        ]);

        $property = current_property();
        if (!$property) {
            return redirect()->back()->with('error', 'No property selected.');
        }

        $user = \App\Models\Tenant\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'property_id' => $property->id,
        ]);

        $user->assignRole($request->role);

        return redirect()->back()
            ->with('success', 'User created successfully!');
    }

    public function select()
    {
        $properties = Property::where('is_active', true)->get();
        return view('tenant.properties.select', compact('properties'));
    }

    public function storeSelection(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id'
        ]);

        session(['selected_property_id' => $request->property_id]);

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Property selected successfully!');
    }

    public function settings()
    {
        if (!is_super_user()) {
            abort(403, 'Only super-users can access property settings.');
        }

        $property = current_property();
        if (!$property) {
            return redirect()->route('tenant.properties.select')
                ->with('error', 'Please select a property first.');
        }

        $users = $property->users;
        $roles = \App\Models\Tenant\Role::all();
        $currency = config('app.defaults.currency', 'USD');

        return view('tenant.properties.settings', compact('property', 'users', 'roles', 'currency'));
    }

    public function storeApiKey(Request $request)
    {
        if (!is_super_user()) {
            abort(403, 'Only super-users can manage API keys.');
        }

        $validated = $request->validate([
            'api_name' => 'nullable|string|max:255',
            'api_key' => 'required|string|max:255|unique:property_apis',
            'api_secret' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['property_id'] = current_property()->id;

        \App\Models\Tenant\PropertyApi::create($validated);

        return redirect()->back()
            ->with('success', 'API key created successfully!');
    }

    public function deleteApiKey(Request $request, $id)
    {
        if (!is_super_user()) {
            abort(403, 'Only super-users can delete API keys.');
        }

        $propertyApi = \App\Models\Tenant\PropertyApi::where('id', $id)
            ->where('property_id', current_property()->id)
            ->firstOrFail();
        
        $propertyApi->delete();

        return redirect()->back()
            ->with('success', 'API key deleted successfully!');
    }
}

