@extends('tenant.layouts.guest')

@section('title', 'Guest Portal Dashboard')

@section('content')
<style>
  .package-label-text {
    text-align: center!important;
    min-height: 150px; /* Adjust based on your design */
  }
  .outer-room-card {
    border: none !important;
  /*  box-shadow: 0px 0px 0px rgba(0, 0, 0, 0.1);*/
  }
  .package-header {
    font-weight: bold;
    font-size: 20px;
  }

  .outer-room-card:hover {
  /*  transform: scale(0.95);*/
  /*  border: none !important;*/
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  }

  .outer-room-card.selected {
    border: 2px solid #B0CACB;
    background-color: #e6f9fc;
  }
  /* Vertical lines between packages */
  .package-border-middle {
      position: relative;
  }
  /* Selected Room Highlight */
  .room-card.selected {
    border: 2px solid #B0CACB;
    background-color: #e6f9fc;
  }

  /* For medium screens and up (md breakpoint) */
  @media (min-width: 768px) {
    .package-border-middle::before,
    .package-border-middle::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 1px;
        background-color: #dee2e6; /* Bootstrap's default border color */
    }

    .package-border-middle::before {
      left: -1px;
    }

    .package-border-middle::after {
      right: -1px;
    }
  }
</style>
<div class="row mb-4">
  <div class="col-12 text-center">
    <h2 class="fw-bold text-success mb-2">Welcome to the {{ config('app.name') }} Booking</h2>
    <p class="lead">Please select a package to continue.</p>
  </div>
</div>
<div class="row g-4 justify-content-center">
  @foreach($packages as $index => $package)
    @php
      $package_name = $package->pkg_name;
      $package_sub_title = $package->pkg_sub_title;
      $package_description = $package->pkg_description;
      $image = $package->pkg_image ? asset('storage/' . $package->pkg_image) : asset('images/default-package.jpg');
      $on_error_image = asset('images/default-package.jpg');

      $border_class = ($index % 3 === 1) ? 'package-border-middle' : '';
      
    @endphp
  <div class='col-md-4 mb-3 {{ $border_class }}'>
    <div class="card outer-room-card p-0 text-center h-100"> <!-- Added h-100 for equal height -->
      <div class="room-card d-flex flex-column h-100">
        <img src="{{ $image }}" 
              alt="{{ $package_name }}" 
              class="img-fluid rounded mb-2 room-img" 
              style="width: 100%; height: 250px; object-fit: cover;"
              onerror="this.src='{{ $on_error_image }}'"
              data-img="{{ $image }}">

        <input type="number" name="packageNights" value="{{ (int)$package->pkg_number_of_nights }}" class="packageNights regular-text d-none">
        <input type="number" name="packageId" value="{{ (int)$package->pkg_id }}" class="packageId regular-text d-none">

        <div class="p-3 package-label-text flex-grow-1"> <!-- flex-grow-1 for equal height -->
          <p class="package-header text-center">{{ strtoupper($package_name) }}</p>
          <div style="max-height: 40vh; overflow-y: auto;">
            <p class="text-bold text-success">{{ $package_sub_title }}</p>
            <div class="mt-2">{!! $package_description !!}</div>
          </div>
        </div>
        
        <div class="mt-auto p-2"> <!-- mt-auto pushes button to bottom -->
          <input type="radio" id="package_{{ (int)$package->pkg_id }}" 
                  name="packageName" 
                  value="{{ $package_name }}" 
                  class="form-check-input d-none">
          <label for="package_{{ (int)$package->pkg_id }}" class="d-block w-100">
            <a href="{{ route('tenant.guest-portal.booking.packages', $package) }}" class="btn btn-success w-100">SELECT PACKAGE</a>
          </label>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection