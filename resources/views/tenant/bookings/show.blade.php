@extends('tenant.layouts.app')

@section('title', 'Booking Details - ' . $booking->bcode)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="bi bi-info-circle"></i> Booking Details
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $booking->bcode }}</li>
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
    
    {{-- Success/Error Messages --}}
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

    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <!-- Header Card with Actions -->
    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">
              Booking: {{ $booking->bcode }}
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
              <span class="badge bg-{{ $statusColor }} ms-2">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
            </h5><br>
            <small class="text-muted">Created {{ $booking->created_at->format('M d, Y \a\t g:i A') }}</small>
          </div>
          <div class="btn-group" role="group">
            <a href="{{ route('tenant.bookings.edit', $booking) }}" class="btn btn-warning">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-cog me-1"></i>Actions
              </button>
              <ul class="dropdown-menu">
                <li>
                  <form action="{{ route('tenant.bookings.clone', $booking) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item" onclick="return confirm('Clone this booking?')">
                      <i class="fas fa-copy me-2"></i>Clone Booking
                    </button>
                  </form>
                </li>
                <li>
                  <a href="{{ route('tenant.bookings.download-room-info', $booking) }}" class="dropdown-item">
                    <i class="fas fa-download me-2"></i>Download Info
                  </a>
                </li>
                <li>
                  <a href="{{ route('tenant.bookings.send-room-info', $booking) }}" class="dropdown-item">
                    <i class="fas fa-paper-plane me-2"></i>Send Info
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('tenant.bookings.toggle-status', $booking) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item" onclick="return confirm('Toggle booking status?')">
                      <i class="fas fa-toggle-on me-2"></i>
                      {{ $booking->status === 'cancelled' ? 'Reactivate' : 'Cancel' }}
                    </button>
                  </form>
                </li>
              </ul>
            </div>
            <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">
    
        <div class="row">
          <!-- Booking Information -->
          <div class="col-md-6">
            <div class="card card-info card-outline mb-4">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="fas fa-calendar-check me-2"></i>Booking Information
                </h5>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-sm-6">
                    <strong>Booking Code:</strong><br>
                    <span class="text-primary">{{ $booking->bcode }}</span>
                  </div>
                  <div class="col-sm-6">
                    <strong>Source:</strong><br>
                    <span class="badge bg-info">{{ ucfirst($booking->source) }}</span>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-sm-6">
                    <strong>Arrival Date:</strong><br>
                    {{ $booking->arrival_date->format('M d, Y') }}
                    <small class="text-muted d-block">{{ $booking->arrival_date->format('l') }}</small>
                  </div>
                  <div class="col-sm-6">
                    <strong>Departure Date:</strong><br>
                    {{ $booking->departure_date->format('M d, Y') }}
                    <small class="text-muted d-block">{{ $booking->departure_date->format('l') }}</small>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-sm-6">
                    <strong>Duration:</strong><br>
                    {{ $booking->nights }} {{ $booking->nights == 1 ? 'night' : 'nights' }}
                  </div>
                  <div class="col-sm-6">
                    <strong>Shared Room:</strong><br>
                    <span class="badge bg-{{ $booking->is_shared ? 'success' : 'secondary' }}">
                      {{ $booking->is_shared ? 'Yes' : 'No' }}
                    </span>
                  </div>
                </div>
                @if($booking->package)
                <div class="mb-3">
                  <strong>Package:</strong><br>
                  <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2">{{ $booking->package->pkg_name }}</span>
                    <small class="text-muted">{{ $booking->package->pkg_number_of_nights }} nights package</small>
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div>
          
          <!-- Room Information -->
          <div class="col-md-6">
            <div class="card card-warning card-outline mb-4">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="fas fa-bed me-2"></i>Room Information
                </h5>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-sm-6">
                    <strong>Room Number:</strong><br>
                    <span class="h5 text-primary">{{ str_pad($booking->room->number, 3, '0', STR_PAD_LEFT) }}</span>
                  </div>
                  <div class="col-sm-6">
                    <strong>Room Type:</strong><br>
                    {{ $booking->room->type->name }}
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-sm-6">
                    <strong>Daily Rate:</strong><br>
                    <span class="h6 text-success">{{ $currency }} {{ number_format($booking->daily_rate, 2) }}</span>
                  </div>
                  <div class="col-sm-6">
                    <strong>Total Amount:</strong><br>
                    <span class="h5 text-success">{{ $currency }} {{ number_format($booking->total_amount, 2) }}</span>
                  </div>
                </div>
                @if($booking->room->description)
                <div class="mb-3">
                  <strong>Room Description:</strong><br>
                  <small class="text-muted">{{ Str::limit($booking->room->description, 100) }}</small>
                </div>
                @endif
              </div>
              <div class="card-footer">
                <div class="d-flex gap-2">
                  <a href="{{ route('tenant.bookings.download-room-info', $booking) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download me-1"></i>Download Info
                  </a>
                  <a href="{{ route('tenant.bookings.send-room-info', $booking) }}" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-paper-plane me-1"></i>Send Info
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Guest Information -->
        <div class="card card-success card-outline mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-users me-2"></i>Guest Information
            </h5>
          </div>
          <div class="card-body">
            @if($primaryGuest)
            <div class="row">
              <div class="col-md-6">
                <h6 class="text-primary mb-3">
                  <i class="fas fa-user me-1"></i>Primary Guest
                </h6>
                <div class="mb-2">
                  <strong>Name:</strong> {{ $primaryGuest->first_name }} {{ $primaryGuest->last_name }}
                </div>
                @if($primaryGuest->email)
                <div class="mb-2">
                  <strong>Email:</strong> 
                  <a href="mailto:{{ $primaryGuest->email }}">{{ $primaryGuest->email }}</a>
                </div>
                @endif
                @if($primaryGuest->phone)
                <div class="mb-2">
                  <strong>Phone:</strong> 
                  <a href="tel:{{ $primaryGuest->phone }}">{{ $primaryGuest->phone }}</a>
                </div>
                @endif
                @if($primaryGuest->nationality)
                <div class="mb-2">
                  <strong>Nationality:</strong> {{ $primaryGuest->nationality }}
                </div>
                @endif
              </div>
              <div class="col-md-6">
                @php
                  $primaryBookingGuest = $booking->bookingGuests->where('is_primary', true)->first();
                @endphp
                @if($primaryBookingGuest)
                <h6 class="text-info mb-3">
                  <i class="fas fa-info-circle me-1"></i>Booking Details
                </h6>
                <div class="mb-2">
                  <strong>Adults:</strong> {{ $primaryBookingGuest->adults ?? 1 }}
                </div>
                <div class="mb-2">
                  <strong>Children:</strong> {{ $primaryBookingGuest->children ?? 0 }}
                </div>
                @if($primaryGuest->special_requests)
                <div class="card mb-2 p-2">
                  <strong>Special Requests:</strong><hr>
                  <div >
                    {{ $primaryGuest->special_requests }}
                  </div>
                </div>
                @endif
                @endif
              </div>
            </div>
            
            @if($secondaryGuest)
            <hr>
            <div class="row">
              <div class="col-md-6">
                <h6 class="text-secondary mb-3">
                  <i class="fas fa-user-plus me-1"></i>Secondary Guest
                </h6>
                <div class="mb-2">
                  <strong>Name:</strong> {{ $secondaryGuest->first_name }} {{ $secondaryGuest->last_name }}
                </div>
                @if($secondaryGuest->email)
                <div class="mb-2">
                  <strong>Email:</strong> 
                  <a href="mailto:{{ $secondaryGuest->email }}">{{ $secondaryGuest->email }}</a>
                </div>
                @endif
                @if($secondaryGuest->phone)
                <div class="mb-2">
                  <strong>Phone:</strong> 
                  <a href="tel:{{ $secondaryGuest->phone }}">{{ $secondaryGuest->phone }}</a>
                </div>
                @endif
                @if($secondaryGuest->nationality)
                <div class="mb-2">
                  <strong>Nationality:</strong> {{ $secondaryGuest->nationality }}
                </div>
                @endif
              </div>
              <div class="col-md-6">
                @php
                  $secondaryBookingGuest = $booking->bookingGuests->where('is_primary', false)->first();
                @endphp
                @if($secondaryBookingGuest && $secondaryGuest->special_requests)
                <h6 class="text-info mb-3">
                  <i class="fas fa-info-circle me-1"></i>Additional Notes
                </h6>
                <div class="card mb-2 p-2">
                  <strong>Special Requests:</strong><hr>
                  <div >
                    {{ $secondaryGuest->special_requests }}
                  </div>
                </div>
                @endif
              </div>
            </div>
            @endif
            @else
            <div class="text-center py-4">
              <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
              <h5 class="text-muted">No guest information available</h5>
              <p class="text-muted">Guest details have not been assigned to this booking.</p>
            </div>
            @endif
          </div>
        </div>

        <!-- Invoice Information -->
        @if($booking->invoices->isNotEmpty())
        <div class="card card-info card-outline mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-file-invoice me-2"></i>Invoice Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($booking->invoices as $invoice)
              <div class="col-md-6 mb-3">
                <div class="border p-3 rounded">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong>Invoice #{{ $invoice->invoice_number }}</strong>
                    @php
                      $invoiceStatusColors = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'info'
                      ];
                      $invoiceStatusColor = $invoiceStatusColors[$invoice->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $invoiceStatusColor }}">{{ ucfirst($invoice->status) }}</span>
                  </div>
                  <div class="mb-2">
                    <strong>Amount:</strong> {{ $currency }} {{ number_format($invoice->amount, 2) }}
                    @if($invoice->tax_amount > 0)
                    <div class="small text-muted mt-1">
                      <div>Subtotal: {{ $currency }} {{ $invoice->formatted_subtotal }}</div>
                      <div>{{ $invoice->tax_name }} ({{ $invoice->tax_type === 'percentage' ? $invoice->tax_rate . '%' : $currency . ' ' . number_format($invoice->tax_rate, 2) }}): {{ $currency }} {{ $invoice->formatted_tax_amount }}</div>
                      @if($invoice->tax_inclusive)
                      <div class="text-info"><small><i class="fas fa-info-circle me-1"></i>Tax inclusive</small></div>
                      @endif
                    </div>
                    @endif
                  </div>
                  <div class="mb-2">
                    <strong>Created:</strong> {{ $invoice->created_at->format('M d, Y') }}
                  </div>
                  <a href="{{ route('tenant.booking-invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>View Invoice
                  </a>
                </div>
              </div>
              @endforeach
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

@endsection