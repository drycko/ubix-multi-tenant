<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Room;
use App\Models\Tenant\Guest;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\Package;
use App\Models\Tenant\RoomType;
use Exception;
use PDF;

class BookingController extends Controller
{
    /**
     * Display a listing of the bookings. This is better optimized for large datasets.
     */
    public function index()
    {
        // since have old imported this month bookings with created_at day let us update that to an older date
        // $bookings = Booking::where('created_at', '>=', now()->format('Y-m-d'))->where('source', 'legacy')
        //     ->where('property_id', current_property()->id)
        //     ->get();
        //     // I want to set it directly in phpmy database to now()
        //     // UPDATE bookings SET created_at = now() WHERE id = ?
        // foreach ($bookings as $booking) {
        //     $createdAt = $booking->arrival_date->subDays(rand(3, 10))->format('Y-m-d H:i:s');
        //     $booking->created_at = $createdAt;
        //     $booking->updated_at = now()->format('Y-m-d H:i:s');
        //     $booking->save();
        // }

        $bookings = Booking::with(['room', 'bookingGuests.guest'])
            ->where('property_id', current_property()->id)
            ->latest('arrival_date')
            ->paginate(20);

        $packages = Package::where('property_id', current_property()->id)
            ->get();

        $currency = current_property()->currency;

        return view('tenant.bookings.index', compact('bookings', 'currency', 'packages'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        // make sure we only load available rooms from today onwards (we can use the getAvailableRooms method from Room model if needed)
        $rooms = Room::getAvailableRooms();

        $currency = current_property()->currency;

        $guests = Guest::where('property_id', current_property()->id)
            ->get();

        $packages = Package::where('property_id', current_property()->id)
            ->get();
        $package = null;
        // if we have package_id in the params
        if (request()->has('package_id')) {
            $package = $packages->find(request()->get('package_id'));
        }
        // only allow bookings for future dates, not past dates
        // we can also add a date picker to select the arrival date and filter available rooms based on that
        $arrivalDate = now()->format('Y-m-d');
        $bookingSources = ['website', 'walk_in', 'phone', 'agent', 'legacy', 'inhouse'];

        return view('tenant.bookings.create', compact('rooms', 'guests', 'currency', 'arrivalDate', 'bookingSources', 'packages', 'package'));
    }

    /**
     * Show the form for creating a new booking with a preselected package.
     */
    public function createWithPackage(Package $package)
    {

        // Eager load packages relationship
        $rooms = Room::getAvailableRooms();
        $rooms->load('packages');

        // Only allow rooms that are compatible with the package
        $rooms = $rooms->filter(function ($room) use ($package) {
            return $room->packages && $room->packages->contains($package);
        });

        $currency = current_property()->currency;

        $allowedPackages = Package::where('property_id', current_property()->id)
            ->get();
        $guests = Guest::where('property_id', current_property()->id)
            ->get();
        // only allow bookings for future dates, not past dates
        // we can also add a date picker to select the arrival date and filter available rooms based on that
        $arrivalDate = now()->format('Y-m-d');
        $bookingSources = ['website', 'walk_in', 'phone', 'agent', 'legacy', 'inhouse'];
        // $pkg_checkin_days

        return view('tenant.bookings.package-create', compact('rooms', 'guests', 'currency', 'arrivalDate', 'package', 'bookingSources', 'allowedPackages'));
    }

    /**
     * Download the room information for a specific booking.
     */
    public function downloadRoomInfo(Booking $booking)
    {
        // Authorization check - ensure booking belongs to current property
        if ($booking->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships needed for the PDF
        $property = current_property();
        $currency = $property->currency ?? 'USD';
        $booking->load(['room.type', 'package', 'bookingGuests.guest']);
        $pdf = PDF::loadView('tenant.bookings.room-info-pdf', compact('booking', 'property', 'currency'));

        // Return as download
        return $pdf->download('room-info-booking-' . $booking->bcode . '.pdf');
    }

    /**
     * Send the room information for a specific booking.
     */
    public function sendRoomInfo(Booking $booking)
    {
        // Authorization check - ensure booking belongs to current property
        if ($booking->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships needed for the email
        $booking->load(['room.type', 'guests']);
        $currency = current_property()->currency;

        // Generate PDF from Blade view
        $pdf = PDF::loadView('tenant.bookings.room-info-pdf', compact('booking', 'currency'));

        // Logic to send room information
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        
        try {
            // \DB::beginTransaction();

            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'guest_id' => 'required|exists:guests,id',
                'arrival_date' => 'required|date|after_or_equal:today',
                'departure_date' => 'required|date|after:arrival_date',
                'adults' => 'required|integer|min:1',
                'children' => 'sometimes|integer|min:0',
                'daily_rate' => 'nullable|numeric|min:0',
                'special_requests' => 'sometimes|string|max:500',
                'source' => 'sometimes|in:website,walk_in,phone,agent,legacy,inhouse',
                'is_shared' => 'sometimes|boolean',
                'package_id' => 'sometimes|exists:packages,id',
            ]);

            $package_id = $request->input('package_id', null);

            $isShared = $request->input('is_shared', false);

            // we will check if the room and guest belong to the current property
            $room = Room::where('property_id', current_property()->id)
                ->where('id', $validated['room_id'])
                ->first();

            if (!$room) {
                throw new Exception("Selected room not found.");
            }
            // we assume primary guest exists as we validated above
            $guest = Guest::where('property_id', current_property()->id)
                ->where('id', $validated['guest_id'])
                ->first();

            if (!$guest) {
                throw new Exception("Selected guest not found.");
            }

            // if there is a secondary guest, we validate and fetch them too
            $secondaryGuest = null;
            if ($isShared && $request->has('guest2_id') && !empty($request->input('guest2_id'))) {
                $secondaryGuest = Guest::where('property_id', current_property()->id)
                    ->where('id', $request->input('guest2_id'))
                    ->first();

                if (!$secondaryGuest) {
                    throw new Exception("Selected secondary guest not found.");
                }
            }

            $arrivalDate = date('Y-m-d', strtotime($validated['arrival_date']));
            $departureDate = date('Y-m-d', strtotime($validated['departure_date']));
            $nights = ceil((strtotime($departureDate) - strtotime($arrivalDate)) / (60 * 60 * 24));

            // if daily_rate is not provided, we calculate it based on room type or package (if any)
            if (empty($validated['daily_rate'])) {
                $validated['daily_rate'] = $this->calculatePackageDailyRate($request->input('package_id'), $room, $nights, $isShared);

            }

            $dailyRate = round(floatval($validated['daily_rate']), 2);
            $totalAmount = $nights * $dailyRate;

            // Generate booking code
            $bcode = $this->generateBookingCode($arrivalDate, $room->number);

            $booking_status = 'pending';

            $booking_source = $request->input('source', 'inhouse');

            // Create booking
            $booking = Booking::create([
                'package_id' => $package_id,
                'is_shared' => $isShared,
                'property_id' => current_property()->id,
                'room_id' => $room->id,
                'bcode' => $bcode,
                'arrival_date' => $arrivalDate,
                'departure_date' => $departureDate,
                'nights' => $nights,
                'daily_rate' => $dailyRate,
                'total_amount' => $totalAmount,
                'status' => $booking_status,
                'source' => $booking_source,
                'ip_address' => $request->ip(),
            ]);

            // Attach guest as primary booking guest (like import)
            $booking->bookingGuests()->create([
                'guest_id' => $guest->id,
                'is_primary' => true,
                'is_adult' => true,
                'adults' => $validated['adults'],
                'children' => $validated['children'] ?? 0,
                'special_requests' => $validated['special_requests'] ?? null,
                'property_id' => current_property()->id,
            ]);

            if ($isShared && $secondaryGuest) {
                // Attach secondary guest as non-primary booking guest
                $booking->bookingGuests()->create([
                    'guest_id' => $secondaryGuest->id,
                    'is_primary' => false,
                    'is_adult' => true,
                    'adults' => 1,
                    'children' => 0,
                    'special_requests' => $request->input('g2_special_requests') ?? null,
                    'property_id' => current_property()->id,
                ]);
            }

            $invoice_number = $this->generateUniqueInvoiceNumber('0000001');
            $invoice_status = $booking_status == 'pending' ? 'paid' : 'pending';

            $booking_invoice = $booking->invoices()->create([
                'property_id' => current_property()->id,
                'invoice_number' => $invoice_number,
                'amount' => $totalAmount,
                'status' => $invoice_status,
            ]);
            // if we reach here, we can commit the transaction and redirect to show new booking page
            // \DB::commit();
            return redirect()->route('tenant.bookings.show', $booking->id)->with('success', 'Booking created successfully!');

            // return redirect()->route('tenant.bookings.index')->with('success', 'Booking created successfully!');

        //     // $this->processStoreBooking($request);

        } catch (\Exception $e) {
            // \DB::rollBack();
            \Log::error("Booking failed: " . $e->getMessage());
            // do not redirect, just show error on the same page
            return back()->withErrors(['error' => 'Failed to create booking: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * generate a unique booking code based on arrival date, room number and a random number
     * Format: YYYYMMDDRNXXX where RN is room number (2 digits) and XXX is a random 3 digit number
     * Example: 20231015020123 (for room 2, arrival date 2023-10-15, random 123)
     */
    private function generateBookingCode($arrivalDate, $roomNumber)
    {
        $datePart = date('Ymd', strtotime($arrivalDate));
        $roomPart = str_pad($roomNumber, 2, '0', STR_PAD_LEFT);
        $randomPart = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $newBookingCode = $datePart . $roomPart . $randomPart;
        // we need to ensure the booking code is unique for the property
        // if not, we regenerate
        $existing = Booking::where('property_id', current_property()->id)
            ->where('bcode', $newBookingCode)
            ->first();

        if ($existing) {
            return $this->generateBookingCode($arrivalDate, $roomNumber);
        }

        return $newBookingCode;
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        // Authorization check - ensure booking belongs to current property
        if ($booking->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        // get all guests related to this booking
        $booking->load(['room', 'guests', 'room.type']);
        $currency = current_property()->currency;

        $primaryBkGuest = $booking->bookingGuests()->where('is_primary', true)->first(); // this is from booking_guests table now we want the guest details from guests table
        $primaryGuest = $primaryBkGuest ? $primaryBkGuest->guest : null;
        $primaryGuest->special_requests = $primaryBkGuest->special_requests;

        $secondaryBkGuest = $booking->bookingGuests()->where('is_primary', false)->first();
        $secondaryGuest = $secondaryBkGuest ? $secondaryBkGuest->guest : null;
        $secondaryGuest->special_requests = $secondaryBkGuest ? $secondaryBkGuest->special_requests : null;

        return view('tenant.bookings.show', compact('booking', 'currency', 'primaryGuest', 'secondaryGuest'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(Booking $booking)
    {
        if ($booking->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        $rooms = Room::where('property_id', current_property()->id)
            ->where('is_enabled', true)
            ->with('type')
            ->get();

        $guests = Guest::where('property_id', current_property()->id)
            ->get();

        $primaryBkGuest = $booking->bookingGuests()->where('is_primary', true)->first(); // this is from booking_guests table now we want the guest details from guests table
        $primaryGuest = $primaryBkGuest ? $primaryBkGuest->guest : null;
        $primaryGuest->special_requests = $primaryBkGuest->special_requests;

        $secondaryBkGuest = $booking->bookingGuests()->where('is_primary', false)->first();
        $secondaryGuest = $secondaryBkGuest ? $secondaryBkGuest->guest : null;
        $secondaryGuest->special_requests = $secondaryBkGuest ? $secondaryBkGuest->special_requests : null;

        $bookingStatuses = ['pending', 'booked', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];

        $currency = current_property()->currency;
        $bookingSources = ['website', 'walk_in', 'phone', 'agent', 'legacy', 'inhouse'];

        $arrivalDate = $booking->arrival_date->format('Y-m-d');
        $departureDate = $booking->departure_date->format('Y-m-d');
        $minArrivalDate = now()->format('Y-m-d');
        $selectedRoom = $booking->room_id;
        $bookingCountAdults = $booking->bookingGuests->sum('is_adult');
        // addd these to $booking
        // $booking->arrival_date = $arrivalDate;
        // $booking->departure_date = $departureDate;
        $booking->min_arrival_date = $minArrivalDate;
        $booking->selected_room = $selectedRoom;
        $booking->booking_count_adults = $bookingCountAdults;
        $booking->booking_count_children = 0;
        $booking->primary_guest_id = $primaryGuest ? $primaryGuest->id : 0;
        $booking->is_shared = $secondaryGuest ? true : false;

        $booking->load('guests');

        return view('tenant.bookings.edit', compact('booking', 'rooms', 'guests', 'primaryGuest', 'secondaryGuest', 'currency', 'bookingSources', 'bookingStatuses'));
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        try {
            if ($booking->property_id !== current_property()->id) {
                abort(403, 'Unauthorized action.');
            }

            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'guest_id' => 'required|exists:guests,id',
                'arrival_date' => 'required|date|after_or_equal:today',
                'departure_date' => 'required|date|after:arrival_date',
                'adults' => 'required|integer|min:1',
                'children' => 'sometimes|integer|min:0',
                'daily_rate' => 'nullable|numeric|min:0',
                'special_requests' => 'sometimes|string|max:500',
                'status' => 'required|in:pending,booked,confirmed,checked_in,checked_out,cancelled',
                'source' => 'sometimes|in:website,walk_in,phone,agent,legacy,inhouse',
                'is_shared' => 'sometimes|boolean',
                'package_id' => 'sometimes|exists:packages,id',
            ]);

            $isShared = $request->input('is_shared', false);

            // Validate room and guests belong to property
            $room = Room::where('property_id', current_property()->id)
                ->where('id', $validated['room_id'])
                ->first();
            if (!$room) {
                throw new Exception("Selected room not found.");
            }
            $guest = Guest::where('property_id', current_property()->id)
                ->where('id', $validated['guest_id'])
                ->first();
            if (!$guest) {
                throw new Exception("Selected guest not found.");
            }
            $secondaryGuest = null;
            if ($isShared && $request->has('guest2_id') && !empty($request->input('guest2_id'))) {
                $secondaryGuest = Guest::where('property_id', current_property()->id)
                    ->where('id', $request->input('guest2_id'))
                    ->first();
                if (!$secondaryGuest) {
                    throw new Exception("Selected secondary guest not found.");
                }
            }

            $arrivalDate = date('Y-m-d', strtotime($validated['arrival_date']));
            $departureDate = date('Y-m-d', strtotime($validated['departure_date']));
            $nights = ceil((strtotime($departureDate) - strtotime($arrivalDate)) / (60 * 60 * 24));

            // if daily_rate is not provided, we calculate it based on room type or package (if any)
            if (empty($validated['daily_rate'])) {
                $validated['daily_rate'] = $this->calculatePackageDailyRate($request->input('package_id'), $room, $nights, $isShared);
            }

            $dailyRate = round(floatval($validated['daily_rate']), 2);
            $totalAmount = $nights * $dailyRate;

            // we might need a new booking code if arrival date or room changed
            if ($booking->arrival_date != $arrivalDate || $booking->room_id != $room->id) {
                $booking->bcode = $this->generateBookingCode($arrivalDate, $room->number);
            }

            // Update booking
            $booking->update([
                'room_id' => $room->id,
                'arrival_date' => $arrivalDate,
                'departure_date' => $departureDate,
                'nights' => $nights,
                'daily_rate' => $dailyRate,
                'total_amount' => $totalAmount,
                'status' => $validated['status'],
                'source' => $request->input('source', $booking->source),
                'package_id' => $request->input('package_id', $booking->package_id),
            ]);

            // Update booking guests
            // Remove all existing bookingGuests and re-add (simple approach)
            $booking->bookingGuests()->delete();

            // Add primary guest
            $booking->bookingGuests()->create([
                'guest_id' => $guest->id,
                'is_primary' => true,
                'is_adult' => true,
                'adults' => $validated['adults'],
                'children' => $validated['children'] ?? 0,
                'special_requests' => $validated['special_requests'] ?? null,
                'property_id' => current_property()->id,
            ]);

            // Add secondary guest if shared
            if ($isShared && $secondaryGuest) {
                $booking->bookingGuests()->create([
                    'guest_id' => $secondaryGuest->id,
                    'is_primary' => false,
                    'is_adult' => true,
                    'adults' => 1,
                    'children' => 0,
                    'special_requests' => $validated['g2_special_requests'] ?? null,
                    'property_id' => current_property()->id,
                ]);
            }

            // Optionally update invoice amount/status if needed
            $invoice = $booking->invoices()->latest()->first();
            if ($invoice) {
                $invoice->update([
                    'amount' => $totalAmount,
                    'status' => $validated['status'] == 'pending' ? 'paid' : 'pending',
                ]);
            }

            return redirect()->route('tenant.bookings.show', $booking->id)
                ->with('success', 'Booking updated successfully!');
        } catch (\Exception $e) {
            \Log::error("Booking update failed: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update booking: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking)
    {
        if ($booking->property_id !== current_property()->id) {
            abort(403, 'Unauthorized action.');
        }

        $booking->delete();

        return redirect()->route('tenant.bookings.index')
            ->with('success', 'Booking deleted successfully!');
    }

    /**
     * Show the form for importing bookings.
     */
    public function importBookings()
    {
        return view('tenant.bookings.import');
    }

    /**
     * Import bookings from an external CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');
        
        // Skip BOM if present (from your export function)
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file); // Not a BOM file, rewind to beginning
        }

        $header = fgetcsv($file);
        $imported = 0;
        $skipped = 0;
        // all imports will have created_at backdated to allow property enough credit to cover new bookings
        $createdAt = date('Y-m-d H:i:s', strtotime('-30 days'));

        while (($row = fgetcsv($file)) !== false) {
            try {
                $data = array_combine($header, $row);
                
                // Remove tab characters from numeric fields (from your export)
                $data = array_map(function($value) {
                    return str_replace("\t", '', $value);
                }, $data);

                // Basic validation for required fields from your CSV export
                $requiredFields = ['BCODE', 'GSTNAME', 'ARDATE', 'DPDATE', 'ROOMNO'];
                foreach ($requiredFields as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                // Parse meta data
                $metaData = json_decode($data['META_DATA'] ?? '{}', true) ?? [];

                // we prefixed BCODE with "\t" to preserve leading zeros in Excel, so we remove it here
                $data['BCODE'] = ltrim($data['BCODE'], "\t");

                // BCODE is booking code, must be unique per property
                if (empty($data['BCODE'])) {
                    throw new Exception("Booking code (BCODE) is required.");
                }

                // Check if booking with same BCODE already exists for this property
                $existingBooking = Booking::where('property_id', current_property()->id)
                    ->where('bcode', $data['BCODE'])
                    ->first();
                // we will not store duplicate bookings, but we will attach the guest to the existing booking if it's a secondary guest (BCODE.2, BCODE.3, etc.)
                if ($existingBooking && strpos($data['BCODE'], '.') === false) {
                    throw new Exception("Booking with code {$data['BCODE']} already exists.");
                }

                // Find or create guest based on GSTNAME, GSTTELNO, GSTEMAIL
                // some of these fields might be empty, so we use a combination of name and email or phone
                // some guests are using the same email (this is the cause of intergrity constraint violation)
                // Custom guest matching logic
                $firstName = $this->extractFirstName($data['GSTNAME']);
                $lastName = $this->extractLastName($data['GSTNAME']);
                $email = $data['GSTEMAIL'] ?? null;
                $phone = $data['GSTTELNO'] ?? null;

                // Try to find guest by email first (if available)
                if (!empty($email)) {
                    $guest = Guest::where('property_id', current_property()->id)
                                ->where('email', $email)
                                ->first();
                }

                // If not found by email, try by phone (if available)
                if (!isset($guest) && !empty($phone)) {
                    $guest = Guest::where('property_id', current_property()->id)
                                ->where('phone', $phone)
                                ->first();
                }

                // If still not found, try by name
                if (!isset($guest)) {
                    $guest = Guest::where('property_id', current_property()->id)
                                ->where('first_name', $firstName)
                                ->where('last_name', $lastName)
                                ->first();
                }

                // If guest doesn't exist, create them
                if (!isset($guest)) {
                    $guest = Guest::create([
                        'property_id' => current_property()->id,
                        'title' => $data['GSTTITLE'] ?? 'Mr/Ms',
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => $phone,
                        'nationality' => $data['NATIONALITY'] ?? '',
                        'country_name' => $data['NATIONALITY'] ?? '',
                        'id_number' => $data['GSTIDNO'] ?? '',
                        'emergency_contact' => $metaData['EMERGENCY_CONTACT'] ?? '',
                        'emergency_contact_phone' => $metaData['EMERGENCY_CONTACT_PHONE'] ?? '',
                        'physical_address' => $metaData['PHYSICAL_ADDRESS'] ?? '',
                        'medical_notes' => $data['MEDICAL'] ?? '',
                        'dietary_preferences' => $metaData['DIETARY_PREFERENCES'] ?? '',
                        'gown_size' => $data['GOWN'] ?? '',
                        'car_registration' => $metaData['CAR_REGISTRATION'] ?? '',
                        'is_active' => true,
                        'created_at' => $createdAt,
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }

                // Find room by room number
                $room = Room::where('property_id', current_property()->id)
                            ->where('number', $data['ROOMNO'])
                            ->first();

                if (!$room) {
                    throw new Exception("Room not found: " . $data['ROOMNO']);
                }

                // Calculate nights and total amount
                $arrivalDate = date('Y-m-d', strtotime($data['ARDATE'])); // convert to Y-m-d if needed
                $departureDate = date('Y-m-d', strtotime($data['DPDATE']));

                $nights = ceil((strtotime($departureDate) - strtotime($arrivalDate)) / (60 * 60 * 24));

                // Get daily rate (convert to rounded float) from package or use default
                // round(): Argument #1 ($num) must be of type int|float, string given
                $dailyRate = round(floatval($data['DAILYTARIFF'] ?? 0), 2);

                $total_amount = $nights * $dailyRate;

                // the csv returns (Y,N)
                $isShared = $data['ISSHARES'] === 'Y';
                // csv TIMEARRIVE needs to be cleaned/formatted if needed - currency it's just a string sometime like "17h00, 17-18h00 or 5pm"
                // format to HH:MM:SS - if there is a '-' or 'to' we take the last part
                $arrivalTime = clean_ctime($data['TIMEARRIVE'] ?? '');


                // we will need to only create a booking once, so if the BCODE with .2 exists, we skip it and fetch the original to store the guest booking
                // check if bcode has a dot in it
                if (strpos($data['BCODE'], '.') !== false) {
                    // get the part before the dot
                    $originalBcode = explode('.', $data['BCODE'])[0];
                    // find the original booking
                    $existingBooking = Booking::where('property_id', current_property()->id)
                        ->where('bcode', $originalBcode)
                        ->first();
                    if ($existingBooking) {
                        // Attach guest to existing booking
                        // For the existing booking (secondary guest):
                        $existingBooking->bookingGuests()->create([
                            'guest_id' => $guest->id,
                            'is_primary' => false,
                            'is_adult' => true,
                            'age' => $metaData['AGE'] ?? null,
                            'is_sharing' => $isShared,
                            'special_requests' => $metaData['SPREQUEST'] ?? '',
                            'arrival_time' => $arrivalTime,
                            'property_id' => current_property()->id, // Make sure this is included!
                            'created_at' => $createdAt,
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                        $imported++;
                        continue; // Skip creating a new booking
                    }
                    // If original booking not found, proceed to create new booking with original BCODE
                    $data['BCODE'] = $originalBcode;
                }

                $booking_status = $this->mapBookingStatus($data['STATUS'] ?? 'pending');

                // Create or update booking
                $booking = Booking::updateOrCreate(
                    [
                        'property_id' => current_property()->id,
                        'bcode' => $data['BCODE'],
                    ],
                    [
                        'room_id' => $room->id,
                        'package_id' => $data['PACKAGE'] ?? null,
                        'is_shared' => $isShared,
                        'arrival_date' => $arrivalDate,
                        'departure_date' => $departureDate,
                        'nights' => $nights,
                        'daily_rate' => $dailyRate,
                        'total_amount' => $total_amount,
                        'deposit_amount' => $total_amount,
                        'status' => $booking_status,
                        'source' => 'legacy',
                        'invoice_number' => $data['INVOICE_NO'] ?? null,
                        'legacy_group_id' => $data['GSTGROUP'] ?? null,
                        // 'special_requests' => $data['WEB_DESCRIPTION'] ?? '',
                        'meta_data' => $metaData,
                        'created_at' => $createdAt,
                        'updated_at' => now()->format('Y-m-d H:i:s'), // keep updated_at current for updates
                    ]
                );

                // And for the main booking (primary guest):
                $booking->bookingGuests()->create([
                    'guest_id' => $guest->id,
                    'is_primary' => true,
                    'is_adult' => true,
                    'age' => $metaData['AGE'] ?? null,
                    'is_sharing' => $isShared,
                    'special_requests' => $metaData['SPREQUEST'] ?? '',
                    'arrival_time' => $arrivalTime,
                    'property_id' => current_property()->id, // Make sure this is included!
                    'created_at' => $createdAt,
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);

                // add an invoice if the $total_amount > 0 and invoice number is present
                if ($total_amount > 0 && !empty($data['INVOICE_NO'])) {
                    $invoice_status = 'pending';
                    // if booking status is cancelled, set invoice to cancelled... if the status is booked or confirmed, set to paid
                    if ($booking_status === 'cancelled') {
                        $invoice_status = 'cancelled';
                    } elseif (in_array($booking_status, ['booked', 'confirmed'])) {
                        $invoice_status = 'paid';
                    }

                    // check if invoice already exists
                    $existingInvoice = $booking->invoices()
                        ->where('invoice_number', $data['INVOICE_NO'])
                        ->first();
                    
                    if (!$existingInvoice) {
                        // if invoice doesn't exist we can check if the invoice number exists in other bookings for the same property
                        $existingInvoiceInOtherBooking = Booking::where('property_id', current_property()->id)
                            ->whereHas('invoices', function($query) use ($data) {
                                $query->where('invoice_number', $data['INVOICE_NO']);
                            })
                            ->first();
                        if ($existingInvoiceInOtherBooking) {
                            // if it exists, we generate a new unique invoice number with a BookingInvoiceController method
                            $data['INVOICE_NO'] = $this->generateUniqueInvoiceNumber($data['INVOICE_NO']);
                        }
                        $booking->invoices()->create([
                            'property_id' => current_property()->id,
                            'invoice_number' => $data['INVOICE_NO'],
                            'amount' => $total_amount,
                            'status' => $invoice_status,
                            'created_at' => $createdAt,
                            'updated_at' => now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
                $createdAt = date('Y-m-d H:i:s', strtotime($createdAt . ' +1 minute')); // increment created_at for next record to avoid identical timestamps
                $imported++;

            } catch (\Exception $e) {
                $skipped++;
                \Log::error("Booking import skipped: " . $e->getMessage());
                continue;
            }
        }

        fclose($file);

        return redirect()->route('tenant.bookings.index')
            ->with('success', "Bookings imported successfully! $imported imported, $skipped skipped.");
    }

    private function generateUniqueInvoiceNumber($invoiceNumber = null)
    {
        if ($invoiceNumber) {
            $existingInvoice = BookingInvoice::where('invoice_number', $invoiceNumber)
                ->where('property_id', current_property()->id)
                ->first();
            if ($existingInvoice) {
                // Increment and try again
                $invoiceNumber = increment_unique_number($invoiceNumber);
                return $this->generateUniqueInvoiceNumber($invoiceNumber);
            }
            // Unique, return it
            return $invoiceNumber;
        } else {
            // Generate new invoice number
            $lastInvoice = BookingInvoice::where('property_id', current_property()->id)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($lastInvoice) {
                $invoiceNumber = increment_unique_number($lastInvoice->invoice_number);
            } else {
                $invoiceNumber = '0000001';
            }
            return $invoiceNumber;
        }
    }


    // Helper methods to add to your controller
    private function extractFirstName($fullName)
    {
        // some names might have parts like "Kelly-Anne Smith", if there is space we take the first part only
        $parts = explode(' ', $fullName);
        return $parts[0] ?? '';
    }

    private function extractLastName($fullName)
    {
        $parts = explode(' ', $fullName);
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    private function calculatePackageDailyRate($packageId, $room, $nights, $isShared = false)
    {
        if ($packageId && $package = Package::find($packageId)) {
            $basePrice = $package->pkg_base_price;
            if ($basePrice > 0 && $package->pkg_number_of_nights > 0 && $nights >= $package->pkg_number_of_nights) {
                return $basePrice / $package->pkg_number_of_nights;
            }

        }

        return $this->calculateDailyRate($room, $isShared); // Default rate
    }

    private function calculateDailyRate($room, $isShared = false)
    {
        // room does not have standard_rate, we get it from room type rates
        $standard_rate = $room->type->rates->where('is_shared', $isShared)->first() ?? 0; // Default rate
        return $standard_rate;
    }

    private function mapBookingStatus($status)
    {
        $statusMap = [
            'Booked' => 'booked',
            'Confirmed' => 'confirmed',
            'Club Member' => 'confirmed',
            'Cancelled' => 'cancelled',
            'Pending' => 'pending'
        ];
        
        return $statusMap[$status] ?? 'pending';
    }
}