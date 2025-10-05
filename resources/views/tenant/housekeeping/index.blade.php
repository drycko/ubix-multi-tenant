@extends('tenant.layouts.app')

@section('title', 'Housekeeping Dashboard')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Housekeeping Dashboard</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Housekeeping</li>
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
              <a href="{{ route('tenant.room-status.index') }}" class="btn btn-primary">
                <i class="bi bi-grid-3x3-gap"></i> Room Status Grid
              </a>
              <a href="{{ route('tenant.housekeeping.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Create Task
              </a>
              <a href="{{ route('tenant.maintenance.create') }}" class="btn btn-warning">
                <i class="bi bi-tools"></i> Report Maintenance
              </a>
              <a href="{{ route('tenant.cleaning-schedule.calendar') }}" class="btn btn-info">
                <i class="bi bi-calendar3"></i> Schedule Calendar
              </a>
              <a href="{{ route('tenant.housekeeping.daily-report') }}" class="btn btn-secondary">
                <i class="bi bi-file-earmark-text"></i> Daily Report
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Overview Cards -->
    <div class="row">
      <!-- Room Status Summary -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Room Status Overview</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-2">
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3>{{ $statusCounts['dirty'] }}</h3>
                    <p>Dirty</p>
                  </div>
                  <i class="bi bi-exclamation-triangle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>{{ $statusCounts['clean'] }}</h3>
                    <p>Clean</p>
                  </div>
                  <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3>{{ $statusCounts['inspected'] }}</h3>
                    <p>Inspected</p>
                  </div>
                  <i class="bi bi-shield-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                  
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3>{{ $statusCounts['maintenance'] }}</h3>
                    <p>Maintenance</p>
                  </div>
                  <i class="bi bi-tools small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
              <div class="col-md-2">
                <div class="small-box text-bg-dark">
                  <div class="inner">
                    <h3>{{ $statusCounts['out_of_order'] }}</h3>
                    <p>Out of Order</p>
                  </div>
                  <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Housekeeping Status -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Housekeeping Progress</h3>
          </div>
          <div class="card-body">
            <div class="progress-group">
              <div class="progress-text">Pending</div>
              <div class="float-end"><b>{{ $housekeepingCounts['pending'] }}</b>/{{ array_sum($housekeepingCounts) }}</div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-warning" style="width: {{ array_sum($housekeepingCounts) > 0 ? ($housekeepingCounts['pending'] / array_sum($housekeepingCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
            <div class="progress-group">
              <div class="progress-text">In Progress</div>
              <div class="float-end"><b>{{ $housekeepingCounts['in_progress'] }}</b>/{{ array_sum($housekeepingCounts) }}</div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-info" style="width: {{ array_sum($housekeepingCounts) > 0 ? ($housekeepingCounts['in_progress'] / array_sum($housekeepingCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
            <div class="progress-group">
              <div class="progress-text">Completed</div>
              <div class="float-end"><b>{{ $housekeepingCounts['completed'] }}</b>/{{ array_sum($housekeepingCounts) }}</div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ array_sum($housekeepingCounts) > 0 ? ($housekeepingCounts['completed'] / array_sum($housekeepingCounts)) * 100 : 0 }}%"></div>
              </div>
            </div>
            <div class="progress-group">
              <div class="progress-text">Inspected</div>
              <div class="float-end"><b>{{ $housekeepingCounts['inspected'] }}</b>/{{ array_sum($housekeepingCounts) }}</div>
              <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ array_sum($housekeepingCounts) > 0 ? ($housekeepingCounts['inspected'] / array_sum($housekeepingCounts)) * 100 : 0 }}%"></div>
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
              <span class="badge badge-primary">{{ $todaysTasks->count() }} tasks</span>
            </div>
          </div>
          <div class="card-body table-responsive p-0" style="max-height: 400px;">
            <table class="table table-striped text-nowrap">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Task</th>
                  <th>Priority</th>
                  <th>Assigned To</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                @forelse($todaysTasks as $task)
                <tr>
                  <td>{{ $task->room->number }}</td>
                  <td>{{ $task->title }}</td>
                  <td>
                    <span class="badge badge-{{ $task->priority_color }}">
                      {{ ucfirst($task->priority) }}
                    </span>
                  </td>
                  <td>{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                  <td>
                    <span class="badge badge-{{ $task->status_color }}">
                      {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                  </td>
                  <td>{{ $task->scheduled_for->format('H:i') }}</td>
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
              <span class="badge badge-danger">{{ $overdueTasks->count() }} overdue</span>
            </div>
          </div>
          <div class="card-body table-responsive p-0" style="max-height: 400px;">
            <table class="table table-striped text-nowrap">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Task</th>
                  <th>Priority</th>
                  <th>Assigned To</th>
                  <th>Overdue By</th>
                </tr>
              </thead>
              <tbody>
                @forelse($overdueTasks as $task)
                <tr class="text-danger">
                  <td>{{ $task->room->number }}</td>
                  <td>{{ $task->title }}</td>
                  <td>
                    <span class="badge badge-{{ $task->priority_color }}">
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
                @forelse($maintenanceRequests as $request)
                <tr>
                  <td>
                    <a href="{{ route('tenant.maintenance.show', $request) }}">
                      {{ $request->request_number }}
                    </a>
                  </td>
                  <td>{{ $request->room->number }}</td>
                  <td>{{ ucfirst($request->category) }}</td>
                  <td>
                    <span class="badge badge-{{ $request->priority_color }}">
                      {{ ucfirst($request->priority) }}
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-{{ $request->status_color }}">
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
                <strong>{{ $staff->assignedTo->name }}</strong><br>
                <small class="text-muted">
                  {{ $staff->completed_tasks }}/{{ $staff->total_tasks }} tasks
                  @if($staff->avg_time)
                    • Avg: {{ round($staff->avg_time) }}min
                  @endif
                </small>
              </div>
              <div class="text-end">
                <span class="badge badge-{{ $staff->completed_tasks == $staff->total_tasks ? 'success' : 'warning' }}">
                  {{ round(($staff->completed_tasks / $staff->total_tasks) * 100) }}%
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