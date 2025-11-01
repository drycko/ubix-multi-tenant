@extends('tenant.layouts.guest')

@section('title', 'Landing Page')

@section('content')

<!-- Banner Section / Style Five-->
<section class="banner-section style-five">
  <div class="banner-carousel owl-carousel owl-theme">
    <!-- Slide Item -->
    <div class="slide-item" style="background-image: url({{ asset('vendor/guest-listdo/images/banner/6.jpg') }});">
      <div class="slide-content">
        <div class="auto-container">
          <h2>Book Your Stay</h2>
          <div class="text">A perfect getaway awaits you</div>
        </div>
      </div> 
    </div>    
    
    <!-- Slide Item -->
    <div class="slide-item" style="background-image: url({{ asset('vendor/guest-listdo/images/banner/6.jpg') }});">
      <div class="slide-content">
        <div class="auto-container">
          <h2>Book Your Stay</h2>
          <div class="text">A perfect getaway awaits you</div>
        </div>
      </div> 
    </div>  
  </div>
  
  <div class="content-box">
    <div class="auto-container">
      <!-- Listing Search Tabs -->
      <div class="listing-search-tabs style-three tabs-box">
        <ul class="tab-buttons">
          <li class="tab-btn active-btn" data-tab="#tab1">Room Type</li>
          <li class="tab-btn" data-tab="#tab2">Package</li>
        </ul>
        
        <div class="tabs-content">
          <!--Tab-->
          <div class="tab active-tab" id="tab1">
            <div class="listing-search-form">
              <form method="POST" action="{{ route('tenant.guest-portal.index.post') }}">
                @csrf
                <div class="row">
                  <div class="form-group col-lg-4 col-md-6 col-sm-12">
                    <select name="room_type" id="room_type" class="chosen-select">
                      {{-- <option>All Room Types</option> --}}
                      @foreach($availableRoomTypes as $roomType)
                        <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  
                  <!-- Form Group -->
                  <div class="form-group col-lg-3 col-md-6 col-sm-12 date-picker">
                    <input type="date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" name="checkin_date" id="checkin_date" placeholder="Check-in Date">
                    <span class="icon flaticon-calendar" data-text="Select Date"></span>
                  </div>
                  
                  <!-- Form Group -->
                  <div class="form-group col-lg-3 col-md-6 col-sm-12 date-picker">
                    <input type="date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" name="checkout_date" id="checkout_date" placeholder="Checkout Date">
                    <span class="icon flaticon-calendar" data-text="Select Date"></span>
                  </div>
                  
                  <!-- Form Group -->
                  <div class="form-group col-lg-2 col-md-6 col-sm-12 text-right">
                    <button type="submit" class="theme-btn btn-style-one bg-green">Search</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          
          <!--Tab-->
          <div class="tab" id="tab2">
            <div class="listing-search-form">
              <form method="POST" action="{{ route('tenant.guest-portal.index.post') }}">
                @csrf
                <div class="row">
                  <div class="form-group col-lg-4 col-md-6 col-sm-12">
                    <select name="package_id" id="package_id" class="chosen-select">
                      {{-- <option>All Packages</option> --}}
                      @foreach($packages as $package)
                        <option value="{{ $package->id }}">{{ $package->pkg_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  
                  <!-- Form Group -->
                  <div class="form-group col-lg-3 col-md-6 col-sm-12 date-picker">
                    {{-- <input type="date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" name="checkin_date" id="checkin_date" placeholder="Check-in Date">
                    <span class="icon flaticon-calendar" data-text="Select Date"></span> --}}
                  </div>
                  
                  <!-- Form Group -->
                  <div class="form-group col-lg-3 col-md-6 col-sm-12 date-picker">
                    {{-- <input type="date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" name="checkout_date" id="checkout_date" placeholder="Checkout Date">
                    <span class="icon flaticon-calendar" data-text="Select Date"></span> --}}
                  </div>
                  
                  <!-- Form Group -->
                  <div class="form-group col-lg-2 col-md-6 col-sm-12 text-right">
                    <button type="submit" class="theme-btn btn-style-one bg-red">Search</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- End Banner Section-->

<!-- Listing Section Four-->
<section class="listing-section-four">
  <div class="auto-container">
    <div class="sec-title text-center">
      <h2>Our Featured Rooms</h2>
      <span class="divider"></span>
      <div class="text">Explore our featured rooms. You won’t be disappointed.</div>
    </div>

    <div class="carousel-outer">            
      <!-- Three Items Carousel -->
      <div class="three-items-carousel owl-carousel owl-theme default-nav no-dots">
        @foreach($availableRooms as $index => $room)
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
          
          $dailyRate = $room->type->rates->where('is_shared', false)->first()?->amount ?? 'N/A';
          $sharedRate = $room->type->rates->where('is_shared', true)->first()?->amount ?? 'N/A';

          $room_rate_total = $dailyRate;
          $room_price_display = number_format($room_rate_total, 2);
          // generate thumbnail image
          $room_thumbnail = $room_image;
          $room_location = $room->property->name ?? 'N/A';

          $room_amenities = $room->type->amenities_with_details; // icons are bi bi-*

        @endphp
        <!-- Listing lLock Four-->
        <div class="listing-block-four">
          <div class="inner-box">
            <div class="image-box" style="height: 250px">
              <figure class="image" style="height: 250px"><img src="{{ $room_image }}" alt=""></figure>
              <div class="tags">
                <span>Featured</span>
                <span>{{ $currency }} {{ $room_price_display }}</span>
              </div>
              {{-- <a href="#" class="like-btn"><span class="flaticon-heart"></span> Save</a> --}}
              <h3><a href="#">{{ $room_name }}</a></h3>
            </div>
            <div class="features-box">
              {{-- image must fit in user-thumb --}}
              {{-- <div class="user-thumb"><img src="{{ $room_thumbnail }}"  alt="" /></div> --}}
              <ul class="features">
                @foreach($room_amenities as $amenity)
                  <li title="{{ $amenity['name'] }}">
                    <i class="{{ $amenity['icon'] }}"></i>
                    <span>{{ truncate($amenity['name'], 13) }}</span>
                  </li>
                @endforeach
              </ul>
            </div>
            <div class="bottom-box">
              <div class="rating">
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                {{-- <span class="title">(7 review)</span> --}}
              </div>
              <div class="location"> <span class="flaticon-pin"></span> {{ $room_location }} </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
<!-- End Listing Section -->
<!-- Packages Section Two -->
<section class="packages-section style-two">
  <div class="auto-container">
    <div class="sec-title text-center">
      <h2>Find a Room By Package</h2>
      <span class="divider"></span>
      <div class="text">One click away to book your perfect room.</div>
    </div>

    <div class="row justify-content-center">
      @foreach($packages as $index => $package)
      @php
        $package_name = $package->pkg_name;
        $package_sub_title = $package->pkg_sub_title;
        $package_description = $package->pkg_description;
        $image = $package->pkg_image ? asset('storage/' . $package->pkg_image) : asset('images/default-package.jpg');
        $on_error_image = asset('images/default-package.jpg');

        $border_class = ($index % 3 === 1) ? 'package-border-middle' : '';
        // Count the number of rooms in the package padded with leading zeros
        $count_rooms = str_pad($package->rooms->count(), 2, '0', STR_PAD_LEFT);

      @endphp
      <!-- Feature Block Two -->
      <div class="packages-block col-lg-3 col-md-6 col-sm-12">
        <div class="inner-box">
          <figure class="image"><img src="{{ $image }}" alt=""></figure>
          <div class="content">
            <span class="icon-box flaticon-house-1"></span>
            <h5>{{ $package_name }}</h5>
            <span class="locations">{{ $count_rooms }} Rooms</span>
            <a href="{{ route('tenant.guest-portal.package-booking', ['package' => $package->id]) }}" class="overlay-link"></a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
<!-- End Features Section Two -->
@endsection