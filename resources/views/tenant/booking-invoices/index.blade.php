@extends('tenant.layouts.app')

@section('title', 'Booking Invoices')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-file-invoice"></i>
          <small class="text-muted">Booking Invoices</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Booking Invoices</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">

    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card card-info card-outline mb-3">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-filter"></i> Filters
        </h5>
      </div>
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <label for="search" class="form-label">Search</label>
            <input type="text" class="form-control" id="search" name="search" 
                   value="{{ request('search') }}" placeholder="Invoice or booking number">
          </div>
          <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="">All Statuses</option>
              <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
              <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
              <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="date_from" class="form-label">From Date</label>
            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
          </div>
          <div class="col-md-2">
            <label for="date_to" class="form-label">To Date</label>
            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid gap-2 d-md-flex">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
              </button>
              <a href="{{ route('tenant.booking-invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Invoices List -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list"></i> Invoices
        </h3>
        <div class="card-tools">
          <span class="badge bg-info">{{ $invoices->total() }} Total</span>
        </div>
      </div>
      <div class="card-body p-0">
        @if($invoices->count() > 0)
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
              <tr>
                <th>Invoice #</th>
                <th>Booking</th>
                <th>Guest</th>
                <th>Room</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($invoices as $invoice)
              <tr>
                <td>
                  <strong>{{ $invoice->invoice_number }}</strong>
                </td>
                <td>
                  <a href="{{ route('tenant.bookings.show', $invoice->booking) }}" class="text-decoration-none">
                    {{ $invoice->booking->bcode }}
                  </a>
                </td>
                <td>
                  @php
                    $primaryGuest = $invoice->booking->bookingGuests->where('is_primary', true)->first()?->guest;
                  @endphp
                  @if($primaryGuest)
                    {{ $primaryGuest->first_name }} {{ $primaryGuest->last_name }}
                  @else
                    <span class="text-muted">No guest</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-secondary">{{ $invoice->booking->room->number }}</span>
                  <small class="text-muted d-block">{{ $invoice->booking->room->type->name }}</small>
                </td>
                <td>
                  <strong class="text-success">{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong>
                </td>
                <td>
                  @php
                    $statusColors = [
                      'pending' => 'warning',
                      'paid' => 'success',
                      'partially_paid' => 'info',
                      'cancelled' => 'danger'
                    ];
                  @endphp
                  <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                  </span>
                </td>
                <td>
                  {{ $invoice->created_at->format('M j, Y') }}
                  <small class="text-muted d-block">{{ $invoice->created_at->format('g:i A') }}</small>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                      Actions
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="{{ route('tenant.booking-invoices.show', $invoice) }}">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('tenant.booking-invoices.download', $invoice) }}">
                          <i class="fas fa-download"></i> Download PDF
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('tenant.booking-invoices.print', $invoice) }}" target="_blank">
                          <i class="fas fa-print"></i> Print
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="{{ route('tenant.bookings.show', $invoice->booking) }}">
                          <i class="fas fa-bed"></i> View Booking
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No invoices found</h5>
          <p class="text-muted">No booking invoices match your current filters.</p>
          @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
          <a href="{{ route('tenant.booking-invoices.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-times"></i> Clear Filters
          </a>
          @endif
        </div>
        @endif
      </div>
      {{-- Pagination links --}}
        {{-- Beautiful pagination --}}
        @if($invoices->hasPages())
        <div class="container-fluid py-3">
          <div class="row align-items-center">
              <div class="col-md-12 float-end">
                  {{ $invoices->links('vendor.pagination.bootstrap-5') }}
              </div>
          </div>
        </div>
        @endif
    </div>
  </div>
</div>
<!--end::App Content-->
@endsection