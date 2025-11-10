@extends('central.layouts.app')

@section('page-title', 'Create Permission')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row mb-2">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-user-tag"></i> <small class="text-muted">Create Permissions</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.permissions.index') }}">Permissions</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create Permission</li>
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
        <!-- Create Permission Form -->
        <div class="card card-info card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-key me-2"></i>Permission Information
            </h5>
          </div>
          <form action="{{ route('central.permissions.store') }}" method="POST">
            @csrf
            <div class="card-body">
              <div class="mb-3">
                <label for="name" class="form-label required">Permission Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name') }}" required
                       placeholder="e.g., view bookings, edit rooms, manage users">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Use format: "action resource" (e.g., "view bookings", "edit rooms")</div>
              </div>

              <div class="mb-3">
                <label for="display_name" class="form-label">Display Name</label>
                <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                       id="display_name" name="display_name" value="{{ old('display_name') }}"
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
                          placeholder="Describe what this permission allows users to do">{{ old('description') }}</textarea>
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
                        <strong>Name:</strong> <span id="preview-name" class="text-muted">Enter permission name</span>
                      </div>
                      <div class="mb-2">
                        <strong>Display Name:</strong> <span id="preview-display" class="text-muted">Not set</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-2">
                        <strong>Guard:</strong> <span class="badge bg-info">tenant</span>
                      </div>
                      <div class="mb-2">
                        <strong>Type:</strong> <span id="preview-type" class="badge bg-secondary">Custom</span>
                      </div>
                    </div>
                  </div>
                  <div class="mt-2">
                    <strong>Description:</strong> 
                    <div id="preview-description" class="text-muted mt-1">Enter description</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('central.permissions.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to Permissions
                </a>
                <div>
                  <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="fas fa-undo me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-info">
                    <i class="fas fa-save me-1"></i>Create Permission
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Help Information -->
        <div class="card card-outline card-info">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-question-circle me-1"></i>Permission Guidelines
            </h6>
          </div>
          <div class="card-body">
            <h6 class="text-primary">Naming Convention</h6>
            <ul class="small mb-3">
              <li>Use format: "action resource"</li>
              <li>Use lowercase with spaces</li>
              <li>Be specific and clear</li>
            </ul>

            <h6 class="text-primary">Examples</h6>
            <ul class="small mb-3">
              <li><code>view bookings</code></li>
              <li><code>create rooms</code></li>
              <li><code>edit guests</code></li>
              <li><code>delete users</code></li>
              <li><code>manage settings</code></li>
            </ul>

            <h6 class="text-primary">Best Practices</h6>
            <ul class="small">
              <li>Create granular permissions</li>
              <li>Group related permissions</li>
              <li>Use consistent action verbs</li>
              <li>Document complex permissions</li>
            </ul>
          </div>
        </div>

        <!-- Common Actions -->
        <div class="card card-outline card-success">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-bolt me-1"></i>Quick Actions
            </h6>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <button type="button" class="btn btn-outline-primary" onclick="fillExample('view')">
                <i class="fas fa-eye me-2"></i>View Example
              </button>
              <button type="button" class="btn btn-outline-success" onclick="fillExample('create')">
                <i class="fas fa-plus me-2"></i>Create Example
              </button>
              <button type="button" class="btn btn-outline-warning" onclick="fillExample('edit')">
                <i class="fas fa-edit me-2"></i>Edit Example
              </button>
              <button type="button" class="btn btn-outline-danger" onclick="fillExample('delete')">
                <i class="fas fa-trash me-2"></i>Delete Example
              </button>
              <button type="button" class="btn btn-outline-info" onclick="fillExample('manage')">
                <i class="fas fa-cogs me-2"></i>Manage Example
              </button>
            </div>
          </div>
        </div>

        <!-- Bulk Create Option -->
        <div class="card card-outline card-warning">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <i class="fas fa-layer-group me-1"></i>Need Multiple Permissions?
            </h6>
          </div>
          <div class="card-body">
            <p class="small mb-3">If you need to create multiple permissions for a resource, use the bulk create feature.</p>
            <a href="{{ route('central.permissions.index') }}" class="btn btn-warning w-100">
              <i class="fas fa-layer-group me-1"></i>Go to Bulk Create
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection