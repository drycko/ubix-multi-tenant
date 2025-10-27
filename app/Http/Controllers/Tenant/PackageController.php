<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Room;
use App\Models\Tenant\Package;
use App\Models\Tenant\RoomPackage;
use Illuminate\Http\Request;
use App\Services\HtmlSanitizerService;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Storage\StorageClient;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    use LogsTenantUserActivity;

    public function __construct()
    {
        $this->middleware(['auth:tenant', 'permission:view room packages'])->only(['index', 'show']);
        $this->middleware(['auth:tenant', 'permission:create room packages'])->only(['create', 'store', 'importPackage', 'import']);
        $this->middleware(['auth:tenant', 'permission:edit room packages'])->only(['edit', 'update']);
        $this->middleware(['auth:tenant', 'permission:delete room packages'])->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $propertyId = $request->get('property_id') ?: selected_property_id();
        
        if (!$propertyId) {
            return redirect()->back()->with('error', 'Please select a property to view packages.');
        }

        $tenantId = tenant('id');
        \Log::info('Tenant ID: ' . $tenantId);

        // build query
        $query = Package::where('property_id', $propertyId)->with(['rooms']);

        // Apply filters
        if ($request->filled('checkin_days')) {
            $days = explode(',', $request->input('checkin_days'));
            $query->where(function($q) use ($days) {
                foreach ($days as $day) {
                    $q->orWhere('pkg_checkin_days', 'LIKE', '%' . trim($day) . '%');
                }
            });
        }
        if ($request->filled('status')) {
            $query->where('pkg_status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('pkg_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('pkg_sub_title', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('pkg_description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Paginate results
        $packages = $query->orderBy('created_at', 'desc')->paginate(15);

        $currency = property_currency();
        
        // $this->logActivity('viewed_packages_list', [
        //     'property_id' => $propertyId,
        //     'total_packages' => $packages->total()
        // ]);
        
        return view('tenant.room-packages.index', compact('packages', 'currency', 'tenantId', 'propertyId'));
    }

    /**
     * Show the form for importing resources.
     */
    public function importPackage()
    {
        return view('tenant.room-packages.import');
    }

    /**
     * Handle the import of packages from a CSV file.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $propertyId = selected_property_id();
            if (!$propertyId) {
                return redirect()->back()->with('error', 'Please select a property before importing packages.');
            }

            $file = $request->file('csv_file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            $imported = 0;

            foreach ($data as $row) {
                // Skip the header row
                if ($row[0] === 'pkg_id') {
                    continue;
                }
                
                // Validate row has enough columns
                if (count($row) < 7) {
                    continue;
                }
                
                // Status mapping
                $status = 'active';
                if (isset($row[6])) {
                    if ($row[6] === 'available') {
                        $status = 'active';
                    } elseif ($row[6] === 'unavailable') {
                        $status = 'inactive';
                    } else {
                        $status = $row[6];
                    }
                }

                // Create the package
                Package::create([
                    'property_id' => $propertyId,
                    'pkg_id' => $row[0] ?? null,
                    'pkg_name' => $row[1] ?? 'Imported Package',
                    'pkg_sub_title' => $row[2] ?? '',
                    'pkg_description' => $row[3] ?? '',
                    'pkg_number_of_nights' => $row[4] ?? 1,
                    'pkg_checkin_days' => $row[5] ?? json_encode(['Monday']),
                    'pkg_status' => $status,
                    'pkg_enterby' => auth()->user()->id,
                    'pkg_image' => $row[9] ?? null,
                ]);
                
                $imported++;
            }

            $this->logTenantActivity(
                'imported_packages',
                'Imported packages from CSV file',
                null, // No specific subject for this activity
                [
                    'table' => 'properties',
                    'id' => $propertyId,
                    'user_id' => auth()->id(),
                    'changes' => []
                ]
            );

            return redirect()->route('tenant.room-packages.index', ['property_id' => $propertyId])
                ->with('success', "Successfully imported {$imported} packages.");
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to import packages: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $propertyId = $request->get('property_id') ?: selected_property_id();
        
        if (!$propertyId) {
            return redirect()->back()->with('error', 'Please select a property before creating packages.');
        }

        $rooms = Room::where('property_id', $propertyId)->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $currency = property_currency();
        $maxNumOfNights = 30;
        
        return view('tenant.room-packages.create', compact('rooms', 'daysOfWeek', 'maxNumOfNights', 'currency', 'propertyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, HtmlSanitizerService $htmlSanitizer)
    {
        $propertyId = $request->get('property_id') ?: selected_property_id();
        
        if (!$propertyId) {
            return redirect()->back()->with('error', 'Please select a property to create packages.');
        }

        // Validate the request
        $validated = $request->validate([
            'pkg_name' => 'required|string|max:255',
            'pkg_sub_title' => 'required|string|max:500',
            'pkg_description' => 'nullable|string',
            'pkg_number_of_nights' => 'required|integer|min:1|max:30',
            'pkg_checkin_days' => 'required|array|min:1',
            'pkg_checkin_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'pkg_rooms' => 'required|array|min:1',
            'pkg_rooms.*' => 'integer|exists:rooms,id',
            'pkg_status' => 'required|in:active,inactive',
            'pkg_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pkg_base_price' => 'required|numeric|min:0',
            'pkg_inclusions' => 'nullable|array',
            'pkg_exclusions' => 'nullable|array',
            'pkg_min_guests' => 'nullable|integer|min:1',
            'pkg_max_guests' => 'nullable|integer|min:1|gte:pkg_min_guests',
            'pkg_valid_from' => 'nullable|date',
            'pkg_valid_to' => 'nullable|date|after_or_equal:pkg_valid_from',
        ]);

        try {
            DB::beginTransaction();

            // Initialize image path variable
            $imagePath = null;

            $tenant_id = tenant('id');

            // Handle image upload to Google Cloud Storage if in production
            if ($request->hasFile('pkg_image') && config('app.env') === 'production') {
                $file = $request->file('pkg_image');
                $gcsPath = 'tenant' . $tenant_id . '/package-images/' . uniqid() . '_' . $file->getClientOriginalName();
                $stream = fopen($file->getRealPath(), 'r');
                Storage::disk('gcs')->put($gcsPath, $stream);
                fclose($stream);
                $imagePath = $gcsPath;
            }
            elseif ($request->hasFile('pkg_image')) {
                // If not in production, handle local storage (optional)
                $file = $request->file('pkg_image');
                // tenant already stores the files in tenant specific folder like (storage\tenant1\app\public\package_images) we need to adjust the path on show and index methods or blades accordingly
                $imagePath = $file->store('package-images', 'public');
            }

            // Sanitize HTML input
            $sanitizedDescription = $htmlSanitizer->sanitize($validated['pkg_description'] ?? '');

            // Create the package
            $package = Package::create([
                'property_id' => $propertyId,
                'pkg_name' => $validated['pkg_name'],
                'pkg_sub_title' => $validated['pkg_sub_title'],
                'pkg_description' => $sanitizedDescription,
                'pkg_number_of_nights' => $validated['pkg_number_of_nights'],
                'pkg_checkin_days' => json_encode($validated['pkg_checkin_days']),
                'pkg_status' => $validated['pkg_status'],
                'pkg_enterby' => auth()->user()->id,
                'pkg_image' => $imagePath,
                'pkg_base_price' => $validated['pkg_base_price'],
                'pkg_inclusions' => isset($validated['pkg_inclusions']) ? json_encode(array_filter($validated['pkg_inclusions'])) : null,
                'pkg_exclusions' => isset($validated['pkg_exclusions']) ? json_encode(array_filter($validated['pkg_exclusions'])) : null,
                'pkg_min_guests' => $validated['pkg_min_guests'] ?? null,
                'pkg_max_guests' => $validated['pkg_max_guests'] ?? null,
                'pkg_valid_from' => $validated['pkg_valid_from'] ?? null,
                'pkg_valid_to' => $validated['pkg_valid_to'] ?? null,
            ]);

            // Associate rooms with the package
            if (isset($validated['pkg_rooms'])) {
                foreach ($validated['pkg_rooms'] as $roomId) {
                    RoomPackage::firstOrCreate([
                        'room_id' => $roomId,
                        'package_id' => $package->id,
                    ]);
                }
            }

            $this->logTenantActivity(
                'created_package',
                'Created package: ' . $package->pkg_name . ' (ID: ' . $package->id . ') for property: ' . $propertyId,
                $package, // Pass the package object as subject
                [
                    'table' => 'packages',
                    'id' => $package->id,
                    'user_id' => auth()->id(),
                    'changes' => $package->toArray()
                ]
            );

            DB::commit();

            return redirect()->route('tenant.room-packages.index', ['property_id' => $propertyId])
                ->with('success', 'Package created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $roomPackage)
    {
        // Check property access
        if ($roomPackage->property_id && $roomPackage->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized access to package.');
        }

        $tenant_id = tenant('id');
        \Log::info('Tenant ID: ' . $tenant_id);

        $roomPackage->load(['rooms']);
        $currency = property_currency();
        
        $this->logTenantActivity(
                'viewed_package',
                'Viewed package: ' . $roomPackage->pkg_name . ' (ID: ' . $roomPackage->id . ') for property: ' . $roomPackage->property_id,
                $roomPackage, // Pass the package object as subject
                [
                    'table' => 'packages',
                    'id' => $roomPackage->id,
                    'user_id' => auth()->id(),
                    'changes' => []
                ]
            );

        return view('tenant.room-packages.show', compact('roomPackage', 'currency', 'tenant_id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $roomPackage)
    {
        if (!$roomPackage) {
            abort(404, 'Package not found.');
        }

        if (selected_property_id() === null) {
            abort(403, 'Unauthorized action. No property context available.');
        }

        // Allow access to packages from current property OR packages with no property
        if ($roomPackage->property_id && $roomPackage->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action. This package belongs to a different property.');
        }

        // Load relationships
        $roomPackage->load(['rooms']);
        
        // Get available rooms for this property
        $rooms = Room::where('property_id', selected_property_id())->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $currency = property_currency();
        $maxNumOfNights = 30;

        // Format dates for input fields
        $pkg_valid_from = $roomPackage->pkg_valid_from ? $roomPackage->pkg_valid_from->format('Y-m-d') : null;
        $pkg_valid_to = $roomPackage->pkg_valid_to ? $roomPackage->pkg_valid_to->format('Y-m-d') : null;

        // if in production
        if (config('app.env') === 'production') {
            // Get full GCS image URL if image exists
            $gcsImageUrl = null;
            if ($roomPackage->pkg_image) {
                $gcsConfig = config('filesystems.disks.gcs');
                $bucket = $gcsConfig['bucket'] ?? null;
                $path = ltrim($roomPackage->pkg_image, '/');
                if ($bucket) {
                    $gcsImageUrl = "https://storage.googleapis.com/{$bucket}/{$path}";
                }
            }
        } else {
            // For local storage in multi-tenant setup
            $gcsImageUrl = $roomPackage->pkg_image ? asset('storage/' . $roomPackage->pkg_image) : null;
        }

        // Get the selected rooms from the pivot table
        $selectedRooms = RoomPackage::where('package_id', $roomPackage->id)->pluck('room_id')->toArray();

        // Check-in days as arrays
        $selectedCheckinDays = is_string($roomPackage->pkg_checkin_days) 
            ? json_decode($roomPackage->pkg_checkin_days, true) ?? []
            : (is_array($roomPackage->pkg_checkin_days) ? $roomPackage->pkg_checkin_days : []);

        // Parse inclusions and exclusions
        $inclusions = is_string($roomPackage->pkg_inclusions) 
            ? json_decode($roomPackage->pkg_inclusions, true) ?? []
            : (is_array($roomPackage->pkg_inclusions) ? $roomPackage->pkg_inclusions : []);
            
        $exclusions = is_string($roomPackage->pkg_exclusions) 
            ? json_decode($roomPackage->pkg_exclusions, true) ?? []
            : (is_array($roomPackage->pkg_exclusions) ? $roomPackage->pkg_exclusions : []);

        return view('tenant.room-packages.edit', compact(
            'roomPackage', 'rooms', 'daysOfWeek', 'selectedRooms', 'selectedCheckinDays', 
            'maxNumOfNights', 'currency', 'pkg_valid_from', 'pkg_valid_to', 'gcsImageUrl',
            'inclusions', 'exclusions'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $roomPackage, HtmlSanitizerService $htmlSanitizer)
    {
        // Check property access
        if ($roomPackage->property_id && $roomPackage->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized access to package.');
        }

        // Validate the request
        $validated = $request->validate([
            'pkg_name' => 'required|string|max:255',
            'pkg_sub_title' => 'required|string|max:500',
            'pkg_description' => 'nullable|string',
            'pkg_number_of_nights' => 'required|integer|min:1|max:30',
            'pkg_checkin_days' => 'required|array|min:1',
            'pkg_checkin_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'pkg_rooms' => 'required|array|min:1',
            'pkg_rooms.*' => 'integer|exists:rooms,id',
            'pkg_status' => 'required|in:active,inactive',
            'pkg_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pkg_base_price' => 'required|numeric|min:0',
            'pkg_inclusions' => 'nullable|array',
            'pkg_exclusions' => 'nullable|array',
            'pkg_min_guests' => 'nullable|integer|min:1',
            'pkg_max_guests' => 'nullable|integer|min:1|gte:pkg_min_guests',
            'pkg_valid_from' => 'nullable|date',
            'pkg_valid_to' => 'nullable|date|after_or_equal:pkg_valid_from',
        ]);

        try {
            DB::beginTransaction();

            // Initialize image path variable (keep existing by default)
            $imagePath = $roomPackage->pkg_image;

            // Handle image upload to Google Cloud Storage if in production
            if ($request->hasFile('pkg_image') && config('app.env') === 'production') {
                $file = $request->file('pkg_image');
                $gcsPath = 'package_images/' . uniqid() . '_' . $file->getClientOriginalName();
                $stream = fopen($file->getRealPath(), 'r');
                Storage::disk('gcs')->put($gcsPath, $stream);
                fclose($stream);
                $imagePath = $gcsPath;
            }
            elseif ($request->hasFile('pkg_image')) {
                // If not in production, handle local storage (optional)
                $file = $request->file('pkg_image');
                $imagePath = $file->store('package_images', 'public');
            }

            // Sanitize HTML input
            $sanitizedDescription = $htmlSanitizer->sanitize($validated['pkg_description'] ?? '');

            // Update the package
            $roomPackage->update([
                'pkg_name' => $validated['pkg_name'],
                'pkg_sub_title' => $validated['pkg_sub_title'],
                'pkg_description' => $sanitizedDescription,
                'pkg_number_of_nights' => $validated['pkg_number_of_nights'],
                'pkg_checkin_days' => json_encode($validated['pkg_checkin_days']),
                'pkg_status' => $validated['pkg_status'],
                'pkg_image' => $imagePath,
                'pkg_base_price' => $validated['pkg_base_price'],
                'pkg_inclusions' => isset($validated['pkg_inclusions']) ? json_encode(array_filter($validated['pkg_inclusions'])) : null,
                'pkg_exclusions' => isset($validated['pkg_exclusions']) ? json_encode(array_filter($validated['pkg_exclusions'])) : null,
                'pkg_min_guests' => $validated['pkg_min_guests'] ?? null,
                'pkg_max_guests' => $validated['pkg_max_guests'] ?? null,
                'pkg_valid_from' => $validated['pkg_valid_from'] ?? null,
                'pkg_valid_to' => $validated['pkg_valid_to'] ?? null,
            ]);

            // Sync rooms with the package
            if (isset($validated['pkg_rooms'])) {
                // Force delete existing associations and add new ones
                RoomPackage::where('package_id', $roomPackage->id)->forceDelete();
                RoomPackage::flushEventListeners();
                // \Log::info('Flushed RoomPackage event listeners to prevent unwanted side effects during update.');
                
                foreach ($validated['pkg_rooms'] as $roomId) {
                    RoomPackage::create([
                        'room_id' => $roomId,
                        'package_id' => $roomPackage->id,
                    ]);
                }
            }

            $this->logTenantActivity(
                'updated_package',
                'Updated package: ' . $roomPackage->pkg_name . ' (ID: ' . $roomPackage->id . ') for property: ' . $roomPackage->property_id,
                $roomPackage, // Pass the package object as subject
                [
                    'table' => 'packages',
                    'id' => $roomPackage->id,
                    'user_id' => auth()->id(),
                    'changes' => $roomPackage->toArray()
                ]
            );

            DB::commit();

            return redirect()->route('tenant.room-packages.index', ['property_id' => $roomPackage->property_id])
                ->with('success', 'Package updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(Package $roomPackage)
    {
        // Check property access
        if ($roomPackage->property_id && $roomPackage->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized access to package.');
        }

        try {
            $oldStatus = $roomPackage->pkg_status;
            $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';
            
            $roomPackage->update(['pkg_status' => $newStatus]);

            $this->logTenantActivity(
                'updated_package',
                'Updated package: ' . $roomPackage->pkg_name . ' (ID: ' . $roomPackage->id . ') for property: ' . $roomPackage->property_id,
                $roomPackage, // Pass the package object as subject
                [
                    'table' => 'packages',
                    'id' => $roomPackage->id,
                    'user_id' => auth()->id(),
                    'changes' => $roomPackage->toArray()
                ]
            );

            return redirect()->route('tenant.room-packages.index', ['property_id' => $roomPackage->property_id])
                ->with('success', "Package status changed to {$newStatus}.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to toggle package status.');
        }
    }

    /**
     * Clone the specified resource.
     */
    public function clone(Request $request, Package $roomPackage)
    {
        // Check property access
        if ($roomPackage->property_id && $roomPackage->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized access to package.');
        }

        try {
            DB::beginTransaction();

            $clonedPackage = $roomPackage->replicate();
            $clonedPackage->pkg_name = $clonedPackage->pkg_name . ' (Copy)';
            $clonedPackage->pkg_status = 'inactive'; // Set cloned package as inactive by default
            $clonedPackage->save();

            // Clone room associations
            $roomAssociations = RoomPackage::where('package_id', $roomPackage->id)->get();
            foreach ($roomAssociations as $association) {
                RoomPackage::create([
                    'room_id' => $association->room_id,
                    'package_id' => $clonedPackage->id,
                ]);
            }

            $this->logTenantActivity(
                'cloned_package',
                'Cloned package: ' . $clonedPackage->pkg_name . ' (ID: ' . $clonedPackage->id . ') for property: ' . $clonedPackage->property_id,
                $clonedPackage, // Pass the package object as subject
                [
                    'table' => 'packages',
                    'id' => $clonedPackage->id,
                    'user_id' => auth()->id(),
                    'changes' => $clonedPackage->toArray()
                ]
            );

            DB::commit();

            return redirect()->route('tenant.room-packages.edit', $clonedPackage)
                ->with('success', 'Package cloned successfully. You can now edit the copy.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to clone package.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $roomPackage)
    {
        // Check property access
        if ($roomPackage->property_id && $roomPackage->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized access to package.');
        }

        try {
            $propertyId = $roomPackage->property_id;
            $packageName = $roomPackage->pkg_name;
            $packageId = $roomPackage->id;

            // Soft delete the package (this will also handle relationships due to model design)
            $roomPackage->delete();

            $this->logTenantActivity(
                'deleted_package',
                'Deleted package: ' . $packageName . ' (ID: ' . $packageId . ') for property: ' . $propertyId,
                $roomPackage, // Pass the package object as subject
                [
                    'table' => 'packages',
                    'id' => $roomPackage->id,
                    'user_id' => auth()->id(),
                    'changes' => $roomPackage->toArray()
                ]
            );

            return redirect()->route('tenant.room-packages.index', ['property_id' => $propertyId])
                ->with('success', 'Package deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete package.');
        }
    }
}
