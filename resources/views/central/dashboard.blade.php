@extends('central.layouts.app')

@section('title', 'Dashboard')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
            <h3 class="mb-0">Dashboard</h3>
            </div>
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_tenants'] }}</h3>
                        <p>Total Tenants</p>
                    </div>
                    {{-- Use Bootstrap Icon instead of SVG --}}
                    <i class="bi bi-house small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('central.tenants.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ $stats['active_tenants'] }}</h3>
                        <p>Active Tenants</p>
                    </div>
                    <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('central.tenants.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['trialing_tenants'] }}</h3>
                        <p>Trialing Tenants</p>
                    </div>
                    <i class="bi bi-hourglass-split small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('central.tenants.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['invoiced_tenants'] }}</h3>
                        <p>Invoiced Tenants</p>
                    </div>
                    <i class="bi bi-file-earmark-text small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('central.invoices.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
        </div>
        {{-- <div class="col-md-3 col-6">
            <div class="card card-dashboard bg-warning text-dark">
                <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title">Available Rooms</h6>
                                <h3 class="card-text">{{ $stats['available_rooms'] }}</h3>
                            </div>
                            <i class="fas fa-door-open fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- now we need to add quick links to create a new tenant, user, trialing tenant, and invoice --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <a href="{{ route('central.tenants.create') }}" class="btn btn-outline-success w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Tenant
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" class="btn btn-outline-primary w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New User
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('central.tenants.create') }}" class="btn btn-outline-warning w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Trialing Tenant
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('central.invoices.create') }}" class="btn btn-outline-danger w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Invoice
                </a>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h5 class="card-title">Recent Tenants</h5>
                {{-- Need to add a button to create a new booking to the left --}}
                {{-- <div class="card-tools float-end">
                    <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createBookingModal">
                        <i class="fas fa-plus me-2"></i>New Booking
                    </a>
                </div> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Primary Domain</th>
                                <th>Database</th>
                                <th>Email</th>
                                <th>Date Created</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenants as $tenant)
                            <tr>
                                <td><strong>{{ $tenant->name }}</strong><br><small>Created {{ $tenant->created_at->diffForHumans() }}</small></td>
                                <td>
                                    <a href="http://{{ $tenant->domains->where('is_primary', true)->pluck('domain')->join(', ') }}" target="_blank" rel="noopener">
                                        {{ $tenant->domains->where('is_primary', true)->pluck('domain')->join(', ') }}
                                    </a>
                                </td>
                                <td>{{ $tenant->database }}</td>
                                <td>{{ $tenant->email }}</td>
                                <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $tenant->is_active ? 'success' : 'secondary' }}">
                                        {{ ucfirst($tenant->is_active ? 'Active' : 'Inactive') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No tenants found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end">
                                    <a href="{{ route('central.tenants.index') }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i> View All Tenants
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->
@endsection