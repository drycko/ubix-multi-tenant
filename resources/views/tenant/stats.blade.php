@extends('tenant.layouts.app')

@section('title', 'Stats')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
            <h3 class="mb-0">Stats</h3>
            </div>
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stats</li>
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
        <!-- Stats Cards with graphs -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title">Bookings Over Time</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="bookingsChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-4">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title">Bookings by Arrival Date</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="bookingsArrivalChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            {{-- <div class="row mt-4"> --}}
            <div class="col-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title">Bookings by Room Type</h5>
                    </div>
                    <div class="card-body pb-0">
                        <canvas id="roomTypeChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        {{-- </div> --}}
        </div>
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

{{-- @push('scripts') --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('bookingsChart').getContext('2d');
        const bookingsData = @json($bookingsOverTime['data'] ?? []);
        const bookingsLabels = @json($bookingsOverTime['labels'] ?? []);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: bookingsLabels,
                datasets: [{
                    label: 'Bookings',
                    data: bookingsData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Bookings' }, beginAtZero: true }
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const ctxArrival = document.getElementById('bookingsArrivalChart').getContext('2d');
        const bookingsArrivalData = @json($bookingsByArrivalDate['data'] ?? []);
        const bookingsArrivalLabels = @json($bookingsByArrivalDate['labels'] ?? []);
        new Chart(ctxArrival, {
            type: 'bar',
            data: {
                labels: bookingsArrivalLabels,
                datasets: [{
                    label: 'Bookings',
                    data: bookingsArrivalData,
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Arrival Date' } },
                    y: { title: { display: true, text: 'Bookings' }, beginAtZero: true }
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const ctxRoomType = document.getElementById('roomTypeChart').getContext('2d');
        const roomTypeLabels = @json($bookingsByRoomType['labels'] ?? []);
        const roomTypeData = @json($bookingsByRoomType['data'] ?? []);
        new Chart(ctxRoomType, {
            type: 'bar',
            data: {
                labels: roomTypeLabels,
                datasets: [{
                    label: 'Bookings',
                    data: roomTypeData,
                    backgroundColor: '#28a745',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Room Type' } },
                    y: { title: { display: true, text: 'Bookings' }, beginAtZero: true }
                }
            }
        });
    });
</script>
{{-- @endpush --}}

@endsection