@extends('tenant.layouts.guest')

@section('title', 'My Bookings')

@section('content')
<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="fw-bold text-success mb-2">
            <i class="bi bi-list-check me-2"></i>
            My Bookings
          </h2>
          <p class="text-muted mb-0">View and manage all your bookings</p>
        </div>
        <div>
          <a href="{{ route('tenant.guest-portal.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i> Dashboard
          </a>
          <a href="{{ route('tenant.guest-portal.booking') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> New Booking
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="row mb-4">
    <div class="col-12">
      <ul class="nav nav-pills">
        <li class="nav-item">
          <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.bookings', ['filter' => 'all']) }}">
            All Bookings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $filter === 'upcoming' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.bookings', ['filter' => 'upcoming']) }}">
            <i class="bi bi-hourglass-split me-1"></i> Upcoming
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $filter === 'current' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.bookings', ['filter' => 'current']) }}">
            <i class="bi bi-door-open me-1"></i> Current
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $filter === 'past' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.bookings', ['filter' => 'past']) }}">
            <i class="bi bi-check-circle me-1"></i> Past
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $filter === 'cancelled' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.bookings', ['filter' => 'cancelled']) }}">
            <i class="bi bi-x-circle me-1"></i> Cancelled
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Bookings List -->
  @if($bookings->count() > 0)
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Booking Code</th>
                  <th>Property</th>
                  <th>Room</th>
                  <th>Arrival</th>
                  <th>Departure</th>
                  <th>Nights</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($bookings as $booking)
                <tr>
                  <td>
                    <strong class="text-primary">{{ $booking->bcode }}</strong>
                  </td>
                  <td>{{ $booking->property->name ?? 'N/A' }}</td>
                  <td>
                    <div>
                      <strong>{{ $booking->room->type->name ?? 'N/A' }}</strong>
                      <br>
                      <small class="text-muted">Room {{ $booking->room->number ?? 'N/A' }}</small>
                    </div>
                  </td>
                  <td>{{ \Carbon\Carbon::parse($booking->arrival_date)->format('M d, Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}</td>
                  <td>{{ $booking->nights }}</td>
                  <td>{{ tenant_currency() }} {{ number_format($booking->total_amount, 2) }}</td>
                  <td>
                    @php
                      $statusBadges = [
                        'pending' => 'secondary',
                        'confirmed' => 'warning',
                        'checked_in' => 'success',
                        'checked_out' => 'info',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        'no_show' => 'dark',
                      ];
                      $badgeClass = $statusBadges[$booking->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badgeClass }}">
                      {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
                         class="btn btn-sm btn-outline-primary" title="View Details">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="{{ route('tenant.guest-portal.bookings.download', $booking->id) }}" 
                         class="btn btn-sm btn-outline-success" 
                         target="_blank"
                         title="Download Info">
                        <i class="bi bi-download"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-3">
            {{ $bookings->appends(['filter' => $filter])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
  @else
  <!-- No Bookings Message -->
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
          <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
          <h4>No Bookings Found</h4>
          <p class="text-muted mb-4">
            @if($filter === 'all')
              You haven't made any bookings yet.
            @elseif($filter === 'upcoming')
              You don't have any upcoming bookings.
            @elseif($filter === 'current')
              You don't have any current stays.
            @elseif($filter === 'past')
              You don't have any past bookings.
            @else
              No bookings found with this filter.
            @endif
          </p>
          <a href="{{ route('tenant.guest-portal.booking') }}" class="btn btn-success btn-lg">
            <i class="bi bi-calendar-plus me-2"></i>
            Make a Booking
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
