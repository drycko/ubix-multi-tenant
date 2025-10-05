@extends('tenant.layouts.app')

@section('title', 'Maintenance Tasks')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">Maintenance Tasks Management</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.maintenance.dashboard') }}">Maintenance</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tasks</li>
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

    <!-- Summary Stats -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                <p class="mb-0">Total Tasks</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-list-task" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                <p class="mb-0">Pending</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-clock" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ $stats['in_progress'] }}</h4>
                <p class="mb-0">In Progress</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-gear" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ $stats['overdue'] }}</h4>
                <p class="mb-0">Overdue</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Filter Tasks</h3>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('tenant.maintenance.tasks') }}">
          <div class="row">
            <div class="col-md-3">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statuses as $statusOption)
                <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>
                  {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label for="task_type" class="form-label">Task Type</label>
              <select name="task_type" id="task_type" class="form-select">
                <option value="">All Types</option>
                @foreach($taskTypes as $type)
                <option value="{{ $type }}" {{ $taskType === $type ? 'selected' : '' }}>
                  {{ ucfirst($type) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label for="assigned_to" class="form-label">Assigned To</label>
              <select name="assigned_to" id="assigned_to" class="form-select">
                <option value="">All Staff</option>
                @foreach($maintenanceStaff as $staff)
                <option value="{{ $staff->id }}" {{ $assignedTo == $staff->id ? 'selected' : '' }}>
                  {{ $staff->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <div class="d-grid">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-funnel"></i> Filter
                </button>
              </div>
            </div>
          </div>
          @if($propertyId)
          <input type="hidden" name="property_id" value="{{ $propertyId }}">
          @endif
        </form>
      </div>
    </div>

    <!-- Tasks List -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Maintenance Tasks</h3>
        <div>
          <a href="{{ route('tenant.maintenance.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Request
          </a>
          <a href="{{ route('tenant.maintenance.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Request #</th>
              <th>Room</th>
              <th>Task Type</th>
              <th>Title</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Assigned To</th>
              <th>Scheduled</th>
              <th>Progress</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($maintenanceTasks as $task)
            <tr>
              <td>
                <a href="{{ route('tenant.maintenance.show', $task->maintenanceRequest) }}" class="text-decoration-none">
                  {{ $task->maintenanceRequest->request_number ?? 'MR' . $task->maintenanceRequest->id }}
                </a>
              </td>
              <td>
                <span class="fw-semibold">
                  {{ $task->maintenanceRequest->room->number ?? 'Property-wide' }}
                </span>
                @if($task->maintenanceRequest->room)
                <br><small class="text-muted">{{ $task->maintenanceRequest->room->type->name }}</small>
                @endif
              </td>
              <td>
                <span class="badge bg-secondary">{{ ucfirst($task->task_type) }}</span>
              </td>
              <td>
                <strong>{{ $task->title }}</strong>
                @if($task->description)
                <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                @endif
              </td>
              <td>
                <span class="badge 
                  @if($task->priority === 'urgent') bg-danger
                  @elseif($task->priority === 'high') bg-warning
                  @elseif($task->priority === 'normal') bg-primary
                  @else bg-success @endif">
                  {{ ucfirst($task->priority) }}
                </span>
              </td>
              <td>
                <span class="badge 
                  @if($task->status === 'completed') bg-success
                  @elseif($task->status === 'in_progress') bg-info
                  @elseif($task->status === 'assigned') bg-primary
                  @elseif($task->status === 'on_hold') bg-dark
                  @elseif($task->status === 'cancelled') bg-danger
                  @else bg-warning @endif">
                  {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
              </td>
              <td>
                @if($task->assignedTo)
                <div class="d-flex align-items-center">
                  <div>
                    <strong>{{ $task->assignedTo->name }}</strong>
                  </div>
                </div>
                @else
                <span class="text-muted">Unassigned</span>
                @endif
              </td>
              <td>
                @if($task->scheduled_for)
                {{ $task->scheduled_for->format('M j, Y H:i') }}
                @if($task->scheduled_for->isPast() && !in_array($task->status, ['completed', 'cancelled']))
                  <br><span class="badge bg-danger">Overdue</span>
                @endif
                @else
                <span class="text-muted">Not scheduled</span>
                @endif
              </td>
              <td>
                @if($task->estimated_minutes && $task->actual_minutes)
                @php
                  $progress = min(($task->actual_minutes / $task->estimated_minutes) * 100, 100);
                @endphp
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar 
                    @if($progress >= 100) bg-success
                    @elseif($progress >= 80) bg-warning
                    @else bg-info @endif" 
                    style="width: {{ $progress }}%">
                    {{ round($progress) }}%
                  </div>
                </div>
                @elseif($task->status === 'completed')
                <span class="badge bg-success">Complete</span>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    Actions
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="{{ route('tenant.maintenance.show', $task->maintenanceRequest) }}">
                        <i class="bi bi-eye"></i> View Request
                      </a>
                    </li>
                    @can('edit maintenance requests')
                    @if($task->status !== 'completed' && $task->status !== 'cancelled')
                    <li><hr class="dropdown-divider"></li>
                    @if($task->status === 'pending' || $task->status === 'assigned')
                    <li>
                      <a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'in_progress')">
                        <i class="bi bi-play"></i> Start Task
                      </a>
                    </li>
                    @endif
                    @if($task->status === 'in_progress')
                    <li>
                      <a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'completed')">
                        <i class="bi bi-check"></i> Complete Task
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'on_hold')">
                        <i class="bi bi-pause"></i> Put on Hold
                      </a>
                    </li>
                    @endif
                    @if($task->status === 'on_hold')
                    <li>
                      <a class="dropdown-item" href="#" onclick="updateTaskStatus({{ $task->id }}, 'in_progress')">
                        <i class="bi bi-play"></i> Resume Task
                      </a>
                    </li>
                    @endif
                    <li>
                      <a class="dropdown-item text-danger" href="#" onclick="updateTaskStatus({{ $task->id }}, 'cancelled')">
                        <i class="bi bi-x"></i> Cancel Task
                      </a>
                    </li>
                    @endif
                    @endcan
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-4">
                <i class="bi bi-list-task text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No maintenance tasks found.</p>
                <small class="text-muted">Tasks are created automatically when work logs are added to maintenance requests.</small>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($maintenanceTasks->hasPages())
      <div class="card-footer">
        {{ $maintenanceTasks->appends(request()->query())->links() }}
      </div>
      @endif
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Task Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="statusUpdateForm">
        @csrf
        <div class="modal-body">
          <input type="hidden" id="taskId" name="task_id">
          <input type="hidden" id="newStatus" name="status">
          
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <span id="statusMessage"></span>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Notes (Optional)</label>
            <textarea name="notes" id="notes" class="form-control" rows="3" 
                      placeholder="Add any notes about this status change..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="confirmStatusUpdate">
            Update Status
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateTaskStatus(taskId, status) {
    document.getElementById('taskId').value = taskId;
    document.getElementById('newStatus').value = status;
    
    const statusMessages = {
        'in_progress': 'This will mark the task as "In Progress" and set the start time.',
        'completed': 'This will mark the task as "Completed" and set the completion time.',
        'on_hold': 'This will put the task on hold until further notice.',
        'cancelled': 'This will cancel the task. This action should only be used when the task is no longer needed.',
    };
    
    document.getElementById('statusMessage').textContent = statusMessages[status] || 'This will update the task status.';
    
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const taskId = document.getElementById('taskId').value;
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('notes').value;
    
    fetch(`{{ url('tenant/maintenance/tasks') }}/${taskId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update task status. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>
@endsection