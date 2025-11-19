@extends('tenant.layouts.app')

@section('title', 'Occupancy Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Occupancy Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Occupancy</li>
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
        <!-- Check if a property is selected -->
        @if (empty($occupancyData))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Please select a property to view occupancy reports.
            </div>
        @else
        <!-- Occupancy Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Daily Occupancy Trends
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#occupancyChart">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="occupancyChart">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="occupancyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card card-secondary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-range me-2"></i>Date Range & Property Filter
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filtersCard">
                        <i class="bi bi-dash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapse show" id="filtersCard">
                <form method="GET" action="{{ route('tenant.reports.occupancy') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from', now()->subDays(30)->toDateString()) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to', now()->toDateString()) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="property_id" class="form-label">Property</label>
                                <select class="form-select" id="property_id" name="property_id">
                                    <option value="">Current Property</option>
                                    @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i>Apply Filter
                            </button>
                            <a href="{{ route('tenant.reports.occupancy') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Performance -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-house me-2"></i>Top Performing Rooms
                        </h5>
                        <div class="card-tools">
                            <span class="badge bg-info">{{ $roomPerformance->count() }} rooms</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Room</th>
                                        <th>Type</th>
                                        <th>Bookings</th>
                                        <th>Avg Nights</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roomPerformance as $room)
                                    <tr>
                                        <td>
                                            <strong>Room {{ str_pad($room->room->number ?? 'N/A', 3, '0', STR_PAD_LEFT) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $room->room->type->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ number_format($room->booking_count) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ number_format($room->avg_nights, 1) }} nights</span>
                                        </td>
                                        <td>
                                            @php
                                                $performance = $room->booking_count;
                                                $performanceClass = $performance >= 10 ? 'success' : ($performance >= 5 ? 'warning' : 'danger');
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $performanceClass }}" role="progressbar" 
                                                     style="width: {{ min(($performance / 15) * 100, 100) }}%;" 
                                                     aria-valuenow="{{ $performance }}" aria-valuemin="0" aria-valuemax="15">
                                                    {{ $performance }} bookings
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-house-x display-6 d-block mb-2"></i>
                                                No room performance data available for the selected period.
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Occupancy Statistics -->
                <div class="card card-info card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pie-chart me-2"></i>Occupancy Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalBookings = $occupancyData->sum('bookings');
                            $totalDays = $occupancyData->count();
                            $avgDaily = $totalDays > 0 ? round($totalBookings / $totalDays, 1) : 0;
                            $peakDay = $occupancyData->sortByDesc('bookings')->first();
                        @endphp
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="metric-card bg-gradient-primary text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ number_format($totalBookings) }}</h5>
                                        <small class="text-white-50">Total Arrivals</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="metric-card bg-gradient-success text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ $avgDaily }}</h5>
                                        <small class="text-white-50">Avg Daily Arrivals</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="metric-card bg-gradient-warning text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ $peakDay ? \Carbon\Carbon::parse($peakDay->date)->format('M d') : 'N/A' }}</h5>
                                        <small class="text-white-50">Peak Day</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-calendar-check me-2"></i>View All Bookings
                            </a>
                            <a href="{{ route('tenant.rooms.index') }}" class="btn btn-outline-success">
                                <i class="bi bi-house me-2"></i>Manage Rooms
                            </a>
                            <a href="{{ route('tenant.reports.bookings') }}" class="btn btn-outline-info">
                                <i class="bi bi-graph-up me-2"></i>Booking Reports
                            </a>
                            <a href="{{ route('tenant.reports.export', 'csv') }}?report_type=occupancy&{{ request()->getQueryString() }}" class="btn btn-outline-secondary">
                                <i class="bi bi-download me-2"></i>Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Occupancy Trends Chart
    const ctx = document.getElementById('occupancyTrendsChart').getContext('2d');
    const occupancyData = @json($occupancyData);
    
    const chartLabels = occupancyData.map(item => new Date(item.date));
    const chartData = occupancyData.map(item => parseInt(item.bookings) || 0);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Daily Arrivals',
                data: chartData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Arrivals: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: 'MMM dd'
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush