@extends('tenant.layouts.app')

@section('title', 'Properties Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">
          <i class="bi bi-buildings"></i>
          <small class="text-muted"> Properties Management</small>
        </h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Properties</li>
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
                  <i class="bi bi-list"></i> All Properties
                  <span class="badge bg-primary">{{ $properties->total() }}</span>
                </h5>
              </div>
              <div>
                <a href="{{ route('tenant.properties.create') }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Property
                </a>
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

    <!-- Properties Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            @if($properties->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Property</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Rooms</th>
                      <th class="text-center">Users</th>
                      <th class="text-center">Currency</th>
                      <th class="text-center">Created</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($properties as $property)
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                   style="width: 40px; height: 40px;">
                                <i class="bi bi-building text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0">{{ $property->name }}</h6>
                              <small class="text-muted">{{ $property->code }}</small><br>
                              <small class="text-muted">
                                <i class="bi bi-geo-alt"></i> {{ $property->city }}, {{ $property->country }}
                              </small>
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          @if($property->is_active)
                            <span class="badge bg-success">Active</span>
                          @else
                            <span class="badge bg-danger">Inactive</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <span class="badge bg-info">{{ $property->rooms_count }}</span>
                        </td>
                        <td class="text-center">
                          <span class="badge bg-warning">{{ $property->users_count }}</span>
                        </td>
                        <td class="text-center">
                          <strong>{{ $property->currency }}</strong>
                        </td>
                        <td class="text-center">
                          <small class="text-muted">{{ date('M d, Y', strtotime($property->created_at)) }}</small>
                        </td>
                        <td class="text-center">
                          <div class="btn-group" role="group">
                            <!-- View -->
                            <a href="{{ route('tenant.properties.show', $property) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                              <i class="bi bi-eye"></i>
                            </a>
                            
                            <!-- Enter/Switch -->
                            <a href="{{ route('tenant.dashboard') }}?switch_property={{ $property->id }}" 
                               class="btn btn-sm btn-outline-primary" title="Enter Property">
                              <i class="bi bi-arrow-right"></i>
                            </a>
                            
                            <!-- Edit -->
                            <a href="{{ route('tenant.properties.edit', $property) }}" 
                               class="btn btn-sm btn-outline-warning" title="Edit">
                              <i class="bi bi-pencil"></i>
                            </a>
                            
                            <!-- Toggle Status -->
                            <form action="{{ route('tenant.properties.toggle-status', $property) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" 
                                      class="btn btn-sm btn-outline-{{ $property->is_active ? 'danger' : 'success' }}" 
                                      title="{{ $property->is_active ? 'Deactivate' : 'Activate' }}"
                                      onclick="return confirm('Are you sure you want to {{ $property->is_active ? 'deactivate' : 'activate' }} this property?')">
                                <i class="bi bi-{{ $property->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                              </button>
                            </form>
                            
                            <!-- Clone -->
                            <form action="{{ route('tenant.properties.clone', $property) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-outline-secondary" title="Clone"
                                      onclick="return confirm('Clone this property?')">
                                <i class="bi bi-files"></i>
                              </button>
                            </form>
                            
                            <!-- Delete -->
                            <form action="{{ route('tenant.properties.destroy', $property) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                      onclick="return confirm('Are you sure? This action cannot be undone.')">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div class="d-flex justify-content-center mt-3">
                {{ $properties->links() }}
              </div>
            @else
              <div class="text-center py-5">
                <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No Properties Found</h5>
                <p class="text-muted">Create your first property to get started.</p>
                <a href="{{ route('tenant.properties.create') }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Property
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection