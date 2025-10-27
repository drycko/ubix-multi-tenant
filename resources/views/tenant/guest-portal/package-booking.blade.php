@php
// Pass allowed check-in days and nights from the package to JS
// $package->pkg_checkin_days is a json array stored as string in DB, e.g. '["Wednesday","Sunday"]'
$allowedDays = json_decode($package->pkg_checkin_days, true) ?? ['Monday','Wednesday','Friday']; // Example fallback
$packageNights = $package->pkg_number_of_nights ?? 1;
// Map to 3-letter uppercase codes for JS calendar logic
$dayMap = [
'Sunday' => 'SUN',
'Monday' => 'MON',
'Tuesday' => 'TUE',
'Wednesday' => 'WED',
'Thursday' => 'THU',
'Friday' => 'FRI',
'Saturday' => 'SAT',
];
$allowedWeekdays = array_map(fn($d) => $dayMap[$d] ?? strtoupper(substr($d,0,3)), $allowedDays);
@endphp

@extends('tenant.layouts.guest')

@section('title', 'Book a Package')

{{-- calendar css (for booking form) --}}
<link rel="stylesheet" href="{{ asset('assets/css/calendar.css') }}">

<script>
  // allowedWeekdays: e.g. ["MON","WED","FRI"]
  const allowedWeekdays = @json($allowedWeekdays);
  const packageNights = @json($packageNights);
</script>

<style>
  /* make navigation items equal width */
  #bookingSteps .nav-item {
    flex: 1;
    text-align: center;
    padding: 0 5px;
  }
  /* put borders around the link */
  #bookingSteps .nav-link {
    width: 100%;
    border: 1px solid var(--bs-success); /* success color */
    color: var(--bs-success);
    border-radius: 5px;
  }
  #bookingSteps .nav-link.active {
    background-color: var(--bs-success);
    color: #fff;
  }
</style>

@section('content')
<div class="row mb-4">
  <div class="col-12 text-center">
    <h2 class="fw-bold text-success mb-2">Book a Package</h2>
    <p class="lead">Select your package, dates, room, and guest details to complete your booking.</p>
  </div>
</div>
<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="card shadow-sm">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-gift me-2"></i>Package Booking</h5>
      </div>
      <form action="{{ route('tenant.guest-portal.book') }}" method="POST" id="guestPackageBookingForm">
        @csrf
        <div class="card-body">
          <!-- Step Navigation -->
          <ul class="nav nav-pills mb-4 justify-content-center" id="bookingSteps">
            <li class="nav-item"><a class="nav-link active" href="#step1" data-bs-toggle="pill">1. Select Package</a></li>
            <li class="nav-item"><a class="nav-link" href="#step2" data-bs-toggle="pill">2. Select Room</a></li>
            <li class="nav-item"><a class="nav-link" href="#step3" data-bs-toggle="pill">3. Room & Guests</a></li>
            <li class="nav-item"><a class="nav-link" href="#step4" data-bs-toggle="pill">4. Confirm</a></li>
          </ul>
          <div class="tab-content">
            <!-- Step 1: Select Package -->
            <div class="tab-pane fade show active" id="step1">
              <div class="row mb-3">
                <div class="col-md-8">
                  <label for="package_id" class="form-label">Selected Package <span class="text-danger">*</span></label>
                  <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id" required>
                    @foreach ($allowedPackages as $allowedPackage)
                    <option value="{{ $allowedPackage->id }}" {{ $allowedPackage->id == old('package_id', $package->id) ? 'selected' : '' }}>
                      {{ $allowedPackage->pkg_name }} - {{ $allowedPackage->pkg_number_of_nights }} nights @if ($allowedPackage->pkg_base_price > 0) ({{ $currency }} {{ number_format($allowedPackage->pkg_base_price, 2) }}) @endif
                    </option>
                    @endforeach
                  </select>
                  @error('package_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">
                    <i class="fas fa-info-circle text-info me-1"></i>
                    Allowed Check-In Days:
                    @foreach($allowedDays as $day)
                    <span class="badge bg-success">{{ $day }}</span>@if(!$loop->last) @endif
                    @endforeach
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Check-In Date Selection</label>
                  <div class="d-grid">
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#calendarModal">
                      <i class="bi bi-calendar me-1"></i>Select Date
                    </button>
                  </div>
                  <input type="text" id="reservation" class="form-control mt-2" placeholder="Selected dates will appear here" readonly>
                </div>
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-success next-step">Next</button>
              </div>
            </div>
            <!-- Step 2: Choose Room -->
            <div class="tab-pane fade" id="step2">
              <p class="text-center text-success hl5 mb-2">Choose Room Type</p>
              <p class="text-success text-center">The following rooms are available over your selected dates</p>
              <div id="roomSelection" class="row g-4 justify-content-center">
                @foreach($rooms as $index => $room)
                {{-- Rooms will be populated here via JS --}}
                @php
                  $room_name = $room->name;
                  $room_type_name = $room->type->name;
                  $room_web_description = $room->web_description;
                  $room_code = $room->code;
                  // if image has http or https, use it directly
                  $room_image = preg_match('/^https?:\/\//', $room->web_image) ? $room->web_image : asset('storage/' . $room->web_image);
                  // if no image, use default
                  $room_image = $room_image ? $room_image : asset('assets/images/image_not_available.png');

                  $on_error_image = asset('assets/images/image_not_available.png');
                  
                  $room_description = $room->description;
                  
                  $dailyRate = $room->type->rates->where('is_shared', false)->first()?->amount ?? 'N/A';
                  $sharedRate = $room->type->rates->where('is_shared', true)->first()?->amount ?? 'N/A';

                  $room_rate_total = $dailyRate;
                  $room_price_display = number_format($room_rate_total, 2);

                  $border_class = ($index % 3 === 1) ? 'package-border-middle' : '';
                @endphp

                <div class="col-md-4 mb-3 {{ $border_class }}">
                  <div class="card outer-room-card p-0 text-center d-flex flex-column"> <!-- Ensure full height -->

                    <!-- Room Image -->
                    <img src="{{ $room_image }}"
                        alt="{{ $room_type_name }}" 
                        class="img-fluid rounded mb-2 room-img"
                        style="width: 100%; height: 250px; object-fit: cover;"
                        onerror="this.src='{{ $on_error_image }}'"
                        data-img="{{ $room_image }}">
                         

                    <!-- Room Content -->
                    <div class="room-card flex-grow-1 d-flex flex-column justify-content-between">
                      <input type="hidden" name="availableRoomId" value="{{ $room_code }}" class="availableRoomId">
                      <input type="radio" id="room_{{ $room_type_name }}" 
                            name="roomType" 
                            value="{{ $room_type_name }}" 
                            class="form-check-input d-none">

                      <label for="room_{{ $room_type_name }}" class="d-block w-100 p-3">
                        <p class="mb-1">
                          <span class="card-title package-header">{{ $room_web_description }}</span><br>
                          <span class="text-success"> Available</small></span>
                        </p>
                        <p class="px-4">{{ $room_description }}</p>
                        <p>
                          <span class="text-success">Package Price:</span><br>
                          <span hidden class="ubookn-price text-bold">{{ $room_rate_total }}</span>
                          <strong>{{ $currency }}<span class="ubookn-price-display">{{ $room_price_display }}</span></strong>
                        </p>
                        <span class="btn btn-primary mt-2">BOOK NOW</span>
                      </label>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary prev-step">Back</button>
                <button type="button" class="btn btn-success next-step">Next</button>
              </div>
            </div>
            <!-- Step 3: Room & Guests -->
            <div class="tab-pane fade" id="step3">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="room_id" class="form-label">Room <span class="text-danger">*</span></label>
                  <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                    <option value="">Select Room</option>
                    @foreach($rooms as $room)
                      <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        Room {{ $room->number }} - {{ $room->type->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('room_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-3">
                  <label for="adults" class="form-label">Adults <span class="text-danger">*</span></label>
                  <input type="number" name="adults" id="adults" class="form-control @error('adults') is-invalid @enderror" value="{{ old('adults', 1) }}" min="1" required>
                  @error('adults')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-3">
                  <label for="children" class="form-label">Children</label>
                  <input type="number" name="children" id="children" class="form-control @error('children') is-invalid @enderror" value="{{ old('children', 0) }}" min="0">
                  @error('children')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <hr>
              <h6 class="mb-3">Guest Information</h6>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="guest_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="guest_name" id="guest_name" class="form-control @error('guest_name') is-invalid @enderror" value="{{ old('guest_name') }}" required>
                  @error('guest_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="guest_email" class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" name="guest_email" id="guest_email" class="form-control @error('guest_email') is-invalid @enderror" value="{{ old('guest_email') }}" required>
                  @error('guest_email')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="mb-3">
                <label for="special_requests" class="form-label">Special Requests</label>
                <textarea name="special_requests" id="special_requests" rows="2" class="form-control @error('special_requests') is-invalid @enderror" placeholder="Any special requirements or requests...">{{ old('special_requests') }}</textarea>
                @error('special_requests')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary prev-step">Back</button>
                <button type="button" class="btn btn-success next-step">Next</button>
              </div>
            </div>
            <!-- Step 4: Confirm -->
            <div class="tab-pane fade" id="step4">
              <div class="alert alert-success">
                <h5 class="mb-2"><i class="bi bi-check-circle me-2"></i>Review & Confirm Your Booking</h5>
                <p>Please review your booking details before submitting.</p>
              </div>
              <div class="mb-3" hidden>
                <div class="mb-3">
                  <label for="arrival_date" class="form-label">Arrival Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('arrival_date') is-invalid @enderror" id="arrival_date" name="arrival_date" required readonly>
                  @error('arrival_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label for="departure_date" class="form-label">Departure Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('departure_date') is-invalid @enderror" id="departure_date" name="departure_date" required readonly>
                  @error('departure_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <!-- Summary (can be enhanced with JS) -->
              <div id="bookingSummary"></div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary prev-step">Back</button>
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-check-circle me-1"></i>Complete Booking
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Calendar Modal --}}
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="calendarModalLabel">
          <i class="bi bi-calendar me-2"></i>Select Check-In Date
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Package Requirements:</strong> You can only check-in on 
          @foreach($allowedDays as $day)
          <span class="badge bg-success">{{ $day }}</span>@if(!$loop->last) @endif
          @endforeach
          for this {{ $packageNights }}-night package.
        </div>
        <div id="calendar" class="calendar"></div>
        <div class="ubook-booking-range mt-3 text-center">
          <p class="mb-0">Selected dates will appear here</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
          <i class="bi bi-check"></i> Done
        </button>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/calendar.js') }}" defer></script>
<script>
// Multi-step navigation
const steps = document.querySelectorAll('#bookingSteps .nav-link');
const panes = document.querySelectorAll('.tab-pane');
const nextBtns = document.querySelectorAll('.next-step');
const prevBtns = document.querySelectorAll('.prev-step');
let currentStep = 0;
function showStep(idx) {
  steps.forEach((step, i) => step.classList.toggle('active', i === idx));
  panes.forEach((pane, i) => pane.classList.toggle('show', i === idx));
  panes.forEach((pane, i) => pane.classList.toggle('active', i === idx));
  currentStep = idx;
}
nextBtns.forEach(btn => btn.addEventListener('click', () => showStep(Math.min(currentStep + 1, steps.length - 1))));
prevBtns.forEach(btn => btn.addEventListener('click', () => showStep(Math.max(currentStep - 1, 0))));
showStep(0);
// Calendar logic and summary can be added here
</script>
@endsection
