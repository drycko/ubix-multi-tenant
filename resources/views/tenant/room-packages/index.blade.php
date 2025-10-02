@extends('tenant.layouts.app')

@section('title', 'Room Packages Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">
          <i class="bi bi-box"></i>
          <small class="text-muted"> Room Packages Management</small>
        </h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Room Packages</li>
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
                  <i class="bi bi-box"></i> All Room Packages
                  <span class="badge bg-primary">{{ $packages->total() }}</span>
                </h5>
              </div>
              <div class="btn-group">
                @can('create packages')
                
                <a href="{{ route('tenant.room-packages.import') }}" class="btn btn-outline-info me-2">
                  <i class="bi bi-upload"></i> Import Packages
                </a>
                <a href="{{ route('tenant.room-packages.create', ['property_id' => $propertyId]) }}" class="btn btn-success">
                  <i class="bi bi-plus-circle"></i> Create Package
                </a>
                @endcan
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- messages from session --}}
    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @elseif(session('error'))
      <div class="alert alert-danger">
        {{ session('error') }}
      </div>
    @endif
    {{-- validation errors --}}
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
            <form method="GET" action="{{ route('tenant.room-packages.index') }}" class="row g-3">
              <input type="hidden" name="property_id" value="{{ request('property_id') }}">
              
              <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search packages by name, subtitle, or description...">
              </div>
              
              <div class="col-md-2">
                <label for="checkin_days" class="form-label">Checkin Days</label>
                <input type="text" class="form-control" id="checkin_days" name="checkin_days" 
                       value="{{ request('checkin_days') }}" placeholder="e.g., Mon,Tue">
                
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
                  <a href="{{ route('tenant.room-packages.index', ['property_id' => request('property_id')]) }}" 
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

    <!-- Packages List -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-list"></i> Packages List
            </h3>
            <div class="card-tools">
              <!-- Search functionality can be added here -->
            </div>
          </div>
          <div class="card-body p-0">
            @if($packages->count() > 0)
              <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th style="width: 50px;">#</th>
                      <th>Package Details</th>
                      <th style="width: 120px;">Nights</th>
                      <th style="width: 150px;">Base Price</th>
                      <th style="width: 120px;">Check-in Days</th>
                      <th style="width: 100px;">Rooms</th>
                      <th style="width: 100px;">Status</th>
                      <th style="width: 200px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($packages as $package)
                      <tr>
                        <td>
                          <div class="fw-bold text-primary">#{{ $package->id }}</div>
                          <small class="text-muted">{{ $package->pkg_id ?: '-' }}</small>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            @if($package->pkg_image)
                              @php
                              if (config('app.env') === 'production') {
                                $gcsConfig = config('filesystems.disks.gcs');
                                $bucket = $gcsConfig['bucket'] ?? null;
                                $path = ltrim($package->pkg_image, '/');
                                $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
                              } else {
                                // For local storage in multi-tenant setup
                                $imageUrl = asset('storage/' . $package->pkg_image);
                              }
                              @endphp
                              @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $package->pkg_name }}" 
                                     class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                              @endif
                            @else
                              <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                   style="width: 60px; height: 60px;">
                                <i class="bi bi-box text-white"></i>
                              </div>
                            @endif
                            <div>
                              <div class="fw-bold">{{ $package->pkg_name }}</div>
                              <div class="text-muted small">{{ $package->pkg_sub_title }}</div>
                              @if($package->pkg_valid_from || $package->pkg_valid_to)
                                <div class="text-info small">
                                  <i class="bi bi-calendar-range"></i>
                                  {{ $package->pkg_valid_from ? $package->pkg_valid_from->format('M d, Y') : 'No start' }} - 
                                  {{ $package->pkg_valid_to ? $package->pkg_valid_to->format('M d, Y') : 'No end' }}
                                </div>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-info">
                            <i class="bi bi-moon"></i> {{ $package->pkg_number_of_nights }}
                          </span>
                        </td>
                        <td>
                          <div class="fw-bold text-success">
                            {{ $currency }}{{ number_format($package->pkg_base_price, 2) }}
                          </div>
                          @if($package->pkg_min_guests || $package->pkg_max_guests)
                            <small class="text-muted">
                              <i class="bi bi-people"></i>
                              {{ $package->pkg_min_guests ?: '1' }}-{{ $package->pkg_max_guests ?: '∞' }} guests
                            </small>
                          @endif
                        </td>
                        <td>
                          @php
                            $checkinDays = is_string($package->pkg_checkin_days) 
                              ? json_decode($package->pkg_checkin_days, true) 
                              : (is_array($package->pkg_checkin_days) ? $package->pkg_checkin_days : []);
                          @endphp
                          @if($checkinDays && count($checkinDays) > 0)
                            @foreach(array_slice($checkinDays, 0, 2) as $day)
                              <span class="badge bg-light text-dark mb-1">{{ substr($day, 0, 3) }}</span>
                            @endforeach
                            @if(count($checkinDays) > 2)
                              <span class="badge bg-secondary">+{{ count($checkinDays) - 2 }}</span>
                            @endif
                          @else
                            <span class="text-muted">No days set</span>
                          @endif
                        </td>
                        <td>
                          <span class="badge bg-primary">
                            <i class="bi bi-door-open"></i> {{ $package->rooms()->count() }}
                          </span>
                        </td>
                        <td>
                          <span class="badge {{ $package->pkg_status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            <i class="bi bi-{{ $package->pkg_status === 'active' ? 'check-circle' : 'pause-circle' }}"></i>
                            {{ ucfirst($package->pkg_status) }}
                          </span>
                        </td>
                        <td>
                          <div class="btn-group" role="group">
                            @can('view packages')
                            <a href="{{ route('tenant.room-packages.show', $package) }}" 
                               class="btn btn-sm btn-outline-info" title="View Details">
                              <i class="bi bi-eye"></i>
                            </a>
                            @endcan
                            
                            @can('create packages')
                            <a href="{{ route('tenant.room-packages.edit', $package) }}" 
                               class="btn btn-sm btn-outline-primary" title="Edit Package">
                              <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Toggle Status -->
                            <form action="{{ route('tenant.room-packages.toggle-status', $package) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" 
                                      class="btn btn-sm btn-outline-{{ $package->pkg_status === 'active' ? 'warning' : 'success' }}"
                                      title="{{ $package->pkg_status === 'active' ? 'Deactivate' : 'Activate' }} Package"
                                      onclick="return confirm('Are you sure you want to {{ $package->pkg_status === 'active' ? 'deactivate' : 'activate' }} this package?')">
                                <i class="bi bi-{{ $package->pkg_status === 'active' ? 'pause' : 'play' }}"></i>
                              </button>
                            </form>

                            <!-- Clone Package -->
                            <form action="{{ route('tenant.room-packages.clone', $package) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                      title="Clone Package"
                                      onclick="return confirm('Clone this package?')">
                                <i class="bi bi-files"></i>
                              </button>
                            </form>

                            <!-- Delete Package -->
                            <form action="{{ route('tenant.room-packages.destroy', $package) }}" 
                                  method="POST" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" 
                                      title="Delete Package"
                                      onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                            @endcan
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="8" class="text-center py-4">
                          <div class="text-muted">
                            <i class="bi bi-box display-4"></i>
                            <p>No packages found for this property.</p>
                            @can('create packages')
                            <a href="{{ route('tenant.room-packages.create', ['property_id' => $propertyId]) }}" 
                               class="btn btn-primary">
                              <i class="bi bi-plus-circle"></i> Create First Package
                            </a>
                            @endcan
                          </div>
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              @if($packages->hasPages())
                <div class="card-footer">
                  <div class="row align-items-center">
                    <div class="col-sm-6">
                      <div class="text-muted">
                        Showing {{ $packages->firstItem() }} to {{ $packages->lastItem() }} of {{ $packages->total() }} packages
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="d-flex justify-content-end">
                        {{ $packages->appends(request()->query())->links() }}
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            @else
              <div class="text-center py-5">
                <i class="bi bi-box display-4 text-muted"></i>
                <h4 class="mt-3">No Packages Found</h4>
                <p class="text-muted">There are no packages for this property yet.</p>
                @can('create packages')
                <a href="{{ route('tenant.room-packages.create', ['property_id' => $propertyId]) }}" 
                   class="btn btn-primary">
                  <i class="bi bi-plus-circle"></i> Create First Package
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