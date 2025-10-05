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
        <h3 class="mb-0 text-muted">Maintenance Request #{{ $maintenanceRequest->id }}</h3>
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

    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="row">
      <div class="col-md-8">
        <!-- Request Details -->
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Request Information</h3>
            <div>
              @can('edit maintenance requests')
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

            <div class="row">
              <div class="col-md-6">
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
              </div>
              <div class="col-md-6">

                @if($maintenanceRequest->estimated_cost)
                <div class="mt-3">
                  <h6>Estimated Cost:</h6>
                  <p class="text-success fw-bold">{{ number_format($maintenanceRequest->estimated_cost, 2) }}</p>
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
            @php
            $workLogs = collect();
            foreach($maintenanceRequest->maintenanceTasks ?? [] as $task) {
                foreach($task->staffHours ?? [] as $hour) {
                  // we need to have actual and estimate costs to show the entry
                
                    $workLogs->push($hour);
                }
            }
            $workLogs = $workLogs->sortByDesc('created_at');
            @endphp

            @forelse($workLogs as $log)
            <div class="border-bottom pb-3 mb-3">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between">
                    <strong class="text-primary">{{ $log->user->name }}</strong>
                    <small class="text-muted">{{ $log->work_date }} {{ $log->start_time ? $log->start_time . ' - ' . $log->end_time : '' }}</small>
                  </div>
                  <p class="mb-2 mt-1">{{ $log->description }}</p>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <small class="text-info">
                        <i class="bi bi-clock"></i> {{ $log->hours_worked }} hours
                        @if($log->is_overtime)
                          <span class="badge bg-warning text-dark ms-1">OT</span>
                        @endif
                      </small>
                    </div>
                    <div class="col-md-6">
                      @if($log->total_amount)
                      <small class="text-success">
                        <i class="bi bi-currency-dollar"></i> ${{ number_format($log->total_amount, 2) }} (Labor)
                      </small>
                      @endif
                      @if($log->task && $log->task->actual_cost)
                      <br><small class="text-warning">
                        <i class="bi bi-tools"></i> ${{ number_format($log->task->actual_cost, 2) }} (Materials)
                      </small>
                      @endif
                    </div>
                  </div>

                  @if($log->notes)
                  <div class="mt-2">
                    <small class="text-muted">
                      <i class="bi bi-sticky"></i> {{ $log->notes }}
                    </small>
                  </div>
                  @endif

                  <div class="mt-2">
                    <span class="badge {{ $log->is_approved ? 'bg-success' : 'bg-warning' }}">
                      {{ $log->is_approved ? 'Approved' : 'Pending Approval' }}
                    </span>
                    @if($log->task && $log->task->task_type)
                    <span class="badge bg-secondary ms-1">{{ ucfirst($log->task->task_type) }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @empty
            <div class="text-center py-4">
              <i class="bi bi-clipboard-data text-muted" style="font-size: 2rem;"></i>
              <p class="text-muted mt-2 mb-0">No work log entries yet.</p>
              <small class="text-muted">Click "Add Entry" to start tracking work sessions.</small>
            </div>
            @endforelse

            @if($workLogs->count() > 0)
            <div class="mt-3 pt-3 border-top">
              @php
                $totalLaborCost = $workLogs->sum('total_amount');
                $totalMaterialsCost = $maintenanceRequest->maintenanceTasks->sum('actual_cost');
                $totalCost = $totalLaborCost + $totalMaterialsCost;
              @endphp
              <div class="row text-center">
                <div class="col-3">
                  <div class="h6 mb-0 text-primary">{{ $workLogs->sum('hours_worked') }}</div>
                  <small class="text-muted">Total Hours</small>
                </div>
                <div class="col-3">
                  <div class="h6 mb-0 text-success">${{ number_format($totalLaborCost, 2) }}</div>
                  <small class="text-muted">Labor Cost</small>
                </div>
                <div class="col-3">
                  <div class="h6 mb-0 text-warning">${{ number_format($totalMaterialsCost, 2) }}</div>
                  <small class="text-muted">Materials</small>
                </div>
                <div class="col-3">
                  <div class="h6 mb-0 text-info">{{ $workLogs->where('is_approved', false)->count() }}</div>
                  <small class="text-muted">Pending</small>
                </div>
              </div>
              @if($totalCost > 0)
              <div class="row text-center mt-2 pt-2 border-top">
                <div class="col-12">
                  <div class="h5 mb-0 text-dark">${{ number_format($totalCost, 2) }}</div>
                  <small class="text-muted">Total Project Cost</small>
                </div>
              </div>
              @endif
            </div>
            @endif
          </div>
        </div>

        <!-- Maintenance Tasks -->
        <div class="card mt-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Maintenance Tasks</h3>
            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addTaskModal">
              <i class="bi bi-plus"></i> Add Task
            </button>
          </div>
          <div class="card-body">
            @forelse($maintenanceRequest->maintenanceTasks as $task)
            <div class="border rounded p-3 mb-3 {{ $task->status === 'completed' ? 'bg-light' : '' }}">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">{{ $task->title }}</h6>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item" href="#" onclick="editTask({{ $task->id }})">
                            <i class="bi bi-pencil"></i> Edit Task
                          </a>
                        </li>
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
                            <i class="bi bi-check"></i> Complete
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
                            <i class="bi bi-play"></i> Resume
                          </a>
                        </li>
                        @endif
                        @endif
                      </ul>
                    </div>
                  </div>
                  
                  @if($task->description)
                  <p class="text-muted mb-2">{{ $task->description }}</p>
                  @endif
                  
                  <div class="row">
                    <div class="col-md-6">
                      <small>
                        <strong>Type:</strong> 
                        <span class="badge bg-secondary">{{ ucfirst($task->task_type) }}</span>
                      </small><br>
                      <small>
                        <strong>Priority:</strong> 
                        <span class="badge bg-{{ $task->priority_color }}">{{ ucfirst($task->priority) }}</span>
                      </small><br>
                      <small>
                        <strong>Status:</strong> 
                        <span class="badge bg-{{ $task->status_color }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                      </small>
                    </div>
                    <div class="col-md-6">
                      <small>
                        <strong>Assigned to:</strong> {{ $task->assignedTo->name ?? 'Unassigned' }}
                      </small><br>
                      @if($task->scheduled_for)
                      <small>
                        <strong>Scheduled:</strong> {{ $task->scheduled_for->format('M j, Y H:i') }}
                      </small><br>
                      @endif
                      @if($task->estimated_minutes)
                      <small>
                        <strong>Est. Time:</strong> {{ $task->estimated_minutes }} minutes
                      </small>
                      @endif
                    </div>
                  </div>
                  
                  @if($task->staffHours->count() > 0)
                  <div class="mt-2 pt-2 border-top">
                    <small class="text-success">
                      <i class="bi bi-clock"></i> {{ $task->staffHours->sum('hours_worked') }} hours logged
                    </small>
                    @if($task->actual_cost)
                    <small class="text-warning ms-3">
                      <i class="bi bi-currency-dollar"></i> ${{ number_format($task->actual_cost, 2) }} materials cost
                    </small>
                    @endif
                  </div>
                  @endif
                </div>
              </div>
            </div>
            @empty
            <div class="text-center py-4">
              <i class="bi bi-list-task text-muted" style="font-size: 2rem;"></i>
              <p class="text-muted mt-2 mb-0">No tasks created yet.</p>
              <small class="text-muted">Tasks are created automatically when adding work logs, or you can create them manually.</small>
            </div>
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

            @can('edit maintenance requests')
            <a href="{{ route('tenant.maintenance.edit', $maintenanceRequest) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
              <i class="bi bi-pencil"></i> Edit Request
            </a>
            @endcan

            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="printMaintenanceReport({{ $maintenanceRequest->id }})">
              <i class="bi bi-printer"></i> Print Report
            </button>
          </div>
        </div>

        <!-- Photos -->
        @if($maintenanceRequest->images && count($maintenanceRequest->images) > 0)
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">Photos</h3>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($maintenanceRequest->images as $photoUrl)
              <div class="col-6 mb-2">
                @php
                if (config('app.env') === 'production') {
                  $gcsConfig = config('filesystems.disks.gcs');
                  $bucket = $gcsConfig['bucket'] ?? null;
                  $path = ltrim($photoUrl, '/');
                  $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
                } else {
                  // For local storage in multi-tenant setup
                  $imageUrl = asset('storage/' . $photoUrl);
                }
                @endphp
                <img src="{{ $imageUrl }}" class="img-thumbnail w-100" 
                     data-bs-toggle="modal" data-bs-target="#photoModal" 
                     data-photo="{{ $imageUrl }}" onClick="document.getElementById('modalPhoto').src='{{ $imageUrl }}'"
                     style="cursor: pointer;">
              </div>
              @endforeach
            </div>
          </div>
        </div>
        @endif
{{-- 
        @if($roomPackage->pkg_image)
              @php
              if (config('app.env') === 'production') {
                $gcsConfig = config('filesystems.disks.gcs');
                $bucket = $gcsConfig['bucket'] ?? null;
                $path = ltrim($roomPackage->pkg_image, '/');
                $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
              } else {
                // For local storage in multi-tenant setup
                $imageUrl = asset('storage/' . $roomPackage->pkg_image);
              }
              @endphp
              @if($imageUrl)
                <div class="text-center mb-4">
                  <img src="{{ $imageUrl }}" alt="{{ $roomPackage->pkg_name }}" 
                       class="img-fluid rounded" style="max-height: 300px;">
                </div>
              @endif
            @endif --}}

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
      <form method="POST" action="{{ route('tenant.maintenance.add-work-log', $maintenanceRequest) }}" id="workLogForm">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="description" class="form-label">Work Description <span class="text-danger">*</span></label>
            <textarea name="description" id="description" class="form-control" rows="3" required 
                      placeholder="Describe the work performed..." maxlength="1000"></textarea>
            <div class="form-text">Describe what work was performed during this session.</div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="work_date" class="form-label">Work Date</label>
                <input type="date" name="work_date" id="work_date" class="form-control" 
                       value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="task_type" class="form-label">Task Type</label>
                <select name="task_type" id="task_type" class="form-select">
                  <option value="repair">Repair</option>
                  <option value="diagnosis">Diagnosis</option>
                  <option value="replacement">Replacement</option>
                  <option value="testing">Testing</option>
                  <option value="cleanup">Cleanup</option>
                  <option value="documentation">Documentation</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Time Tracking Method -->
          <div class="mb-3">
            <label class="form-label">Time Tracking Method</label>
            <div class="btn-group w-100" role="group" aria-label="Time tracking method">
              <input type="radio" class="btn-check" name="time_method" id="method_hours" value="hours" checked>
              <label class="btn btn-outline-primary" for="method_hours">Total Hours</label>
              
              <input type="radio" class="btn-check" name="time_method" id="method_times" value="times">
              <label class="btn btn-outline-primary" for="method_times">Start/End Times</label>
            </div>
          </div>

          <!-- Hours Method -->
          <div class="mb-3" id="hours_method">
            <label for="hours_spent" class="form-label">Hours Worked</label>
            <input type="number" name="hours_spent" id="hours_spent" class="form-control" 
                   min="0.1" max="24" step="0.1" placeholder="0.5">
            <div class="form-text">Enter total hours worked (e.g., 2.5 for 2 hours 30 minutes)</div>
          </div>

          <!-- Start/End Times Method -->
          <div class="row" id="times_method" style="display: none;">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" name="start_time" id="start_time" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" name="end_time" id="end_time" class="form-control">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="materials_used" class="form-label">Materials/Parts Used</label>
                <input type="text" name="materials_used" id="materials_used" class="form-control" 
                       placeholder="e.g., 2x screws, 1x switch">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="cost" class="form-label">Cost Incurred</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" name="cost" id="cost" class="form-control" 
                         min="0" step="0.01" placeholder="0.00">
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="2" 
                      placeholder="Any additional observations or notes..." maxlength="500"></textarea>
          </div>

          <div class="form-check">
            <input type="checkbox" name="is_complete" id="is_complete" class="form-check-input">
            <label class="form-check-label" for="is_complete">
              Mark this task as complete
            </label>
            <div class="form-text">Check this if this work session completes the maintenance task.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Work Log
          </button>
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

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Maintenance Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="addTaskForm">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="task_title" class="form-label">Task Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="task_title" class="form-control" required 
                   placeholder="Brief description of the task">
          </div>

          <div class="mb-3">
            <label for="task_description" class="form-label">Description</label>
            <textarea name="description" id="task_description" class="form-control" rows="3"
                      placeholder="Detailed description of what needs to be done"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="task_type_add" class="form-label">Task Type <span class="text-danger">*</span></label>
                <select name="task_type" id="task_type_add" class="form-select" required>
                  <option value="diagnosis">Diagnosis</option>
                  <option value="repair">Repair</option>
                  <option value="replacement">Replacement</option>
                  <option value="testing">Testing</option>
                  <option value="cleanup">Cleanup</option>
                  <option value="documentation">Documentation</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="task_priority" class="form-label">Priority</label>
                <select name="priority" id="task_priority" class="form-select">
                  <option value="low">Low</option>
                  <option value="normal" selected>Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="task_assigned_to" class="form-label">Assign To</label>
                <select name="assigned_to" id="task_assigned_to" class="form-select">
                  <option value="">Unassigned</option>
                  @foreach($maintenanceStaff ?? [] as $staff)
                  <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="task_estimated_minutes" class="form-label">Estimated Time (minutes)</label>
                <input type="number" name="estimated_minutes" id="task_estimated_minutes" class="form-control" 
                       min="1" placeholder="60">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="task_scheduled_for" class="form-label">Schedule For</label>
            <input type="datetime-local" name="scheduled_for" id="task_scheduled_for" class="form-control">
          </div>

          <div class="mb-3">
            <label for="task_instructions" class="form-label">Instructions</label>
            <textarea name="instructions" id="task_instructions" class="form-control" rows="2"
                      placeholder="Specific instructions for completing this task"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create Task
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Maintenance Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editTaskForm">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_task_id" name="task_id">
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_task_title" class="form-label">Task Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="edit_task_title" class="form-control" required 
                   placeholder="Brief description of the task">
          </div>

          <div class="mb-3">
            <label for="edit_task_description" class="form-label">Description</label>
            <textarea name="description" id="edit_task_description" class="form-control" rows="3"
                      placeholder="Detailed description of what needs to be done"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_task_type" class="form-label">Task Type <span class="text-danger">*</span></label>
                <select name="task_type" id="edit_task_type" class="form-select" required>
                  <option value="diagnosis">Diagnosis</option>
                  <option value="repair">Repair</option>
                  <option value="replacement">Replacement</option>
                  <option value="testing">Testing</option>
                  <option value="cleanup">Cleanup</option>
                  <option value="documentation">Documentation</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_task_priority" class="form-label">Priority</label>
                <select name="priority" id="edit_task_priority" class="form-select">
                  <option value="low">Low</option>
                  <option value="normal">Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_task_assigned_to" class="form-label">Assign To</label>
                <select name="assigned_to" id="edit_task_assigned_to" class="form-select">
                  <option value="">Unassigned</option>
                  @foreach($maintenanceStaff ?? [] as $staff)
                  <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="edit_task_estimated_minutes" class="form-label">Estimated Time (minutes)</label>
                <input type="number" name="estimated_minutes" id="edit_task_estimated_minutes" class="form-control" 
                       min="1" placeholder="60">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="edit_task_scheduled_for" class="form-label">Schedule For</label>
            <input type="datetime-local" name="scheduled_for" id="edit_task_scheduled_for" class="form-control">
          </div>

          <div class="mb-3">
            <label for="edit_task_instructions" class="form-label">Instructions</label>
            <textarea name="instructions" id="edit_task_instructions" class="form-control" rows="2"
                      placeholder="Specific instructions for completing this task"></textarea>
          </div>

          <div class="mb-3">
            <label for="edit_task_status" class="form-label">Status</label>
            <select name="status" id="edit_task_status" class="form-select">
              <option value="pending">Pending</option>
              <option value="assigned">Assigned</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="on_hold">On Hold</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Update Task
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>

// Work Log Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const timeMethodRadios = document.querySelectorAll('input[name="time_method"]');
    const hoursMethod = document.getElementById('hours_method');
    const timesMethod = document.getElementById('times_method');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const hoursSpentInput = document.getElementById('hours_spent');

    // Toggle between time tracking methods
    timeMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'hours') {
                hoursMethod.style.display = 'block';
                timesMethod.style.display = 'none';
                // Clear start/end times
                startTimeInput.value = '';
                endTimeInput.value = '';
            } else {
                hoursMethod.style.display = 'none';
                timesMethod.style.display = 'flex';
                // Clear hours
                hoursSpentInput.value = '';
                // Set default start time to now
                const now = new Date();
                startTimeInput.value = now.toTimeString().slice(0, 5);
            }
        });
    });

    // Auto-calculate hours when start/end times change
    function calculateHours() {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        
        if (startTime && endTime) {
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);
            
            if (end > start) {
                const diffMs = end - start;
                const diffHours = diffMs / (1000 * 60 * 60);
                hoursSpentInput.value = diffHours.toFixed(1);
            }
        }
    }

    startTimeInput.addEventListener('change', calculateHours);
    endTimeInput.addEventListener('change', calculateHours);

    // Form validation
    document.getElementById('workLogForm').addEventListener('submit', function(e) {
        const timeMethod = document.querySelector('input[name="time_method"]:checked').value;
        const hoursSpent = hoursSpentInput.value;
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        if (timeMethod === 'hours') {
            if (!hoursSpent || parseFloat(hoursSpent) <= 0) {
                e.preventDefault();
                alert('Please enter a valid number of hours worked.');
                hoursSpentInput.focus();
                return false;
            }
        } else {
            if (!startTime || !endTime) {
                e.preventDefault();
                alert('Please enter both start and end times.');
                return false;
            }
            
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);
            
            if (end <= start) {
                e.preventDefault();
                alert('End time must be after start time.');
                endTimeInput.focus();
                return false;
            }

            // Calculate and set hours_spent for submission
            const diffMs = end - start;
            const diffHours = diffMs / (1000 * 60 * 60);
            hoursSpentInput.value = diffHours.toFixed(2);
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Adding...';
        submitBtn.disabled = true;

        // Re-enable button after 5 seconds as fallback
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    });

    // Reset form when modal is hidden
    document.getElementById('addWorkLogModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('workLogForm').reset();
        hoursMethod.style.display = 'block';
        timesMethod.style.display = 'none';
        document.getElementById('method_hours').checked = true;
    });
});

// Task Management Functions
function printMaintenanceReport(requestId) {
    // Open print-optimized view in new window
    const printUrl = `{{ route('tenant.maintenance.print', ['maintenance' => '__ID__']) }}`.replace('__ID__', requestId);
    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    
    // Auto-print when window loads
    if (printWindow) {
        printWindow.addEventListener('load', function() {
            setTimeout(function() {
                printWindow.print();
            }, 500);
        });
    }
}

function updateTaskStatus(taskId, status) {
    if (confirm(`Are you sure you want to change the task status to "${status.replace('_', ' ')}"?`)) {
        $.post(`{{ url('maintenance/tasks') }}/${taskId}/update-status`, {
            status: status,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update task status. Please try again.');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Task status update failed:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            alert('An error occurred. Please try again.');
        });
    }
}

function updateStatus(status) {
    if (confirm(`Are you sure you want to change the status to "${status.replace('_', ' ')}"?`)) {
        $.post(`{{ route('tenant.maintenance.update-status', $maintenanceRequest) }}`, {
            status: status,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update status. Please try again.');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Status update failed:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            alert('An error occurred. Please try again.');
        });
    }
}

function editTask(taskId) {
    // Get task details using jQuery with .done/.fail pattern
    $.get(`{{ url('maintenance/tasks') }}/${taskId}`)
    .done(function(task) {
        // Populate edit form using vanilla JS
        document.getElementById('edit_task_id').value = task.id;
        document.getElementById('edit_task_title').value = task.title || '';
        document.getElementById('edit_task_description').value = task.description || '';
        document.getElementById('edit_task_type').value = task.task_type || '';
        document.getElementById('edit_task_priority').value = task.priority || '';
        document.getElementById('edit_task_assigned_to').value = task.assigned_to || '';
        document.getElementById('edit_task_estimated_minutes').value = task.estimated_minutes || '';
        document.getElementById('edit_task_instructions').value = task.instructions || '';
        document.getElementById('edit_task_status').value = task.status || '';

        // Format datetime for input
        if (task.scheduled_for) {
            const date = new Date(task.scheduled_for);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            document.getElementById('edit_task_scheduled_for').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        } else {
            document.getElementById('edit_task_scheduled_for').value = '';
        }
        
        // Show modal using vanilla JS
        document.getElementById('editTaskModal').style.display = 'block';
        var editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        editTaskModal.show();
    })
    .fail(function(xhr, status, error) {
        console.error('Failed to load task details:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            error: error
        });
        alert('Failed to load task details. Please try again.');
    });
}

// Add Task Form Handler (use vanilla JS to serialize form data)
document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('maintenance_request_id', '{{ $maintenanceRequest->id }}');

    $.post(`{{ url('maintenance/tasks') }}`, formData)
    .done(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to create task. Please try again.');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Task creation failed:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            error: error
        });
        alert('An error occurred. Please try again.');
    });
});

// Edit Task Form Handler (using jQuery to serialize form data)
// $('#editTaskForm').on('submit', function(e) {
document.getElementById('editTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const taskId = $('#edit_task_id').val();
    const formData = $(this).serialize();
    
    $.post(`{{ url('maintenance/tasks') }}/${taskId}`, formData)
    .done(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update task. Please try again.');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Task update failed:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            error: error
        });
        alert('An error occurred. Please try again.');
    });
});
</script>
@endsection
