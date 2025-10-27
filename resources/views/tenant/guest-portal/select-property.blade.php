@extends('tenant.layouts.guest')

@section('title', 'Guest Portal Dashboard')

@section('content')
<div class="row mb-4">
  <div class="col-12 text-center">
    <h2 class="fw-bold text-success mb-2">Welcome to the {{ config('app.name') }} Booking</h2>
    <p class="lead">Please select a property to continue.</p>
  </div>
</div>
<div class="row g-4 justify-content-center">
  @foreach($properties as $property)
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center">
        <i class="bi bi-building display-4 text-success mb-3"></i>
        <h5 class="card-title">{{ $property->name }}</h5>
        <p class="card-text">{{ $property->address }}</p>
        <a href="{{ route('tenant.guest-portal.select-property', $property->id) }}" class="btn btn-success">Select Property</a>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection