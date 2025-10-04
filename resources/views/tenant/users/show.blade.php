@extends('tenant.layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user"></i> User Details
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.users.index') }}">Users</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
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
          <div class="d-flex align-items-center">
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                 class="rounded-circle me-3" width="60" height="60">
            <div>
              <h5 class="card-title mb-0">
                {{ $user->name }}
                @if($user->is_active)
                  <span class="badge bg-success ms-2">Active</span>
                @else
                  <span class="badge bg-danger ms-2">Inactive</span>
                @endif
              </h5>
              <small class="text-muted">{{ $user->email }}</small>
            </div>
          </div>
          <div class="btn-group" role="group">
            <a href="{{ route('tenant.users.edit', $user) }}" class="btn btn-warning">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            @if($user->id !== auth()->id())
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-cog me-1"></i>Actions
              </button>
              <ul class="dropdown-menu">
                <li>
                  <form action="{{ route('tenant.users.toggle-status', $user) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item" onclick="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?')">
                      <i class="fas fa-toggle-{{ $user->is_active ? 'off' : 'on' }} me-2"></i>
                      {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                    </button>
                  </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('tenant.users.destroy', $user) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                      <i class="fas fa-trash me-2"></i>Delete User
                    </button>
                  </form>
                </li>
              </ul>
            </div>
            @endif
            <a href="{{ route('tenant.users.index') }}" class="btn btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <!-- User Information -->
      <div class="col-md-6">
        <div class="card card-info card-outline mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-user me-2"></i>Personal Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Full Name:</strong>
              </div>
              <div class="col-sm-8">
                {{ $user->name }}
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Email:</strong>
              </div>
              <div class="col-sm-8">
                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
              </div>
            </div>
            @if($user->phone)
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Phone:</strong>
              </div>
              <div class="col-sm-8">
                <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
              </div>
            </div>
            @endif
            @if($user->address)
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Address:</strong>
              </div>
              <div class="col-sm-8">
                {{ $user->address }}
              </div>
            </div>
            @endif
            @if($user->position)
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Position:</strong>
              </div>
              <div class="col-sm-8">
                {{ $user->position }}
              </div>
            </div>
            @endif
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Account Status:</strong>
              </div>
              <div class="col-sm-8">
                @if($user->is_active)
                  <span class="badge bg-success">Active</span>
                @else
                  <span class="badge bg-danger">Inactive</span>
                @endif
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Created:</strong>
              </div>
              <div class="col-sm-8">
                {{ $user->created_at->format('M d, Y \a\t g:i A') }}
                <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Last Updated:</strong>
              </div>
              <div class="col-sm-8">
                {{ $user->updated_at->format('M d, Y \a\t g:i A') }}
                <small class="text-muted d-block">{{ $user->updated_at->diffForHumans() }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Access & Role Information -->
      <div class="col-md-6">
        <div class="card card-warning card-outline mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-key me-2"></i>Access & Permissions
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Property:</strong>
              </div>
              <div class="col-sm-8">
                @if($user->property)
                  <span class="badge bg-info">{{ $user->property->name }}</span>
                @else
                  <span class="text-muted">No Property Assigned</span>
                @endif
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Role:</strong>
              </div>
              <div class="col-sm-8">
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
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Permissions:</strong>
              </div>
              <div class="col-sm-8">
                @php
                  $roleDescriptions = [
                    'super-user' => 'Complete system access across all properties',
                    'super-manager' => 'Multi-property management capabilities',
                    'property-admin' => 'Full access to assigned property',
                    'manager' => 'Property operations and staff management',
                    'receptionist' => 'Guest services and booking management',
                    'housekeeping' => 'Room maintenance and cleaning schedules',
                    'accountant' => 'Financial records and reporting',
                    'support' => 'Customer support and basic operations',
                    'guest' => 'Limited guest portal access'
                  ];
                  $description = $roleDescriptions[$user->role] ?? 'Standard user access';
                @endphp
                <small class="text-muted">{{ $description }}</small>
              </div>
            </div>
            @if($user->email_verified_at)
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Email Verified:</strong>
              </div>
              <div class="col-sm-8">
                <span class="badge bg-success">Verified</span>
                <small class="text-muted d-block">{{ $user->email_verified_at->format('M d, Y') }}</small>
              </div>
            </div>
            @else
            <div class="row mb-3">
              <div class="col-sm-4">
                <strong>Email Verified:</strong>
              </div>
              <div class="col-sm-8">
                <span class="badge bg-warning">Pending</span>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Summary -->
    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-chart-line me-2"></i>Activity Summary
        </h5>
      </div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-md-3">
            <div class="card ">
              <div class="card-body">
                <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                <h4 class="mb-0">{{ $user->bookingsCount() ?? 0 }}</h4>
                <small class="text-muted">Bookings Created</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card ">
              <div class="card-body">
                <i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i>
                <h4 class="mb-0">{{ $user->activityLogs->count() ?? 0 }}</h4>
                <small class="text-muted">Activity Logs</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card ">
              <div class="card-body">
                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                <h4 class="mb-0">{{ $user->created_at->diffInDays(now()) }}</h4>
                <small class="text-muted">Days Since Joined</small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card ">
              <div class="card-body">
                <i class="fas fa-sign-in-alt fa-2x text-success mb-2"></i>
                <h4 class="mb-0">-</h4>
                <small class="text-muted">Last Login</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection