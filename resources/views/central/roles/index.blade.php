@extends('central.layouts.app')

@section('page-title', 'Roles Management')

@section('content')

<div class="content">
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

    <!-- Page Header -->
    <div class="card card-primary card-outline mb-4 mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">
              <i class="fas fa-users-cog me-2"></i>System Roles
            </h5><br>
            <p class="text-muted small mb-0 mt-1">Manage user roles and their permissions</p>
          </div>
          @can('manage roles')
          <div>
            <a href="{{ route('central.role-assignments.index') }}" class="btn btn-outline-info me-2">
              <i class="fas fa-user-tag me-1"></i>Role Assignments
            </a>
            <a href="{{ route('central.roles.create') }}" class="btn btn-primary">
              <i class="fas fa-plus me-1"></i>Create Role
            </a>
          </div>
          @endcan
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
                <h4 class="mb-1">{{ $roles->count() }}</h4>
                <p class="mb-0">Total Roles</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-users-cog fa-2x"></i>
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
                <h4 class="mb-1">{{ $roles->where('name', '!=', 'super-user')->count() }}</h4>
                <p class="mb-0">Custom Roles</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-user-edit fa-2x"></i>
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
                <h4 class="mb-1">{{ $roles->sum('users_count') }}</h4>
                <p class="mb-0">Total Assignments</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-user-friends fa-2x"></i>
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
                <h4 class="mb-1">{{ $roles->sum(function($role) { return $role->permissions->count(); }) }}</h4>
                <p class="mb-0">Total Permissions</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-key fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Roles List -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-list me-2"></i>All Roles
        </h5>
      </div>
      <div class="card-body">
        @if($roles->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Role Name</th>
                <th>Display Name</th>
                <th>Users</th>
                <th>Permissions</th>
                <th>Type</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($roles as $role)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    @if(in_array($role->name, ['super-user', 'super-manager', 'support']))
                    <span class="badge bg-danger me-2">
                      <i class="fas fa-lock"></i>
                    </span>
                    @else
                    <span class="badge bg-success me-2">
                      <i class="fas fa-user-cog"></i>
                    </span>
                    @endif
                    <div>
                      <strong>{{ $role->name }}</strong>
                    </div>
                  </div>
                </td>
                <td>
                  {{ $role->display_name ?: 'Not set' }}
                  @if($role->description)
                  <br><small class="text-muted">{{ Str::limit($role->description, 50) }}</small>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary">{{ $role->users_count }} users</span>
                </td>
                <td>
                  <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                </td>
                <td>
                  @if(in_array($role->name, ['super-user', 'super-manager', 'support']))
                  <span class="badge bg-danger">System Role</span>
                  @else
                  <span class="badge bg-success">Custom Role</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('central.roles.show', $role) }}" class="btn btn-outline-primary" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                    @can('manage roles')
                    @if(!in_array($role->name, ['super-user', 'super-manager', 'support']))
                    <a href="{{ route('central.roles.edit', $role) }}" class="btn btn-outline-warning" title="Edit Role">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($role->users_count == 0)
                    <form action="{{ route('central.roles.destroy', $role) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this role?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Delete Role">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    @endif
                    @endif
                    @endcan
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-4">
          <i class="fas fa-users-cog fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No roles found</h5>
          <p class="text-muted">Get started by creating your first role.</p>
          @can('manage roles')
          <a href="{{ route('central.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create First Role
          </a>
          @endcan
        </div>
        @endif
      </div>
    </div>

    <!-- Quick Actions -->
    @can('manage roles')
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-bolt me-1"></i>Quick Actions
            </h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('central.permissions.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-key me-2"></i>Manage Permissions
              </a>
              <a href="{{ route('central.role-assignments.index') }}" class="btn btn-outline-success">
                <i class="fas fa-user-tag me-2"></i>Assign User Roles
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-outline card-warning">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-info-circle me-1"></i>Role Information
            </h6>
          </div>
          <div class="card-body">
            <div class="small">
              <p class="mb-2"><strong>System Roles:</strong> Cannot be modified or deleted</p>
              <p class="mb-2"><strong>Custom Roles:</strong> Can be edited and deleted</p>
              <p class="mb-0"><strong>Role Assignments:</strong> Users can have multiple roles</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endcan
  </div>
</div>
@endsection