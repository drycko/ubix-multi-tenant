@extends('tenant.layouts.app')

@section('title', 'Create Room')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-plus-circle"></i>
          <small class="text-muted">Create Room</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.rooms.index', ['property_id' => $propertyId]) }}">Rooms</a></li>
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

    <form action="{{ route('tenant.rooms.store') }}" method="POST" enctype="multipart/form-data" id="roomForm">
      @csrf
      <input type="hidden" name="property_id" value="{{ $propertyId }}">
      
      <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-info-circle"></i> Room Information
              </h3>
            </div>
            <div class="card-body">
              <!-- Room Number and Name -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="number" class="form-label">Room Number <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('number') is-invalid @enderror" 
                         id="number" 
                         name="number" 
                         value="{{ old('number', $nextNumber) }}" 
                         required>
                  @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-8">
                  <label for="name" class="form-label">Room Name <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('name') is-invalid @enderror" 
                         id="name" 
                         name="name" 
                         value="{{ old('name') }}" 
                         required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Short Code and Type -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="short_code" class="form-label">Short Code <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('short_code') is-invalid @enderror" 
                         id="short_code" 
                         name="short_code" 
                         value="{{ old('short_code') }}" 
                         placeholder="e.g., Q STD, 2x3/4 STD"
                         required>
                  @error('short_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="room_type_id" class="form-label">Room Type <span class="text-danger">*</span></label>
                  <select class="form-select @error('room_type_id') is-invalid @enderror" 
                          id="room_type_id" 
                          name="room_type_id" 
                          required>
                    <option value="">Select room type...</option>
                    @foreach($roomTypes as $roomType)
                      <option value="{{ $roomType->id }}" {{ old('room_type_id') == $roomType->id ? 'selected' : '' }}>
                        {{ $roomType->name }} ({{ $roomType->legacy_code ?? 'N/A' }})
                      </option>
                    @endforeach
                  </select>
                  @error('room_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Floor and Legacy Code -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="floor" class="form-label">Floor</label>
                  <input type="number" 
                         class="form-control @error('floor') is-invalid @enderror" 
                         id="floor" 
                         name="floor" 
                         value="{{ old('floor') }}" 
                         min="1">
                  @error('floor')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="legacy_room_code" class="form-label">Legacy Room Code</label>
                  <input type="number" 
                         class="form-control @error('legacy_room_code') is-invalid @enderror" 
                         id="legacy_room_code" 
                         name="legacy_room_code" 
                         value="{{ old('legacy_room_code') }}">
                  @error('legacy_room_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Descriptions -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="description" class="form-label">Internal Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="4"
                            placeholder="Internal room description for staff...">{{ old('description') }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label for="web_description" class="form-label">Website Description</label>
                  <textarea class="form-control @error('web_description') is-invalid @enderror" 
                            id="web_description" 
                            name="web_description" 
                            rows="4"
                            placeholder="Public description for website...">{{ old('web_description') }}</textarea>
                  @error('web_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Notes -->
              <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" 
                          name="notes" 
                          rows="3"
                          placeholder="Additional notes...">{{ old('notes') }}</textarea>
                @error('notes')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Room Image -->
              <div class="mb-3">
                <label for="web_image" class="form-label">Room Image</label>
                <input type="file" 
                       class="form-control @error('web_image') is-invalid @enderror" 
                       id="web_image" 
                       name="web_image" 
                       accept="image/*">
                @error('web_image')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Upload an image for this room (JPEG, PNG, JPG, GIF, WebP - Max: 2MB)</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
          <!-- Room Settings -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-gear"></i> Room Settings
              </h3>
            </div>
            <div class="card-body">
              <!-- Status Toggles -->
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" 
                         type="checkbox" 
                         id="is_enabled" 
                         name="is_enabled" 
                         {{ old('is_enabled', true) ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_enabled">
                    <strong>Room Enabled</strong>
                    <div class="form-text">Room is available for bookings</div>
                  </label>
                </div>
              </div>

              <div class="mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" 
                         type="checkbox" 
                         id="is_featured" 
                         name="is_featured" 
                         {{ old('is_featured') ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_featured">
                    <strong>Featured Room</strong>
                    <div class="form-text">Highlight this room on website</div>
                  </label>
                </div>
              </div>

              <!-- Display Order -->
              <div class="mb-3">
                <label for="display_order" class="form-label">Display Order</label>
                <input type="number" 
                       class="form-control @error('display_order') is-invalid @enderror" 
                       id="display_order" 
                       name="display_order" 
                       value="{{ old('display_order', $nextOrder) }}" 
                       min="1">
                @error('display_order')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Order in which rooms appear (lower = first)</div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="card mt-3">
            <div class="card-body">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-check-circle"></i> Create Room
                </button>
                <a href="{{ route('tenant.rooms.index', ['property_id' => $propertyId]) }}" 
                   class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Back to Rooms
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
  // Image preview functionality
  document.getElementById('web_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        console.log('Room image selected:', file.name);
      };
      reader.readAsDataURL(file);
    }
  });

  // Auto-generate short code based on room type selection
  const roomTypeSelect = document.getElementById('room_type_id');
  const shortCodeInput = document.getElementById('short_code');
  
  roomTypeSelect.addEventListener('change', function() {
    if (this.value && !shortCodeInput.value) {
      const selectedOption = this.options[this.selectedIndex];
      const roomTypeName = selectedOption.text;
      // Extract legacy code from the option text
      const legacyCodeMatch = roomTypeName.match(/\(([^)]+)\)/);
      if (legacyCodeMatch) {
        shortCodeInput.value = legacyCodeMatch[1];
      }
    }
  });
});
</script>

@endsection