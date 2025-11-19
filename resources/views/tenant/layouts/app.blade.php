<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }} Admin - @yield('title')</title>
  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#373643" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="title" content="Ubix Admin - @yield('title')" />
  <meta name="author" content="Ubix[at]nexusflow.co.za" />
  <!--end::Accessibility Meta Tags-->
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('vendor/admin-lte/dist/css/adminlte.min.css') }}">
  {{-- custom css --}}
  <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
  {{-- favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}"/>
  {{-- bootstrap icons --}}
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/images/site.webmanifest') }}">
  <x-rich-text::styles theme="richte xtlaravel" data-turbo-track="false" />
</head>
{{-- <body class="layout-fixed fixed-header fixed-footer sidebar-expand-lg sidebar-open bg-body-tertiary"> --}}
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <!--begin::Header-->
    <nav class="app-header navbar navbar-expand bg-body">
      <!--begin::Container-->
      <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
              <i class="bi bi-list"></i>
            </a>
          </li>
          <li class="nav-item d-none d-md-block">
            <a href="{{ route('tenant.dashboard') }}" class="nav-link">Home</a>
          </li>
          <li class="nav-item d-none d-md-block">
            <a href="{{ route('tenant.knowledge-base') }}" class="nav-link">
              <i class="bi bi-book"></i> Knowledge Base
            </a>
          </li>
          <li class="nav-item d-none d-md-block">
            @if(auth()->check() && auth()->user()->property_id === null)
            <a href="#" class="nav-link text-warning"><i class="fas fa-crown"></i> Super User</a>
            @endif
          </li>
        </ul>
        <!--end::Start Navbar Links-->

        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
          <!--begin::Navbar Settings-->
          <li class="nav-item">
            <a class="nav-link" href="{{ route('tenant.settings.index') }}" role="button">
              <i class="bi bi-gear-fill"></i>
            </a>
          </li>
          <!--end::Navbar Settings-->

          <!--begin::Messages Dropdown Menu-->
          <li hidden class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-chat-text"></i>
              <span class="navbar-badge badge text-bg-danger">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <small>No messages</small>
            </div>
          </li>
          <!--end::Messages Dropdown Menu-->

          <!--begin::Notifications Dropdown Menu-->
          <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-bell-fill"></i>
              <span class="navbar-badge badge text-bg-warning">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <span class="dropdown-item dropdown-header">No Notifications</span>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="bi bi-envelope me-2"></i> 0 new messages
                <span class="float-end text-muted text-sm">0 mins</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
            </div>
          </li>
          <!--end::Notifications Dropdown Menu-->

          <!--begin::Fullscreen Toggle-->
          <li class="nav-item">
            <a class="nav-link" href="#" data-lte-toggle="fullscreen">
              <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
              <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
            </a>
          </li>
          <!--end::Fullscreen Toggle-->

          <!--begin::User Menu Dropdown-->
          <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i>
              {{-- if you want to use profile photo, uncomment the code below and comment the icon above --}}
              {{-- <img
                  src="{{ Auth::user()->profile_photo_url }}"
                  class="rounded-circle shadow"
                  alt="User Image"
                /> --}}
              <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
              <!--begin::User Image replace with image when we add the profile photo-->
              <li class="user-header text-bg-success">
                <i class="bi bi-person-circle fs-1"></i>
                {{-- if you want to use profile photo, uncomment the code below and comment the icon above --}}
                {{-- <img
                  src="{{ Auth::user()->profile_photo_url }}"
                  class="rounded-circle shadow"
                  alt="User Image"
                /> --}}
                <p>
                  {{ Auth::user()->name }} - {{ Auth::user()->role }}
                  <small>{{ strtoupper(current_property()->name ?? 'Global') }}</small>
                </p>
              </li>
              <!--end::User Image-->
              <!--begin::Menu Body-->
              <li class="user-body">
                <!--begin::Row-->
                <div class="row">
                  {{-- <div class="col-4 text-center">
                    <a href="#">Followers</a>
                  </div>
                  <div class="col-4 text-center">
                    <a href="#">Sales</a>
                  </div>
                  <div class="col-4 text-center">
                    <a href="#">Friends</a>
                  </div> --}}
                </div>
                <!--end::Row-->
              </li>
              <!--end::Menu Body-->
              <!--begin::Menu Footer-->
              <li class="user-footer">

                <a hidden href="{{ route('tenant.settings.index') }}" class="btn btn-default btn-flat">Settings</a>
                <a href="{{ route('tenant.users.profile') }}" class="btn btn-default btn-flat">Profile</a>
                <a href="#" class="btn btn-default btn-flat float-end" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
              </li>
              <!--end::Menu Footer-->
            </ul>
          </li>
          <!--end::User Menu Dropdown-->
          <li class="nav-item dropdown">
            <button
              class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center"
              id="bd-theme"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              data-bs-display="static"
            >
              <span class="theme-icon-active">
                <i class="my-1"></i>
              </span>
              <span class="d-lg-none ms-2" id="bd-theme-text">Toggle theme</span>
            </button>
            <ul
              class="dropdown-menu dropdown-menu-end"
              aria-labelledby="bd-theme-text"
              style="--bs-dropdown-min-width: 8rem;"
            >
              <li>
                <button
                  type="button"
                  class="dropdown-item d-flex align-items-center active"
                  data-bs-theme-value="light"
                  aria-pressed="false"
                >
                  <i class="bi bi-sun-fill me-2"></i>
                  Light
                  <i class="bi bi-check-lg ms-auto d-none"></i>
                </button>
              </li>
              <li>
                <button
                  type="button"
                  class="dropdown-item d-flex align-items-center"
                  data-bs-theme-value="dark"
                  aria-pressed="false"
                >
                  <i class="bi bi-moon-fill me-2"></i>
                  Dark
                  <i class="bi bi-check-lg ms-auto d-none"></i>
                </button>
              </li>
              <li>
                <button
                  type="button"
                  class="dropdown-item d-flex align-items-center"
                  data-bs-theme-value="auto"
                  aria-pressed="true"
                >
                  <i class="bi bi-circle-half me-2"></i>
                  Auto
                  <i class="bi bi-check-lg ms-auto d-none"></i>
                </button>
              </li>
            </ul>
          </li>
        </ul>
        <!--end::End Navbar Links-->
      </div>
      <!--end::Container-->
    </nav>
    <!--end::Header-->
    <!--begin::Sidebar-->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
      <!--begin::Sidebar Brand-->
      <div class="sidebar-brand">
        <!--begin::Brand Link-->
        <a href="{{ route('tenant.dashboard') }}" class="brand-link logo-switch">
          <!--begin::Brand Image Small-->
          <img
            src="{{ asset('assets/images/nexusflow-logo-small.png') }}"
            alt="Ubix Logo Small"
            class="brand-image-xl logo-xs opacity-75 shadow"
          />
          <!--end::Brand Image Small-->
          <!--begin::Brand Image Large-->
          <img
            src="{{ asset('assets/images/ubix-logo-full.png') }}"
            alt="Ubix Logo Large"
            class="brand-image-xs logo-xl opacity-75"
          />
          <!--end::Brand Image Large-->
        </a>
        <!--end::Brand Link-->
      </div>
      <!--end::Sidebar Brand-->
      <!--begin::Sidebar Wrapper-->
      <div class="sidebar-wrapper">
        <nav class="mt-2">
          <!--begin::Sidebar Menu-->
          <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
            id="navigation"
          >
          {{-- Dashboard --}}
            <li class="nav-header"><strong>DASHBOARD</strong></li>
            <li class="nav-item">
              <a class="nav-link {{ Request::is('t/dashboard') ? 'active' : '' }}" href="{{ route('tenant.dashboard') }}">
                <i class="nav-icon bi bi-speedometer"></i>
                <p>Home</p>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ Request::is('t/stats*') ? 'active' : '' }}" href="{{ route('tenant.stats.index') }}">
                <i class="nav-icon bi bi-graph-up"></i>
                <p>Statistics</p>
              </a>
            </li>

            {{-- Bookings & Packages --}}
            <li class="nav-header">BOOKINGS</li>
            <li class="nav-item {{ Request::is('t/bookings*') || Request::is('t/room-packages*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/bookings*') || Request::is('t/room-packages*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-calendar-check"></i>
                <p>Reservations
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/bookings') || Request::is('t/bookings/index') ? 'active' : '' }}" href="{{ route('tenant.bookings.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Bookings</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/bookings/import') ? 'active' : '' }}" href="{{ route('tenant.bookings.import') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Import Bookings</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/room-packages*') ? 'active' : '' }}" href="{{ route('tenant.room-packages.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Packages</p>
                  </a>
                </li>
              </ul>
            </li>

            {{-- Guests --}}
            <li class="nav-header">GUEST MANAGEMENT</li>
            <li class="nav-item {{ Request::is('t/guests*') || Request::is('t/guest-clubs*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/guests*') || Request::is('t/guest-clubs*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-people"></i>
                <p>Guests
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/guests') || Request::is('t/guests/index') ? 'active' : '' }}" href="{{ route('tenant.guests.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>All Guests</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/guest-clubs*') ? 'active' : '' }}" href="{{ route('tenant.guest-clubs.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Guest Clubs</p>
                  </a>
                </li>
              </ul>
            </li>

            {{-- Room Management --}}
            <li class="nav-header">ROOM MANAGEMENT</li>
            <li class="nav-item {{ Request::is('t/rooms*') || Request::is('t/room-types*') || Request::is('t/room-amenities*') || Request::is('t/room-rates*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/rooms*') || Request::is('t/room-types*') || Request::is('t/room-amenities*') || Request::is('t/room-rates*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-house-door"></i>
                <p>Rooms & Configuration
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/rooms') || Request::is('t/rooms/index') ? 'active' : '' }}" href="{{ route('tenant.rooms.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Rooms</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/room-types*') ? 'active' : '' }}" href="{{ route('tenant.room-types.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Room Types</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/room-amenities*') ? 'active' : '' }}" href="{{ route('tenant.room-amenities.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Room Amenities</p>
                  </a>
                </li>
                @can('view room rates')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/room-rates') || Request::is('t/room-rates/index') ? 'active' : '' }}" href="{{ route('tenant.room-rates.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Room Rates</p>
                  </a>
                </li>
                @can('create room rates')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/room-rates/import') ? 'active' : '' }}" href="{{ route('tenant.room-rates.import') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Import Room Rates</p>
                  </a>
                </li>
                @endcan
                @endcan
              </ul>
            </li>

            @can('view housekeeping')
            {{-- Housekeeping & Operations --}}
            <li class="nav-header">OPERATIONS</li>
            <li class="nav-item {{ Request::is('t/housekeeping*') || Request::is('t/room-status*') || Request::is('t/maintenance*') || Request::is('t/cleaning-schedule*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/housekeeping*') || Request::is('t/room-status*') || Request::is('t/maintenance*') || Request::is('t/cleaning-schedule*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-gear-wide-connected"></i>
                <p>Housekeeping & Maintenance
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/housekeeping') || Request::is('t/housekeeping/index') ? 'active' : '' }}" href="{{ route('tenant.housekeeping.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Housekeeping</p>
                  </a>
                </li>
                @can('view room status')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/room-status*') ? 'active' : '' }}" href="{{ route('tenant.room-status.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Room Status</p>
                  </a>
                </li>
                @endcan
                @can('view maintenance')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/maintenance/dashboard') ? 'active' : '' }}" href="{{ route('tenant.maintenance.dashboard') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Maintenance Dashboard</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/maintenance') && !Request::is('t/maintenance/*') ? 'active' : '' }}" href="{{ route('tenant.maintenance.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Maintenance Requests</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/maintenance/tasks') ? 'active' : '' }}" href="{{ route('tenant.maintenance.tasks') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Maintenance Tasks</p>
                  </a>
                </li>
                @endcan
                @can('view cleaning schedules')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/cleaning-schedule') || Request::is('t/cleaning-schedule/index') ? 'active' : '' }}" href="{{ route('tenant.cleaning-schedule.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Cleaning Schedule</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/cleaning-schedule/calendar') ? 'active' : '' }}" href="{{ route('tenant.cleaning-schedule.calendar') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Cleaning Calendar</p>
                  </a>
                </li>
                @endcan
              </ul>
            </li>
            @endcan

            {{-- Financials --}}
            <li class="nav-header">FINANCIALS</li>
            <li class="nav-item {{ Request::is('t/booking-invoices*') || Request::is('t/refunds*') || Request::is('t/taxes*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/booking-invoices*') || Request::is('t/refunds*') || Request::is('t/taxes*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-currency-dollar"></i>
                <p>Financial Management
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                @can('view invoices')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/booking-invoices*') ? 'active' : '' }}" href="{{ route('tenant.booking-invoices.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Invoices</p>
                  </a>
                </li>
                @endcan
                @can('view refunds')
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/refunds*') ? 'active' : '' }}" href="{{ route('tenant.refunds.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Refunds</p>
                  </a>
                </li>
                @endcan
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/taxes*') ? 'active' : '' }}" href="{{ route('tenant.taxes.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Tax Management</p>
                  </a>
                </li>
              </ul>
            </li>

            {{-- Reports --}}
            <li class="nav-header">REPORTS</li>
            <li class="nav-item {{ Request::is('t/reports*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/reports*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-bar-chart"></i>
                <p>Reports
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/reports/bookings') ? 'active' : '' }}" href="{{ route('tenant.reports.bookings') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Bookings Report</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/reports/occupancy') ? 'active' : '' }}" href="{{ route('tenant.reports.occupancy') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Occupancy Report</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/reports/financial') ? 'active' : '' }}" href="{{ route('tenant.reports.financial') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Financial Report</p>
                  </a>
                </li>
              </ul>
            </li>

            {{-- System & Settings --}}
            <li class="nav-header">SYSTEM</li>
            @can('manage properties')
            <li class="nav-item">
              <a class="nav-link {{ Request::is('t/properties*') ? 'active' : '' }}" href="{{ route('tenant.properties.index') }}">
                <i class="nav-icon bi bi-building"></i>
                <p>Properties</p>
              </a>
            </li>
            @endcan

            @if (is_super_user())
            <li class="nav-item {{ Request::is('t/users*') || Request::is('t/roles*') || Request::is('t/permissions*') ? 'menu-open' : '' }}">
              <a class="nav-link {{ Request::is('t/users*') || Request::is('t/roles*') || Request::is('t/permissions*') ? 'active' : '' }}" href="#">
                <i class="nav-icon bi bi-people"></i>
                <p>User Management
                  <i class="nav-arrow bi bi-chevron-right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/users') || Request::is('t/users/index') ? 'active' : '' }}" href="{{ route('tenant.users.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Users</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/roles') || Request::is('t/roles/index') ? 'active' : '' }}" href="{{ route('tenant.roles.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Roles</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Request::is('t/permissions*') ? 'active' : '' }}" href="{{ route('tenant.permissions.index') }}">
                    <i class="bi bi-circle nav-icon"></i>
                    <p>Permissions</p>
                  </a>
                </li>
              </ul>
            </li>
            @endif
          </ul>
          <!--end::Sidebar Menu-->
        </nav>
      </div>
      <!--end::Sidebar Wrapper-->
    </aside>
    <!--end::Sidebar-->
    <!--begin::App Main-->
    <main class="app-main">
      @yield('content')
    </main>
    <!--end::App Main-->
    <!--begin::Footer-->
    <footer class="app-footer">
      <!--begin::To the end-->
      <div class="float-end d-none d-sm-inline"><b>Version</b> {{ env('APP_VERSION') }}</div>
      <!--end::To the end-->
      <!--begin::Copyright-->
      <strong>
        Copyright &copy; 2025&nbsp;
        <a href="https://nexusflow.co.za" class="text-decoration-none text-success"> {{ config('app.name') }}</a>.
      </strong>
      All rights reserved.
      <!--end::Copyright-->
    </footer>
    <!--end::Footer-->

    {{-- <footer class="bg-white border-top sticky-footer mt-auto">
      <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted">© {{ date('Y') }} Ubix Admin. All rights reserved.</div>
          <div>
            <a href="#" class="text-muted me-3">Privacy Policy</a>
            <a href="#" class="text-muted">Terms of Service</a>
          </div>
        </div>
      </div>
    </footer> --}}
  </div>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Select "Logout" below if you are ready to end your current session.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="{{ route('tenant.logout') }}">
            @csrf
            <button type="submit" class="btn btn-success">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Success/Error Messages -->
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

  <!-- Success/Error Messages -->
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

  {{-- adminlte js --}}
  <script src="{{ asset('vendor/admin-lte/dist/js/adminlte.min.js') }}"></script>
  
  {{-- Custom page scripts --}}
  @stack('scripts')
  
  <script>
    // Auto-show toasts
    document.addEventListener('DOMContentLoaded', function() {
      const toasts = document.querySelectorAll('.toast');
      toasts.forEach(toast => {
        new bootstrap.Toast(toast).show();
      });
    });
  </script>
  <script>
    // Color Mode Toggler
(() => {
  "use strict";

  const storedTheme = localStorage.getItem("theme");

  const getPreferredTheme = () => {
    if (storedTheme) {
      return storedTheme;
    }

    return window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  };

  const setTheme = function (theme) {
    if (
      theme === "auto" &&
      window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
      document.documentElement.setAttribute("data-bs-theme", "dark");
    } else {
      document.documentElement.setAttribute("data-bs-theme", theme);
    }
  };

  setTheme(getPreferredTheme());

  const showActiveTheme = (theme, focus = false) => {
    const themeSwitcher = document.querySelector("#bd-theme");

    if (!themeSwitcher) {
      return;
    }

    const themeSwitcherText = document.querySelector("#bd-theme-text");
    const activeThemeIcon = document.querySelector(".theme-icon-active i");
    const btnToActive = document.querySelector(
      `[data-bs-theme-value="${theme}"]`
    );
    const svgOfActiveBtn = btnToActive.querySelector("i").getAttribute("class");

    for (const element of document.querySelectorAll("[data-bs-theme-value]")) {
      element.classList.remove("active");
      element.setAttribute("aria-pressed", "false");
    }

    btnToActive.classList.add("active");
    btnToActive.setAttribute("aria-pressed", "true");
    activeThemeIcon.setAttribute("class", svgOfActiveBtn);
    const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActive.dataset.bsThemeValue})`;
    themeSwitcher.setAttribute("aria-label", themeSwitcherLabel);

    if (focus) {
      themeSwitcher.focus();
    }
  };

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", () => {
      if (storedTheme !== "light" || storedTheme !== "dark") {
        setTheme(getPreferredTheme());
      }
    });

  window.addEventListener("DOMContentLoaded", () => {
    showActiveTheme(getPreferredTheme());

    for (const toggle of document.querySelectorAll("[data-bs-theme-value]")) {
      toggle.addEventListener("click", () => {
        const theme = toggle.getAttribute("data-bs-theme-value");
        localStorage.setItem("theme", theme);
        setTheme(theme);
        showActiveTheme(theme, true);
      });
    }
  });
})();
  </script>
</body>
<!--end::body-->
</html>