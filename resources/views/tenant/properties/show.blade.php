@extends('tenant.layouts.app')

@section('title', $property->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">
          {{ $property->name }}
          <span class="badge bg-{{ $property->is_active ? 'success' : 'danger' }} ms-2">
            {{ $property->is_active ? 'Active' : 'Inactive' }}
          </span>
        </h4>
        <small class="text-muted">{{ $property->code }}</small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.properties.index') }}">Properties</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $property->name }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    <!-- Action Bar -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-0">
                  <i class="bi bi-info-circle"></i> Property Details
                </h5>
              </div>
              <div>
                <!-- Enter Property -->
                <a href="{{ route('tenant.dashboard') }}?switch_property={{ $property->id }}" 
                   class="btn btn-primary me-2">
                  <i class="bi bi-arrow-right"></i> Enter Property
                </a>
                
                <!-- Edit -->
                <a href="{{ route('tenant.properties.edit', $property) }}" class="btn btn-warning me-2">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                
                <!-- Toggle Status -->
                <form action="{{ route('tenant.properties.toggle-status', $property) }}" 
                      method="POST" class="d-inline me-2">
                  @csrf
                  <button type="submit" 
                          class="btn btn-outline-{{ $property->is_active ? 'danger' : 'success' }}" 
                          onclick="return confirm('Are you sure you want to {{ $property->is_active ? 'deactivate' : 'activate' }} this property?')">
                    <i class="bi bi-{{ $property->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                    {{ $property->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
                
                <!-- Back to List -->
                <a href="{{ route('tenant.properties.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-list"></i> All Properties
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
                <label class="form-label fw-bold">Property Name</label>
                <p class="mb-0">{{ $property->name }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Property Code</label>
                <p class="mb-0">
                  <code>{{ $property->code }}</code>
                </p>
              </div>
              @if($property->description)
                <div class="col-12 mb-3">
                  <label class="form-label fw-bold">Description</label>
                  <p class="mb-0">{{ $property->description }}</p>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Location Information -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-geo-alt"></i> Location
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-12 mb-3">
                <label class="form-label fw-bold">Address</label>
                <p class="mb-0">{{ $property->address }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">City</label>
                <p class="mb-0">{{ $property->city }}</p>
              </div>
              @if($property->state)
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">State/Province</label>
                  <p class="mb-0">{{ $property->state }}</p>
                </div>
              @endif
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Country</label>
                <p class="mb-0">{{ $property->country }}</p>
              </div>
              @if($property->postal_code)
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Postal Code</label>
                  <p class="mb-0">{{ $property->postal_code }}</p>
                </div>
              @endif
            </div>
            
            <!-- Full Address Badge -->
            <div class="mt-2">
              <span class="badge bg-light text-dark">
                <i class="bi bi-geo-alt-fill"></i>
                {{ $property->address }}, {{ $property->city }}, {{ $property->country }}
              </span>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        @if($property->phone || $property->email || $property->website)
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="bi bi-telephone"></i> Contact Information
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                @if($property->phone)
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Phone</label>
                    <p class="mb-0">
                      <a href="tel:{{ $property->phone }}" class="text-decoration-none">
                        <i class="bi bi-telephone"></i> {{ $property->phone }}
                      </a>
                    </p>
                  </div>
                @endif
                @if($property->email)
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <p class="mb-0">
                      <a href="mailto:{{ $property->email }}" class="text-decoration-none">
                        <i class="bi bi-envelope"></i> {{ $property->email }}
                      </a>
                    </p>
                  </div>
                @endif
                @if($property->website)
                  <div class="col-12 mb-3">
                    <label class="form-label fw-bold">Website</label>
                    <p class="mb-0">
                      <a href="{{ $property->website }}" target="_blank" class="text-decoration-none">
                        <i class="bi bi-globe"></i> {{ $property->website }}
                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                      </a>
                    </p>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endif

        <!-- Configuration -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-gear"></i> Configuration
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Currency</label>
                <p class="mb-0">
                  <span class="badge bg-success">{{ $property->currency }}</span>
                  <small class="text-muted ms-2">{{ get_currency_name($property->currency) }}</small>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Timezone</label>
                <p class="mb-0">
                  <span class="badge bg-info">{{ $property->timezone }}</span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Status</label>
                <p class="mb-0">
                  <span class="badge bg-{{ $property->is_active ? 'success' : 'danger' }}">
                    <i class="bi bi-{{ $property->is_active ? 'check-circle' : 'x-circle' }}"></i>
                    {{ $property->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Created</label>
                <p class="mb-0">
                  {{ \Carbon\Carbon::parse($property->created_at)->format('M d, Y') }}
                  <small class="text-muted">({{ \Carbon\Carbon::parse($property->created_at)->diffForHumans() }})</small>
                </p>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        
        <!-- Statistics -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-graph-up"></i> Statistics
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 mb-3">
                <div class="border rounded p-3">
                  <h4 class="text-primary mb-0">{{ $stats['rooms_count'] }}</h4>
                  <small class="text-muted">Rooms</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="border rounded p-3">
                  <h4 class="text-warning mb-0">{{ $stats['users_count'] }}</h4>
                  <small class="text-muted">Users</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="border rounded p-3">
                  <h4 class="text-success mb-0">{{ $stats['bookings_count'] ?? 0 }}</h4>
                  <small class="text-muted">Bookings</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="border rounded p-3">
                  <h4 class="text-info mb-0">{{ $stats['guests_count'] ?? 0 }}</h4>
                  <small class="text-muted">Guests</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-lightning"></i> Quick Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.dashboard') }}?switch_property={{ $property->id }}" 
                 class="btn btn-primary">
                <i class="bi bi-arrow-right"></i> Enter Property Dashboard
              </a>
              
              <a href="{{ route('tenant.rooms.index') }}?property_id={{ $property->id }}" 
                 class="btn btn-outline-info">
                <i class="bi bi-door-open"></i> View Rooms ({{ $property->rooms_count }})
              </a>
              
              @if($property->users_count > 0)
                <a href="{{ route('tenant.users.index') }}?property_id={{ $property->id }}" 
                   class="btn btn-outline-warning">
                  <i class="bi bi-people"></i> View Users ({{ $property->users_count }})
                </a>
              @endif
              
              <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots"></i> More Actions
                </button>
                <ul class="dropdown-menu w-100">
                  <li>
                    <form action="{{ route('tenant.properties.clone', $property) }}" method="POST" class="dropdown-item p-0">
                      @csrf
                      <button type="submit" class="btn btn-link text-decoration-none text-start w-100 p-2"
                              onclick="return confirm('Clone this property?')">
                        <i class="bi bi-files"></i> Clone Property
                      </button>
                    </form>
                  </li>
                  @if($property->rooms_count == 0 && $property->users_count == 0)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form action="{{ route('tenant.properties.destroy', $property) }}" method="POST" class="dropdown-item p-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger text-decoration-none text-start w-100 p-2"
                                onclick="return confirm('Are you sure? This action cannot be undone.')">
                          <i class="bi bi-trash"></i> Delete Property
                        </button>
                      </form>
                    </li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        @if($property->updated_at != $property->created_at)
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="bi bi-clock-history"></i> Recent Activity
              </h5>
            </div>
            <div class="card-body">
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-marker bg-warning"></div>
                  <div class="timeline-content">
                    <h6 class="timeline-title">Property Updated</h6>
                    <p class="timeline-text text-muted">
                      {{ $property->updated_at->format('M d, Y \a\t g:i A') }}
                      <small>({{ $property->updated_at->diffForHumans() }})</small>
                    </p>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-marker bg-success"></div>
                  <div class="timeline-content">
                    <h6 class="timeline-title">Property Created</h6>
                    <p class="timeline-text text-muted">
                      {{ $property->created_at->format('M d, Y \a\t g:i A') }}
                      <small>({{ $property->created_at->diffForHumans() }})</small>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif

      </div>
    </div>

  </div>
</div>

@push('styles')
<style>
.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline-item {
  position: relative;
  margin-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
  content: '';
  position: absolute;
  left: -1.5rem;
  top: 1.5rem;
  bottom: -1.5rem;
  width: 2px;
  background-color: #dee2e6;
}

.timeline-marker {
  position: absolute;
  left: -1.75rem;
  top: 0.25rem;
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  border: 2px solid #fff;
}

.timeline-content {
  padding-left: 0.5rem;
}

.timeline-title {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.timeline-text {
  font-size: 0.75rem;
  margin-bottom: 0;
}
</style>
@endpush
@endsection