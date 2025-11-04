@php

// packageId from get parameter
$packageId = $_GET['package'] ?? null;
$package = App\Models\Tenant\Package::findOrFail($packageId);
if (!$package) {
  abort(404, 'Package not found.');
}

// Pass allowed check-in days and nights to JS
// $package->pkg_checkin_days is a json array stored as string in DB, e.g. '["Wednesday","Sunday"]'
$allowedDays = json_decode($package->pkg_checkin_days, true) ?? ['Monday','Wednesday','Friday']; // Example fallback
$packageNights = $package->pkg_number_of_nights ?? 1;
// Map to 3-letter uppercase codes for JS calendar logic
$dayMap = [
'Sunday' => 'SUN',
'Monday' => 'MON',
'Tuesday' => 'TUE',
'Wednesday' => 'WED',
'Thursday' => 'THU',
'Friday' => 'FRI',
'Saturday' => 'SAT',
];
$allowedWeekdays = array_map(fn($d) => $dayMap[$d] ?? strtoupper(substr($d,0,3)), $allowedDays);

$tenant_icons_path = asset('storage/branding/icons');
$tenantPackagesIcon = $tenant_icons_path . '/brook-package.png';
$tenantDateIcon = $tenant_icons_path . '/brook-date.png';
$tenantRoomIcon = $tenant_icons_path . '/brook-rooms.png';
$tenantGuestsIcon = $tenant_icons_path . '/brook-guests.png';
$tenantConfirmIcon = $tenant_icons_path . '/brook-cost.png';
@endphp

@extends('tenant.layouts.guest')

@section('title', 'Book a Room')

{{-- calendar css (for booking form) --}}
<link rel="stylesheet" href="{{ asset('assets/css/calendar.css') }}">

<script>
  // allowedWeekdays: e.g. ["MON","WED","FRI"]
  const allowedWeekdays = @json($allowedWeekdays);
  let packageNights = @json($packageNights); // since this is free pick we will allow the user to change nights
</script>

