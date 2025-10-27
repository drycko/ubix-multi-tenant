<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Booking;
use App\Models\Tenant\Room;
use App\Models\Tenant\RoomType;
use App\Models\Tenant\RoomAmenity;
use App\Models\Tenant\Package;
use App\Models\Tenant\Guest;
use App\Models\Tenant\GuestRequest;
use App\Models\Tenant\GuestFeedback;
use App\Models\Tenant\DigitalKey;

class GuestPortalController extends Controller
{
  // Show guest portal dashboard
  public function index(Request $request)
  {
    // Show available rooms, bookings, and guest actions
    $rooms = Room::getAvailableRooms();
    $bookings = Booking::where('guest_id', $request->user()?->id)->get();
    return view('tenant.guest-portal.index', compact('rooms', 'bookings'));
  }

  // landing page
  public function landing()
  {
    // Load necessary data for the landing page
    $availableRooms = Room::getAvailableRooms()->load('type', 'property');
    $availableRoomTypes = RoomType::where('is_active', true)->get();
    $packages = Package::where('pkg_status', 'active')->get();
    $currency = tenant_currency();
    return view('tenant.guest-portal.landing', compact('availableRooms', 'availableRoomTypes', 'packages', 'currency'));
  }

  // post
  public function landingSearch(Request $request)
  {
    try {
      // Process search form submission from landing page
      $query = Room::query();
      // if room type is selected
      if ($request->filled('room_type')) {
        $query->whereHas('type', function ($q) use ($request) {
          $q->where('id', $request->input('room_type'));
        });
      }

      $packageBooking = false;

      // if package is selected
      if ($request->filled('package_id')) {
        $packageId = $request->input('package_id');
        $query->whereHas('packages', function ($q) use ($packageId) {
          $q->where('id', $packageId);
          $packageBooking = true;
        });
      }

      // validate check-in and check-out dates
      $request->validate([
        'checkin_date' => 'required|date',
        'checkout_date' => 'required|date|after:checkin_date',
      ]);

      if ($request->filled('checkin_date') && $request->filled('checkout_date')) {
        $checkIn = $request->input('checkin_date');
        $checkOut = $request->input('checkout_date');
        $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
          $q->where(function ($subQ) use ($checkIn, $checkOut) {
            $subQ->where('status', 'confirmed')
                ->whereBetween('arrival_date', [$checkIn, $checkOut])
                ->orWhereBetween('departure_date', [$checkIn, $checkOut])
                ->orWhere(function ($qq) use ($checkIn, $checkOut) {
                  $qq->where('arrival_date', '<=', $checkIn)
                      ->where('departure_date', '>=', $checkOut);
                });
          });
        });
      }

      $availableRooms = $query->get()->load('type', 'property');
      $availableRoomTypes = RoomType::where('is_active', true)->get();
      $packages = Package::where('pkg_status', 'active')->get();
      $currency = tenant_currency();

      if ($packageBooking) {
        return redirect()->route('tenant.guest-portal.package-booking', ['package' => $request->input('package_id')])->withInput()->with([
          'checkin_date' => $request->input('checkin_date'),
          'checkout_date' => $request->input('checkout_date'),
        ]);
      }
      return redirect()->route('tenant.guest-portal.booking')->withInput()->with([
        'checkin_date' => $request->input('checkin_date'),
        'checkout_date' => $request->input('checkout_date'),
      ]);
    } catch (\Exception $e) {
      \Log::error('Guest portal landing search error: ' . $e->getMessage());
      return redirect()->route('tenant.guest-portal.landing')->withErrors(['error' => 'An error occurred while processing your search: ' . $e->getMessage()])->withInput();
    }
  }

  // show package selection form
  public function showPackageSelection()
  {
    $packages = Package::where('pkg_status', 'active')->get();
    return view('tenant.guest-portal.select-package', compact('packages'));
  }
  
  // Show booking form
  public function showBookingForm(Request $request)
  {
    // filter rooms based on post
    $query = Room::query();
    if ($request->query('room_type')) {
      $query->whereHas('type', function ($q) use ($request) {
        $q->where('id', $request->query('room_type'));
      });
    }
    // filter by availability dates
    if ($request->query('checkin_date') && $request->query('checkout_date')) {
      $checkIn = $request->query('checkin_date');
      $checkOut = $request->query('checkout_date');
      $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
        $q->where(function ($subQ) use ($checkIn, $checkOut) {
          $subQ->where('status', 'confirmed')
              ->whereBetween('arrival_date', [$checkIn, $checkOut])
              ->orWhereBetween('departure_date', [$checkIn, $checkOut])
              ->orWhere(function ($qq) use ($checkIn, $checkOut) {
                $qq->where('arrival_date', '<=', $checkIn)
                    ->where('departure_date', '>=', $checkOut);
              });
        });
      });
    }
    // filter by room type amenities
    if ($request->query('amenities')) {
      // array of amenity slugs
      $amenities = $request->query('amenities', []);
      // amenities from type->amenities_array
      $query->whereHas('type', function ($q) use ($amenities) {
        $q->whereIn('amenities', $amenities);
      });
    }

    $today = \Carbon\Carbon::today()->format('Y-m-d');
    $currency = tenant_currency();

    $selectedRoomType = $request->query('room_type') ?? null;
    $checkinDate = $request->query('checkin_date') ?? $today;
    $checkoutDate = $request->query('checkout_date') ?? $today;
    $isShared = $request->query('is_shared', false) ? true : false;

    $availableRoomTypes = RoomType::where('is_active', true)->get();
    $availableRooms = $query->paginate(6);
    // only amenities that are assigned to any room type
    $roomAmenities = RoomAmenity::all();
    return view('tenant.guest-portal.booking', compact('availableRooms', 'availableRoomTypes', 'roomAmenities', 'selectedRoomType', 'checkinDate', 'checkoutDate', 'isShared', 'currency'));
  }
  
  // Handle booking submission
  public function book(Request $request)
  {
    // Validate and create booking (simplified)
    $data = $request->validate([
      'room_id' => 'required|exists:rooms,id',
      'check_in' => 'required|date',
      'check_out' => 'required|date|after:check_in',
      'guest_name' => 'required|string',
      'guest_email' => 'required|email',
    ]);
    // ...create guest and booking logic...
    // Redirect to portal dashboard
    return redirect()->route('tenant.guest-portal.index')->with('success', 'Booking created!');
  }

  // Show package booking form
  public function showPackageBookingForm(Package $package)
  {
    $rooms = Room::getAvailableRooms();
    $rooms->load('packages');

    // Only allow rooms that are compatible with the package
    $rooms = $rooms->filter(function ($room) use ($package) {
      return $room->packages && $room->packages->contains($package);
    });

    $currency = tenant_currency();

    $allowedPackages = Package::where('pkg_status', 'active')
        ->get();
    // $guests = Guest::where('property_id', $propertyId)
    //     ->get();
    // only allow bookings for future dates, not past dates
    // we can also add a date picker to select the arrival date and filter available rooms based on that
    $arrivalDate = now()->format('Y-m-d');
    $bookingSources = ['website', 'walk_in', 'phone', 'agent', 'legacy', 'inhouse'];

    return view('tenant.guest-portal.package-booking', compact('rooms', 'package', 'allowedPackages', 'bookingSources', 'currency', 'arrivalDate'));
        
  }
}
