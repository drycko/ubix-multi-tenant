@extends('tenant.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Dashboard</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_bookings'] }}</h3>
                        <p>Total Bookings</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['active_bookings'] }}</h3>
                        <p>Active Bookings</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <a href="{{ route('tenant.bookings.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_rooms'] }}</h3>
                        <p>Total Rooms</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <a href="{{ route('tenant.rooms.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['total_guests'] }}</h3>
                        <p>Registered Guests</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('tenant.guests.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Bookings</h3>
                <div class="card-tools">
                    <a href="{{ route('tenant.bookings.index') }}" class="btn btn-tool">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_bookings as $booking)
                            <tr>
                                <td>
                                    <a href="{{ route('tenant.bookings.show', $booking) }}">
                                        {{ $booking->bcode }}
                                    </a>
                                </td>
                                <td>{{ optional($booking->bookingGuests->first()->guest ?? null)->full_name ?? 'N/A' }}</td>
                                <td>{{ optional($booking->room)->name ?? 'N/A' }}</td>
                                <td>{{ $booking->arrival_date->format('M d, Y') }}</td>
                                <td>{{ $booking->departure_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status_color }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>{{ format_money($booking->total_amount, $currency) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No bookings found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recent_bookings->hasPages())
            <div class="card-footer">
                {{ $recent_bookings->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
<!--end::App Content-->
@endsection