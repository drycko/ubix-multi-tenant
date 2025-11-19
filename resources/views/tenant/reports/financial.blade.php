@extends('tenant.layouts.app')

@section('title', 'Financial Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Financial Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Financial</li>
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
        @if (empty($summary))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Please select a property to view financial reports.
            </div>
        @else
        <!-- Financial Summary -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>R{{ number_format($summary['total_revenue'], 0) }}</h3>
                        <p>Total Revenue</p>
                    </div>
                    <i class="bi bi-currency-dollar small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary['total_bookings']) }}</h3>
                        <p>Revenue Bookings</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>R{{ number_format($summary['avg_booking_value'], 0) }}</h3>
                        <p>Avg Booking Value</p>
                    </div>
                    <i class="bi bi-graph-up small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>R{{ number_format($summary['avg_daily_rate'], 0) }}</h3>
                        <p>Avg Daily Rate</p>
                    </div>
                    <i class="bi bi-cash small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
        </div>

        <!-- Revenue Trends Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Revenue Trends
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#revenueChart">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="revenueChart">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="revenueTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-secondary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Date Range Filter
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filtersCard">
                        <i class="bi bi-dash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapse show" id="filtersCard">
                <form method="GET" action="{{ route('tenant.reports.financial') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search me-1"></i>Apply Filter
                                    </button>
                                    <a href="{{ route('tenant.reports.financial') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Revenue Details -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>Revenue Breakdown
                        </h5>
                        <div class="card-tools">
                            <span class="badge bg-info">{{ $payments->total() }} records</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Booking</th>
                                        <th>Guest</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td>
                                            <div>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</div>
                                            <small class="text-muted">{{ $payment->created_at->format('g:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($payment->invoice && $payment->invoice->booking)
                                            <strong>{{ $payment->invoice->booking->bcode }}</strong>
                                            <br><small class="text-muted">Room {{ $payment->invoice->booking->room->number ?? 'N/A' }}</small>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->invoice && $payment->invoice->booking && $payment->invoice->booking->guests->count() > 0)
                                            {{ $payment->invoice->booking->guests->first()->first_name }} {{ $payment->invoice->booking->guests->first()->last_name }}
                                            @if($payment->invoice->booking->guests->count() > 1)
                                            <br><small class="text-muted">+{{ $payment->invoice->booking->guests->count() - 1 }} more</small>
                                            @endif
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>R{{ number_format($payment->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($payment->payment_method)
                                            <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Paid</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-cash-stack display-6 d-block mb-2"></i>
                                                No payment records found for the selected period.
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
                    @if($payments->hasPages())
                    <div class="container-fluid py-3">
                        <div class="row align-items-center">
                            <div class="col-md-12 float-end">
                                {{ $payments->links('vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Financial Metrics -->
                <div class="card card-info card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calculator me-2"></i>Financial Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="metric-card bg-gradient-primary text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">R{{ number_format($summary['total_revenue'], 2) }}</h5>
                                        <small class="text-white-50">Total Revenue</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="metric-card bg-gradient-success text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">R{{ number_format($summary['avg_booking_value'], 2) }}</h5>
                                        <small class="text-white-50">Average Booking</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="metric-card bg-gradient-warning text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">R{{ number_format($summary['avg_daily_rate'], 2) }}</h5>
                                        <small class="text-white-50">Average Daily Rate</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card card-dark card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-download me-2"></i>Export Financial Data
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('tenant.reports.export', 'csv') }}?report_type=financial&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Export as CSV
                            </a>
                            <a href="{{ route('tenant.reports.export', 'excel') }}?report_type=financial&{{ request()->getQueryString() }}" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export as Excel
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
    // Revenue Trends Chart
    const ctx = document.getElementById('revenueTrendsChart').getContext('2d');
    const revenueTrends = @json($revenueTrends);
    
    const chartLabels = revenueTrends.map(item => new Date(item.date));
    const chartData = revenueTrends.map(item => parseFloat(item.revenue) || 0);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Daily Revenue',
                data: chartData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#28a745',
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
                    borderColor: '#28a745',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: R' + context.parsed.y.toLocaleString('en-ZA', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
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
                            return 'R' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush