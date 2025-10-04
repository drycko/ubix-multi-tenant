@extends('tenant.layouts.app')

@section('title', 'Edit Guest - ' . $guest->full_name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-edit"></i>
          Edit Guest
          <small class="text-muted">{{ $guest->full_name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.index') }}">Guests</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.show', $guest) }}">{{ $guest->full_name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    {{-- Error Messages --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h6>Please correct the following errors:</h6>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('tenant.guests.update', $guest) }}" method="POST">
      @csrf
      @method('PUT')
      
      <div class="row">
        <!-- Main Form -->
        <div class="col-md-8">
          <!-- Basic Information -->
          <div class="card card-primary card-outline mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-user"></i> Basic Information
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-2">
                  <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <select name="title" id="title" class="form-select">
                      <option value="">Select</option>
                      <option value="Mr" {{ old('title', $guest->title) === 'Mr' ? 'selected' : '' }}>Mr</option>
                      <option value="Mrs" {{ old('title', $guest->title) === 'Mrs' ? 'selected' : '' }}>Mrs</option>
                      <option value="Ms" {{ old('title', $guest->title) === 'Ms' ? 'selected' : '' }}>Ms</option>
                      <option value="Dr" {{ old('title', $guest->title) === 'Dr' ? 'selected' : '' }}>Dr</option>
                      <option value="Prof" {{ old('title', $guest->title) === 'Prof' ? 'selected' : '' }}>Prof</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="first_name" 
                           id="first_name" 
                           class="form-control @error('first_name') is-invalid @enderror" 
                           value="{{ old('first_name', $guest->first_name) }}" 
                           required>
                    @error('first_name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="last_name" 
                           id="last_name" 
                           class="form-control @error('last_name') is-invalid @enderror" 
                           value="{{ old('last_name', $guest->last_name) }}" 
                           required>
                    @error('last_name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $guest->email) }}" 
                           required>
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="phone" 
                           id="phone" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone', $guest->phone) }}" 
                           required>
                    @error('phone')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="id_number" class="form-label">ID Number</label>
                    <input type="text" 
                           name="id_number" 
                           id="id_number" 
                           class="form-control @error('id_number') is-invalid @enderror" 
                           value="{{ old('id_number', $guest->id_number) }}">
                    @error('id_number')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="nationality" class="form-label">Nationality</label>
                    <select name="nationality" id="nationality" class="form-select @error('nationality') is-invalid @enderror">
                      <option value="">Select Nationality</option>
                      @foreach($countries as $country)
                        <option value="{{ $country['code'] }}" {{ old('nationality', $guest->nationality) === $country['code'] ? 'selected' : '' }}>
                          {{ $country['name'] }}
                        </option>
                      @endforeach
                    </select>
                    @error('nationality')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                  
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="physical_address" class="form-label">Physical Address</label>
                      <textarea name="physical_address" 
                                id="physical_address" 
                                class="form-control @error('physical_address') is-invalid @enderror" 
                                rows="3">{{ old('physical_address', $guest->physical_address) }}</textarea>
                      @error('physical_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="residential_address" class="form-label">Residential Address</label>
                      <textarea name="residential_address" 
                                id="residential_address" 
                                class="form-control @error('residential_address') is-invalid @enderror" 
                                rows="3">{{ old('residential_address', $guest->residential_address) }}</textarea>
                      @error('residential_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="country_name" class="form-label">Country of Residence</label>
                      <select name="country_name" id="country_name" class="form-select @error('country_name') is-invalid @enderror">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                          <option value="{{ $country['name'] }}" {{ old('country_name', $guest->country_name) === $country['name'] ? 'selected' : '' }}>
                            {{ $country['name'] }}
                          </option>
                        @endforeach
                      </select>
                      @error('country_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact & Emergency Information -->
          <div class="card card-info card-outline mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-phone"></i> Contact & Emergency Information
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                    <input type="text" 
                           name="emergency_contact" 
                           id="emergency_contact" 
                           class="form-control @error('emergency_contact') is-invalid @enderror" 
                           value="{{ old('emergency_contact', $guest->emergency_contact) }}">
                    @error('emergency_contact')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                    <input type="text" 
                           name="emergency_contact_phone" 
                           id="emergency_contact_phone" 
                           class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                           value="{{ old('emergency_contact_phone', $guest->emergency_contact_phone) }}">
                    @error('emergency_contact_phone')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
            </div>
          </div>

          <!-- Additional Information -->
          <div class="card card-warning card-outline mb-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Additional Information
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="gown_size" class="form-label">Gown Size</label>
                    <select name="gown_size" id="gown_size" class="form-select @error('gown_size') is-invalid @enderror">
                      <option value="">Select Size</option>
                      <option value="XS" {{ old('gown_size', $guest->gown_size) === 'XS' ? 'selected' : '' }}>Extra Small (XS)</option>
                      <option value="S" {{ old('gown_size', $guest->gown_size) === 'S' ? 'selected' : '' }}>Small (S)</option>
                      <option value="M" {{ old('gown_size', $guest->gown_size) === 'M' ? 'selected' : '' }}>Medium (M)</option>
                      <option value="L" {{ old('gown_size', $guest->gown_size) === 'L' ? 'selected' : '' }}>Large (L)</option>
                      <option value="XL" {{ old('gown_size', $guest->gown_size) === 'XL' ? 'selected' : '' }}>Extra Large (XL)</option>
                      <option value="XXL" {{ old('gown_size', $guest->gown_size) === 'XXL' ? 'selected' : '' }}>Double XL (XXL)</option>
                    </select>
                    @error('gown_size')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="car_registration" class="form-label">Car Registration</label>
                    <input type="text" 
                           name="car_registration" 
                           id="car_registration" 
                           class="form-control @error('car_registration') is-invalid @enderror" 
                           value="{{ old('car_registration', $guest->car_registration) }}">
                    @error('car_registration')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="medical_notes" class="form-label">Medical Notes</label>
                    <textarea name="medical_notes" 
                              id="medical_notes" 
                              class="form-control @error('medical_notes') is-invalid @enderror" 
                              rows="3" 
                              placeholder="Any medical conditions or allergies...">{{ old('medical_notes', $guest->medical_notes) }}</textarea>
                    @error('medical_notes')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="dietary_preferences" class="form-label">Dietary Preferences</label>
                    <textarea name="dietary_preferences" 
                              id="dietary_preferences" 
                              class="form-control @error('dietary_preferences') is-invalid @enderror" 
                              rows="3" 
                              placeholder="Vegetarian, vegan, allergies, etc...">{{ old('dietary_preferences', $guest->dietary_preferences) }}</textarea>
                    @error('dietary_preferences')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
          <!-- Guest Clubs -->
          @if($guestClubs->count() > 0)
          <div class="card card-success card-outline mb-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-users"></i> Guest Club Memberships
              </h5>
            </div>
            <div class="card-body">
              @foreach($guestClubs as $club)
              <div class="form-check mb-2">
                <input class="form-check-input" 
                       type="checkbox" 
                       name="guest_club_id[]" 
                       value="{{ $club->id }}" 
                       id="club{{ $club->id }}"
                       {{ in_array($club->id, old('guest_club_id', $guestClubsForGuest)) ? 'checked' : '' }}>
                <label class="form-check-label" for="club{{ $club->id }}">
                  {{ $club->name }}
                  @if($club->description)
                  <br><small class="text-muted">{{ $club->description }}</small>
                  @endif
                </label>
              </div>
              @endforeach
              @error('guest_club_id')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
          @endif

          <!-- Status -->
          <div class="card card-secondary card-outline mb-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-toggle-on"></i> Guest Status
              </h5>
            </div>
            <div class="card-body">
              <div class="form-check form-switch">
                <input class="form-check-input" 
                       type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       {{ old('is_active', $guest->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  Active Guest
                </label>
              </div>
              <small class="text-muted">Active guests can make new bookings</small>
            </div>
          </div>

          <!-- Guest Stats -->
          <div class="card card-info card-outline mb-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-chart-bar"></i> Guest Statistics
              </h5>
            </div>
            <div class="card-body">
              <table class="table table-sm">
                <tr>
                  <td><strong>Guest ID:</strong></td>
                  <td>#{{ $guest->id }}</td>
                </tr>
                <tr>
                  <td><strong>Total Bookings:</strong></td>
                  <td><span class="badge bg-primary">{{ $guest->bookings->count() }}</span></td>
                </tr>
                <tr>
                  <td><strong>Registered:</strong></td>
                  <td>{{ $guest->created_at->format('M j, Y') }}</td>
                </tr>
                @if($guest->updated_at != $guest->created_at)
                <tr>
                  <td><strong>Last Updated:</strong></td>
                  <td>{{ $guest->updated_at->format('M j, Y') }}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="card">
            <div class="card-body">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning">
                  <i class="fas fa-save"></i> Update Guest
                </button>
                <a href="{{ route('tenant.guests.show', $guest) }}" class="btn btn-outline-primary">
                  <i class="fas fa-eye"></i> View Guest
                </a>
                <a href="{{ route('tenant.guests.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left"></i> Back to Guests
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
@endsection