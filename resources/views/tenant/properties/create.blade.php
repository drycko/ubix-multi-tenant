@extends('tenant.layouts.app')

@section('title', 'Create Property')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          {{-- <i class="bi bi-plus-circle"></i> --}}
          <small class="text-muted">Add New Property</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.properties.index') }}">Properties</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
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
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-building"></i> Property Information
            </h5>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.properties.store') }}" method="POST">
              @csrf
              
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
                         value="{{ old('name') }}" 
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
                         value="{{ old('code') }}" 
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
                            placeholder="Brief description of the property...">{{ old('description') }}</textarea>
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
                         value="{{ old('address') }}" 
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
                         value="{{ old('city') }}" 
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
                         value="{{ old('state') }}" 
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
                      <option value="{{ $country['name'] }}" {{ old('country') == $country['name'] ? 'selected' : '' }}>
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
                         value="{{ old('zip_code') }}" 
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
                         value="{{ old('phone') }}" 
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
                         value="{{ old('email') }}" 
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
                         value="{{ old('website') }}" 
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
                      <option value="{{ $code }}" {{ old('currency') == $code ? 'selected' : '' }}>
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
                      <option value="{{ $timezone }}" {{ old('timezone') == $timezone ? 'selected' : '' }}>
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
                      <option value="{{ $locale }}" {{ old('locale', 'en_US') == $locale ? 'selected' : '' }}>
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
                         value="{{ old('max_rooms') }}" 
                         placeholder="Enter max rooms">
                  @error('max_rooms')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-3 mb-3">
                  <label for="check_in_time" class="form-label">Check-in Time</label>
                  <input type="text" 
                         class="form-control @error('check_in_time') is-invalid @enderror" 
                         id="check_in_time" 
                         name="check_in_time" 
                         value="{{ old('check_in_time') }}" 
                         placeholder="Enter check-in time">
                  @error('check_in_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-3 mb-3">
                  <label for="check_out_time" class="form-label">Check-out Time</label>
                  <input type="text" 
                         class="form-control @error('check_out_time') is-invalid @enderror" 
                         id="check_out_time" 
                         name="check_out_time" 
                         value="{{ old('check_out_time') }}" 
                         placeholder="Enter check-out time">
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
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      <strong>Active Property</strong>
                      <small class="text-muted d-block">Property will be available for operations immediately</small>
                    </label>
                  </div>
                </div>

              </div>

              <!-- Action Buttons -->
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.properties.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </button>
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg"></i> Create Property
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
// Auto-generate property code from name
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
</script>
@endpush
@endsection