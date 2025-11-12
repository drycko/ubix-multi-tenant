@extends('tenant.layouts.guest')

@section('title', 'Guest Login')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-lg border-0">
        <div class="card-body p-5">
          <!-- Header -->
          <div class="text-center mb-4">
            <i class="bi bi-person-circle display-3 text-success mb-3"></i>
            <h3 class="fw-bold text-success">Welcome Back!</h3>
            <p class="text-muted">Sign in to access your bookings</p>
          </div>

          <!-- Success Message -->
          @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif

          <!-- Error Messages -->
          @if($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif

          <!-- Login Form -->
          <form method="POST" action="{{ route('tenant.guest-portal.send-login-link') }}">
            @csrf
            
            <div class="mb-4">
              <label for="email" class="form-label">
                <i class="bi bi-envelope me-2"></i>Email Address
              </label>
              <input 
                type="email" 
                class="form-control form-control-lg @error('email') is-invalid @enderror" 
                id="email" 
                name="email" 
                placeholder="Enter your email address"
                value="{{ old('email') }}" 
                required 
                autofocus>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">
                <i class="bi bi-info-circle me-1"></i>
                We'll send you a secure login link to your email
              </div>
            </div>

            <div class="d-grid gap-2 mb-4">
              <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-envelope-paper me-2"></i>
                Send Login Link
              </button>
            </div>
          </form>

          <hr class="my-4">

          <!-- Additional Info -->
          <div class="text-center">
            <p class="text-muted mb-3">
              <small>
                <i class="bi bi-shield-check me-1"></i>
                We use passwordless authentication for your security
              </small>
            </p>
            <p class="mb-0">
              <small>
                Don't have a booking yet? 
                <a href="{{ route('tenant.guest-portal.index') }}" class="text-success fw-bold">
                  Make a Booking
                </a>
              </small>
            </p>
          </div>
        </div>
      </div>

      <!-- Help Section -->
      <div class="card shadow-sm border-0 mt-4">
        <div class="card-body text-center">
          <h6 class="mb-3">
            <i class="bi bi-question-circle me-2"></i>
            How does it work?
          </h6>
          <ol class="text-start small text-muted">
            <li class="mb-2">Enter your email address (the one you used for booking)</li>
            <li class="mb-2">Click "Send Login Link"</li>
            <li class="mb-2">Check your email for the secure login link</li>
            <li class="mb-0">Click the link to access your bookings</li>
          </ol>
          <p class="text-muted mb-0 mt-3">
            <small>
              <i class="bi bi-clock me-1"></i>
              The login link expires in 30 minutes
            </small>
          </p>
        </div>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-4">
        <a href="{{ route('tenant.guest-portal.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i>
          Back to Home
        </a>
      </div>
    </div>
  </div>
</div>

<style>
.card {
  border-radius: 15px;
}

.form-control:focus {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-success {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
}

.btn-success:hover {
  background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  transition: all 0.3s ease;
}
</style>
@endsection
