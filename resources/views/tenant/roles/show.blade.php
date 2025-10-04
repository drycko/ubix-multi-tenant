@extends('tenant.layouts.app')

@section('page-title', 'Role Details: ' . $role->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Role Details</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.roles.index') }}">Roles</a></li>
          <li class="breadcrumb-item active" aria-current="page">Details</li>
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

    <!-- Role Header -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            @if(in_array($role->name, ['super-user', 'super-manager', 'support']))
            <span class="badge bg-danger me-3">
              <i class="fas fa-lock me-1"></i>System Role
            </span>
            @else
            <span class="badge bg-success me-3">
              <i class="fas fa-user-cog me-1"></i>Custom Role
            </span>
            @endif
            <div>
              <h5 class="mb-0">{{ $role->name }}</h5>
              @if($role->display_name)
              <p class="text-muted mb-0">{{ $role->display_name }}</p>
              @endif
            </div>
          </div>
          <div>
            <a href="{{ route('tenant.roles.index') }}" class="btn btn-outline-secondary me-2">
              <i class="fas fa-arrow-left me-1"></i>Back to Roles
            </a>
            @can('manage roles')
            @if(!in_array($role->name, ['super-user', 'super-manager', 'support']))
            <a href="{{ route('tenant.roles.edit', $role) }}" class="btn btn-warning">
              <i class="fas fa-edit me-1"></i>Edit Role
            </a>
            @endif
            @endcan
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <!-- Role Information -->
      <div class="col-md-8">
        <!-- Role Details -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle me-2"></i>Role Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="fw-bold text-muted">Role Name:</label>
                  <p class="mb-0">{{ $role->name }}</p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Display Name:</label>
                  <p class="mb-0">{{ $role->display_name ?: 'Not set' }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="fw-bold text-muted">Guard:</label>
                  <p class="mb-0"><span class="badge bg-info">{{ $role->guard_name }}</span></p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Created:</label>
                  <p class="mb-0">{{ $role->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
              </div>
            </div>
            @if($role->description)
            <div class="mb-3">
              <label class="fw-bold text-muted">Description:</label>
              <p class="mb-0">{{ $role->description }}</p>
            </div>
            @endif
          </div>
        </div>

        <!-- Permissions -->
        <div class="card mt-3">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-key me-2"></i>Permissions ({{ $role->permissions->count() }})
              </h5>
              <span class="badge bg-info">{{ $role->permissions->count() }} permissions assigned</span>
            </div>
          </div>
          <div class="card-body">
            @if($role->permissions->count() > 0)
            @foreach($groupedPermissions as $resource => $resourcePermissions)
            <div class="mb-4">
              <h6 class="text-primary border-bottom pb-2">
                <i class="fas fa-folder me-1"></i>{{ ucfirst($resource) }}
                <span class="badge bg-primary ms-2">{{ count($resourcePermissions) }}</span>
              </h6>
              <div class="row">
                @foreach($resourcePermissions as $permission)
                <div class="col-md-6 col-lg-4 mb-2">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <span>{{ $permission->name }}</span>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @endforeach
            @else
            <div class="text-center py-4">
              <i class="fas fa-key fa-3x text-muted mb-3"></i>
              <h6 class="text-muted">No permissions assigned</h6>
              <p class="text-muted">This role has no permissions assigned to it.</p>
              @can('manage roles')
              @if(!in_array($role->name, ['super-user', 'super-manager', 'support']))
              <a href="{{ route('tenant.roles.edit', $role) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Assign Permissions
              </a>
              @endif
              @endcan
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card card-primary">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-chart-bar me-1"></i>Quick Stats
            </h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <div class="border-end">
                  <h4 class="text-primary mb-1">{{ $role->users->count() }}</h4>
                  <small class="text-muted">Users</small>
                </div>
              </div>
              <div class="col-6">
                <h4 class="text-info mb-1">{{ $role->permissions->count() }}</h4>
                <small class="text-muted">Permissions</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Assigned Users -->
        <div class="card mt-3">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="card-title mb-0">
                <i class="fas fa-users me-1"></i>Assigned Users
              </h6>
              @can('assign roles')
              <a href="{{ route('tenant.role-assignments.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user-tag"></i>
              </a>
              @endcan
            </div>
          </div>
          <div class="card-body">
            @if($role->users->count() > 0)
            @foreach($role->users->take(10) as $user)
            <div class="d-flex align-items-center mb-2">
              @if($user->profile_photo_path)
              <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                   class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
              @else
              <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                   style="width: 32px; height: 32px;">
                <i class="fas fa-user text-white"></i>
              </div>
              @endif
              <div class="flex-grow-1">
                <div class="fw-bold">{{ $user->name }}</div>
                <small class="text-muted">{{ $user->email }}</small>
              </div>
            </div>
            @endforeach
            @if($role->users->count() > 10)
            <div class="text-center mt-3">
              <small class="text-muted">and {{ $role->users->count() - 10 }} more users...</small>
            </div>
            @endif
            @else
            <div class="text-center py-3">
              <i class="fas fa-users fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">No users assigned</p>
              @can('assign roles')
              <a href="{{ route('tenant.role-assignments.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="fas fa-plus me-1"></i>Assign Users
              </a>
              @endcan
            </div>
            @endif
          </div>
        </div>

        <!-- Actions -->
        @can('manage roles')
        <div class="card card-outline card-warning mt-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-cogs me-1"></i>Actions
            </h6>
          </div>
          <div class="card-body">
            @if(!in_array($role->name, ['super-user', 'super-manager', 'support']))
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.roles.edit', $role) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit Role
              </a>
              @if($role->users->count() == 0)
              <form action="{{ route('tenant.roles.destroy', $role) }}" method="POST" 
                    onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger w-100">
                  <i class="fas fa-trash me-1"></i>Delete Role
                </button>
              </form>
              @else
              <button class="btn btn-danger w-100" disabled title="Cannot delete role with assigned users">
                <i class="fas fa-trash me-1"></i>Delete Role
              </button>
              <small class="text-muted">Remove all users first to delete this role</small>
              @endif
            </div>
            @else
            <div class="text-center">
              <i class="fas fa-lock fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">System roles cannot be modified</p>
            </div>
            @endif
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>
@endsection
