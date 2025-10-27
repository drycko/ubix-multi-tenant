@extends('tenant.layouts.guest')@extends('tenant.layouts.guest')



@section('title', 'Book a Room')@section('title', 'Book a Room')



@section('content')@section('content')

<div class="row mb-4"><!-- Listing Section / Sytle Two-->
      
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
