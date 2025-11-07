@extends('tenant.layouts.guest-error')

@section('title', 'Error Page')

@section('content')
<!--Error Section-->
<section class="error-section">
  <div class="auto-container">
    <div class="error-image"><img src="{{ asset('assets/images/undraw_connection-lost_am29.png') }}" alt=""></div>
    <div class="text">{{ $error }}</div>
    
    <div class="mt-4">
      <a href="{{ route('tenant.guest-portal.home') }}" class="theme-btn btn-style-one">
        <span class="btn-title">Back to Home</span>
      </a>
    </div>
  </div>
</section>
<!--Error Section-->
@endsection