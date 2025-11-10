@extends('central.layouts.app')

@section('title', 'User Details: ' . $user->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user"></i> <small class="text-muted">User Details</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.users.index') }}">Users</a></li>
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

    <!-- User Header -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center text-white" 
                 style="width: 64px; height: 64px; font-size: 24px; font-weight: bold;">
              {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
              <h5 class="mb-0">
                {{ $user->name }}
                @if($user->id === auth()->id())
                <span class="badge bg-info ms-2">You</span>
                @endif
              </h5>
              <p class="text-muted mb-0">{{ $user->email }}</p>
              <div class="mt-1">
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
              </div>
            </div>
          </div>
          <div>
            <a href="{{ route('central.users.index') }}" class="btn btn-outline-secondary me-2">
              <i class="fas fa-arrow-left me-1"></i>Back to Users
            </a>
            @can('manage users')
            <a href="{{ route('central.users.edit', $user) }}" class="btn btn-warning">
              <i class="fas fa-edit me-1"></i>Edit User
            </a>
            @endcan
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <!-- User Information -->
      <div class="col-md-8">
        <!-- Basic Details -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle me-2"></i>User Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="fw-bold text-muted">Full Name:</label>
                  <p class="mb-0">{{ $user->name }}</p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Email Address:</label>
                  <p class="mb-0">{{ $user->email }}</p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Email Status:</label>
                  <p class="mb-0">
                    @if($user->email_verified_at)
                    <span class="badge bg-success">
                      <i class="fas fa-check-circle me-1"></i>Verified on {{ $user->email_verified_at->format('M d, Y') }}
                    </span>
                    @else
                    <span class="badge bg-warning">
                      <i class="fas fa-clock me-1"></i>Not Verified
                    </span>
                    @endif
                  </p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="fw-bold text-muted">User ID:</label>
                  <p class="mb-0"><span class="badge bg-secondary">{{ $user->id }}</span></p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Account Created:</label>
                  <p class="mb-0">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
                <div class="mb-3">
                  <label class="fw-bold text-muted">Last Updated:</label>
                  <p class="mb-0">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Roles & Permissions -->
        <div class="card mt-3">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-user-tag me-2"></i>Roles & Permissions
              </h5>
              <span class="badge bg-info">{{ $user->roles->count() }} role(s)</span>
            </div>
          </div>
          <div class="card-body">
            @if($user->roles->count() > 0)
            @foreach($user->roles as $role)
            <div class="card card-outline card-secondary mb-3">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">
                    @if($role->name === 'super-admin')
                    <span class="badge bg-danger me-2">
                      <i class="fas fa-user-shield"></i>
                    </span>
                    @elseif($role->name === 'super-manager')
                    <span class="badge bg-warning me-2">
                      <i class="fas fa-user-tie"></i>
                    </span>
                    @else
                    <span class="badge bg-info me-2">
                      <i class="fas fa-user"></i>
                    </span>
                    @endif
                    {{ $role->display_name ?: $role->name }}
                  </h6>
                  <span class="badge bg-primary">{{ $role->permissions->count() }} permissions</span>
                </div>
              </div>
              @if($role->description)
              <div class="card-body py-2">
                <p class="mb-0 small text-muted">{{ $role->description }}</p>
              </div>
              @endif
              @if($role->permissions->count() > 0)
              <div class="card-body">
                <h6 class="text-muted mb-2">Permissions:</h6>
                <div class="row">
                  @foreach($role->permissions->take(12) as $permission)
                  <div class="col-md-6 col-lg-4 mb-2">
                    <div class="d-flex align-items-center">
                      <i class="fas fa-check-circle text-success me-2 small"></i>
                      <small>{{ $permission->name }}</small>
                    </div>
                  </div>
                  @endforeach
                </div>
                @if($role->permissions->count() > 12)
                <div class="text-center mt-2">
                  <small class="text-muted">and {{ $role->permissions->count() - 12 }} more permissions...</small>
                </div>
                @endif
              </div>
              @endif
            </div>
            @endforeach
            @else
            <div class="text-center py-4">
              <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
              <h6 class="text-muted">No roles assigned</h6>
              <p class="text-muted">This user has no roles assigned.</p>
            </div>
            @endif
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-history me-2"></i>Recent Activity
            </h5>
          </div>
          <div class="card-body">
            @if($user->adminActivities && $user->adminActivities->count() > 0)
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Activity</th>
                    <th>Description</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($user->adminActivities as $activity)
                  <tr>
                    <td>
                      @if($activity->activity_type === 'create')
                      <span class="badge bg-success">Create</span>
                      @elseif($activity->activity_type === 'update')
                      <span class="badge bg-warning">Update</span>
                      @elseif($activity->activity_type === 'delete' || $activity->activity_type === 'soft_delete')
                      <span class="badge bg-danger">Delete</span>
                      @else
                      <span class="badge bg-info">{{ ucfirst($activity->activity_type) }}</span>
                      @endif
                    </td>
                    <td>{{ Str::limit($activity->description, 60) }}</td>
                    <td>
                      <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center py-4">
              <i class="fas fa-history fa-3x text-muted mb-3"></i>
              <h6 class="text-muted">No recent activity</h6>
              <p class="text-muted">This user hasn't performed any actions yet.</p>
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
                  <h4 class="text-primary mb-1">{{ $user->roles->count() }}</h4>
                  <small class="text-muted">Roles</small>
                </div>
              </div>
              <div class="col-6">
                <h4 class="text-info mb-1">{{ $user->roles->sum(function($role) { return $role->permissions->count(); }) }}</h4>
                <small class="text-muted">Permissions</small>
              </div>
            </div>
            <hr>
            <div class="row text-center">
              <div class="col-12">
                <h5 class="text-success mb-1">{{ $user->adminActivities ? $user->adminActivities->count() : 0 }}</h5>
                <small class="text-muted">Recent Activities</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Status -->
        <div class="card mt-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-shield-alt me-1"></i>Account Status
            </h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="fw-bold">Status:</label>
              <p class="mb-0">
                @if($user->trashed())
                <span class="badge bg-danger">
                  <i class="fas fa-ban me-1"></i>Deleted
                </span>
                @else
                <span class="badge bg-success">
                  <i class="fas fa-check-circle me-1"></i>Active
                </span>
                @endif
              </p>
            </div>
            <div class="mb-3">
              <label class="fw-bold">Account Type:</label>
              <p class="mb-0">
                @if($user->hasRole('super-admin'))
                <span class="badge bg-danger">System Administrator</span>
                @elseif($user->hasRole('super-manager'))
                <span class="badge bg-warning">System Manager</span>
                @else
                <span class="badge bg-info">Support Staff</span>
                @endif
              </p>
            </div>
            <div class="mb-0">
              <label class="fw-bold">Member Since:</label>
              <p class="mb-0">{{ $user->created_at->diffForHumans() }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        @can('manage users')
        <div class="card card-outline card-warning mt-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-cogs me-1"></i>Actions
            </h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('central.users.edit', $user) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit User
              </a>
              @if($user->id !== auth()->id() && !$user->hasRole('super-admin'))
              @if($user->trashed())
              <form action="{{ route('central.users.restore', $user->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-undo me-1"></i>Restore User
                </button>
              </form>
              @else
              <form action="{{ route('central.users.destroy', $user) }}" method="POST" 
                    onsubmit="return confirm('Are you sure you want to delete this user? This action can be reversed later.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger w-100">
                  <i class="fas fa-trash me-1"></i>Delete User
                </button>
              </form>
              @endif
              @else
              <button class="btn btn-danger w-100" disabled title="Cannot delete this user">
                <i class="fas fa-lock me-1"></i>Protected Account
              </button>
              @endif
            </div>
          </div>
        </div>
        @endcan

        <!-- Security Info -->
        @if($user->hasRole('super-admin'))
        <div class="card card-outline card-danger mt-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-exclamation-triangle me-1"></i>Security Notice
            </h6>
          </div>
          <div class="card-body">
            <p class="small mb-0">
              <strong>Protected Account:</strong> This is a Super Admin account with full system access. Exercise caution when making changes.
            </p>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
