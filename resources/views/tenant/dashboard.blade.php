@extends('tenant.layouts.app')

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
                        <h3>{{ $stats['total_bookings'] }}</h3>
                        <p>Bookings To Date</p>
                    </div>
                    {{-- Use Bootstrap Icon instead of SVG --}}
                    <i class="bi bi-calendar small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ $stats['current_bookings'] }}</h3>
                        <p>Current Bookings</p>
                    </div>
                    <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['cancelled_bookings'] }}</h3>
                        <p>Cancelled Bookings</p>
                    </div>
                    <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_rooms'] }}</h3>
                        <p>Total Rooms</p>
                    </div>
                    <i class="bi bi-house small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
                    <a href="{{ route('tenant.rooms.index') }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
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

        {{-- now we need to add quick links to create a new booking, view bookings, view rooms, and view guests --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <a href="{{ route('tenant.bookings.create') }}" class="btn btn-outline-success w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Booking
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('tenant.rooms.create') }}" class="btn btn-outline-primary w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Room
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('tenant.guests.create') }}" class="btn btn-outline-danger w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Guest
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('tenant.guest-clubs.create') }}" class="btn btn-outline-warning w-100 py-3">
                    <i class="fas fa-plus me-2"></i> New Club
                </a>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h5 class="card-title">Recent Bookings</h5>
                {{-- Need to add a button to create a new booking to the left --}}
                <div class="card-tools float-end">
                    <a href="{{ route('tenant.bookings.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-2"></i>New Booking
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Dates</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_bookings as $booking)
                            <tr>
                                <td><strong>{{ $booking->bcode }}</strong><br><small>Created {{ $booking->created_at->diffForHumans() }}</small></td>
                                <td>
                                    @if($booking->primaryGuest)
                                        {{ $booking->primaryGuest->first_name }} {{ $booking->primaryGuest->last_name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                {{-- pad room number to prefix 0 if 1 digit --}}
                                <td>Room: <strong>{{ str_pad($booking->room->number, 3, '0', STR_PAD_LEFT) }}</strong><br>Type: {{ $booking->room->type->name }}</td>
                                <td>{{ $booking->arrival_date->format('M d') }}<br>to {{ $booking->departure_date->format('M d Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No bookings found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end">
                                    <a href="{{ route('tenant.bookings.index') }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i> View All Bookings
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