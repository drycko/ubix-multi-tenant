@extends('tenant.layouts.guest')

@section('title', 'Landing Page')

@section('content')

<!-- Packages Section -->
<section class="listing-package-section mt-4">
  <div class="auto-container">
    <div class="sec-title text-center">
      <h2 class="txt-brook-blue">Choose Your Package</h2>
      <span class="divider"></span>
      <div class="text">Choose the package that best suits your stay period.</div>
    </div>
    
    <div class="row">
      @foreach($packages as $index => $package)
      @php
        $package_name = $package->pkg_name;
        $package_sub_title = $package->pkg_sub_title;
        $package_description = $package->pkg_description;
        $image = $package->pkg_image ? asset('storage/' . $package->pkg_image) : asset('assets/images/image_not_available.png');
        $on_error_image = asset('assets/images/image_not_available.png');

        $border_class = ($index % 3 === 1) ? 'package-border-middle' : '';
        // Count the number of rooms in the package padded with leading zeros
        $count_rooms = str_pad($package->rooms->count(), 2, '0', STR_PAD_LEFT);

      @endphp

      <!-- Package Block -->
      <div class="listing-block col-lg-4 col-md-6 col-sm-12">
        <div class="inner-box package-box" data-package-id="{{ $package->id }}">
          <div class="image-box">
            <figure class="image"><img src="{{ $image }}" alt="" onerror="this.onerror=null;this.src='{{ $on_error_image }}';"></figure>
            <div class="tags">
              {{-- <span>Featured</span> --}}
              {{-- <span>${{ $package->pkg_price_min }} - ${{ $package->pkg_price_max }}</span> --}}
            </div>
            {{-- <a href="#" class="like-btn"><span class="flaticon-heart"></span> Save</a> --}}
          </div>
          <div class="lower-content text-center scrollable">
            {{-- <div class="user-thumb"><img src="{{ $on_error_image }}" alt="" /></div> --}}
            {{-- <div class="rating">
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="fa fa-star"></span>
              <span class="title">(7 review)</span>
            </div> --}}
            <h3 class="text-center text-bold h2 mb-2">{{ strtoupper($package_name) }}</h3>
            <p class="text-center txt-brook-blue mb-3 text-bold border-bottom">{{ $package_sub_title }}</p>
            <div class="text">{!! $package_description !!}</div>
            <ul class="info">
              {{-- <li><span class="flaticon-pin"></span> Santa Monica, CA</li>
              <li><span class="flaticon-phone-call"></span> +61 2 8236 9200 </li> --}}
            </ul>
          </div>
          <div class="bottom-box">
            <button type="button" class="theme-btn small bg-brook-blue btn-style-one select-package btn-block" onclick="window.location.href='{{ route('tenant.guest-portal.booking', ['package' => $package->id]) }}'">Select Package</button>
            {{-- <div class="places"> 
              <div class="place"><span class="icon flaticon-bed"></span> Hotels </div>
              <span class="count">+3</span>
            </div>
            <div class="status">Now Closed</div> --}}
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
<!-- End Listing Section -->
@push('scripts' )
{{-- <script>
  $(function() {
    $('.select-package, .package-box').on('click', function() {
      var packageId = $(this).closest('.package-box').data('package-id');
      console.log('Selected Package ID:', packageId);
      // Redirect to booking page with selected package ID
      var url = "{{ route('tenant.guest-portal.booking', ['package' => ':packageId']) }}";
      url = url.replace(':packageId', packageId);
      window.location.href = url;
    });
  });
</script> --}}
@endpush
@endsection