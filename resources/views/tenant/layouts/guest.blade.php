<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }} Guest Portal - @yield('title')</title>

<!-- Stylesheets -->
<link href="{{ asset('vendor/guest-listdo/css/bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/guest-listdo/css/style.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/guest-listdo/css/responsive.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">

<link rel="shortcut icon" href="{{ asset('assets/images/apple-touch-icon.png') }}" type="image/x-icon">
<link rel="icon" href="{{ asset('assets/images/apple-touch-icon.png') }}" type="image/x-icon">

{{-- <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}"/>
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon-16x16.png') }}"> --}}

{{-- custom css --}}
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
<link rel="manifest" href="{{ asset('assets/images/site.webmanifest') }}">
<!-- Responsive -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<!--[if lt IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script><![endif]-->
<!--[if lt IE 9]><script src="js/respond.js"></script><![endif]-->

</head>

<body>

  <div class="page-wrapper">

    <!-- Preloader -->
    <div class="preloader"></div>
    {{-- if title is landing --}}
    @php $pageTitle = View::yieldContent('title'); @endphp
    @if ($pageTitle == 'Landing Page')

    <!-- Main Header-->
    <header class="main-header header-style-four">
    @else
        <!-- Header Span -->
    <span class="header-span"></span>
    
    <!-- Main Header-->
    <header class="main-header header-style-four">
    @endif
      
      <!-- Main box -->
      <div class="main-box">
        <div class="logo-box">
          <div class="logo"><a href="{{ url('/') }}"><img src="{{ asset('assets/images/ubix-logo-small.png') }}" alt="" title=""></a></div>
        </div>

        <!--Nav Box-->
        <div class="nav-outer">
          <nav class="nav main-menu">
            <ul class="navigation" id="navbar">
              <li class="current">
                <a href="{{ url('/') }}">Home</a>
              </li>
              <li><a href="{{ route('tenant.guest-portal.checkin') }}">Check-In/Out</a></li>
              <li><a href="{{ route('tenant.guest-portal.requests') }}">Requests & Feedback</a></li>
              
              <li class="mm-add-listing"><a href="#" class="theme-btn btn-style-three"><span class="flaticon-plus-symbol"></span>Book a Room</a></li>
            </ul>
          </nav>
          <!-- Main Menu End-->

          <div class="outer-box">
            <!-- Add Listing -->
            <a href="#" class="add-listing"> <span class="flaticon-plus-symbol"></span> Book a Room</a>

            {{-- if guest is not authenticated --}}
            @if(!$guest)
            <!-- Login/Register -->
            <div class="login-box">
              <span class="flaticon-user"></span> 
              <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal">Login</a>
            </div>
            @else
            <!-- Dashboard Option -->
            <div class="dropdown dashboard-option">
              <a class="dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="false"> 
                <img src="{{ $guest->profile_photo_url }}" alt="avatar" class="thumb"> 
                <span class="name text-muted">{{ $guest->first_name }} {{ $guest->last_name }}</span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item active" href="dashboard.html"> <i class="la la-home"></i> Dashboard</a>
                <a class="dropdown-item" href="dashboard-profile.html"><i class="la la-user"></i>Profile</a>
                <a class="dropdown-item" href="dashboard-listing.html"><i class="la la-layer-group"></i>Listings</a>
                <a class="dropdown-item" href="dashboard-messages.html"><i class="la la-envelope"></i> Messages </a>
                <a class="dropdown-item" href="dashboard-reviews.html"><i class="la la-calendar"></i> Reviews</a>
                <a class="dropdown-item" href="dashboard-favorites.html"><i class="la la-thumbs-o-up"></i>Favorites</a>
                <form method="POST" action="{{ route('tenant.guest-portal.logout') }}" class="dropdown-item">
                    @csrf
                    <button type="submit"><i class="la la-sign-out"></i> Logout</button>
                </form>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Mobile Header -->
      <div class="mobile-header">
        <div class="logo"><a href="#"><img src="{{ asset('assets/images/ubix-logo-small.png') }}" alt="" title=""></a></div>

        <!--Nav Box-->
        <div class="nav-outer clearfix">

          <div class="outer-box">

            <!-- Cart btn -->
            <div class="cart-btn">
              <a href="#"><i class="icon flaticon-shopping-bag"></i> <span class="count">2</span></a>
            </div>

            <!-- Login/Register -->
            <div class="login-box"> 
              <a href="#" class="call-modal"><span class="flaticon-user"></span></a>
            </div>
            <a href="#nav-mobile" class="mobile-nav-toggler navbar-trigger"><span class="fa fa-bars"></span></a>
          </div>
        </div>
      </div>

      <!-- Mobile Nav -->
      <div id="nav-mobile"></div>

      <!-- Header Search -->
      <div class="search-popup">
        <span class="search-back-drop"></span>
        
        <div class="search-inner">
          <button class="close-search"><span class="fa fa-times"></span></button>
          <form method="post" action="#">
            <div class="form-group">
              <input type="search" name="search-field" value="" placeholder="Search..." required="">
              <button type="submit"><i class="flaticon-magnifying-glass"></i></button>
            </div>
          </form>
        </div>
      </div>
      <!-- End Header Search -->

    </header>
    <!--End Main Header -->

    @yield('content')

  <!-- Call to Action -->
  <section hidden class="call-to-action" style="background-image: url({{ asset('vendor/guest-listdo/images/background/1.jpg') }})">
    <div class="auto-container">
      <div class="content">
        <h3>Need More Information</h3>
        <div class="text">For inquiries, please contact us.</div>
        <div class="btn-box"><a href="#" class="theme-btn btn-style-three">Explore <span class="flaticon-right"></span></a></div>
      </div>
    </div>
  </section>
  <!-- End Call to Action -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- Footer Upper -->
    <div class="footer-upper">
      <ul class="footer-nav">
        <li><a href="#">Home</a></li>
        <li><a href="#">Booking</a></li>
        <li><a href="#">Checkin</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>

    <!-- Footer Content -->
    <div class="footer-content">
      <div class="auto-container">
        <ul class="social-icon-one">
          <li><a href="#"><span class="fab fa-facebook"></span></a></li>
          <li><a href="#"><span class="fab fa-twitter"></span></a></li>
          <li><a href="#"><span class="fab fa-instagram"></span></a></li>
          <li><a href="#"><span class="fab fa-pinterest"></span></a></li>
          <li><a href="#"><span class="fab fa-dribbble"></span></a></li>
          <li><a href="#"><span class="fab fa-google"></span></a></li>
        </ul>

        <ul class="copyright-text">
          <li>Copyright © 2025 {{ config('app.name') }}</li>
          <li>Cape Town, South Africa</li>
          <li><a href="#">nexusflow.co.za</a></li>
        </ul>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
      <div class="text">Proudly Powered by Laravel</div>
    </div>

    <!-- Scroll To Top -->
    <div class="scroll-to-top scroll-to-target" data-target="html"><span class="flaticon-up"></span></div>
  </footer>
  <!-- End Footer -->

  <!-- Success/Error Messages -->
  @if(session('success'))
  <div class="floating-message-box message-box success">
    <p>{{ session('success') }}</p>
    <button class="close-btn"><span class="close_icon"></span></button>
  </div>
  @endif

  @if($errors->any())
  <div class="floating-message-box message-box error">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li><p>{{ $error }}</p></li>
      @endforeach
    </ul>
    <button class="close-btn"><span class="close_icon"></span></button>
  </div>
  @endif

</div><!-- End Page Wrapper -->
@include('tenant.guest-portal.auth.login-modal')
@include('tenant.guest-portal.auth.register-modal')

<script src="{{ asset('vendor/guest-listdo/js/jquery.js') }}"></script> 
<script src="{{ asset('vendor/guest-listdo/js/popper.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/chosen.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/jquery.fancybox.js') }}"></script>
{{-- <script src="{{ asset('vendor/guest-listdo/js/jquery.modal.min.js') }}"></script> --}}
<script src="{{ asset('vendor/guest-listdo/js/jquery.hideseek.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/mmenu.polyfills.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/sticky_sidebar.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/mmenu.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/owl.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/wow.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/appear.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/script.js') }}"></script>

{{-- custom scripts --}}
<script>
  $(document).ready(function() {
    // Close floating message boxes
    $('.floating-message-box .close-btn').on('click', function() {
      $(this).closest('.floating-message-box').fadeOut();
    });
    // toggle login/register modal
    $('#registerLink').on('click', function(e) {
      e.preventDefault();
      $('#loginModal').modal('hide');
      setTimeout(function() {
      $('#registerModal').modal('show');
      }, 500);
    });
    $('#loginLink').on('click', function(e) {
      e.preventDefault();
      $('#registerModal').modal('hide');
      setTimeout(function() {
      $('#loginModal').modal('show');
      }, 500);
    });
  });
</script>

{{-- page scripts --}}
@stack('scripts')


<!-- Typed Script -->
{{-- <script src="{{ asset('vendor/guest-listdo/js/typed.js') }}"></script>
<script>
  var typed = new Typed('.typed-words', {
    strings: ["City Gems"," Restaurants"," Hotels"],
    typeSpeed: 80,
    backSpeed: 80,
    backDelay: 4000,
    startDelay: 1000,
    loop: true,
    showCursor: true
  });
</script> --}}
</body>
</html>