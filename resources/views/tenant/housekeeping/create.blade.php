@extends('tenant.layouts.app')

@section('title', 'Create Housekeeping Task')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Create Housekeeping Task</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.housekeeping.index') }}">Housekeeping</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create Task</li>
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

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Please fix the following errors:</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Task Details</h3>
          </div>
          <form method="POST" action="{{ route('tenant.housekeeping.store') }}">
            @csrf
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="property_id" class="form-label">Property *</label>
                    <select name="property_id" id="property_id" class="form-select" required>
                      <option value="">Select Property</option>
                      @foreach($properties as $property)
                      <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                        {{ $property->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="room_id" class="form-label">Room *</label>
                    <select name="room_id" id="room_id" class="form-select" required>
                      <option value="">Select Room</option>
                      @foreach($rooms as $room)
                      <option value="{{ $room->id }}" 
                              data-property-id="{{ $room->property_id }}"
                              {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        Room {{ $room->number }} - {{ $room->type->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="task_type" class="form-label">Task Type *</label>
                    <select name="task_type" id="task_type" class="form-select" required>
                      <option value="">Select Task Type</option>
                      <option value="cleaning" {{ old('task_type') == 'cleaning' ? 'selected' : '' }}>Standard Cleaning</option>
                      <option value="maintenance" {{ old('task_type') == 'maintenance' ? 'selected' : '' }}>Maintenance Check</option>
                      <option value="inspection" {{ old('task_type') == 'inspection' ? 'selected' : '' }}>Room Inspection</option>
                      <option value="deep_clean" {{ old('task_type') == 'deep_clean' ? 'selected' : '' }}>Deep Clean</option>
                      <option value="setup" {{ old('task_type') == 'setup' ? 'selected' : '' }}>Room Setup</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="priority" class="form-label">Priority *</label>
                    <select name="priority" id="priority" class="form-select" required>
                      <option value="">Select Priority</option>
                      <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                      <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                      <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                      <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="title" class="form-label">Task Title *</label>
                <input type="text" name="title" id="title" class="form-control" 
                       value="{{ old('title') }}" required 
                       placeholder="e.g., Clean Room 101">
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" 
                          placeholder="Detailed description of the task...">{{ old('description') }}</textarea>
              </div>

              <div class="mb-3">
                <label for="instructions" class="form-label">Special Instructions</label>
                <textarea name="instructions" id="instructions" class="form-control" rows="3" 
                          placeholder="Any specific instructions for completing this task...">{{ old('instructions') }}</textarea>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="scheduled_for" class="form-label">Scheduled For *</label>
                    <input type="datetime-local" name="scheduled_for" id="scheduled_for" class="form-control" 
                           value="{{ old('scheduled_for', now()->format('Y-m-d\TH:i')) }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="estimated_minutes" class="form-label">Estimated Time (minutes)</label>
                    <input type="number" name="estimated_minutes" id="estimated_minutes" class="form-control" 
                           value="{{ old('estimated_minutes', 30) }}" min="1" max="480">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="assigned_to" class="form-label">Assign To</label>
                <select name="assigned_to" id="assigned_to" class="form-select">
                  <option value="">Leave Unassigned</option>
                  @foreach($staff as $member)
                  <option value="{{ $member->id }}" {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                    {{ $member->name }}
                  </option>
                  @endforeach
                </select>
                <div class="form-text">If assigned, the task will be immediately available to the staff member</div>
              </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.housekeeping.index') }}" class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Create Task
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <!-- What You're Creating -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clipboard-check"></i> Creating Individual Task</h3>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i>
              <strong>Individual Work Assignment:</strong> You're creating a specific task for a particular room that will be assigned to staff.
            </div>
            <div class="workflow-info">
              <h6 class="text-primary mb-2">Task Workflow:</h6>
              <ol class="small">
                <li><strong>Create Task</strong> - Define specific work for a room</li>
                <li><strong>Assign Staff</strong> - Optional immediate assignment</li>
                <li><strong>Schedule Time</strong> - Set when work should be done</li>
                <li><strong>Track Progress</strong> - Monitor completion status</li>
              </ol>
            </div>
            <hr>
            <div class="template-note">
              <h6 class="text-secondary mb-2">Need Reusable Templates?</h6>
              <p class="small text-muted mb-2">For standardized cleaning procedures, use:</p>
              <a href="{{ route('tenant.cleaning-schedule.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-list-check"></i> Cleaning Templates
              </a>
            </div>
          </div>
        </div>

        <!-- Task Type Info -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">Task Type Guide</h3>
          </div>
          <div class="card-body">
            <div class="task-type-info">
              <div class="mb-3">
                <h6 class="text-primary"><i class="bi bi-house-fill"></i> Standard Cleaning</h6>
                <p class="small text-muted">Regular room cleaning including bed making, bathroom cleaning, and vacuuming</p>
                <span class="badge bg-light text-dark small">~30-45 min</span>
              </div>
              <div class="mb-3">
                <h6 class="text-warning"><i class="bi bi-tools"></i> Maintenance Check</h6>
                <p class="small text-muted">Inspection and basic maintenance tasks for room facilities</p>
                <span class="badge bg-light text-dark small">~15-30 min</span>
              </div>
              <div class="mb-3">
                <h6 class="text-success"><i class="bi bi-search"></i> Room Inspection</h6>
                <p class="small text-muted">Quality control check after cleaning or maintenance work</p>
                <span class="badge bg-light text-dark small">~10-15 min</span>
              </div>
              <div class="mb-3">
                <h6 class="text-info"><i class="bi bi-sparkles"></i> Deep Clean</h6>
                <p class="small text-muted">Thorough cleaning including carpet cleaning, window washing, etc.</p>
                <span class="badge bg-light text-dark small">~60-90 min</span>
              </div>
              <div class="mb-3">
                <h6 class="text-secondary"><i class="bi bi-gear-fill"></i> Room Setup</h6>
                <p class="small text-muted">Special room preparation for events or VIP guests</p>
                <span class="badge bg-light text-dark small">~20-40 min</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Priority Guide -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Priority Levels</h3>
          </div>
          <div class="card-body">
            <div class="priority-guide">
              <div class="mb-3">
                <span class="badge bg-success"><i class="bi bi-calendar2"></i> Low</span>
                <p class="small text-muted mb-1">Can be done later - routine maintenance</p>
              </div>
              <div class="mb-3">
                <span class="badge bg-primary"><i class="bi bi-calendar2-check"></i> Normal</span>
                <p class="small text-muted mb-1">Standard priority - regular cleaning tasks</p>
              </div>
              <div class="mb-3">
                <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> High</span>
                <p class="small text-muted mb-1">Should be done soon - guest checkout/checkin</p>
              </div>
              <div class="mb-3">
                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Urgent</span>
                <p class="small text-muted mb-1">Needs immediate attention - emergency issues</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection
