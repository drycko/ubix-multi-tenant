@extends('tenant.layouts.app')

@section('title', 'Cleaning Schedules & Checklists')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Cleaning Schedules & Checklists</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.housekeeping.index') }}">Housekeeping</a></li>
          <li class="breadcrumb-item active" aria-current="page">Cleaning Schedules</li>
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

    <!-- Quick Actions -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body row">
            <div class="col-md-6">
              <div class="btn-group" role="group">
                <a href="{{ route('tenant.cleaning-schedule.calendar') }}" class="btn btn-primary">
                  <i class="bi bi-calendar3"></i> Schedule Calendar
                </a>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#generateScheduleModal">
                  <i class="bi bi-gear"></i> Generate Schedule
                </button>
                <form method="POST" action="{{ route('tenant.cleaning-schedule.load-defaults') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="property_id" value="{{ request('property_id') ?: (session('selected_property_id') ?: '') }}">
                  <button type="submit" class="btn btn-secondary" 
                          onclick="return confirm('This will create default checklists. Continue?')">
                    <i class="bi bi-download"></i> Load Defaults
                  </button>
                </form>
              </div>
            </div>
            <div class="col-md-6">
              {{-- push element to the right --}}
              <div class="float-end">
                <a href="{{ route('tenant.cleaning-schedule.create') }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Checklist
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Filters</h3>
          </div>
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.cleaning-schedule.index') }}">
              <div class="row">
                <div class="col-md-4">
                  <label for="type">Checklist Type</label>
                  <select name="type" id="type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($types as $typeOption)
                    <option value="{{ $typeOption }}" {{ request('type') == $typeOption ? 'selected' : '' }}>
                      {{ ucfirst(str_replace('_', ' ', $typeOption)) }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-4">
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
    </div>

    <!-- Checklists -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Cleaning Checklists</h3>
            <div class="card-tools">
              <span class="badge badge-secondary">{{ $checklists->count() }} checklists</span>
            </div>
          </div>
          <div class="card-body">
            @if($checklists->count() > 0)
            <div class="row">
              @foreach($checklists->groupBy('checklist_type') as $type => $typeChecklists)
              <div class="col-md-6 mb-4">
                <h5 class="text-muted">
                  <span class="badge badge-{{ 
                    match($type) {
                      'standard' => 'primary',
                      'checkout' => 'warning', 
                      'deep_clean' => 'info',
                      'maintenance' => 'danger',
                      'inspection' => 'success',
                      default => 'secondary'
                    }
                  }}">
                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                  </span>
                </h5>
                
                @foreach($typeChecklists as $checklist)
                <div class="card mb-3 border-{{ $checklist->type_color }}">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="row">
                        <h6 class="card-title mb-1">
                          <a href="{{ route('tenant.cleaning-schedule.show', $checklist) }}" class="text-decoration-none">
                            {{ $checklist->name }}
                          </a>
                        </h6>
                        <p class="card-text text-muted ">
                          {{ $checklist->description ?: 'No description' }}
                        </p>
                        <div class="small text-muted">
                          <i class="bi bi-list-check"></i> {{ $checklist->items_count }} items
                          @if($checklist->estimated_minutes)
                          • <i class="bi bi-clock"></i> {{ $checklist->estimated_minutes }} min
                          @endif
                          @if($checklist->roomType)
                          • <i class="bi bi-door-open"></i> {{ $checklist->roomType->name }}
                          @endif
                        </div>
                      </div>
                      <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('tenant.cleaning-schedule.show', $checklist) }}" 
                           class="btn btn-outline-info" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        @can('edit cleaning schedules')
                        <a href="{{ route('tenant.cleaning-schedule.edit', $checklist) }}" 
                           class="btn btn-outline-primary" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('tenant.cleaning-schedule.duplicate', $checklist) }}" class="d-inline">
                          @csrf
                          <button type="submit" class="btn btn-outline-secondary" title="Duplicate">
                            <i class="bi bi-files"></i>
                          </button>
                        </form>
                        @endcan
                        
                        @if(!$checklist->is_active)
                        <span class="badge badge-secondary ms-2">Inactive</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              @endforeach
            </div>
            @else
            <div class="text-center py-4">
              <i class="bi bi-list-check text-muted" style="font-size: 3rem;"></i>
              <h4 class="text-muted">No Checklists Found</h4>
              <p class="text-muted">Create your first cleaning checklist to get started.</p>
              <a href="{{ route('tenant.cleaning-schedule.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Create Checklist
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Generate Schedule Modal -->
<div class="modal fade" id="generateScheduleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('tenant.cleaning-schedule.generate') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-gear-fill"></i> Generate Cleaning Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <!-- Form Fields -->
              <div class="mb-3">
                <label for="schedule_property_id" class="form-label">Property <span class="text-danger">*</span></label>
                <select name="property_id" id="schedule_property_id" class="form-select" required>
                  <option value="">Select Property</option>
                  @foreach($properties as $property)
                  <option value="{{ $property->id }}">{{ $property->name }}</option>
                  @endforeach
                </select>
              </div>
              
              <div class="mb-3">
                <label for="date_range" class="form-label">Date Range <span class="text-danger">*</span></label>
                <select name="date_range" id="date_range" class="form-select" required>
                  <option value="today">Today Only</option>
                  <option value="tomorrow">Tomorrow Only</option>
                  <option value="week" selected>This Week</option>
                  <option value="month">This Month</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label for="task_type" class="form-label">Task Type <span class="text-danger">*</span></label>
                <select name="task_type" id="task_type" class="form-select" required>
                  @foreach($taskTypes as $taskType)
                  <option value="{{ $taskType }}" {{ $taskType == 'cleaning' ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $taskType)) }}
                  </option>
                  @endforeach
                </select>
              </div>
              
              <div class="mb-3">
                <label for="schedule_assigned_to" class="form-label">Assign To</label>
                <select name="assigned_to" id="schedule_assigned_to" class="form-select">
                  <option value="">Leave Unassigned</option>
                  @foreach(\App\Models\Tenant\User::whereHas('roles', function($q) {
                    $q->where('name', 'like', '%housekeeping%');
                  })->get() as $staff)
                  <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                  @endforeach
                </select>
                <div class="form-text">Tasks can be assigned later if left blank</div>
              </div>
              
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Important:</strong> This will create housekeeping tasks for all enabled rooms in the selected property and date range. Existing tasks for the same date/room will be skipped.
              </div>
            </div>
            
            <div class="col-md-4">
              <!-- Guidance Panel -->
              <div class="card h-100">
                <div class="card-header">
                  <h6 class="card-title mb-0"><i class="bi bi-lightbulb"></i> Schedule Generation Guide</h6>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <h6 class="text-primary">What This Does:</h6>
                    <ul class="small mb-0">
                      <li>Creates individual tasks for each room</li>
                      <li>Uses existing checklist templates</li>
                      <li>Schedules tasks automatically</li>
                      <li>Assigns to staff (optional)</li>
                    </ul>
                  </div>
                  
                  <div class="mb-3">
                    <h6 class="text-info">Date Range Options:</h6>
                    <div class="small">
                      <div class="mb-1"><strong>Today:</strong> Urgent tasks only</div>
                      <div class="mb-1"><strong>Tomorrow:</strong> Next day planning</div>
                      <div class="mb-1"><strong>Week:</strong> Standard scheduling</div>
                      <div class="mb-1"><strong>Month:</strong> Bulk planning</div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <h6 class="text-success">Smart Features:</h6>
                    <ul class="small mb-0">
                      <li>Skips existing tasks</li>
                      <li>Uses appropriate checklists</li>
                      <li>Sets realistic timeframes</li>
                      <li>Avoids weekends for routine cleaning</li>
                    </ul>
                  </div>
                  
                  <div class="alert alert-info p-2">
                    <small>
                      <i class="bi bi-info-circle"></i>
                      <strong>Tip:</strong> Generate weekly schedules on Sunday evenings for optimal planning.
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-calendar-plus"></i> Generate Schedule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection