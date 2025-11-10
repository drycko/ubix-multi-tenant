@extends('central.layouts.app')

@section('page-title', 'Permissions Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Permissions Management</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Permissions</li>
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

    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <!-- Page Header -->
    <div class="card card-info card-outline mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">
              <i class="fas fa-key me-2"></i>System Permissions
            </h5><br>
            <p class="text-muted small mb-0 mt-1">Manage system permissions and their assignments</p>
          </div>
          @can('manage permissions')
          <div>
            <a href="{{ route('central.roles.index') }}" class="btn btn-outline-primary me-2">
              <i class="fas fa-users-cog me-1"></i>Manage Roles
            </a>
            <div class="btn-group">
              <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-plus me-1"></i>Create Permission
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('central.permissions.create') }}">
                  <i class="fas fa-plus me-2"></i>Single Permission
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                  <i class="fas fa-layer-group me-2"></i>Bulk Create
                </a></li>
              </ul>
            </div>
          </div>
          @endcan
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ $permissions->count() }}</h4>
                <p class="mb-0">Total Permissions</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-key fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-1">{{ count($groupedPermissions) }}</h4>
                <p class="mb-0">Resource Groups</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-folder fa-2x"></i>
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
                <h4 class="mb-1">{{ $permissions->sum('roles_count') }}</h4>
                <p class="mb-0">Total Assignments</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-link fa-2x"></i>
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
                <h4 class="mb-1">{{ $permissions->where('roles_count', 0)->count() }}</h4>
                <p class="mb-0">Unassigned</p>
              </div>
              <div class="align-self-center">
                <i class="fas fa-unlink fa-2x"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Permissions by Resource Group -->
    <div class="row">
      @foreach($groupedPermissions as $resource => $resourcePermissions)
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
          <div class="card-header">
            <h6 class="card-title mb-0 text-capitalize">
              <i class="fas fa-folder me-2"></i>{{ $resource }}
              <span class="badge bg-primary ms-2">{{ count($resourcePermissions) }}</span>
            </h6>
          </div>
          <div class="card-body">
            <div class="permission-list" style="max-height: 300px; overflow-y: auto;">
              @foreach($resourcePermissions as $permission)
              <div class="d-flex justify-content-between align-items-center mb-2 p-2  rounded">
                <div class="flex-grow-1">
                  <div class="fw-bold">{{ $permission->name }}</div>
                  @if($permission->display_name)
                  <small class="text-muted">{{ $permission->display_name }}</small>
                  @endif
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-{{ $permission->roles_count > 0 ? 'success' : 'secondary' }} me-2">
                    {{ $permission->roles_count }} roles
                  </span>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('central.permissions.show', $permission) }}" 
                       class="btn btn-outline-primary btn-sm" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                    @can('manage permissions')
                    <a href="{{ route('central.permissions.edit', $permission) }}" 
                       class="btn btn-outline-warning btn-sm" title="Edit Permission">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($permission->roles_count == 0)
                    <form action="{{ route('central.permissions.destroy', $permission) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this permission?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Permission">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    @endif
                    @endcan
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    @if(count($groupedPermissions) == 0)
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="fas fa-key fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No permissions found</h5>
        <p class="text-muted">Get started by creating your first permission.</p>
        @can('manage permissions')
        <a href="{{ route('central.permissions.create') }}" class="btn btn-info">
          <i class="fas fa-plus me-1"></i>Create First Permission
        </a>
        @endcan
      </div>
    </div>
    @endif

    <!-- Quick Actions -->
    @can('manage permissions')
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-bolt me-1"></i>Quick Actions
            </h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('central.roles.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-users-cog me-2"></i>Manage Roles
              </a>
              <a href="{{ route('central.role-assignments.index') }}" class="btn btn-outline-success">
                <i class="fas fa-user-tag me-2"></i>Role Assignments
              </a>
              <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#bulkCreateModal">
                <i class="fas fa-layer-group me-2"></i>Bulk Create Permissions
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-info-circle me-1"></i>Permission Information
            </h6>
          </div>
          <div class="card-body">
            <div class="small">
              <p class="mb-2"><strong>Permission Groups:</strong> Organized by resource type</p>
              <p class="mb-2"><strong>Role Assignments:</strong> Shows how many roles use each permission</p>
              <p class="mb-0"><strong>Deletion:</strong> Only unassigned permissions can be deleted</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endcan
  </div>
</div>

<!-- Bulk Create Modal -->
@can('manage permissions')
<div class="modal fade" id="bulkCreateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('central.permissions.bulk-create') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-layer-group me-2"></i>Bulk Create Permissions
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="resource" class="form-label required">Resource Name</label>
            <input type="text" class="form-control" id="resource" name="resource" required
                   placeholder="e.g., bookings, rooms, guests">
            <div class="form-text">The resource these permissions will manage</div>
          </div>
          <div class="mb-3">
            <label for="actions" class="form-label required">Actions</label>
            <div class="row">
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="view" id="action_view">
                  <label class="form-check-label" for="action_view">View</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="create" id="action_create">
                  <label class="form-check-label" for="action_create">Create</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="edit" id="action_edit">
                  <label class="form-check-label" for="action_edit">Edit</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="delete" id="action_delete">
                  <label class="form-check-label" for="action_delete">Delete</label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="manage" id="action_manage">
                  <label class="form-check-label" for="action_manage">Manage</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="export" id="action_export">
                  <label class="form-check-label" for="action_export">Export</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="import" id="action_import">
                  <label class="form-check-label" for="action_import">Import</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="actions[]" value="assign" id="action_assign">
                  <label class="form-check-label" for="action_assign">Assign</label>
                </div>
              </div>
            </div>
            <div class="form-text">Select the actions that can be performed on this resource</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">
            <i class="fas fa-plus me-1"></i>Create Permissions
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endcan
@endsection
