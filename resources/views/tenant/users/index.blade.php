@extends('tenant.layouts.app')

@section('title', 'User Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-users"></i> <small class="text-muted">User Management</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Users</li>
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
    
    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
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

    <!-- Header Card with Actions -->
    <div class="card card-primary card-outline mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">
              <i class="fas fa-users me-2"></i>Users
            </h5><br>
            <small class="text-muted">Manage system users and their access</small>
          </div>
          <div class="btn-group" role="group">
            <a href="{{ route('tenant.users.create') }}" class="btn btn-success">
              <i class="fas fa-plus me-1"></i>Add User
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="card card-secondary card-outline mb-4">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-filter me-2"></i>Filters
        </h6>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('tenant.users.index') }}">
          <div class="row g-3">
            <div class="col-md-3">
              <label for="search" class="form-label">Search</label>
              <input type="text" class="form-control" id="search" name="search" 
                     value="{{ request('search') }}" placeholder="Name, email, phone...">
            </div>
            @if(is_super_user() && $properties->isNotEmpty())
            <div class="col-md-3">
              <label for="property_id" class="form-label">Property</label>
              <select class="form-select" id="property_id" name="property_id">
                <option value="">All Properties</option>
                @foreach($properties as $property)
                <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                  {{ $property->name }}
                </option>
                @endforeach
              </select>
            </div>
            @endif
            <div class="col-md-2">
              <label for="role" class="form-label">Role</label>
              <select class="form-select" id="role" name="role">
                <option value="">All Roles</option>
                @foreach(\App\Models\Tenant\User::SUPPORTED_ROLES as $role)
                <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                  {{ ucfirst(str_replace('-', ' ', $role)) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" id="status" name="status">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-search me-1"></i>Filter
                </button>
              </div>
            </div>
          </div>
          @if(request()->hasAny(['search', 'property_id', 'role', 'status']))
          <div class="row mt-2">
            <div class="col-12">
              <a href="{{ route('tenant.users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times me-1"></i>Clear Filters
              </a>
            </div>
          </div>
          @endif
        </form>
      </div>
    </div>

    <!-- Users List -->
    <div class="card card-info card-outline">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-list me-2"></i>Users List
          <span class="badge bg-info ms-2">{{ $users->total() }} total</span>
        </h6>
      </div>
      <div class="card-body p-0">
        @if($users->count() > 0)
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
              <tr>
                <th width="60">#</th>
                <th>User</th>
                <th>Property</th>
                <th>Role</th>
                <th>Position</th>
                <th>Contact</th>
                <th width="100">Status</th>
                <th width="160">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                         class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                  </div>
                </td>
                <td>
                  <div>
                    <strong class="text-primary">{{ $user->name }}</strong>
                    <div class="small text-muted">{{ $user->email }}</div>
                  </div>
                </td>
                <td>
                  @if($user->property)
                    <span class="badge bg-info">{{ $user->property->name }}</span>
                  @else
                    <span class="text-muted">No Property</span>
                  @endif
                </td>
                <td>
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
                </td>
                <td>
                  <span class="text-muted">{{ $user->position ?? 'Not specified' }}</span>
                </td>
                <td>
                  @if($user->phone)
                    <div class="small">
                      <i class="fas fa-phone me-1"></i>{{ $user->phone }}
                    </div>
                  @endif
                  <div class="small text-muted">
                    <i class="fas fa-clock me-1"></i>{{ $user->created_at->format('M d, Y') }}
                  </div>
                </td>
                <td>
                  @if($user->is_active)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-danger">Inactive</span>
                  @endif
                </td>
                <td>
                  @can('manage users')
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('tenant.users.show', $user) }}" class="btn btn-outline-info" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('tenant.users.edit', $user) }}" class="btn btn-outline-warning" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('tenant.users.toggle-status', $user) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'secondary' : 'success' }}" 
                              title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                              onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?')">
                        <i class="fas fa-toggle-{{ $user->is_active ? 'off' : 'on' }}"></i>
                      </button>
                    </form>
                    <form action="{{ route('tenant.users.destroy', $user) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Delete"
                              onclick="return confirm('Are you sure you want to delete this user?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    @endif
                  </div>
                  @endcan
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No Users Found</h5>
          <p class="text-muted">No users match your current filters.</p>
          <a href="{{ route('tenant.users.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Add First User
          </a>
        </div>
        @endif
      </div>
      {{-- Pagination links --}}
      {{-- Beautiful pagination --}}
      @if($users->hasPages())
      <div class="container-fluid py-3">
        <div class="row align-items-center">
            <div class="col-md-12 float-end">
                {{ $users->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
      </div>
      @endif
    </div>
    
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection