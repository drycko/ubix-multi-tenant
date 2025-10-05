@extends('tenant.layouts.app')

@section('title', 'Create Maintenance Request')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">Create Maintenance Request</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.maintenance.index') }}">Maintenance</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create Request</li>
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
            <h3 class="card-title">Request Details</h3>
          </div>
          <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
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
                    <label for="room_id" class="form-label">Room</label>
                    <select name="room_id" id="room_id" class="form-select">
                      <option value="">Select Room (Optional)</option>
                      @foreach($rooms as $room)
                      <option value="{{ $room->id }}" 
                              data-property-id="{{ $room->property_id }}"
                              {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        Room {{ $room->number }} - {{ $room->type->name }}
                      </option>
                      @endforeach
                    </select>
                    <div class="form-text">Leave empty for property-wide issues</div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select" required>
                      <option value="">Select Category</option>
                      @foreach($categories as $category)
                      <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $category)) }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                    <select name="priority" id="priority" class="form-select" required>
                      <option value="">Select Priority</option>
                      @foreach($priorities as $priority)
                      <option value="{{ $priority }}" {{ old('priority') == $priority ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $priority)) }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" 
                       value="{{ old('title') }}" required 
                       placeholder="Brief description of the issue">
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                <textarea name="description" id="description" class="form-control" rows="4" required
                          placeholder="Provide a detailed description of the issue, including when it started, symptoms, and any attempted fixes...">{{ old('description') }}</textarea>
              </div>

              <div class="mb-3">
                <label for="location_details" class="form-label">Specific Location</label>
                <input type="text" name="location_details" id="location_details" class="form-control" 
                       value="{{ old('location_details') }}" 
                       placeholder="e.g., Bathroom sink, Living room ceiling, Kitchen refrigerator">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="assigned_to" class="form-label">Assign To</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                      <option value="">Leave Unassigned</option>
                      @foreach($maintenanceStaff as $staff)
                      <option value="{{ $staff->id }}" {{ old('assigned_to') == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6"></div>
              </div>

              <div class="mb-3">
                <label for="photos" class="form-label">Photos</label>
                <input type="file" name="photos[]" id="photos" class="form-control" 
                       multiple accept="image/*">
                <div class="form-text">Upload photos of the issue to help with diagnosis</div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="estimated_cost" class="form-label">Estimated Cost</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" name="estimated_cost" id="estimated_cost" class="form-control" 
                             value="{{ old('estimated_cost') }}" min="0" step="0.01"
                             placeholder="0.00">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="scheduled_for" class="form-label">Scheduled For</label>
                    <input type="datetime-local" name="scheduled_for" id="scheduled_for" class="form-control" 
                           value="{{ old('scheduled_for') }}">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" name="requires_room_closure" id="requires_room_closure" 
                         class="form-check-input" value="1" {{ old('requires_room_closure') ? 'checked' : '' }}>
                  <label class="form-check-label" for="requires_room_closure">
                    Requires Room Closure
                  </label>
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
                    <i class="bi bi-plus-circle"></i> Create Request
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
            <h3 class="card-title"><i class="bi bi-tools"></i> Creating Maintenance Request</h3>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i>
              <strong>Maintenance Workflow:</strong> You're creating a formal request that will be tracked, assigned, and resolved through our maintenance system.
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

@endsection