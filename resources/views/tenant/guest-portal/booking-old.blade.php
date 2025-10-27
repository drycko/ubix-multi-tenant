@extends('tenant.layouts.guest')

@section('title', 'Book a Room')

@section('content')
<!-- Listing Section / Sytle Two-->
<section class="ls-section style-two">
  <div class="auto-container">
    <div class="filters-backdrop"></div>
    
    <div class="row">

      <!-- Filters Column -->
      <div class="filters-column col-lg-4 col-md-12 col-sm-12">
        <div class="inner-column">
          <button type="button" class="theme-btn close-filters">X</button>
          <div class="default-tabs style-two tabs-box">
            <!--Tabs Box-->
            <ul class="tab-buttons clearfix">
              <li class="tab-btn active-btn" data-tab="#tabFilters">Filters</li>
              <li class="tab-btn" data-tab="#tabSummary">Booking Summary</li>
            </ul>
            <!-- Top Filters -->
            {{-- <ul class="top-filters">
              <li class="active"><a href="javascript:void(0);" id="filters-tab">Filters</a></li>
              <li><a href="javascript:void(0);" id="summary-tab">Summary</a></li>
            </ul> --}}
            <div class="tabs-content p-0 pt-3">
              <!--Tab-->
              {{-- <div class="tab active-tab" id="tabFilters"> --}}
                <!-- Listing Form -->
                <div class="tab active-tab listing-search-form" id="tabFilters">
                  <form action="{{ route('tenant.guest-portal.booking.search') }}" method="GET">
                    <div class="row">

                      <!-- Form Group -->
                      <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <select name="room_type" id="room_type" class="chosen-select">
                          <option value="">All Room Types</option>
                          @foreach($availableRoomTypes as $roomType)
                            <option value="{{ $roomType->id }}" {{ old('room_type', $selectedRoomType) == $roomType->id ? 'selected' : '' }}>{{ $roomType->name }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <input type="date"
                          min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                          name="checkin_date" id="checkin_date"
                          value="{{ old('checkin_date', $checkinDate) }}"
                          placeholder="Check-in Date">
                        <span class="icon flaticon-calendar" data-text="Check-in Date"></span>
                      </div>
                      <!-- Form Group -->
                      <div class="form-group col-lg-12 col-md-12 col-sm-12 location">
                        <input type="date"
                          min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                          name="checkout_date" id="checkout_date"
                          value="{{ old('checkout_date', $checkoutDate) }}"
                          placeholder="Check-out Date">
                        <span class="icon flaticon-calendar" data-text="Check-out Date"></span>
                      </div>

                      <!-- Form Group -->
                      {{-- <div class="form-group col-lg-12 col-md-12 col-sm-12">
                        <select class="chosen-select">
                          <option>Price</option>
                          <option>Residential</option>
                          <option>Commercial</option>
                          <option>Industrial</option>
                          <option>Apartments</option>
                        </select>
                      </div> --}}
                    </div>

                    
                    <!-- Switchbox Outer -->
                    <div class="switchbox-outer">

                      <ul class="switchbox">
                        <li>
                          <label class="switch">
                            <input type="checkbox" name="is_shared" id="is_shared" @if(old('is_shared', $isShared)) checked @endif>
                            <span class="slider round"></span>
                            <span class="title">With Company</span>
                          </label>
                        </li>
                        {{-- <li>
                          <label class="switch">
                            <input type="checkbox">
                            <span class="slider round"></span>
                            <span class="title">Near Me</span>
                          </label>
                        </li> --}}
                      </ul>
                    </div>

                    <!-- Checkboxes Ouer -->
                    <div class="checkbox-outer">
                      <h4>Amenities</h4>
                      <ul class="checkboxes two-column scrollable-content" style="max-height: 150px; overflow-y: auto;">
                        @foreach($roomAmenities as $amenity)
                        <li>
                          <input type="checkbox" id="amenity-{{ $amenity->id }}" name="amenities[]"
                          @if(in_array($amenity->slug, old('amenities', []))) checked @endif
                          value="{{ $amenity->slug }}">
                          <label for="amenity-{{ $amenity->id }}">{{ $amenity->name }}</label>
                        </li>
                        @endforeach
                      </ul>
                    </div>

                    <div class="form-group text-center">
                      <button type="submit" class="theme-btn bg-green btn-style-one">Show Results</button>
                    </div>
                  </form>
                </div>
                <!-- End Listing Form -->
              {{-- </div> --}}
              <!--Tab-->
              <div class="tab" id="tabSummary">
                <!-- Booking summary -->
                <div class="listing-search-form" id="summary">
                  <div class="summary-box">
                    {{-- <h4>Booking Summary</h4> --}}
                    <ul class="summary-list">
                      <li class="border-bottom">
                        <div class="sec-title text-center mb-1 mt-2">
                          <h4>Date Range</h4>
                          <span class="divider"></span>
                          <div class="text">{{ \Carbon\Carbon::parse($checkinDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($checkoutDate)->format('M d, Y') }}</div>
                        </div>
                        {{-- <span class="icon flaticon-time"></span><strong class="text-green">Check-in Date:</strong> <span>{{ \Carbon\Carbon::parse($checkinDate)->format('M d, Y') }}</span> --}}
                      </li>
                      <li class="border-bottom">
                        <div class="sec-title text-center mb-1 mt-2">
                          <h4>Number of Nights</h4>
                          <span class="divider"></span>
                          <div class="text">{{ \Carbon\Carbon::parse($checkinDate)->diffInDays(\Carbon\Carbon::parse($checkoutDate)) }} Night(s)</div>
                        </div>
                      </li>
                      <li class="border-bottom">
                        <div class="sec-title text-center mb-1 mt-2">
                          <h4>Room Type</h4>
                          <span class="divider"></span>
                          <div class="text">{{ $selectedRoomTypeName ?? 'All' }}</div>
                        </div>
                      </li>
                      <li class="border-bottom">
                        <div class="sec-title text-center mb-1 mt-2">
                          <h4>Sharing Option</h4>
                          <span class="divider"></span>
                          <div class="text">{{ $isShared ? 'With Company' : 'Private' }}</div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- End Booking summary -->
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Content Column -->
      <div class="content-column col-lg-8 col-md-12 col-sm-12">
        <div class="ls-outer">
          <button type="button" class="theme-btn btn-style-two toggle-filters">Show Filters</button>

          <!-- ls Switcher -->
          <div class="ls-switcher">
            <div class="showing-result">
              <div class="arrange">
                <a href="#" class="active"><span class="icon flaticon-squares"></span></a>
                <a href="#"><span class="icon flaticon-setup"></span></a>
              </div>
              <div class="text">{{ $availableRooms->count() }} Results Found (Showing {{ $availableRooms->firstItem() }}-{{ $availableRooms->lastItem() }})</div>
            </div>
            <div class="sort-by">
              <select class="chosen-select">
                <option>Sort By</option>
                <option>Room Type</option>
              </select>
            </div>
          </div>
          <!-- Listings -->
          <div class="row">
            @foreach($availableRooms as $room)
            @php
              $room_name = $room->name;
              $room_type_name = $room->type->name;
              $room_web_description = $room->web_description;
              $room_code = $room->code;
              // if image has http or https, use it directly
              $room_image = preg_match('/^https?:\/\//', $room->web_image) ? $room->web_image : asset('storage/' . $room->web_image);
              // if no image, use default
              $room_image = $room_image ? $room_image : asset('assets/images/image_not_available.png');

              $on_error_image = asset('assets/images/image_not_available.png');
              
              $room_description = $room->description;

              // if checkout is the same as checkin date, set checkout date to next day
              if ($checkinDate == $checkoutDate) {
                  $checkoutDate = \Carbon\Carbon::parse($checkoutDate)->addDay();
              }

              if ($isShared) {
                $guests = $room->type->max_capacity;
                $roomRate = $room->type->getRangeRates(true, $checkinDate, $checkoutDate)->first();
              } else {
                $guests = 1;
                $roomRate = $room->type->getRangeRates(false, $checkinDate, $checkoutDate)->first();
              }

              $rateBasis = $roomRate->conditions['is_per_night'] ?? false ? 'per night' : 'per person';

              $dailyRate = $roomRate->amount;
              // adjust daily rate based on rate basis
              if ($rateBasis == 'per person') {
                $dailyRate = $dailyRate * max(1, $guests);
              }

              $room_rate_total = $dailyRate;
              $room_price_display = number_format($room_rate_total, 2);
              // generate thumbnail image
              $room_thumbnail = $room_image;
              $room_location = $room->property->name ?? 'N/A';

              $room_amenities = $room->type->amenities_with_details; // icons are bi bi-*
              $first_amenity = $room_amenities->last();
              $amenities_count = $room->type->amenities_count;
              $is_available = $room->isAvailable($checkinDate, $checkoutDate);

              // calculate rate basis display
              

            @endphp
            <!-- Listing Block -->
            <div class="listing-block col-lg-6 col-md-6 col-sm-12">
              <div class="inner-box">
                <div class="image-box">
                  <figure class="image"><img src="{{ $room_thumbnail }}" alt=""></figure>
                  <div class="tags">
                    @if($room->is_featured)
                      <span>Featured</span>
                    @endif
                    <span>{{ $currency }}{{ $room_price_display }} (/night)</span>
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
                    <li><button type="submit" class="theme-btn small bg-green btn-style-one">Book Now</button></li>
                  </ul>
                </div>
                <div class="bottom-box">
                  <div class="places"> 
                    <div class="place"><i class="icon {{ $first_amenity->icon }}"></i> {{ $first_amenity->name ?? 'N/A' }} </div>
                    <span class="count">+{{ $amenities_count - 1 }}</span>
                  </div>
                  <div class="status"><span class="{{ $is_available ? 'text-success' : 'text-danger' }}">{{ $is_available ? 'Available' : 'Not Available' }}</span></div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @if($availableRooms->hasPages())
          <!-- Pagination -->
          <div class="container-fluid py-3 justify-content-center">
            <div class="row align-items-center">
                <div class="col-md-12 float-end">
                    {{ $availableRooms->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
<!--End Listing Page Section -->

@endsection

alright I need us to move on from this screen, I need to have a 4 step booking here. Step1. Date rage with calendar. Step2. Select Room. Step3. Guest Details, (must have an option to add more guests for details based on the $room->type->max_capacity). Step4. Confirm the booking. then button to submit the booking. The left side bar Summary must just track the bookings steps with the choices. I have internal booking form on views/tenant/bookings/create.blade.php to understand the required booking information.
