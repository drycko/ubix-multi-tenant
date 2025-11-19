@extends('tenant.layouts.app')

@section('title', 'General Settings')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0 text-muted">
          <i class="fas fa-sliders-h"></i> General Settings
        </h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.settings.index') }}">Settings</a></li>
          <li class="breadcrumb-item active" aria-current="page">General Settings</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">

    {{-- messages from redirect --}}
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

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('tenant.settings.general.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="row justify-content-center">
        <div class="col-md-10">
          <div class="card card-secondary card-outline">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-sliders-h me-2"></i>General Configuration
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                {{-- Tenant Name --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_name" class="form-label required">Tenant Name</label>
                  <input type="text" class="form-control @error('tenant_name') is-invalid @enderror" 
                         id="tenant_name" name="tenant_name" 
                         value="{{ old('tenant_name', $settings['tenant_name'] ?? '') }}" required>
                  @error('tenant_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Admin Email --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_admin_email" class="form-label required">Admin Email</label>
                  <input type="email" class="form-control @error('tenant_admin_email') is-invalid @enderror" 
                         id="tenant_admin_email" name="tenant_admin_email" 
                         value="{{ old('tenant_admin_email', $settings['tenant_admin_email'] ?? '') }}" required>
                  @error('tenant_admin_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Phone --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_phone" class="form-label">Phone</label>
                  <input type="text" class="form-control @error('tenant_phone') is-invalid @enderror" 
                         id="tenant_phone" name="tenant_phone" 
                         value="{{ old('tenant_phone', $settings['tenant_phone'] ?? '') }}">
                  @error('tenant_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Website --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_website" class="form-label">Website</label>
                  <input type="url" class="form-control @error('tenant_website') is-invalid @enderror" 
                         id="tenant_website" name="tenant_website" 
                         placeholder="https://example.com"
                         value="{{ old('tenant_website', $settings['tenant_website'] ?? '') }}">
                  @error('tenant_website')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Address Section --}}
                <div class="col-12 mt-3 mb-2">
                  <h6 class="text-muted">
                    <i class="fas fa-map-marker-alt me-2"></i>Address Information
                  </h6>
                  <hr>
                </div>

                {{-- Street Address --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_address_street" class="form-label">Street Address</label>
                  <input type="text" class="form-control @error('tenant_address_street') is-invalid @enderror" 
                         id="tenant_address_street" name="tenant_address_street" 
                         value="{{ old('tenant_address_street', $settings['tenant_address_street'] ?? '') }}">
                  @error('tenant_address_street')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Street Address 2 --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_address_street_2" class="form-label">Street Address 2</label>
                  <input type="text" class="form-control @error('tenant_address_street_2') is-invalid @enderror" 
                         id="tenant_address_street_2" name="tenant_address_street_2" 
                         placeholder="Apartment, suite, etc. (optional)"
                         value="{{ old('tenant_address_street_2', $settings['tenant_address_street_2'] ?? '') }}">
                  @error('tenant_address_street_2')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- City --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_address_city" class="form-label">City</label>
                  <input type="text" class="form-control @error('tenant_address_city') is-invalid @enderror" 
                         id="tenant_address_city" name="tenant_address_city" 
                         value="{{ old('tenant_address_city', $settings['tenant_address_city'] ?? '') }}">
                  @error('tenant_address_city')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- State/Province --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_address_state" class="form-label">State/Province</label>
                  <input type="text" class="form-control @error('tenant_address_state') is-invalid @enderror" 
                         id="tenant_address_state" name="tenant_address_state" 
                         value="{{ old('tenant_address_state', $settings['tenant_address_state'] ?? '') }}">
                  @error('tenant_address_state')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- ZIP/Postal Code --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_address_zip" class="form-label">ZIP/Postal Code</label>
                  <input type="text" class="form-control @error('tenant_address_zip') is-invalid @enderror" 
                         id="tenant_address_zip" name="tenant_address_zip" 
                         value="{{ old('tenant_address_zip', $settings['tenant_address_zip'] ?? '') }}">
                  @error('tenant_address_zip')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Country --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_address_country" class="form-label">Country</label>
                  <input type="text" class="form-control @error('tenant_address_country') is-invalid @enderror" 
                         id="tenant_address_country" name="tenant_address_country" 
                         value="{{ old('tenant_address_country', $settings['tenant_address_country'] ?? '') }}">
                  @error('tenant_address_country')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Business Information Section --}}
                <div class="col-12 mt-3 mb-2">
                  <h6 class="text-muted">
                    <i class="fas fa-briefcase me-2"></i>Business Information
                  </h6>
                  <hr>
                </div>

                {{-- Tax Number --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_tax_number" class="form-label">Tax Number</label>
                  <input type="text" class="form-control @error('tenant_tax_number') is-invalid @enderror" 
                         id="tenant_tax_number" name="tenant_tax_number" 
                         placeholder="VAT/Tax ID"
                         value="{{ old('tenant_tax_number', $settings['tenant_tax_number'] ?? '') }}">
                  @error('tenant_tax_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Registration Number --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_registration_number" class="form-label">Registration Number</label>
                  <input type="text" class="form-control @error('tenant_registration_number') is-invalid @enderror" 
                         id="tenant_registration_number" name="tenant_registration_number" 
                         placeholder="Business Registration Number"
                         value="{{ old('tenant_registration_number', $settings['tenant_registration_number'] ?? '') }}">
                  @error('tenant_registration_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Regional Settings Section --}}
                <div class="col-12 mt-3 mb-2">
                  <h6 class="text-muted">
                    <i class="fas fa-globe me-2"></i>Regional Settings
                  </h6>
                  <hr>
                </div>

                {{-- Currency --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_currency" class="form-label">Currency</label>
                  <select class="form-control @error('tenant_currency') is-invalid @enderror" 
                          id="tenant_currency" name="tenant_currency">
                    <option value="">Select Currency</option>
                    <option value="ZAR" {{ old('tenant_currency', $settings['tenant_currency'] ?? '') == 'ZAR' ? 'selected' : '' }}>ZAR - South African Rand</option>
                    <option value="USD" {{ old('tenant_currency', $settings['tenant_currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    <option value="EUR" {{ old('tenant_currency', $settings['tenant_currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    <option value="GBP" {{ old('tenant_currency', $settings['tenant_currency'] ?? '') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                    <option value="AUD" {{ old('tenant_currency', $settings['tenant_currency'] ?? '') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                    <option value="CAD" {{ old('tenant_currency', $settings['tenant_currency'] ?? '') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                  </select>
                  @error('tenant_currency')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Timezone --}}
                <div class="col-md-6 mb-3">
                  <label for="tenant_timezone" class="form-label">Timezone</label>
                  <select class="form-control @error('tenant_timezone') is-invalid @enderror" 
                          id="tenant_timezone" name="tenant_timezone">
                    <option value="">Select Timezone</option>
                    <option value="Africa/Johannesburg" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'Africa/Johannesburg' ? 'selected' : '' }}>Africa/Johannesburg</option>
                    <option value="America/New_York" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                    <option value="America/Los_Angeles" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                    <option value="Europe/London" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                    <option value="Europe/Paris" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                    <option value="Asia/Dubai" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                    <option value="Australia/Sydney" {{ old('tenant_timezone', $settings['tenant_timezone'] ?? '') == 'Australia/Sydney' ? 'selected' : '' }}>Australia/Sydney</option>
                  </select>
                  @error('tenant_timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                {{-- Branding Section --}}
                <div class="col-12 mt-3 mb-2">
                  <h6 class="text-muted">
                    <i class="fas fa-image me-2"></i>Branding
                  </h6>
                  <hr>
                </div>

                {{-- Logo Upload --}}
                <div class="col-md-12 mb-3">
                  <label for="tenant_logo" class="form-label">Tenant Logo</label>
                  @if(isset($settings['tenant_logo']) && $settings['tenant_logo'])
                    <div class="mb-2">
                      @php
                      if (config('app.env') === 'production' && config('filesystems.default') === 'gcs') {
                        $gcsConfig = config('filesystems.disks.gcs');
                        $bucket = $gcsConfig['bucket'] ?? null;
                        $logo_path = "tenant{{tenant('id')}}/{$settings['tenant_logo']}";
                        $path = ltrim($settings['tenant_logo'], '/');
                        $logoUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
                      } else {
                        
                        // $tenant_branding_path = asset('storage/branding');
                        $logoUrl = asset('storage/' . $settings['tenant_logo']);
                      }
                      @endphp
                      <img src="{{ $logoUrl }}" 
                           alt="Current Logo" 
                           class="img-thumbnail" 
                           style="max-height: 100px;">
                      <p class="text-muted small mt-1">Current logo</p>
                    </div>
                  @endif
                  <input type="file" class="form-control @error('tenant_logo') is-invalid @enderror" 
                         id="tenant_logo" name="tenant_logo" 
                         accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml">
                  <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB</small>
                  @error('tenant_logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="reset" class="btn btn-outline-secondary">
                <i class="fas fa-undo me-1"></i>Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Settings
              </button>
              <a href="{{ route('tenant.settings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Settings
              </a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<!--end::App Content-->
@endsection
