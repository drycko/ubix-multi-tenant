@extends('tenant.layouts.guest')

@section('title', 'Leave a Review')

@section('content')
<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="fw-bold text-success mb-2">
            <i class="bi bi-star me-2"></i>
            Leave a Review
          </h2>
          <p class="text-muted mb-0">Share your experience with us</p>
        </div>
        <div>
          <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Booking
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <!-- Booking Summary Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">
            <i class="bi bi-info-circle me-2"></i>
            Booking Details
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <p class="mb-2"><strong>Booking Code:</strong> {{ $booking->bcode }}</p>
              <p class="mb-2"><strong>Room:</strong> {{ $booking->room->type->name ?? 'N/A' }} - Room {{ $booking->room->number ?? 'N/A' }}</p>
              <p class="mb-0"><strong>Property:</strong> {{ $booking->property->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
              <p class="mb-2"><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($booking->arrival_date)->format('M d, Y') }}</p>
              <p class="mb-2"><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}</p>
              <p class="mb-0"><strong>Nights:</strong> {{ $booking->nights }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Review Form -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">
            <i class="bi bi-pencil-square me-2"></i>
            Your Feedback
          </h5>
        </div>
        <div class="card-body">
          @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <form action="{{ route('tenant.guest-portal.bookings.review.submit', $booking->id) }}" method="POST">
            @csrf

            <!-- Rating -->
            <div class="mb-4">
              <label class="form-label"><strong>Overall Rating</strong> <span class="text-danger">*</span></label>
              <div class="rating-stars" id="rating-container">
                <input type="hidden" name="rating" id="rating-value" value="{{ old('rating', 0) }}">
                @for($i = 1; $i <= 5; $i++)
                <i class="bi bi-star rating-star fs-1" data-rating="{{ $i }}" style="cursor: pointer; color: #ccc;"></i>
                @endfor
              </div>
              <small class="text-muted d-block mt-2">Click on the stars to rate your experience</small>
              @error('rating')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>

            <!-- Feedback Text -->
            <div class="mb-4">
              <label for="feedback" class="form-label"><strong>Your Review</strong> <span class="text-danger">*</span></label>
              <textarea 
                class="form-control @error('feedback') is-invalid @enderror" 
                id="feedback" 
                name="feedback" 
                rows="6" 
                maxlength="1000"
                placeholder="Tell us about your experience... What did you like? What could be improved?"
                required>{{ old('feedback') }}</textarea>
              <div class="form-text">Maximum 1000 characters</div>
              @error('feedback')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Suggested Review Areas -->
            <div class="mb-4">
              <label class="form-label"><strong>Review Guidelines</strong></label>
              <div class="alert alert-light">
                <p class="mb-2">Consider commenting on:</p>
                <ul class="mb-0">
                  <li>Room cleanliness and comfort</li>
                  <li>Staff friendliness and service</li>
                  <li>Facilities and amenities</li>
                  <li>Value for money</li>
                  <li>Overall experience</li>
                </ul>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancel
              </a>
              <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle me-1"></i> Submit Review
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Privacy Notice -->
      <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
          <p class="mb-0 text-muted">
            <i class="bi bi-shield-check me-2"></i>
            <small>
              <strong>Privacy Notice:</strong> Your review may be displayed publicly on our website and marketing materials. 
              Please do not include personal information in your review.
            </small>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const stars = document.querySelectorAll('.rating-star');
  const ratingValue = document.getElementById('rating-value');
  let currentRating = parseInt(ratingValue.value) || 0;

  // Initialize stars based on old value
  updateStars(currentRating);

  stars.forEach(star => {
    // Hover effect
    star.addEventListener('mouseenter', function() {
      const rating = parseInt(this.getAttribute('data-rating'));
      updateStars(rating);
    });

    // Click to set rating
    star.addEventListener('click', function() {
      currentRating = parseInt(this.getAttribute('data-rating'));
      ratingValue.value = currentRating;
      updateStars(currentRating);
    });
  });

  // Reset to current rating on mouse leave
  document.getElementById('rating-container').addEventListener('mouseleave', function() {
    updateStars(currentRating);
  });

  function updateStars(rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.remove('bi-star');
        star.classList.add('bi-star-fill');
        star.style.color = '#ffc107'; // Yellow
      } else {
        star.classList.remove('bi-star-fill');
        star.classList.add('bi-star');
        star.style.color = '#ccc'; // Gray
      }
    });
  }
});
</script>

<style>
.rating-star:hover {
  transform: scale(1.1);
  transition: transform 0.2s;
}
</style>
@endsection
