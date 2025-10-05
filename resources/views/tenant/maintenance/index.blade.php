@extends('tenant.layouts.app')

@section('title', 'Maintenance Requests')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Maintenance Requests</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.housekeeping.index') }}">Housekeeping</a></li>
          <li class="breadcrumb-item active" aria-current="page">Maintenance</li>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info">
          <div class="inner">
            <h3>{{ $stats['total'] }}</h3>
            <p>Total Requests</p>
          </div>
          <i class="bi bi-tools small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
          <div class="inner">
            <h3>{{ $stats['pending'] }}</h3>
            <p>Pending</p>
          </div>
          <i class="bi bi-clock small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
          <div class="inner">
            <h3>{{ $stats['in_progress'] }}</h3>
            <p>In Progress</p>
          </div>
          <i class="bi bi-gear small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box text-bg-danger">
          <div class="inner">
            <h3>{{ $stats['urgent'] }}</h3>
            <p>Urgent</p>
          </div>
          <i class="bi bi-exclamation-triangle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mb-4">
      <div class="col-md-9">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Filters</h3>
          </div>
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.maintenance.index') }}">
              <div class="row">
                <div class="col-md-2">
                  <label for="status">Status</label>
                  <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                      {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="category">Category</label>
                  <select name="category" id="category" class="form-select">
                    <option value="">All</option>
                    @foreach($categories as $categoryOption)
                    <option value="{{ $categoryOption }}" {{ request('category') == $categoryOption ? 'selected' : '' }}>
                      {{ ucfirst($categoryOption) }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label for="priority">Priority</label>
                  <select name="priority" id="priority" class="form-select">
                    <option value="">All</option>
                    @foreach($priorities as $priorityOption)
                    <option value="{{ $priorityOption }}" {{ request('priority') == $priorityOption ? 'selected' : '' }}>
                      {{ ucfirst($priorityOption) }}
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

      <div class="col-md-3">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Actions</h3>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.maintenance.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> New Request
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Maintenance Requests Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">All Maintenance Requests</h3>
            <div class="card-tools">
              <span class="badge bg-secondary">{{ $maintenanceRequests->total() }} total</span>
            </div>
          </div>
          <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th>Request #</th>
                  <th>Room</th>
                  <th>Category</th>
                  <th>Priority</th>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Reported By</th>
                  <th>Assigned To</th>
                  <th>Est. Cost</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($maintenanceRequests as $request)
                <tr class="{{ $request->priority === 'urgent' ? 'table-danger' : '' }}">
                  <td>
                    <a href="{{ route('tenant.maintenance.show', $request) }}" class="text-decoration-none">
                      <strong>{{ $request->request_number }}</strong>
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-secondary">{{ $request->room->number }}</span>
                    <br><small class="text-muted">{{ $request->room->type->name }}</small>
                  </td>
                  <td>
                    <i class="bi bi-{{ 
                      match($request->category) {
                        'electrical' => 'lightning',
                        'plumbing' => 'droplet',
                        'hvac' => 'wind',
                        'furniture' => 'house',
                        'appliance' => 'gear',
                        default => 'tools'
                      }
                    }}"></i>
                    {{ ucfirst($request->category) }}
                  </td>
                  <td>
                    <span class="badge bg-{{ $request->priority_color }}">
                      {{ ucfirst($request->priority) }}
                    </span>
                    @if($request->requires_room_closure)
                    <br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Room Closure</small>
                    @endif
                  </td>
                  <td>
                    <strong>{{ $request->title }}</strong>
                    @if($request->location_details)
                    <br><small class="text-muted">{{ Str::limit($request->location_details, 30) }}</small>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-{{ $request->status_color }}">
                      {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                    @if($request->isOverdue())
                    <br><small class="text-danger">Overdue</small>
                    @endif
                  </td>
                  <td>{{ $request->reportedBy->name }}</td>
                  <td>{{ $request->assignedTo->name ?? 'Unassigned' }}</td>
                  <td>
                    @if($request->estimated_cost)
                      ${{ number_format($request->estimated_cost, 2) }}
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>{{ $request->created_at->format('M j, Y') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="{{ route('tenant.maintenance.show', $request) }}" 
                         class="btn btn-outline-info" title="View">
                        <i class="bi bi-eye"></i>
                      </a>
                      @can('edit maintenance requests')
                      <a href="{{ route('tenant.maintenance.edit', $request) }}" 
                         class="btn btn-outline-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                      @endcan
                      
                      @if($request->status === 'pending' && $request->assignedTo)
                      <form method="POST" action="{{ route('tenant.maintenance.start', $request) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success" title="Start Work">
                          <i class="bi bi-play"></i>
                        </button>
                      </form>
                      @endif
                      
                      @if($request->status === 'in_progress')
                      <button type="button" class="btn btn-outline-warning complete-btn" 
                              data-request-id="{{ $request->id }}" title="Complete">
                        <i class="bi bi-check"></i>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="11" class="text-center">No maintenance requests found</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            {{ $maintenanceRequests->links() }}
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Complete Work Modal -->
<div class="modal fade" id="completeWorkModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="#" id="completeMaintenanceForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Complete Maintenance Work</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="resolution_notes" class="form-label">Resolution Notes *</label>
            <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="parts_used" class="form-label">Parts Used</label>
            <textarea name="parts_used" id="parts_used" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label for="actual_cost" class="form-label">Actual Cost</label>
            <input type="number" name="actual_cost" id="actual_cost" class="form-control" step="0.01" min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Complete Work</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
