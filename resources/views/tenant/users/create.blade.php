@extends('tenant.layouts.app')

@section('title', 'Create User')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-plus"></i> Create User
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.users.index') }}">Users</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
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

    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form action="{{ route('tenant.users.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="row">
        <!-- Basic Information -->
        <div class="col-md-8">
          <!-- Create User Form -->
          <div class="card card-success card-outline">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-user-plus me-2"></i>User Information
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-user me-1"></i>Basic Information
                  </h6>
                  
                  <div class="mb-3">
                    <label for="name" class="form-label required">Full Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                          id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="email" class="form-label required">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                          id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                          id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                              id="address" name="address" rows="3" 
                              placeholder="Street address, city, state, postal code">{{ old('address') }}</textarea>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="position" class="form-label">Position/Title</label>
                    <input type="text" class="form-control @error('position') is-invalid @enderror" 
                          id="position" name="position" value="{{ old('position') }}"
                          placeholder="e.g., Front Desk Manager, Housekeeping Supervisor">
                    @error('position')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <!-- Access & Security -->
                <div class="col-md-6">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-lock me-1"></i>Access & Security
                  </h6>

                  <div class="mb-3">
                    <label for="password" class="form-label required">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                          id="password" name="password" required>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Minimum 8 characters required</div>
                  </div>

                  <div class="mb-3">
                    <label for="password_confirmation" class="form-label required">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                  </div>

                  <div class="mb-3">
                    <label for="property_id" class="form-label required">Property Assignment</label>
                    <select class="form-select @error('property_id') is-invalid @enderror" 
                            id="property_id" name="property_id" required>
                      <option value="">Select Property</option>
                      @foreach($properties as $property)
                      <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                        {{ $property->name }}
                      </option>
                      @endforeach
                    </select>
                    @error('property_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="role" class="form-label required">Role</label>
                    <select class="form-select @error('role') is-invalid @enderror" 
                            id="role" name="role" required>
                      <option value="">Select Role</option>
                      @foreach($roles as $role)
                      <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('-', ' ', $role)) }}
                      </option>
                      @endforeach
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                      <small>
                        <strong>Roles:</strong><br>
                        <em>Super User:</em> Full system access<br>
                        <em>Manager:</em> Property management<br>
                        <em>Receptionist:</em> Bookings & guests<br>
                        <em>Housekeeping:</em> Room management
                      </small>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="profile_photo" class="form-label">Profile Photo</label>
                    <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                          id="profile_photo" name="profile_photo" accept="image/*">
                    @error('profile_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Max size: 2MB. Supported: JPEG, PNG, JPG, GIF</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Sidebar -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <!-- Status -->
              <div class="row">
                <div class="col-12">
                  <hr>
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-toggle-on me-1"></i>Account Status
                  </h6>
                  
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                          {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      <strong>Active Account</strong>
                      <div class="form-text">User can log in and access the system</div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mt-3">
            <div class="card-body">
              <div class="d-grid gap-2">
                <button type="reset" class="btn btn-outline-warning ">
                  <i class="fas fa-undo me-1"></i>Reset
                </button>
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save me-1"></i>Create User
                </button>
                <a href="{{ route('tenant.users.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Users
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@push('scripts')
<script>
// Preview uploaded image
document.getElementById('profile_photo').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      // You could add image preview functionality here
      console.log('Image selected:', file.name);
    };
    reader.readAsDataURL(file);
  }
});

// Role descriptions
document.getElementById('role').addEventListener('change', function(e) {
  const role = e.target.value;
  const descriptions = {
    'super-user': 'Complete system access across all properties',
    'super-manager': 'Multi-property management capabilities',
    'property-admin': 'Full access to assigned property',
    'manager': 'Property operations and staff management',
    'receptionist': 'Guest services and booking management',
    'housekeeping': 'Room maintenance and cleaning schedules',
    'accountant': 'Financial records and reporting',
    'support': 'Customer support and basic operations',
    'guest': 'Limited guest portal access'
  };
  
  if (descriptions[role]) {
    // Update description if you have a description element
    console.log('Role selected:', role, descriptions[role]);
  }
});
</script>
@endpush