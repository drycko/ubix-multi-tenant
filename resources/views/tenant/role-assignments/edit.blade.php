@extends('tenant.layouts.app')

@section('page-title', 'Edit Roles: ' . $user->name)

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Edit User Roles</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.role-assignments.index') }}">Role Assignments</a></li>
          <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-md-8">
        <!-- User Role Assignment Form -->
        <div class="card card-success card-outline">
          <div class="card-header">
            <div class="d-flex align-items-center">
              @if($user->profile_photo_path)
              <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                   class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
              @else
              <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                   style="width: 50px; height: 50px;">
                <i class="fas fa-user text-white fa-lg"></i>
              </div>
              @endif
              <div>
                <h5 class="card-title mb-0">{{ $user->name }}</h5>
                <p class="text-muted mb-0">{{ $user->email }}</p>
              </div>
            </div>
          </div>
          <form action="{{ route('tenant.role-assignments.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
              <div class="mb-4">
                <h6 class="text-primary mb-3">
                  <i class="fas fa-user-tag me-1"></i>Role Assignment
                </h6>
                <p class="text-muted">Select the roles to assign to this user. Users can have multiple roles, and permissions are cumulative.</p>
              </div>

              <!-- Current Roles Display -->
              <div class="mb-4">
                <label class="fw-bold text-muted">Current Roles:</label>
                <div class="mt-2">
                  @if($user->roles->count() > 0)
                  @foreach($user->roles as $role)
                  <span class="badge bg-{{ in_array($role->name, ['super-user', 'super-manager', 'support']) ? 'danger' : 'primary' }} me-1">
                    {{ $role->name }}
                  </span>
                  @endforeach
                  @else
                  <span class="text-muted">No roles currently assigned</span>
                  @endif
                </div>
              </div>

              <!-- Role Selection -->
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <label class="fw-bold">Available Roles</label>
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-success" id="select-all-roles">
                      <i class="fas fa-check-double me-1"></i>Select All
                    </button>
                    <button type="button" class="btn btn-outline-warning" id="clear-all-roles">
                      <i class="fas fa-times me-1"></i>Clear All
                    </button>
                  </div>
                </div>

                <div class="row">
                  @foreach($roles as $role)
                  <div class="col-md-6 mb-3">
                    <div class="card {{ in_array($role->id, $userRoles) ? 'border-primary' : '' }}">
                      <div class="card-body">
                        <div class="form-check">
                          <input class="form-check-input role-checkbox" 
                                 type="checkbox" 
                                 name="roles[]" 
                                 value="{{ $role->id }}" 
                                 id="role_{{ $role->id }}"
                                 {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                          <label class="form-check-label w-100" for="role_{{ $role->id }}">
                            <div class="d-flex justify-content-between align-items-start">
                              <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                  @if(in_array($role->name, ['super-user', 'super-manager', 'support']))
                                  <span class="badge bg-danger me-2">
                                    <i class="fas fa-lock"></i>
                                  </span>
                                  @else
                                  <span class="badge bg-success me-2">
                                    <i class="fas fa-user-cog"></i>
                                  </span>
                                  @endif
                                  <strong>{{ $role->name }}</strong>
                                </div>
                                @if($role->display_name)
                                <div class="text-muted small mb-1">{{ $role->display_name }}</div>
                                @endif
                                @if($role->description)
                                <div class="text-muted small">{{ Str::limit($role->description, 100) }}</div>
                                @endif
                              </div>
                              <div class="ms-2">
                                <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                              </div>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                @error('roles')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
              </div>

              <!-- Role Summary -->
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="text-info mb-3">
                    <i class="fas fa-chart-pie me-1"></i>Assignment Summary
                  </h6>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="text-center">
                        <h5 class="mb-1 text-primary" id="selected-roles-count">{{ count($userRoles) }}</h5>
                        <small class="text-muted">Selected Roles</small>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-center">
                        <h5 class="mb-1 text-secondary">{{ $roles->count() }}</h5>
                        <small class="text-muted">Total Available</small>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="text-center">
                        <h5 class="mb-1 text-info" id="total-permissions">
                          {{ $user->roles->sum(function($role) { return $role->permissions->count(); }) }}
                        </h5>
                        <small class="text-muted">Total Permissions</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.role-assignments.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Assignments
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2" id="reset-roles">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i>Update Roles
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <!-- User Information -->
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-user me-1"></i>User Information
            </h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <strong>Name:</strong> {{ $user->name }}
            </div>
            <div class="mb-3">
              <strong>Email:</strong> {{ $user->email }}
            </div>
            @if($user->property)
            <div class="mb-3">
              <strong>Property:</strong> 
              <span class="badge bg-info">{{ $user->property->name }}</span>
            </div>
            @endif
            <div class="mb-3">
              <strong>Status:</strong> 
              @if($user->is_active)
              <span class="badge bg-success">Active</span>
              @else
              <span class="badge bg-danger">Inactive</span>
              @endif
            </div>
            <div class="mb-3">
              <strong>Created:</strong> {{ $user->created_at->format('M d, Y') }}
            </div>
            @if($user->last_login_at)
            <div class="mb-0">
              <strong>Last Login:</strong> {{ $user->last_login_at->format('M d, Y g:i A') }}
            </div>
            @endif
          </div>
        </div>

        <!-- Current Permissions -->
        <div class="card card-outline card-success">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-key me-1"></i>Current Permissions
            </h6>
          </div>
          <div class="card-body">
            @php
            $allPermissions = collect();
            foreach($user->roles as $role) {
                $allPermissions = $allPermissions->merge($role->permissions);
            }
            $allPermissions = $allPermissions->unique('id');
            @endphp
            
            @if($allPermissions->count() > 0)
            <div style="max-height: 200px; overflow-y: auto;">
              @foreach($allPermissions->groupBy(function($permission) {
                  $parts = explode(' ', $permission->name);
                  return implode(' ', array_slice($parts, 1)) ?: 'general';
              }) as $resource => $permissions)
              <div class="mb-2">
                <strong class="text-primary">{{ ucfirst($resource) }}:</strong>
                <div class="ms-2">
                  @foreach($permissions as $permission)
                  <small class="d-block text-muted">• {{ $permission->name }}</small>
                  @endforeach
                </div>
              </div>
              @endforeach
            </div>
            @else
            <p class="text-muted mb-0">No permissions assigned</p>
            @endif
          </div>
        </div>

        <!-- Role Assignment Guidelines -->
        <div class="card card-outline card-warning">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-exclamation-triangle me-1"></i>Assignment Guidelines
            </h6>
          </div>
          <div class="card-body">
            <div class="small">
              <h6 class="text-primary">Role Assignment</h6>
              <ul class="mb-3">
                <li>Users can have multiple roles</li>
                <li>Permissions are cumulative</li>
                <li>Changes take effect immediately</li>
              </ul>
              
              <h6 class="text-primary">Security Tips</h6>
              <ul class="mb-0">
                <li>Follow principle of least privilege</li>
                <li>Avoid over-privileging users</li>
                <li>Review assignments regularly</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-bolt me-1"></i>Quick Actions
            </h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.users.show', $user) }}" class="btn btn-outline-primary">
                <i class="fas fa-user me-2"></i>View User Profile
              </a>
              <a href="{{ route('tenant.users.edit', $user) }}" class="btn btn-outline-warning">
                <i class="fas fa-edit me-2"></i>Edit User Details
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Store original roles for reset
const originalRoles = @json($userRoles);

$(document).ready(function() {
    updateRoleSummary();
    
    // Update summary when roles change
    $('.role-checkbox').change(function() {
        updateRoleSummary();
    });

    // Event handlers for buttons
    $('#select-all-roles').click(function() {
        $('.role-checkbox').prop('checked', true);
        updateRoleSummary();
    });

    $('#clear-all-roles').click(function() {
        $('.role-checkbox').prop('checked', false);
        updateRoleSummary();
    });

    $('#reset-roles').click(function() {
        // Clear all first
        $('.role-checkbox').prop('checked', false);
        
        // Check original roles
        originalRoles.forEach(function(roleId) {
            $(`#role_${roleId}`).prop('checked', true);
        });
        
        updateRoleSummary();
    });
});

function updateRoleSummary() {
    const selectedCount = $('.role-checkbox:checked').length;
    let totalPermissions = 0;
    
    $('.role-checkbox:checked').each(function() {
        const roleCard = $(this).closest('.card');
        const permissionBadge = roleCard.find('.badge.bg-info');
        const count = parseInt(permissionBadge.text().split(' ')[0]) || 0;
        totalPermissions += count;
    });
    
    $('#selected-roles-count').text(selectedCount);
    $('#total-permissions').text(totalPermissions);
}
</script>
@endpush