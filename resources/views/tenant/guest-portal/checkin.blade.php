@extends('tenant.layouts.guest')

@section('title', 'Check-In / Check-Out')

@section('content')
<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="fw-bold text-success mb-2">
            <i class="bi bi-door-open me-2"></i>
            Check-In / Check-Out
          </h2>
          <p class="text-muted mb-0">Complete your check-in or check-out process</p>
        </div>
        <div>
          <a href="{{ route('tenant.guest-portal.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i> Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Check-In Section -->
  @if($checkInBookings->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            Ready for Check-In
          </h5>
        </div>
        <div class="card-body">
          <p class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Welcome! You can now complete your check-in online. Once checked in, you'll receive a digital key to access your room.
          </p>

          @foreach($checkInBookings as $booking)
          <div class="card mb-3 border-success">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-lg-8">
                  <h5 class="card-title mb-3">
                    <strong>{{ $booking->bcode }}</strong> - {{ $booking->room->type->name ?? 'N/A' }}
                  </h5>
                  <div class="row">
                    <div class="col-md-6">
                      <p class="mb-2">
                        <i class="bi bi-door-closed me-2 text-success"></i>
                        <strong>Room:</strong> {{ $booking->room->number ?? 'N/A' }}
                      </p>
                      <p class="mb-2">
                        <i class="bi bi-building me-2 text-success"></i>
                        <strong>Property:</strong> {{ $booking->property->name ?? 'N/A' }}
                      </p>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-2">
                        <i class="bi bi-calendar-check me-2 text-success"></i>
                        <strong>Check-in:</strong> {{ \Carbon\Carbon::parse($booking->arrival_date)->format('M d, Y') }}
                      </p>
                      <p class="mb-2">
                        <i class="bi bi-calendar-x me-2 text-success"></i>
                        <strong>Check-out:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}
                      </p>
                      <p class="mb-0">
                        <i class="bi bi-moon-stars me-2 text-success"></i>
                        <strong>Nights:</strong> {{ $booking->nights }}
                      </p>
                    </div>
                  </div>

                  @if($booking->guests && $booking->guests->count() > 0)
                  <hr>
                  <p class="mb-2"><strong>Guests:</strong></p>
                  <ul class="mb-0">
                    @foreach($booking->guests as $bookingGuest)
                    <li>{{ $bookingGuest->full_name }}</li>
                    @endforeach
                  </ul>
                  @endif
                </div>
                <div class="col-lg-4 text-center">
                  <form action="{{ route('tenant.guest-portal.checkin.submit', $booking->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg w-100 mb-2" 
                            onclick="return confirm('Confirm check-in for this booking?')">
                      <i class="bi bi-check-circle me-2"></i>
                      Check In Now
                    </button>
                  </form>
                  <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
                     class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> View Booking Details
                  </a>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Check-Out Section -->
  @if($checkOutBookings->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">
            <i class="bi bi-box-arrow-right me-2"></i>
            Current Stay - Ready for Check-Out
          </h5>
        </div>
        <div class="card-body">
          <p class="alert alert-warning">
            <i class="bi bi-info-circle me-2"></i>
            You are currently checked in. When you're ready to leave, please complete the check-out process below.
          </p>

          @foreach($checkOutBookings as $booking)
          <div class="card mb-3 border-info">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-lg-8">
                  <h5 class="card-title mb-3">
                    <strong>{{ $booking->bcode }}</strong> - {{ $booking->room->type->name ?? 'N/A' }}
                  </h5>
                  <div class="row">
                    <div class="col-md-6">
                      <p class="mb-2">
                        <i class="bi bi-door-closed me-2 text-info"></i>
                        <strong>Room:</strong> {{ $booking->room->number ?? 'N/A' }}
                      </p>
                      <p class="mb-2">
                        <i class="bi bi-building me-2 text-info"></i>
                        <strong>Property:</strong> {{ $booking->property->name ?? 'N/A' }}
                      </p>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-2">
                        <i class="bi bi-calendar-x me-2 text-info"></i>
                        <strong>Check-out:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}
                      </p>
                      <p class="mb-0">
                        <i class="bi bi-clock me-2 text-info"></i>
                        <strong>Time Remaining:</strong> 
                        {{ \Carbon\Carbon::parse($booking->departure_date)->diffForHumans() }}
                      </p>
                    </div>
                  </div>

                  @if($booking->digitalKeys && $booking->digitalKeys->count() > 0)
                  <hr>
                  <div class="alert alert-light mb-0">
                    <p class="mb-2"><strong><i class="bi bi-key me-2"></i>Active Digital Keys:</strong></p>
                    <div class="row g-2">
                      @foreach($booking->digitalKeys as $key)
                      <div class="col-md-6">
                        <div class="d-flex align-items-center">
                          <i class="bi bi-door-closed me-2 text-primary"></i>
                          <div>
                            <strong>Room {{ $key->room->number ?? 'N/A' }}:</strong> 
                            <code class="fs-6">{{ $key->key_code }}</code>
                          </div>
                        </div>
                      </div>
                      @endforeach
                    </div>
                  </div>
                  @endif
                </div>
                <div class="col-lg-4 text-center">
                  <form action="{{ route('tenant.guest-portal.checkout.submit', $booking->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info btn-lg w-100 mb-2 text-white" 
                            onclick="return confirm('Confirm check-out? Your digital key will be deactivated.')">
                      <i class="bi bi-box-arrow-right me-2"></i>
                      Check Out Now
                    </button>
                  </form>
                  <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
                     class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> View Booking Details
                  </a>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- No Eligible Bookings -->
  @if($checkInBookings->count() === 0 && $checkOutBookings->count() === 0)
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
          <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
          <h4>No Check-In or Check-Out Available</h4>
          <p class="text-muted mb-4">
            You don't have any bookings that are ready for check-in or check-out at this time.
          </p>
          <a href="{{ route('tenant.guest-portal.bookings') }}" class="btn btn-primary btn-lg me-2">
            <i class="bi bi-list-check me-2"></i>
            View My Bookings
          </a>
          <a href="{{ route('tenant.guest-portal.booking') }}" class="btn btn-success btn-lg">
            <i class="bi bi-calendar-plus me-2"></i>
            Make a Booking
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Instructions Card -->
  <div class="row mt-5">
    <div class="col-lg-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white">
          <h6 class="mb-0">
            <i class="bi bi-info-circle me-2"></i>
            Check-In Instructions
          </h6>
        </div>
        <div class="card-body">
          <ol class="mb-0">
            <li class="mb-2">Review your booking details carefully</li>
            <li class="mb-2">Click "Check In Now" when you're ready</li>
            <li class="mb-2">You'll receive a digital key code</li>
            <li class="mb-0">Use the key code to access your room</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white">
          <h6 class="mb-0">
            <i class="bi bi-info-circle me-2"></i>
            Check-Out Instructions
          </h6>
        </div>
        <div class="card-body">
          <ol class="mb-0">
            <li class="mb-2">Ensure all belongings are packed</li>
            <li class="mb-2">Check for any outstanding payments</li>
            <li class="mb-2">Click "Check Out Now" when ready</li>
            <li class="mb-0">Don't forget to leave a review!</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Support -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <i class="bi bi-headset display-4 text-muted mb-3"></i>
          <h6>Need Assistance?</h6>
          <p class="text-muted mb-3">If you encounter any issues or have questions, our support team is here to help.</p>
          <a href="{{ route('tenant.guest-portal.requests') }}" class="btn btn-outline-primary">
            <i class="bi bi-chat-dots me-1"></i>
            Contact Support
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
