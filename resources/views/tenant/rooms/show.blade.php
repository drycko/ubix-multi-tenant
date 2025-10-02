@extends('tenant.layouts.app')

@section('title', 'Room Details - ' . $room->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-door-open"></i>
          <small class="text-muted">Room Details</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.rooms.index', ['property_id' => $room->property_id]) }}">Rooms</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $room->name }}</li>
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

    <div class="row">
      <!-- Main Content -->
      <div class="col-md-8">
        <!-- Room Overview -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-info-circle"></i> {{ $room->name }}
            </h3>
            <div class="card-tools">
              <span class="badge bg-{{ $room->is_enabled ? 'success' : 'secondary' }}">
                <i class="bi bi-{{ $room->is_enabled ? 'check-circle' : 'pause-circle' }}"></i>
                {{ $room->is_enabled ? 'Active' : 'Inactive' }}
              </span>
              @if($room->is_featured)
                <span class="badge bg-warning text-dark">
                  <i class="bi bi-star"></i> Featured
                </span>
              @endif
            </div>
          </div>
          <div class="card-body">
            @if($imageUrl)
              <div class="text-center mb-4">
                <img src="{{ $imageUrl }}" alt="{{ $room->name }}" 
                     class="img-fluid rounded" style="max-height: 300px;">
              </div>
            @endif

            <!-- Room Details Grid -->
            <div class="row">
              <div class="col-md-6">
                <h5><i class="bi bi-hash"></i> Basic Information</h5>
                <table class="table table-sm">
                  <tr>
                    <td><strong>Room Number:</strong></td>
                    <td>{{ $room->number }}</td>
                  </tr>
                  <tr>
                    <td><strong>Room Name:</strong></td>
                    <td>{{ $room->name }}</td>
                  </tr>
                  <tr>
                    <td><strong>Short Code:</strong></td>
                    <td><span class="badge bg-light text-dark">{{ $room->short_code }}</span></td>
                  </tr>
                  <tr>
                    <td><strong>Room Type:</strong></td>
                    <td>
                      @if($room->type)
                        <span class="badge bg-info">{{ $room->type->name }}</span>
                        @if($room->type->legacy_code)
                          <small class="text-muted">({{ $room->type->legacy_code }})</small>
                        @endif
                      @else
                        <span class="text-muted">No Type Assigned</span>
                      @endif
                    </td>
                  </tr>
                  @if($room->floor)
                  <tr>
                    <td><strong>Floor:</strong></td>
                    <td><span class="badge bg-light text-dark"><i class="bi bi-building"></i> {{ $room->floor }}</span></td>
                  </tr>
                  @endif
                  @if($room->legacy_room_code)
                  <tr>
                    <td><strong>Legacy Code:</strong></td>
                    <td><span class="badge bg-secondary">{{ $room->legacy_room_code }}</span></td>
                  </tr>
                  @endif
                  <tr>
                    <td><strong>Display Order:</strong></td>
                    <td>{{ $room->display_order }}</td>
                  </tr>
                </table>
              </div>
              
              <div class="col-md-6">
                <h5><i class="bi bi-currency-dollar"></i> Current Rates</h5>
                @if($roomRates->count() > 0)
                  <div class="table-responsive">
                    <table class="table table-sm">
                      @foreach($roomRates as $rate)
                        <tr>
                          <td><strong>{{ $rate->rate_type }}:</strong></td>
                          <td class="text-success fw-bold">{{ $currency }}{{ number_format($rate->amount, 2) }}</td>
                        </tr>
                        <tr>
                          <td colspan="2">
                            <small class="text-muted">
                              <i class="bi bi-calendar-range"></i>
                              {{ $rate->effective_from ? $rate->effective_from->format('M d, Y') : 'No start' }} - 
                              {{ $rate->effective_until ? $rate->effective_until->format('M d, Y') : 'No end' }}
                            </small>
                          </td>
                        </tr>
                        @if(!$loop->last)<tr><td colspan="2"><hr class="my-1"></td></tr>@endif
                      @endforeach
                    </table>
                  </div>
                @else
                  <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No active rates found for this room type.
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- Descriptions -->
        @if($room->description || $room->web_description)
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-card-text"></i> Descriptions
            </h3>
          </div>
          <div class="card-body">
            @if($room->description)
              <div class="mb-3">
                <h6><i class="bi bi-building"></i> Internal Description</h6>
                <div class="border rounded p-3 ">
                  {!! nl2br(e($room->description)) !!}
                </div>
              </div>
            @endif
            
            @if($room->web_description)
              <div class="mb-3">
                <h6><i class="bi bi-globe"></i> Website Description</h6>
                <div class="border rounded p-3 ">
                  {!! nl2br(e($room->web_description)) !!}
                </div>
              </div>
            @endif
          </div>
        </div>
        @endif

        <!-- Notes -->
        @if($room->notes)
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-sticky"></i> Notes
            </h3>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              {!! nl2br(e($room->notes)) !!}
            </div>
          </div>
        </div>
        @endif

        <!-- Recent Bookings -->
        @if($room->bookings && $room->bookings->count() > 0)
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-calendar-check"></i> Recent Bookings
            </h3>
            <div class="card-tools">
              <span class="badge bg-primary">{{ $room->bookings->count() }}</span>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Booking ID</th>
                    <th>Guest</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($room->bookings as $booking)
                    <tr>
                      <td><span class="badge bg-info">#{{ $booking->id }}</span></td>
                      <td>{{ $booking->guest_name ?? 'N/A' }}</td>
                      <td>{{ $booking->check_in_date ? $booking->check_in_date->format('M d, Y') : 'N/A' }}</td>
                      <td>{{ $booking->check_out_date ? $booking->check_out_date->format('M d, Y') : 'N/A' }}</td>
                      <td>
                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                          {{ ucfirst($booking->status ?? 'unknown') }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        @endif
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-lightning"></i> Quick Actions
            </h3>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              @can('edit rooms')
              <a href="{{ route('tenant.rooms.edit', $room) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Room
              </a>
              
              <!-- Toggle Status -->
              <form action="{{ route('tenant.rooms.toggle-status', $room) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" 
                        class="btn btn-outline-{{ $room->is_enabled ? 'warning' : 'success' }} w-100"
                        onclick="return confirm('Are you sure you want to {{ $room->is_enabled ? 'disable' : 'enable' }} this room?')">
                  <i class="bi bi-{{ $room->is_enabled ? 'pause' : 'play' }}"></i>
                  {{ $room->is_enabled ? 'Disable' : 'Enable' }} Room
                </button>
              </form>

              <!-- Clone Room -->
              <form action="{{ route('tenant.rooms.clone', $room) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100" 
                        onclick="return confirm('Clone this room?')">
                  <i class="bi bi-files"></i> Clone Room
                </button>
              </form>
              @endcan

              <a href="{{ route('tenant.rooms.index', ['property_id' => $room->property_id]) }}" 
                 class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Rooms
              </a>
            </div>
          </div>
        </div>

        <!-- Room Statistics -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-graph-up"></i> Room Statistics
            </h3>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <div class="rounded p-3">
                  <div class="h4 mb-0 text-success">{{ $room->bookings ? $room->bookings->count() : 0 }}</div>
                  <small class="text-muted">Total Bookings</small>
                </div>
              </div>
              <div class="col-6">
                <div class="rounded p-3">
                  <div class="h4 mb-0 text-success">
                    @if($roomRates->count() > 0)
                      {{ $currency }}{{ number_format($roomRates->first()->amount, 0) }}
                    @else
                      N/A
                    @endif
                  </div>
                  <small class="text-muted">Current Rate</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Room Property Info -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-building"></i> Property Information
            </h3>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td><strong>Property:</strong></td>
                <td>{{ $room->property->name ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Location:</strong></td>
                <td>{{ $room->property->address ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Currency:</strong></td>
                <td><span class="badge bg-info">{{ $currency }}</span></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Danger Zone -->
        @can('delete rooms')
        <div class="card mt-3 border-danger">
          <div class="card-header bg-danger text-white">
            <h3 class="card-title">
              <i class="bi bi-exclamation-triangle"></i> Danger Zone
            </h3>
          </div>
          <div class="card-body">
            <p class="text-muted">Once you delete a room, there is no going back. Please be certain.</p>
            <form action="{{ route('tenant.rooms.destroy', $room) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger w-100" 
                      onclick="return confirm('Are you sure you want to delete this room? This action cannot be undone.')">
                <i class="bi bi-trash"></i> Delete Room
              </button>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>
@endsection