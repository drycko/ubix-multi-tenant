@extends('tenant.layouts.app')

@section('title', 'Package Details')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-eye"></i>
          <small class="text-muted">Package Details</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-packages.index', ['property_id' => $roomPackage->property_id]) }}">Room Packages</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $roomPackage->pkg_name }}</li>
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
    
    {{-- messages from session --}}
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

    <div class="row">
      <!-- Main Content -->
      <div class="col-md-8">
        <!-- Package Overview -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-box"></i> {{ $roomPackage->pkg_name }}
            </h3>
            <div class="card-tools">
              <span class="badge bg-{{ $roomPackage->pkg_status === 'active' ? 'success' : 'secondary' }} fs-6">
                <i class="bi bi-{{ $roomPackage->pkg_status === 'active' ? 'check-circle' : 'pause-circle' }}"></i>
                {{ ucfirst($roomPackage->pkg_status) }}
              </span>
            </div>
          </div>
          <div class="card-body">
            @if($roomPackage->pkg_image)
              @php
              if (config('app.env') === 'production' && config('filesystems.default') === 'gcs') {
                $gcsConfig = config('filesystems.disks.gcs');
                $bucket = $gcsConfig['bucket'] ?? null;
                $path = ltrim($roomPackage->pkg_image, '/');
                $imageUrl = $bucket ? "https://storage.googleapis.com/{$bucket}/{$path}" : null;
              } else {
                // For local storage in multi-tenant setup
                $imageUrl = asset('storage/' . $roomPackage->pkg_image);
              }
              @endphp
              @if($imageUrl)
                <div class="text-center mb-4">
                  <img src="{{ $imageUrl }}" alt="{{ $roomPackage->pkg_name }}" 
                       class="img-fluid rounded" style="max-height: 300px;">
                </div>
              @endif
            @endif

            <div class="row mb-4">
              <div class="col-md-6">
                <h4 class="text-primary">{{ $roomPackage->pkg_name }}</h4>
                <p class="text-muted mb-3">{{ $roomPackage->pkg_sub_title }}</p>
                
                @if($roomPackage->pkg_description)
                  <div class="mb-4 card p-3">
                    <div class="card-header">
                      <h6 class="card-title"><i class="bi bi-file-text"></i> Description</h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        {!! $roomPackage->pkg_description !!}
                      </div>
                    </div>
                  </div>
                @endif
              </div>
              <div class="col-md-6">
                <div class="bg-primary bg-opacity-10 p-3 rounded">
                  <h5 class="text-primary mb-3"><i class="bi bi-info-circle"></i> Package Details</h5>
                  
                  <div class="row g-3">
                    <div class="col-6">
                      <div class="text-center">
                        <div class="h4 text-primary mb-1">
                          <i class="bi bi-moon"></i> {{ $roomPackage->pkg_number_of_nights }}
                        </div>
                        <small class="text-muted">{{ $roomPackage->pkg_number_of_nights == 1 ? 'Night' : 'Nights' }}</small>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="text-center">
                        <div class="h4 text-success mb-1">{{ $currency }}{{ number_format($roomPackage->pkg_base_price, 2) }}</div>
                        <small class="text-muted">Base Price</small>
                      </div>
                    </div>
                    @if($roomPackage->pkg_min_guests || $roomPackage->pkg_max_guests)
                      <div class="col-12">
                        <div class="text-center">
                          <div class="h5 text-info mb-1">
                            <i class="bi bi-people"></i> 
                            {{ $roomPackage->pkg_min_guests ?: '1' }} - {{ $roomPackage->pkg_max_guests ?: '∞' }}
                          </div>
                          <small class="text-muted">Guest Capacity</small>
                        </div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            @if($roomPackage->pkg_valid_from || $roomPackage->pkg_valid_to)
              <div class="alert alert-info">
                <h6><i class="bi bi-calendar-range"></i> Validity Period</h6>
                <strong>From:</strong> {{ $roomPackage->pkg_valid_from ? $roomPackage->pkg_valid_from->format('F j, Y') : 'No start date' }}
                <br>
                <strong>To:</strong> {{ $roomPackage->pkg_valid_to ? $roomPackage->pkg_valid_to->format('F j, Y') : 'No end date' }}
              </div>
            @endif
          </div>
        </div>

        <!-- Package Inclusions & Exclusions -->
        @php
          $inclusions = is_string($roomPackage->pkg_inclusions) 
            ? json_decode($roomPackage->pkg_inclusions, true) ?? []
            : (is_array($roomPackage->pkg_inclusions) ? $roomPackage->pkg_inclusions : []);
          $exclusions = is_string($roomPackage->pkg_exclusions) 
            ? json_decode($roomPackage->pkg_exclusions, true) ?? []
            : (is_array($roomPackage->pkg_exclusions) ? $roomPackage->pkg_exclusions : []);
        @endphp

        @if(count($inclusions) > 0 || count($exclusions) > 0)
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-list-check"></i> Package Details
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                @if(count($inclusions) > 0)
                  <div class="col-md-6">
                    <h6 class="text-success"><i class="bi bi-check-circle"></i> What's Included</h6>
                    <ul class="list-unstyled">
                      @foreach($inclusions as $inclusion)
                        @if(!empty($inclusion))
                          <li class="mb-2">
                            <i class="bi bi-check text-success me-2"></i>{{ $inclusion }}
                          </li>
                        @endif
                      @endforeach
                    </ul>
                  </div>
                @endif

                @if(count($exclusions) > 0)
                  <div class="col-md-6">
                    <h6 class="text-danger"><i class="bi bi-x-circle"></i> What's Not Included</h6>
                    <ul class="list-unstyled">
                      @foreach($exclusions as $exclusion)
                        @if(!empty($exclusion))
                          <li class="mb-2">
                            <i class="bi bi-x text-danger me-2"></i>{{ $exclusion }}
                          </li>
                        @endif
                      @endforeach
                    </ul>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endif

        <!-- Associated Rooms -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-door-open"></i> Associated Rooms
              <span class="badge bg-primary">{{ $roomPackage->rooms->count() }}</span>
            </h3>
          </div>
          <div class="card-body">
            @if($roomPackage->rooms->count() > 0)
              <div class="row">
                @foreach($roomPackage->rooms as $room)
                  <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border">
                      <div class="card-body p-3">
                        <h6 class="card-title mb-2">{{ $room->name }}</h6>
                        <p class="card-text small text-muted mb-2">
                          {{ $room->type->name ?? 'No Room Type' }}
                        </p>
                        @if($room->room_capacity)
                          <span class="badge bg-light text-dark">
                            <i class="bi bi-people"></i> {{ $room->room_capacity }} guests
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center text-muted py-4">
                <i class="bi bi-door-open display-4"></i>
                <p>No rooms associated with this package.</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-gear"></i> Actions
            </h3>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              @can('create packages')
                <a href="{{ route('tenant.room-packages.edit', $roomPackage) }}" 
                   class="btn btn-primary">
                  <i class="bi bi-pencil"></i> Edit Package
                </a>

                <!-- Toggle Status -->
                <form action="{{ route('tenant.room-packages.toggle-status', $roomPackage) }}" 
                      method="POST" class="d-inline">
                  @csrf
                  <button type="submit" 
                          class="btn btn-{{ $roomPackage->pkg_status === 'active' ? 'warning' : 'success' }} w-100"
                          onclick="return confirm('Are you sure you want to {{ $roomPackage->pkg_status === 'active' ? 'deactivate' : 'activate' }} this package?')">
                    <i class="bi bi-{{ $roomPackage->pkg_status === 'active' ? 'pause' : 'play' }}"></i>
                    {{ $roomPackage->pkg_status === 'active' ? 'Deactivate' : 'Activate' }} Package
                  </button>
                </form>

                <!-- Clone Package -->
                <form action="{{ route('tenant.room-packages.clone', $roomPackage) }}" 
                      method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary w-100" 
                          onclick="return confirm('Clone this package?')">
                    <i class="bi bi-files"></i> Clone Package
                  </button>
                </form>

                <hr>

                <!-- Delete Package -->
                <form action="{{ route('tenant.room-packages.destroy', $roomPackage) }}" 
                      method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger w-100" 
                          onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                    <i class="bi bi-trash"></i> Delete Package
                  </button>
                </form>
              @endcan

              <a href="{{ route('tenant.room-packages.index', ['property_id' => $roomPackage->property_id]) }}" 
                 class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Packages
              </a>
            </div>
          </div>
        </div>

        <!-- Check-in Days -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-calendar-week"></i> Check-in Days
            </h3>
          </div>
          <div class="card-body">
            @php
              $checkinDays = is_string($roomPackage->pkg_checkin_days) 
                ? json_decode($roomPackage->pkg_checkin_days, true) 
                : (is_array($roomPackage->pkg_checkin_days) ? $roomPackage->pkg_checkin_days : []);
            @endphp
            
            @if($checkinDays && count($checkinDays) > 0)
              <div class="d-flex flex-wrap gap-2">
                @foreach($checkinDays as $day)
                  <span class="badge bg-primary">{{ $day }}</span>
                @endforeach
              </div>
            @else
              <p class="text-muted mb-0">No check-in days specified.</p>
            @endif
          </div>
        </div>

        <!-- Package Metadata -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-info"></i> Package Information
            </h3>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12">
                <div class="border-bottom pb-2 mb-2">
                  <small class="text-muted">Package ID</small>
                  <div class="fw-bold">{{ $roomPackage->pkg_id ?: 'N/A' }}</div>
                </div>
              </div>
              <div class="col-12">
                <div class="border-bottom pb-2 mb-2">
                  <small class="text-muted">Created</small>
                  <div class="fw-bold">{{ $roomPackage->created_at ? $roomPackage->created_at->format('M j, Y g:i A') : 'N/A' }}</div>
                </div>
              </div>
              <div class="col-12">
                <div class="border-bottom pb-2 mb-2">
                  <small class="text-muted">Last Updated</small>
                  <div class="fw-bold">{{ $roomPackage->updated_at ? $roomPackage->updated_at->format('M j, Y g:i A') : 'N/A' }}</div>
                </div>
              </div>
              @if($roomPackage->pkg_enterby)
                <div class="col-12">
                  <div>
                    <small class="text-muted">Created By</small>
                    <div class="fw-bold">User ID: {{ $roomPackage->pkg_enterby }}</div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Add any specific JavaScript for the show page here
</script>
@endpush