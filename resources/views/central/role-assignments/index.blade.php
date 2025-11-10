@extends('central.layouts.app')

@section('page-title', 'Role Assignments')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Role Assignments</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Role Assignments</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="card card-success card-outline">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">
              <i class="fas fa-user-tag me-2"></i>User Role Assignments
            </h5>
            <p class="text-muted small mb-0 mt-1">Manage which roles are assigned to users</p>
          </div>
          <div>
            <a href="{{ route('central.roles.index') }}" class="btn btn-outline-primary me-2">
              <i class="fas fa-users-cog me-1"></i>Manage Roles
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
              <i class="fas fa-users me-1"></i>Bulk Assignment
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ $users->count() }}</h4>
                <p class="mb-0">Total Users</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-users fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ $users->sum(function($user) { return $user->roles->count(); }) }}</h4>
                <p class="mb-0">Role Assignments</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-user-tag fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ $roles->count() }}</h4>
                <p class="mb-0">Available Roles</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-users-cog fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ $users->where(function($user) { return $user->roles->count() === 0; })->count() }}</h4>
                <p class="mb-0">No Roles</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-user-slash fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Users and Role Assignments -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-list me-2"></i>User Role Matrix
        </h5>
      </div>
      <div class="card-body">
        @if($users->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>User</th>
                <th>Property</th>
                <th>Current Roles</th>
                <th>Last Login</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    @if($user->profile_photo_path)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                         class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                    @else
                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px;">
                      <i class="fas fa-user text-white"></i>
                    </div>
                    @endif
                    <div>
                      <div class="fw-bold">{{ $user->name }}</div>
                      <small class="text-muted">{{ $user->email }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  @if($user->property)
                  <span class="badge bg-info">{{ $user->property->name }}</span>
                  @else
                  <span class="badge bg-secondary">No Property</span>
                  @endif
                </td>
                <td>
                  @if($user->roles->count() > 0)
                  <div class="d-flex flex-wrap gap-1">
                    @foreach($user->roles as $role)
                    <span class="badge bg-{{ in_array($role->name, ['super-user', 'super-manager', 'support']) ? 'danger' : 'primary' }}">
                      {{ $role->name }}
                    </span>
                    @endforeach
                  </div>
                  @else
                  <span class="text-muted">No roles assigned</span>
                  @endif
                </td>
                <td>
                  @if($user->last_login_at)
                  {{ $user->last_login_at->format('M d, Y') }}
                  <br><small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                  @else
                  <span class="text-muted">Never</span>
                  @endif
                </td>
                <td>
                  @if($user->is_active)
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-danger">Inactive</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('central.users.show', $user) }}" class="btn btn-outline-primary" title="View User">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('central.role-assignments.edit', $user) }}" class="btn btn-outline-success" title="Edit Roles">
                      <i class="fas fa-user-tag"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-4">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No users found</h5>
          <p class="text-muted">No users available for role assignment.</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Role Distribution -->
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-chart-pie me-1"></i>Role Distribution
            </h6>
          </div>
          <div class="card-body">
            @foreach($roles as $role)
            @php
            $userCount = $users->filter(function($user) use ($role) {
                return $user->roles->contains('id', $role->id);
            })->count();
            $percentage = $users->count() > 0 ? round(($userCount / $users->count()) * 100, 1) : 0;
            @endphp
            <div class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-bold">{{ $role->name }}</span>
                <span class="badge bg-primary">{{ $userCount }} users ({{ $percentage }}%)</span>
              </div>
              <div class="progress mt-1">
                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-info-circle me-1"></i>Assignment Guidelines
            </h6>
          </div>
          <div class="card-body">
            <div class="small">
              <h6 class="text-primary">Role Assignment Tips</h6>
              <ul class="mb-3">
                <li>Users can have multiple roles</li>
                <li>Permissions are cumulative across roles</li>
                <li>Super users can assign any role</li>
                <li>Property admins can only assign within their property</li>
              </ul>
              
              <h6 class="text-primary">Security Best Practices</h6>
              <ul class="mb-0">
                <li>Follow principle of least privilege</li>
                <li>Regularly review role assignments</li>
                <li>Remove unused roles promptly</li>
                <li>Document role assignment reasons</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Assignment Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('central.role-assignments.bulk-assign') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-users me-2"></i>Bulk Role Assignment
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="role" class="form-label required">Role</label>
                <select class="form-select" id="role" name="role" required>
                  <option value="">Select Role</option>
                  @foreach($roles as $role)
                  <option value="{{ $role->id }}">{{ $role->name }}</option>
                  @endforeach
                </select>
                <div class="form-text">The role to assign or remove</div>
              </div>

              <div class="mb-3">
                <label for="action" class="form-label required">Action</label>
                <select class="form-select" id="action" name="action" required>
                  <option value="">Select Action</option>
                  <option value="assign">Assign Role</option>
                  <option value="remove">Remove Role</option>
                </select>
                <div class="form-text">Whether to assign or remove the role</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label required">Select Users</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.5rem;">
                  @foreach($users as $user)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                    <label class="form-check-label" for="user_{{ $user->id }}">
                      <div class="d-flex align-items-center">
                        @if($user->profile_photo_path)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                             class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                        @else
                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                             style="width: 24px; height: 24px;">
                          <i class="fas fa-user text-white small"></i>
                        </div>
                        @endif
                        <div>
                          <div class="fw-bold">{{ $user->name }}</div>
                          <small class="text-muted">{{ $user->email }}</small>
                        </div>
                      </div>
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="form-text">Select the users for this operation</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-users me-1"></i>Apply Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Bulk select functionality
    $('#bulkAssignModal').on('shown.bs.modal', function() {
        // Add select all functionality
        if (!$('#select-all-users').length) {
            const selectAllHtml = `
                <div class="form-check border-bottom pb-2 mb-2">
                    <input class="form-check-input" type="checkbox" id="select-all-users">
                    <label class="form-check-label fw-bold" for="select-all-users">
                        Select All Users
                    </label>
                </div>
            `;
            $('.modal-body .col-md-6:last-child .form-label:last-of-type').parent().find('div[style*="max-height"]').prepend(selectAllHtml);
        }
        
        $('#select-all-users').change(function() {
            $('input[name="users[]"]').prop('checked', $(this).is(':checked'));
        });
    });
});
</script>
@endpush