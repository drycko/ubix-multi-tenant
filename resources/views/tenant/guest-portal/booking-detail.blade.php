@extends('tenant.layouts.guest')

@section('title', 'Booking Details')

@section('content')
<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="fw-bold text-success mb-2">
            <i class="bi bi-file-text me-2"></i>
            Booking Details
          </h2>
          <p class="text-muted mb-0">
            Booking Code: <strong>{{ $booking->bcode }}</strong>
            <span class="ms-3">
              @php
                $statusBadges = [
                  'pending' => 'secondary',
                  'booked' => 'primary',
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
            </span>
          </p>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('tenant.guest-portal.bookings.download', $booking->id) }}" 
             class="btn btn-success" target="_blank">
            <i class="bi bi-download me-1"></i> Download Info
          </a>
          <a href="{{ route('tenant.guest-portal.bookings') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bookings
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Status Alert -->
  @if($booking->status === 'confirmed' && $canCheckIn)
  <div class="row mb-4">
    <div class="col-12">
      <div class="alert alert-success border-0 d-flex align-items-center justify-content-between">
        <div>
          <i class="bi bi-check-circle-fill fs-4 me-3"></i>
          <span>Your booking is confirmed! You can now check in.</span>
        </div>
        <a href="{{ route('tenant.guest-portal.checkin') }}" class="btn btn-success">
          <i class="bi bi-box-arrow-in-right me-1"></i> Check In Now
        </a>
      </div>
    </div>
  </div>
  @elseif($booking->status === 'checked_in' && $canCheckOut)
  <div class="row mb-4">
    <div class="col-12">
      <div class="alert alert-info border-0">
        <i class="bi bi-info-circle-fill me-2"></i>
        You are currently checked in. Enjoy your stay!
      </div>
    </div>
  </div>
  @elseif($booking->status === 'checked_out' && $canReview)
  <div class="row mb-4">
    <div class="col-12">
      <div class="alert alert-warning border-0 d-flex align-items-center justify-content-between">
        <div>
          <i class="bi bi-star fs-4 me-3"></i>
          <span>How was your stay? Share your feedback with us!</span>
        </div>
        <a href="{{ route('tenant.guest-portal.bookings.review', $booking->id) }}" class="btn btn-warning">
          <i class="bi bi-star me-1"></i> Leave a Review
        </a>
      </div>
    </div>
  </div>
  @endif

  <div class="row g-4">
    <!-- Booking Information -->
    <div class="col-lg-8">
      <!-- Package Information (if applicable) -->
      @if($booking->package)
      <div class="alert alert-success border-success mb-4">
        <div class="d-flex align-items-start">
          <i class="bi bi-box-seam fs-3 me-3"></i>
          <div class="flex-grow-1">
            <h5 class="alert-heading mb-2">
              <i class="bi bi-gift me-2"></i>Package: {{ $booking->package->pkg_name }}
            </h5>
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><strong>Duration:</strong> {{ $booking->package->pkg_number_of_nights }} nights</p>
                <p class="mb-1"><strong>Base Price:</strong> {{ tenant_currency() }} {{ number_format($booking->package->pkg_base_price, 2) }}</p>
              </div>
              <div class="col-md-6">
                @if($booking->package->pkg_description)
                <p class="mb-0"><small>{{ strip_tags($booking->package->pkg_description) }}</small></p>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Room Details -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">
            <i class="bi bi-door-closed me-2"></i>
            Room Information
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <p><strong>Room Type:</strong> {{ $booking->room->type->name ?? 'N/A' }}</p>
              <p><strong>Room Number:</strong> {{ $booking->room->number ?? 'N/A' }}</p>
              <p><strong>Property:</strong> {{ $booking->property->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Arrival Date:</strong> {{ \Carbon\Carbon::parse($booking->arrival_date)->format('l, F j, Y') }}</p>
              <p><strong>Departure Date:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('l, F j, Y') }}</p>
              <p><strong>Nights:</strong> {{ $booking->nights }}</p>
              <p><strong>Booking Type:</strong> {{ $booking->is_shared ? 'Shared Room' : 'Private Room' }}</p>
            </div>
          </div>
          
          @if($booking->room->type->amenities_with_details && $booking->room->type->amenities_with_details->count() > 0)
          <hr>
          <h6 class="mb-3"><strong>Room Amenities:</strong></h6>
          <div class="d-flex flex-wrap gap-2">
            @foreach($booking->room->type->amenities_with_details as $amenity)
            <span class="badge bg-light text-dark border">
              <i class="bi bi-check2 text-success me-1"></i>
              {{ $amenity->name }}
            </span>
            @endforeach
          </div>
          @endif
        </div>
      </div>

      <!-- Guest Information -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">
            <i class="bi bi-people me-2"></i>
            Guest Information
          </h5>
        </div>
        <div class="card-body">
          @if($booking->bookingGuests && $booking->bookingGuests->count() > 0)
          @foreach($booking->bookingGuests as $index => $bookingGuest)
          <div class="mb-3 {{ $loop->last ? '' : 'pb-3 border-bottom' }}">
            <h6 class="text-primary mb-2">
              {{ $bookingGuest->is_primary ? '👤 Primary Guest' : '👥 Guest ' . ($index + 1) }}
            </h6>
            <div class="row">
              <div class="col-md-6">
                <p class="mb-1"><strong>Name:</strong> {{ $bookingGuest->guest->first_name }} {{ $bookingGuest->guest->last_name }}</p>
                @if($bookingGuest->guest->email)
                <p class="mb-1"><strong>Email:</strong> {{ $bookingGuest->guest->email }}</p>
                @endif
                @if($bookingGuest->guest->phone)
                <p class="mb-1"><strong>Phone:</strong> {{ $bookingGuest->guest->phone }}</p>
                @endif
              </div>
              <div class="col-md-6">
                @if($bookingGuest->guest->nationality)
                <p class="mb-1"><strong>Nationality:</strong> {{ $bookingGuest->guest->nationality }}</p>
                @endif
                <p class="mb-1"><strong>Adults:</strong> {{ $bookingGuest->count_adults ?? 1 }}</p>
                <p class="mb-1"><strong>Children:</strong> {{ $bookingGuest->count_children ?? 0 }}</p>
              </div>
            </div>
            @if($bookingGuest->special_requests)
            <div class="alert alert-warning mt-2 mb-0">
              <small><strong>Special Requests:</strong></small><br>
              <small>{{ $bookingGuest->special_requests }}</small>
            </div>
            @endif
          </div>
          @endforeach
          @else
          <p class="mb-0 text-muted">No guest information available.</p>
          @endif
        </div>
      </div>

      <!-- Invoices -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-warning text-white">
          <h5 class="mb-0">
            <i class="bi bi-receipt me-2"></i>
            Invoices
          </h5>
        </div>
        <div class="card-body">
          @if($booking->invoices && $booking->invoices->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Invoice #</th>
                  <th>Amount</th>
                  <th>Paid</th>
                  <th>Balance</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($booking->invoices as $invoice)
                <tr>
                  <td><strong>{{ $invoice->invoice_number }}</strong></td>
                  <td>{{ tenant_currency() }} {{ number_format($invoice->amount, 2) }}</td>
                  <td>{{ tenant_currency() }} {{ number_format($invoice->total_paid, 2) }}</td>
                  <td class="{{ $invoice->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                    <strong>{{ tenant_currency() }} {{ number_format($invoice->remaining_balance, 2) }}</strong>
                  </td>
                  <td>
                    @php
                      $statusBadges = [
                        'pending' => 'warning',
                        'partially_paid' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'secondary',
                      ];
                      $badgeClass = $statusBadges[$invoice->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badgeClass }}">
                      {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                  </td>
                  <td>
                    <a href="{{ route('tenant.guest-portal.invoices.show', $invoice->id) }}" 
                       class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-eye"></i> View
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <p class="mb-0">No invoices found for this booking.</p>
          @endif
        </div>
      </div>

      <!-- Digital Keys -->
      @if($booking->digitalKeys && $booking->digitalKeys->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <i class="bi bi-key me-2"></i>
            Digital Keys
          </h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            @foreach($booking->digitalKeys as $key)
            <div class="col-md-6">
              <div class="card border-primary">
                <div class="card-body">
                  <h6 class="card-title">Room {{ $key->room->number ?? 'N/A' }}</h6>
                  <p class="mb-2">
                    <strong>Key Code:</strong> 
                    <code class="fs-5">{{ $key->key_code }}</code>
                  </p>
                  <p class="mb-2 text-muted">
                    <small>
                      <i class="bi bi-clock"></i>
                      Issued: {{ \Carbon\Carbon::parse($key->issued_at)->format('M d, Y H:i') }}
                    </small>
                  </p>
                  <p class="mb-0 text-muted">
                    <small>
                      <i class="bi bi-calendar-x"></i>
                      Expires: {{ \Carbon\Carbon::parse($key->expires_at)->format('M d, Y H:i') }}
                    </small>
                  </p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>

    <!-- Sidebar: Actions & Summary -->
    <div class="col-lg-4">
      <!-- Booking Summary -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>
            Booking Summary
          </h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span>Status:</span>
            <span>
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
            </span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Daily Rate:</span>
            <strong>{{ tenant_currency() }} {{ number_format($booking->daily_rate, 2) }}</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Nights:</span>
            <strong>{{ $booking->nights }}</strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <span><strong>Total Amount:</strong></span>
            <strong class="text-success fs-5">{{ tenant_currency() }} {{ number_format($booking->total_amount, 2) }}</strong>
          </div>
          <hr>
          <small class="text-muted">
            <i class="bi bi-calendar-plus"></i>
            Booked on: {{ \Carbon\Carbon::parse($booking->created_at)->format('M d, Y H:i') }}
          </small>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">
            <i class="bi bi-gear me-2"></i>
            Actions
          </h5>
        </div>
        <div class="card-body d-grid gap-2">
          <!-- Download/Print Booking Info -->
          <a href="{{ route('tenant.guest-portal.bookings.download', $booking->id) }}" 
             class="btn btn-outline-success" target="_blank">
            <i class="bi bi-download me-2"></i>
            Download Booking Info
          </a>
          
          @if($canCheckIn)
          <a href="{{ route('tenant.guest-portal.checkin') }}" class="btn btn-success">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            Check In
          </a>
          @endif
          
          @if($canCheckOut)
          <form action="{{ route('tenant.guest-portal.checkout.submit', $booking->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-info w-100" 
                    onclick="return confirm('Are you sure you want to check out?')">
              <i class="bi bi-box-arrow-right me-2"></i>
              Check Out
            </button>
          </form>
          @endif
          
          @if($canReview)
          <a href="{{ route('tenant.guest-portal.bookings.review', $booking->id) }}" class="btn btn-warning">
            <i class="bi bi-star me-2"></i>
            Leave a Review
          </a>
          @endif
          
          @if(in_array($booking->status, ['pending', 'confirmed']) && $booking->arrival_date > now()->format('Y-m-d'))
          <form action="{{ route('tenant.guest-portal.bookings.cancel', $booking->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger w-100" 
                    onclick="return confirm('Are you sure you want to cancel this booking?')">
              <i class="bi bi-x-circle me-2"></i>
              Cancel Booking
            </button>
          </form>
          @endif
        </div>
      </div>

      <!-- Contact Support -->
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <i class="bi bi-headset display-4 text-muted mb-3"></i>
          <h6>Need Help?</h6>
          <p class="text-muted">Contact our support team for assistance.</p>
          <a href="{{ route('tenant.guest-portal.requests') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-chat-dots me-1"></i>
            Submit a Request
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
