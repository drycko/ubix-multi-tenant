@extends('central.layouts.app')

@section('page-title', 'Edit Role: ' . $role->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Edit Role</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.roles.index') }}">Roles</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<div class="content">
  <div class="container-fluid">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row mb-3">
      <div class="col-8">

        <!-- Edit Role Form -->
        <div class="card card-warning card-outline">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-user-edit me-2"></i>Role Information
              </h5>
              @if(in_array($role->name, ['super-user', 'super-manager', 'support']))
              <span class="badge bg-danger">
                <i class="fas fa-lock me-1"></i>System Role
              </span>
              @endif
            </div>
          </div>
          <form action="{{ route('central.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
              <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-info-circle me-1"></i>Basic Information
                  </h6>
                  
                  <div class="mb-3">
                    <label for="name" class="form-label required">Role Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                          id="name" name="name" value="{{ old('name', $role->name) }}" required
                          placeholder="e.g., custom-manager, content-editor">
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Use lowercase with hyphens. Be careful when changing this.</div>
                  </div>

                  <div class="mb-3">
                    <label for="display_name" class="form-label">Display Name</label>
                    <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                          id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}"
                          placeholder="e.g., Custom Manager, Content Editor">
                    @error('display_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Human-readable name for display purposes</div>
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4" 
                              placeholder="Describe what this role is for and what access it provides">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Optional description to help understand this role's purpose</div>
                  </div>

                  <!-- Role Information -->
                  <div class="card mt-4">
                    <div class="card-body">
                      <h6 class="text-info mb-3">
                        <i class="fas fa-info-circle me-1"></i>Current Role Information
                      </h6>
                      <div class="row">
                        <div class="col-12 mb-2">
                          <strong>Users Assigned:</strong> 
                          <span class="badge bg-primary">{{ $role->users->count() }} users</span>
                        </div>
                        <div class="col-12 mb-2">
                          <strong>Created:</strong> {{ $role->created_at->format('M d, Y \a\t g:i A') }}
                        </div>
                        <div class="col-12">
                          <strong>Last Modified:</strong> {{ $role->updated_at->format('M d, Y \a\t g:i A') }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Permission Assignment -->
                <div class="col-md-6">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-key me-1"></i>Permissions
                  </h6>
                  
                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-bold">Assign Permissions</span>
                      <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success" id="select-all-btn">
                          <i class="fas fa-check-double me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-outline-warning" id="clear-all-btn">
                          <i class="fas fa-times me-1"></i>Clear All
                        </button>
                      </div>
                    </div>

                    <div class="permission-groups" style="max-height: 400px; overflow-y: auto;">
                      @foreach($groupedPermissions as $resource => $resourcePermissions)
                      <div class="card card-outline card-secondary mb-2">
                        <div class="card-header py-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-capitalize">
                              <i class="fas fa-folder me-1"></i>{{ $resource }}
                              <span class="badge bg-secondary ms-1">{{ count($resourcePermissions) }}</span>
                            </h6>
                            <div class="btn-group btn-group-sm">
                              <button type="button" class="btn btn-outline-primary btn-sm select-group-btn" 
                                      data-group="{{ $resource }}">
                                <i class="fas fa-check"></i>
                              </button>
                              <button type="button" class="btn btn-outline-secondary btn-sm clear-group-btn" 
                                      data-group="{{ $resource }}">
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                        <div class="card-body py-2">
                          <div class="row">
                            @foreach($resourcePermissions as $permission)
                            <div class="col-12 mb-1">
                              <div class="form-check">
                                <input class="form-check-input permission-checkbox" 
                                      type="checkbox" 
                                      name="permissions[]" 
                                      value="{{ $permission->id }}" 
                                      id="permission_{{ $permission->id }}"
                                      data-group="{{ $resource }}"
                                      {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                  {{ $permission->name }}
                                </label>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </div>
                      </div>
                      @endforeach
                    </div>
                    @error('permissions')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Select the permissions this role should have</div>
                  </div>
                </div>
              </div>

              <!-- Permission Summary -->
              <div class="row mt-4">
                <div class="col-12">
                  <hr>
                  <h6 class="text-info mb-3">
                    <i class="fas fa-chart-pie me-1"></i>Permission Summary
                  </h6>
                  <div class="row">
                    <div class="col-md-3">
                      <div class="card ">
                        <div class="card-body text-center py-2">
                          <h5 class="mb-1 text-primary" id="selected-count">{{ count($rolePermissions) }}</h5>
                          <small class="text-muted">Selected</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card ">
                        <div class="card-body text-center py-2">
                          <h5 class="mb-1 text-secondary" id="total-count">{{ $permissions->count() }}</h5>
                          <small class="text-muted">Total Available</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card ">
                        <div class="card-body py-2">
                          <div class="progress" style="height: 25px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $permissions->count() > 0 ? round((count($rolePermissions) / $permissions->count()) * 100) : 0 }}%" id="permission-progress">
                              <span id="progress-text">{{ $permissions->count() > 0 ? round((count($rolePermissions) / $permissions->count()) * 100) : 0 }}%</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Changes Warning -->
              @if($role->users->count() > 0)
              <div class="row mt-4">
                <div class="col-12">
                  <div class="alert alert-warning">
                    <h6 class="alert-heading">
                      <i class="fas fa-exclamation-triangle me-2"></i>Impact Warning
                    </h6>
                    <p class="mb-0">This role is currently assigned to <strong>{{ $role->users->count() }} user(s)</strong>. 
                    Changing permissions will immediately affect their access to the system.</p>
                  </div>
                </div>
              </div>
              @endif
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('central.roles.show', $role) }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Role
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2" id="reset-btn">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>Update Role
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-4">
        <!-- Help Information -->
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-question-circle me-1"></i>Role Editing Guidelines
            </h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <h6 class="text-primary">Permission Changes</h6>
                <ul class="small">
                  <li>Changes take effect immediately</li>
                  <li>Affects all users with this role</li>
                  <li>Review carefully before saving</li>
                </ul>
              </div>
              <div class="col-md-4">
                <h6 class="text-primary">Naming Changes</h6>
                <ul class="small">
                  <li>Role name changes affect code references</li>
                  <li>Display name is for UI only</li>
                  <li>Test thoroughly after changes</li>
                </ul>
              </div>
              <div class="col-md-4">
                <h6 class="text-primary">Safety Tips</h6>
                <ul class="small">
                  <li>Don't remove critical permissions</li>
                  <li>Test with a non-admin account</li>
                  <li>Keep backups of important roles</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Store original permissions for reset
const originalPermissions = @json($rolePermissions);

$(document).ready(function() {
    updatePermissionSummary();
    
    // Update summary when permissions change
    $('.permission-checkbox').change(function() {
        updatePermissionSummary();
    });

    // Event handlers for buttons
    $('#select-all-btn').click(function() {
        selectAllPermissions();
    });

    $('#clear-all-btn').click(function() {
        clearAllPermissions();
    });

    $('.select-group-btn').click(function() {
        const group = $(this).data('group');
        selectGroupPermissions(group);
    });

    $('.clear-group-btn').click(function() {
        const group = $(this).data('group');
        clearGroupPermissions(group);
    });

    $('#reset-btn').click(function() {
        resetToOriginal();
    });
});

function selectAllPermissions() {
    $('.permission-checkbox').prop('checked', true);
    updatePermissionSummary();
}

function clearAllPermissions() {
    $('.permission-checkbox').prop('checked', false);
    updatePermissionSummary();
}

function selectGroupPermissions(group) {
    $(`.permission-checkbox[data-group="${group}"]`).prop('checked', true);
    updatePermissionSummary();
}

function clearGroupPermissions(group) {
    $(`.permission-checkbox[data-group="${group}"]`).prop('checked', false);
    updatePermissionSummary();
}

function resetToOriginal() {
    // Uncheck all first
    $('.permission-checkbox').prop('checked', false);
    
    // Check original permissions
    originalPermissions.forEach(function(permissionId) {
        $(`#permission_${permissionId}`).prop('checked', true);
    });
    
    updatePermissionSummary();
}

function updatePermissionSummary() {
    const selectedCount = $('.permission-checkbox:checked').length;
    const totalCount = $('.permission-checkbox').length;
    const percentage = totalCount > 0 ? Math.round((selectedCount / totalCount) * 100) : 0;
    
    $('#selected-count').text(selectedCount);
    $('#total-count').text(totalCount);
    $('#permission-progress').css('width', percentage + '%');
    $('#progress-text').text(percentage + '%');
    
    // Update progress bar color based on percentage
    const progressBar = $('#permission-progress');
    progressBar.removeClass('bg-danger bg-warning bg-success');
    
    if (percentage < 25) {
        progressBar.addClass('bg-danger');
    } else if (percentage < 75) {
        progressBar.addClass('bg-warning');
    } else {
        progressBar.addClass('bg-success');
    }
}
</script>
@endpush