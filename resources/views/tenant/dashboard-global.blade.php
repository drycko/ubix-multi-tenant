@extends('tenant.layouts.app')

@section('title', 'Global Dashboard')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-globe"></i> 
          <small class="text-muted">All Properties Overview</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Global Dashboard</li>
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
    
    <!-- Global Stats Overview -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-bar-chart"></i> Portfolio Overview
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-2 col-6">
                <div class="text-center">
                  <div class="h3 text-success mb-1">{{ $stats['total_properties'] }}</div>
                  <small class="text-muted">Properties</small>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="text-center">
                  <div class="h3 text-success mb-1">{{ $stats['total_bookings'] }}</div>
                  <small class="text-muted">Total Bookings</small>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="text-center">
                  <div class="h3 text-info mb-1">{{ $stats['total_rooms'] }}</div>
                  <small class="text-muted">Total Rooms</small>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="text-center">
                  <div class="h3 text-warning mb-1">{{ $stats['total_room_types'] }}</div>
                  <small class="text-muted">Room Types</small>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="text-center">
                  <div class="h3 text-secondary mb-1">{{ $stats['total_guests'] }}</div>
                  <small class="text-muted">Total Guests</small>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="text-center">
                  <div class="h3 text-dark mb-1">{{ $currency }}</div>
                  <small class="text-muted">Base Currency</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Properties Grid -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="bi bi-buildings"></i> Properties Performance
            </h5>
            <small class="text-muted">{{ $properties->count() }} Active Properties</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Property Cards Grid -->
    <div class="row">
      @forelse($properties as $property)
        <div class="col-lg-6 col-xl-4 mb-4">
          <div class="card h-100 shadow-sm">
            <!-- Property Header -->
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                  <i class="bi bi-building text-success"></i>
                  {{ $property['name'] }}
                </h6>
                <a href="{{ route('tenant.dashboard') }}?switch_property={{ $property['id'] }}" 
                   class="btn btn-sm btn-outline-success">
                  <i class="bi bi-arrow-right"></i> Enter
                </a>
              </div>
            </div>

            <!-- Property Stats -->
            <div class="card-body">
              <div class="row g-3">
                <!-- Rooms -->
                <div class="col-6">
                  <div class="text-center p-2 border border-dark rounded">
                    <div class="h4 text-info mb-1">{{ $property['rooms_count'] }}</div>
                    <small class="text-muted">
                      <i class="bi bi-house"></i> Rooms
                    </small>
                  </div>
                </div>
                
                <!-- Bookings -->
                <div class="col-6">
                  <div class="text-center p-2 border border-dark rounded">
                    <div class="h4 text-success mb-1">{{ $property['bookings_count'] }}</div>
                    <small class="text-muted">
                      <i class="bi bi-calendar-check"></i> Bookings
                    </small>
                  </div>
                </div>

                <!-- Guests -->
                <div class="col-6">
                  <div class="text-center p-2 border border-dark rounded">
                    <div class="h4 text-warning mb-1">{{ $property['guests_count'] }}</div>
                    <small class="text-muted">
                      <i class="bi bi-people"></i> Guests
                    </small>
                  </div>
                </div>

                <!-- Performance Score (calculated) -->
                <div class="col-6">
                  <div class="text-center p-2 border border-dark rounded">
                    @php
                      $occupancyRate = $property['rooms_count'] > 0 ? round(($property['bookings_count'] / $property['rooms_count']) * 10, 1) : 0;
                      $scoreColor = $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="h4 text-{{ $scoreColor }} mb-1">{{ $occupancyRate }}%</div>
                    <small class="text-muted">
                      <i class="bi bi-graph-up"></i> Activity
                    </small>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              @if($property['recent_bookings']->count() > 0)
                <div class="mt-3">
                  <h6 class="text-muted mb-2">
                    <i class="bi bi-clock-history"></i> Recent Activity
                  </h6>
                  <div class="list-group list-group-flush">
                    @foreach($property['recent_bookings']->take(2) as $booking)
                      <div class="list-group-item px-0 py-1 border-0">
                        <small class="text-muted">
                          <i class="bi bi-calendar-event"></i>
                          Booking #{{ $booking->bcode ?? $booking->id }} 
                          <span class="text-success">{{ $booking->created_at->diffForHumans() }}</span>
                        </small>
                      </div>
                    @endforeach
                  </div>
                </div>
              @else
                <div class="mt-3 text-center">
                  <small class="text-muted">
                    <i class="bi bi-info-circle"></i> No recent activity
                  </small>
                </div>
              @endif
            </div>

            <!-- Quick Actions -->
            <div class="card-footer bg-white border-top">
              <div class="row g-2">
                <div class="col-6">
                  <a href="{{ route('tenant.bookings.index') }}?switch_property={{ $property['id'] }}" 
                     class="btn btn-sm btn-outline-success w-100">
                    <i class="bi bi-plus-circle"></i> New Booking
                  </a>
                </div>
                <div class="col-6">
                  <a href="{{ route('tenant.rooms.index') }}?switch_property={{ $property['id'] }}" 
                     class="btn btn-sm btn-outline-info w-100">
                    <i class="bi bi-house-gear"></i> Manage Rooms
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="card">
            <div class="card-body text-center py-5">
              <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
              <h5 class="mt-3 text-muted">No Properties Found</h5>
              <p class="text-muted">Create your first property to get started.</p>
              <a href="{{ route('tenant.properties.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Create Property
              </a>
            </div>
          </div>
        </div>
      @endforelse
    </div>

    <!-- Quick Stats Comparison Chart -->
    @if($properties->count() > 1)
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-bar-chart-line"></i> Properties Comparison
            </h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Property</th>
                    <th class="text-center">Rooms</th>
                    <th class="text-center">Bookings</th>
                    <th class="text-center">Guests</th>
                    <th class="text-center">Activity Score</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($properties as $property)
                    <tr>
                      <td>
                        <strong>{{ $property['name'] }}</strong>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-info">{{ $property['rooms_count'] }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-success">{{ $property['bookings_count'] }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-warning">{{ $property['guests_count'] }}</span>
                      </td>
                      <td class="text-center">
                        @php
                          $score = $property['rooms_count'] > 0 ? round(($property['bookings_count'] / $property['rooms_count']) * 10, 1) : 0;
                          $scoreColor = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                        @endphp
                        <span class="badge bg-{{ $scoreColor }}">{{ $score }}%</span>
                      </td>
                      <td class="text-center">
                        <a href="{{ route('tenant.dashboard') }}?switch_property={{ $property['id'] }}" 
                           class="btn btn-sm btn-outline-success">
                          View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
@endsection

@push('styles')
<style>
.card {
  transition: transform 0.2s ease-in-out;
}
.card:hover {
  transform: translateY(-2px);
}
.property-card .card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}
.activity-score-high { color: #28a745; }
.activity-score-medium { color: #ffc107; }
.activity-score-low { color: #dc3545; }
</style>
@endpush