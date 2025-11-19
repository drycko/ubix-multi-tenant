@extends('central.layouts.app')

@section('title', 'Subscription Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Subscription Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('central.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
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
            <div class="col-md-2-4 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary['total_subscriptions']) }}</h3>
                        <p>Total</p>
                    </div>
                    <i class="bi bi-calendar-check small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ number_format($summary['active_subscriptions']) }}</h3>
                        <p>Active</p>
                    </div>
                    <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>{{ number_format($summary['trial_subscriptions']) }}</h3>
                        <p>Trial</p>
                    </div>
                    <i class="bi bi-hourglass-split small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($summary['cancelled_subscriptions']) }}</h3>
                        <p>Cancelled</p>
                    </div>
                    <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
            <div class="col-md-2-4 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($summary['expired_subscriptions']) }}</h3>
                        <p>Expired</p>
                    </div>
                    <i class="bi bi-calendar-x small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                </div>
            </div>
        </div>

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
                            <a href="{{ route('central.reports.export', 'csv') }}?report_type=subscriptions&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
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
                            <i class="bi bi-filter me-2"></i>Filter Subscriptions
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('central.reports.subscriptions') }}" method="GET">
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
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="planId" class="form-label">Plan</label>
                                        <select class="form-select" id="planId" name="plan_id">
                                            <option value="">All Plans</option>
                                            @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                                {{ $plan->name }}
                                            </option>
                                            @endforeach
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
                                            <a href="{{ route('central.reports.subscriptions') }}" class="btn btn-secondary">
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

        <!-- Subscriptions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>Subscription Details
                        </h5>
                        <div class="card-tools">
                            <span class="badge bg-info">{{ $subscriptions->total() }} subscriptions</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tenant</th>
                                        <th>Plan</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subscriptions as $subscription)
                                    <tr>
                                        <td>{{ $subscription->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 24px; height: 24px;">
                                                    <i class="bi bi-building text-white" style="font-size: 12px;"></i>
                                                </div>
                                                <span>{{ $subscription->tenant->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $subscription->plan->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $subscription->status === 'active' ? 'success' : 
                                                ($subscription->status === 'trial' ? 'info' : 
                                                ($subscription->status === 'cancelled' ? 'warning' : 'danger'))
                                            }}">{{ ucfirst($subscription->status) }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $subscription->start_date ? \Carbon\Carbon::parse($subscription->start_date)->format('M d, Y') : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date)->format('M d, Y') : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <strong>R{{ number_format($subscription->amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ route('central.subscriptions.show', $subscription->id) }}" 
                                               class="btn btn-sm btn-info" title="View Subscription">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No subscriptions found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $subscriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

@endsection
