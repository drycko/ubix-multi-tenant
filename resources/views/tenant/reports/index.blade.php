@extends('tenant.layouts.app')

@section('title', 'Reports Dashboard')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Reports & Analytics</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
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
        @if (empty($stats))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Please select a property to view reports and analytics.
            </div>
        @else
        <!-- Reports Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_bookings']) }}</h3>
                        <p>Total Bookings</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.reports.bookings') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Report <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>R{{ number_format($stats['total_revenue'], 0) }}</h3>
                        <p>Total Revenue</p>
                    </div>
                    <i class="bi bi-currency-dollar small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.reports.financial') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Report <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_guests']) }}</h3>
                        <p>Total Guests</p>
                    </div>
                    <i class="bi bi-people small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.guests.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Guests <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($stats['total_activities']) }}</h3>
                        <p>User Activities</p>
                    </div>
                    <i class="bi bi-activity small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.reports.user-activity') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Report <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Categories -->
        <div class="row mb-4">
            <!-- Booking Reports -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card card-primary card-outline h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Booking Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Analyze booking patterns, trends, and performance metrics.</p>
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-primary mb-1">{{ number_format($stats['bookings_this_month']) }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-secondary mb-1">{{ number_format($stats['avg_booking_value'], 0) }}</h4>
                                    <small class="text-muted">Avg Value</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('tenant.reports.bookings') }}" class="btn btn-primary">
                                <i class="bi bi-graph-up me-2"></i>View Booking Reports
                            </a>
                            <a href="{{ route('tenant.reports.occupancy') }}" class="btn btn-outline-primary">
                                <i class="bi bi-house me-2"></i>Occupancy Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Reports -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card card-success card-outline h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-currency-dollar me-2"></i>Financial Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Track revenue, payments, and financial performance.</p>
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success mb-1">R{{ number_format($stats['revenue_this_month'], 0) }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-secondary mb-1">{{ number_format($stats['avg_booking_value'], 0) }}</h4>
                                    <small class="text-muted">Avg Booking</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('tenant.reports.financial') }}" class="btn btn-success">
                                <i class="bi bi-graph-up-arrow me-2"></i>View Financial Reports
                            </a>
                            <a href="{{ route('tenant.reports.advanced') }}" class="btn btn-outline-success">
                                <i class="bi bi-bar-chart me-2"></i>Advanced Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Activity Reports -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card card-info card-outline h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-activity me-2"></i>User Activity Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Monitor user activities, logins, and system usage.</p>
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-info mb-1">{{ number_format($stats['active_users']) }}</h4>
                                    <small class="text-muted">Active Users</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-secondary mb-1">{{ number_format($stats['total_activities']) }}</h4>
                                    <small class="text-muted">Total Activities</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid">
                            <a href="{{ route('tenant.reports.user-activity') }}" class="btn btn-info">
                                <i class="bi bi-people me-2"></i>View Activity Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export & Tools -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card card-dark card-outline h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-download me-2"></i>Export & Tools
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Export reports and access advanced analytics tools.</p>
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="text-center">
                                    <h6 class="text-primary mb-1">Available Formats</h6>
                                    <div class="d-flex justify-content-center gap-2">
                                        <span class="badge bg-primary">CSV</span>
                                        <span class="badge bg-success">Excel</span>
                                        <span class="badge bg-info">PDF</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="bi bi-file-earmark-arrow-down me-2"></i>Export Reports
                            </button>
                            <a href="{{ route('tenant.stats') }}" class="btn btn-outline-dark">
                                <i class="bi bi-graph-up me-2"></i>Statistics Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent User Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>Recent User Activity
                        </h5>
                        <div class="card-tools">
                            <a href="{{ route('tenant.reports.user-activity') }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-eye me-1"></i>View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivity as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($activity->user && $activity->user->profile_photo_path)
                                                <img src="{{ $activity->user->profile_photo_url }}" alt="{{ $activity->user->name }}" 
                                                     class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                                @else
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 24px; height: 24px;">
                                                    <i class="bi bi-person text-white" style="font-size: 12px;"></i>
                                                </div>
                                                @endif
                                                <span>{{ $activity->user->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $activity->action }}</span>
                                        </td>
                                        <td>
                                            @if($activity->model_type && $activity->model_id)
                                            <small class="text-muted">{{ class_basename($activity->model_type) }} #{{ $activity->model_id }}</small>
                                            @else
                                            <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent activity found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="bi bi-download me-2"></i>Export Reports
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tenant.reports.export', 'csv') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reportType" class="form-label">Report Type</label>
                        <select class="form-select" id="reportType" name="report_type" required>
                            <option value="">Select Report Type</option>
                            <option value="bookings">Booking Reports</option>
                            <option value="financial">Financial Reports</option>
                            <option value="user_activity">User Activity Reports</option>
                            <option value="occupancy">Occupancy Reports</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Export Format</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateFrom" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="dateFrom" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateTo" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="dateTo" name="date_to">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-2"></i>Export Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle export form submission
    $('#exportModal form').on('submit', function(e) {
        const reportType = $('#reportType').val();
        const format = $('#exportFormat').val();
        
        if (!reportType) {
            e.preventDefault();
            alert('Please select a report type');
            return;
        }
        
        // Update form action with selected format
        const actionUrl = "{{ route('tenant.reports.export', '') }}/" + format;
        $(this).attr('action', actionUrl);
    });
});
</script>
@endpush