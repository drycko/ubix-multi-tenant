@extends('tenant.layouts.app')

@section('page-title', 'Permission Details: ' . $permission->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Show Permissions</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.permissions.index') }}">Permissions</a></li>
          <li class="breadcrumb-item active" aria-current="page">Show Permission</li>
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

    <!-- Permission Header -->
    <div class="card card-info card-outline">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <span class="badge bg-info me-3">
              <i class="fas fa-key me-1"></i>Permission
            </span>
            <div>
              <h5 class="mb-0">{{ $permission->name }}</h5>
              @if($permission->display_name)
              <p class="text-muted mb-0">{{ $permission->display_name }}</p>
              @endif
            </div>
          </div>
          <div>
            <a href="{{ route('tenant.permissions.index') }}" class="btn btn-outline-secondary me-2">
              <i class="fas fa-arrow-left me-1"></i>Back to Permissions
            </a>
            @can('manage permissions')
            <a href="{{ route('tenant.permissions.edit', $permission) }}" class="btn btn-info">
              <i class="fas fa-edit me-1"></i>Edit Permission
            </a>
            @endcan
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Permission Information -->
      <div class="col-md-8">
        <!-- Permission Details -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle me-2"></i>Permission Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="fw-bold text-muted">Permission Name:</label>
                  <p class="mb-0">{{ $permission->name }}</p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Display Name:</label>
                  <p class="mb-0">{{ $permission->display_name ?: 'Not set' }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="fw-bold text-muted">Guard:</label>
                  <p class="mb-0"><span class="badge bg-info">{{ $permission->guard_name }}</span></p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Created:</label>
                  <p class="mb-0">{{ $permission->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
              </div>
            </div>
            @if($permission->description)
            <div class="mb-3">
              <label class="fw-bold text-muted">Description:</label>
              <p class="mb-0">{{ $permission->description }}</p>
            </div>
            @endif
          </div>
        </div>

        <!-- Assigned Roles -->
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-users-cog me-2"></i>Assigned Roles ({{ $permission->roles->count() }})
              </h5>
              <span class="badge bg-info">{{ $permission->roles->count() }} roles assigned</span>
            </div>
          </div>
          <div class="card-body">
            @if($permission->roles->count() > 0)
            <div class="row">
              @foreach($permission->roles as $role)
              <div class="col-md-6 mb-3">
                <div class="card bg-light h-100">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="flex-grow-1">
                        <h6 class="card-title mb-1">
                          @if(in_array($role->name, ['super-user', 'super-manager', 'support']))
                          <span class="badge bg-danger me-2">
                            <i class="fas fa-lock"></i>
                          </span>
                          @else
                          <span class="badge bg-success me-2">
                            <i class="fas fa-user-cog"></i>
                          </span>
                          @endif
                          {{ $role->name }}
                        </h6>
                        @if($role->display_name)
                        <p class="card-text small text-muted mb-1">{{ $role->display_name }}</p>
                        @endif
                        @if($role->description)
                        <p class="card-text small">{{ Str::limit($role->description, 80) }}</p>
                        @endif
                      </div>
                      <div class="ms-2">
                        <a href="{{ route('tenant.roles.show', $role) }}" class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-eye"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            @else
            <div class="text-center py-4">
              <i class="fas fa-users-cog fa-3x text-muted mb-3"></i>
              <h6 class="text-muted">No roles assigned</h6>
              <p class="text-muted">This permission is not currently assigned to any roles.</p>
              @can('manage roles')
              <a href="{{ route('tenant.roles.index') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Assign to Roles
              </a>
              @endcan
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-chart-bar me-1"></i>Quick Stats
            </h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <div class="border-end">
                  <h4 class="text-info mb-1">{{ $permission->roles->count() }}</h4>
                  <small class="text-muted">Roles</small>
                </div>
              </div>
              <div class="col-6">
                <h4 class="text-success mb-1">{{ $permission->roles->sum(function($role) { return $role->users->count(); }) }}</h4>
                <small class="text-muted">Users</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Permission Analysis -->
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-analytics me-1"></i>Permission Analysis
            </h6>
          </div>
          <div class="card-body">
            @php
            $parts = explode(' ', $permission->name);
            $action = $parts[0] ?? 'unknown';
            $resource = implode(' ', array_slice($parts, 1)) ?: 'general';
            @endphp
            <div class="mb-3">
              <label class="fw-bold text-muted">Action:</label>
              <p class="mb-0">
                <span class="badge bg-primary">{{ ucfirst($action) }}</span>
              </p>
            </div>
            <div class="mb-3">
              <label class="fw-bold text-muted">Resource:</label>
              <p class="mb-0">
                <span class="badge bg-secondary">{{ ucfirst($resource) }}</span>
              </p>
            </div>
            <div class="mb-3">
              <label class="fw-bold text-muted">Usage:</label>
              <p class="mb-0">
                @if($permission->roles->count() > 0)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-warning">Unused</span>
                @endif
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        @can('manage permissions')
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-cogs me-1"></i>Actions
            </h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.permissions.edit', $permission) }}" class="btn btn-info">
                <i class="fas fa-edit me-1"></i>Edit Permission
              </a>
              @if($permission->roles->count() == 0)
              <form action="{{ route('tenant.permissions.destroy', $permission) }}" method="POST" 
                    onsubmit="return confirm('Are you sure you want to delete this permission? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger w-100">
                  <i class="fas fa-trash me-1"></i>Delete Permission
                </button>
              </form>
              @else
              <button class="btn btn-danger w-100" disabled title="Cannot delete permission assigned to roles">
                <i class="fas fa-trash me-1"></i>Delete Permission
              </button>
              <small class="text-muted">Remove from all roles first to delete this permission</small>
              @endif
            </div>
          </div>
        </div>
        @endcan

        <!-- Related Permissions -->
        @if($permission->roles->count() > 0)
        <div class="card card-outline card-secondary">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-link me-1"></i>Related Permissions
            </h6>
          </div>
          <div class="card-body">
            @php
            $relatedPermissions = collect();
            foreach($permission->roles as $role) {
                $relatedPermissions = $relatedPermissions->merge($role->permissions);
            }
            $relatedPermissions = $relatedPermissions->unique('id')->where('id', '!=', $permission->id)->take(5);
            @endphp
            
            @if($relatedPermissions->count() > 0)
            <div class="small">
              <p class="text-muted mb-2">Permissions often used together:</p>
              @foreach($relatedPermissions as $related)
              <div class="mb-1">
                <a href="{{ route('tenant.permissions.show', $related) }}" class="text-decoration-none">
                  <i class="fas fa-key me-1"></i>{{ $related->name }}
                </a>
              </div>
              @endforeach
            </div>
            @else
            <p class="text-muted small mb-0">No related permissions found</p>
            @endif
          </div>
        </div>
        @endif
      </div>
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
});
</script>
@endpush