@extends('tenant.layouts.app')

@section('title', 'Room Amenities Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">
          <i class="bi bi-star"></i>
          <small class="text-muted"> Room Amenities Management</small>
        </h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Room Amenities</li>
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
                  <i class="bi bi-star"></i> All Room Amenities
                  <span class="badge bg-primary">{{ $roomAmenities->total() }}</span>
                </h5>
              </div>
              <div>
                @can('create room amenities')
                <a href="{{ route('tenant.room-amenities.create', ['property_id' => request('property_id')]) }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Amenity
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
    {{-- validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search Filter -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.room-amenities.index') }}" class="row g-3">
              <input type="hidden" name="property_id" value="{{ request('property_id') }}">
              
              <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search amenities, descriptions, or slugs...">
              </div>

              {{-- super admin can filter by property --}}
              @if(is_super_user())
              <div class="col-md-4">
                <label for="property_id" class="form-label">Property</label>
                <select class="form-select" id="property_id" name="property_id">
                  <option value="">All Properties</option>
                  @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                      {{ $property->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              @endif

              <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2 d-md-flex">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                  </button>
                  <a href="{{ route('tenant.room-amenities.index', ['property_id' => request('property_id')]) }}" 
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

    <!-- Room Amenities Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            @if($roomAmenities->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Amenity Details</th>
                      <th class="text-center">Icon Preview</th>
                      <th class="text-center">Slug</th>
                      <th class="text-center">Property</th>
                      <th class="text-center">Created</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($roomAmenities as $amenity)
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                              <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                   style="width: 40px; height: 40px;">
                                <i class="{{ $amenity->icon }} text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0">{{ $amenity->name }}</h6>
                              @if($amenity->description)
                                <small class="text-muted">{{ Str::limit($amenity->description, 60) }}</small>
                              @else
                                <small class="text-muted">No description</small>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          <div class="fs-4">
                            <i class="{{ $amenity->icon }}" title="{{ $amenity->icon }}"></i>
                          </div>
                          <small class="text-muted">{{ $amenity->icon }}</small>
                        </td>
                        <td class="text-center">
                          <code class="bg-light px-2 py-1 rounded">{{ $amenity->slug }}</code>
                        </td>
                        <td class="text-center">
                          @if($amenity->property)
                            <span class="badge bg-info">{{ $amenity->property->name }}</span>
                          @else
                            <span class="badge bg-secondary">Global</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <small class="text-muted">{{ $amenity->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-center">
                          <div class="btn-group" role="group">
                            <!-- View -->
                            <a href="{{ route('tenant.room-amenities.show', $amenity) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                              <i class="bi bi-eye"></i>
                            </a>
                            
                            <!-- Edit -->
                            @can('edit room amenities')
                            <a href="{{ route('tenant.room-amenities.edit', $amenity) }}" 
                               class="btn btn-sm btn-outline-warning" title="Edit">
                              <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            
                            <!-- Clone -->
                            @can('create room amenities')
                            <form action="{{ route('tenant.room-amenities.clone', $amenity) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-outline-secondary" title="Clone"
                                      onclick="return confirm('Clone this room amenity?')">
                                <i class="bi bi-files"></i>
                              </button>
                            </form>
                            @endcan
                            
                            <!-- Delete -->
                            @can('delete room amenities')
                            <form action="{{ route('tenant.room-amenities.destroy', $amenity) }}" 
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
              <div class="row align-items-center">
                  <div class="col-md-12 float-end">
                      {{ $roomAmenities->links('vendor.pagination.bootstrap-5') }}
                  </div>
              </div>
            @else
              <div class="text-center py-5">
                <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No Room Amenities Found</h5>
                <p class="text-muted">Create your first room amenity to get started.</p>
                @can('create room amenities')
                <a href="{{ route('tenant.room-amenities.create', ['property_id' => request('property_id')]) }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Amenity
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