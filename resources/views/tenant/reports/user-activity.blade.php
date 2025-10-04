@extends('tenant.layouts.app')

@section('title', 'User Activity Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">User Activity Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tenant.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Activity</li>
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
        <!-- Activity Summary -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary['total_activities']) }}</h3>
                        <p>Total Activities</p>
                    </div>
                    <i class="bi bi-activity small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ number_format($summary['unique_users']) }}</h3>
                        <p>Active Users</p>
                    </div>
                    <i class="bi bi-people small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>{{ number_format($summary['activities_today']) }}</h3>
                        <p>Today's Activities</p>
                    </div>
                    <i class="bi bi-calendar-day small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        @php
                            $avgDaily = $summary['total_activities'] > 0 ? round($summary['total_activities'] / 30, 1) : 0;
                        @endphp
                        <h3>{{ $avgDaily }}</h3>
                        <p>Avg Daily Activities</p>
                    </div>
                    <i class="bi bi-graph-up small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
        </div>

        <!-- Top Actions Chart -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>Top User Actions
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#topActionsChart">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show" id="topActionsChart">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="actionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ol me-2"></i>Top Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($summary['top_actions'] as $action => $count)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-truncate me-2">{{ $action }}</span>
                            <span class="badge bg-primary">{{ number_format($count) }}</span>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No activity data available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-secondary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Activity Filters
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filtersCard">
                        <i class="bi bi-dash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapse show" id="filtersCard">
                <form method="GET" action="{{ route('tenant.reports.user-activity') }}">
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">User</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="action" class="form-label">Action</label>
                                <input type="text" class="form-control" id="action" name="action" 
                                       value="{{ request('action') }}" placeholder="Search actions...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('tenant.reports.user-activity') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Details -->
        <div class="row">
            <div class="col-lg-9">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>Activity Log
                        </h5>
                        <div class="card-tools">
                            <span class="badge bg-info">{{ $activities->total() }} activities</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Model</th>
                                        <th>Details</th>
                                        <th>IP Address</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($activity->user && $activity->user->profile_photo_path)
                                                <img src="{{ $activity->user->profile_photo_url }}" alt="{{ $activity->user->name }}" 
                                                     class="rounded-circle me-2" width="28" height="28" style="object-fit: cover;">
                                                @else
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 28px; height: 28px;">
                                                    <i class="bi bi-person text-white" style="font-size: 14px;"></i>
                                                </div>
                                                @endif
                                                <div>
                                                    <div class="fw-medium">{{ $activity->user->name ?? 'System' }}</div>
                                                    <small class="text-muted">{{ $activity->user->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                str_contains($activity->action, 'Created') ? 'success' : 
                                                (str_contains($activity->action, 'Updated') ? 'warning' : 
                                                (str_contains($activity->action, 'Deleted') ? 'danger' : 'info')) 
                                            }}">
                                                {{ $activity->action }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($activity->model_type && $activity->model_id)
                                            <div>
                                                <strong>{{ class_basename($activity->model_type) }}</strong>
                                                <br><small class="text-muted">ID: {{ $activity->model_id }}</small>
                                            </div>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->details)
                                            <div style="max-width: 200px;">
                                                <small class="text-muted">{{ Str::limit(json_encode($activity->details), 100) }}</small>
                                            </div>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->ip_address)
                                            <code>{{ $activity->ip_address }}</code>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $activity->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $activity->created_at->format('g:i A') }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-activity display-6 d-block mb-2"></i>
                                                No activity records found matching your criteria.
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
                    @if($activities->hasPages())
                    <div class="container-fluid py-3">
                        <div class="row align-items-center">
                            <div class="col-md-12 float-end">
                                {{ $activities->links('vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-3">
                <!-- Activity Statistics -->
                <div class="card card-warning card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pie-chart me-2"></i>Activity Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="metric-card bg-gradient-primary text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ number_format($summary['total_activities']) }}</h5>
                                        <small class="text-white-50">Total Activities</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="metric-card bg-gradient-success text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ number_format($summary['unique_users']) }}</h5>
                                        <small class="text-white-50">Active Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="metric-card bg-gradient-info text-white p-3 rounded">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ number_format($summary['activities_today']) }}</h5>
                                        <small class="text-white-50">Today's Activities</small>
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
                            <i class="bi bi-download me-2"></i>Export Activity Data
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('tenant.reports.export', 'csv') }}?report_type=user_activity&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Export as CSV
                            </a>
                            <a href="{{ route('tenant.reports.export', 'excel') }}?report_type=user_activity&{{ request()->getQueryString() }}" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export as Excel
                            </a>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Top Actions Chart
    const ctx = document.getElementById('actionsChart').getContext('2d');
    const topActions = @json($summary['top_actions']);
    
    const labels = Object.keys(topActions);
    const data = Object.values(topActions);
    
    const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Activity Count',
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderColor: colors.slice(0, data.length),
                borderWidth: 1,
                borderRadius: 4,
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
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
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