@section('content')
<!-- Listing Section / Sytle Two-->
<section class="add-listing-section" id="listing-section">
  <div class="auto-container">
    {{-- <div class="filters-backdrop"></div> --}}
    
    <div class="sec-title text-center">
      <h2 class="txt-brook-blue">Package Booking</h2>
      <span class="divider"></span>
      <div class="text">{{ $package->pkg_name }} ({{ $package->pkg_number_of_nights }} Nights)</div>
    </div>
    
    <div class="row">
      <!-- Sidebar Column (hide in mobile) -->
      <div class="sidebar-column sidebar-side sticky-container col-lg-4 col-md-12 col-sm-12 d-none d-lg-block">
        <aside class="sidebar theiaStickySidebar">
          <div class="sticky-sidebar">
            <ul class="listing-content-list">
              @if ($package)
              <li class="active"><span class="icon text-muted bi bi-gift"></span> <span id="summaryPackage">{{ $package->pkg_name }}</span></li>
              @endif
              <li><span class="icon text-muted bi bi-calendar"></span> <span id="summaryDates">Date Range</span></li>
              <li><span class="icon text-muted bi bi-door-open"></span> <span id="summaryRoom">Room</span></li>
              <li><span class="icon text-muted bi bi-person"></span> <span id="summaryGuests">Guests</span></li>
              <li><span class="icon text-muted bi bi-cash"></span> {{ $currencySymbol }}<span id="summaryRate">0.00</span></li>
              <li><span class="icon text-muted bi bi-credit-card"></span> {{ $currencySymbol }}<span id="summaryTotal">0.00</span></li>
              <li><span class="icon text-muted bi bi-card-list"></span> <span id="summaryRequests">Special Requests</span></li>
            </ul>
            {{-- Error Messages --}}
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <h6>Please correct the following errors:</h6>
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="d-flex justify-content-between mt-3">
              <div>
                <a href="javascript:void(0);" class="theme-btn btn-style-one bg-gray small" id="prevStep"><span class="icon flaticon-left-arrow"></span> Back</a>
                <a href="javascript:void(0);" class="theme-btn btn-style-one bg-brook-blue small" id="nextStep">Next <span class="icon flaticon-right"></span></a>
              </div>
            </div>
        </aside>
      </div>
      
      {{-- <!-- Filters Column -->
      <div class="filters-column col-lg-4 col-md-12 col-sm-12">
        <!-- Sidebar Summary -->
        <div class="inner-column">
          <button type="button" class="theme-btn close-filters">X</button>
          <div class="default-tabs style-two tabs-box">
            <!--Tabs Box-->
            <ul class="tab-buttons clearfix">
              <li class="tab-btn active-btn" data-tab="#tabSummary">Booking Summary</li>
            </ul>
            <div class="tabs-content p-0 pt-3">
              <div class="tab active-tab" id="bookingSummary">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item"><strong>Date Range:</strong> <span id="summaryDates"></span></li>
                  <li class="list-group-item"><strong>Room:</strong> <span id="summaryRoom"></span></li>
                  <li class="list-group-item"><strong>Guests:</strong> <span id="summaryGuests"></span></li>
                  <li class="list-group-item"><strong>Rate:</strong> <span id="summaryRate"></span></li>
                  <li class="list-group-item"><strong>Total:</strong> <span id="summaryTotal"></span></li>
                  <li class="list-group-item"><strong>Special Requests:</strong> <span id="summaryRequests"></span></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div> --}}
      <!-- Main Stepper -->
      <!-- Content Column -->
      <div class="content-column col-lg-8 col-md-12 col-sm-12">
        <div class="ls-outer">
          {{-- <div class="col-lg-8"> --}}
            <div class="stepper-container">
              {{-- <div class="card-body"> --}}
                <!-- Bootstrap 4 Stepper (use javascript to control navigation, and update params for persistent state) -->
                <div id="bookingStepper" class="stepper-wrapper">
                  <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link stepper-link content-center" id="step1-tab" href="javascript:void(0);" >
                        <img class="nav-item-icon" src="{{ $tenantDateIcon }}" alt="Dates">
                        Dates
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link stepper-link content-center " id="step2-tab" href="javascript:void(0);">
                        <img class="nav-item-icon" src="{{ $tenantRoomIcon }}" alt="Rooms">
                        Room
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link stepper-link content-center " id="step3-tab" href="javascript:void(0);" >
                        <img class="nav-item-icon" src="{{ $tenantPackagesIcon }}" alt="Guests">
                        Guests
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link stepper-link content-center " id="step4-tab" href="javascript:void(0);" >
                        <img class="nav-item-icon" src="{{ $tenantConfirmIcon }}" alt="Confirm">
                        Confirm
                      </a>
                    </li>
                  </ul>
                  <form id="bookingForm" method="POST" action="{{ route('tenant.guest-portal.booking.store') }}">
                    @csrf
                    <div class="tab-content">
                      <!-- Step 1: Date Range -->
                      <div class="tab-pane fade show active" id="step1" role="tabpanel">
                        <div class="form-row">
                          <div class="form-group col-md-5">
                            <label for="arrival_date">Arrival Date</label>
                            <input type="date" class="form-control" id="arrival_date" name="arrival_date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required readonly>
                          </div>
                          <div class="form-group col-md-5">
                            <label for="departure_date">Departure Date</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required readonly>
                          </div>
                          <div class="form-group col-md-2 d-flex align-items-end">
                            <a href="#" type="button" class="btn btn-outline-info mt-4 float-right" data-toggle="modal" data-target="#calendarModal"><i class="fas fa-calendar-alt"></i> Pick Dates</a>
                          </div>
                          
                        </div>
                        <div class="form-row">

                          <div class="form-group col-md-5">
                            <label for="is_shared">Multiple Guests</label>
                            <select class="form-control" id="is_shared" name="is_shared">
                              <option value="0">No</option>
                              <option value="1">Yes</option>
                            </select>
                          </div>
                          {{-- <div class="form-group col-md-5">
                            <label for="number_of_nights">Nights <small class="text-muted">(Min: {{ $packageNights }}, Max: {{ $packageMaxNights }})</small></label>
                            <input type="number" class="form-control"
                              id="number_of_nights" name="number_of_nights"
                              min="{{ $packageNights }}" max="{{ $packageMaxNights }}"
                              value="{{ old('number_of_nights', $packageNights) }}" required>
                            <div class="invalid-feedback">
                              Please enter a valid number of nights.
                            </div>
                          </div> --}}
                        </div>
                        <a type="button" class="theme-btn btn-style-one bg-brook-blue float-right" id="toStep2">Next <span class="icon flaticon-right"></span></a>
                      </div>
                      <!-- Step 2: Select Room -->
                      {{-- ... previous step markup ... --}}
                      <!-- Step 2: Select Room -->
                      <div class="tab-pane fade" id="step2" role="tabpanel">
                        <div class="row scollable-listings" style="max-height:90vh;overflow-y:auto;">
                          @foreach($availableRoomTypes as $type)
                          @php
                          // Get one example room of this type
                          $room = $availableRooms->where('room_type_id', $type->id)->first();
                          // if we have package we have to only show rooms that are part of the package
                          if ($package) {
                            // does this make sense, because we have packages relation with room?
                            $room->setRelation('package', $package);
                          }
                          // Count available rooms of this type
                          $availableCount = $availableRooms->where('room_type_id', $type->id)->count();

                          $room_name = $room->name;
                          $room_type_name = $room->type->name;
                          $room_web_description = $room->web_description;
                          $room_code = $room->code;
                          $room_image = preg_match('/^https?:\/\//', $room->web_image) ? $room->web_image : asset('storage/' . $room->web_image);
                          $room_image = $room_image ? $room_image : asset('assets/images/image_not_available.png');
                          $on_error_image = asset('assets/images/image_not_available.png');
                          $room_description = $room->description;
                          if ($checkinDate == $checkoutDate) {
                            $checkoutDate = \Carbon\Carbon::parse($checkoutDate)->addDay();
                          }

                          $guest_count = 1;
                          $sharedGuestCount = 2;
                          if ($isShared) {
                          $guest_count = 2;
                          }

                          $sharedRoomRate = $room->type->getRangeRates(true, $checkinDate, $checkoutDate)->first();
                          $roomRate = $room->type->getRangeRates(false, $checkinDate, $checkoutDate)->first();

                          $rateBasis = $roomRate->conditions['is_per_night'] ?? false ? 'per night' : 'per person';
                          $dailyRate = $roomRate->amount;
                          $sharedDailyRate = $sharedRoomRate ? $sharedRoomRate->amount : 0;
                          if ($rateBasis == 'per person') {
                            $dailyRate = $dailyRate * max(1, $guest_count);
                            $sharedDailyRate = $sharedDailyRate * max(1, $sharedGuestCount);
                          }

                          $room_rate_total = $dailyRate;
                          // $shared_room_rate_total = $sharedRoomRate ? $sharedRoomRate->amount * max(1, $guests) : 0;
                          $room_price_display = number_format($room_rate_total, 2);
                          $room_price_shared_display = number_format(($sharedRoomRate->amount*2) ?? 0, 2);
                          $room_thumbnail = $room_image;
                          $room_location = $room->property->name ?? 'N/A';
                          $room_amenities = $room->type->amenities_with_details; // icons are bi bi-*
                          $first_amenity = $room_amenities->last();
                          $amenities_count = $room->type->amenities_count;
                          $is_available = $room->isAvailable($checkinDate, $checkoutDate);

                          $maxGuests = $sharedGuestCount;
                          @endphp
                          
                          <!-- Listing Block -->
                          <div class="listing-block col-lg-6 col-md-6 col-sm-12">
                            <label class="inner-box">
                              <input type="radio"
                                name="room_id"
                                value="{{ $room->id }}"
                                class="room-radio"
                                data-typeid="{{ $room->room_type_id }}"
                                data-type="{{ $room_type_name }}"
                                data-rate="{{ $dailyRate }}"
                                data-shared-rate="{{ $sharedDailyRate }}"
                                data-max="{{ $maxGuests }}"
                                data-roomname="{{ $room_name }}"
                                style="position:absolute;top:10px;left:10px;z-index:2;"
                                {{ old('room_id') == $room->id ? 'checked' : '' }}
                                {{ !$is_available ? 'disabled' : '' }}
                              >
                              <div class="inner-box {{ !$is_available ? 'bg-light text-muted' : '' }}" style="padding-left:30px;">
                                <figure class="image"><img src="{{ $room_thumbnail }}" alt=""></figure>
                                <div class="tags">
                                  @if($room->is_featured)
                                    <span>Featured</span>
                                  @endif
                                  <span class="display-price-shared">{{ $currencySymbol }}{{ $room_price_shared_display }} (/night)</span>
                                  <span class="display-price">{{ $currencySymbol }}{{ $room_price_display }} (/night)</span>
                                </div>
                                {{-- <a href="#" class="like-btn"><span class="flaticon-heart"></span> Save</a> --}}
                              </div>
                              <div class="lower-content">
                                {{-- <div class="user-thumb"><img src="images/resource/user-thumb-1.jpg" alt="" /></div> --}}
                                <div class="rating">
                                  @if($room->average_rating && $room->published_feedback_count > 0)
                                    @for($i = 1; $i <= 5; $i++)
                                      @if($i <= floor($room->average_rating))
                                        <span class="fa fa-star"></span>
                                      @elseif($i - $room->average_rating < 1)
                                        <span class="fa fa-star-half-o"></span>
                                      @else
                                        <span class="fa fa-star"></span>
                                      @endif
                                    @endfor
                                  <span class="title">({{ $room->published_feedback_count }})</span>
                                  @else
                                  <span class="far fa-star"></span>
                                  <span class="far fa-star"></span>
                                  <span class="far fa-star"></span>
                                  <span class="far fa-star"></span>
                                  <span class="far fa-star"></span>
                                  @endif
                                </div>
                                <h3><a href="#">{{ $room_name }} <span class="icon icon-verified"></span></a></h3>
                                <div class="text">{{ $room_web_description }}</div>
                                <ul class="info">
                                  <li><span class="flaticon-pin"></span> {{ $room_location }}</li>
                                  {{-- This will automatically select the first available room of this type, and go to next step --}}
                                  <li><button type="button" class="theme-btn small bg-brook-blue btn-style-one select-room" data-room-id="{{ $room->id }}">Book Now</button></li>
                                </ul>
                              </div>
                              <div class="bottom-box">
                                <div class="places"> 
                                  <div class="place"><i class="icon {{ $first_amenity->icon }}"></i> {{ $first_amenity->name ?? 'N/A' }} </div>
                                  <span class="count">+{{ $amenities_count - 1 }}</span>
                                </div>
                                <div class="status"><span class="{{ $is_available ? 'text-success' : 'text-danger' }}">{{ $is_available ? $availableCount . ' Available' : 'Not Available' }}</span></div>
                              </div>
                            </label>
                          </div>
                          @endforeach
                        </div>
                        {{-- @if($availableRooms->hasPages())
                        <!-- Pagination -->
                        <div class="container-fluid py-3 justify-content-center">
                          <div class="row align-items-center">
                            <div class="col-md-12 float-end">
                              {{ $availableRooms->links('vendor.pagination.bootstrap-4') }}
                            </div>
                          </div>
                        </div>
                        @endif --}}
                        
                        <div class="form-row mt-3">
                          
                          <input type="number" id="count_guests" name="count_guests"  value="{{ $guest_count }}">
                          <div class="form-group col-md-4">
                            <label for="daily_rate">Daily Rate</label>
                            <input type="number" class="form-control" id="daily_rate" name="daily_rate" min="0" step="0.01" required readonly>
                          </div>
                          <div class="form-group col-md-4">
                            <label for="total_amount">Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" readonly>
                          </div>
                        </div>
                        <div class="">
                          <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary me-2" id="backToStep1">Back</button>
                            <a type="button" class="theme-btn btn-style-one bg-brook-blue float-right" id="toStep3">Next <span class="icon flaticon-right"></span></a>
                          </div>
                        </div>
                      </div>
                      {{-- ... next step markup ... --}}
                      {{-- <div class="tab-pane fade" id="step2" role="tabpanel">
                        <div class="form-row">
                          <div class="form-group col-md-8">
                            <label for="room_id">Room</label>
                            <select class="form-control" id="room_id" name="room_id" required>
                              <option value="">Select Room</option>
                              @foreach($availableRooms as $room)
                              <option value="{{ $room->id }}"
                                data-type="{{ $room->type->name }}"
                                data-rate="{{ $room->type->rates->where('is_shared', false)->first()?->amount ?? 0 }}"
                                data-shared-rate="{{ $room->type->rates->where('is_shared', true)->first()?->amount ?? 0 }}"
                                data-max="{{ $room->type->max_capacity }}">
                                Room {{ $room->number }} - {{ $room->type->name }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="daily_rate">Daily Rate</label>
                            <input type="number" class="form-control" id="daily_rate" name="daily_rate" min="0" step="0.01" required readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="total_amount">Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" readonly>
                          </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="backToStep1">Back</button>
                        <button type="button" class="btn btn-primary float-right" id="toStep3">Next</button>
                      </div> --}}
                      <!-- Step 3: Guest Details -->
                      <div class="tab-pane fade" id="step3" role="tabpanel">
                        <div id="guestFields" class="mb-3">
                          <!-- Primary guest fields -->
                          <div class="form-row">
                            <div class="form-group col-md-6">
                              <label for="guest_fname_1">First Name <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="guest_fname_1" name="guests[0][fname]" required>
                            </div>
                            <div class="form-group col-md-6">
                              <label for="guest_lname_1">Last Name <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="guest_lname_1" name="guests[0][lname]" required>
                            </div>
                          </div>
                          <div class="form-row">
                            <div class="form-group col-md-6">
                              <label for="guest_phone_1">Phone Number <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="guest_phone_1" name="guests[0][phone]" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                              <label for="guest_email_1">Email <span class="text-danger">*</span></label>
                              <input type="email" class="form-control" id="guest_email_1" name="guests[0][email]" required>
                            </div>
                          </div>
                          <div class="form-row">
                            <div class="form-group col-md-6">
                              <label for="guest_idno_1">ID/Passport Number <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" id="guest_idno_1" name="guests[0][idno]" required>
                            </div>
                            <div class="form-group col-md-6">
                              <label for="guest_gown_1">Gown Size</label>
                              <select name="guests[0][gown_size]" id="guest_gown_1" class="form-control @error('guests.0.gown_size') is-invalid @enderror">
                                <option value="">Select Size</option>
                                <option value="XS" {{ old('guests.0.gown_size') === 'XS' ? 'selected' : '' }}>Extra Small (XS)</option>
                                <option value="S" {{ old('guests.0.gown_size') === 'S' ? 'selected' : '' }}>Small (S)</option>
                                <option value="M" {{ old('guests.0.gown_size') === 'M' ? 'selected' : '' }}>Medium (M)</option>
                                <option value="L" {{ old('guests.0.gown_size') === 'L' ? 'selected' : '' }}>Large (L)</option>
                                <option value="XL" {{ old('guests.0.gown_size') === 'XL' ? 'selected' : '' }}>Extra Large (XL)</option>
                                <option value="XXL" {{ old('guests.0.gown_size') === 'XXL' ? 'selected' : '' }}>Double XL (XXL)</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-row">
                            <div class="form-group col-md-12">
                              <label for="special_requests_1">Any Dietary Requirements or Food Allergies?</label>
                              <textarea class="form-control" id="special_requests_1" name="guests[0][special_requests]"></textarea>
                            </div>
                          </div>
                          <!-- Placeholder for additional guests, injected dynamically -->
                          <div id="additionalGuestFields"></div>
                          <button type="button" class="btn btn-outline-info btn-sm" id="addGuestBtn">Add Another Guest</button>
                        </div>
                        <button type="button" class="btn btn-secondary" id="backToStep2">Back</button>
                        <a type="button" class="theme-btn btn-style-one bg-brook-blue float-right" id="toStep4">Next <span class="icon flaticon-right"></span></a>
                      </div>
                      <!-- Step 4: Confirm and Submit -->
                      <div class="tab-pane fade" id="step4" role="tabpanel">
                        <h5>Review & Confirm</h5>
                        <div class="alert alert-info">
                          Please review your booking details before submitting.
                        </div>
                        <div id="confirmationSummary" class="mb-3"></div>
                        <div hidden class="form-group">
                          <label for="source">Booking Source</label>
                          <select class="form-control" id="source" name="source">
                            <option value="website" selected>Website</option>
                            <option value="walk_in">Walk In</option>
                            <option value="phone">Phone</option>
                            <option value="agent">Agent</option>
                          </select>
                        </div>
                        <button type="button" class="theme-btn btn-style-one small bg-gray" id="backToStep3"><span class="icon flaticon-left-arrow"></span> Back</button>
                        <button type="submit"  class="theme-btn btn-style-one bg-brook-blue small float-right">Submit Booking <span class="icon flaticon-plus-symbol"></span></button>
                      </div>
                    </div>
                  </form>
                </div>
              {{-- </div> --}}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Listing Section -->
</section>
  @push('scripts')
  {{-- Calendar Modal --}}
  <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
    <div class="modal-dialog calendar-modal">
      <div class="modal-content">
        {{-- <div class="modal-header">
          <h5 class="modal-title" id="calendarModalLabel">
            <i class="fas fa-calendar-alt"></i>&nbsp;Select Check-In Date
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div> --}}
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            @if ($package)
            <strong>Package Notice: </strong>This booking is for the <em>{{ $package->pkg_name }}</em> package which requires a limited stay of {{ $packageNights }} nights. Allowed check-in days are
            @foreach($allowedDays as $day)
            <span class="badge bg-secondary text-white">{{ $day }}</span>@if(!$loop->last) @endif
            @endforeach
            .
            @else
            <strong>Property Notice: </strong>This property lets you check-in on 
            @foreach($allowedDays as $day)
            <span class="badge bg-secondary text-white">{{ $day }}</span>@if(!$loop->last) @endif
            @endforeach
            .
            @endif
          </div>
          <div id="calendar" class="calendar"></div>
          {{-- <hr> --}}
          <div class="booking-range-preview mt-3 text-center">
            <p class="mb-0">Selected dates will appear here</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="resetCalendarSelection">
            <i class="fas fa-undo"></i> Reset
          </button>
          <button type="button" class="btn btn-success" id="confirmCalendarSelection" data-bs-dismiss="modal">
            <i class="fas fa-check"></i> Done
          </button>
        </div>
      </div>
    </div>
  </div>
  
  @if ($package)
  {{-- Calendar JS (for booking form) --}}
  <script src="{{ asset('assets/js/calendar.js') }}"></script>
  
  @else
  {{-- Calendar JS (for booking form) --}}
  <script src="{{ asset('assets/js/calendar_dynamic.js') }}"></script>
  @endif
  <script>
    $(function() {
      // default price display
      $('.display-price-shared').hide();
      $('.display-price').show();
      let isShared = false;
      let maxGuests = 1;
      let countGuests = parseInt($('#count_guests').val()) || 1;
      // $('#is_shared').change(function() {
      //   let roomOpt = $('.room-radio:checked'); // this is now a radio button(handled below, remove here)
      //   let isShared = $('#is_shared').val() == '1';
      //   let rate = isShared ? roomOpt.data('shared-rate') : roomOpt.data('rate');
      //   maxGuests = isShared ? roomOpt.data('max') : 1;
        
      //   console.log('isShared:', isShared, 'rate:', rate, 'maxGuests:', maxGuests, 'countGuests:', countGuests);
      //   $('#daily_rate').val(rate);
      //   let nights = getNights();
      //   $('#total_amount').val(rate * nights * countGuests);
      //   updateSummary();
      //   if ($('#additionalGuestFields').children().length > (maxGuests-1)) {
      //     $('#additionalGuestFields').empty();
      //   }
      // });
      // toggle price display based on is_shared
      $('#is_shared').change(function() {
        isShared = $(this).val() == '1';
        if (isShared) {
          $('.display-price').hide();
          $('.display-price-shared').show();
          $('#count_guests').val('{{ $sharedGuestCount }}');
        } else {
          $('.display-price-shared').hide();
          $('.display-price').show();
          $('#count_guests').val('1');
        }
        // trigger room change to update rates
        $('.room-radio:checked').trigger('change');
      }).trigger('change');

      $('#pickDates').click(function() {
        $('#calendarModal').modal('show');
        // initCalendar(allowedWeekdays, packageNights);
      });

      // make sure departure_date is at least arrival_date + number_of_nights
      $('#arrival_date').change(function() {
        let arrival = $(this).val();
        let nights = parseInt($('#number_of_nights').val()) || packageNights;
        if (arrival) {
          let minDeparture = new Date(arrival);
          minDeparture.setDate(minDeparture.getDate() + nights);
          let minDepartureStr = minDeparture.toISOString().split('T')[0];
          $('#departure_date').attr('min', minDepartureStr);
          if ($('#departure_date').val() < minDepartureStr) {
            $('#departure_date').val(minDepartureStr);
          }
        }
      });
    });
  </script>
  {{-- Stepper JS --}}
  <script>
    $(function() {
      let currentStep = 1;
      let maxGuests = 1; // Will be set when room selected
      let countGuests = parseInt($('#count_guests').val()) || 1;
      // dynamic summary buttons
      function updateSummaryButtons() {
        $('#prevStep').toggle(currentStep > 1);
        if (currentStep < 4) {
          $('#nextStep').html('Next <span class="icon flaticon-right"></span>');
        } else {
          $('#nextStep').text('Submit');
        }
      }
      updateSummaryButtons();
      $('#prevStep').click(function() {
        if (currentStep > 1) {
          showStep(currentStep - 1);
          updateSummaryButtons();
        }
      });
      $('#nextStep').click(function() {
        if (currentStep < 4) {
          if (!validateStep(currentStep)) {
            return;
          }
          showStep(currentStep + 1);
          updateSummaryButtons();
        } else {
          // submit the form
          $('#bookingForm').submit();
        }
      });
      $('.select-room').click(function() {
        let roomId = $(this).data('room-id');
        $(`.room-radio[value="${roomId}"]`).prop('checked', true).trigger('change');
        validateStep(2);
        updateSummary();
        // move to next step
        $('#toStep3').trigger('click');
      });
      // validate steps before moving forward
      function validateStep(step) {
        if (step === 1) {
          let arrival = $('#arrival_date').val();
          let departure = $('#departure_date').val();
          let nights = parseInt($('#number_of_nights').val()) || packageNights;
          if (!arrival || !departure) {
            alert('Please select both arrival and departure dates.');
            return;
          }
          let arrivalDate = new Date(arrival);
          let departureDate = new Date(departure);
          let diffTime = Math.abs(departureDate - arrivalDate);
          let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
          if (diffDays < nights) {
            alert(`Please ensure your stay is at least ${nights} nights.`);
            return;
          }
          // all good
          // return true;
        } else if (step === 2) {
          if (!$('.room-radio:checked').length) {
            alert('Please select a room to proceed.');
            return false;
          }
        } else if (step === 3) {
          // validate guest details
          let valid = true;
          $('#guestFields input[required], #guestFields textarea[required]').each(function() {
            if (!$(this).val()) {
              valid = false;
              return false; // break loop
            }
          });
          // if shared make sure at least 2 guest details are filled
          if (isShared && countGuests > 1) {
            let filledGuests = 0;
            for (let i = 1; i <= countGuests; i++) {
              let fname = $(`#guest_fname_${i}`).val();
              let lname = $(`#guest_lname_${i}`).val();
              let phone = $(`#guest_phone_${i}`).val();
              let email = $(`#guest_email_${i}`).val();
              let idno = $(`#guest_idno_${i}`).val();
              if (fname && lname && phone && email && idno) {
                filledGuests++;
              }
            }
            if (filledGuests < 2) {
              valid = false;
            }
          }

          if (!valid) {
            alert('Please fill in all required guest details to proceed.');
            return false;
          }
          
        }
        return true;
      }
      function showStep(step) {
        $('#bookingStepper .nav-link').removeClass('active');
        $('#bookingStepper .tab-pane').removeClass('show active');
        $('#bookingStepper .nav-link').eq(step-1).addClass('active');
        $('#bookingStepper .tab-pane').eq(step-1).addClass('show active');
        currentStep = step;
        // window.location.hash = `#step${step}`;
        // scroll to top of form
        $('html, body').animate({ scrollTop: $('#listing-section').offset().top - 20 }, 500);
        // updateSummaryButtons();
      }
      // function redirectToStep(step) {
      //   showStep(step);
      //   // update url hash
      // }
      // $('#toStep2').click(function() { showStep(2); updateSummary(); });
      // validate step 1 before moving to step 2
      $('#toStep2, #toStep3, #toStep4').click(function() {
        let arrival = $('#arrival_date').val();
        let departure = $('#departure_date').val();
        let nights = parseInt($('#number_of_nights').val()) || packageNights;
        if (!arrival || !departure) {
          alert('Please select both arrival and departure dates.');
          return;
        }
        let arrivalDate = new Date(arrival);
        let departureDate = new Date(departure);
        let diffTime = Math.abs(departureDate - arrivalDate);
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays < nights) {
          alert(`Please ensure your stay is at least ${nights} nights.`);
          return;
        }
        showStep(2);
        updateSummary();
      });
      $('#toStep3').click(function() {
        // validate room radio selection
        if (!$('.room-radio:checked').length) {
          alert('Please select a room to proceed.');
          return;
        }
        // update summary and go to step 3
        updateSummary();
        showStep(3);
      });
      $('#toStep4').click(function() {
        validateStep(3);
        showStep(4); updateSummary(); showConfirmation();
      });
      $('#backToStep1').click(function() { showStep(1); });
      $('#backToStep2').click(function() { showStep(2); });
      $('#backToStep3').click(function() { showStep(3); });

      // if navigating nav-link item clicked directly , validate and update summary
      $('#step1-tab').click(function() {
        showStep(1);
        // showStep(1);
      });
      $('#step2-tab').click(function() {
        if (validateStep(1)) {
          updateSummary();
          showStep(2);
        } else {
          showStep(1);
        }
      });
      $('#step3-tab').click(function() {
        if (validateStep(1) && validateStep(2)) {
          updateSummary();
          showStep(3);
        } else if (!validateStep(1)) {
          showStep(1);
        } else {
          showStep(2);
        }
      });
      $('#step4-tab').click(function() {
        if (validateStep(1) && validateStep(2) && validateStep(3)) {
          updateSummary();
          showStep(4);
          showConfirmation();
        } else if (!validateStep(1)) {
          showStep(1);
        } else if (!validateStep(2)) {
          showStep(2);
        } else {
          showStep(3);
        }
      });

      // When a room is selected
      $('.room-radio').change(function() {
        isShared = $('#is_shared').val() == '1';
        let minSharedGuests = isShared ? 2 : 1;
        let rate = isShared ? $(this).data('shared-rate') : $(this).data('rate');
        var $radio = $(this);
        maxGuests = isShared ? '{{ $sharedGuestCount }}' : $radio.data('max');
        $('#daily_rate').val(rate);
        let nights = getNights();
        $('#total_amount').val(rate * nights * countGuests);
        // Optionally update summary, guest capacity, etc.
        updateSummary();
      });
      $('#addGuestBtn').click(function() {
        let count = $('#additionalGuestFields').children().length + 2;
        console.log('Adding guest field for guest number:', count, 'maxGuests:', maxGuests);
        if (count <= maxGuests) {
          $('#count_guests').val(count);
          let field = `
            <div class="border p-2 mb-2">
              <h6>Guest ${count}</h6>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="guest_fname_${count}">First Name</label>
                  <input type="text" class="form-control" id="guest_fname_${count}" name="guests[${count-1}][fname]" required>
                </div>
                <div class="form-group col-md-6">
                  <label for="guest_lname_${count}">Last Name</label>
                  <input type="text" class="form-control" id="guest_lname_${count}" name="guests[${count-1}][lname]" required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="guest_idno_${count}">ID/Passport Number</label>
                  <input type="text" class="form-control" id="guest_idno_${count}" name="guests[${count-1}][idno]" required>
                </div>
                <div class="form-group col-md-6">
                  <label for="guest_gown_${count}">Gown Size</label>
                  <select name="guests[${count-1}][gown_size]" id="guest_gown_${count}" class="form-control">
                    <option value="">Select Size</option>
                    <option value="XS">Extra Small (XS)</option>
                    <option value="S">Small (S)</option>
                    <option value="M">Medium (M)</option>
                    <option value="L">Large (L)</option>
                    <option value="XL">Extra Large (XL)</option>
                    <option value="XXL">Double XL (XXL)</option>
                  </select>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="guest_phone_${count}">Phone</label>
                  <input type="text" class="form-control" id="guest_phone_${count}" name="guests[${count-1}][phone]">
                </div>
                <div class="form-group col-md-6">
                  <label for="guest_email_${count}">Email</label>
                  <input type="email" class="form-control" id="guest_email_${count}" name="guests[${count-1}][email]" required>
                </div>
              </div>
              <div class="form-row">
                  <div class="form-group col-md-12">
                    <label for="special_requests_${count}">Any Dietary Requirements or Food Allergies?</label>
                    <textarea class="form-control" id="special_requests_${count}" name="guests[${count-1}][special_requests]"></textarea>
                  </div>
              </div>
            </div>`;
          $('#additionalGuestFields').append(field);
        }
      });
      $('#arrival_date, #departure_date').change(function() {
        updateSummary();
        let arrival = $('#arrival_date').val();
        $('#departure_date').attr('min', arrival);
      });
      function formatDisplayDate(dateStr) {
        let options = { year: 'numeric', month: 'short', day: 'numeric' };
        let dateObj = new Date(dateStr);
        return dateObj.toLocaleDateString(undefined, options);
      }
      function updateSummary() {
        // must format dates nicely to display here
        let arrival = formatDisplayDate($('#arrival_date').val());
        let departure = formatDisplayDate($('#departure_date').val());
        $('#summaryDates').text(arrival + ' to ' + departure);
        let roomText = $('.room-radio:checked').data('roomname'); // get from selected radio
        $('#summaryRoom').text(roomText);
        // guests each first and last name
        let guestFNames = $('#guestFields input[type="text"][id^="guest_fname_"]').map(function(){ return $(this).val(); }).get().join(', ');
        let guestLNames = $('#guestFields input[type="text"][id^="guest_lname_"]').map(function(){ return $(this).val(); }).get().join(', ');
        // each full name
        let guestNames = [];
        $('#guestFields input[type="text"][id^="guest_fname_"]').each(function(index){
          let fname = $(this).val();
          let lname = $('#guest_lname_' + (index + 1)).val();
          guestNames.push(fname + ' ' + lname);
        });
        $('#summaryGuests').text(guestNames.join(', ') || 'Not entered');
        $('#summaryRate').text($('#daily_rate').val());
        $('#summaryTotal').text($('#total_amount').val());
        let reqs = $('#guestFields textarea[id^="special_requests_"]').map(function(){ return $(this).val(); }).get().join('; ');
        $('#summaryRequests').text(reqs);
      }
      function showConfirmation() {
        @if ($package)
        let html = `
          <ul class="list-group">
            <li class="list-group-item active">Package: <span id="summaryDates">{{ $package->pkg_name }}</span></li>
            <li class="list-group-item"><strong>Date Range:</strong> ${$('#summaryDates').text()} (${getNights()} nights)</li>
            <li class="list-group-item"><strong>Room:</strong> ${$('#summaryRoom').text()}</li>
            <li class="list-group-item"><strong>Guests:</strong> ${$('#summaryGuests').text()}</li>
            <li class="list-group-item"><strong>Rate:</strong> {{ $currencySymbol }} ${$('#summaryRate').text()}</li>
            <li class="list-group-item"><strong>Total:</strong> {{ $currencySymbol }} ${$('#summaryTotal').text()}</li>
            <li class="list-group-item"><strong>Dietary Requirements or Food Allergies:</strong> ${$('#summaryRequests').text()}</li>
          </ul>`;
        $('#confirmationSummary').html(html);
        @else
        let html = `
          <ul class="list-group">
            <li class="list-group-item"><strong>Date Range:</strong> ${$('#summaryDates').text()} (${getNights()} nights)</li>
            <li class="list-group-item"><strong>Room:</strong> ${$('#summaryRoom').text()}</li>
            <li class="list-group-item"><strong>Guests:</strong> ${$('#summaryGuests').text()}</li>
            <li class="list-group-item"><strong>Rate:</strong> {{ $currencySymbol }} ${$('#summaryRate').text()}</li>
            <li class="list-group-item"><strong>Total:</strong> {{ $currencySymbol }} ${$('#summaryTotal').text()}</li>
            <li class="list-group-item"><strong>Requests:</strong> ${$('#summaryRequests').text()}</li>
          </ul>`;
        $('#confirmationSummary').html(html);
        @endif
      }
      function getNights() {
        let a = $('#arrival_date').val();
        let d = $('#departure_date').val();
        if(a && d) {
          let start = new Date(a);
          let end = new Date(d);
          let diff = (end - start)/(1000*60*60*24);
          return diff > 0 ? diff : 1;
        }
        return 1;
      }
      showStep(1);
    });
  </script>
  @endpush
@endsection