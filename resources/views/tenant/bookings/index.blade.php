@extends('tenant.layouts.app')

@section('title', 'Manage Bookings')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">All Bookings</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">All Bookings</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">

    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
    {{-- messages from redirect --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ $bookings->total() }}</h3>
            <p>Total Bookings</p>
          </div>
          <i class="bi bi-calendar small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ $bookings->where('status', 'confirmed')->count() }}</h3>
            <p>Confirmed</p>
          </div>
          <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ $bookings->where('status', 'pending')->count() }}</h3>
            <p>Pending</p>
          </div>
          <i class="bi bi-clock small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{ $bookings->where('status', 'cancelled')->count() }}</h3>
            <p>Cancelled</p>
          </div>
          <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
    </div>

    {{-- filters --}}
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="card-title">Filter Bookings</h5>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('tenant.bookings.index') }}">
          <div class="row g-3">
            <div class="col-md-3">
              <label for="guest_name" class="form-label">Guest Name</label>
              <input type="text" name="guest_name" id="guest_name" class="form-control" value="{{ request('guest_name') }}" placeholder="Search by guest name">
            </div>
            <div class="col-md-2">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['pending', 'booked', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'] as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                  {{ ucfirst(str_replace('_', ' ', $status)) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label for="arrival_date" class="form-label">Arrival Date</label>
              <input type="date" name="arrival_date" id="arrival_date" class="form-control" value="{{ request('arrival_date') }}">
            </div>
            <div class="col-md-2">
              <label for="departure_date" class="form-label">Departure Date</label>
              <input type="date" name="departure_date" id="departure_date" class="form-control" value="{{ request('departure_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-primary me-2">
                <i class="fas fa-filter me-1"></i>Apply Filters
              </button>
              <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-redo-alt me-1"></i>Reset
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Bookings Table -->
    <div class="card card-success card-outline">
      <div class="card-header">
        <h5 class="card-title">Bookings Management</h5>
        <div class="card-tools">
          <div class="btn-group" role="group">
            <a href="{{ route('tenant.bookings.export') }}" class="btn btn-sm btn-outline-success">
              <i class="fas fa-download me-1"></i>Export
            </a>
            <a href="{{ route('tenant.bookings.import') }}" class="btn btn-sm btn-outline-info">
              <i class="fas fa-upload me-1"></i>Import
            </a>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createBookingModal">
              <i class="fas fa-plus me-1"></i>New Booking
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Booking Details</th>
                <th>Guest Information</th>
                <th>Room & Dates</th>
                <th>Financial</th>
                <th>Package</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($bookings as $booking)
              <tr>
                <td>
                  <div class="fw-bold text-primary">{{ $booking->bcode }}</div>
                  <small class="text-muted">{{ $booking->created_at->format('M d, Y') }}</small><br>
                  <small class="text-muted">Source: {{ ucfirst($booking->source) }}</small>
                </td>
                <td>
                  @php
                    $primaryGuest = $booking->bookingGuests->where('is_primary', true)->first();
                  @endphp
                  @if($primaryGuest && $primaryGuest->guest)
                    <div class="fw-bold">{{ $primaryGuest->guest->first_name }} {{ $primaryGuest->guest->last_name }}</div>
                    @if($primaryGuest->guest->email)
                      <small class="text-muted">{{ $primaryGuest->guest->email }}</small><br>
                    @endif
                    @if($primaryGuest->guest->phone)
                      <small class="text-muted">{{ $primaryGuest->guest->phone }}</small>
                    @endif
                  @else
                    <span class="text-muted">No guest assigned</span>
                  @endif
                </td>
                <td>
                  <div class="fw-bold">Room {{ str_pad($booking->room->number, 3, '0', STR_PAD_LEFT) }}</div>
                  <small class="text-muted">{{ $booking->room->type->name }}</small><br>
                  <small>{{ $booking->arrival_date->format('M d') }} - {{ $booking->departure_date->format('M d, Y') }}</small><br>
                  <small class="text-muted">{{ $booking->nights }} {{ $booking->nights == 1 ? 'night' : 'nights' }}</small>
                </td>
                <td>
                  <div class="fw-bold text-success">{{ $currency }} {{ number_format($booking->total_amount, 2) }}</div>
                  <small class="text-muted">{{ $currency }} {{ number_format($booking->daily_rate, 2) }}/night</small>
                </td>
                <td>
                  @if($booking->package)
                    <span class="badge bg-info">{{ $booking->package->pkg_name }}</span>
                  @else
                    <span class="text-muted">None</span>
                  @endif
                </td>
                <td>
                  @php
                    $statusColors = [
                      'pending' => 'warning',
                      'booked' => 'primary', 
                      'confirmed' => 'success',
                      'checked_in' => 'info',
                      'checked_out' => 'secondary',
                      'cancelled' => 'danger',
                      'no_show' => 'dark'
                    ];
                    $statusColor = $statusColors[$booking->status] ?? 'secondary';
                  @endphp
                  <span class="badge bg-{{ $statusColor }}">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="{{ route('tenant.bookings.show', $booking) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('tenant.bookings.edit', $booking) }}" class="btn btn-sm btn-outline-warning" title="Edit Booking">
                      <i class="fas fa-edit"></i>
                    </a>
                    <div class="btn-group" role="group">
                      <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <form action="{{ route('tenant.bookings.clone', $booking) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item" onclick="return confirm('Clone this booking?')">
                              <i class="fas fa-copy me-2"></i>Clone
                            </button>
                          </form>
                        </li>
                        <li>
                          <form action="{{ route('tenant.bookings.toggle-status', $booking) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item" onclick="return confirm('Toggle booking status?')">
                              <i class="fas fa-toggle-on me-2"></i>
                              {{ $booking->status === 'cancelled' ? 'Reactivate' : 'Cancel' }}
                            </button>
                          </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <form action="{{ route('tenant.bookings.destroy', $booking) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')">
                              <i class="fas fa-trash me-2"></i>Delete
                            </button>
                          </form>
                        </li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-4">
                  <div class="text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                    <h5>No bookings found</h5>
                    <p>Start by creating your first booking or importing existing data.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBookingModal">
                      <i class="fas fa-plus me-1"></i>Create First Booking
                    </button>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        
        {{-- Pagination links --}}
        {{-- Beautiful pagination --}}
        @if($bookings->hasPages())
        <div class="container-fluid py-3">
          <div class="row align-items-center">
              <div class="col-md-12 float-end">
                  {{ $bookings->links('vendor.pagination.bootstrap-5') }}
              </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

{{-- Create Booking Modal --}}
<div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createBookingModalLabel">Create New Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Select the type of booking you want to create:</p>
        <div class="d-grid gap-2">
          <a href="{{ route('tenant.bookings.create') }}" class="btn btn-outline-success">
            <i class="fas fa-calendar me-2"></i>Simple Booking
            <br><small class="text-muted">Create a standard room booking</small>
          </a>
          @if($packages->count() > 0)
          <div class="dropdown-center">
            <button class="btn btn-outline-info dropdown-toggle w-100" type="button" id="packageBookingDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-gift me-2"></i>Package Booking
              <br><small class="text-muted">Create booking with a package</small>
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="packageBookingDropdown">
              @foreach($packages as $package)
              <li>
                <a class="dropdown-item" href="{{ route('tenant.bookings.create') }}?package_id={{ $package->id }}">
                  <div class="fw-bold">{{ $package->pkg_name }}</div>
                  <small class="text-muted">{{ $package->pkg_number_of_nights }} nights - {{ $currency }} {{ number_format($package->pkg_base_price, 2) }}</small>
                </a>
              </li>
              @endforeach
            </ul>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection