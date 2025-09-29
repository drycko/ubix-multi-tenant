@extends('tenant.layouts.app')

@section('title', 'Manage Bookings')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">All Bookings</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">All Bookings</li>
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
    
    {{-- messages from redirect --}}
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
    <div class="card card-success card-outline">
      <div class="card-header">
        <h5 class="card-title">Bookings</h5>
        {{-- Need to add a button to create a new booking to the left --}}
        <div class="card-tools float-end">
          <a href="{{ route('tenant.bookings.import') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-import me-2"></i>Import Bookings
          </a>
          <a href="#" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#createBookingModal">
            <i class="fas fa-plus me-2"></i>New Booking
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Booking</th>
                <th>Main Guest</th>
                <th>Room</th>
                <th>Dates</th>
                <th>Amount</th>
                <th>Package</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($bookings as $booking)
              <tr>
                <td><strong>{{ $booking->bcode }}</strong><br><small>Created: {{ $booking->created_at->format('M d, Y') }}</small></td>
                <td>
                  @if($booking->successGuest)
                  {{ $booking->successGuest->first_name }} {{ $booking->successGuest->last_name }}
                  @else
                  N/A
                  @endif
                </td>
                <td><strong>{{ str_pad($booking->room->number, 3, '0', STR_PAD_LEFT) }}</strong><br>Type: {{ $booking->room->type->name }}</td>
                <td>
                  {{ $booking->arrival_date->format('M d, Y') }}<br>
                  to {{ $booking->departure_date->format('M d, Y') }}<br>
                  {{ $booking->nights }} nights
                </td>
                <td>{{ number_format($booking->total_amount, 2) }} {{ $currency }}</td>
                <td>{{ $booking->package->name ?? 'N/A' }}</td>
                <td>
                  <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'secondary' }}">
                    {{ ucfirst($booking->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('tenant.bookings.show', $booking) }}" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('tenant.bookings.edit', $booking) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center">No bookings found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        
        {{-- Pagination links --}}
        @if($bookings->hasPages())
        <div class="card-footer bg-light py-3">
          <div class="row align-items-center">
            <div class="col-md-8">
              <p class="mb-0 text-muted">
                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} entries
              </p>
            </div>
            <div class="col-md-4 float-end">
              {{ $bookings->links('vendor.pagination.bootstrap-5') }} {{-- I want to align the links to the end of the column --}}
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

{{-- Create Booking Modal with 2 button links to create daily booking and dropdown one to preselect package to create booking --}}
<div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createBookingModalLabel">Create Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Select the type of booking you want to create:</p>
        <div class="d-flex justify-content-between">
          <a href="{{ route('tenant.bookings.create') }}" class="btn btn-sm btn-outline-success">
            <i class="fa fa-calendar me-2"></i> Simple Booking
          </a>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" id="packageBookingDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa fa-gift me-2"></i> Package Booking
            </button>
            <ul class="dropdown-menu" aria-labelledby="packageBookingDropdown">
              @foreach($packages as $package)
              <li><a class="dropdown-item" href="{{ route('tenant.bookings.create.package', $package) }}">{{ $package->pkg_name }}</a></li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection