@extends('central.layouts.app')

@section('title', 'Edit User: ' . $user->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-edit"></i> <small class="text-muted">Edit User</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.users.index') }}">Users</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
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

    <div class="row mb-3">
      <div class="col-8">

        <!-- Edit User Form -->
        <div class="card card-warning card-outline">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-user-edit me-2"></i>User Information
              </h5>
              @if($user->id === auth()->id())
              <span class="badge bg-info">
                <i class="fas fa-user me-1"></i>Editing Your Account
              </span>
              @endif
            </div>
          </div>
          <form action="{{ route('central.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
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
                              id="name" name="name" value="{{ old('name', $user->name) }}" required
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
                              id="email" name="email" value="{{ old('email', $user->email) }}" required
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
                        <label for="password" class="form-label">New Password (Optional)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                              id="password" name="password"
                              placeholder="Leave blank to keep current password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Only fill if you want to change the password</div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" 
                              id="password_confirmation" name="password_confirmation"
                              placeholder="Re-enter new password">
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
                            id="role" name="role" required
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                      <option value="">-- Select a Role --</option>
                      @foreach($roles as $role)
                      <option value="{{ $role->name }}" 
                              {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}
                              data-description="{{ $role->description }}">
                        {{ $role->display_name ?: $role->name }}
                      </option>
                      @endforeach
                    </select>
                    @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->roles->first()?->name }}">
                    @endif
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" id="role-description">
                      @if($user->id === auth()->id())
                      You cannot change your own role
                      @else
                      Select a role to see its description
                      @endif
                    </div>
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

                <!-- Account Information -->
                <div class="col-md-12 mt-3">
                  <div class="card">
                    <div class="card-body">
                      <h6 class="text-info mb-3">
                        <i class="fas fa-info-circle me-1"></i>Account Information
                      </h6>
                      <div class="row">
                        <div class="col-md-6 mb-2">
                          <strong>User ID:</strong> 
                          <span class="badge bg-secondary">{{ $user->id }}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                          <strong>Account Created:</strong> {{ $user->created_at->format('M d, Y \a\t g:i A') }}
                        </div>
                        <div class="col-md-6 mb-2">
                          <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y \a\t g:i A') }}
                        </div>
                        <div class="col-md-6 mb-2">
                          <strong>Email Verified:</strong> 
                          @if($user->email_verified_at)
                          <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Verified
                          </span>
                          @else
                          <span class="badge bg-warning">
                            <i class="fas fa-clock me-1"></i>Pending
                          </span>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Changes Warning -->
              @if($user->id === auth()->id())
              <div class="row mt-4">
                <div class="col-12">
                  <div class="alert alert-info">
                    <h6 class="alert-heading">
                      <i class="fas fa-info-circle me-2"></i>Editing Your Own Account
                    </h6>
                    <p class="mb-0">You are editing your own account. You cannot change your own role for security reasons.</p>
                  </div>
                </div>
              </div>
              @endif
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('central.users.show', $user) }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to User
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>Update User
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
              <i class="fas fa-question-circle me-1"></i>User Editing Guidelines
            </h6>
          </div>
          <div class="card-body">
            <h6 class="text-primary">Password Changes</h6>
            <ul class="small">
              <li>Leave password blank to keep current password</li>
              <li>New password must be at least 8 characters</li>
              <li>User will need to log in with new password</li>
            </ul>

            <h6 class="text-primary mt-3">Role Changes</h6>
            <ul class="small">
              <li>Role changes take effect immediately</li>
              <li>User may need to log out and back in</li>
              <li>Cannot change your own role</li>
            </ul>

            <h6 class="text-primary mt-3">Safety Tips</h6>
            <ul class="small">
              <li>Verify email address changes carefully</li>
              <li>Document significant role changes</li>
              <li>Notify user of any access changes</li>
            </ul>
          </div>
        </div>

        <!-- Security Warning -->
        @if($user->hasRole('super-admin') && $user->id !== auth()->id())
        <div class="card card-outline card-danger mt-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-exclamation-triangle me-1"></i>Security Warning
            </h6>
          </div>
          <div class="card-body">
            <p class="small mb-0">
              <strong>Warning:</strong> You are editing a Super Admin account. Changes to this account could affect system security and operations.
            </p>
          </div>
        </div>
        @endif
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

    // Trigger on page load
    $('#role').trigger('change');
});
</script>
@endpush
@endsection
