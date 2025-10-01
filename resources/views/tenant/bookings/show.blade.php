@extends('tenant.layouts.app')

@section('title', 'Booking Details')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Booking Details</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Booking Details</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->
{{-- <div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Booking Details: {{ $booking->bcode }}</h1>
  <div>
    <a href="{{ route('tenant.bookings.edit', $booking) }}" class="btn btn-warning">
      <i class="fas fa-edit me-2"></i>Edit
    </a>
    <a href="{{ route('tenant.bookings.index') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back to List
    </a>
  </div>
</div> --}}

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    
    {{-- messages from redirect --}}
    @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
    @endif
    
    
    <div class="row">
      <div class="col-md-6">
        <div class="card card-success card-outline mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Booking Information</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-6">
                <strong>Status:</strong><br>
                <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'secondary' }}">
                  {{ ucfirst($booking->status) }}
                </span>
              </div>
              <div class="col-6">
                <strong>Source:</strong><br>
                {{ ucfirst($booking->source) }}
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-6">
                <strong>Arrival:</strong><br>
                {{ $booking->arrival_date->format('M d, Y') }}
              </div>
              <div class="col-6">
                <strong>Departure:</strong><br>
                {{ $booking->departure_date->format('M d, Y') }}
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-6">
                <strong>Nights:</strong><br>
                {{ $booking->nights }}
              </div>
              <div class="col-6">
                <strong>Total Amount:</strong><br>
                {{ number_format($booking->total_amount, 2) }} {{ $currency }}
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-6">
                <strong>Package:</strong><br>
                {{ $booking->package->pkg_name ?? 'No Package' }}
              </div>
              <div class="col-6">
                <strong>Invoice:</strong><br>
                @if($booking->invoices->isEmpty())
                N/A
                @else
                <a href="{{ route('tenant.booking-invoices.show', $booking->invoices->first()->id) }}" class="btn btn-sm btn-primary">
                  <i class="fas fa-file-invoice me-2"></i>Invoice #{{ $booking->invoices->first()->invoice_number }}
                </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card card-success card-outline mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Room Information</h5>
          </div>
          <div class="card-body">
            <strong>Room Number:</strong> {{ $booking->room->number }}<br>
            <strong>Room Type:</strong> {{ $booking->room->type->name }}<br>
            <strong>Daily Rate:</strong> {{ number_format($booking->daily_rate, 2) }} {{ $currency }}<br>
            <strong>Booked On:</strong> {{ $booking->created_at->format('M d, Y h:i A') }}<br>
            <strong>Booking Code:</strong> {{ $booking->bcode }}
          </div>
          <div class="card-footer d-flex">
            <div class="justify-content-start">
              <a href="{{ route('tenant.bookings.download-room-info', $booking->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                <i class="bi bi-download"></i> Download Information
              </a>
            </div>
            <div class="justify-content-end">
              <a href="{{ route('tenant.bookings.send-room-info', $booking->id) }}" target="_blank" class="btn btn-sm btn-outline-success ms-2">
                <i class="bi bi-paper-plane"></i> Send Information
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Guest Information</h5>
      </div>
      <div class="card-body">
        @if($booking->primaryGuest)
        <div class="row">
          <div class="col-md-6">
            <strong>Name:</strong> {{ $booking->primaryGuest->first_name }} {{ $booking->primaryGuest->last_name }}<br>
            <strong>Email:</strong> {{ $booking->primaryGuest->email }}<br>
            <strong>Phone:</strong> {{ $booking->primaryGuest->phone }}<br>
          </div>
          <div class="col-md-6">
            <strong>Adults:</strong> {{ $booking->guests->count() }}<br>
            {{-- get the children count from the children column in booking_guests table on primaryGuest --}}
            <strong>Children:</strong> {{ $booking->primaryGuest->children }}<br>
            <strong>Special Requests:</strong><br>
            {{ $primaryGuest->special_requests ?? 'None' }}
          </div>
        </div>
        {{-- get other guests if any --}}
        @if($booking->guests->count() > 1)
        <hr>
        <h5>Additional Guests</h5>
        <div class="row">
          @foreach($booking->guests->where('id', '!=', $booking->primaryGuest->id) as $guest)
          <div class="col-md-6 mb-3">
            <strong>Name:</strong> {{ $guest->first_name }} {{ $guest->last_name }}<br>
            <strong>Email:</strong> {{ $guest->email }}<br>
            <strong>Phone:</strong> {{ $guest->phone }}<br>
          </div>
          <div class="col-md-6">
            <strong>Adults:</strong> {{ $guest->adults }}<br>
            <strong>Children:</strong> {{ $guest->children }}<br>
            <strong>Special Requests:</strong><br>
            {{ $secondaryGuest->special_requests ?? 'None' }}
          </div>
          @endforeach
        </div>
        {{-- end other guests --}}
        @endif
        @else
        <p>No guest information available.</p>
        @endif
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection