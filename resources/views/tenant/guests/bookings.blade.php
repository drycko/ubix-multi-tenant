@extends('tenant.layouts.app')

@section('title', 'Guest Bookings - ' . $guest->full_name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-bed"></i>
          Guest Bookings
          <small class="text-muted">{{ $guest->full_name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.index') }}">Guests</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.show', $guest) }}">{{ $guest->full_name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Bookings</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    <!-- Guest Summary -->
    <div class="row mb-3">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar-circle me-3">
                {{ strtoupper(substr($guest->first_name, 0, 1) . substr($guest->last_name, 0, 1)) }}
              </div>
              <div>
                <h5 class="mb-1">{{ $guest->full_name }}</h5>
                <div class="text-muted">
                  <i class="fas fa-envelope"></i> {{ $guest->email }} | 
                  <i class="fas fa-phone"></i> {{ $guest->phone }}
                  @if($guest->nationality)
                  | <span class="badge bg-info">{{ $guest->nationality }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-grid gap-2">
          <a href="{{ route('tenant.guests.show', $guest) }}" class="btn btn-outline-primary">
            <i class="fas fa-user"></i> View Guest Profile
          </a>
          <a href="{{ route('tenant.guests.edit', $guest) }}" class="btn btn-outline-warning">
            <i class="fas fa-edit"></i> Edit Guest
          </a>
        </div>
      </div>
    </div>

    <!-- Bookings List -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list"></i> Booking History
        </h3>
      </div>
      <div class="card-body">
        @if($bookings->count() > 0)
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-light">
              <tr>
                <th>Booking Code</th>
                <th>Room</th>
                <th>Dates</th>
                <th>Nights</th>
                <th>Package</th>
                <th>Status</th>
                <th>Created</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($bookings as $bookingGuest)
              <tr>
                <td>
                  <strong>{{ $bookingGuest->booking->bcode }}</strong>
                  @if($bookingGuest->is_primary)
                  <br><small class="badge bg-success">Primary Guest</small>
                  @endif
                </td>
                <td>
                  <div>
                    <strong>Room {{ $bookingGuest->booking->room->number }}</strong>
                    <br><small class="text-muted">{{ $bookingGuest->booking->room->type->name }}</small>
                  </div>
                </td>
                <td>
                  <div>
                    {{ $bookingGuest->booking->arrival_date->format('M j, Y') }}
                    <br><small class="text-muted">to {{ $bookingGuest->booking->departure_date->format('M j, Y') }}</small>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge bg-primary rounded-pill">{{ $bookingGuest->booking->nights }}</span>
                </td>
                <td>
                  @if($bookingGuest->booking->package)
                    <span class="badge bg-info">{{ $bookingGuest->booking->package->pkg_name }}</span>
                  @else
                    <span class="text-muted">No Package</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-{{ $bookingGuest->booking->status === 'confirmed' ? 'success' : ($bookingGuest->booking->status === 'pending' ? 'warning' : ($bookingGuest->booking->status === 'checked_in' ? 'info' : ($bookingGuest->booking->status === 'checked_out' ? 'secondary' : 'danger'))) }}">
                    {{ ucfirst(str_replace('_', ' ', $bookingGuest->booking->status)) }}
                  </span>
                </td>
                <td>
                  <div>{{ $bookingGuest->booking->created_at->format('M j, Y') }}</div>
                  <small class="text-muted">{{ $bookingGuest->booking->created_at->diffForHumans() }}</small>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('tenant.bookings.show', $bookingGuest->booking) }}" 
                       class="btn btn-outline-primary" 
                       title="View Booking">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('tenant.bookings.edit', $bookingGuest->booking) }}" 
                       class="btn btn-outline-warning" 
                       title="Edit Booking">
                      <i class="fas fa-edit"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        {{ $bookings->links() }}
        @else
        <div class="text-center py-5">
          <i class="fas fa-bed fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No bookings found</h5>
          <p class="text-muted">This guest hasn't made any bookings yet.</p>
          <a href="{{ route('tenant.bookings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Booking
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

@push('styles')
<style>
.avatar-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(45deg, #3B82F6, #1D4ED8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 16px;
}
</style>
@endpush
@endsection