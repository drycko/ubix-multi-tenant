@extends('tenant.layouts.app')

@section('title', 'Room Status Grid')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Room Status Grid</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.housekeeping.index') }}">Housekeeping</a></li>
          <li class="breadcrumb-item active" aria-current="page">Room Status</li>
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

    <!-- Filters and Actions -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Filters</h3>
          </div>
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.room-status.index') }}">
              <div class="row">
                <div class="col-md-3">
                  <label for="status">Status</label>
                  <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="dirty" {{ request('status') == 'dirty' ? 'selected' : '' }}>Dirty</option>
                    <option value="clean" {{ request('status') == 'clean' ? 'selected' : '' }}>Clean</option>
                    <option value="inspected" {{ request('status') == 'inspected' ? 'selected' : '' }}>Inspected</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="out_of_order" {{ request('status') == 'out_of_order' ? 'selected' : '' }}>Out of Order</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="floor">Floor</label>
                  <select name="floor" id="floor" class="form-select">
                    <option value="">All Floors</option>
                    @foreach($floors as $floorNum)
                    @php $floorNum = intval($floorNum) ?? 0; @endphp
                    <option value="{{ $floorNum }}" {{ request('floor') == $floorNum ? 'selected' : '' }}>
                      Floor {{ $floorNum }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="property_id">Property</label>
                  <select name="property_id" id="property_id" class="form-select">
                    <option value="">All Properties</option>
                    @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                      {{ $property->name }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label>&nbsp;</label>
                  <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                <i class="bi bi-people"></i> Bulk Assign
              </button>
              <a href="{{ route('tenant.room-status.initialize') }}" class="btn btn-warning" 
                 onclick="return confirm('This will initialize room statuses for rooms without status. Continue?')">
                <i class="bi bi-gear"></i> Initialize Statuses
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Room Status Grid -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Room Status Overview</h3>
            <div class="card-tools">
              <span class="badge badge-secondary">{{ $roomStatuses->count() }} rooms</span>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($roomStatuses->groupBy('property.name') as $propertyName => $propertyRooms)
                <div class="col-12 mb-4">
                  <h5 class="text-muted">{{ $propertyName }}</h5>
                  <div class="row">
                    @foreach($propertyRooms->groupBy('room.floor') as $floor => $floorRooms)
                      <div class="col-12 mb-3">
                        <h6 class="text-sm text-muted">Floor {{ intval($floor) ?? 0 }}</h6>
                        <div class="row">
                          @foreach($floorRooms as $roomStatus)
                          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                            <div class="card room-status-card border-{{ $roomStatus->status_color }}" 
                                 data-room-status-id="{{ $roomStatus->id }}" 
                                 style="cursor: pointer;">
                              <div class="card-body text-center p-3">
                                <div class="room-number h5 mb-1 text-muted">{{ $roomStatus->room->number }}</div>
                                <div class="room-type text-xs text-muted mb-2">{{ $roomStatus->room->type->name }}</div>
                                
                                <!-- Status Badges -->
                                <div class="status-badges mb-2">
                                  <span class="badge badge-{{ $roomStatus->status_color }} badge-sm">
                                    {{ ucfirst(str_replace('_', ' ', $roomStatus->status)) }}
                                  </span>
                                  <br>
                                  <span class="badge badge-{{ $roomStatus->housekeeping_status_color }} badge-sm mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $roomStatus->housekeeping_status)) }}
                                  </span>
                                </div>

                                <!-- Assigned Staff -->
                                @if($roomStatus->assignedTo)
                                <div class="assigned-staff text-xs text-muted">
                                  <i class="bi bi-person"></i> {{ $roomStatus->assignedTo->name }}
                                </div>
                                @endif

                                <!-- Timing Info -->
                                @if($roomStatus->started_at && !$roomStatus->completed_at)
                                <div class="timing-info text-xs text-info">
                                  Started: {{ $roomStatus->started_at->format('H:i') }}
                                </div>
                                @elseif($roomStatus->completed_at)
                                <div class="timing-info text-xs text-success">
                                  Completed: {{ $roomStatus->completed_at->format('H:i') }}
                                </div>
                                @endif
                              </div>
                              
                              <!-- Action Buttons -->
                              <div class="card-footer p-2">
                                <div class="btn-group w-100" role="group">
                                  @if($roomStatus->canBeAssigned())
                                  <button type="button" class="btn btn-sm btn-outline-primary assign-btn" 
                                          data-room-status-id="{{ $roomStatus->id }}">
                                    <i class="bi bi-person-plus"></i>
                                  </button>
                                  @endif
                                  
                                  @if($roomStatus->canStart())
                                  <button type="button" class="btn btn-sm btn-outline-success start-btn" 
                                          data-room-status-id="{{ $roomStatus->id }}">
                                    <i class="bi bi-play"></i>
                                  </button>
                                  @endif
                                  
                                  @if($roomStatus->canComplete())
                                  <button type="button" class="btn btn-sm btn-outline-warning complete-btn" 
                                          data-room-status-id="{{ $roomStatus->id }}">
                                    <i class="bi bi-check"></i>
                                  </button>
                                  @endif
                                  
                                  @if($roomStatus->housekeeping_status === 'completed')
                                  <button type="button" class="btn btn-sm btn-outline-info inspect-btn" 
                                          data-room-status-id="{{ $roomStatus->id }}">
                                    <i class="bi bi-shield-check"></i>
                                  </button>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </div>
                          @endforeach
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Modals -->
@include('tenant.room-status.modals.bulk-assign')
@include('tenant.room-status.modals.room-details')
@include('tenant.room-status.modals.assign-staff')
@include('tenant.room-status.modals.complete-work')
@include('tenant.room-status.modals.inspect-room')

<style>
.room-status-card {
    transition: all 0.2s ease;
    min-height: 140px;
}

.room-status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.room-number {
    font-weight: bold;
    color: #333;
}

.badge-sm {
    font-size: 0.65em;
}

.timing-info {
    margin-top: 0.25rem;
}

.assigned-staff {
    margin-top: 0.25rem;
}
</style>
@endsection