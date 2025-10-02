@extends('tenant.layouts.app')

@section('title', 'Rooms Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">
          <i class="bi bi-door-open"></i>
          <small class="text-muted"> Rooms Management</small>
        </h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Rooms</li>
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
                  <i class="bi bi-door-open"></i> All Rooms
                  <span class="badge bg-primary">{{ $rooms->total() }}</span>
                </h5>
              </div>
              <div class="btn-group">
                @can('create rooms')
                <a href="{{ route('tenant.rooms.import') }}" class="btn btn-outline-info me-2">
                  <i class="bi bi-upload"></i> Import Rooms
                </a>
                <a href="{{ route('tenant.rooms.create', ['property_id' => $propertyId]) }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Room
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
            <form method="GET" action="{{ route('tenant.rooms.index') }}" class="row g-3">
              <input type="hidden" name="property_id" value="{{ request('property_id') }}">
              
              <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search rooms by name, code or description...">
              </div>
              
              <div class="col-md-2">
                <label for="room_type" class="form-label">Room Type</label>
                <select class="form-select" id="room_type" name="room_type">
                  <option value="">All Types</option>
                  @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ request('room_type') == $type->id ? 'selected' : '' }}>
                      {{ $type->name }}
                    </option>
                  @endforeach
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

    <!-- Rooms List -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-list"></i> Rooms List
            </h3>
            <div class="card-tools">
              <!-- Search functionality can be added here -->
            </div>
          </div>
          <div class="card-body p-0">
            @if($rooms->count() > 0)
              <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th style="width: 50px;">#</th>
                      <th>Room Details</th>
                      <th style="width: 120px;">Type</th>
                      <th style="width: 100px;">Floor</th>
                      <th style="width: 150px;">Current Rate</th>
                      <th style="width: 100px;">Status</th>
                      <th style="width: 200px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($rooms as $room)
                      <tr>
                        <td>
                          <div class="fw-bold text-primary">#{{ $room->number }}</div>
                          <small class="text-muted">{{ $room->legacy_room_code ?: '-' }}</small>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            @if($room->web_image)
                              @php
                              // if image has https
                              if (Str::startsWith($room->web_image, 'https://')) {
                                $imageUrl = $room->web_image;
                              } elseif (config('app.env') === 'production') {
                                $gcsConfig = config('filesystems.disks.gcs');
                                $bucket = $gcsConfig['bucket'] ?? null;
                                $path = ltrim($room->web_image, '/');
                                $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
                              } else {
                                // For local storage in multi-tenant setup
                                $imageUrl = asset('storage/' . $room->web_image);
                              }
                              @endphp
                              @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $room->name }}" 
                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                              @endif
                            @else
                              <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                   style="width: 60px; height: 60px;">
                                <i class="bi bi-door-open text-white"></i>
                              </div>
                            @endif
                            <div>
                              <div class="fw-bold">{{ $room->name }}</div>
                              <div class="text-muted small">{{ $room->short_code }}</div>
                              @if($room->is_featured)
                                <span class="badge bg-warning text-dark">
                                  <i class="bi bi-star"></i> Featured
                                </span>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td>
                          @if($room->type)
                            <span class="badge bg-info">
                              {{ $room->type->name }}
                            </span>
                            <br>
                            <small class="text-muted">{{ $room->type->legacy_code ?? '' }}</small>
                          @else
                            <span class="text-muted">No Type</span>
                          @endif
                        </td>
                        <td>
                          @if($room->floor)
                            <span class="badge bg-light text-dark">
                              <i class="bi bi-building"></i> {{ $room->floor }}
                            </span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>
                          @if($room->type && $room->type->rates->count() > 0)
                            @php
                              $currentRate = $room->type->rates->first();
                            @endphp
                            <div class="fw-bold text-success">
                              {{ $currency }}{{ number_format($currentRate->amount, 2) }}
                            </div>
                            <small class="text-muted">{{ $currentRate->rate_type }}</small>
                          @else
                            <span class="text-muted">No Rate</span>
                          @endif
                        </td>
                        <td>
                          <span class="badge {{ $room->is_enabled ? 'bg-success' : 'bg-secondary' }}">
                            <i class="bi bi-{{ $room->is_enabled ? 'check-circle' : 'pause-circle' }}"></i>
                            {{ $room->is_enabled ? 'Active' : 'Inactive' }}
                          </span>
                        </td>
                        <td>
                          <div class="btn-group" role="group">
                            @can('view rooms')
                            <a href="{{ route('tenant.rooms.show', $room) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                              <i class="bi bi-eye"></i>
                            </a>
                            @endcan
                            
                            @can('edit rooms')
                            <a href="{{ route('tenant.rooms.edit', $room) }}" 
                               class="btn btn-sm btn-outline-primary" title="Edit Room">
                              <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Toggle Status -->
                            <form action="{{ route('tenant.rooms.toggle-status', $room) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" 
                                      class="btn btn-sm btn-outline-{{ $room->is_enabled ? 'warning' : 'success' }}"
                                      title="{{ $room->is_enabled ? 'Disable' : 'Enable' }} Room"
                                      onclick="return confirm('Are you sure you want to {{ $room->is_enabled ? 'disable' : 'enable' }} this room?')">
                                <i class="bi bi-{{ $room->is_enabled ? 'pause' : 'play' }}"></i>
                              </button>
                            </form>

                            <!-- Clone Room -->
                            <form action="{{ route('tenant.rooms.clone', $room) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                      title="Clone Room"
                                      onclick="return confirm('Clone this room?')">
                                <i class="bi bi-files"></i>
                              </button>
                            </form>

                            <!-- Delete Room -->
                            <form action="{{ route('tenant.rooms.destroy', $room) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" 
                                      title="Delete Room"
                                      onclick="return confirm('Are you sure you want to delete this room? This action cannot be undone.')">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                            @endcan
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center py-4">
                          <div class="text-muted">
                            <i class="bi bi-door-open display-4"></i>
                            <p>No rooms found for this property.</p>
                            @can('create rooms')
                            <a href="{{ route('tenant.rooms.create', ['property_id' => $propertyId]) }}" 
                               class="btn btn-primary">
                              <i class="bi bi-plus-circle"></i> Create First Room
                            </a>
                            @endcan
                          </div>
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              
              {{-- Pagination links --}}
              {{-- Beautiful pagination --}}
              @if($rooms->hasPages())
              <div class="container-fluid py-3">
                <div class="row align-items-center">
                    <div class="col-md-12 float-end">
                        {{ $rooms->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
              </div>
              @endif
            @else
              <div class="text-center py-5">
                <i class="bi bi-door-open display-4 text-muted"></i>
                <h4 class="mt-3">No Rooms Found</h4>
                <p class="text-muted">There are no rooms for this property yet.</p>
                @can('create rooms')
                <a href="{{ route('tenant.rooms.create', ['property_id' => $propertyId]) }}" 
                   class="btn btn-primary">
                  <i class="bi bi-plus-circle"></i> Create First Room
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

@push('scripts')
<script>
  // Auto-refresh functionality if needed
  // You can add real-time updates here
</script>
@endpush
