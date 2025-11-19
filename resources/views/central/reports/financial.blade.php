@extends('central.layouts.app')

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
                    <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('central.reports.index') }}">Reports</a></li>
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

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>R{{ number_format($summary['total_revenue'], 2) }}</h3>
                        <p>Total Revenue</p>
                    </div>
                    <i class="bi bi-currency-dollar small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary['total_payments']) }}</h3>
                        <p>Total Payments</p>
                    </div>
                    <i class="bi bi-receipt small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>R{{ number_format($summary['avg_payment_value'], 2) }}</h3>
                        <p>Avg Payment</p>
                    </div>
                    <i class="bi bi-graph-up small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($summary['pending_payments']) }}</h3>
                        <p>Pending</p>
                    </div>
                    <i class="bi bi-clock small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
        </div>

        <!-- Revenue Trends Chart -->
        @if($revenueTrends && $revenueTrends->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Revenue Trends
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueTrendsChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Export Options -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-download me-2"></i>Export Options
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="{{ route('central.reports.export', 'csv') }}?report_type=financial&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Export as CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-filter me-2"></i>Filter Payments
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('central.reports.financial') }}" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="dateFrom" class="form-label">Date From</label>
                                        <input type="date" class="form-control" id="dateFrom" name="date_from" 
                                               value="{{ request('date_from') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="dateTo" class="form-label">Date To</label>
                                        <input type="date" class="form-control" id="dateTo" name="date_to" 
                                               value="{{ request('date_to') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search me-2"></i>Filter
                                            </button>
                                            <a href="{{ route('central.reports.financial') }}" class="btn btn-secondary">
                                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>Payment Details
                        </h5>
                        <div class="card-tools">
                            <span class="badge bg-info">{{ $payments->total() }} payments</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tenant</th>
                                        <th>Subscription</th>
                                        <th>Amount</th>
                                        <th>Payment Date</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 24px; height: 24px;">
                                                    <i class="bi bi-building text-white" style="font-size: 12px;"></i>
                                                </div>
                                                <span>{{ $payment->subscription->tenant->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $payment->subscription->plan->name ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <strong class="text-success">R{{ number_format($payment->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $payment->status === 'completed' ? 'success' : 
                                                ($payment->status === 'pending' ? 'warning' : 'danger')
                                            }}">{{ ucfirst($payment->status) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($payment->payment_method ?? 'N/A') }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No payments found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $payments->appends(request()->query())->links() }}
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
<script>
@if($revenueTrends && $revenueTrends->count() > 0)
// Revenue Trends Chart
const ctx = document.getElementById('revenueTrendsChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($revenueTrends->pluck('date')) !!},
        datasets: [{
            label: 'Revenue (R)',
            data: {!! json_encode($revenueTrends->pluck('revenue')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'R' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush
