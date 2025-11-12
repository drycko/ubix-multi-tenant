@extends('tenant.layouts.guest')

@section('title', 'Guest Portal Dashboard')

@section('content')
<div class="container-fluid py-5">
  <!-- Welcome Header -->
  <div class="row mb-4">
    <div class="col-12 text-center">
      <h2 class="fw-bold text-success mb-2">
        <i class="bi bi-house-heart me-2"></i>
        Welcome, {{ $guest->full_name ?? $guest->email }}!
      </h2>
      <p class="lead text-muted">Manage your bookings and explore {{ current_tenant()->name }} Guest Portal</p>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="row g-3 mb-5">
    <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-calendar-check display-4 text-primary mb-2"></i>
          <h3 class="mb-0">{{ $stats['total_bookings'] }}</h3>
          <p class="text-muted mb-0">Total Bookings</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-hourglass-split display-4 text-warning mb-2"></i>
          <h3 class="mb-0">{{ $stats['upcoming'] }}</h3>
          <p class="text-muted mb-0">Upcoming</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-door-open display-4 text-success mb-2"></i>
          <h3 class="mb-0">{{ $stats['current'] }}</h3>
          <p class="text-muted mb-0">Current Stay</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <i class="bi bi-currency-dollar display-4 text-danger mb-2"></i>
          <h3 class="mb-0">{{ tenant_currency() }} {{ number_format($stats['pending_payments'], 2) }}</h3>
          <p class="text-muted mb-0">Pending Payments</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Cards -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-body text-center">
          <i class="bi bi-calendar2-plus display-4 text-success mb-3"></i>
          <h5 class="card-title">Book a Room</h5>
          <p class="card-text">Find available rooms and make a new booking.</p>
          <a href="{{ route('tenant.guest-portal.booking') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Book Now
          </a>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-body text-center">
          <i class="bi bi-list-check display-4 text-primary mb-3"></i>
          <h5 class="card-title">My Bookings</h5>
          <p class="card-text">View and manage all your bookings.</p>
          <a href="{{ route('tenant.guest-portal.bookings') }}" class="btn btn-primary">
            <i class="bi bi-eye me-1"></i> View Bookings
          </a>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-body text-center">
          <i class="bi bi-door-open display-4 text-warning mb-3"></i>
          <h5 class="card-title">Check-In / Check-Out</h5>
          <p class="card-text">Complete check-in or check-out online.</p>
          <a href="{{ route('tenant.guest-portal.checkin') }}" class="btn btn-warning">
            <i class="bi bi-box-arrow-in-right me-1"></i> Check-In/Out
          </a>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-body text-center">
          <i class="bi bi-receipt display-4 text-info mb-3"></i>
          <h5 class="card-title">My Invoices</h5>
          <p class="card-text">View and download your invoices.</p>
          <a href="{{ route('tenant.guest-portal.invoices') }}" class="btn btn-info text-white">
            <i class="bi bi-file-text me-1"></i> View Invoices
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Upcoming Bookings -->
  @if($upcomingBookings->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-white">
          <h5 class="mb-0">
            <i class="bi bi-calendar-event me-2"></i>
            Upcoming Bookings
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Booking Code</th>
                  <th>Room</th>
                  <th>Arrival</th>
                  <th>Departure</th>
                  <th>Nights</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($upcomingBookings->take(5) as $booking)
                <tr>
                  <td><strong>{{ $booking->bcode }}</strong></td>
                  <td>{{ $booking->room->type->name ?? 'N/A' }} - {{ $booking->room->number ?? 'N/A' }}</td>
                  <td>{{ \Carbon\Carbon::parse($booking->arrival_date)->format('M d, Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}</td>
                  <td>{{ $booking->nights }}</td>
                  <td>
                    <span class="badge bg-warning">{{ ucfirst($booking->status) }}</span>
                  </td>
                  <td>
                    <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
                       class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-eye"></i> View
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @if($upcomingBookings->count() > 5)
          <div class="text-center mt-3">
            <a href="{{ route('tenant.guest-portal.bookings', ['filter' => 'upcoming']) }}" 
               class="btn btn-outline-warning">
              View All Upcoming Bookings <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Current Bookings -->
  @if($currentBookings->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">
            <i class="bi bi-door-open me-2"></i>
            Current Stay
          </h5>
        </div>
        <div class="card-body">
          @foreach($currentBookings as $booking)
          <div class="alert alert-success border-0 d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill fs-3 me-3"></i>
            <div class="flex-grow-1">
              <h6 class="mb-1"><strong>{{ $booking->bcode }}</strong> - {{ $booking->room->type->name ?? 'N/A' }} {{ $booking->room->number ?? 'N/A' }}</h6>
              <p class="mb-0">
                Check-out: {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }} 
                ({{ \Carbon\Carbon::parse($booking->departure_date)->diffForHumans() }})
              </p>
            </div>
            <div>
              <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
                 class="btn btn-sm btn-success">
                View Details
              </a>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Active Digital Keys -->
  @if($activeKeys->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">
            <i class="bi bi-key me-2"></i>
            Active Digital Keys
          </h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            @foreach($activeKeys as $key)
            <div class="col-md-6">
              <div class="card border-info">
                <div class="card-body">
                  <h6 class="card-title">
                    <i class="bi bi-door-closed me-2"></i>
                    Room {{ $key->room->number ?? 'N/A' }}
                  </h6>
                  <p class="mb-2"><strong>Key Code:</strong> <code class="fs-5">{{ $key->key_code }}</code></p>
                  <p class="mb-2 text-muted">
                    <small>
                      <i class="bi bi-clock"></i>
                      Expires: {{ \Carbon\Carbon::parse($key->expires_at)->format('M d, Y H:i') }}
                    </small>
                  </p>
                  <p class="mb-0 text-muted">
                    <small>Booking: {{ $key->booking->bcode ?? 'N/A' }}</small>
                  </p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Pending Invoices -->
  @if($pendingInvoices->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-danger text-white">
          <h5 class="mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Pending Invoices
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Invoice #</th>
                  <th>Booking</th>
                  <th>Amount</th>
                  <th>Paid</th>
                  <th>Balance</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pendingInvoices as $invoice)
                <tr>
                  <td><strong>{{ $invoice->invoice_number }}</strong></td>
                  <td>{{ $invoice->booking->bcode ?? 'N/A' }}</td>
                  <td>{{ tenant_currency() }} {{ number_format($invoice->amount, 2) }}</td>
                  <td>{{ tenant_currency() }} {{ number_format($invoice->total_paid, 2) }}</td>
                  <td class="text-danger">
                    <strong>{{ tenant_currency() }} {{ number_format($invoice->remaining_balance, 2) }}</strong>
                  </td>
                  <td>
                    <span class="badge bg-{{ $invoice->status === 'overdue' ? 'danger' : 'warning' }}">
                      {{ ucfirst($invoice->status) }}
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
          <div class="text-center mt-3">
            <a href="{{ route('tenant.guest-portal.invoices', ['status' => 'pending']) }}" 
               class="btn btn-outline-danger">
              View All Pending Invoices <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Recent Feedback -->
  @if($recentFeedback->count() > 0)
  <div class="row mb-5">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>
            Your Recent Reviews
          </h5>
        </div>
        <div class="card-body">
          @foreach($recentFeedback as $feedback)
          <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">{{ $feedback->booking->room->number ?? 'Room' }} - {{ $feedback->booking->bcode }}</h6>
                <div class="mb-2">
                  @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= $feedback->rating ? '-fill' : '' }} text-warning"></i>
                  @endfor
                </div>
                <p class="mb-0 text-muted">{{ Str::limit($feedback->feedback, 150) }}</p>
              </div>
              <small class="text-muted">{{ \Carbon\Carbon::parse($feedback->submitted_at)->format('M d, Y') }}</small>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- No Bookings Message -->
  @if($stats['total_bookings'] === 0)
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
          <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
          <h4>No Bookings Yet</h4>
          <p class="text-muted mb-4">Start your journey by booking a room with us!</p>
          <a href="{{ route('tenant.guest-portal.booking') }}" class="btn btn-success btn-lg">
            <i class="bi bi-calendar-plus me-2"></i>
            Make Your First Booking
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection

