<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Guest;
use App\Models\Tenant\GuestClub;
use App\Models\Tenant\GuestClubMember;
use App\Models\Tenant\Booking;
use App\Models\Tenant\BookingGuest;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    use LogsTenantUserActivity;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get property context
        $propertyId = selected_property_id();
        
        // Build the query with relationships
        $query = Guest::where('property_id', $propertyId);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('id_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply nationality filter
        if ($request->filled('nationality')) {
            $query->where('nationality', $request->get('nationality'));
        }

        // Apply guest club filter
        if ($request->filled('guest_club')) {
            $query->whereHas('guestClubMembers', function($q) use ($request) {
                $q->where('guest_club_id', $request->get('guest_club'));
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, ['first_name', 'last_name', 'email', 'created_at', 'nationality'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Add booking count using proper relationship
        $query->withCount([
            'bookings as total_bookings'
        ]);

        // Add last booking date using subquery
        $query->addSelect([
            'last_booking_date' => BookingGuest::select('bookings.arrival_date')
                ->join('bookings', 'booking_guests.booking_id', '=', 'bookings.id')
                ->whereColumn('booking_guests.guest_id', 'guests.id')
                ->where('bookings.property_id', $propertyId)
                ->orderBy('bookings.arrival_date', 'desc')
                ->limit(1)
        ]);

        $guests = $query->paginate(15)->withQueryString();

        // Get filter options
        $guestClubs = GuestClub::where('property_id', $propertyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $nationalities = Guest::where('property_id', $propertyId)
            ->whereNotNull('nationality')
            ->distinct()
            ->orderBy('nationality')
            ->pluck('nationality');

        return view('tenant.guests.index', compact('guests', 'guestClubs', 'nationalities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = get_countries();
        $guestClubs = GuestClub::where('property_id', selected_property_id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tenant.guests.create', compact('countries', 'guestClubs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {        
        // The is active field must be true or false we are sending checkbox on or off
        $request->merge([
            'is_active' => $request->has('is_active') ? true : false
        ]);
        // Validate input
        $validated = $request->validate([
            'title' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
            'country_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:guests,email,NULL,id,property_id,' . selected_property_id(),
            'phone' => 'required|string|max:100',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:100',
            'physical_address' => 'nullable|string|max:500',
            'residential_address' => 'nullable|string|max:500',
            'medical_notes' => 'nullable|string|max:1000',
            'dietary_preferences' => 'nullable|string|max:1000',
            'gown_size' => 'nullable|string|max:10',
            'car_registration' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'guest_club_id' => 'nullable|array',
            'guest_club_id.*' => 'exists:guest_clubs,id'
        ]);

        DB::beginTransaction();
        try {
            // make sure we are in the property context
            if (!selected_property_id()) {
                throw new \Exception('No property selected. Please select a property to continue.');
            }

            // Create the guest
            $guest = Guest::create(array_merge($validated, [
                'property_id' => selected_property_id(),
                'is_active' => $request->has('is_active') ? true : true,
                'legacy_meta' => []
            ]));

            // Add guest club memberships
            if ($request->has('guest_club_id')) {
                foreach ($request->input('guest_club_id') as $clubId) {
                    GuestClubMember::create([
                        'guest_club_id' => $clubId,
                        'guest_id' => $guest->id,
                    ]);
                }
            }

            // Log the activity
            $this->logTenantActivity(
                'create_guest',
                'Created guest: ' . $guest->full_name,
                $guest,
                [
                    'table' => 'guests',
                    'id' => $guest->id,
                    'user_id' => auth()->id(),
                    'action' => 'create'
                ]
            );

            DB::commit();

            return redirect()
                ->route('tenant.guests.index')
                ->with('success', 'Guest created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create guest. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load relationships
        $guest->load([
            'guestClubMembers.guestClub'
        ]);

        // Get booking statistics through proper relationships
        $totalBookings = $guest->bookings()
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id());
            })
            ->count();

        $confirmedBookings = $guest->bookings()
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id())
                  ->where('status', 'confirmed');
            })
            ->count();

        $checkedInBookings = $guest->bookings()
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id())
                  ->where('status', 'checked_in');
            })
            ->count();

        $completedBookings = $guest->bookings()
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id())
                  ->where('status', 'checked_out');
            })
            ->count();

        $cancelledBookings = $guest->bookings()
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id())
                  ->where('status', 'cancelled');
            })
            ->count();

        $totalSpent = $guest->totalAmountSpent();

        // Create booking stats object
        $bookingStats = (object) [
            'total_bookings' => $totalBookings,
            'total_spent' => $totalSpent,
            'confirmed_bookings' => $confirmedBookings,
            'checked_in_bookings' => $checkedInBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'first_visit' => null, // Will calculate this separately if needed
            'last_visit' => null   // Will calculate this separately if needed
        ];

        // Get recent bookings with proper relationships
        $recentBookings = $guest->bookings()
            ->with(['booking.room.type', 'booking.package'])
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id());
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $currency = property_currency();

        return view('tenant.guests.show', compact('guest', 'bookingStats', 'recentBookings', 'currency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $countries = get_countries();
        $guestClubs = GuestClub::where('property_id', selected_property_id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get current guest club memberships
        $guestClubsForGuest = GuestClubMember::where('guest_id', $guest->id)
            ->pluck('guest_club_id')
            ->toArray();

        return view('tenant.guests.edit', compact('guest', 'countries', 'guestClubs', 'guestClubsForGuest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }
        // The is active field must be true or false we are sending checkbox on or off
        $request->merge([
            'is_active' => $request->has('is_active') ? true : false
        ]);
        // Validate input

        $validated = $request->validate([
            'title' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
            'country_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:guests,email,' . $guest->id . ',id,property_id,' . selected_property_id(),
            'phone' => 'required|string|max:100',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:100',
            'physical_address' => 'nullable|string|max:500',
            'residential_address' => 'nullable|string|max:500',
            'medical_notes' => 'nullable|string|max:1000',
            'dietary_preferences' => 'nullable|string|max:1000',
            'gown_size' => 'nullable|string|max:10',
            'car_registration' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'guest_club_id' => 'nullable|array',
            'guest_club_id.*' => 'exists:guest_clubs,id'
        ]);

        DB::beginTransaction();
        try {
            $oldData = $guest->toArray();

            // Update the guest
            $guest->update(array_merge($validated, [
                'is_active' => $request->has('is_active')
            ]));

            // Update guest club memberships
            if ($request->has('guest_club_id')) {
                // Remove existing memberships
                GuestClubMember::where('guest_id', $guest->id)->delete();
                
                // Add new memberships
                foreach ($request->input('guest_club_id') as $clubId) {
                    GuestClubMember::create([
                        'guest_club_id' => $clubId,
                        'guest_id' => $guest->id,
                    ]);
                }
            } else {
                // Remove all memberships if none selected
                GuestClubMember::where('guest_id', $guest->id)->delete();
            }

            // Log the activity
            $this->logTenantActivity(
                'update_guest',
                'Updated guest: ' . $guest->full_name,
                $guest,
                [
                    'table' => 'guests',
                    'id' => $guest->id,
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => $guest->fresh()->toArray()
                ]
            );

            DB::commit();

            return redirect()
                ->route('tenant.guests.show', $guest)
                ->with('success', 'Guest updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update guest. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Check if guest has any bookings
            $hasBookings = $guest->bookings()
                ->whereHas('booking', function($q) {
                    $q->where('property_id', selected_property_id());
                })
                ->exists();

            if ($hasBookings) {
                return back()->withErrors(['error' => 'Cannot delete guest with existing bookings. Please cancel all bookings first.']);
            }

            // Log the activity before deletion
            $this->logTenantActivity(
                'delete_guest',
                'Deleted guest: ' . $guest->full_name,
                $guest,
                [
                    'table' => 'guests',
                    'id' => $guest->id,
                    'user_id' => auth()->id(),
                    'action' => 'delete',
                    'guest_data' => $guest->toArray()
                ]
            );

            // Remove guest club memberships
            GuestClubMember::where('guest_id', $guest->id)->delete();

            // Soft delete the guest
            $guest->delete();

            DB::commit();

            return redirect()
                ->route('tenant.guests.index')
                ->with('success', 'Guest deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete guest. Please try again.']);
        }
    }

    /**
     * Toggle guest status.
     */
    public function toggleStatus(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $guest->update(['is_active' => !$guest->is_active]);

        // Log the activity
        $this->logTenantActivity(
            'toggle_guest_status',
            'Toggled status for guest: ' . $guest->full_name . ' to ' . ($guest->is_active ? 'Active' : 'Inactive'),
            $guest,
            [
                'table' => 'guests',
                'id' => $guest->id,
                'new_status' => $guest->is_active
            ]
        );

        return back()->with('success', 'Guest status updated successfully!');
    }

    /**
     * Get guest bookings.
     */
    public function bookings(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $query = $guest->bookings()
            ->with(['booking.room.type', 'booking.package'])
            ->whereHas('booking', function($q) {
                $q->where('property_id', selected_property_id());
            });

        // Apply filters
        if (request('status')) {
            $query->whereHas('booking', function($q) {
                $q->where('status', request('status'));
            });
        }

        if (request('date_from')) {
            $query->whereHas('booking', function($q) {
                $q->where('arrival_date', '>=', request('date_from'));
            });
        }

        if (request('date_to')) {
            $query->whereHas('booking', function($q) {
                $q->where('departure_date', '<=', request('date_to'));
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')
                         ->paginate(15);

        return view('tenant.guests.bookings', compact('guest', 'bookings'));
    }

    /**
     * Get guest invoices.
     */
    public function invoices(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Get invoices through bookings with proper relationship and error handling
        $invoices = collect();
        
        try {
            $bookingGuests = $guest->bookings()
                ->whereHas('booking', function($q) {
                    $q->where('property_id', selected_property_id());
                })
                ->with(['booking.invoices.booking.room'])
                ->get();

            foreach($bookingGuests as $bookingGuest) {
                if ($bookingGuest->booking && $bookingGuest->booking->relationLoaded('invoices')) {
                    $bookingInvoices = $bookingGuest->booking->invoices;
                    if ($bookingInvoices && $bookingInvoices->count() > 0) {
                        foreach($bookingInvoices as $invoice) {
                            $invoices->push($invoice);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but continue with empty collection
            \Log::error('Error fetching guest invoices: ' . $e->getMessage());
        }
        
        // Sort by created_at descending and limit results
        $invoices = $invoices->sortByDesc('created_at')->take(50);
        $currency = property_currency();

        return view('tenant.guests.invoices', compact('guest', 'invoices', 'currency'));
    }

    /**
     * Get guest payments.
     */
    public function payments(Guest $guest)
    {
        // Authorization check
        if ($guest->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Get payments through invoices with proper relationship and error handling
        $payments = collect();
        
        try {
            $bookingGuests = $guest->bookings()
                ->whereHas('booking', function($q) {
                    $q->where('property_id', selected_property_id());
                })
                ->with(['booking.invoices.invoicePayments.recordedBy', 'booking.invoices.booking.room'])
                ->get();

            foreach($bookingGuests as $bookingGuest) {
                if ($bookingGuest->booking && $bookingGuest->booking->relationLoaded('invoices')) {
                    $bookingInvoices = $bookingGuest->booking->invoices;
                    if ($bookingInvoices && $bookingInvoices->count() > 0) {
                        foreach($bookingInvoices as $invoice) {
                            if ($invoice->relationLoaded('invoicePayments') && $invoice->invoicePayments->count() > 0) {
                                foreach($invoice->invoicePayments as $payment) {
                                    $payments->push($payment);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but continue with empty collection
            \Log::error('Error fetching guest payments: ' . $e->getMessage());
        }

        // Sort by payment_date descending and limit results
        $payments = $payments->sortByDesc('payment_date')->take(50);
        $currency = property_currency();

        return view('tenant.guests.payments', compact('guest', 'payments', 'currency'));
    }
}
