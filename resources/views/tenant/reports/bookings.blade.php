@extends('tenant.layouts.app')

@section('title', 'Booking Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Booking Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Bookings</li>
                </ol>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <!--begin::Container-->
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
        @if (empty(current_property()))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Please select a property to view booking reports.
            </div>
        @else
        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary['total_bookings']) }}</h3>
                        <p>Total Bookings</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ number_format($summary['confirmed_bookings']) }}</h3>
                        <p>Confirmed</p>
                    </div>
                    <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($summary['pending_bookings']) }}</h3>
                        <p>Pending</p>
                    </div>
                    <i class="bi bi-clock small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($summary['cancelled_bookings']) }}</h3>
                        <p>Cancelled</p>
                    </div>
                    <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Booking Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="metric-card bg-gradient-info text-white p-3 rounded">
                                    <div class="text-center">
                                        <h4 class="mb-1">{{ number_format($summary['avg_nights'], 1) }}</h4>
                                        <small class="text-white-50">Average Nights</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card bg-gradient-success text-white p-3 rounded">
                                    <div class="text-center">
                                        <h4 class="mb-1">{{ number_format($summary['total_guests']) }}</h4>
                                        <small class="text-white-50">Total Guests</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card bg-gradient-primary text-white p-3 rounded">
                                    <div class="text-center">
                                        @php
                                            $avgGuests = $summary['total_bookings'] > 0 ? round($summary['total_guests'] / $summary['total_bookings'], 1) : 0;
                                        @endphp
                                        <h4 class="mb-1">{{ $avgGuests }}</h4>
                                        <small class="text-white-50">Avg Guests/Booking</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-download me-2"></i>Export Options
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('tenant.reports.export', 'csv') }}?report_type=bookings&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Export as CSV
                            </a>
                            <a href="{{ route('tenant.reports.export', 'excel') }}?report_type=bookings&{{ request()->getQueryString() }}" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export as Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filtersCard">
                        <i class="bi bi-dash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapse show" id="filtersCard">
                <form method="GET" action="{{ route('tenant.reports.bookings') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
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
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="room_type_id" class="form-label">Room Type</label>
                                <select class="form-select" id="room_type_id" name="room_type_id">
                                    <option value="">All Room Types</option>
                                    @foreach($roomTypes as $roomType)
                                    <option value="{{ $roomType->id }}" {{ request('room_type_id') == $roomType->id ? 'selected' : '' }}>
                                        {{ $roomType->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('tenant.reports.bookings') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2"></i>Booking Details
                </h5>
                <div class="card-tools">
                    <span class="badge bg-info">{{ $bookings->total() }} total bookings</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Code</th>
                                <th>Guest(s)</th>
                                <th>Room</th>
                                <th>Dates</th>
                                <th>Nights</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->bcode }}</strong>
                                    @if($booking->source)
                                    <br><small class="text-muted">{{ $booking->source }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->guests->count() > 0)
                                    <div>
                                        @foreach($booking->guests->take(2) as $guest)
                                        <div class="small">{{ $guest->first_name }} {{ $guest->last_name }}</div>
                                        @endforeach
                                        @if($booking->guests->count() > 2)
                                        <small class="text-muted">+{{ $booking->guests->count() - 2 }} more</small>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-muted">No guests</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>Room {{ str_pad($booking->room->number, 3, '0', STR_PAD_LEFT) }}</strong>
                                    <br><small class="text-muted">{{ $booking->room->type->name }}</small>
                                </td>
                                <td>
                                    <div>{{ $booking->arrival_date->format('M d, Y') }}</div>
                                    <div>{{ $booking->departure_date->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $booking->arrival_date->diffInDays($booking->departure_date) }} nights
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $booking->status === 'confirmed' ? 'success' : 
                                        ($booking->status === 'pending' ? 'warning' : 
                                        ($booking->status === 'cancelled' ? 'danger' : 'secondary')) 
                                    }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($booking->total_amount)
                                    <strong>R{{ number_format($booking->total_amount, 2) }}</strong>
                                    @if($booking->daily_rate)
                                    <br><small class="text-muted">R{{ number_format($booking->daily_rate, 2) }}/night</small>
                                    @endif
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $booking->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $booking->created_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('tenant.bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
                                        No bookings found matching your criteria.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Pagination links --}}
            {{-- Beautiful pagination --}}
            @if($bookings->hasPages())
            <div class="container-fluid py-3">
                <div class="row align-items-center">
                    <div class="col-md-12 float-end">
                        {{ $bookings->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#status, #property_id, #room_type_id').change(function() {
        // Optional: Auto-submit on change
        // $(this).closest('form').submit();
    });
});
</script>
@endpush