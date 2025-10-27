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

{{-- calendar css (for booking form) --}}
<link rel="stylesheet" href="{{ asset('assets/css/calendar.css') }}">

<script>
  // allowedWeekdays: e.g. ["MON","WED","FRI"]
  const allowedWeekdays = @json($allowedWeekdays);
  const packageNights = @json($packageNights);
</script>
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

@extends('tenant.layouts.app')

@section('title', 'Create Package Booking')

{{-- calendar css (for booking form) --}}
<link rel="stylesheet" href="{{ asset('assets/css/calendar.css') }}">

<script>
  // allowedWeekdays: e.g. ["MON","WED","FRI"]
  const allowedWeekdays = @json($allowedWeekdays);
  const packageNights = @json($packageNights);
</script>

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-gift"></i>
          <small class="text-muted">Package Booking</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Package Booking</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
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
    
    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h6 class="alert-heading">Please fix the following errors:</h6>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('tenant.bookings.store') }}" method="POST" id="packageBookingForm">
      @csrf

      <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
          <!-- Package Information -->
          <div class="card card-primary card-outline mb-4">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-gift"></i> Package Information
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <label for="package_id" class="form-label">Selected Package <span class="text-danger">*</span></label>
                  <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id" required>
                    @foreach ($allowedPackages as $allowedPackage)
                    <option value="{{ $allowedPackage->id }}" {{ $allowedPackage->id == old('package_id', $package->id) ? 'selected' : '' }}>
                      {{ $allowedPackage->pkg_name }} - {{ $allowedPackage->pkg_number_of_nights }} nights ({{ $currency }} {{ number_format($allowedPackage->pkg_base_price, 2) }})
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
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#calendarModal">
                      <i class="fas fa-calendar-alt me-1"></i>Select Date
                    </button>
                  </div>
                  <input type="text" id="reservation" class="form-control mt-2" placeholder="Selected dates will appear here" readonly>
                </div>
              </div>
            </div>
          </div>

          <!-- Booking Details -->
          <div class="card card-info card-outline mb-4">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-calendar-check"></i> Booking Details
              </h3>
            </div>
            <div class="card-body">
              <!-- Dates -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="arrival_date" class="form-label">Arrival Date <span class="text-danger">*</span></label>
                  <input type="date" 
                         class="form-control @error('arrival_date') is-invalid @enderror" 
                         id="arrival_date" 
                         name="arrival_date" 
                         required 
                         min="{{ $arrivalDate }}" 
                         value="{{ old('arrival_date', $arrivalDate) }}" 
                         readonly>
                  @error('arrival_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="departure_date" class="form-label">Departure Date <span class="text-danger">*</span></label>
                  <input type="date" 
                         class="form-control @error('departure_date') is-invalid @enderror" 
                         id="departure_date" 
                         name="departure_date" 
                         value="{{ old('departure_date') }}" 
                         required 
                         readonly>
                  @error('departure_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="is_shared" class="form-label">Room Sharing <span class="text-danger">*</span></label>
                  <select class="form-select @error('is_shared') is-invalid @enderror" id="is_shared" name="is_shared">
                    <option value="0" {{ old('is_shared') == 0 ? 'selected' : '' }}>Private Room</option>
                    <option value="1" {{ old('is_shared') == 1 ? 'selected' : '' }}>Shared Room</option>
                  </select>
                  @error('is_shared')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Room and Pricing -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="room_id" class="form-label">Available Room <span class="text-danger">*</span></label>
                  <select class="form-select @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                    <option value="">Select Room</option>
                    @foreach($rooms as $room)
                    @php
                      $dailyRate = $room->type->rates->where('is_shared', false)->first()?->amount ?? 'N/A';
                      $sharedRate = $room->type->rates->where('is_shared', true)->first()?->amount ?? 'N/A';
                    @endphp
                    <option value="{{ $room->id }}" 
                            data-rate="{{ $dailyRate }}" 
                            data-shared-rate="{{ $sharedRate }}"
                            {{ old('room_id') == $room->id ? 'selected' : '' }}>
                      Room {{ $room->number }} - {{ $room->type->name }} ({{ $currency }} {{ $dailyRate }}/night)
                    </option>
                    @endforeach
                  </select>
                  @error('room_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-2">
                  <label for="adults" class="form-label">Adults <span class="text-danger">*</span></label>
                  <input type="number" 
                         class="form-control @error('adults') is-invalid @enderror" 
                         id="adults" 
                         name="adults" 
                         value="{{ old('adults', 1) }}" 
                         min="1" 
                         required>
                  @error('adults')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-2">
                  <label for="children" class="form-label">Children</label>
                  <input type="number" 
                         class="form-control @error('children') is-invalid @enderror" 
                         id="children" 
                         name="children" 
                         value="{{ old('children', 0) }}" 
                         min="0">
                  @error('children')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="daily_rate" class="form-label">Daily Rate ({{ $currency }}) <span class="text-danger">*</span></label>
                  <input type="number" 
                         step="0.01" 
                         class="form-control @error('daily_rate') is-invalid @enderror" 
                         id="daily_rate" 
                         name="daily_rate" 
                         value="{{ old('daily_rate') }}" 
                         required>
                  @error('daily_rate')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
          <!-- Package Summary -->
          <div class="card card-success card-outline mb-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-gift"></i> Package Summary
              </h5>
            </div>
            <div class="card-body">
              <div class="text-center mb-3">
                <h5 class="text-success">{{ $package->pkg_name }}</h5>
                <div class="badge bg-primary fs-6">{{ $packageNights }} Nights Package</div>
              </div>
              <hr>
              <div class="row text-center">
                <div class="col-6">
                  <div class="border-end">
                    <div class="h4 text-success mb-0">{{ $currency }} <span id="total-price">{{ number_format($package->pkg_base_price, 2) }}</span></div>
                    <small class="text-muted">Total Price</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="h4 text-info mb-0">{{ $currency }} <span id="per-night">{{ number_format($package->pkg_base_price / $packageNights, 2) }}</span></div>
                  <small class="text-muted">Per Night</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Guest Information -->
          <div class="card card-warning card-outline mb-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-users"></i> Guest Information
              </h5>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <small class="text-muted">Select the primary guest for this booking. If the guest is not in the list, click the New Guest button.</small>
              </div>
              
              <!-- Primary Guest -->
              <div class="mb-3">
                <label for="guest_id" class="form-label">Primary Guest <span class="text-danger">*</span></label>
                <select class="form-select @error('guest_id') is-invalid @enderror" id="guest_id" name="guest_id" required>
                  <option value="">Select Guest...</option>
                  @foreach($guests as $guest)
                  <option value="{{ $guest->id }}" {{ old('guest_id') == $guest->id ? 'selected' : '' }}>
                    {{ $guest->first_name }} {{ $guest->last_name }}
                    @if($guest->email) - {{ $guest->email }} @endif
                  </option>
                  @endforeach
                </select>
                @error('guest_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="special_requests" class="form-label">Special Requests</label>
                <textarea class="form-control @error('special_requests') is-invalid @enderror" 
                          id="special_requests" 
                          name="special_requests" 
                          rows="3" 
                          placeholder="Any special requirements or requests...">{{ old('special_requests') }}</textarea>
                @error('special_requests')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Secondary Guest Section (Hidden by default) -->
              <div id="secondary_guest_section" style="display: none;">
                <hr>
                <div class="mb-3">
                  <label for="guest2_id" class="form-label">Secondary Guest</label>
                  <select class="form-select @error('guest2_id') is-invalid @enderror" id="guest2_id" name="guest2_id">
                    <option value="">Select Secondary Guest...</option>
                    @foreach($guests as $guest)
                    <option value="{{ $guest->id }}" {{ old('guest2_id') == $guest->id ? 'selected' : '' }}>
                      {{ $guest->first_name }} {{ $guest->last_name }}
                      @if($guest->email) - {{ $guest->email }} @endif
                    </option>
                    @endforeach
                  </select>
                  @error('guest2_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="g2_special_requests" class="form-label">Secondary Guest Requests</label>
                  <textarea class="form-control @error('g2_special_requests') is-invalid @enderror" 
                            id="g2_special_requests" 
                            name="g2_special_requests" 
                            rows="2" 
                            placeholder="Special requests for secondary guest...">{{ old('g2_special_requests') }}</textarea>
                  @error('g2_special_requests')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="d-grid">
                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addGuestModal">
                  <i class="fas fa-user-plus"></i> Add New Guest
                </button>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="card">
            <div class="card-body">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success" id="submitBtn">
                  <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                  <i class="fas fa-save"></i> Create Package Booking
                </button>
                <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<!--end::App Content-->

{{-- Calendar Modal --}}
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="calendarModalLabel">
          <i class="fas fa-calendar-alt me-2"></i>Select Check-In Date
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
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
          <i class="fas fa-check"></i> Done
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add Guest Modal --}}
<div class="modal fade" id="addGuestModal" tabindex="-1" aria-labelledby="addGuestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addGuestModalLabel">
          <i class="fas fa-user-plus me-2"></i>Add New Guest
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>Note:</strong> You will be redirected to the guest creation page. After creating the guest, please return to complete this booking.
        </div>
        <p>Click OK to continue to the guest creation page.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="confirmAddGuestBtn">
          <i class="fas fa-check"></i> OK, Create Guest
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('assets/js/calendar.js') }}"></script>
<script>
// Guest creation redirect
document.getElementById('confirmAddGuestBtn').addEventListener('click', function() {
  window.location.href = "{{ route('tenant.guests.create') }}";
});

// Room selection handler
document.getElementById('room_id').addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  const rate = selectedOption.getAttribute('data-rate');
  if (rate && rate !== 'N/A') {
    document.getElementById('daily_rate').value = rate;
    const isShared = document.getElementById('is_shared').value === '1';
    const totalPriceElement = document.getElementById('total-price');
    const perNightElement = document.getElementById('per-night');
    const dailyRate = isShared ? selectedOption.getAttribute('data-shared-rate') : rate;
    const totalPrice = dailyRate * packageNights;
    totalPriceElement.textContent = parseFloat(totalPrice).toFixed(2);
    perNightElement.textContent = parseFloat(totalPrice / packageNights).toFixed(2);
    
  }
});

// Arrival date change handler (though it's readonly for package bookings)
document.getElementById('arrival_date').addEventListener('change', function() {
  const arrivalDate = new Date(this.value);
  if (!isNaN(arrivalDate.getTime())) {
    const departureDate = new Date(arrivalDate);
    departureDate.setDate(departureDate.getDate() + packageNights);
    document.getElementById('departure_date').value = departureDate.toISOString().split('T')[0];
    document.getElementById('departure_date').min = this.value;
  }
});

// Room sharing change handler
document.getElementById('is_shared').addEventListener('change', function() {
  const isShared = this.value === '1';
  const roomSelect = document.getElementById('room_id');
  const selectedOption = roomSelect.options[roomSelect.selectedIndex];
  const totalPriceElement = document.getElementById('total-price');
  const perNightElement = document.getElementById('per-night');

  if (selectedOption) {
    const dailyRate = selectedOption.getAttribute('data-rate');
    const sharedRate = selectedOption.getAttribute('data-shared-rate');
    
    if (dailyRate && sharedRate) {
      document.getElementById('daily_rate').value = isShared ? sharedRate : dailyRate;
      const totalPrice = (isShared ? sharedRate : dailyRate) * packageNights;
      totalPriceElement.textContent = parseFloat(totalPrice).toFixed(2);
      perNightElement.textContent = parseFloat(totalPrice / packageNights).toFixed(2);

    }
  }
  
  // Show/hide secondary guest section
  const secondaryGuestSection = document.getElementById('secondary_guest_section');
  const guest2Select = document.getElementById('guest2_id');
  const guest2Requests = document.getElementById('g2_special_requests');
  
  if (isShared) {
    secondaryGuestSection.style.display = 'block';
    guest2Select.setAttribute('required', 'required');
  } else {
    secondaryGuestSection.style.display = 'none';
    guest2Select.removeAttribute('required');
    guest2Select.value = '';
    guest2Requests.value = '';
  }
});

// Form submission handler
document.getElementById('packageBookingForm').addEventListener('submit', function() {
  const submitBtn = document.getElementById('submitBtn');
  const spinner = document.getElementById('spinner');
  
  submitBtn.disabled = true;
  spinner.classList.remove('d-none');
  
  // Re-enable after 10 seconds as failsafe
  setTimeout(function() {
    submitBtn.disabled = false;
    spinner.classList.add('d-none');
  }, 10000);
});

// Package change handler
document.getElementById('package_id').addEventListener('change', function() {
  const selectedPackageId = this.value;
  if (selectedPackageId !== '{{ $package->id }}') {
    window.location.href = '{{ route("tenant.bookings.create") }}?package_id=' + selectedPackageId;
  }
});

// Auto-close modal when date is selected
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('selected-date')) {
    setTimeout(function() {
      const modal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
      if (modal) {
        modal.hide();
      }
    }, 1000);
  }
});
</script>

@endsection