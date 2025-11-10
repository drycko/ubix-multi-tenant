@extends('central.layouts.app')

@section('page-title', 'Create Role')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Create Role</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.roles.index') }}">Roles</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
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

    <div class="row">
      <div class="col-8">
        <!-- Create Role Form -->
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-user-plus me-2"></i>Role Information
            </h5>
          </div>
          <form action="{{ route('central.roles.store') }}" method="POST">
            @csrf
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
                          id="name" name="name" value="{{ old('name') }}" required
                          placeholder="e.g., custom-manager, content-editor">
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Use lowercase with hyphens. This cannot be changed later.</div>
                  </div>

                  <div class="mb-3">
                    <label for="display_name" class="form-label">Display Name</label>
                    <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                          id="display_name" name="display_name" value="{{ old('display_name') }}"
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
                              placeholder="Describe what this role is for and what access it provides">{{ old('description') }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Optional description to help understand this role's purpose</div>
                  </div>
                </div>

                <!-- Permission Assignment -->
                <div class="col-md-6">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-key me-1"></i>Permissions
                  </h6>
                  
                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-bold">Select Permissions</span>
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
                                      {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                      <div class="card">
                        <div class="card-body text-center py-2">
                          <h5 class="mb-1 text-primary" id="selected-count">0</h5>
                          <small class="text-muted">Selected</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="card">
                        <div class="card-body text-center py-2">
                          <h5 class="mb-1 text-secondary" id="total-count">{{ $permissions->count() }}</h5>
                          <small class="text-muted">Total Available</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card">
                        <div class="card-body py-2">
                          <div class="progress" style="height: 25px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%" id="permission-progress">
                              <span id="progress-text">0%</span>
                            </div>
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
                <a href="{{ route('central.roles.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Roles
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2" id="reset-btn">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Create Role
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
              <i class="fas fa-question-circle me-1"></i>Role Creation Guidelines
            </h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6 class="text-primary">Naming Convention</h6>
                <ul class="small">
                  <li>Use lowercase letters and hyphens</li>
                  <li>Be descriptive but concise</li>
                  <li>Examples: <code>property-manager</code>, <code>guest-services</code></li>
                </ul>
              </div>
              <div class="col-md-6">
                <h6 class="text-primary">Permission Selection</h6>
                <ul class="small">
                  <li>Only assign necessary permissions</li>
                  <li>Group related permissions together</li>
                  <li>Review regularly and adjust as needed</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection