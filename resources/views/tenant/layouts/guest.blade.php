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

            <!-- Cart btn -->
            <div hidden class="cart-btn">
              <a href="#"><i class="icon flaticon-shopping-bag"></i> <span class="count">2</span></a>

              <div class="shopping-cart">
                <ul class="shopping-cart-items">
                  <li class="cart-item">
                    <img src="{{ asset('vendor/guest-listdo/images/resource/item-thumb-1.jpg') }}" alt="" class="thumb" />
                    <span class="item-name">Dolar Sit Amet</span>
                    <span class="item-quantity">1 x <span class="item-amount">$7.90</span></span>
                    <a href="shop-single.html" class="product-detail"></a>
                    <button class="remove-item"><span class="fa fa-times"></span></button>
                  </li>

                  <li class="cart-item">
                    <img src="{{ asset('vendor/guest-listdo/images/resource/item-thumb-2.jpg') }}" alt="" class="thumb"  />
                    <span class="item-name">Lorem Ipsum</span>
                    <span class="item-quantity">3 x <span class="item-amount">$7.90</span></span>
                    <a href="shop-single.html" class="product-detail"></a>
                    <button class="remove-item"><span class="fa fa-times"></span></button>
                  </li>
                </ul>

                <div class="shopping-cart-total"><span>Subtotal: </span> $57.70</div>

                <div class="cart-footer">
                  <a href="cart.html" class="theme-btn btn-style-one">View Cart</a>
                  <a href="checkout.html" class="theme-btn btn-style-two bg-red">Checkout</a>
                </div>
              </div> <!--end shopping-cart -->
            </div>

            <!-- Login/Register -->
            <div class="login-box"> 
              <span class="flaticon-user"></span> 
              <a href="#" class="call-modal">Login</a> or 
              <a href="#" class="call-modal">Register </a>
            </div>
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
  <section class="call-to-action" style="background-image: url({{ asset('vendor/guest-listdo/images/background/1.jpg') }})">
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

<script src="{{ asset('vendor/guest-listdo/js/jquery.js') }}"></script> 
<script src="{{ asset('vendor/guest-listdo/js/popper.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/chosen.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/jquery.fancybox.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/jquery.modal.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/jquery.hideseek.min.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/mmenu.polyfills.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/mmenu.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/owl.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/wow.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/appear.js') }}"></script>
<script src="{{ asset('vendor/guest-listdo/js/script.js') }}"></script>

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