@extends('tenant.layouts.app')

@section('title', 'Guest Clubs')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-crown"></i>
          Guest Clubs
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Guest Clubs</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
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

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Control Panel -->
    <div class="row mb-3">
      <div class="col-md-6">
        <form method="GET" action="{{ route('tenant.guest-clubs.index') }}" class="d-flex">
          <input type="text" 
                 name="search" 
                 class="form-control me-2" 
                 placeholder="Search clubs..." 
                 value="{{ request('search') }}">
          <button type="submit" class="btn btn-outline-primary">
            <i class="fas fa-search"></i>
          </button>
          @if(request('search') || request('status') || request('tier'))
          <a href="{{ route('tenant.guest-clubs.index') }}" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-times"></i>
          </a>
          @endif
        </form>
      </div>
      <div class="col-md-6 text-end">
        <a href="{{ route('tenant.guest-clubs.create') }}" class="btn btn-primary">
          <i class="fas fa-plus"></i> Create Club
        </a>
      </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.guest-clubs.index') }}" class="row g-3">
              <input type="hidden" name="search" value="{{ request('search') }}">
              
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="">All Statuses</option>
                  <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
              
              <div class="col-md-4">
                <label class="form-label">Tier Level</label>
                <select name="tier" class="form-select">
                  <option value="">All Tiers</option>
                  @foreach($tiers as $tier)
                  <option value="{{ $tier }}" {{ request('tier') === $tier ? 'selected' : '' }}>
                    {{ ucfirst($tier) }}
                  </option>
                  @endforeach
                </select>
              </div>
              
              <div class="col-md-4">
                <label class="form-label">Sort By</label>
                <div class="input-group">
                  <select name="sort_by" class="form-select">
                    <option value="tier_priority" {{ request('sort_by') === 'tier_priority' ? 'selected' : '' }}>Priority</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created</option>
                    <option value="members_count" {{ request('sort_by') === 'members_count' ? 'selected' : '' }}>Members</option>
                  </select>
                  <select name="sort_direction" class="form-select">
                    <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
                  </select>
                  <button type="submit" class="btn btn-outline-primary">Apply</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Guest Clubs List -->
    <div class="row">
      <div class="col-12">
        @if($guestClubs->count() > 0)
        <div class="row">
          @foreach($guestClubs as $club)
          <div class="col-md-4 mb-4">
            <div class="card h-100 {{ !$club->is_active ? 'opacity-75' : '' }}">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  @if($club->icon)
                  <i class="{{ $club->icon }} me-2" style="color: {{ $club->badge_color }}"></i>
                  @else
                  <div class="club-badge me-2" style="background-color: {{ $club->badge_color }}">
                    {{ strtoupper(substr($club->name, 0, 2)) }}
                  </div>
                  @endif
                  <div>
                    <h6 class="mb-0">{{ $club->name }}</h6>
                    @if($club->tier_level)
                    <small class="text-muted">{{ ucfirst($club->tier_level) }} Tier</small>
                    @endif
                  </div>
                </div>
                
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                          data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="{{ route('tenant.guest-clubs.show', $club) }}">
                        <i class="fas fa-eye"></i> View Details
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('tenant.guest-clubs.edit', $club) }}">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    </li>
                    <li>
                      <form method="POST" action="{{ route('tenant.guest-clubs.toggle-status', $club) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="dropdown-item">
                          <i class="fas fa-toggle-{{ $club->is_active ? 'off' : 'on' }}"></i> 
                          {{ $club->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                      </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form method="POST" action="{{ route('tenant.guest-clubs.destroy', $club) }}" 
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this guest club?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      </form>
                    </li>
                  </ul>
                </div>
              </div>
              
              <div class="card-body">
                @if($club->description)
                <p class="card-text text-muted small">{{ Str::limit($club->description, 100) }}</p>
                @endif
                
                <!-- Benefits Preview -->
                @if($club->benefits && count($club->benefits) > 0)
                <div class="mb-3">
                  <h6 class="text-muted mb-2">Benefits:</h6>
                  <div class="d-flex flex-wrap gap-1">
                    @foreach(collect($club->benefits)->take(3) as $key => $value)
                    @if($benefit = $club->formatBenefit($key, $value))
                    <span class="badge bg-light text-dark small">{{ Str::limit($benefit, 20) }}</span>
                    @endif
                    @endforeach
                    @if(count($club->benefits) > 3)
                    <span class="badge bg-secondary small">+{{ count($club->benefits) - 3 }} more</span>
                    @endif
                  </div>
                </div>
                @endif
                
                <!-- Stats -->
                <div class="row text-center mt-3">
                  <div class="col-6">
                    <div class="border-end">
                      <h5 class="mb-0 text-primary">{{ $club->members_count }}</h5>
                      <small class="text-muted">Total Members</small>
                    </div>
                  </div>
                  <div class="col-6">
                    <h5 class="mb-0 text-success">{{ $club->active_members_count }}</h5>
                    <small class="text-muted">Active Members</small>
                  </div>
                </div>
              </div>
              
              <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="badge bg-{{ $club->is_active ? 'success' : 'secondary' }}">
                      {{ $club->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($club->tier_priority > 0)
                    <span class="badge bg-warning text-dark">Priority: {{ $club->tier_priority }}</span>
                    @endif
                  </div>
                  <div>
                    <a href="{{ route('tenant.guest-clubs.show', $club) }}" class="btn btn-sm btn-outline-primary">
                      View <i class="fas fa-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="row">
          <div class="col-12">
            {{ $guestClubs->links() }}
          </div>
        </div>
        @else
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="fas fa-crown fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Guest Clubs Found</h5>
            <p class="text-muted">Create your first guest club to offer exclusive benefits to your valued guests.</p>
            <a href="{{ route('tenant.guest-clubs.create') }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> Create Guest Club
            </a>
          </div>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>
<!--end::App Content-->

@endsection

@push('styles')
<style>
.club-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 12px;
}
</style>
@endpush