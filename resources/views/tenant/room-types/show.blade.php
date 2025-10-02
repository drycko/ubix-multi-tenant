@extends('tenant.layouts.app')

@section('title', $roomType->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">
          {{ $roomType->name }}
          <span class="badge bg-{{ $roomType->is_active ? 'success' : 'danger' }} ms-2">
            {{ $roomType->is_active ? 'Active' : 'Inactive' }}
          </span>
        </h4>
        <small class="text-muted">{{ $roomType->code }}</small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-types.index') }}">Room Types</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $roomType->name }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
    <!-- Action Bar -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-0">
                  <i class="bi bi-info-circle"></i> Room Type Details
                </h5>
              </div>
              <div>
                <!-- Edit -->
                @can('manage room types')
                <a href="{{ route('tenant.room-types.edit', $roomType) }}" class="btn btn-warning me-2">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                @endcan
                
                <!-- Toggle Status -->
                @can('manage room types')
                <form action="{{ route('tenant.room-types.toggle-status', $roomType) }}" 
                      method="POST" class="d-inline me-2">
                  @csrf
                  <button type="submit" 
                          class="btn btn-outline-{{ $roomType->is_active ? 'danger' : 'success' }}" 
                          onclick="return confirm('Are you sure you want to {{ $roomType->is_active ? 'deactivate' : 'activate' }} this room type?')">
                    <i class="bi bi-{{ $roomType->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                    {{ $roomType->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
                @endcan
                
                <!-- Clone -->
                @can('manage room types')
                <form action="{{ route('tenant.room-types.clone', $roomType) }}" 
                      method="POST" class="d-inline me-2">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary"
                          onclick="return confirm('Clone this room type?')">
                    <i class="bi bi-files"></i> Clone
                  </button>
                </form>
                @endcan
                
                <!-- Back to List -->
                <a href="{{ route('tenant.room-types.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-list"></i> All Room Types
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Main Information -->
      <div class="col-lg-8">
        
        <!-- Basic Information -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-info-circle"></i> Basic Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Room Type Name</label>
                <p class="mb-0">{{ $roomType->name }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Room Type Code</label>
                <p class="mb-0">
                  <code>{{ $roomType->code }}</code>
                </p>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Description</label>
                <p class="mb-0">{{ $roomType->description ?? 'No description provided.' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Capacity Information -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-people"></i> Capacity Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Base Capacity</label>
                <p class="mb-0">
                  <span class="badge bg-info fs-6">{{ $roomType->base_capacity }} guests</span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Maximum Capacity</label>
                <p class="mb-0">
                  <span class="badge bg-warning fs-6">{{ $roomType->max_capacity }} guests</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Amenities -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-star"></i> Amenities
            </h5>
          </div>
          <div class="card-body">
            @if($amenities && $amenities->count() > 0)
              <div class="row">
                @foreach($amenities as $amenity)
                  <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                      <i class="{{ $amenity->icon }} me-2 text-success"></i>
                      <span>{{ $amenity->name }}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <p class="text-muted mb-0">No amenities configured for this room type.</p>
            @endif
          </div>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        
        <!-- Status & Statistics -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-graph-up"></i> Statistics
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-12 mb-3">
                <div class="border rounded p-3">
                  <h4 class="mb-1 text-primary">{{ $roomType->rooms_count ?? 0 }}</h4>
                  <small class="text-muted">Total Rooms</small>
                </div>
              </div>
            </div>
            
            <hr>
            
            <div class="row">
              <div class="col-12 mb-2">
                <strong>Status:</strong>
                <span class="badge bg-{{ $roomType->is_active ? 'success' : 'danger' }} ms-2">
                  {{ $roomType->is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <div class="col-12 mb-2">
                <strong>Created:</strong>
                <small class="text-muted">{{ $roomType->created_at->format('M d, Y') }}</small>
              </div>
              <div class="col-12 mb-2">
                <strong>Last Updated:</strong>
                <small class="text-muted">{{ $roomType->updated_at->format('M d, Y') }}</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-lightning"></i> Quick Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              @can('manage rooms')
              <a href="{{ route('tenant.rooms.create') }}?room_type={{ $roomType->id }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Add Room
              </a>
              @endcan
              
              @can('manage room types')
              <a href="{{ route('tenant.room-types.edit', $roomType) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil"></i> Edit Room Type
              </a>
              @endcan
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
@endsection