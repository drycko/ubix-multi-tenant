@extends('tenant.layouts.app')

@section('title', 'Statistics & Analytics')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>Statistics & Analytics
                    @if($viewingAllProperties)
                        <span class="badge bg-primary ms-2">All Properties</span>
                    @elseif($propertyStats)
                        <span class="badge bg-info ms-2">{{ $propertyStats['name'] }}</span>
                    @endif
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Statistics</li>
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

        <!-- Date Range Filter & Export Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel me-2"></i>Filters & Export
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tenant.stats.index') }}" method="GET" id="filterForm">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-search me-1"></i>Apply Filter
                                    </button>
                                    <a href="{{ route('tenant.stats.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDateRange(7)">Last 7 Days</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDateRange(30)">Last 30 Days</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDateRange(90)">Last 90 Days</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDateRange('month')">This Month</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDateRange('year')">This Year</button>
                                    </div>
                                    <div class="btn-group float-end" role="group">
                                        <a href="{{ route('tenant.stats.print', request()->query()) }}" class="btn btn-sm btn-success" target="_blank">
                                            <i class="bi bi-printer me-1"></i>Print Report
                                        </a>
                                        <a href="{{ route('tenant.stats.download', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-sm btn-danger">
                                            <i class="bi bi-file-pdf me-1"></i>Download PDF
                                        </a>
                                        <a href="{{ route('tenant.stats.download', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Download CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison Alert -->
        @if($monthlyComparison)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="bi bi-calendar-range me-2"></i>Monthly Comparison</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Bookings:</strong> {{ $monthlyComparison['current_month_bookings'] }} this month
                            @if($monthlyComparison['bookings_change'] != 0)
                                <span class="badge bg-{{ $monthlyComparison['bookings_change'] > 0 ? 'success' : 'danger' }}">
                                    <i class="bi bi-arrow-{{ $monthlyComparison['bookings_change'] > 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($monthlyComparison['bookings_change']) }}%
                                </span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Revenue:</strong> R {{ number_format($monthlyComparison['current_month_revenue'], 2) }} this month
                            @if($monthlyComparison['revenue_change'] != 0)
                                <span class="badge bg-{{ $monthlyComparison['revenue_change'] > 0 ? 'success' : 'danger' }}">
                                    <i class="bi bi-arrow-{{ $monthlyComparison['revenue_change'] > 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($monthlyComparison['revenue_change']) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Overview Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_bookings']) }}</h3>
                        <p>Total Bookings</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ number_format($stats['active_bookings']) }}</h3>
                        <p>Active Bookings</p>
                    </div>
                    <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>R {{ number_format($stats['total_revenue'], 0) }}</h3>
                        <p>Total Revenue</p>
                    </div>
                    <i class="bi bi-cash-stack small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.payments.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_guests']) }}</h3>
                        <p>Total Guests</p>
                    </div>
                    <i class="bi bi-people small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.guests.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-secondary">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_rooms']) }}</h3>
                        <p>Total Rooms</p>
                    </div>
                    <i class="bi bi-house small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.rooms.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-light text-dark">
                    <div class="inner">
                        <h3>{{ number_format($stats['available_rooms']) }}</h3>
                        <p>Available Rooms</p>
                    </div>
                    <i class="bi bi-door-open small-box-icon" style="font-size:2.5rem; opacity:0.3;"></i>
                    <a href="{{ route('tenant.rooms.index') }}" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($stats['cancelled_bookings']) }}</h3>
                        <p>Cancelled Bookings</p>
                    </div>
                    <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="small-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="inner">
                        <h3>R {{ number_format($stats['pending_revenue'], 0) }}</h3>
                        <p>Pending Revenue</p>
                    </div>
                    <i class="bi bi-hourglass-split small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.booking-invoices.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Details <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <!-- Revenue Over Time Chart -->
            <div class="col-lg-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-cash-coin me-2"></i>Revenue Trend
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#revenueChartCard">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="revenueChartCard">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Bookings Over Time Chart -->
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Booking Trends
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#bookingsTrendCard">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="bookingsTrendCard">
                        <div class="chart-container" style="position: relative; height: 350px;">
                            <canvas id="bookingsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Type Distribution -->
            <div class="col-lg-4">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pie-chart me-2"></i>Room Type Distribution
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#roomTypeCard">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="roomTypeCard">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="roomTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Analytics -->
        <div class="row mb-4">
            <!-- Arrivals Chart -->
            <div class="col-lg-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Daily Arrivals
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#arrivalsCard">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="arrivalsCard">
                        <div class="chart-container" style="position: relative; height: 250px;">
                            <canvas id="bookingsArrivalChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Occupancy Rate Gauge -->
            <div class="col-lg-6">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Current Occupancy Rate
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#occupancyCard">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="occupancyCard">
                        <div class="chart-container" style="position: relative; height: 250px;">
                            <canvas id="occupancyChart"></canvas>
                        </div>
                        <div class="text-center mt-3">
                            @php
                                $occupancyRate = $stats['total_rooms'] > 0 ? round(($stats['active_bookings'] / $stats['total_rooms']) * 100, 1) : 0;
                            @endphp
                            <h4 class="text-{{ $occupancyRate > 80 ? 'success' : ($occupancyRate > 50 ? 'warning' : 'danger') }}">
                                {{ $occupancyRate }}% Occupied
                            </h4>
                            <p class="text-muted">{{ $stats['active_bookings'] }} of {{ $stats['total_rooms'] }} rooms</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>Performance Metrics
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#metricsCard">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="metricsCard">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="metric-card bg-gradient-primary text-white p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-white-50 mb-1">Avg Booking Duration</h6>
                                            <h4 class="mb-0">{{ round($avgBookingDuration, 1) }} nights</h4>
                                        </div>
                                        <i class="bi bi-moon-stars fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card bg-gradient-success text-white p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-white-50 mb-1">Occupancy Rate</h6>
                                            <h4 class="mb-0">{{ $occupancyRate }}%</h4>
                                        </div>
                                        <i class="bi bi-speedometer2 fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card bg-gradient-info text-white p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-white-50 mb-1">Available Rooms</h6>
                                            <h4 class="mb-0">{{ number_format($stats['available_rooms']) }}</h4>
                                        </div>
                                        <i class="bi bi-door-open fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card bg-gradient-warning text-white p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-white-50 mb-1">Rooms in Use</h6>
                                            @php
                                                $roomsInUse = $stats['total_rooms'] - $stats['available_rooms'];
                                            @endphp
                                            <h4 class="mb-0">{{ number_format($roomsInUse) }}</h4>
                                        </div>
                                        <i class="bi bi-house-check fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('tenant.bookings.create') }}" class="btn btn-outline-success w-100 py-3">
                                    <i class="bi bi-plus-circle me-2"></i>New Booking
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('tenant.rooms.create') }}" class="btn btn-outline-primary w-100 py-3">
                                    <i class="bi bi-house-add me-2"></i>Add Room
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('tenant.guests.create') }}" class="btn btn-outline-info w-100 py-3">
                                    <i class="bi bi-person-plus me-2"></i>Add Guest
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary w-100 py-3">
                                    <i class="bi bi-house-door me-2"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
// Date range helper functions
function setDateRange(range) {
    const today = new Date();
    let startDate, endDate = today;
    
    if (range === 'month') {
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
    } else if (range === 'year') {
        startDate = new Date(today.getFullYear(), 0, 1);
    } else {
        startDate = new Date(today);
        startDate.setDate(today.getDate() - range);
    }
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    document.getElementById('filterForm').submit();
}

document.addEventListener('DOMContentLoaded', function () {
    // Chart.js default configuration
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;

    // Color palette
    const colors = {
        primary: '#0d6efd',
        success: '#198754',
        danger: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0',
        light: '#f8f9fa',
        dark: '#212529'
    };

    // Revenue Over Time Chart
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($revenueOverTime['data'] ?? []);
    const revenueLabels = @json($revenueOverTime['labels'] ?? []);
    
    new Chart(ctxRevenue, {
        type: 'bar',
        data: {
            labels: revenueLabels.map(date => new Date(date)),
            datasets: [{
                label: 'Revenue (R)',
                data: revenueData,
                backgroundColor: colors.success + '80',
                borderColor: colors.success,
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
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
                    borderColor: colors.success,
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: R ' + context.parsed.y.toFixed(2);
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
                        callback: function(value) {
                            return 'R ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Bookings Over Time Line Chart
    const ctx = document.getElementById('bookingsChart').getContext('2d');
    const bookingsData = @json($bookingsOverTime['data'] ?? []);
    const bookingsLabels = @json($bookingsOverTime['labels'] ?? []);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: bookingsLabels.map(date => new Date(date)),
            datasets: [{
                label: 'Bookings Created',
                data: bookingsData,
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: colors.primary,
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
                    borderColor: colors.primary,
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
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

    // Arrivals Bar Chart
    const ctxArrival = document.getElementById('bookingsArrivalChart').getContext('2d');
    const bookingsArrivalData = @json($bookingsByArrivalDate['data'] ?? []);
    const bookingsArrivalLabels = @json($bookingsByArrivalDate['labels'] ?? []);
    
    new Chart(ctxArrival, {
        type: 'bar',
        data: {
            labels: bookingsArrivalLabels.map(date => new Date(date)),
            datasets: [{
                label: 'Arrivals',
                data: bookingsArrivalData,
                backgroundColor: colors.info,
                borderColor: colors.info,
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: colors.info,
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
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

    // Room Type Pie Chart
    const ctxRoomType = document.getElementById('roomTypeChart').getContext('2d');
    const roomTypeLabels = @json($bookingsByRoomType['labels'] ?? []);
    const roomTypeData = @json($bookingsByRoomType['data'] ?? []);
    
    const pieColors = [colors.primary, colors.success, colors.warning, colors.info, colors.danger];
    
    new Chart(ctxRoomType, {
        type: 'doughnut',
        data: {
            labels: roomTypeLabels,
            datasets: [{
                data: roomTypeData,
                backgroundColor: pieColors.slice(0, roomTypeData.length),
                borderColor: '#fff',
                borderWidth: 2,
                hoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        boxWidth: 12
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Occupancy Gauge Chart
    const ctxOccupancy = document.getElementById('occupancyChart').getContext('2d');
    const occupancyRate = {{ $occupancyRate ?? 0 }};
    
    new Chart(ctxOccupancy, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [occupancyRate, 100 - occupancyRate],
                backgroundColor: [
                    occupancyRate > 80 ? colors.success : (occupancyRate > 50 ? colors.warning : colors.danger),
                    '#e9ecef'
                ],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            },
            elements: {
                center: {
                    text: occupancyRate + '%',
                    color: occupancyRate > 80 ? colors.success : (occupancyRate > 50 ? colors.warning : colors.danger),
                    fontStyle: 'bold',
                    sidePadding: 20,
                    minFontSize: 25,
                    lineHeight: 25
                }
            }
        },
        plugins: [{
            beforeDraw: function(chart) {
                const width = chart.width;
                const height = chart.height;
                const ctx = chart.ctx;
                ctx.restore();
                const fontSize = (height / 114).toFixed(2);
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "top";
                const text = occupancyRate + '%';
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;
                ctx.fillStyle = occupancyRate > 80 ? colors.success : (occupancyRate > 50 ? colors.warning : colors.danger);
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
});
</script>
@endpush