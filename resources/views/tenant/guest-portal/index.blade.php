@extends('tenant.layouts.guest')

@section('title', 'Guest Portal Dashboard')

@section('content')
<div class="row mb-4">
  <div class="col-12 text-center">
    <h2 class="fw-bold text-success mb-2">Welcome to the Ubix Guest Portal</h2>
    <p class="lead">Manage your bookings, check-in/out, make requests, and access your digital keys—all in one place.</p>
  </div>
</div>
<div class="row g-4 justify-content-center">
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="bi bi-calendar2-plus display-4 text-success mb-3"></i>
        <h5 class="card-title">Book a Room</h5>
        <p class="card-text">Find available rooms and make a new booking quickly and easily.</p>
        <a href="{{ route('tenant.guest-portal.booking') }}" class="btn btn-success">Book Now</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="bi bi-door-open display-4 text-success mb-3"></i>
        <h5 class="card-title">Check-In / Check-Out</h5>
        <p class="card-text">Complete your check-in or check-out online for a seamless experience.</p>
        <a href="{{ route('tenant.guest-portal.checkin') }}" class="btn btn-success">Check-In/Out</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="bi bi-chat-dots display-4 text-success mb-3"></i>
        <h5 class="card-title">Requests & Feedback</h5>
        <p class="card-text">Request room service, report issues, or leave feedback and reviews.</p>
        <a href="{{ route('tenant.guest-portal.requests') }}" class="btn btn-success">Submit Request</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="bi bi-key display-4 text-success mb-3"></i>
        <h5 class="card-title">Digital Keys</h5>
        <p class="card-text">Access your digital room keys securely and conveniently.</p>
        <a href="{{ route('tenant.guest-portal.keys') }}" class="btn btn-success">View Keys</a>
      </div>
    </div>
  </div>
</div>
@endsection
