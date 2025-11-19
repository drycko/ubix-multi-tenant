@extends('central.layouts.app')

@section('title', 'Admin Activity Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Admin Activity Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('central.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Admin Activity</li>
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
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary['total_activities']) }}</h3>
                        <p>Total Activities</p>
                    </div>
                    <i class="bi bi-activity small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>{{ number_format($summary['unique_users']) }}</h3>
                        <p>Active Admins</p>
                    </div>
                    <i class="bi bi-people small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ number_format($summary['activities_today']) }}</h3>
                        <p>Today</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $summary['top_actions']->count() }}</h3>
                        <p>Action Types</p>
                    </div>
                    <i class="bi bi-list-ul small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
        </div>

        <!-- Top Actions -->
        @if($summary['top_actions']->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Top Admin Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($summary['top_actions'] as $action => $count)
                            <div class="col-md-2-4">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon"><i class="bi bi-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ ucfirst($action) }}</span>
                                        <span class="info-box-number">{{ number_format($count) }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
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
                            <a href="{{ route('central.reports.export', 'csv') }}?report_type=user_activity&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
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
                            <i class="bi bi-filter me-2"></i>Filter Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('central.reports.user-activity') }}" method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="dateFrom" class="form-label">Date From</label>
                                        <input type="date" class="form-control" id="dateFrom" name="date_from" 
                                               value="{{ request('date_from') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="dateTo" class="form-label">Date To</label>
                                        <input type="date" class="form-control" id="dateTo" name="date_to" 
                                               value="{{ request('date_to') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="userId" class="form-label">Admin User</label>
                                        <select class="form-select" id="userId" name="user_id">
                                            <option value="">All Admins</option>
                                            @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="activityType" class="form-label">Activity Type</label>
                                        <select class="form-select" id="activityType" name="activity_type">
                                            <option value="">All Types</option>
                                            <option value="create" {{ request('activity_type') === 'create' ? 'selected' : '' }}>Create</option>
                                            <option value="update" {{ request('activity_type') === 'update' ? 'selected' : '' }}>Update</option>
                                            <option value="delete" {{ request('activity_type') === 'delete' ? 'selected' : '' }}>Delete</option>
                                            <option value="view" {{ request('activity_type') === 'view' ? 'selected' : '' }}>View</option>
                                            <option value="export" {{ request('activity_type') === 'export' ? 'selected' : '' }}>Export</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search me-2"></i>Filter
                                            </button>
                                            <a href="{{ route('central.reports.user-activity') }}" class="btn btn-secondary">
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

        <!-- Activities Table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-info card-outline">
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
                                        <th>Admin</th>
                                        <th>Action</th>
                                        <th>Table</th>
                                        <th>Record ID</th>
                                        <th>Description</th>
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
                                            <span class="badge bg-{{ 
                                                $activity->activity_type === 'create' ? 'success' : 
                                                ($activity->activity_type === 'update' ? 'warning' : 
                                                ($activity->activity_type === 'delete' ? 'danger' : 'info'))
                                            }}">{{ ucfirst($activity->activity_type) }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $activity->table_name ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <code>{{ $activity->record_id ?? '—' }}</code>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($activity->description, 60) }}</small>
                                        </td>
                                        <td>
                                            @if($activity->ip_address)
                                            <code>{{ $activity->ip_address }}</code>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $activity->created_at->format('M d, H:i') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No activities found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $activities->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

@endsection
