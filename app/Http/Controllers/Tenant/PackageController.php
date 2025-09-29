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

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // test GCS connection
        // $this->testGCSConnection();
        // $this->testDirectGoogleSDK();
        // $this->testUniformGCS();

        // Fetch all packages for the current property
        $packages = Package::where('property_id', property_id())->paginate(15);
        $currency = property_currency();
        return view('tenant.room-packages.index', compact('packages', 'currency'));
    }

    /**
     * Show the form for importing resources.
     */
    public function importPackage()
    {
        return view('tenant.room-packages.import');
    }

    
    // run this once
    public function makeExistingFilesPublic()
    {
        $storageClient = new StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);
        
        $bucket = $storageClient->bucket(config('filesystems.disks.gcs.bucket'));
        
        // Get all objects in package_images directory
        $objects = $bucket->objects(['prefix' => 'package_images/']);
        
        foreach ($objects as $object) {
            try {
                // Make object public
                $object->update([], ['predefinedAcl' => 'publicRead']);
                echo "Made public: " . $object->name() . "\n";
            } catch (\Exception $e) {
                echo "Failed: " . $object->name() . " - " . $e->getMessage() . "\n";
            }
        }
        
        echo "All existing package_images files are now public!\n";
    }

    public function reuploadFilesAsPublic()
    {
        $storageClient = new StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);
        
        $bucket = $storageClient->bucket(config('filesystems.disks.gcs.bucket'));
        
        // Get all objects in package_images directory
        $objects = $bucket->objects(['prefix' => 'package_images/']);
        
        foreach ($objects as $object) {
            try {
                // Download the file
                $content = $object->downloadAsString();
                
                // Re-upload with public access
                $bucket->upload($content, [
                    'name' => $object->name(),
                    'predefinedAcl' => 'publicRead'
                ]);
                
                echo "Re-uploaded as public: " . $object->name() . "\n";
                
            } catch (\Exception $e) {
                echo "Failed: " . $object->name() . " - " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Test GCS with UniformGCSAdapter, this works so we will use this adapter for GCS
    */
    public function testUniformGCS()
    {
        
        // disable testing
        // return;
        try {
            // Bulk Update Existing Files
            $this->reuploadFilesAsPublic();

            echo "✓ Testing with corrected UniformGCSAdapter\n";
            
            $path = 'package_images/test-corrected-'.time().'.txt';
            
            // Test file creation
            $putResult = Storage::disk('gcs')->put($path, 'Hello Corrected GCS!');
            echo "✓ Put result: " . ($putResult ? 'Success' : 'Failed') . "\n";
            
            // Test file existence
            $exists = Storage::disk('gcs')->exists($path);
            echo "✓ File exists: " . ($exists ? 'Yes' : 'No') . "\n";
            
            if ($exists) {
                // Test reading content
                $content = Storage::disk('gcs')->get($path);
                echo "✓ File content: " . $content . "\n";
            }
            
        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "✗ Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    /**
     * Handle the import of packages from a CSV file.
     */
    public function import(Request $request)
    {
        
        try {
            
            // $request->validate([
            //     'csv_file' => 'required|mimes:csv,txt',
            // ]);
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            // Handle the CSV import logic here
            // Example CSV content:
            // "pkg_id","pkg_name","pkg_sub_title","pkg_description","pkg_number_of_nights","pkg_checkin_days","pkg_status","pkg_enterby","deleted","pkg_image"
            // "1","2 Night Quick Escape","A Two Night Tonic & Taste of Brookdale.","2","1","active","admin","0","image1.jpg"
            // "2","3 Night Family Getaway","A Three Night Family Adventure.","3","2","active","admin","0","image2.jpg"
            // Parse the CSV and create Package records accordingly
            $file = $request->file('csv_file');
            $data = array_map('str_getcsv', file($file->getRealPath()));

            foreach ($data as $row) {
                // Skip the header row
                if ($row[0] === 'pkg_id') {
                    continue;
                }
                // change the pkg_status to active/inactive based on the CSV value, when the CSV value is 'available' set it to 'active', if 'unavailable' set it to 'inactive'
                if ($row[6] === 'available') {
                    $row[6] = 'active';
                } elseif ($row[6] === 'unavailable') {
                    $row[6] = 'inactive';
                }
                // Undefined Undefined array key 6 

                // Create the package
                Package::create([
                    'property_id' => property_id(),
                    'pkg_id' => $row[0],
                    'pkg_name' => $row[1],
                    'pkg_sub_title' => $row[2],
                    'pkg_description' => $row[3],
                    'pkg_number_of_nights' => $row[4],
                    'pkg_checkin_days' => $row[5],
                    'pkg_status' => $row[6],
                    'pkg_enterby' => auth()->user()->id,
                    'pkg_image' => $row[9] ?? null,
                ]);
            }

            return redirect()->route('tenant.admin.room-packages.index')->with('success', 'Packages imported successfully.');
        } catch (\Exception $th) {
            return redirect()->back()->with('error', 'Failed to import packages: ' . $th->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rooms = Room::where('property_id', property_id())->get();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $currency = property_currency();
        $maxNumOfNights = 30; // Define a maximum number of nights
        // Show the form for creating a new package
        return view('tenant.room-packages.create', compact('rooms', 'daysOfWeek', 'maxNumOfNights', 'currency'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, HtmlSanitizerService $htmlSanitizer)
    {
        // Validate the request
        $validated = $request->validate([
            'pkg_name' => 'required|string|max:255',
            'pkg_sub_title' => 'required|string|max:255', // Changed from nullable to required to match your form
            'pkg_description' => 'nullable|string',
            'pkg_number_of_nights' => 'required|integer|min:1',
            'pkg_checkin_days' => 'required|array', // Changed from nullable to required to match your form
            'pkg_checkin_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'pkg_rooms' => 'required|array', // Changed from nullable to required to match your form
            'pkg_rooms.*' => 'integer|exists:rooms,id',
            'pkg_status' => 'required|in:active,inactive',
            'pkg_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pkg_base_price' => 'required|numeric|min:0',
            'pkg_inclusions' => 'nullable|array',
            'pkg_exclusions' => 'nullable|array',
            'pkg_min_guests' => 'nullable|integer|min:1',
            'pkg_max_guests' => 'nullable|integer|min:1',
            'pkg_valid_from' => 'nullable|date',
            'pkg_valid_to' => 'nullable|date|after_or_equal:pkg_valid_from',
        ]);


        // Initialize image path variable
        $imagePath = null;

        // Handle image upload to Google Cloud Storage using the gcs disk with UniformGCSAdapter
        if ($request->hasFile('pkg_image')) {
            $file = $request->file('pkg_image');
            $gcsPath = 'package_images/' . uniqid() . '_' . $file->getClientOriginalName();
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('gcs')->put($gcsPath, $stream);
            fclose($stream);
            $imagePath = $gcsPath; // Save the GCS path in the database
        }

        // SANITIZE THE HTML INPUT BEFORE STORING
        $sanitizedDescription = $htmlSanitizer->sanitize($validated['pkg_description'] ?? '');

        // Create the package
        $package = Package::create([
            'property_id' => property_id(),
            'pkg_name' => $validated['pkg_name'],
            'pkg_sub_title' => $validated['pkg_sub_title'],
            'pkg_description' => $sanitizedDescription,
            'pkg_number_of_nights' => $validated['pkg_number_of_nights'],
            'pkg_checkin_days' => json_encode($validated['pkg_checkin_days']),
            'pkg_status' => $validated['pkg_status'],
            'pkg_enterby' => auth()->user()->id,
            'pkg_image' => $imagePath, // Use the $imagePath variable here
            'pkg_base_price' => $validated['pkg_base_price'],
            'pkg_inclusions' => isset($validated['pkg_inclusions']) ? json_encode($validated['pkg_inclusions']) : null,
            'pkg_exclusions' => isset($validated['pkg_exclusions']) ? json_encode($validated['pkg_exclusions']) : null,
            'pkg_min_guests' => $validated['pkg_min_guests'] ?? null,
            'pkg_max_guests' => $validated['pkg_max_guests'] ?? null,
            'pkg_valid_from' => $validated['pkg_valid_from'] ?? null,
            'pkg_valid_to' => $validated['pkg_valid_to'] ?? null,
        ]);

        // Associate rooms with the package using sync to avoid duplicates
        if (isset($validated['pkg_rooms'])) {
            // $package->rooms()->sync($validated['pkg_rooms']);
            foreach ($validated['pkg_rooms'] as $roomId) {
                RoomPackage::firstOrCreate([
                    'room_id' => $roomId,
                    'package_id' => $package->id,
                ]);
            }
        }

        return redirect()->route('tenant.admin.room-packages.index')->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $roomPackage)
    {
        if (!$roomPackage) {
            abort(404, 'Package not found.');
        }

        // debug in the LOG package array
        // \Log::debug('Package array:', $roomPackage->toArray()); // I am getting empty array here

        if (property_id() === null) {
            abort(403, 'Unauthorized action. No property context available.');
        }


        // Allow access to packages from current property OR packages with no property
        if ($roomPackage->property_id && $roomPackage->property_id !== property_id()) {
            abort(403, 'Unauthorized action. This package belongs to a different property.');
        }

        // Show the form for editing a package
        $rooms = Room::where('property_id', property_id())->get(); // FIX: Use property_id() here too
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $currency = property_currency();
        $maxNumOfNights = 30;

        $pkg_valid_to = null;
        $pkg_valid_from = null;

        // format date to Y-m-d for the date input fields
        if ($roomPackage->pkg_valid_from) {
            $pkg_valid_from = date('Y-m-d', strtotime($roomPackage->pkg_valid_from));
        }
        if ($roomPackage->pkg_valid_to) {
            $pkg_valid_to = date('Y-m-d', strtotime($roomPackage->pkg_valid_to));
        }

        // get full GCS image URL using uniform adapter because gcs is throwing error: This driver does not support retrieving URLs.
        $gcsImageUrl = null;
        if ($roomPackage->pkg_image) {
            // Get GCS config
            $gcsConfig = config('filesystems.disks.gcs');
            $bucket = $gcsConfig['bucket'] ?? null;
            $projectId = $gcsConfig['project_id'] ?? null;
            $path = ltrim($roomPackage->pkg_image, '/');
            // Default public base URL for GCS
            if ($bucket) {
                $gcsImageUrl = "https://storage.googleapis.com/{$bucket}/{$path}";
            }
        }

        // Get the selected rooms from the pivot table
        $selectedRooms = RoomPackage::where('package_id', $roomPackage->id)->pluck('room_id')->toArray();

        // check-in days as arrays
        $selectedCheckinDays = json_decode($roomPackage->pkg_checkin_days, true) ?? [];

    return view('tenant.room-packages.edit', compact('roomPackage', 'rooms', 'daysOfWeek', 'selectedRooms', 'selectedCheckinDays', 'maxNumOfNights', 'currency', 'pkg_valid_from', 'pkg_valid_to', 'gcsImageUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $roomPackage)
    {
        // Validate the request
        $validated = $request->validate([
            'pkg_name' => 'required|string|max:255',
            'pkg_sub_title' => 'required|string|max:255', // Changed from nullable to required to match your form
            'pkg_description' => 'nullable|string',
            'pkg_number_of_nights' => 'required|integer|min:1',
            'pkg_checkin_days' => 'required|array', // Changed from nullable to required to match your form
            'pkg_checkin_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'pkg_rooms' => 'required|array', // Changed from nullable to required to match your form
            'pkg_rooms.*' => 'integer|exists:rooms,id',
            'pkg_status' => 'required|in:active,inactive',
            'pkg_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'pkg_base_price' => 'required|numeric|min:0',
            'pkg_inclusions' => 'nullable|array',
            'pkg_exclusions' => 'nullable|array',
            'pkg_min_guests' => 'nullable|integer|min:1',
            'pkg_max_guests' => 'nullable|integer|min:1',
            'pkg_valid_from' => 'nullable|date',
            'pkg_valid_to' => 'nullable|date|after_or_equal:pkg_valid_from',
        ]);

        // Initialize image path variable
        $imagePath = $roomPackage->pkg_image; // Keep existing image path by default

        // Handle image upload to Google Cloud Storage using the gcs disk with UniformGCSAdapter
        if ($request->hasFile('pkg_image')) {
            $file = $request->file('pkg_image');
            $gcsPath = 'package_images/' . uniqid() . '_' . $file->getClientOriginalName();
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('gcs')->put($gcsPath, $stream);
            fclose($stream);
            $imagePath = $gcsPath; // Save the GCS path in the database
        }

        // Update the package
        $roomPackage->update([
            'pkg_name' => $validated['pkg_name'],
            'pkg_sub_title' => $validated['pkg_sub_title'],
            'pkg_description' => $validated['pkg_description'],
            'pkg_number_of_nights' => $validated['pkg_number_of_nights'],
            'pkg_checkin_days' => json_encode($validated['pkg_checkin_days']),
            'pkg_status' => $validated['pkg_status'],
            'pkg_image' => $imagePath, // Use the $imagePath variable here
            'pkg_base_price' => $validated['pkg_base_price'],
            'pkg_inclusions' => isset($validated['pkg_inclusions']) ? json_encode($validated['pkg_inclusions']) : null,
            'pkg_exclusions' => isset($validated['pkg_exclusions']) ? json_encode($validated['pkg_exclusions']) : null,
            'pkg_min_guests' => $validated['pkg_min_guests'] ?? null,
            'pkg_max_guests' => $validated['pkg_max_guests'] ?? null,
            'pkg_valid_from' => $validated['pkg_valid_from'] ?? null,
            'pkg_valid_to' => $validated['pkg_valid_to'] ?? null,
        ]);

        // Associate rooms with the package using sync to avoid duplicates
        if (isset($validated['pkg_rooms'])) {
            // if we have older package rooms, we ignore them and just add new ones if they don't exist
            foreach ($validated['pkg_rooms'] as $roomId) {

                RoomPackage::firstOrCreate([
                    'room_id' => $roomId,
                    'package_id' => $roomPackage->id,
                ]);
            }
        }

        return redirect()->route('tenant.admin.room-packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Clone the specified resource.
     */
    public function clone(Request $request, Package $roomPackage)
    {
        $clonedPackage = $roomPackage->replicate();
        $clonedPackage->pkg_name = $clonedPackage->pkg_name . ' (Copy)';
        $clonedPackage->save();

        return redirect()->route('tenant.admin.room-packages.index')->with('success', 'Package cloned successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $roomPackage)
    {
        // Soft delete the package
        $roomPackage->delete();
        return redirect()->route('tenant.admin.room-packages.index')->with('success', 'Package deleted successfully.');
    }
}
