@extends('tenant.layouts.app')

@section('title', 'Edit Room Rate')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">Edit Room Rate</h4>
        <small class="text-muted">{{ $roomRate->name }}</small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-rates.index') }}">Room Rates</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-rates.show', $roomRate) }}">{{ $roomRate->name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-pencil"></i> Edit Room Rate
            </h5>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.room-rates.update', $roomRate) }}" method="POST">
              @csrf
              @method('PUT')

              <!-- Basic Information -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-info-circle"></i> Basic Information
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="room_type_id" class="form-label">Room Type <span class="text-danger">*</span></label>
                  <select class="form-select @error('room_type_id') is-invalid @enderror" id="room_type_id" name="room_type_id" required>
                    <option value="">Select a room type</option>
                    @foreach($roomTypes as $roomType)
                      <option value="{{ $roomType->id }}" {{ (old('room_type_id', $roomRate->room_type_id) == $roomType->id) ? 'selected' : '' }}>
                        {{ $roomType->name }} ({{ $roomType->code }})
                      </option>
                    @endforeach
                  </select>
                  @error('room_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Rate Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" 
                         id="name" name="name" value="{{ old('name', $roomRate->name) }}" 
                         placeholder="e.g., Summer Standard Rate" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="rate_type" class="form-label">Rate Type <span class="text-danger">*</span></label>
                  <select class="form-select @error('rate_type') is-invalid @enderror" id="rate_type" name="rate_type" required>
                    <option value="">Select rate type</option>
                    <option value="standard" {{ old('rate_type', $roomRate->rate_type) === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="off_season" {{ old('rate_type', $roomRate->rate_type) === 'off_season' ? 'selected' : '' }}>Off Season</option>
                    <option value="package" {{ old('rate_type', $roomRate->rate_type) === 'package' ? 'selected' : '' }}>Package</option>
                  </select>
                  @error('rate_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="amount" class="form-label">Amount (R) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">R</span>
                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" 
                           id="amount" name="amount" value="{{ old('amount', $roomRate->amount) }}" 
                           placeholder="0.00" required>
                  </div>
                  @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Effective Period -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-calendar-range"></i> Effective Period
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="effective_from" class="form-label">Effective From <span class="text-danger">*</span></label>
                  <input type="date" class="form-control @error('effective_from') is-invalid @enderror" 
                         id="effective_from" name="effective_from" value="{{ old('effective_from', $roomRate->effective_from?->format('Y-m-d')) }}" required>
                  @error('effective_from')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="effective_until" class="form-label">Effective Until</label>
                  <input type="date" class="form-control @error('effective_until') is-invalid @enderror" 
                         id="effective_until" name="effective_until" value="{{ old('effective_until', $roomRate->effective_until?->format('Y-m-d')) }}">
                  <div class="form-text">Leave empty for ongoing rate</div>
                  @error('effective_until')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Stay Requirements -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-moon"></i> Stay Requirements
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="min_nights" class="form-label">Minimum Nights</label>
                  <input type="number" min="1" class="form-control @error('min_nights') is-invalid @enderror" 
                         id="min_nights" name="min_nights" value="{{ old('min_nights', $roomRate->min_nights) }}" 
                         placeholder="1">
                  @error('min_nights')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="max_nights" class="form-label">Maximum Nights</label>
                  <input type="number" min="1" class="form-control @error('max_nights') is-invalid @enderror" 
                         id="max_nights" name="max_nights" value="{{ old('max_nights', $roomRate->max_nights) }}" 
                         placeholder="Unlimited">
                  @error('max_nights')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Settings -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-gear"></i> Settings
                  </h6>
                </div>
                
                <div class="col-md-4 mb-3">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_shared" name="is_shared" 
                           value="1" {{ old('is_shared', $roomRate->is_shared) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_shared">
                      Shared Rate
                    </label>
                    <div class="form-text">Rate can be used across multiple properties</div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                           value="1" {{ old('is_active', $roomRate->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      Active
                    </label>
                    <div class="form-text">Rate is available for bookings</div>
                  </div>
                </div>

                {{-- is per night or per person dropdown --}}
                <div class="col-md-4 mb-3">
                  <label class="form-label">Rate Basis</label>
                  <select class="form-select @error('is_per_night') is-invalid @enderror" id="is_per_night" name="is_per_night">
                    <option value="0" {{ old('is_per_night', $roomRate->conditions['is_per_night'] ?? false) == false ? 'selected' : '' }}>Per Person</option>
                    <option value="1" {{ old('is_per_night', $roomRate->conditions['is_per_night'] ?? false) == true ? 'selected' : '' }}>Per Night</option>
                  </select>
                  @error('is_per_night')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Conditions (Optional) -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-list-check"></i> Additional Conditions
                  </h6>
                </div>
                
                <div class="col-12 mb-3">
                  <label class="form-label">Special Conditions</label>
                  <div id="conditions-container">
                    @php
                      $conditions = old('conditions', $roomRate->conditions ?? []);
                    @endphp
                    @if($conditions && count($conditions) > 0)
                      @foreach($conditions as $index => $condition)
                      {{-- Except for the is_per_night condition --}}
                        @if($index === 'is_per_night') @continue @endif
                        <div class="input-group mb-2 condition-row">
                          <input type="text" class="form-control condition-key" name="conditions[{{ $index }}][key]" value="{{ $condition['key'] ?? '' }}" placeholder="Condition Key">
                          <input type="text" class="form-control condition-value" name="conditions[{{ $index }}][value]" value="{{ $condition['value'] ?? '' }}" placeholder="Condition Value">
                          <button type="button" class="btn btn-outline-danger remove-condition">
                            <i class="bi bi-dash"></i>
                          </button>
                        </div>
                      @endforeach
                    @endif
                  </div>
                  <button type="button" class="btn btn-outline-success btn-sm" id="add-condition">
                    <i class="bi bi-plus"></i> Add Condition
                  </button>
                </div>
              </div>

              <!-- Submit Buttons -->
              <div class="row">
                <div class="col-12">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                      <i class="bi bi-check-circle"></i> Update Room Rate
                    </button>
                    <a href="{{ route('tenant.room-rates.show', $roomRate) }}" 
                       class="btn btn-outline-secondary">
                      <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add condition functionality
    document.getElementById('add-condition').addEventListener('click', function() {
      const container = document.getElementById('conditions-container');
      const index = container.children.length;
      const newCondition = document.createElement('div');
      newCondition.className = 'input-group mb-2 condition-row';
      newCondition.innerHTML = `
          <input type="text" class="form-control" name="conditions[${index}][key]" placeholder="Condition Key">
          <input type="text" class="form-control" name="conditions[${index}][value]" placeholder="Condition Value">
          <button type="button" class="btn btn-outline-danger remove-condition">
              <i class="bi bi-dash"></i>
          </button>
      `;
      container.appendChild(newCondition);
    });
    // document.getElementById('add-condition').addEventListener('click', function() {
    //     const container = document.getElementById('conditions-container');
    //     const newCondition = document.createElement('div');
    //     newCondition.className = 'input-group mb-2 condition-row';
    //     newCondition.innerHTML = `
    //         <input type="text" class="form-control" name="conditions[]" 
    //                placeholder="e.g., Advance booking required">
    //         <button type="button" class="btn btn-outline-danger remove-condition">
    //             <i class="bi bi-dash"></i>
    //         </button>
    //     `;
    //     container.appendChild(newCondition);
    // });

    // Remove condition functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-condition')) {
            e.target.closest('.condition-row').remove();
        }
    });

    // Prevent spaces in condition key inputs
    function preventSpaces(event) {
        if (event.key === ' ') {
            event.preventDefault();
        }
    }
    document.querySelectorAll('.condition-key').forEach(function(input) {
        input.addEventListener('keydown', preventSpaces);
    });

    // document.querySelectorAll('.condition-value').forEach(function(input) {
    //     input.addEventListener('keydown', preventSpaces);
    // });

    // Min/max nights validation
    const minNights = document.getElementById('min_nights');
    const maxNights = document.getElementById('max_nights');
    
    function validateNights() {
        if (minNights.value && maxNights.value) {
            if (parseInt(maxNights.value) < parseInt(minNights.value)) {
                maxNights.setCustomValidity('Maximum nights cannot be less than minimum nights');
            } else {
                maxNights.setCustomValidity('');
            }
        }
    }
    
    minNights.addEventListener('input', validateNights);
    maxNights.addEventListener('input', validateNights);
});
</script>
@endpush
@endsection