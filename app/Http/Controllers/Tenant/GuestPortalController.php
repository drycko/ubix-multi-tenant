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
use App\Services\TaxCalculationService;
use App\Services\Tenant\NotificationService;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class GuestPortalController extends Controller
{
  use LogsTenantUserActivity;
  protected NotificationService $notificationService;

  public function __construct(NotificationService $notificationService)
  {
    $this->notificationService = $notificationService;
  }

  protected function getGuest()
  {
      return session('guest_id') ? Guest::find(session('guest_id')) : null;
  }
  // Show guest portal dashboard
  public function dashboard(Request $request)
  {
    // Show available rooms, bookings, and guest actions
    $guest = $this->getGuest();
    $bookings = $guest ? $guest->bookings : [];
    $clubs = $guest ? $guest->clubs : [];
    return view('tenant.guest-portal.dashboard', compact('guest', 'bookings', 'clubs'));
  }
  
  // landing page
  public function index()
  {
    // Load necessary data for the landing page
    $guest = $this->getGuest();
    $availableRooms = Room::getAvailableRooms()->load('type', 'property');
    $availableRoomTypes = RoomType::where('is_active', true)->get();
    $packages = Package::where('pkg_status', 'active')->get();
    $currency = tenant_currency();
    return view('tenant.guest-portal.index', compact('availableRooms', 'availableRoomTypes', 'packages', 'currency', 'guest'));
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

        // get package to verify it exists
        $package = Package::findOrFail($packageId);
        if ($package) {
          $packageBooking = true;
        }
      }
      
      // validate check-in and check-out dates
      $request->validate([
        'checkin_date' => 'sometimes|date',
        'checkout_date' => 'sometimes|date|after:checkin_date',
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
        return redirect()->route('tenant.guest-portal.booking', ['package' => $package])->withInput()->with([
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
      return redirect()->route('tenant.guest-portal.index')
        ->withErrors(['error' => 'An error occurred while processing your search: ' . $e->getMessage()])->withInput();
    }
  }
  
  // show package selection form
  public function showPackageSelection()
  {
    $guest = $this->getGuest();
    $packages = Package::where('pkg_status', 'active')->get();
    return view('tenant.guest-portal.select-package', compact('packages', 'guest'));
  }
  
  // Show booking form
  public function showBookingForm(Request $request)
  {
    
    \Log::info('Booking form accessed', $request->all());
    $guest = $this->getGuest();
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
    $currencySymbol = get_currency_symbol($currency);
    
    $selectedRoomType = $request->query('room_type') ?? null;
    $checkinDate = $request->query('checkin_date') ?? $today;
    $checkoutDate = $request->query('checkout_date') ?? $today;
    $isShared = $request->query('is_shared', false) ? true : false;
    
    $defaultTenantCheckinDays = Booking::ALLOWED_BOOKING_DAYS;
    $defaultTenantCheckinNights = Booking::DEFAULT_MIN_NIGHTS;
    $defaultTenantMaxNights = Booking::DEFAULT_MAX_NIGHTS;
    
    $packageCheckinDays = $request->query('package_checkin_days', $defaultTenantCheckinDays);
    $packageNumberOfNights = $request->query('package_number_of_nights', $defaultTenantCheckinNights);
    $packageMaxNights = $request->query('package_max_nights', $defaultTenantMaxNights);
    
    // $availableRoomTypes = RoomType::where('is_active', true)->get();
    $availableRooms = $query->get(); // pagination is making us loose track of booking progress
    // if we are booking a package, we need to filter rooms by package
    if ($request->query('package')) {
      $packageId = $request->query('package');
      // get package to verify it exists
      $package = Package::findOrFail($packageId);
      if ($package) {
        // Only allow rooms that are compatible with the package
        $availableRooms = $availableRooms->filter(function ($room) use ($package) {
          return $room->packages && $room->packages->contains($package);
        });
      }
    }
    $availableRoomTypes = $availableRooms->pluck('type')->unique('id')->values();

    // only amenities that are assigned to any room type
    $roomAmenities = RoomAmenity::all();
    return view('tenant.guest-portal.booking', compact(
      'availableRooms', 'availableRoomTypes', 'roomAmenities', 'selectedRoomType',
      'checkinDate', 'checkoutDate', 'isShared', 'currencySymbol',
      'packageCheckinDays', 'packageNumberOfNights', 'packageMaxNights', 'guest'
    ));
  }
  
  // Handle booking submission
  public function book(Request $request)
  {
    try {
      \DB::beginTransaction();
      
      $validated = $request->validate([
        'room_id' => 'required|exists:rooms,id',
        // 'guest_id' => 'required|exists:guests,id',
        'arrival_date' => 'required|date|after_or_equal:today',
        'departure_date' => 'required|date|after:arrival_date',
        // 'adults' => 'required|integer|min:1',
        'children' => 'sometimes|integer|min:0',
        // 'daily_rate' => 'nullable|numeric|min:0',
        // 'special_requests' => 'sometimes|string|max:500',
        'source' => 'sometimes|in:website,walk_in,phone,agent,legacy,inhouse',
        'is_shared' => 'sometimes|boolean',
        'package_id' => 'sometimes|exists:packages,id',
        'guests' => 'required|array|min:1',
      ]);
      
      $guest = $this->getGuest();
      
      $package_id = $request->input('package_id', null);
      // count array length of guests
      $countGuests = count($guests ?? []);
      $isShared = $countGuests > 1 ? true : false;

      $checkinDate = date('Y-m-d', strtotime($validated['arrival_date']));
      $checkoutDate = date('Y-m-d', strtotime($validated['departure_date']));

      // if package we have to validate if the checkin and checkout dates align with package settings
      if ($package_id) {
        $package = Package::find($package_id);
        if (!$package) {
          throw new \Exception("Selected package not found.");
        }
        // check if checkin day is allowed
        $checkinDayOfWeek = date('l', strtotime($checkinDate)); // get day name
        $allowedCheckinDays = explode(',', $package->checkin_days); // assuming checkin_days is stored as comma-separated values
        if (!in_array($checkinDayOfWeek, $allowedCheckinDays)) {
          throw new \Exception("Check-in day is not allowed for the selected package.");
        }
        $packageNights = ceil((strtotime($checkoutDate) - strtotime($checkinDate)) / (60 * 60 * 24));
        if ($packageNights < $package->min_nights || $packageNights > $package->max_nights) {
          throw new \Exception("The selected dates do not comply with the package's night requirements.");
        }
      }

      // we will check if the room and guest belong to the current property
      $room = Room::where('id', $validated['room_id'])
      ->first();
      // get room property from selected room
      $roomProperty = $room->property;
      $propertyId = $roomProperty->id;
      
      if (!$room) {
        throw new Exception("Selected room not found.");
      }

      if ($isShared) {
        $countGuests = $countGuests ?? $room->type->max_capacity;
        $roomRate = $room->type->getRangeRates(true, $checkinDate, $checkoutDate)->first();
      } else {
        $countGuests = 1;
        $roomRate = $room->type->getRangeRates(false, $checkinDate, $checkoutDate)->first();
      }
      $rateBasis = $roomRate->conditions['is_per_night'] ?? false ? 'per night' : 'per person';

      $dailyRate = $roomRate->amount;
      if ($rateBasis == 'per person') {
        $dailyRate = $dailyRate * max(1, $countGuests);
      }
      
      $arrivalDate = date('Y-m-d', strtotime($validated['arrival_date']));
      $departureDate = date('Y-m-d', strtotime($validated['departure_date']));
      $nights = ceil((strtotime($departureDate) - strtotime($arrivalDate)) / (60 * 60 * 24));
      
      // if daily_rate is not provided, we calculate it based on room type or package (if any)
      // if (empty($dailyRate)) {
      //   $dailyRate = Booking::calculatePackageDailyRate($isShared);
  
      // }
      $rateBasis = $roomRate->conditions['is_per_night'] ?? false ? 'per night' : 'per person';
      $dailyRate = $roomRate->amount;
      if ($rateBasis == 'per person') {
        $dailyRate = $dailyRate * max(1, $countGuests);
      }
      
      $dailyRate = round(floatval($dailyRate), 2);

      $totalAmount = $nights * $dailyRate;
      
      // Generate booking code
      $bcode = Booking::generateBookingCode($arrivalDate, $room->number);
      
      $booking_status = 'pending';
      
      $booking_source = $request->input('source', 'website');
      
      // Create booking
      $booking = Booking::create([
        'package_id' => $package_id,
        'is_shared' => $isShared,
        'property_id' => $propertyId,
        'room_id' => $room->id,
        'bcode' => $bcode,
        'arrival_date' => $arrivalDate,
        'departure_date' => $departureDate,
        'nights' => $nights,
        'daily_rate' => $dailyRate,
        'total_amount' => $totalAmount,
        'status' => $booking_status,
        'source' => 'website',
        'ip_address' => $request->ip(),
      ]);

      // since we are not passing guest_id from the form, we first check if the email exists and save the guest and get its ID.. main guest is guests[0] we are not getting guest_email_1 but all guests for guests[0]
      $guests = $validated['guests'];
      $guest = Guest::where('email', $guests[0]['email'])
      ->first();
      
      if (!$guest) {
        // create new guest
        $guest = Guest::create([
          'property_id' => $propertyId,
          'first_name' => $guests[0]['fname'],
          'last_name' => $guests[0]['lname'],
          'email' => $guests[0]['email'],
          'phone' => $guests[0]['phone'] ?? null,
          'id_number' => $guests[0]['idno'] ?? null,
          'gown_size' => $guests[0]['gown_size'] ?? null,
          'dietary_preferences' => $guests[0]['special_requests'] ?? null,
          // other guest fields can be added here
        ]);
        // Attach guest as primary booking guest (like import)
        $booking->bookingGuests()->create([
          'guest_id' => $guest->id,
          'is_primary' => true,
          'is_adult' => true,
          'adults' => $validated['adults'] ?? 1,
          'children' => $validated['children'] ?? 0,
          'special_requests' => $guests[0]['special_requests'] ?? null,
          'property_id' => $propertyId,
        ]);
      }

      // We are passing more than 1 guest(so we will have to check how many guests have been passed)
      $additionalGuestIds = [];

      if ($isShared && $countGuests > 1) {
        // now save the guests as per countGuests
        for ($i = 2; $i <= $countGuests; $i++) {
          $guestData = $guests[$i - 1];
          $additionalGuest = Guest::where('email', $guestData['email'])->first();
          if (!$additionalGuest) {
            // create new guest
            $additionalGuest = Guest::create([
              'property_id' => $propertyId,
              'first_name' => $guestData['fname'],
              'last_name' => $guestData['lname'],
              'email' => $guestData['email'],
              'phone' => $guestData['phone'] ?? null,
              'id_number' => $guestData['idno'] ?? null,
              'gown_size' => $guestData['gown_size'] ?? null,
              'dietary_preferences' => $guestData['special_requests'] ?? null,
              // other guest fields can be added here
            ]);
          }
          $additionalGuestIds[] = $additionalGuest->id;
          $booking->bookingGuests()->create([
            'guest_id' => $additionalGuest->id,
            'is_primary' => false,
            'is_adult' => true,
            'adults' => 1,
            'children' => 0,
            'special_requests' => $guestData['special_requests'] ?? null,
            'property_id' => $propertyId,
          ]);
        }
      }
      
      // if ($isShared && $countGuests > 1) {
      //   // Attach as non-primary booking guests according to countGuests
      //   for ($i = 2; $i <= $countGuests; $i++) {
      //     $booking->bookingGuests()->create([
      //       'guest_id' => $additionalGuestIds[$i - 2],
      //       'is_primary' => false,
      //       'is_adult' => true,
      //       'adults' => 1,
      //       'children' => 0,
      //       'special_requests' => $guests[$i - 1]['special_requests'] ?? null,
      //       'property_id' => $propertyId,
      //     ]);
      //   }
      // }
      
      $invoice_number = Booking::generateUniqueInvoiceNumber('0000001');
      $invoice_status = 'pending';
      
      // Calculate tax for the booking
      $taxService = new TaxCalculationService();
      $taxCalculation = $taxService->calculateTaxForInvoice($totalAmount);
      
      $booking_invoice = $booking->invoices()->create([
        'property_id' => $propertyId,
        'invoice_number' => $invoice_number,
        'amount' => $taxCalculation['total_amount'],
        'subtotal_amount' => $taxCalculation['subtotal_amount'],
        'tax_amount' => $taxCalculation['tax_amount'],
        'tax_rate' => $taxCalculation['tax_rate'],
        'tax_name' => $taxCalculation['tax_name'],
        'tax_type' => $taxCalculation['tax_type'],
        'tax_inclusive' => $taxCalculation['tax_inclusive'],
        'tax_id' => $taxCalculation['tax_id'],
        'status' => $invoice_status,
      ]);
      // if we reach here, we can commit the transaction and redirect to show new booking page
      // \DB::commit();
      
      // $this->logActivity('created', 'Booking', $booking->id, "Created booking {$booking->bcode} for room {$room->number}");
      $this->logTenantActivity(
        'create_booking',
        'Created a new booking: ' . $booking->bcode . ' for room ' . $room->number . ' (ID: ' . $room->id . ')',
        $booking,
        [
          'table' => 'bookings',
          'id' => $booking->id,
          'user_id' => 0, // guest portal bookings have no user
          'guest_id' => $guest->id,
          'changes' => $booking->toArray()
        ]
      );
      \DB::commit();
      // let us redirect to the public booking invoice page
      return redirect()->route('tenant.booking-invoices.public-view', $booking_invoice)->with('success', 'Booking created successfully!');
      
      // return redirect()->route('tenant.bookings.index')->with('success', 'Booking created successfully!');
      
      //     // $this->processStoreBooking($request);
      
    } catch (\Exception $e) {
      \DB::rollBack();
      \Log::error("Booking failed: " . $e->getMessage());
      // do not redirect, just show error on the same page
      return back()->withErrors(['error' => 'Failed to create booking: ' . $e->getMessage()])->withInput();
    }
  }
  
  // error page
  public function showErrorPage($errorCode)
  {
    // get error message based on code
    $errorMessages = [
      404 => 'We’re sorry, but the page you were looking for doesn’t exist.',
      500 => 'An internal server error occurred.',
      403 => 'You do not have permission to access this page.',
    ];
    $error = $errorMessages[$errorCode] ?? 'Unknown error';
    return view('tenant.guest-portal.error', compact('error'));
  }

  public function showPackageBookingForm(Request $request)
  {
    
    \Log::info('Booking form accessed', $request->all());
    $guest = $this->getGuest();
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
    $currencySymbol = get_currency_symbol($currency);
    
    $selectedRoomType = $request->query('room_type') ?? null;
    $checkinDate = $request->query('checkin_date') ?? $today;
    $checkoutDate = $request->query('checkout_date') ?? $today;
    $isShared = $request->query('is_shared', false) ? true : false;
    
    $defaultTenantCheckinDays = Booking::ALLOWED_BOOKING_DAYS;
    $defaultTenantCheckinNights = Booking::DEFAULT_MIN_NIGHTS;
    $defaultTenantMaxNights = Booking::DEFAULT_MAX_NIGHTS;
    
    $packageCheckinDays = $request->query('package_checkin_days', $defaultTenantCheckinDays);
    $packageNumberOfNights = $request->query('package_number_of_nights', $defaultTenantCheckinNights);
    $packageMaxNights = $request->query('package_max_nights', $defaultTenantMaxNights);
    
    // $availableRoomTypes = RoomType::where('is_active', true)->get();
    $availableRooms = $query->get(); // pagination is making us loose track of booking progress
    // if we are booking a package, we need to filter rooms by package
    if ($request->query('package')) {
      $packageId = $request->query('package');
      // get package to verify it exists
      $package = Package::findOrFail($packageId);
      if ($package) {
        // Only allow rooms that are compatible with the package
        $availableRooms = $availableRooms->filter(function ($room) use ($package) {
          return $room->packages && $room->packages->contains($package);
        });
      }
    }
    if (!isset($package)) {
      // if no package provided, redirect back with error
      return redirect()->route('tenant.guest-portal.index')
        ->withErrors(['error' => 'Please select a package to proceed with booking.'])->withInput();
    }
    $availableRoomTypes = $availableRooms->pluck('type')->unique('id')->values();

    // only amenities that are assigned to any room type
    $roomAmenities = RoomAmenity::all();
    // return view('tenant.guest-portal.booking', compact(
    //   'availableRooms', 'availableRoomTypes', 'roomAmenities', 'selectedRoomType',
    //   'checkinDate', 'checkoutDate', 'isShared', 'currencySymbol',
    //   'packageCheckinDays', 'packageNumberOfNights', 'packageMaxNights', 'guest'
    // ));
    return view('tenant.guest-portal.package-booking', compact(
      'package', 'availableRooms', 'availableRoomTypes', 'roomAmenities', 'selectedRoomType',
      'checkinDate', 'checkoutDate', 'isShared', 'currencySymbol',
      'packageCheckinDays', 'packageNumberOfNights', 'packageMaxNights', 'guest'
    ));
  }

  // Show login form
  public function showLoginForm()
  {
    return view('tenant.guest-portal.login');
  }

  public function sendLoginLink(Request $request)
  {
    $request->validate(['email' => 'required|email']);
    $guest = Guest::where('email', $request->email)->first();

    if ($guest) {
      // Generate a signed login link
      $link = URL::temporarySignedRoute(
          'tenant.guest-portal.magic-login', now()->addMinutes(30), ['guest' => $guest->id]
      );
      // if is local environment, log the link instead of sending email
      if (app()->environment('local')) {
        \Log::info("Magic login link for {$guest->email}: {$link}");
        $this->notificationService->logEmail('info', 'sending_login_email', [
          'url' => $link,
          'recipient_email' => $guest->email,
        ]);
        return back()->with('success', 'Check your email for a login link. (Link logged in local environment)');
      }
      // Send email with $link
      Mail::to($guest->email)->send(new MagicLoginMail($link));
    }
    // Always respond with success message (do not reveal guest existence)
    return back()->with('success', 'Check your email for a login link.');
  }

  public function magicLogin(Request $request, $guestId)
  {
    // Validate signed route
    $guest = Guest::findOrFail($guestId);
    // Authenticate as this guest
    session(['guest_id' => $guest->id]);
    return redirect()->route('tenant.guest-portal.dashboard');
  }

  public function logout()
  {
    session()->forget('guest_id');
    return redirect()->route('tenant.guest-portal.login');
  }
}
  