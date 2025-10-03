@extends('tenant.layouts.app')

@section('title', 'Create Booking')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-calendar-plus"></i>
          <small class="text-muted">Create Booking</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
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

    <form action="{{ route('tenant.bookings.store') }}" method="POST" id="bookingForm">
      @csrf
      
      <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
          <!-- Booking Information -->
          <div class="card card-primary card-outline mb-4">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-calendar-check"></i> Booking Information
              </h3>
              <!-- if we have package -->
              @if($package)
              <div class="mt-2 text-end">
                <span class="badge bg-success">{{ $package->pkg_name }}</span>
                <small class="text-muted">{{ $package->pkg_number_of_nights }} nights package</small>
              </div>
              @endif
            </div>
            <div class="card-body">
              <!-- Dates and Room Sharing -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="arrival_date" class="form-label">Arrival Date <span class="text-danger">*</span></label>
                  <input type="date" 
                         class="form-control @error('arrival_date') is-invalid @enderror" 
                         id="arrival_date" 
                         name="arrival_date" 
                         required 
                         min="{{ $arrivalDate }}" 
                         value="{{ old('arrival_date', $arrivalDate) }}">
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
                         required>
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

              <!-- Booking Source -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="source" class="form-label">Booking Source <span class="text-danger">*</span></label>
                  <select class="form-select @error('source') is-invalid @enderror" name="source" id="source" required>
                    <option value="">Select Source...</option>
                    @foreach($bookingSources as $source)
                    <option value="{{ $source }}" {{ old('source') == $source ? 'selected' : '' }}>
                      {{ ucfirst(str_replace('_', ' ', $source)) }}
                    </option>
                    @endforeach
                  </select>
                  @error('source')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Sidebar -->
        <div class="col-md-4">
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
                <button type="submit" class="btn btn-primary" id="submitBtn">
                  <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                  <i class="fas fa-save"></i> Create Booking
                </button>
                <a href="{{ route('tenant.bookings.index', ['property_id' => $propertyId]) }}" class="btn btn-outline-secondary">
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
<!-- Add Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1" aria-labelledby="addGuestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addGuestModalLabel">
          <i class="fas fa-user-plus"></i> Add New Guest
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <h6 class="alert-heading">Guest Creation Notice</h6>
          <p class="mb-0">Guest creation will redirect you to another page. Your current booking information will be saved in your browser.</p>
        </div>
        <p class="text-muted">Click "Continue" to proceed to the guest creation page.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="confirmAddGuestBtn">
          <i class="fas fa-arrow-right"></i> Continue
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // When confirmAddGuestBtn is clicked, redirect to the guest creation page
  document.getElementById('confirmAddGuestBtn').addEventListener('click', function() {
    window.location.href = "{{ route('tenant.guests.create') }}";
  });
  
  // Form auto-fill and calculations
  document.getElementById('room_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const isShared = document.getElementById('is_shared').value === '1';
    const rate = selectedOption.getAttribute(isShared ? 'data-shared-rate' : 'data-rate');
    if (rate && rate !== 'N/A') {
      document.getElementById('daily_rate').value = rate;
    }
  });
  
  // Auto-calculate departure date
  document.getElementById('arrival_date').addEventListener('change', function() {
    const arrivalDate = new Date(this.value);
    if (!isNaN(arrivalDate.getTime())) {
      const departureDate = new Date(arrivalDate);
      departureDate.setDate(departureDate.getDate() + 1);
      document.getElementById('departure_date').value = departureDate.toISOString().split('T')[0];
      document.getElementById('departure_date').min = this.value;
    }
  });
  
  // Fetch available rooms based on dates
  document.getElementById('departure_date').addEventListener('change', function() {
    const arrivalDate = document.getElementById('arrival_date').value;
    const departureDate = this.value;
    
    if (!arrivalDate || !departureDate) return;
    
    const url = `{{ route('tenant.rooms.available') }}?arrival_date=${arrivalDate}&departure_date=${departureDate}`;
    
    fetch(url, {
      credentials: 'include',
      headers: {
        'X-Property-ID': '{{ $propertyId }}',
        'Accept': 'application/json',
      }
    })
    .then(response => response.json())
    .then(data => {
      const roomSelect = document.getElementById('room_id');
      roomSelect.innerHTML = '<option value="">Select Room</option>';
      
      data.rooms.forEach(room => {
        const option = document.createElement('option');
        option.value = room.id;
        
        const standardRate = room.type.rates.find(rate => rate.name === 'Standard Rate' && !rate.is_shared);
        const sharedRate = room.type.rates.find(rate => rate.name === 'Standard Rate' && rate.is_shared);
        
        option.textContent = `Room ${room.number} - ${room.type.name} (${standardRate ? standardRate.daily_rate_formatted : 'N/A'} ${data.currency}/night)`;
        option.setAttribute('data-rate', standardRate ? standardRate.daily_rate : 0);
        option.setAttribute('data-shared-rate', sharedRate ? sharedRate.daily_rate : 0);
        roomSelect.appendChild(option);
      });
    })
    .catch(error => console.error('Error fetching rooms:', error));
  });
  
  // Handle room sharing changes
  document.getElementById('is_shared').addEventListener('change', function() {
    const isShared = this.value === '1';
    const roomSelect = document.getElementById('room_id');
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    
    // Update rate based on sharing option
    if (selectedOption && selectedOption.value) {
      const rate = selectedOption.getAttribute(isShared ? 'data-shared-rate' : 'data-rate');
      if (rate && rate !== 'N/A') {
        document.getElementById('daily_rate').value = rate;
      }
    }
    
    // Show/hide secondary guest section
    const secondaryGuestSection = document.getElementById('secondary_guest_section');
    const guest2Select = document.getElementById('guest2_id');
    
    if (isShared) {
      secondaryGuestSection.style.display = 'block';
      guest2Select.setAttribute('required', 'required');
    } else {
      secondaryGuestSection.style.display = 'none';
      guest2Select.removeAttribute('required');
      guest2Select.value = '';
      document.getElementById('g2_special_requests').value = '';
    }
  });
  
  // Form submission with loading state
  document.getElementById('bookingForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    const spinner = document.getElementById('spinner');
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Change button text to show processing
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Booking...';
  });
</script>
@endsection