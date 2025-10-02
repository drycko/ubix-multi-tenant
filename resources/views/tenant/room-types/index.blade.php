@extends('tenant.layouts.app')

@section('title', 'Room Types Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">
          <i class="bi bi-door-open"></i>
          <small class="text-muted"> Room Types Management</small>
        </h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Room Types</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    <!-- Property Selector -->
    @include('tenant.components.property-selector')

    <!-- Action Bar -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-0">
                  <i class="bi bi-door-open"></i> All Room Types
                  <span class="badge bg-primary">{{ $roomTypes->total() }}</span>
                </h5>
              </div>
              <div>
                @can('manage room types')
                <a href="{{ route('tenant.room-types.create') }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Room Type
                </a>
                @endcan
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- messages from redirect --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- error messages if any --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.room-types.index') }}" class="row g-3">
              <input type="hidden" name="property_id" value="{{ request('property_id') }}">
              
              <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by room type name, code or description...">
              </div>
              
              <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="">All Status</option>
                  <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
              
              <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2 d-md-flex">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                  </button>
                  <a href="{{ route('tenant.room-types.index', ['property_id' => request('property_id')]) }}" 
                     class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Clear
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Room Types Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            @if($roomTypes->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Room Type</th>
                      <th class="text-center">Capacity</th>
                      <th class="text-center">Total Rooms</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Created</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($roomTypes as $type)
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                              <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                   style="width: 40px; height: 40px;">
                                <i class="bi bi-door-open text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0">{{ $type->name }}</h6>
                              <small class="text-muted">{{ $type->code }}</small><br>
                              @if($type->description)
                                <small class="text-muted">{{ Str::limit($type->description, 50) }}</small>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          <div>
                            <span class="badge bg-info">Base: {{ $type->base_capacity }}</span>
                            @if($type->max_capacity > $type->base_capacity)
                              <br><span class="badge bg-warning mt-1">Max: {{ $type->max_capacity }}</span>
                            @endif
                          </div>
                        </td>
                        <td class="text-center">
                          <span class="badge bg-primary">{{ $type->rooms_count }}</span>
                        </td>
                        <td class="text-center">
                          @if($type->is_active)
                            <span class="badge bg-success">Active</span>
                          @else
                            <span class="badge bg-danger">Inactive</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <small class="text-muted">{{ date('M d, Y', strtotime($type->created_at)) }}</small>
                        </td>
                        <td class="text-center">
                          <div class="btn-group" role="group">
                            <!-- View -->
                            <a href="{{ route('tenant.room-types.show', $type) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                              <i class="bi bi-eye"></i>
                            </a>
                            
                            <!-- Edit -->
                            @can('manage room types')
                            <a href="{{ route('tenant.room-types.edit', $type) }}" 
                               class="btn btn-sm btn-outline-warning" title="Edit">
                              <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            
                            <!-- Toggle Status -->
                            @can('manage room types')
                            <form action="{{ route('tenant.room-types.toggle-status', $type) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" 
                                      class="btn btn-sm btn-outline-{{ $type->is_active ? 'danger' : 'success' }}" 
                                      title="{{ $type->is_active ? 'Deactivate' : 'Activate' }}"
                                      onclick="return confirm('Are you sure you want to {{ $type->is_active ? 'deactivate' : 'activate' }} this room type?')">
                                <i class="bi bi-{{ $type->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                              </button>
                            </form>
                            @endcan
                            
                            <!-- Clone -->
                            @can('manage room types')
                            <form action="{{ route('tenant.room-types.clone', $type) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-outline-secondary" title="Clone"
                                      onclick="return confirm('Clone this room type?')">
                                <i class="bi bi-files"></i>
                              </button>
                            </form>
                            @endcan
                            
                            <!-- Delete -->
                            @can('delete rooms')
                            <form action="{{ route('tenant.room-types.destroy', $type) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                      onclick="return confirm('Are you sure? This action cannot be undone.')">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                            @endcan
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              {{-- Pagination links --}}
              {{-- Beautiful pagination --}}
              @if($roomTypes->hasPages())
              <div class="container-fluid py-3">
                  <div class="row align-items-center">
                      <div class="col-md-12 float-end">
                          {{ $roomTypes->links('vendor.pagination.bootstrap-5') }}
                      </div>
                  </div>
              </div>
              @endif
            @else
              <div class="text-center py-5">
                <i class="bi bi-door-open text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No Room Types Found</h5>
                <p class="text-muted">Create your first room type to get started.</p>
                @can('manage room types')
                <a href="{{ route('tenant.room-types.create') }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Room Type
                </a>
                @endcan
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection