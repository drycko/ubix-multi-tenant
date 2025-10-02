@extends('tenant.layouts.app')

@section('title', $roomAmenity->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">
          <i class="{{ $roomAmenity->icon }} me-2"></i>
          {{ $roomAmenity->name }}
        </h4>
        <small class="text-muted">
          <code>{{ $roomAmenity->slug }}</code>
        </small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-amenities.index') }}">Room Amenities</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $roomAmenity->name }}</li>
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
                  <i class="bi bi-star"></i> Amenity Details
                </h5>
              </div>
              <div>
                <!-- Edit -->
                @can('manage room amenities')
                <a href="{{ route('tenant.room-amenities.edit', $roomAmenity) }}" class="btn btn-warning me-2">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                @endcan
                
                <!-- Clone -->
                @can('manage room amenities')
                <form action="{{ route('tenant.room-amenities.clone', $roomAmenity) }}" 
                      method="POST" class="d-inline me-2">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary"
                          onclick="return confirm('Clone this room amenity?')">
                    <i class="bi bi-files"></i> Clone
                  </button>
                </form>
                @endcan
                
                <!-- Back to List -->
                <a href="{{ route('tenant.room-amenities.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-list"></i> All Amenities
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
                <label class="form-label fw-bold">Amenity Name</label>
                <p class="mb-0">{{ $roomAmenity->name }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Slug</label>
                <p class="mb-0">
                  <code class="bg-light px-2 py-1 rounded">{{ $roomAmenity->slug }}</code>
                </p>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Description</label>
                <p class="mb-0">{{ $roomAmenity->description ?? 'No description provided.' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Icon Information -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-palette"></i> Icon Information
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Icon Class</label>
                <p class="mb-0">
                  <code class="bg-light px-2 py-1 rounded">{{ $roomAmenity->icon }}</code>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Icon Preview</label>
                <div class="border rounded p-3 text-center">
                  <i class="{{ $roomAmenity->icon }} text-success" style="font-size: 3rem;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Usage Statistics -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-graph-up"></i> Usage Statistics
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Room Types Using This Amenity</label>
                <p class="mb-0">
                  <span class="badge bg-info fs-6">{{ $usageStats['room_types_count'] ?? 0 }} room types</span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Status</label>
                <p class="mb-0">
                  @if($usageStats['room_types_count'] > 0)
                    <span class="badge bg-success">Active - In Use</span>
                  @else
                    <span class="badge bg-warning">Not Currently Used</span>
                  @endif
                </p>
              </div>
            </div>
            
            @if($usageStats['room_types_count'] > 0)
              <div class="mt-3">
                <small class="text-muted">
                  <i class="bi bi-info-circle"></i> 
                  This amenity is currently used by {{ $usageStats['room_types_count'] }} room type(s). 
                  Deleting it may affect those room types.
                </small>
              </div>
            @endif
          </div>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        
        <!-- Amenity Summary -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-card-text"></i> Amenity Summary
            </h5>
          </div>
          <div class="card-body text-center">
            <div class="mb-3">
              <i class="{{ $roomAmenity->icon }} text-success" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-2">{{ $roomAmenity->name }}</h5>
            <p class="text-muted mb-3">{{ Str::limit($roomAmenity->description, 80) ?? 'No description available.' }}</p>
            
            <hr>
            
            <div class="row text-center">
              <div class="col-12 mb-2">
                <strong>Property:</strong>
                <small class="text-muted d-block">{{ $roomAmenity->property->name ?? 'Global' }}</small>
              </div>
              <div class="col-12 mb-2">
                <strong>Created:</strong>
                <small class="text-muted d-block">{{ $roomAmenity->created_at->format('M d, Y') }}</small>
              </div>
              <div class="col-12 mb-2">
                <strong>Last Updated:</strong>
                <small class="text-muted d-block">{{ $roomAmenity->updated_at->format('M d, Y') }}</small>
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
              @can('manage room types')
              <a href="{{ route('tenant.room-types.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Create Room Type
              </a>
              @endcan
              
              @can('manage room amenities')
              <a href="{{ route('tenant.room-amenities.edit', $roomAmenity) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil"></i> Edit Amenity
              </a>
              @endcan

              @can('manage room amenities')
              <a href="{{ route('tenant.room-amenities.create') }}" class="btn btn-outline-success">
                <i class="bi bi-plus-circle"></i> Create New Amenity
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