@extends('tenant.layouts.app')

@section('title', 'Guest Details - ' . $guest->full_name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user"></i>
          Guest Details
          <small class="text-muted">{{ $guest->full_name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.index') }}">Guests</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $guest->full_name }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <!-- Main Guest Information -->
      <div class="col-md-8">
        <!-- Guest Profile -->
        <div class="card card-primary card-outline mb-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-user"></i> Guest Profile
            </h3>
            <div class="card-tools">
              <span class="badge bg-{{ $guest->is_active ? 'success' : 'secondary' }}">
                {{ $guest->is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-sm">
                  <tr>
                    <td><strong>Full Name:</strong></td>
                    <td>{{ $guest->full_name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Email:</strong></td>
                    <td>
                      <a href="mailto:{{ $guest->email }}">{{ $guest->email }}</a>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Phone:</strong></td>
                    <td>
                      <a href="tel:{{ $guest->phone }}">{{ $guest->phone }}</a>
                    </td>
                  </tr>
                  @if($guest->id_number)
                  <tr>
                    <td><strong>ID Number:</strong></td>
                    <td>{{ $guest->id_number }}</td>
                  </tr>
                  @endif
                  @if($guest->nationality)
                  <tr>
                    <td><strong>Nationality:</strong></td>
                    <td>
                      <span class="badge bg-info">{{ $guest->nationality }}</span>
                    </td>
                  </tr>
                  @endif
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-sm">
                  @if($guest->emergency_contact)
                  <tr>
                    <td><strong>Emergency Contact:</strong></td>
                    <td>{{ $guest->emergency_contact }}</td>
                  </tr>
                  @endif
                  @if($guest->emergency_contact_phone)
                  <tr>
                    <td><strong>Emergency Phone:</strong></td>
                    <td>
                      <a href="tel:{{ $guest->emergency_contact_phone }}">{{ $guest->emergency_contact_phone }}</a>
                    </td>
                  </tr>
                  @endif
                  @if($guest->car_registration)
                  <tr>
                    <td><strong>Car Registration:</strong></td>
                    <td>{{ $guest->car_registration }}</td>
                  </tr>
                  @endif
                  @if($guest->gown_size)
                  <tr>
                    <td><strong>Gown Size:</strong></td>
                    <td>{{ $guest->gown_size }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td><strong>Registered:</strong></td>
                    <td>{{ $guest->created_at->format('M j, Y g:i A') }}</td>
                  </tr>
                </table>
              </div>
            </div>

            <!-- Addresses -->
            @if($guest->physical_address || $guest->residential_address)
            <hr>
            <div class="row">
              @if($guest->physical_address)
              <div class="col-md-6">
                <h6>Physical Address:</h6>
                <p class="text-muted">{{ $guest->physical_address }}</p>
              </div>
              @endif
              @if($guest->residential_address)
              <div class="col-md-6">
                <h6>Residential Address:</h6>
                <p class="text-muted">{{ $guest->residential_address }}</p>
              </div>
              @endif
            </div>
            @endif

            <!-- Special Notes -->
            @if($guest->medical_notes || $guest->dietary_preferences)
            <hr>
            <div class="row">
              @if($guest->medical_notes)
              <div class="col-md-6">
                <h6>Medical Notes:</h6>
                <div class="alert alert-warning">
                  <i class="fas fa-exclamation-triangle"></i>
                  {{ $guest->medical_notes }}
                </div>
              </div>
              @endif
              @if($guest->dietary_preferences)
              <div class="col-md-6">
                <h6>Dietary Preferences:</h6>
                <div class="alert alert-info">
                  <i class="fas fa-utensils"></i>
                  {{ $guest->dietary_preferences }}
                </div>
              </div>
              @endif
            </div>
            @endif

            <!-- Guest Clubs -->
            @if($guest->guestClubMembers->count() > 0)
            <hr>
            <h6>Guest Club Memberships:</h6>
            <div class="d-flex flex-wrap gap-1">
              @foreach($guest->guestClubMembers as $membership)
                <span class="badge bg-success">{{ $membership->guestClub->name }}</span>
              @endforeach
            </div>
            @endif
          </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card card-info card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-bed"></i> Recent Bookings
            </h3>
            <div class="card-tools">
              <a href="{{ route('tenant.guests.bookings', $guest) }}" class="btn btn-sm btn-outline-primary">
                View All Bookings
              </a>
            </div>
          </div>
          <div class="card-body">
            @if($recentBookings->count() > 0)
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>Booking Code</th>
                    <th>Room</th>
                    <th>Dates</th>
                    <th>Nights</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentBookings->take(5) as $bookingGuest)
                  <tr>
                    <td>{{ $bookingGuest->booking->bcode }}</td>
                    <td>
                      Room {{ $bookingGuest->booking->room->number }}
                      <br><small class="text-muted">{{ $bookingGuest->booking->room->type->name }}</small>
                    </td>
                    <td>
                      {{ $bookingGuest->booking->arrival_date->format('M j') }} - 
                      {{ $bookingGuest->booking->departure_date->format('M j, Y') }}
                    </td>
                    <td>{{ $bookingGuest->booking->nights }}</td>
                    <td>
                      <span class="badge bg-{{ $bookingGuest->booking->status === 'confirmed' ? 'success' : ($bookingGuest->booking->status === 'pending' ? 'warning' : 'info') }}">
                        {{ ucfirst(str_replace('_', ' ', $bookingGuest->booking->status)) }}
                      </span>
                    </td>
                    <td>
                      <a href="{{ route('tenant.bookings.show', $bookingGuest->booking) }}" 
                         class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center py-3">
              <i class="fas fa-bed fa-2x text-muted mb-2"></i>
              <p class="text-muted">No bookings found for this guest.</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Actions -->
        <div class="card card-success card-outline mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-tools"></i> Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.guests.edit', $guest) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Guest
              </a>
              <form action="{{ route('tenant.guests.toggle-status', $guest) }}" 
                    method="POST" 
                    style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline-{{ $guest->is_active ? 'danger' : 'success' }} w-100">
                  <i class="fas fa-{{ $guest->is_active ? 'pause' : 'play' }}"></i> 
                  {{ $guest->is_active ? 'Deactivate' : 'Activate' }} Guest
                </button>
              </form>
              <hr>
              <a href="{{ route('tenant.guests.bookings', $guest) }}" class="btn btn-outline-primary">
                <i class="fas fa-bed"></i> View All Bookings
              </a>
              <a href="{{ route('tenant.guests.invoices', $guest) }}" class="btn btn-outline-info">
                <i class="fas fa-file-invoice"></i> View Invoices
              </a>
              <a href="{{ route('tenant.guests.payments', $guest) }}" class="btn btn-outline-success">
                <i class="fas fa-credit-card"></i> View Payments
              </a>
              <hr>
              <a href="{{ route('tenant.guests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Guests
              </a>
            </div>
          </div>
        </div>

        <!-- Booking Statistics -->
        <div class="card card-warning card-outline mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-bar"></i> Booking Statistics
            </h5>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td><strong>Total Spent:</strong></td>
                <td><span class="badge bg-primary">{{ $bookingStats->total_spent ?? 0 }}</span></td>
              </tr>
              <tr>
                <td><strong>Total Bookings:</strong></td>
                <td><span class="badge bg-primary">{{ $bookingStats->total_bookings ?? 0 }}</span></td>
              </tr>
              <tr>
                <td><strong>Confirmed:</strong></td>
                <td><span class="badge bg-success">{{ $bookingStats->confirmed_bookings ?? 0 }}</span></td>
              </tr>
              <tr>
                <td><strong>Completed:</strong></td>
                <td><span class="badge bg-info">{{ $bookingStats->completed_bookings ?? 0 }}</span></td>
              </tr>
              <tr>
                <td><strong>Cancelled:</strong></td>
                <td><span class="badge bg-danger">{{ $bookingStats->cancelled_bookings ?? 0 }}</span></td>
              </tr>
              @if($bookingStats->first_visit)
              <tr>
                <td><strong>First Visit:</strong></td>
                <td>{{ \Carbon\Carbon::parse($bookingStats->first_visit)->format('M j, Y') }}</td>
              </tr>
              @endif
              @if($bookingStats->last_visit)
              <tr>
                <td><strong>Last Visit:</strong></td>
                <td>{{ \Carbon\Carbon::parse($bookingStats->last_visit)->format('M j, Y') }}</td>
              </tr>
              @endif
            </table>
          </div>
        </div>

        <!-- Guest Information Summary -->
        <div class="card card-info card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle"></i> Quick Info
            </h5>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td><strong>Guest ID:</strong></td>
                <td>#{{ $guest->id }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  <span class="badge bg-{{ $guest->is_active ? 'success' : 'secondary' }}">
                    {{ $guest->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
              </tr>
              @if($guest->nationality)
              <tr>
                <td><strong>Nationality:</strong></td>
                <td>{{ $guest->nationality }}</td>
              </tr>
              @endif
              <tr>
                <td><strong>Registered:</strong></td>
                <td>{{ $guest->created_at->format('M j, Y') }}</td>
              </tr>
              @if($guest->updated_at != $guest->created_at)
              <tr>
                <td><strong>Last Updated:</strong></td>
                <td>{{ $guest->updated_at->format('M j, Y') }}</td>
              </tr>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->
@endsection