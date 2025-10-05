@extends('tenant.layouts.app')

@section('title', 'Edit Maintenance Request')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">Edit Maintenance Request #{{ $maintenanceRequest->id }}</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.maintenance.index') }}">Maintenance</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.maintenance.show', $maintenanceRequest) }}">Request Details</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Edit Maintenance Request</h3>
          </div>
          <form action="{{ route('tenant.maintenance.update', $maintenanceRequest) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
              <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
                    <select name="property_id" id="property_id" class="form-select" required>
                      <option value="">Select Property</option>
                      @foreach($properties as $property)
                      <option value="{{ $property->id }}" {{ old('property_id', $maintenanceRequest->property_id) == $property->id ? 'selected' : '' }}>
                        {{ $property->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="room_id" class="form-label">Room</label>
                    <select name="room_id" id="room_id" class="form-select">
                      <option value="">Select Room (Optional)</option>
                      @foreach($rooms as $room)
                      <option value="{{ $room->id }}" data-property="{{ $room->property_id }}" {{ old('room_id', $maintenanceRequest->room_id) == $room->id ? 'selected' : '' }}>
                        Room {{ $room->number }} - {{ $room->type->name }} ({{ $room->property->name }})
                      </option>
                      @endforeach
                    </select>
                    <div class="form-text">Leave empty for property-wide issues</div>
                  </div>

                  <div class="mb-3">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select" required>
                      <option value="">Select Category</option>
                      @foreach(\App\Models\Tenant\MaintenanceRequest::SUPPORTED_CATEGORIES as $category)
                      <option value="{{ $category }}" {{ old('category', $maintenanceRequest->category) === $category ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $category)) }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                    <select name="priority" id="priority" class="form-select" required>
                      @foreach(\App\Models\Tenant\MaintenanceRequest::PRIORITY_OPTIONS as $priority)
                      <option value="{{ $priority }}" {{ old('priority', $maintenanceRequest->priority) === $priority ? 'selected' : '' }}>
                        {{ ucfirst($priority) }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                      @foreach(\App\Models\Tenant\MaintenanceRequest::STATUS_OPTIONS as $status)
                      <option value="{{ $status }}" {{ old('status', $maintenanceRequest->status) === $status ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" 
                           value="{{ old('title', $maintenanceRequest->title) }}" required maxlength="255"
                           placeholder="Brief description of the issue">
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control" rows="4" required
                              placeholder="Detailed description of the maintenance issue">{{ old('description', $maintenanceRequest->description) }}</textarea>
                  </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="reported_by" class="form-label">Reported By</label>
                    <select name="reported_by" id="reported_by" class="form-select">
                      @foreach($allUsers as $user)
                      <option value="{{ $user->id }}" {{ old('reported_by', $maintenanceRequest->reported_by) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="assigned_to" class="form-label">Assign To</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                      <option value="">Leave Unassigned</option>
                      @foreach($maintenanceStaff as $staff)
                      <option value="{{ $staff->id }}" {{ old('assigned_to', $maintenanceRequest->assigned_to) == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="location_details" class="form-label">Specific Location</label>
                    <input type="text" name="location_details" id="location_details" class="form-control" 
                           value="{{ old('location_details', $maintenanceRequest->location_details) }}" maxlength="500"
                           placeholder="e.g., Bathroom sink, Near window, etc.">
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="estimated_cost" class="form-label">Estimated Cost</label>
                        <div class="input-group">
                          <span class="input-group-text">$</span>
                          <input type="number" name="estimated_cost" id="estimated_cost" class="form-control" 
                                 value="{{ old('estimated_cost', $maintenanceRequest->estimated_cost) }}" 
                                 min="0" step="0.01" placeholder="0.00">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="actual_cost" class="form-label">Actual Cost</label>
                        <div class="input-group">
                          <span class="input-group-text">$</span>
                          <input type="number" name="actual_cost" id="actual_cost" class="form-control" 
                                 value="{{ old('actual_cost', $maintenanceRequest->actual_cost) }}" 
                                 min="0" step="0.01" placeholder="0.00">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="scheduled_for" class="form-label">Scheduled For</label>
                    <input type="datetime-local" name="scheduled_for" id="scheduled_for" class="form-control"
                           value="{{ old('scheduled_for', $maintenanceRequest->scheduled_for ? $maintenanceRequest->scheduled_for->format('Y-m-d\TH:i') : '') }}">
                  </div>

                  <div class="mb-3">
                    <div class="form-check">
                      <input type="checkbox" name="requires_room_closure" id="requires_room_closure" class="form-check-input"
                             {{ old('requires_room_closure', $maintenanceRequest->requires_room_closure) ? 'checked' : '' }}>
                      <label class="form-check-label" for="requires_room_closure">
                        Requires Room Closure
                      </label>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="resolution_notes" class="form-label">Resolution Notes</label>
                    <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="3"
                              placeholder="Notes about the resolution or work performed">{{ old('resolution_notes', $maintenanceRequest->resolution_notes) }}</textarea>
                  </div>

                  <div class="mb-3">
                    <label for="parts_used" class="form-label">Parts/Materials Used</label>
                    <textarea name="parts_used" id="parts_used" class="form-control" rows="2"
                              placeholder="List of parts or materials used">{{ old('parts_used', $maintenanceRequest->parts_used) }}</textarea>
                  </div>

                  <!-- Timestamp Display Fields (Read-only) -->
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Assigned At</label>
                        <input type="text" class="form-control" 
                               value="{{ $maintenanceRequest->assigned_at ? $maintenanceRequest->assigned_at->format('M d, Y g:i A') : 'Not assigned' }}" 
                               readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Started At</label>
                        <input type="text" class="form-control" 
                               value="{{ $maintenanceRequest->started_at ? $maintenanceRequest->started_at->format('M d, Y g:i A') : 'Not started' }}" 
                               readonly>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Completed At</label>
                        <input type="text" class="form-control" 
                               value="{{ $maintenanceRequest->completed_at ? $maintenanceRequest->completed_at->format('M d, Y g:i A') : 'Not completed' }}" 
                               readonly>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Request Number</label>
                        <input type="text" class="form-control" 
                               value="{{ $maintenanceRequest->request_number }}" 
                               readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Photo Upload -->
              <div class="row">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label for="photos" class="form-label">Additional Photos</label>
                    <input type="file" name="photos[]" id="photos" class="form-control" multiple accept="image/*">
                    <div class="form-text">
                      You can upload multiple photos. Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 2MB per file.
                    </div>
                  </div>

                  @if($maintenanceRequest->images && count($maintenanceRequest->images) > 0)
                  <div class="mb-3">
                    <label class="form-label">Current Photos</label>
                    <div class="row">
                      @foreach($maintenanceRequest->images as $index => $photoUrl)
                      <div class="col-md-2 mb-2">
                        @php
                        if (config('app.env') === 'production') {
                          $gcsConfig = config('filesystems.disks.gcs');
                          $bucket = $gcsConfig['bucket'] ?? null;
                          $path = ltrim($photoUrl, '/');
                          $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
                        } else {
                          $imageUrl = asset('storage/' . $photoUrl);
                        }
                        @endphp
                        <img src="{{ $imageUrl }}" class="img-thumbnail w-100" style="height: 100px; object-fit: cover;">
                      </div>
                      @endforeach
                    </div>
                  </div>
                  @endif
                </div>
              </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.maintenance.index') }}" class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Request
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Current Request Status -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-info-circle"></i> Request Status</h3>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <strong>Request #:</strong> {{ $maintenanceRequest->request_number }}<br>
              <strong>Current Status:</strong> 
              <span class="badge bg-{{ $maintenanceRequest->status_color }}">
                {{ ucfirst(str_replace('_', ' ', $maintenanceRequest->status)) }}
              </span><br>
              <strong>Priority:</strong> 
              <span class="badge bg-{{ $maintenanceRequest->priority_color }}">
                {{ ucfirst($maintenanceRequest->priority) }}
              </span><br>
              <strong>Created:</strong> {{ $maintenanceRequest->created_at->format('M d, Y g:i A') }}<br>
              @if($maintenanceRequest->assigned_at)
              <strong>Assigned:</strong> {{ $maintenanceRequest->assigned_at->format('M d, Y g:i A') }}<br>
              @endif
              @if($maintenanceRequest->started_at)
              <strong>Started:</strong> {{ $maintenanceRequest->started_at->format('M d, Y g:i A') }}<br>
              @endif
              @if($maintenanceRequest->completed_at)
              <strong>Completed:</strong> {{ $maintenanceRequest->completed_at->format('M d, Y g:i A') }}<br>
              @endif
            </div>
            
            @if($maintenanceRequest->isOverdue())
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle"></i>
              <strong>Overdue!</strong> This request is past its scheduled date.
            </div>
            @endif
            
            @if($maintenanceRequest->requires_room_closure)
            <div class="alert alert-warning">
              <i class="bi bi-door-closed"></i>
              <strong>Room Closure Required</strong> This maintenance requires the room to be closed.
            </div>
            @endif
          </div>
        </div>

        <!-- What You're Creating -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-tools"></i> Updating Maintenance Request</h3>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i>
              <strong>Maintenance Workflow:</strong> You're updating a formal request that will be tracked, assigned, and resolved through our maintenance system.
            </div>
            <div class="workflow-steps">
              <h6 class="text-primary mb-2">Request Lifecycle:</h6>
              <ol class="small">
                <li><strong>Submit</strong> - Request enters the system</li>
                <li><strong>Review</strong> - Management evaluates priority</li>
                <li><strong>Assign</strong> - Task given to appropriate staff</li>
                <li><strong>Work</strong> - Issue is investigated and fixed</li>
                <li><strong>Complete</strong> - Request marked as resolved</li>
              </ol>
            </div>
            <hr>
            <div class="tips-section">
              <h6 class="text-success mb-2"><i class="bi bi-lightbulb"></i> Pro Tips:</h6>
              <ul class="small text-muted mb-0">
                <li>Include photos for faster diagnosis</li>
                <li>Be specific about when issues occur</li>
                <li>Note any guest impact for priority</li>
                <li>Mention safety concerns immediately</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Priority Guide -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-exclamation-triangle"></i> Priority Guidelines</h3>
          </div>
          <div class="card-body">
            <div class="priority-guide">
              <div class="mb-3">
                <div class="d-flex align-items-center mb-1">
                  <span class="badge bg-danger me-2"><i class="bi bi-exclamation-triangle-fill"></i> Urgent</span>
                  <small class="text-muted">Response: Immediate</small>
                </div>
                <p class="small text-muted mb-1">Safety hazards, flooding, gas leaks, no heat/AC in extreme weather, power outages affecting operations</p>
                <div class="small text-danger"><strong>Examples:</strong> Burst pipes, electrical sparks, broken locks</div>
              </div>
              <div class="mb-3">
                <div class="d-flex align-items-center mb-1">
                  <span class="badge bg-warning me-2"><i class="bi bi-exclamation-circle"></i> High</span>
                  <small class="text-muted">Response: Same day</small>
                </div>
                <p class="small text-muted mb-1">Guest complaints, broken appliances affecting stay, leaks, heating/cooling issues</p>
                <div class="small text-warning"><strong>Examples:</strong> No hot water, broken refrigerator, AC not working</div>
              </div>
              <div class="mb-3">
                <div class="d-flex align-items-center mb-1">
                  <span class="badge bg-primary me-2"><i class="bi bi-calendar-check"></i> Normal</span>
                  <small class="text-muted">Response: 1-3 days</small>
                </div>
                <p class="small text-muted mb-1">Routine maintenance, minor repairs, scheduled replacements</p>
                <div class="small text-primary"><strong>Examples:</strong> Filter changes, minor paint touch-ups, loose handles</div>
              </div>
              <div class="mb-3">
                <div class="d-flex align-items-center mb-1">
                  <span class="badge bg-success me-2"><i class="bi bi-calendar2"></i> Low</span>
                  <small class="text-muted">Response: 1-2 weeks</small>
                </div>
                <p class="small text-muted mb-1">Cosmetic issues, non-urgent upgrades, preventive maintenance</p>
                <div class="small text-success"><strong>Examples:</strong> Scuff marks, decoration updates, deep cleaning</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Guide -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-grid-3x3-gap"></i> Category Guide</h3>
          </div>
          <div class="card-body">
            <div class="category-examples">
              <div class="mb-3">
                <h6 class="text-primary"><i class="bi bi-droplet"></i> Plumbing</h6>
                <div class="small text-muted">
                  <strong>Common:</strong> Leaks, clogs, low pressure, running toilets<br>
                  <strong>Urgent:</strong> Burst pipes, sewage backup, no water
                </div>
              </div>
              <div class="mb-3">
                <h6 class="text-warning"><i class="bi bi-lightning"></i> Electrical</h6>
                <div class="small text-muted">
                  <strong>Common:</strong> Outlets, lighting, switches, bulbs<br>
                  <strong>Urgent:</strong> Sparks, burning smell, no power
                </div>
              </div>
              <div class="mb-3">
                <h6 class="text-info"><i class="bi bi-thermometer"></i> HVAC</h6>
                <div class="small text-muted">
                  <strong>Common:</strong> Temperature control, filters, vents<br>
                  <strong>Urgent:</strong> No heating in winter, no AC in summer
                </div>
              </div>
              <div class="mb-3">
                <h6 class="text-secondary"><i class="bi bi-tv"></i> Appliances</h6>
                <div class="small text-muted">
                  <strong>Common:</strong> Refrigerator, TV, microwave, dishwasher<br>
                  <strong>High:</strong> Major appliances affecting guest stay
                </div>
              </div>
              <div class="mb-3">
                <h6 class="text-dark"><i class="bi bi-shield-lock"></i> Security</h6>
                <div class="small text-muted">
                  <strong>Common:</strong> Locks, key cards, cameras<br>
                  <strong>Urgent:</strong> Broken entry locks, security breaches
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Best Practices -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-check-circle"></i> Best Practices</h3>
          </div>
          <div class="card-body">
            <div class="best-practices">
              <div class="mb-3">
                <h6 class="text-success">📝 Writing Descriptions</h6>
                <ul class="small text-muted mb-0">
                  <li>Use clear, specific language</li>
                  <li>Include when the issue started</li>
                  <li>Describe what you've already tried</li>
                  <li>Note any patterns or timing</li>
                </ul>
              </div>
              <div class="mb-3">
                <h6 class="text-info">📸 Photo Guidelines</h6>
                <ul class="small text-muted mb-0">
                  <li>Take multiple angles if possible</li>
                  <li>Show the problem clearly</li>
                  <li>Include surrounding area for context</li>
                  <li>Ensure good lighting</li>
                </ul>
              </div>
              <div class="mb-3">
                <h6 class="text-warning">⚠️ Safety First</h6>
                <ul class="small text-muted mb-0">
                  <li>Report hazards immediately</li>
                  <li>Don't attempt dangerous repairs</li>
                  <li>Isolate area if needed</li>
                  <li>Mark as "Affects Guest Experience"</li>
                </ul>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('property_id');
    const roomSelect = document.getElementById('room_id');
    
    // Filter rooms based on selected property
    function filterRooms() {
        const selectedPropertyId = propertySelect.value;
        const roomOptions = roomSelect.querySelectorAll('option[data-property]');
        
        roomOptions.forEach(option => {
            if (selectedPropertyId === '' || option.dataset.property === selectedPropertyId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset room selection if current room doesn't belong to selected property
        const currentRoomOption = roomSelect.querySelector('option:checked');
        if (currentRoomOption && currentRoomOption.dataset.property && 
            currentRoomOption.dataset.property !== selectedPropertyId) {
            roomSelect.value = '';
        }
    }
    
    propertySelect.addEventListener('change', filterRooms);
    
    // Initial filter on page load
    filterRooms();
});
</script>
@endsection