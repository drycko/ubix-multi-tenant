@extends('tenant.layouts.app')

@section('title', $roomRate->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">
          {{ $roomRate->name }}
          <span class="badge bg-{{ $roomRate->is_active ? 'success' : 'danger' }} ms-2">
            {{ $roomRate->is_active ? 'Active' : 'Inactive' }}
          </span>
        </h4>
        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $roomRate->rate_type)) }} Rate</small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-rates.index') }}">Room Rates</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $roomRate->name }}</li>
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
                  <i class="bi bi-currency-dollar"></i> Room Rate Details
                </h5>
              </div>
              <div>
                <!-- Edit -->
                @can('manage room rates')
                <a href="{{ route('tenant.room-rates.edit', $roomRate) }}" class="btn btn-warning me-2">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                @endcan
                
                <!-- Toggle Status -->
                @can('manage room rates')
                <form action="{{ route('tenant.room-rates.toggle-status', $roomRate) }}" 
                      method="POST" class="d-inline me-2">
                  @csrf
                  <button type="submit" 
                          class="btn btn-outline-{{ $roomRate->is_active ? 'danger' : 'success' }}" 
                          onclick="return confirm('Are you sure you want to {{ $roomRate->is_active ? 'deactivate' : 'activate' }} this room rate?')">
                    <i class="bi bi-{{ $roomRate->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                    {{ $roomRate->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
                @endcan
                
                <!-- Clone -->
                @can('manage room rates')
                <form action="{{ route('tenant.room-rates.clone', $roomRate) }}" 
                      method="POST" class="d-inline me-2">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary"
                          onclick="return confirm('Clone this room rate?')">
                    <i class="bi bi-files"></i> Clone
                  </button>
                </form>
                @endcan
                
                <!-- Back to List -->
                <a href="{{ route('tenant.room-rates.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-list"></i> All Room Rates
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
                <label class="form-label fw-bold">Rate Name</label>
                <p class="mb-0">{{ $roomRate->name }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Room Type</label>
                <p class="mb-0">
                  <span class="badge bg-secondary">{{ $roomRate->roomType->name ?? 'N/A' }}</span>
                  @if($roomRate->roomType)
                    <br><small class="text-muted">{{ $roomRate->roomType->code }}</small>
                  @endif
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rate Type</label>
                <p class="mb-0">
                  <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $roomRate->rate_type)) }}</span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Amount</label>
                <p class="mb-0">
                  <span class="h4 text-success">R{{ number_format($roomRate->amount, 2) }}</span>
                  <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $roomRate->rate_type)) }}</small>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Effective Period -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-calendar-range"></i> Effective Period
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Effective From</label>
                <p class="mb-0">
                  <span class="badge bg-info fs-6">{{ $roomRate->effective_from->format('M d, Y') }}</span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Effective Until</label>
                <p class="mb-0">
                  @if($roomRate->effective_until)
                    <span class="badge bg-warning fs-6">{{ $roomRate->effective_until->format('M d, Y') }}</span>
                  @else
                    <span class="badge bg-success fs-6">Ongoing</span>
                  @endif
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Stay Requirements -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-moon"></i> Stay Requirements
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Minimum Nights</label>
                <p class="mb-0">
                  @if($roomRate->min_nights)
                    <span class="badge bg-info fs-6">{{ $roomRate->min_nights }} nights</span>
                  @else
                    <span class="text-muted">No minimum</span>
                  @endif
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Maximum Nights</label>
                <p class="mb-0">
                  @if($roomRate->max_nights)
                    <span class="badge bg-warning fs-6">{{ $roomRate->max_nights }} nights</span>
                  @else
                    <span class="text-muted">No maximum</span>
                  @endif
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Conditions -->
        @if($roomRate->conditions && count($roomRate->conditions) > 0)
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-list-check"></i> Additional Conditions
            </h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0">
              @foreach($roomRate->conditions as $condition)
                <li class="mb-2">
                  <i class="bi bi-check-circle text-success me-2"></i>
                  {{ $condition }}
                </li>
              @endforeach
            </ul>
          </div>
        </div>
        @endif

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        
        <!-- Status & Statistics -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-graph-up"></i> Rate Summary
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-12 mb-3">
                <div class="border rounded p-3 bg-light">
                  <h3 class="mb-1 text-success">R{{ number_format($roomRate->amount, 2) }}</h3>
                  <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $roomRate->rate_type)) }}</small>
                </div>
              </div>
            </div>
            
            <hr>
            
            <div class="row">
              <div class="col-12 mb-2">
                <strong>Status:</strong>
                <span class="badge bg-{{ $roomRate->is_active ? 'success' : 'danger' }} ms-2">
                  {{ $roomRate->is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              @if($roomRate->is_shared)
              <div class="col-12 mb-2">
                <strong>Scope:</strong>
                <span class="badge bg-warning ms-2">Shared Rate</span>
              </div>
              @endif
              <div class="col-12 mb-2">
                <strong>Property:</strong>
                <small class="text-muted">{{ $roomRate->property->name ?? 'N/A' }}</small>
              </div>
              <div class="col-12 mb-2">
                <strong>Created:</strong>
                <small class="text-muted">{{ $roomRate->created_at->format('M d, Y') }}</small>
              </div>
              <div class="col-12 mb-2">
                <strong>Last Updated:</strong>
                <small class="text-muted">{{ $roomRate->updated_at->format('M d, Y') }}</small>
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
              @can('manage bookings')
              <a href="{{ route('tenant.bookings.create') }}?room_type={{ $roomRate->room_type_id }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Create Booking
              </a>
              @endcan
              
              @can('manage room rates')
              <a href="{{ route('tenant.room-rates.edit', $roomRate) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil"></i> Edit Rate
              </a>
              @endcan

              @if($roomRate->roomType)
              <a href="{{ route('tenant.room-types.show', $roomRate->roomType) }}" class="btn btn-outline-info">
                <i class="bi bi-door-open"></i> View Room Type
              </a>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
@endsection