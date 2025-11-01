@extends('tenant.layouts.app')

@section('title', 'Create Package')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-plus-circle"></i>
          <small class="text-muted">Create New Package</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-packages.index', ['property_id' => $propertyId]) }}">Room Packages</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
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
    
    {{-- messages from session --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- validation errors --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif


    <form action="{{ route('tenant.room-packages.store') }}" method="POST" enctype="multipart/form-data" id="packageForm">
      @csrf
      <input type="hidden" name="property_id" value="{{ $propertyId }}">
      
      <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-info-circle"></i> Package Information
              </h3>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle"></i> Image dimensions should be 420x250 pixels for best results.
                </div>
              </div>
              <!-- Package Name -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="pkg_name" class="form-label">Package Name <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('pkg_name') is-invalid @enderror" 
                         id="pkg_name" 
                         name="pkg_name" 
                         value="{{ old('pkg_name') }}" 
                         required>
                  @error('pkg_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="pkg_sub_title" class="form-label">Sub Title <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('pkg_sub_title') is-invalid @enderror" 
                         id="pkg_sub_title" 
                         name="pkg_sub_title" 
                         value="{{ old('pkg_sub_title') }}" 
                         required>
                  @error('pkg_sub_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Package Description -->
              <div class="mb-3 ">
                <label for="pkg_description" class="form-label">Description</label>
                {{-- Trix Editor for pkg_description --}}
                <x-trix-input id="pkg_description" name="pkg_description" :value="old('pkg_description')" autocomplete="off" />
                {{-- <textarea class="form-control @error('pkg_description') is-invalid @enderror" 
                          id="pkg_description" 
                          name="pkg_description" 
                          rows="4"
                          placeholder="Describe what this package includes...">{{ old('pkg_description') }}</textarea> --}}
                @error('pkg_description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Number of Nights and Base Price -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="pkg_number_of_nights" class="form-label">Number of Nights <span class="text-danger">*</span></label>
                  <select class="form-select @error('pkg_number_of_nights') is-invalid @enderror" 
                          id="pkg_number_of_nights" 
                          name="pkg_number_of_nights" 
                          required>
                    <option value="">Select nights...</option>
                    @for($i = 1; $i <= $maxNumOfNights; $i++)
                      <option value="{{ $i }}" {{ old('pkg_number_of_nights') == $i ? 'selected' : '' }}>
                        {{ $i }} {{ $i == 1 ? 'Night' : 'Nights' }}
                      </option>
                    @endfor
                  </select>
                  @error('pkg_number_of_nights')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="pkg_base_price" class="form-label">Base Price ({{ $currency }}) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">{{ $currency }}</span>
                    <input type="number" 
                           class="form-control @error('pkg_base_price') is-invalid @enderror" 
                           id="pkg_base_price" 
                           name="pkg_base_price" 
                           value="{{ old('pkg_base_price') }}" 
                           min="0" 
                           step="0.01"
                           required>
                  </div>
                  @error('pkg_base_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="pkg_status" class="form-label">Status <span class="text-danger">*</span></label>
                  <select class="form-select @error('pkg_status') is-invalid @enderror" 
                          id="pkg_status" 
                          name="pkg_status" 
                          required>
                    <option value="active" {{ old('pkg_status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('pkg_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('pkg_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Guest Capacity -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="pkg_min_guests" class="form-label">Minimum Guests</label>
                  <input type="number" 
                         class="form-control @error('pkg_min_guests') is-invalid @enderror" 
                         id="pkg_min_guests" 
                         name="pkg_min_guests" 
                         value="{{ old('pkg_min_guests') }}" 
                         min="1">
                  @error('pkg_min_guests')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="pkg_max_guests" class="form-label">Maximum Guests</label>
                  <input type="number" 
                         class="form-control @error('pkg_max_guests') is-invalid @enderror" 
                         id="pkg_max_guests" 
                         name="pkg_max_guests" 
                         value="{{ old('pkg_max_guests') }}" 
                         min="1">
                  @error('pkg_max_guests')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Validity Period -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="pkg_valid_from" class="form-label">Valid From</label>
                  <input type="date" 
                         class="form-control @error('pkg_valid_from') is-invalid @enderror" 
                         id="pkg_valid_from" 
                         name="pkg_valid_from" 
                         value="{{ old('pkg_valid_from') }}">
                  @error('pkg_valid_from')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="pkg_valid_to" class="form-label">Valid To</label>
                  <input type="date" 
                         class="form-control @error('pkg_valid_to') is-invalid @enderror" 
                         id="pkg_valid_to" 
                         name="pkg_valid_to" 
                         value="{{ old('pkg_valid_to') }}">
                  @error('pkg_valid_to')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Package Image -->
              <div class="mb-3">
                <label for="pkg_image" class="form-label">Package Image</label>
                <input type="file" 
                       class="form-control @error('pkg_image') is-invalid @enderror" 
                       id="pkg_image" 
                       name="pkg_image" 
                       accept="image/*">
                @error('pkg_image')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Upload an image to represent this package (JPEG, PNG, JPG, GIF, WebP - Max: 2MB)</div>
              </div>
            </div>
          </div>

          <!-- Inclusions & Exclusions -->
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-list-check"></i> Package Details
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <label for="inclusions" class="form-label">Inclusions</label>
                  <div id="inclusions-container">
                    @if(old('pkg_inclusions'))
                      @foreach(old('pkg_inclusions') as $index => $inclusion)
                        @if(!empty($inclusion))
                          <div class="input-group mb-2">
                            <input type="text" 
                                   class="form-control" 
                                   name="pkg_inclusions[]" 
                                   value="{{ $inclusion }}" 
                                   placeholder="What's included...">
                            <button type="button" class="btn btn-outline-danger remove-inclusion">
                              <i class="bi bi-dash"></i>
                            </button>
                          </div>
                        @endif
                      @endforeach
                    @endif
                    <div class="input-group mb-2">
                      <input type="text" 
                             class="form-control" 
                             name="pkg_inclusions[]" 
                             placeholder="What's included...">
                      <button type="button" class="btn btn-outline-danger remove-inclusion">
                        <i class="bi bi-dash"></i>
                      </button>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-success" id="add-inclusion">
                    <i class="bi bi-plus"></i> Add Inclusion
                  </button>
                </div>
                <div class="col-md-6">
                  <label for="exclusions" class="form-label">Exclusions</label>
                  <div id="exclusions-container">
                    @if(old('pkg_exclusions'))
                      @foreach(old('pkg_exclusions') as $index => $exclusion)
                        @if(!empty($exclusion))
                          <div class="input-group mb-2">
                            <input type="text" 
                                   class="form-control" 
                                   name="pkg_exclusions[]" 
                                   value="{{ $exclusion }}" 
                                   placeholder="What's not included...">
                            <button type="button" class="btn btn-outline-danger remove-exclusion">
                              <i class="bi bi-dash"></i>
                            </button>
                          </div>
                        @endif
                      @endforeach
                    @endif
                    <div class="input-group mb-2">
                      <input type="text" 
                             class="form-control" 
                             name="pkg_exclusions[]" 
                             placeholder="What's not included...">
                      <button type="button" class="btn btn-outline-danger remove-exclusion">
                        <i class="bi bi-dash"></i>
                      </button>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-success" id="add-exclusion">
                    <i class="bi bi-plus"></i> Add Exclusion
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
          <!-- Check-in Days -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-calendar-week"></i> Check-in Days <span class="text-danger">*</span>
              </h3>
            </div>
            <div class="card-body">
              <div class="form-text mb-3">Select the days guests can check in for this package.</div>
              @foreach($daysOfWeek as $day)
                <div class="form-check">
                  <input class="form-check-input @error('pkg_checkin_days') is-invalid @enderror" 
                         type="checkbox" 
                         id="day_{{ strtolower($day) }}" 
                         name="pkg_checkin_days[]" 
                         value="{{ $day }}"
                         {{ in_array($day, old('pkg_checkin_days', [])) ? 'checked' : '' }}>
                  <label class="form-check-label" for="day_{{ strtolower($day) }}">
                    {{ $day }}
                  </label>
                </div>
              @endforeach
              @error('pkg_checkin_days')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Associated Rooms -->
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-door-open"></i> Rooms <span class="text-danger">*</span>
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-outline-success" id="select-all-rooms">
                  <i class="bi bi-check-square"></i> Select All
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="form-text mb-3">Select rooms included in this package.</div>
              @if($rooms->count() > 0)
                <div class="room-selection"> <!-- Make scrollable if too many rooms -->
                  @foreach($rooms as $room)
                    <div class="form-check">
                      <input class="form-check-input @error('pkg_rooms') is-invalid @enderror" 
                             type="checkbox" 
                             id="room_{{ $room->id }}" 
                             name="pkg_rooms[]" 
                             value="{{ $room->id }}"
                             {{ in_array($room->id, old('pkg_rooms', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="room_{{ $room->id }}">
                        <div class="fw-bold">{{ $room->name }}</div>
                        <small class="text-muted">{{ $room->type->name ?? 'No Type' }}</small>
                      </label>
                    </div>
                  @endforeach
                </div>
                @error('pkg_rooms')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              @else
                <div class="alert alert-warning">
                  <i class="bi bi-exclamation-triangle"></i>
                  No rooms available for this property. Please create rooms first.
                </div>
              @endif
            </div>
          </div>

          <!-- Form Actions -->
          <div class="card mt-3">
            <div class="card-body">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-check-circle"></i> Create Package
                </button>
                <a href="{{ route('tenant.room-packages.index', ['property_id' => $propertyId]) }}" 
                   class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Back to Packages
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Add inclusion functionality
  document.getElementById('add-inclusion').addEventListener('click', function() {
    const container = document.getElementById('inclusions-container');
    const newRow = document.createElement('div');
    newRow.className = 'input-group mb-2';
    newRow.innerHTML = `
        <input type="text" class="form-control" name="pkg_inclusions[]" placeholder="What's included...">
        <button type="button" class="btn btn-outline-danger remove-inclusion">
            <i class="bi bi-dash"></i>
        </button>
    `;
    container.appendChild(newRow);
  });

  // Add exclusion functionality
  document.getElementById('add-exclusion').addEventListener('click', function() {
    const container = document.getElementById('exclusions-container');
    const newRow = document.createElement('div');
    newRow.className = 'input-group mb-2';
    newRow.innerHTML = `
        <input type="text" class="form-control" name="pkg_exclusions[]" placeholder="What's not included...">
        <button type="button" class="btn btn-outline-danger remove-exclusion">
            <i class="bi bi-dash"></i>
        </button>
    `;
    container.appendChild(newRow);
  });

  // Remove inclusion functionality
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-inclusion')) {
      const container = document.getElementById('inclusions-container');
      if (container.children.length > 1) {
        e.target.closest('.input-group').remove();
      }
    }
  });

  // Remove exclusion functionality
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-exclusion')) {
      const container = document.getElementById('exclusions-container');
      if (container.children.length > 1) {
        e.target.closest('.input-group').remove();
      }
    }
  });

  // Image preview functionality
  document.getElementById('pkg_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        // You can add image preview here if needed
        console.log('Image selected:', file.name);
      };
      reader.readAsDataURL(file);
    }
  });

  // Validate guest numbers
  const minGuestInput = document.getElementById('pkg_min_guests');
  const maxGuestInput = document.getElementById('pkg_max_guests');

  function validateGuestNumbers() {
    const minGuests = parseInt(minGuestInput.value) || 0;
    const maxGuests = parseInt(maxGuestInput.value) || 0;
    
    if (minGuests > 0 && maxGuests > 0 && minGuests > maxGuests) {
      maxGuestInput.setCustomValidity('Maximum guests must be greater than or equal to minimum guests');
    } else {
      maxGuestInput.setCustomValidity('');
    }
  }

  minGuestInput.addEventListener('input', validateGuestNumbers);
  maxGuestInput.addEventListener('input', validateGuestNumbers);

  // Validate dates
  const validFromInput = document.getElementById('pkg_valid_from');
  const validToInput = document.getElementById('pkg_valid_to');

  function validateDates() {
    const fromDate = validFromInput.value;
    const toDate = validToInput.value;
    
    if (fromDate && toDate && fromDate > toDate) {
      validToInput.setCustomValidity('End date must be after start date');
    } else {
      validToInput.setCustomValidity('');
    }
  }

  validFromInput.addEventListener('change', validateDates);
  validToInput.addEventListener('change', validateDates);

  // Select all rooms functionality (make it toggle)
  document.getElementById('select-all-rooms').addEventListener('click', function() {
    const roomCheckboxes = document.querySelectorAll('input[name="pkg_rooms[]"]');
    const allChecked = Array.from(roomCheckboxes).every(checkbox => checkbox.checked);
    roomCheckboxes.forEach(checkbox => {
      checkbox.checked = !allChecked;
    });
    // make the button text change accordingly
    this.innerHTML = allChecked ? '<i class="bi bi-check-square"></i> Select All' : '<i class="bi bi-x-square"></i> Deselect All';
  });
});
</script>

@endsection