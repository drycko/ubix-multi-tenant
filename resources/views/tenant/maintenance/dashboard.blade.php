@extends('tenant.layouts.app')

@section('title', 'Maintenance Dashboard')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">Maintenance Dashboard</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
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

    <!-- Quick Actions Row -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            <div class="btn-group" role="group">
              <a href="{{ route('tenant.maintenance.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Request
              </a>
              <a href="{{ route('tenant.maintenance.tasks') }}" class="btn btn-success">
                <i class="bi bi-list-task"></i> Manage Tasks
              </a>
              <a href="{{ route('tenant.maintenance.index') }}" class="btn btn-info">
                <i class="bi bi-clipboard-data"></i> All Requests
              </a>
              <a href="{{ route('tenant.room-status.index') }}" class="btn btn-warning">
                <i class="bi bi-grid-3x3-gap"></i> Room Status
              </a>
              <a href="{{ route('tenant.housekeeping.index') }}" class="btn btn-secondary">
                <i class="bi bi-house"></i> Housekeeping
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Overview Cards -->
    <div class="row">
      <!-- Request Status Summary -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Request Status Overview</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-2">
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3>{{ $statusCounts['pending'] }}</h3>
                    <p>Pending</p>
                  </div>
                  <i class="bi bi-clock small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3>{{ $statusCounts['assigned'] }}</h3>
                    <p>Assigned</p>
                  </div>
                  <i class="bi bi-person-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-info">
                  <div class="inner">
                    <h3>{{ $statusCounts['in_progress'] }}</h3>
                    <p>In Progress</p>
                  </div>
                  <i class="bi bi-gear small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>{{ $statusCounts['completed'] }}</h3>
                    <p>Completed</p>
                  </div>
                  <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-dark">
                  <div class="inner">
                    <h3>{{ $statusCounts['on_hold'] }}</h3>
                    <p>On Hold</p>
                  </div>
                  <i class="bi bi-pause-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3>{{ $statusCounts['cancelled'] }}</h3>
                    <p>Cancelled</p>
                  </div>
                  <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Priority Overview -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Priority Breakdown</h3>
          </div>
          <div class="card-body">
            <div class="progress-group">
              <div class="progress-text">Urgent</div>
              <div class="float-end"><b>{{ $priorityCounts['urgent'] }}</b></div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-danger" style="width: {{ array_sum($priorityCounts) > 0 ? ($priorityCounts['urgent'] / array_sum($priorityCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
            <div class="progress-group">
              <div class="progress-text">High</div>
              <div class="float-end"><b>{{ $priorityCounts['high'] }}</b></div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-warning" style="width: {{ array_sum($priorityCounts) > 0 ? ($priorityCounts['high'] / array_sum($priorityCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
            <div class="progress-group">
              <div class="progress-text">Normal</div>
              <div class="float-end"><b>{{ $priorityCounts['normal'] }}</b></div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ array_sum($priorityCounts) > 0 ? ($priorityCounts['normal'] / array_sum($priorityCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
            <div class="progress-group">
              <div class="progress-text">Low</div>
              <div class="float-end"><b>{{ $priorityCounts['low'] }}</b></div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ array_sum($priorityCounts) > 0 ? ($priorityCounts['low'] / array_sum($priorityCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Overview Row -->
    <div class="row mt-3">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Task Status Overview</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3>{{ $taskCounts['pending'] }}</h3>
                    <p>Pending Tasks</p>
                  </div>
                  <i class="bi bi-list-ul small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-3">
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3>{{ $taskCounts['assigned'] }}</h3>
                    <p>Assigned Tasks</p>
                  </div>
                  <i class="bi bi-person-lines-fill small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-3">
                <div class="small-box text-bg-info">
                  <div class="inner">
                    <h3>{{ $taskCounts['in_progress'] }}</h3>
                    <p>In Progress</p>
                  </div>
                  <i class="bi bi-arrow-repeat small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-3">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>{{ $taskCounts['completed'] }}</h3>
                    <p>Completed Tasks</p>
                  </div>
                  <i class="bi bi-check2-all small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3 mb-3">
      <!-- Today's Tasks -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Today's Tasks</h3>
            <div class="card-tools">
              <span class="badge bg-primary">{{ $todaysTasks->count() }} tasks</span>
            </div>
          </div>
          <div class="card-body table-responsive p-0" style="max-height: 400px;">
            <table class="table table-striped text-nowrap">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Task Type</th>
                  <th>Priority</th>
                  <th>Assigned To</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                @forelse($todaysTasks as $task)
                <tr>
                  <td>
                    <a href="{{ route('tenant.maintenance.show', $task->maintenanceRequest) }}">
                      {{ $task->maintenanceRequest->room->number ?? 'Property-wide' }}
                    </a>
                  </td>
                  <td>{{ ucfirst($task->task_type) }}</td>
                  <td>
                    <span class="badge bg-{{ $task->priority_color }}">
                      {{ ucfirst($task->priority) }}
                    </span>
                  </td>
                  <td>{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                  <td>
                    <span class="badge bg-{{ $task->status_color }}">
                      {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                  </td>
                  <td>{{ $task->scheduled_for ? $task->scheduled_for->format('H:i') : '-' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center">No tasks scheduled for today</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Overdue Tasks -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Overdue Tasks</h3>
            <div class="card-tools">
              <span class="badge bg-danger">{{ $overdueTasks->count() }} overdue</span>
            </div>
          </div>
          <div class="card-body table-responsive p-0" style="max-height: 400px;">
            <table class="table table-striped text-nowrap">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Task Type</th>
                  <th>Priority</th>
                  <th>Assigned To</th>
                  <th>Overdue By</th>
                </tr>
              </thead>
              <tbody>
                @forelse($overdueTasks as $task)
                <tr class="text-danger">
                  <td>
                    <a href="{{ route('tenant.maintenance.show', $task->maintenanceRequest) }}">
                      {{ $task->maintenanceRequest->room->number ?? 'Property-wide' }}
                    </a>
                  </td>
                  <td>{{ ucfirst($task->task_type) }}</td>
                  <td>
                    <span class="badge bg-{{ $task->priority_color }}">
                      {{ ucfirst($task->priority) }}
                    </span>
                  </td>
                  <td>{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                  <td>{{ $task->scheduled_for->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center">No overdue tasks</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Active Maintenance Requests -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Active Maintenance Requests</h3>
            <div class="card-tools">
              <a href="{{ route('tenant.maintenance.index') }}" class="btn btn-tool">View All</a>
            </div>
          </div>
          <div class="card-body table-responsive p-0">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Request #</th>
                  <th>Room</th>
                  <th>Category</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Assigned To</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                @forelse($activeRequests as $request)
                <tr>
                  <td>
                    <a href="{{ route('tenant.maintenance.show', $request) }}">
                      {{ $request->request_number ?? 'MR' . $request->id }}
                    </a>
                  </td>
                  <td>{{ $request->room->number ?? 'Property-wide' }}</td>
                  <td>{{ ucfirst(str_replace('_', ' ', $request->category)) }}</td>
                  <td>
                    <span class="badge bg-{{ $request->priority_color }}">
                      {{ ucfirst($request->priority) }}
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-{{ $request->status_color }}">
                      {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                  </td>
                  <td>{{ $request->assignedTo->name ?? 'Unassigned' }}</td>
                  <td>{{ $request->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center">No active maintenance requests</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Staff Performance -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Staff Performance (Today)</h3>
          </div>
          <div class="card-body">
            @forelse($staffPerformance as $staff)
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
              <div>
                <strong>{{ $staff->name }}</strong><br>
                <small class="text-muted">
                  {{ $staff->completed_tasks }}/{{ $staff->total_tasks }} tasks
                  @if($staff->avg_time)
                    • Avg: {{ round($staff->avg_time) }}min
                  @endif
                </small>
              </div>
              <div class="text-end">
                <span class="badge bg-{{ $staff->completed_tasks == $staff->total_tasks ? 'success' : 'warning' }}">
                  {{ round($staff->efficiency) }}%
                </span>
              </div>
            </div>
            @empty
            <p class="text-center text-muted">No staff activity today</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->
@endsection