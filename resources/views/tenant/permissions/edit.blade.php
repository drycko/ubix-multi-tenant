@extends('tenant.layouts.app')

@section('page-title', 'Edit Permission: ' . $permission->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Edit Permissions</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.permissions.index') }}">Permissions</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.permissions.show', $permission) }}">{{ $permission->name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Permission</li>
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
      <div class="col-md-8">
        <!-- Edit Permission Form -->
        <div class="card card-warning card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-key me-2"></i>Permission Information
            </h5>
          </div>
          <form action="{{ route('tenant.permissions.update', $permission) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
              <div class="mb-3">
                <label for="name" class="form-label required">Permission Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $permission->name) }}" required
                       placeholder="e.g., view bookings, edit rooms, manage users">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Use format: "action resource" (e.g., "view bookings", "edit rooms")</div>
              </div>

              <div class="mb-3">
                <label for="display_name" class="form-label">Display Name</label>
                <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                       id="display_name" name="display_name" value="{{ old('display_name', $permission->display_name) }}"
                       placeholder="e.g., View Bookings, Edit Rooms">
                @error('display_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Human-readable name for display purposes (optional)</div>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="4" 
                          placeholder="Describe what this permission allows users to do">{{ old('description', $permission->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Optional description to help understand this permission's purpose</div>
              </div>

              <!-- Permission Preview -->
              <div class="card ">
                <div class="card-body">
                  <h6 class="text-primary mb-3">
                    <i class="fas fa-eye me-1"></i>Permission Preview
                  </h6>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-2">
                        <strong>Name:</strong> <span id="preview-name">{{ $permission->name }}</span>
                      </div>
                      <div class="mb-2">
                        <strong>Display Name:</strong> <span id="preview-display">{{ $permission->display_name ?: 'Not set' }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-2">
                        <strong>Guard:</strong> <span class="badge bg-info">{{ $permission->guard_name }}</span>
                      </div>
                      <div class="mb-2">
                        <strong>Last Modified:</strong> {{ $permission->updated_at->format('M d, Y') }}
                      </div>
                    </div>
                  </div>
                  <div class="mt-2">
                    <strong>Description:</strong> 
                    <div id="preview-description" class="text-muted mt-1">{{ $permission->description ?: 'Enter description' }}</div>
                  </div>
                </div>
              </div>

              <!-- Impact Warning -->
              @if($permission->roles->count() > 0)
              <div class="alert alert-warning mt-3">
                <h6 class="alert-heading">
                  <i class="fas fa-exclamation-triangle me-2"></i>Impact Warning
                </h6>
                <p class="mb-0">This permission is currently assigned to <strong>{{ $permission->roles->count() }} role(s)</strong>. 
                Changes will immediately affect users with those roles.</p>
              </div>
              @endif
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.permissions.show', $permission) }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Permission
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>Update Permission
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Current Information -->
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-info-circle me-1"></i>Current Information
            </h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <strong>Assigned Roles:</strong> 
              <span class="badge bg-primary">{{ $permission->roles->count() }} roles</span>
            </div>
            <div class="mb-3">
              <strong>Created:</strong> {{ $permission->created_at->format('M d, Y \a\t g:i A') }}
            </div>
            <div class="mb-3">
              <strong>Last Modified:</strong> {{ $permission->updated_at->format('M d, Y \a\t g:i A') }}
            </div>
            @if($permission->roles->count() > 0)
            <div class="mb-0">
              <strong>Used by Roles:</strong>
              <div class="mt-1">
                @foreach($permission->roles->take(3) as $role)
                <span class="badge bg-secondary me-1">{{ $role->name }}</span>
                @endforeach
                @if($permission->roles->count() > 3)
                <span class="text-muted small">+{{ $permission->roles->count() - 3 }} more</span>
                @endif
              </div>
            </div>
            @endif
          </div>
        </div>

        <!-- Help Information -->
        <div class="card card-outline card-warning">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-question-circle me-1"></i>Editing Guidelines
            </h6>
          </div>
          <div class="card-body">
            <h6 class="text-primary">Permission Changes</h6>
            <ul class="small mb-3">
              <li>Changes take effect immediately</li>
              <li>Affects all roles using this permission</li>
              <li>Review impact carefully before saving</li>
            </ul>

            <h6 class="text-primary">Naming Changes</h6>
            <ul class="small mb-3">
              <li>Permission name changes may affect code</li>
              <li>Display name is for UI only</li>
              <li>Test thoroughly after changes</li>
            </ul>

            <h6 class="text-primary">Safety Tips</h6>
            <ul class="small">
              <li>Document reasons for changes</li>
              <li>Test with non-admin accounts</li>
              <li>Consider creating new instead of editing</li>
            </ul>
          </div>
        </div>

        <!-- Assigned Roles -->
        @if($permission->roles->count() > 0)
        <div class="card card-outline card-primary">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-users-cog me-1"></i>Assigned Roles
            </h6>
          </div>
          <div class="card-body">
            @foreach($permission->roles as $role)
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <div class="fw-bold">{{ $role->name }}</div>
                @if($role->display_name)
                <small class="text-muted">{{ $role->display_name }}</small>
                @endif
              </div>
              <a href="{{ route('tenant.roles.show', $role) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i>
              </a>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card card-outline card-danger">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-exclamation-triangle me-1"></i>Danger Zone
            </h6>
          </div>
          <div class="card-body">
            @if($permission->roles->count() == 0)
            <form action="{{ route('tenant.permissions.destroy', $permission) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this permission? This action cannot be undone.')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger w-100">
                <i class="fas fa-trash me-1"></i>Delete Permission
              </button>
            </form>
            <div class="form-text mt-2">This action cannot be undone.</div>
            @else
            <button class="btn btn-danger w-100" disabled>
              <i class="fas fa-trash me-1"></i>Delete Permission
            </button>
            <div class="form-text mt-2">Cannot delete permission assigned to roles. Remove from all roles first.</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update preview as user types
    $('#name').on('input', function() {
        const value = $(this).val() || '{{ $permission->name }}';
        $('#preview-name').text(value);
    });

    $('#display_name').on('input', function() {
        const value = $(this).val() || 'Not set';
        $('#preview-display').text(value);
    });

    $('#description').on('input', function() {
        const value = $(this).val() || 'Enter description';
        $('#preview-description').text(value);
    });

    // Reset preview on form reset
    $('form').on('reset', function() {
        setTimeout(function() {
            $('#preview-name').text('{{ $permission->name }}');
            $('#preview-display').text('{{ $permission->display_name ?: "Not set" }}');
            $('#preview-description').text('{{ $permission->description ?: "Enter description" }}');
        }, 10);
    });
});
</script>
@endpush