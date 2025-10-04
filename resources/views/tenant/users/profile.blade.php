@extends('tenant.layouts.app')

@section('title', 'My Profile')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-circle"></i> My Profile
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Profile</li>
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

    <!-- Profile Header -->
    <div class="card card-primary card-outline mb-4">
      <div class="card-header">
        <div class="d-flex align-items-center">
          <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
               class="rounded-circle me-3" width="80" height="80" style="object-fit: cover;">
          <div>
            <h5 class="card-title mb-0">{{ $user->name }}</h5><br>
            <p class="text-muted mb-0">{{ $user->email }}</p>
            <small class="text-muted">
              @if($user->property)
                {{ $user->property->name }} • 
              @endif
              {{ ucfirst(str_replace('-', ' ', $user->role)) }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="card card-info card-outline">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-edit me-2"></i>Update Profile Information
        </h5>
      </div>
      <form action="{{ route('tenant.users.update-profile') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
          <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">
                <i class="fas fa-user me-1"></i>Personal Information
              </h6>
              
              <div class="mb-3">
                <label for="name" class="form-label required">Full Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="email" class="form-label required">Email Address</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          id="address" name="address" rows="3" 
                          placeholder="Street address, city, state, postal code">{{ old('address', $user->address) }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Security & Photo -->
            <div class="col-md-6">
              <h6 class="text-primary mb-3">
                <i class="fas fa-lock me-1"></i>Security & Photo
              </h6>

              <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password">
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Leave blank to keep current password</div>
              </div>

              <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
              </div>

              <div class="mb-3">
                <label for="profile_photo" class="form-label">Profile Photo</label>
                @if($user->profile_photo_path)
                <div class="mb-2">
                  <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                       class="rounded-circle" width="100" height="100" style="object-fit: cover;">
                  <div class="form-text">Current profile photo</div>
                </div>
                @endif
                <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                       id="profile_photo" name="profile_photo" accept="image/*">
                @error('profile_photo')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Max size: 2MB. Supported: JPEG, PNG, JPG, GIF</div>
              </div>
            </div>
          </div>

          <!-- Read-only Information -->
          <div class="row mt-4">
            <div class="col-12">
              <hr>
              <h6 class="text-info mb-3">
                <i class="fas fa-info-circle me-1"></i>Account Information
              </h6>
              <div class="row">
                <div class="col-md-3">
                  <div class="mb-3">
                    <strong>Property:</strong><br>
                    @if($user->property)
                      <span class="badge bg-info">{{ $user->property->name }}</span>
                    @else
                      <span class="text-muted">No Property Assigned</span>
                    @endif
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <strong>Role:</strong><br>
                    @php
                      $roleColors = [
                        'super-user' => 'danger',
                        'super-manager' => 'warning',
                        'property-admin' => 'primary',
                        'manager' => 'success',
                        'receptionist' => 'info',
                        'housekeeping' => 'secondary',
                        'accountant' => 'dark',
                        'support' => 'light',
                        'guest' => 'outline-secondary'
                      ];
                      $roleColor = $roleColors[$user->role] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $roleColor }}">
                      {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                    </span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <strong>Position:</strong><br>
                    <span class="text-muted">{{ $user->position ?? 'Not specified' }}</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <strong>Account Status:</strong><br>
                    @if($user->is_active)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-danger">Inactive</span>
                    @endif
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-2">
                    <strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}
                    <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-2">
                    <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y') }}
                    <small class="text-muted d-block">{{ $user->updated_at->diffForHumans() }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="d-flex justify-content-between">
            <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
            <div>
              <button type="reset" class="btn btn-outline-warning me-2">
                <i class="fas fa-undo me-1"></i>Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Profile
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    
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
      console.log('New profile photo selected:', file.name);
    };
    reader.readAsDataURL(file);
  }
});

// Password field validation
document.getElementById('password').addEventListener('input', function(e) {
  const confirmField = document.getElementById('password_confirmation');
  if (e.target.value === '') {
    confirmField.removeAttribute('required');
  } else {
    confirmField.setAttribute('required', 'required');
  }
});
</script>
@endpush