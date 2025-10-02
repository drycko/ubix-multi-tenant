@extends('tenant.layouts.app')

@section('title', 'Room Rates Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">
          <i class="bi bi-currency-dollar"></i>
          <small class="text-muted"> Room Rates Management</small>
        </h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Room Rates</li>
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
                  <i class="bi bi-currency-dollar"></i> All Room Rates
                  <span class="badge bg-primary">{{ $roomRates->total() }}</span>
                </h5>
              </div>
              <div>
                @can('create room rates')
                <a href="{{ route('tenant.room-rates.import') }}" class="btn btn-outline-info me-2">
                  <i class="bi bi-upload"></i> Import Rates
                </a>
                <a href="{{ route('tenant.room-rates.create', ['property_id' => request('property_id')]) }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Room Rate
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
            <form method="GET" action="{{ route('tenant.room-rates.index') }}" class="row g-3">
              <input type="hidden" name="property_id" value="{{ request('property_id') }}">
              
              <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search rates or room types...">
              </div>
              
              <div class="col-md-2">
                <label for="rate_type" class="form-label">Rate Type</label>
                <select class="form-select" id="rate_type" name="rate_type">
                  <option value="">All Types</option>
                  <option value="standard" {{ request('rate_type') === 'standard' ? 'selected' : '' }}>Standard</option>
                  <option value="off_season" {{ request('rate_type') === 'off_season' ? 'selected' : '' }}>Off Season</option>
                  <option value="package" {{ request('rate_type') === 'package' ? 'selected' : '' }}>Package</option>
                </select>
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
                  <a href="{{ route('tenant.room-rates.index', ['property_id' => request('property_id')]) }}" 
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

    <!-- Room Rates Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            @if($roomRates->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Rate Details</th>
                      <th class="text-center">Room Type</th>
                      <th class="text-center">Amount</th>
                      <th class="text-center">Effective Period</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($roomRates as $roomRate)
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                              <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                   style="width: 40px; height: 40px;">
                                <i class="bi bi-currency-dollar text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="mb-0">{{ $roomRate->name }}</h6>
                              <small class="text-muted">
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $roomRate->rate_type)) }}</span>
                                @if($roomRate->is_shared)
                                  <span class="badge bg-warning">Shared</span>
                                @endif
                              </small>
                              @if($roomRate->min_nights || $roomRate->max_nights)
                                <br><small class="text-muted">
                                  @if($roomRate->min_nights && $roomRate->max_nights)
                                    {{ $roomRate->min_nights }}-{{ $roomRate->max_nights }} nights
                                  @elseif($roomRate->min_nights)
                                    Min {{ $roomRate->min_nights }} nights
                                  @elseif($roomRate->max_nights)
                                    Max {{ $roomRate->max_nights }} nights
                                  @endif
                                </small>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td class="text-center">
                          <span class="badge bg-secondary">{{ $roomRate->roomType->name ?? 'N/A' }}</span>
                          @if($roomRate->roomType)
                            <br><small class="text-muted">{{ $roomRate->roomType->code }}</small>
                          @endif
                        </td>
                        <td class="text-center">
                          <div class="fw-bold text-success">R{{ number_format($roomRate->amount, 2) }}</div>
                          <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $roomRate->rate_type)) }}</small>
                        </td>
                        <td class="text-center">
                          <div>
                            <small class="text-muted">From:</small><br>
                            <span class="badge bg-info">{{ $roomRate->effective_from->format('M d, Y') }}</span>
                          </div>
                          @if($roomRate->effective_until)
                            <div class="mt-1">
                              <small class="text-muted">Until:</small><br>
                              <span class="badge bg-warning">{{ $roomRate->effective_until->format('M d, Y') }}</span>
                            </div>
                          @else
                            <div class="mt-1">
                              <span class="badge bg-success">Ongoing</span>
                            </div>
                          @endif
                        </td>
                        <td class="text-center">
                          @if($roomRate->is_active)
                            <span class="badge bg-success">Active</span>
                          @else
                            <span class="badge bg-danger">Inactive</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <div class="btn-group" role="group">
                            <!-- View -->
                            <a href="{{ route('tenant.room-rates.show', $roomRate) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                              <i class="bi bi-eye"></i>
                            </a>
                            
                            <!-- Edit -->
                            @can('edit room rates')
                            <a href="{{ route('tenant.room-rates.edit', $roomRate) }}" 
                               class="btn btn-sm btn-outline-warning" title="Edit">
                              <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            
                            <!-- Toggle Status -->
                            @can('edit room rates')
                            <form action="{{ route('tenant.room-rates.toggle-status', $roomRate) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" 
                                      class="btn btn-sm btn-outline-{{ $roomRate->is_active ? 'danger' : 'success' }}" 
                                      title="{{ $roomRate->is_active ? 'Deactivate' : 'Activate' }}"
                                      onclick="return confirm('Are you sure you want to {{ $roomRate->is_active ? 'deactivate' : 'activate' }} this room rate?')">
                                <i class="bi bi-{{ $roomRate->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                              </button>
                            </form>
                            @endcan
                            
                            <!-- Clone -->
                            @can('create room rates')
                            <form action="{{ route('tenant.room-rates.clone', $roomRate) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-outline-secondary" title="Clone"
                                      onclick="return confirm('Clone this room rate?')">
                                <i class="bi bi-files"></i>
                              </button>
                            </form>
                            @endcan
                            
                            <!-- Delete -->
                            @can('delete room rates')
                            <form action="{{ route('tenant.room-rates.destroy', $roomRate) }}" 
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
              @if($roomRates->hasPages())
              <div class="container-fluid py-3">
                  <div class="row align-items-center">
                      <div class="col-md-12 float-end">
                          {{ $roomRates->links('vendor.pagination.bootstrap-5') }}
                      </div>
                  </div>
              </div>
              @endif
            @else
              <div class="text-center py-5">
                <i class="bi bi-currency-dollar text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No Room Rates Found</h5>
                <p class="text-muted">Create your first room rate to get started.</p>
                @can('edit room rates')
                <a href="{{ route('tenant.room-rates.create', ['property_id' => request('property_id')]) }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Room Rate
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