@extends('tenant.layouts.app')

@section('title', 'Edit Booking')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Edit Booking</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Booking</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->
{{-- <div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Edit Booking: {{ $booking->bcode }}</h1>
  <a href="{{ route('tenant.bookings.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Back to Bookings
  </a>
</div> --}}
<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    {{-- Display validation errors --}}
    @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="card card-success card-outline">
      <div class="card-header">
        <h5 class="card-title">Edit Booking</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('tenant.bookings.update', $booking) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="row">
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="arrival_date" class="form-label">Arrival Date <span class="text-muted">(Required)</span></label>
                <input type="date" class="form-control @error('arrival_date') is-invalid @enderror" id="arrival_date" name="arrival_date" required min="{{ $booking->min_arrival_date }}" value="{{ old('arrival_date', $booking->arrival_date->format('Y-m-d')) }}">
                @error('arrival_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="departure_date" class="form-label">Departure Date <span class="text-muted">(Required)</span></label>
                <input type="date" class="form-control @error('departure_date') is-invalid @enderror" id="departure_date" name="departure_date" value="{{ old('departure_date', $booking->departure_date->format('Y-m-d')) }}" required>
                @error('departure_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="is_shared" class="form-label">Shared Room <span class="text-muted">(Required)</span></label>
                <select class="form-select @error('is_shared') is-invalid @enderror" id="is_shared" name="is_shared">
                  {{-- <option value="" disabled>Select</option> --}}
                  <option {{ old('is_shared', $booking->is_shared) == 1 ? 'selected' : '' }} value="1">Yes</option>
                  <option {{ old('is_shared', $booking->is_shared) == 0 ? 'selected' : '' }} value="0">No</option>
                </select>
                @error('is_shared')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <hr>
          {{-- Room Section --}}
          <div class="row" id="room_section">
            
            <div class="col-md-4">
              <div class="form-group mb-3">
                <label for="room_id" class="form-label">Room <span class="text-muted">(Required)</span></label>
                <select class="form-select  @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                  <option value="">Select Room</option>
                  @foreach($rooms as $room)
                  {{ $dailyRate = $room->type->rates->where('is_shared', false)->first() ? $room->type->rates->where('is_shared', false)->first()->amount : 'N/A' }}
                  {{ $sharedRate = $room->type->rates->where('is_shared', true)->first() ? $room->type->rates->where('is_shared', true)->first()->amount : 'N/A' }}
                  <option {{ old('room_id', $booking->selected_room) == $room->id ? 'selected' : '' }} value="{{ $room->id }}" data-rate="{{ $dailyRate }}" data-shared-rate="{{ $sharedRate }}">
                    RM{{ $room->number }} - {{ $room->type->name }} ({{ $dailyRate }} {{ $currency }}/night)
                  </option>
                  @endforeach
                </select>
                @error('room_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-2">
              <div class="mb-3">
                <label for="adults" class="form-label">Adults <span class="text-muted">(Required)</span></label>
                <input type="number" class="form-control @error('adults') is-invalid @enderror" id="adults" name="adults" value="{{ old('adults', $booking->booking_count_adults) }}" min="1" required>
                @error('adults')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-2">
              <div class="mb-3">
                <label for="children" class="form-label">Children</label>
                <input type="number" class="form-control @error('children') is-invalid @enderror" id="children" name="children" value="{{ old('children', $booking->booking_count_children) }}" min="0" disabled>
                @error('children')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="mb-3">
                <label for="daily_rate" class="form-label">Daily Rate ({{ $currency }}) <span class="text-muted">(Required)</span></label>
                <input type="number" step="0.01" class="form-control @error('daily_rate') is-invalid @enderror" id="daily_rate" name="daily_rate" value="{{ old('daily_rate', $booking->daily_rate) }}" required>
                @error('daily_rate')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <hr>
          {{-- Guest Section --}}
          <div class="mb-3">
            <h5>Guest Information</h5>
            <small class="text-muted">Select the success guest for this booking. If the guest is not in the list, click New Guest button to add a new guest to database.</small>
          </div>
          <hr>
          <div class="row" id="success_guest_section">
            
            <div class="col-md-4 mb-3">
              <div class="form-group">
                <label for="guest_id" class="form-label">success Guest <span class="text-muted">(Required)</span></label>
                <select class="form-control select2-single @error('guest_id') is-invalid @enderror" id="guest_id" name="guest_id" required>
                  
                  @foreach($guests as $guest)
                  <option {{ old('guest_id', $successGuest->id) == $guest->id ? 'selected' : '' }} value="{{ $guest->id }}">{{ $guest->first_name }} {{ $guest->last_name }} - {{ $guest->email }}</option>
                  @endforeach
                  <option value="">Select Guest</option>
                </select>
              </div>
              @error('guest_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-8 mb-3">
              <label for="special_requests" class="form-label">Special Requests</label>
              <textarea class="form-control @error('special_requests') is-invalid @enderror" id="special_requests" name="special_requests" rows="3">{{ old('special_requests', $successGuest->special_requests) }}</textarea>
              @error('special_requests')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row" id="secondary_guest_section" @if(old('is_shared', $booking->is_shared) == 1) @else hidden @endif>
            {{-- if we have secondary guest, we need to declare the variables --}}
            
            <div class="col-md-4 mb-3">
              <div class="form-group">
                <label for="guest2_id" class="form-label">Secondary Guest</label>
                <select class="form-control select2-single @error('guest2_id') is-invalid @enderror" id="guest2_id" name="guest2_id" >
                  <option value="">Select Guest</option>
                  @foreach($guests as $guest)
                  <option {{ old('guest2_id', $secondaryGuest->id ?? '') == $guest->id ? 'selected' : '' }} value="{{ $guest->id }}">{{ $guest->first_name }} {{ $guest->last_name }} - {{ $guest->email }}</option>
                  @endforeach
                </select>
              </div>
              @error('guest_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-8 mb-3">
              <label for="g2_special_requests" class="form-label">Special Requests</label>
              <textarea class="form-control @error('g2_special_requests') is-invalid @enderror" id="g2_special_requests" name="g2_special_requests" rows="3">{{ old('g2_special_requests', $secondaryGuest->special_requests ?? '') }}</textarea>
              @error('g2_special_requests')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          {{-- button to the left to add a new guest --}}
          <div class="row">
            <div class="col-md-6 mb-3">
              <small>If you cannot find the guest in the dropdown, click New Guest button to add a new guest to database.</small>
              <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addGuestModal">
                <i class="fas fa-user-plus"></i> New Guest
              </button>
            </div>
            {{-- status --}}
            <div class="col-md-3 mb-3">
              <div class="form-group">
                <label for="status" class="form-label">Booking Status <span class="text-muted">(Required)</span></label>
                {{-- booking status select --}}
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                  {{-- <option value="">Booking Status</option> --}}
                  @foreach($bookingStatuses as $status)
                  <option {{ old('status', $booking->status) == $status ? 'selected' : '' }} value="{{ $status }}">{{ ucfirst($status) }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            {{-- source --}}
            <div class="col-md-3 mb-3 text-end">
              <div class="form-group">
                <label for="source" class="form-label">Booking Source <span class="text-muted">(Required)</span></label>
                {{-- booking source select --}}
                <select name="source" id="source" class="form-select @error('source') is-invalid @enderror">
                  {{-- <option value="">Booking Source</option> --}}
                  @foreach($bookingSources as $source)
                  <option {{ old('source', $booking->source) == $source ? 'selected' : '' }} value="{{ $source }}">{{ ucfirst($source) }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <hr>
          {{-- button to the left to add a new guest --}}
          <div class="row">
            
            <div class="col-md-6 mb-3">
              <button type="submit" class="btn btn-success" id="submitBtn">
                <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <i class="fas fa-save me-2"></i> Save Booking
              </button>
            </div>
            <div class="col-md-6 mb-3 ms-auto text-end">
              <a href="#" class="btn btn-sm btn-secondary">
                <i class="fas fa-times me-2"></i> Cancel
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Add Guest Modal (placeholder, implement as needed) -->
<div class="modal fade" id="addGuestModal" tabindex="-1" aria-labelledby="addGuestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addGuestModalLabel">Add New Guest</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Guest creation will redirect you to another page, click ok to continue.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-success" id="confirmAddGuestBtn">OK</button>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  // When confirmAddGuestBtn is clicked, redirect to the guest creation page
  document.getElementById('confirmAddGuestBtn').addEventListener('click', function() {
    window.location.href = "{{ route('tenant.guests.create') }}";
  });
  // Simple JavaScript to set default rate and calculate end date
  document.getElementById('room_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const rate = selectedOption.getAttribute('data-rate');
    if (rate) {
      document.getElementById('daily_rate').value = rate;
    }
  });
  
  document.getElementById('arrival_date').addEventListener('change', function() {
    const arrivalDate = new Date(this.value);
    if (!isNaN(arrivalDate.getTime())) {
      const departureDate = new Date(arrivalDate);
      departureDate.setDate(departureDate.getDate() + 1);
      document.getElementById('departure_date').value = departureDate.toISOString().split('T')[0];
      document.getElementById('departure_date').min = this.value;
    }
  });
  
  document.getElementById('departure_date').addEventListener('change', function() {
    // Get the selected dates
    const arrivalDate = document.getElementById('arrival_date').value;
    const departureDate = this.value;
    // We need to get the rooms available with AJAX based on the selected dates
    // fetch(`/admin/rooms/available?arrival_date=${arrivalDate}&departure_date=${departureDate}`)
    const url = `{{ route('rooms.available') }}?arrival_date=${arrivalDate}&departure_date=${departureDate}`;
    console.log('Fetching available rooms from URL:', url);
    console.log('Arrival Date:', arrivalDate);
    console.log('Departure Date:', departureDate);
    console.log('Using API Token:', '{{ auth()->user()->company_id }}');
    
    fetch(url, {
      credentials: 'include', // Include this if your API requires authentication via cookies
      headers: {
        'X-Company-ID': '{{ auth()->user()->company_id }}',
        'Accept': 'application/json',
      }
    })
    .then(response => response.json())
    .then(data => {
      // Update the room selection based on available rooms
      const roomSelect = document.getElementById('room_id');
      console.log('Available rooms data:', data);
      roomSelect.innerHTML = '';
      data.rooms.forEach(room => {
        const option = document.createElement('option');
        option.value = room.id;
        option.textContent = `RM${room.number} - ${room.type.name} (${room.daily_rate} ${data.currency}/night)`;
        option.setAttribute('data-rate', room.daily_rate);
        option.setAttribute('data-shared-rate', room.shared_rate);
        roomSelect.appendChild(option);
      });
    });
  });
  
  // now when is_shared is changed, we need to update the daily_rate to the shared rate if is_shared is true and also vice versa
  document.getElementById('is_shared').addEventListener('change', function() {
    const isShared = this.value === '1';
    const roomSelect = document.getElementById('room_id');
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    if (selectedOption) {
      const dailyRate = selectedOption.getAttribute('data-rate');
      if (dailyRate) {
        // Here you have it available in the data attributes
        const sharedRate = selectedOption.getAttribute('data-shared-rate');
        document.getElementById('daily_rate').value = isShared ? sharedRate : dailyRate;
        // update the daily_rate input field
      }
      
    }
    // if is shared is true, we need to also add a section with an input field for number of guests in the room and select2 for selecting the guests
    // if is shared is false, we need to remove that section
    const secondaryGuestSection = document.getElementById('secondary_guest_section');
    if (isShared) {
      secondaryGuestSection.removeAttribute('hidden');
      document.getElementById('guest2_id').setAttribute('required', 'required');
    } else {
      secondaryGuestSection.setAttribute('hidden', 'hidden');
      document.getElementById('guest2_id').removeAttribute('required');
      document.getElementById('guest2_id').value = '';
      document.getElementById('g2_special_requests').value = '';
    }
  });
  
  // Show loading spinner on submit
  document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('spinner').classList.remove('d-none');
  });
</script>
@endsection