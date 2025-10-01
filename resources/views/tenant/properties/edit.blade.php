@extends('tenant.layouts.app')

@section('title', 'Edit Property')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">
          Edit Property
          <small class="text-muted" style="font-size: 1.2rem;!important">{{ $property->name }}</small>
        </h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.properties.index') }}">Properties</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.properties.show', $property) }}">{{ $property->name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">

    {{-- messages from redirect --}}
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

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif 
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="bi bi-building"></i> Property Information
            </h5>
            <div>
              <span class="badge bg-{{ $property->is_active ? 'success' : 'danger' }}">
                {{ $property->is_active ? 'Active' : 'Inactive' }}
              </span>
              <small class="text-muted ms-2">
                Created {{ \Carbon\Carbon::parse($property->created_at)->format('M d, Y') }}
              </small>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.properties.update', $property) }}" method="POST">
              @csrf
              @method('PUT')
              
              <!-- Basic Information -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-success border-bottom pb-2 mb-3">
                    <i class="bi bi-info-circle"></i> Basic Information
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Property Name <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('name') is-invalid @enderror" 
                         id="name" 
                         name="name" 
                         value="{{ old('name', $property->name) }}" 
                         placeholder="e.g., Grand Hotel Downtown"
                         required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="code" class="form-label">Property Code <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('code') is-invalid @enderror" 
                         id="code" 
                         name="code" 
                         value="{{ old('code', $property->code) }}" 
                         placeholder="e.g., GHD001"
                         required>
                  <div class="form-text">Unique code for this property</div>
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                {{-- <div class="col-12 mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3" 
                            placeholder="Brief description of the property...">{{ old('description', $property->description) }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div> --}}
              </div>

              <!-- Location Information -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-success border-bottom pb-2 mb-3">
                    <i class="bi bi-geo-alt"></i> Location
                  </h6>
                </div>
                
                <div class="col-12 mb-3">
                  <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('address') is-invalid @enderror" 
                         id="address" 
                         name="address" 
                         value="{{ old('address', $property->address) }}" 
                         placeholder="Street address"
                         required>
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                  <input type="text" 
                         class="form-control @error('city') is-invalid @enderror" 
                         id="city" 
                         name="city" 
                         value="{{ old('city', $property->city) }}" 
                         placeholder="City"
                         required>
                  @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="state" class="form-label">State/Province</label>
                  <input type="text" 
                         class="form-control @error('state') is-invalid @enderror" 
                         id="state" 
                         name="state" 
                         value="{{ old('state', $property->state) }}" 
                         placeholder="State or Province">
                  @error('state')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="country" class="form-label">Select Country <span class="text-danger">*</span></label>
                  <select class="form-control select2-single @error('country') is-invalid @enderror" 
                          id="country" 
                          name="country" 
                          required>
                    <option value="">Select Country</option>
                    @foreach(get_countries() as $country)
                      <option value="{{ $country['name'] }}" {{ old('country', $property->country) == $country['name'] ? 'selected' : '' }}>
                        {{ $country['name'] }}
                      </option>
                    @endforeach
                  </select>
                  @error('country')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="zip_code" class="form-label">Postal Code</label>
                  <input type="text" 
                         class="form-control @error('zip_code') is-invalid @enderror" 
                         id="zip_code" 
                         name="zip_code" 
                         value="{{ old('zip_code', $property->zip_code ?? $property->postal_code) }}" 
                         placeholder="Postal/ZIP code">
                  @error('zip_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Contact Information -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-success border-bottom pb-2 mb-3">
                    <i class="bi bi-telephone"></i> Contact Information
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Phone</label>
                  <input type="text" 
                         class="form-control @error('phone') is-invalid @enderror" 
                         id="phone" 
                         name="phone" 
                         value="{{ old('phone', $property->phone) }}" 
                         placeholder="Phone number">
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" 
                         class="form-control @error('email') is-invalid @enderror" 
                         id="email" 
                         name="email" 
                         value="{{ old('email', $property->email) }}" 
                         placeholder="Email address">
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="website" class="form-label">Website</label>
                  <input type="url" 
                         class="form-control @error('website') is-invalid @enderror" 
                         id="website" 
                         name="website" 
                         value="{{ old('website', $property->website) }}" 
                         placeholder="https://www.example.com">
                  @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Configuration -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-success border-bottom pb-2 mb-3">
                    <i class="bi bi-gear"></i> Configuration
                  </h6>
                </div>
                
                <div class="col-md-4 mb-3">
                  <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                  <select class="form-select @error('currency') is-invalid @enderror" 
                          id="currency" 
                          name="currency" 
                          required>
                    <option value="">Select Currency</option>
                    @foreach(get_supported_currencies() as $code => $name)
                      <option value="{{ $code }}" {{ old('currency', $property->currency) == $code ? 'selected' : '' }}>
                        {{ $code }} - {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  @error('currency')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                  <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                  <select class="form-select @error('timezone') is-invalid @enderror" 
                          id="timezone" 
                          name="timezone" 
                          required>
                    <option value="">Select Timezone</option>
                    @foreach(get_supported_timezones() as $timezone => $description)
                      <option value="{{ $timezone }}" {{ old('timezone', $property->timezone) == $timezone ? 'selected' : '' }}>
                        {{ $description }}
                      </option>
                    @endforeach
                  </select>
                  @error('timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-4 mb-3">
                  <label for="locale" class="form-label">Locale <span class="text-danger">*</span></label>
                  <select class="form-select @error('locale') is-invalid @enderror" 
                          id="locale" 
                          name="locale" 
                          required>
                    <option value="">Select Locale</option>
                    @foreach(get_supported_locales() as $locale => $description)
                      <option value="{{ $locale }}" {{ old('locale', $property->locale ?? 'en_US') == $locale ? 'selected' : '' }}>
                        {{ $description }} ({{ $locale }})
                      </option>
                    @endforeach
                  </select>
                  @error('locale')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-3 mb-3">
                  <label for="max_rooms" class="form-label">Max Rooms <span class="text-danger">*</span></label>
                  <input type="number" 
                         class="form-control @error('max_rooms') is-invalid @enderror" 
                         id="max_rooms" 
                         name="max_rooms" 
                         value="{{ old('max_rooms', $property->max_rooms) }}" 
                         placeholder="Enter max rooms">
                  @error('max_rooms')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-3 mb-3">
                  <label for="check_in_time" class="form-label">Check-In Time</label>
                  <input type="time"
                         class="form-control @error('check_in_time') is-invalid @enderror" 
                         id="check_in_time" 
                         name="check_in_time" 
                         value="{{ old('check_in_time', $property->check_in_time) }}" 
                         placeholder="e.g., 14:00">
                  @error('check_in_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-3 mb-3">
                  <label for="check_out_time" class="form-label">Check-Out Time</label>
                  <input type="time"
                         class="form-control @error('check_out_time') is-invalid @enderror" 
                         id="check_out_time" 
                         name="check_out_time" 
                         value="{{ old('check_out_time', $property->check_out_time) }}" 
                         placeholder="e.g., 11:00">
                  @error('check_out_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-3 mb-3">
                  <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $property->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      <strong>Active Property</strong>
                      <small class="text-muted d-block">Property will be available for operations</small>
                    </label>
                  </div>
                </div>

              </div>

              <!-- Statistics (Read-only) -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-info border-bottom pb-2 mb-3">
                    <i class="bi bi-graph-up"></i> Statistics
                  </h6>
                </div>
                
                <div class="col-md-3 mb-3">
                  <div class="card bg-light">
                    <div class="card-body text-center">
                      <h5 class="card-title text-primary">{{ $property->rooms_count }}</h5>
                      <small class="text-muted">Rooms</small>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 mb-3">
                  <div class="card bg-light">
                    <div class="card-body text-center">
                      <h5 class="card-title text-warning">{{ $property->users_count }}</h5>
                      <small class="text-muted">Users</small>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 mb-3">
                  <div class="card bg-light">
                    <div class="card-body text-center">
                      <h5 class="card-title text-success">{{ $property->bookings_count ?? 0 }}</h5>
                      <small class="text-muted">Bookings</small>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 mb-3">
                  <div class="card bg-light">
                    <div class="card-body text-center">
                      <h5 class="card-title text-info">{{ $property->guests_count ?? 0 }}</h5>
                      <small class="text-muted">Guests</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.properties.show', $property) }}" class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Back to Details
                </a>
                <div>
                  <a href="{{ route('tenant.properties.index') }}" class="btn btn-outline-info me-2">
                    <i class="bi bi-list"></i> All Properties
                  </a>
                  <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-lg"></i> Update Property
                  </button>
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
// Auto-generate property code from name (disabled for editing to prevent accidental changes)
// Uncomment if you want to enable auto-generation in edit mode
/*
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const codeField = document.getElementById('code');
    
    if (name && !codeField.dataset.manuallyChanged) {
        // Generate code from name
        const code = name
            .toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .split(' ')
            .map(word => word.substring(0, 3))
            .join('')
            .substring(0, 6);
        
        codeField.value = code;
    }
});

// Track if code was manually changed
document.getElementById('code').addEventListener('input', function() {
    this.dataset.manuallyChanged = 'true';
});
*/
</script>
@endpush
@endsection