@extends('tenant.layouts.app')

@section('title', 'Maintenance Request Details')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Maintenance Request #{{ $maintenanceRequest->id }}</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.maintenance.index') }}">Maintenance</a></li>
          <li class="breadcrumb-item active" aria-current="page">Request Details</li>
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

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-md-8">
        <!-- Request Details -->
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Request Information</h3>
            <div>
              @can('update maintenance requests')
              <a href="{{ route('tenant.maintenance.edit', $maintenanceRequest) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit
              </a>
              @endcan
              @if($maintenanceRequest->status === 'pending')
              <button type="button" class="btn btn-success btn-sm" onclick="updateStatus('in_progress')">
                <i class="bi bi-play"></i> Start Work
              </button>
              @elseif($maintenanceRequest->status === 'in_progress')
              <button type="button" class="btn btn-warning btn-sm" onclick="updateStatus('completed')">
                <i class="bi bi-check"></i> Mark Complete
              </button>
              @endif
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-sm">
                  <tr>
                    <th>Title:</th>
                    <td>{{ $maintenanceRequest->title }}</td>
                  </tr>
                  <tr>
                    <th>Property:</th>
                    <td>{{ $maintenanceRequest->property->name }}</td>
                  </tr>
                  <tr>
                    <th>Room:</th>
                    <td>
                      @if($maintenanceRequest->room)
                        Room {{ $maintenanceRequest->room->number }} - {{ $maintenanceRequest->room->type->name }}
                      @else
                        <span class="text-muted">Property-wide</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th>Category:</th>
                    <td>
                      <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $maintenanceRequest->category)) }}</span>
                    </td>
                  </tr>
                  <tr>
                    <th>Priority:</th>
                    <td>
                      <span class="badge 
                        @if($maintenanceRequest->priority === 'urgent') bg-danger
                        @elseif($maintenanceRequest->priority === 'high') bg-warning
                        @elseif($maintenanceRequest->priority === 'normal') bg-primary
                        @else bg-success @endif">
                        {{ ucfirst($maintenanceRequest->priority) }}
                      </span>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-sm">
                  <tr>
                    <th>Status:</th>
                    <td>
                      <span class="badge 
                        @if($maintenanceRequest->status === 'completed') bg-success
                        @elseif($maintenanceRequest->status === 'in_progress') bg-warning
                        @elseif($maintenanceRequest->status === 'pending') bg-info
                        @else bg-secondary @endif">
                        {{ ucfirst(str_replace('_', ' ', $maintenanceRequest->status)) }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th>Reported By:</th>
                    <td>{{ $maintenanceRequest->reportedBy->name ?? 'System' }}</td>
                  </tr>
                  <tr>
                    <th>Assigned To:</th>
                    <td>{{ $maintenanceRequest->assignedTo->name ?? 'Unassigned' }}</td>
                  </tr>
                  <tr>
                    <th>Created:</th>
                    <td>{{ $maintenanceRequest->created_at->format('M j, Y g:i A') }}</td>
                  </tr>
                  <tr>
                    <th>Due Date:</th>
                    <td>
                      @if($maintenanceRequest->due_date)
                        {{ \Carbon\Carbon::parse($maintenanceRequest->due_date)->format('M j, Y') }}
                        @if($maintenanceRequest->due_date < now() && $maintenanceRequest->status !== 'completed')
                          <span class="badge bg-danger ms-1">Overdue</span>
                        @endif
                      @else
                        <span class="text-muted">Not set</span>
                      @endif
                    </td>
                  </tr>
                </table>
              </div>
            </div>

            @if($maintenanceRequest->location)
            <div class="mt-3">
              <h6>Specific Location:</h6>
              <p class="text-muted">{{ $maintenanceRequest->location }}</p>
            </div>
            @endif

            <div class="mt-3">
              <h6>Description:</h6>
              <p>{{ $maintenanceRequest->description }}</p>
            </div>

            @if($maintenanceRequest->estimated_cost)
            <div class="mt-3">
              <h6>Estimated Cost:</h6>
              <p class="text-success fw-bold">${{ number_format($maintenanceRequest->estimated_cost, 2) }}</p>
            </div>
            @endif

            @if($maintenanceRequest->requires_contractor || $maintenanceRequest->affects_guest_experience)
            <div class="mt-3">
              <h6>Special Notes:</h6>
              <ul class="list-unstyled">
                @if($maintenanceRequest->requires_contractor)
                <li><i class="bi bi-check-circle text-warning"></i> Requires external contractor</li>
                @endif
                @if($maintenanceRequest->affects_guest_experience)
                <li><i class="bi bi-exclamation-triangle text-danger"></i> Affects guest experience</li>
                @endif
              </ul>
            </div>
            @endif
          </div>
        </div>

        <!-- Work Log -->
        <div class="card mt-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Work Log</h3>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addWorkLogModal">
              <i class="bi bi-plus"></i> Add Entry
            </button>
          </div>
          <div class="card-body">
            @forelse($maintenanceRequest->workLogs ?? [] as $log)
            <div class="border-bottom pb-2 mb-2">
              <div class="d-flex justify-content-between">
                <strong>{{ $log->user->name }}</strong>
                <small class="text-muted">{{ $log->created_at->format('M j, Y g:i A') }}</small>
              </div>
              <p class="mb-1">{{ $log->description }}</p>
              @if($log->hours_spent)
              <small class="text-info">Time spent: {{ $log->hours_spent }} hours</small>
              @endif
            </div>
            @empty
            <p class="text-muted">No work log entries yet.</p>
            @endforelse
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            @if($maintenanceRequest->status === 'pending')
            <button type="button" class="btn btn-success btn-sm w-100 mb-2" onclick="updateStatus('in_progress')">
              <i class="bi bi-play"></i> Start Work
            </button>
            @endif

            @if($maintenanceRequest->status === 'in_progress')
            <button type="button" class="btn btn-warning btn-sm w-100 mb-2" onclick="updateStatus('completed')">
              <i class="bi bi-check"></i> Mark Complete
            </button>
            @endif

            @if($maintenanceRequest->status !== 'cancelled')
            <button type="button" class="btn btn-outline-danger btn-sm w-100 mb-2" onclick="updateStatus('cancelled')">
              <i class="bi bi-x"></i> Cancel Request
            </button>
            @endif

            @can('update maintenance requests')
            <a href="{{ route('tenant.maintenance.edit', $maintenanceRequest) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
              <i class="bi bi-pencil"></i> Edit Request
            </a>
            @endcan

            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="window.print()">
              <i class="bi bi-printer"></i> Print Details
            </button>
          </div>
        </div>

        <!-- Photos -->
        @if($maintenanceRequest->photos && count($maintenanceRequest->photos) > 0)
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">Photos</h3>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($maintenanceRequest->photos as $photo)
              <div class="col-6 mb-2">
                <img src="{{ $photo['url'] }}" class="img-thumbnail w-100" 
                     data-bs-toggle="modal" data-bs-target="#photoModal" 
                     data-photo="{{ $photo['url'] }}" 
                     style="cursor: pointer;">
              </div>
              @endforeach
            </div>
          </div>
        </div>
        @endif

        <!-- Activity Timeline -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">Activity Timeline</h3>
          </div>
          <div class="card-body">
            <div class="timeline">
              <div class="timeline-item">
                <div class="timeline-marker bg-primary"></div>
                <div class="timeline-content">
                  <h6>Request Created</h6>
                  <p class="small text-muted">{{ $maintenanceRequest->created_at->format('M j, Y g:i A') }}</p>
                </div>
              </div>
              @if($maintenanceRequest->assigned_to)
              <div class="timeline-item">
                <div class="timeline-marker bg-info"></div>
                <div class="timeline-content">
                  <h6>Assigned to {{ $maintenanceRequest->assignedTo->name }}</h6>
                  <p class="small text-muted">{{ $maintenanceRequest->updated_at->format('M j, Y g:i A') }}</p>
                </div>
              </div>
              @endif
              @if($maintenanceRequest->status === 'completed')
              <div class="timeline-item">
                <div class="timeline-marker bg-success"></div>
                <div class="timeline-content">
                  <h6>Completed</h6>
                  <p class="small text-muted">{{ $maintenanceRequest->completed_at ? $maintenanceRequest->completed_at->format('M j, Y g:i A') : 'Recently' }}</p>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Work Log Modal -->
<div class="modal fade" id="addWorkLogModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Work Log Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('tenant.maintenance.add-work-log', $maintenanceRequest) }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="description" class="form-label">Description *</label>
            <textarea name="description" id="description" class="form-control" rows="3" required 
                      placeholder="Describe the work performed..."></textarea>
          </div>
          <div class="mb-3">
            <label for="hours_spent" class="form-label">Hours Spent</label>
            <input type="number" name="hours_spent" id="hours_spent" class="form-control" 
                   min="0" step="0.1" placeholder="0.1">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Entry</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalPhoto" src="" class="img-fluid">
      </div>
    </div>
  </div>
</div>

<script>
function updateStatus(status) {
    if (confirm(`Are you sure you want to change the status to "${status.replace('_', ' ')}"?`)) {
        fetch(`{{ route('tenant.maintenance.update-status', $maintenanceRequest) }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endsection
