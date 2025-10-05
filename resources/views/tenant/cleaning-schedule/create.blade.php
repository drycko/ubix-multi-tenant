@extends('tenant.layouts.app')

@section('title', 'Create Cleaning Checklist')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Create Cleaning Checklist</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.cleaning-schedule.index') }}">Cleaning Schedule</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create Checklist</li>
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
    
    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h6 class="alert-heading">Please fix the following errors:</h6>
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
            <h3 class="card-title">Checklist Details</h3>
          </div>
          <form method="POST" action="{{ route('tenant.cleaning-schedule.store') }}">
            @csrf
            <div class="card-body">
              <div class="row">
                {{-- checklist needs a name --}}
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="name" class="form-label">Checklist Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="{{ old('name') }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="checklist_type" class="form-label">Checklist Type <span class="text-danger">*</span></label>
                    <select name="checklist_type" id="checklist_type" class="form-select" required>
                      <option value="">Select Type</option>
                      <option value="standard" {{ old('checklist_type') == 'standard' ? 'selected' : '' }}>Standard Cleaning</option>
                      <option value="checkout" {{ old('checklist_type') == 'checkout' ? 'selected' : '' }}>Checkout Cleaning</option>
                      <option value="maintenance" {{ old('checklist_type') == 'maintenance' ? 'selected' : '' }}>Maintenance Clean</option>
                      <option value="deep_clean" {{ old('checklist_type') == 'deep_clean' ? 'selected' : '' }}>Deep Clean</option>
                      <option value="inspection" {{ old('checklist_type') == 'inspection' ? 'selected' : '' }}>Quality Inspection</option>
                    </select>
                  </div>
                </div>
              </div>
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
                    <label for="room_type_id" class="form-label">Room Type</label>
                    <select name="room_type_id" id="room_type_id" class="form-select">
                      <option value="">All Room Types</option>
                      @foreach($roomTypes as $roomType)
                      <option value="{{ $roomType->id }}" {{ old('room_type_id') == $roomType->id ? 'selected' : '' }}>
                        {{ $roomType->name }}
                      </option>
                      @endforeach
                    </select>
                    <small class="form-text text-muted">Leave blank to apply to all room types in this property</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="estimated_minutes" class="form-label">Estimated Duration (minutes)</label>
                    <input type="number" name="estimated_minutes" id="estimated_minutes" class="form-control" 
                           value="{{ old('estimated_minutes', 60) }}" min="15" max="480">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" name="description" id="description" class="form-control" 
                           value="{{ old('description') }}" placeholder="Brief description of this checklist">
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label">Checklist Items <span class="text-danger">*</span></label>
                <div class="checklist-items-container">
                  <!-- Bathroom Section -->
                  <div class="card mb-3">
                    <div class="card-header ">
                      <h6 class="mb-0">Bathroom</h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_toilet_clean" class="form-check-input" id="bathroom_toilet_clean">
                            <label class="form-check-label" for="bathroom_toilet_clean">Clean toilet inside and out</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_shower_clean" class="form-check-input" id="bathroom_shower_clean">
                            <label class="form-check-label" for="bathroom_shower_clean">Clean shower/tub</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_sink_clean" class="form-check-input" id="bathroom_sink_clean">
                            <label class="form-check-label" for="bathroom_sink_clean">Clean sink and faucet</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_mirror_clean" class="form-check-input" id="bathroom_mirror_clean">
                            <label class="form-check-label" for="bathroom_mirror_clean">Clean mirror</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_floor_mop" class="form-check-input" id="bathroom_floor_mop">
                            <label class="form-check-label" for="bathroom_floor_mop">Mop floor</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_towels_replace" class="form-check-input" id="bathroom_towels_replace">
                            <label class="form-check-label" for="bathroom_towels_replace">Replace towels</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_amenities_stock" class="form-check-input" id="bathroom_amenities_stock">
                            <label class="form-check-label" for="bathroom_amenities_stock">Stock amenities</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bathroom_trash_empty" class="form-check-input" id="bathroom_trash_empty">
                            <label class="form-check-label" for="bathroom_trash_empty">Empty trash</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Bedroom Section -->
                  <div class="card mb-3">
                    <div class="card-header ">
                      <h6 class="mb-0">Bedroom</h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bedroom_bed_make" class="form-check-input" id="bedroom_bed_make">
                            <label class="form-check-label" for="bedroom_bed_make">Make bed with fresh linens</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bedroom_nightstands_clean" class="form-check-input" id="bedroom_nightstands_clean">
                            <label class="form-check-label" for="bedroom_nightstands_clean">Clean nightstands</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bedroom_dresser_clean" class="form-check-input" id="bedroom_dresser_clean">
                            <label class="form-check-label" for="bedroom_dresser_clean">Clean dresser/desk</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bedroom_vacuum" class="form-check-input" id="bedroom_vacuum">
                            <label class="form-check-label" for="bedroom_vacuum">Vacuum carpet/floor</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bedroom_windows_clean" class="form-check-input" id="bedroom_windows_clean">
                            <label class="form-check-label" for="bedroom_windows_clean">Clean windows</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="bedroom_trash_empty" class="form-check-input" id="bedroom_trash_empty">
                            <label class="form-check-label" for="bedroom_trash_empty">Empty trash</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Kitchen Section (if applicable) -->
                  <div class="card mb-3 kitchen-section" style="display: none;">
                    <div class="card-header ">
                      <h6 class="mb-0">Kitchen</h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="kitchen_counters_clean" class="form-check-input" id="kitchen_counters_clean">
                            <label class="form-check-label" for="kitchen_counters_clean">Clean countertops</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="kitchen_appliances_clean" class="form-check-input" id="kitchen_appliances_clean">
                            <label class="form-check-label" for="kitchen_appliances_clean">Clean appliances</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="kitchen_sink_clean" class="form-check-input" id="kitchen_sink_clean">
                            <label class="form-check-label" for="kitchen_sink_clean">Clean sink</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="kitchen_dishes_stock" class="form-check-input" id="kitchen_dishes_stock">
                            <label class="form-check-label" for="kitchen_dishes_stock">Stock dishes/utensils</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="kitchen_floor_clean" class="form-check-input" id="kitchen_floor_clean">
                            <label class="form-check-label" for="kitchen_floor_clean">Clean floor</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="kitchen_trash_empty" class="form-check-input" id="kitchen_trash_empty">
                            <label class="form-check-label" for="kitchen_trash_empty">Empty trash</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Living Area Section -->
                  <div class="card mb-3 living-section" style="display: none;">
                    <div class="card-header ">
                      <h6 class="mb-0">Living Area</h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="living_furniture_dust" class="form-check-input" id="living_furniture_dust">
                            <label class="form-check-label" for="living_furniture_dust">Dust furniture</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="living_vacuum" class="form-check-input" id="living_vacuum">
                            <label class="form-check-label" for="living_vacuum">Vacuum carpet/floor</label>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="living_windows_clean" class="form-check-input" id="living_windows_clean">
                            <label class="form-check-label" for="living_windows_clean">Clean windows</label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" name="checklist_items[]" value="living_trash_empty" class="form-check-input" id="living_trash_empty">
                            <label class="form-check-label" for="living_trash_empty">Empty trash</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.cleaning-schedule.index') }}" class="btn btn-secondary">
                  <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Create Checklist Template
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Checklist Type Guide -->
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">Checklist Templates</h3>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i>
              <strong>Creating Templates:</strong> You're creating reusable checklist templates that can be applied to multiple rooms of the same type.
            </div>
            <div class="checklist-guide">
              <div class="mb-3">
                <h6 class="text-primary">Standard Cleaning</h6>
                <p class="small text-muted">Regular daily/weekly cleaning tasks</p>
              </div>
              <div class="mb-3">
                <h6 class="text-warning">Checkout Cleaning</h6>
                <p class="small text-muted">Standard cleaning after guest checkout</p>
              </div>
              <div class="mb-3">
                <h6 class="text-warning">Maintenance Clean</h6>
                <p class="small text-muted">Cleaning during maintenance periods</p>
              </div>
              <div class="mb-3">
                <h6 class="text-info">Deep Clean</h6>
                <p class="small text-muted">Thorough cleaning with additional tasks</p>
              </div>
              <div class="mb-3">
                <h6 class="text-success">Quality Inspection</h6>
                <p class="small text-muted">Final inspection checklist</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="selectAll()">
              Select All Items
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="clearAll()">
              Clear All Items
            </button>
            <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="selectByType()">
              Select by Room Type
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection