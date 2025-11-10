@extends('central.layouts.app')

@section('title', 'Users Management')

@section('content')

<div class="content">
  <div class="container-fluid">

    <!-- Page Header -->
    <div class="card card-primary card-outline mb-4 mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">
              <i class="fas fa-users me-2"></i>System Users
            </h5><br>
            <p class="text-muted small mb-0 mt-1">Manage central system users and their roles</p>
          </div>
          @can('manage users')
          <div>
            <a href="{{ route('central.users.create') }}" class="btn btn-primary">
              <i class="fas fa-plus me-1"></i>Create User
            </a>
          </div>
          @endcan
        </div>
      </div>
    </div>

    
    
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ $users->total() }}</h4>
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
                <h4 class="mb-1">{{ \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'super-admin'); })->count() }}</h4>
                <p class="mb-0">Super Admins</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-user-shield fa-2x"></i>
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
                <h4 class="mb-1">{{ \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'super-manager'); })->count() }}</h4>
                <p class="mb-0">Super Managers</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-user-tie fa-2x"></i>
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
                <h4 class="mb-1">{{ \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'support'); })->count() }}</h4>
                <p class="mb-0">Support Staff</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-headset fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-filter me-1"></i>Filters
        </h6>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('central.users.index') }}">
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Name or email...">
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                  <option value="">All Roles</option>
                  @foreach($roles as $role)
                  <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                    {{ $role->display_name ?: $role->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="">All Status</option>
                  <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                  <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="{{ route('central.users.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-redo me-1"></i>Reset
                </a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Users List -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-list me-2"></i>All Users ({{ $users->total() }})
        </h5>
      </div>
      <div class="card-body">
        @if($users->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>User</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Joined</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center text-white" 
                         style="width: 36px; height: 36px; font-weight: bold;">
                      {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                      <strong>{{ $user->name }}</strong>
                      @if($user->id === auth()->id())
                      <span class="badge bg-info ms-1">You</span>
                      @endif
                    </div>
                  </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>
                  @foreach($user->roles as $role)
                  @if($role->name === 'super-admin')
                  <span class="badge bg-danger">
                    <i class="fas fa-user-shield me-1"></i>{{ $role->display_name ?: $role->name }}
                  </span>
                  @elseif($role->name === 'super-manager')
                  <span class="badge bg-warning">
                    <i class="fas fa-user-tie me-1"></i>{{ $role->display_name ?: $role->name }}
                  </span>
                  @else
                  <span class="badge bg-info">
                    <i class="fas fa-user me-1"></i>{{ $role->display_name ?: $role->name }}
                  </span>
                  @endif
                  @endforeach
                </td>
                <td>
                  <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                </td>
                <td>
                  @if($user->trashed())
                  <span class="badge bg-danger">Deleted</span>
                  @else
                  <span class="badge bg-success">Active</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('central.users.show', $user) }}" class="btn btn-outline-primary" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                    @can('manage users')
                    @if(!$user->trashed())
                    <a href="{{ route('central.users.edit', $user) }}" class="btn btn-outline-warning" title="Edit User">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($user->id !== auth()->id() && !$user->hasRole('super-admin'))
                    <form action="{{ route('central.users.destroy', $user) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Delete User">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    @endif
                    @else
                    <form action="{{ route('central.users.restore', $user->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-outline-success" title="Restore User">
                        <i class="fas fa-undo"></i>
                      </button>
                    </form>
                    @endif
                    @endcan
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $users->links() }}
        </div>
        @else
        <div class="text-center py-4">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No users found</h5>
          <p class="text-muted">
            @if(request()->hasAny(['search', 'role', 'status']))
            Try adjusting your filters to find what you're looking for.
            @else
            Get started by creating your first user.
            @endif
          </p>
          @can('manage users')
          <a href="{{ route('central.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create First User
          </a>
          @endcan
        </div>
        @endif
      </div>
    </div>

    <!-- Quick Actions -->
    @can('manage users')
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
              <a href="{{ route('central.roles.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-users-cog me-2"></i>Manage Roles
              </a>
              <a href="{{ route('central.permissions.index') }}" class="btn btn-outline-success">
                <i class="fas fa-key me-2"></i>Manage Permissions
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-outline card-warning">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-info-circle me-1"></i>User Information
            </h6>
          </div>
          <div class="card-body">
            <div class="small">
              <p class="mb-2"><strong>Super Admin:</strong> Full system access with all permissions</p>
              <p class="mb-2"><strong>Super Manager:</strong> Management access with most permissions</p>
              <p class="mb-0"><strong>Support:</strong> Limited access for customer support tasks</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endcan
  </div>
</div>
@endsection
