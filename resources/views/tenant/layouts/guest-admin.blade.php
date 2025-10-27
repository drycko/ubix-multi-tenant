<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }} Guest Portal - @yield('title')</title>
  <meta name="theme-color" content="#1a1a1a" />
  <meta name="author" content="Ubix[at]nexusflow.co.za" />
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('vendor/admin-lte/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}"/>
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/images/site.webmanifest') }}">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="{{ route('tenant.guest-portal.index') }}">
        <img src="{{ asset('assets/images/ubix-logo-full.png') }}" alt="Ubix Logo" height="32" class="me-2">
        {{-- <span class="fw-bold">{{ config('app.name') }} Guest Portal</span> --}}
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar" aria-controls="guestNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="guestNavbar">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('tenant.guest-portal.booking') }}">Book a Room</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('tenant.guest-portal.checkin') }}">Check-In/Out</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('tenant.guest-portal.requests') }}">Requests & Feedback</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('tenant.guest-portal.keys') }}">Digital Keys</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    @yield('content')
  </main>
  <footer class="bg-success text-white py-3 mt-auto">
    <div class="container text-center">
      <small>&copy; 2025 Ubix Guest Portal. All rights reserved.</small>
    </div>
  </footer>
  @if(session('success'))
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast align-items-center text-white bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          {{ session('success') }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
  @endif
  @if($errors->any())
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast align-items-center text-white bg-danger border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
  @endif
  <script src="{{ asset('vendor/admin-lte/dist/js/adminlte.min.js') }}"></script>
  @stack('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toasts = document.querySelectorAll('.toast');
      toasts.forEach(toast => {
        new bootstrap.Toast(toast).show();
      });
    });
  </script>
</body>
</html>
