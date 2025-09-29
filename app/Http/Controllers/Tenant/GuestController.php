<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Helpers\Helpers;
use App\Models\Tenant\Guest;
use App\Models\Tenant\Property;
use App\Models\Tenant\GuestClub;
use App\Models\Tenant\GuestClubMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $propertyId = current_property()->id;
    
    $guests = Guest::where('guests.property_id', $propertyId)
        ->selectRaw('guests.*, 
            (SELECT COUNT(*) FROM booking_guests 
             JOIN bookings ON booking_guests.booking_id = bookings.id 
             WHERE booking_guests.guest_id = guests.id 
             AND bookings.property_id = ?) as bookings_count', 
            [$propertyId])
        ->paginate(15);

    // Get guest last booking date and room number use selectRaw with a subquery
    $lastBooking = null;

    foreach ($guests as $guest) {
        $lastBooking = DB::table('booking_guests')
            ->join('bookings', 'booking_guests.booking_id', '=', 'bookings.id')
            ->where('booking_guests.guest_id', $guest->id)
            ->where('bookings.property_id', $propertyId)
            ->orderBy('booking_guests.arrival_time', 'desc')
            ->selectRaw('booking_guests.arrival_time, bookings.room_id')
            ->first();

        if ($lastBooking) {
            $guest->last_booking_date = $lastBooking->arrival_time;
            $guest->last_booking_room = $lastBooking->room_id ?? 'N/A';
        } else {
            $guest->last_booking_date = null;
            $guest->last_booking_room = null;
        }
    }
    
    if ($lastBooking) {
        $guest->last_booking_date = $lastBooking->arrival_time;
        $guest->last_booking_room = $lastBooking->room_id ?? 'N/A';
    }

    foreach ($guests as $guest) {
        $guest->physical_address = truncate($guest->physical_address, 20);
    }

    return view('tenant.guests.index', compact('guests'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // I will need an array of countries for the for the form
        // I have created a getCountries() function inside config/countries.php file that reads from the json file and returns an array
        $countries = getCountries();
        $guestClubs = GuestClub::where('property_id', current_property()->id)->get();
        return view('tenant.guests.create', compact('countries', 'guestClubs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {        
        try {
            DB::beginTransaction();
            // Validate the request
            $validated = $request->validate([
                'title' => 'nullable|string|max:10',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'id_number' => 'nullable|string|max:100',
                'nationality' => 'nullable|string|max:100',
                'country_name' => 'nullable|string|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:100',
                'emergency_contact' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:100',
                'physical_address' => 'nullable|string|max:255',
                'residential_address' => 'nullable|string|max:255',
                'medical_notes' => 'nullable|string|max:255',
                'dietary_preferences' => 'nullable|string|max:255',
                'gown_size' => 'nullable|string|max:10',
                'car_registration' => 'nullable|string|max:100',
                'is_active' => 'nullable|boolean',
                'guest_club_id' => 'nullable|array',
                'guest_club_id.*' => 'exists:guest_clubs,id'
            ]);

            // Create the guest
            $guest = Guest::create(array_merge($validated, [
                'property_id' => current_property()->id,
                'is_active' => true,
                'legacy_meta' => []
            ]));

            // insert into guest_club_member table if guest_club_ids are provided
            if ($request->has('guest_club_id')) {
                foreach ($request->input('guest_club_id') as $clubId) {
                    GuestClubMember::create([
                        'guest_club_id' => $clubId,
                        'guest_id' => $guest->id,
                    ]);
                }
            }

            return redirect()->route('tenant.admin.guests.index')->with('success', 'Guest created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tenant.admin.guests.index')->with('error', 'Failed to create guest.');
        } finally {
            DB::commit();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Guest $guest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guest $guest)
    {
        $propertyId = current_property()->id;
        // I will need an array of countries for the for the form
        // I have created a getCountries() function inside config/countries.php file that reads from the json file and returns an array
        $countries = getCountries();

        $guestClubs = GuestClub::where('property_id', $propertyId)->get();

        // Get the last 5 bookings for this guest
        $guestBookings = DB::table('booking_guests')
            ->join('bookings', 'booking_guests.booking_id', '=', 'bookings.id')
            ->where('booking_guests.guest_id', $guest->id)
            ->where('bookings.property_id', $propertyId)
            ->orderBy('booking_guests.arrival_time', 'desc')
            ->limit(5)
            ->selectRaw('bookings.*')
            ->get();

        // Get the guest club ids for this guest
        $guestClubsForGuest = GuestClubMember::where('guest_id', $guest->id)->pluck('guest_club_id')->toArray();
        // $guest->guest_club_id = $guestClubsForGuest;

        return view('tenant.guests.edit', compact('guest', 'countries', 'guestBookings', 'guestClubs' , 'guestClubsForGuest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guest $guest)
    {
        try {
            DB::beginTransaction();
            // validate the request
            $validated = $request->validate([
                'title' => 'nullable|string|max:10',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'id_number' => 'nullable|string|max:100',
                'nationality' => 'nullable|string|max:100',
                'country_name' => 'nullable|string|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:100',
                'emergency_contact' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:100',
                'physical_address' => 'nullable|string|max:255',
                'residential_address' => 'nullable|string|max:255',
                'medical_notes' => 'nullable|string|max:255',
                'dietary_preferences' => 'nullable|string|max:255',
                'gown_size' => 'nullable|string|max:10',
                'car_registration' => 'nullable|string|max:100',
                'is_active' => 'nullable|boolean',
                'guest_club_id' => 'nullable|array',
                'guest_club_id.*' => 'exists:guest_clubs,id'
            ]);

            // Update the guest
            $guest->update($validated);

            // insert into guest_club_member table if guest_club_ids are provided
            if ($request->has('guest_club_id')) {
                // do not delete existing ones, just add new ones
                $existingClubIds = GuestClubMember::where('guest_id', $guest->id)->pluck('guest_club_id')->toArray();
                $newClubIds = array_diff($request->input('guest_club_id'), $existingClubIds);

                foreach ($newClubIds as $clubId) {
                    GuestClubMember::create([
                        'guest_club_id' => $clubId,
                        'guest_id' => $guest->id,
                    ]);
                }
            }

            return redirect()->route('tenant.admin.guests.index')->with('success', 'Guest updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tenant.admin.guests.index')->with('error', 'Failed to update guest.');
        } finally {
            DB::commit();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guest $guest)
    {
        // soft delete
        $guest->delete();
        return redirect()->route('tenant.admin.guests.index')->with('success', 'Guest deleted successfully.');
    }
}
