@extends('central.layouts.app')

@section('title', 'Create User')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-plus"></i> <small class="text-muted">Create User</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.users.index') }}">Users</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<div class="content">
  <div class="container-fluid">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-8">
        <!-- Create User Form -->
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-user-plus me-2"></i>User Information
            </h5>
          </div>
          <form action="{{ route('central.users.store') }}" method="POST">
            @csrf
            <div class="card-body">
              <div class="row">
                <!-- Basic Information -->
                <div class="col-md-12">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-info-circle me-1"></i>Basic Information
                  </h6>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="name" class="form-label required">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                              id="name" name="name" value="{{ old('name') }}" required
                              placeholder="Enter full name">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="email" class="form-label required">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                              id="email" name="email" value="{{ old('email') }}" required
                              placeholder="user@example.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                              id="password" name="password" required
                              placeholder="Minimum 8 characters">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Must be at least 8 characters long</div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="password_confirmation" class="form-label required">Confirm Password</label>
                        <input type="password" class="form-control" 
                              id="password_confirmation" name="password_confirmation" required
                              placeholder="Re-enter password">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Role Assignment -->
                <div class="col-md-12 mt-3">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-user-tag me-1"></i>Role Assignment
                  </h6>
                  
                  <div class="mb-3">
                    <label for="role" class="form-label required">Select Role</label>
                    <select class="form-select @error('role') is-invalid @enderror" 
                            id="role" name="role" required>
                      <option value="">-- Select a Role --</option>
                      @foreach($roles as $role)
                      <option value="{{ $role->name }}" 
                              {{ old('role') == $role->name ? 'selected' : '' }}
                              data-description="{{ $role->description }}">
                        {{ $role->display_name ?: $role->name }}
                      </option>
                      @endforeach
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" id="role-description">Select a role to see its description</div>
                  </div>

                  <!-- Role Details Card -->
                  <div class="card card-outline card-secondary">
                    <div class="card-header">
                      <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-1"></i>Role Information
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-4">
                          <h6 class="text-danger">
                            <i class="fas fa-user-shield me-1"></i>Super Admin
                          </h6>
                          <p class="small mb-0">Full system access with all permissions. Can manage everything.</p>
                        </div>
                        <div class="col-md-4">
                          <h6 class="text-warning">
                            <i class="fas fa-user-tie me-1"></i>Super Manager
                          </h6>
                          <p class="small mb-0">Management access with most permissions. Limited admin capabilities.</p>
                        </div>
                        <div class="col-md-4">
                          <h6 class="text-info">
                            <i class="fas fa-headset me-1"></i>Support
                          </h6>
                          <p class="small mb-0">Customer support access. Can view and assist users.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('central.users.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Users
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Create User
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-4">
        <!-- Help Information -->
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-question-circle me-1"></i>User Creation Guidelines
            </h6>
          </div>
          <div class="card-body">
            <h6 class="text-primary">Account Security</h6>
            <ul class="small">
              <li>Use a strong password (minimum 8 characters)</li>
              <li>Include uppercase, lowercase, and numbers</li>
              <li>Email must be unique in the system</li>
            </ul>

            <h6 class="text-primary mt-3">Role Selection</h6>
            <ul class="small">
              <li>Choose the role based on user responsibilities</li>
              <li>Roles determine access permissions</li>
              <li>Roles can be changed later if needed</li>
            </ul>

            <h6 class="text-primary mt-3">Best Practices</h6>
            <ul class="small">
              <li>Use full legal names when possible</li>
              <li>Verify email address before creating</li>
              <li>Assign minimal required permissions</li>
            </ul>
          </div>
        </div>

        <!-- Security Notice -->
        <div class="card card-outline card-warning mt-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-shield-alt me-1"></i>Security Notice
            </h6>
          </div>
          <div class="card-body">
            <p class="small mb-2">
              <strong>Important:</strong> The user will need to reset their password on first login for security purposes.
            </p>
            <p class="small mb-0">
              Make sure to communicate the temporary password securely to the new user.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update role description when role is selected
    $('#role').change(function() {
        const selectedOption = $(this).find('option:selected');
        const description = selectedOption.data('description');
        
        if (description) {
            $('#role-description').text(description);
        } else {
            $('#role-description').text('Select a role to see its description');
        }
    });

    // Trigger on page load if there's an old value
    if ($('#role').val()) {
        $('#role').trigger('change');
    }
});
</script>
@endpush
@endsection
