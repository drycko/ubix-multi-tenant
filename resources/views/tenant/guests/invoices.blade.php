@extends('tenant.layouts.app')

@section('title', 'Guest Invoices - ' . $guest->full_name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-file-invoice"></i>
          Guest Invoices
          <small class="text-muted">{{ $guest->full_name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.index') }}">Guests</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.show', $guest) }}">{{ $guest->full_name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Invoices</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    <!-- Guest Summary -->
    <div class="row mb-3">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="avatar-circle me-3">
                {{ strtoupper(substr($guest->first_name, 0, 1) . substr($guest->last_name, 0, 1)) }}
              </div>
              <div>
                <h5 class="mb-1">{{ $guest->full_name }}</h5>
                <div class="text-muted">
                  <i class="fas fa-envelope"></i> {{ $guest->email }} | 
                  <i class="fas fa-phone"></i> {{ $guest->phone }}
                  @if($guest->nationality)
                  | <span class="badge bg-info">{{ $guest->nationality }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-grid gap-2">
          <a href="{{ route('tenant.guests.show', $guest) }}" class="btn btn-outline-primary">
            <i class="fas fa-user"></i> View Guest Profile
          </a>
          <a href="{{ route('tenant.guests.payments', $guest) }}" class="btn btn-outline-success">
            <i class="fas fa-credit-card"></i> View Payments
          </a>
          <a href="{{ route('tenant.guests.bookings', $guest) }}" class="btn btn-outline-info">
            <i class="fas fa-bed"></i> View Bookings
          </a>
          <a href="{{ route('tenant.guests.edit', $guest) }}" class="btn btn-outline-warning">
            <i class="fas fa-edit"></i> Edit Guest
          </a>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Total Invoices</h5>
            <h3 class="text-primary">{{ $invoices->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Total Amount</h5>
            <h3 class="text-success">{{ $currency }}{{ number_format($invoices->sum('amount'), 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Paid Amount</h5>
            <h3 class="text-info">{{ $currency }}{{ number_format($invoices->where('status', 'paid')->sum('amount'), 2) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices List -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-file-invoice"></i> Invoice History
            </h3>
          </div>
          <div class="card-body p-0">
            @if($invoices->count() > 0)
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Invoice #</th>
                    <th>Booking</th>
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
                      @if($invoice->external_reference)
                      <br><small class="text-muted">Ref: {{ $invoice->external_reference }}</small>
                      @endif
                    </td>
                    <td>
                      @if($invoice->booking)
                      <strong>Booking #{{ $invoice->booking->booking_number }}</strong>
                      @if($invoice->booking->room)
                      <br><small class="text-muted">Room {{ $invoice->booking->room->number }}</small>
                      @endif
                      <br><small class="text-muted">
                        {{ $invoice->booking->arrival_date ? $invoice->booking->arrival_date->format('M d, Y') : 'N/A' }} - 
                        {{ $invoice->booking->departure_date ? $invoice->booking->departure_date->format('M d, Y') : 'N/A' }}
                      </small>
                      @else
                      <span class="text-muted">Booking not found</span>
                      @endif
                    </td>
                    <td>
                      <strong>{{ $currency }}{{ number_format($invoice->amount, 2) }}</strong>
                    </td>
                    <td>
                      @php
                        $statusColors = [
                          'pending' => 'warning',
                          'partially_paid' => 'info',
                          'paid' => 'success',
                          'overdue' => 'danger',
                          'cancelled' => 'secondary'
                        ];
                        $statusColor = $statusColors[$invoice->status] ?? 'secondary';
                      @endphp
                      <span class="badge bg-{{ $statusColor }}">
                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                      </span>
                    </td>
                    <td>
                      <strong>{{ $invoice->created_at->format('M d, Y') }}</strong>
                      <br><small class="text-muted">{{ $invoice->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        @if($invoice->booking)
                        <a href="{{ route('tenant.bookings.show', $invoice->booking) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View Booking">
                          <i class="fas fa-eye"></i>
                        </a>
                        @endif
                        
                        @if(in_array($invoice->status, ['pending', 'partially_paid', 'overdue']))
                        <button type="button" 
                                class="btn btn-sm btn-outline-success" 
                                title="Record Payment"
                                onclick="recordPayment({{ $invoice->id }})">
                          <i class="fas fa-credit-card"></i>
                        </button>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-sm btn-outline-info" 
                                title="Print Invoice"
                                onclick="printInvoice({{ $invoice->id }})">
                          <i class="fas fa-print"></i>
                        </button>
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
              <h5 class="text-muted">No Invoices Found</h5>
              <p class="text-muted">This guest has no invoices yet.</p>
              <a href="{{ route('tenant.guests.bookings', $guest) }}" class="btn btn-primary">
                <i class="fas fa-bed"></i> View Bookings
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    @if($invoices->count() > 0)
    <div class="row mt-3">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-success">Paid</h5>
            <h3>{{ $invoices->where('status', 'paid')->count() }}</h3>
            <small class="text-muted">{{ $currency }}{{ number_format($invoices->where('status', 'paid')->sum('amount'), 2) }}</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-warning">Pending</h5>
            <h3>{{ $invoices->where('status', 'pending')->count() }}</h3>
            <small class="text-muted">{{ $currency }}{{ number_format($invoices->where('status', 'pending')->sum('amount'), 2) }}</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-info">Partially Paid</h5>
            <h3>{{ $invoices->where('status', 'partially_paid')->count() }}</h3>
            <small class="text-muted">{{ $currency }}{{ number_format($invoices->where('status', 'partially_paid')->sum('amount'), 2) }}</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title text-danger">Overdue</h5>
            <h3>{{ $invoices->where('status', 'overdue')->count() }}</h3>
            <small class="text-muted">{{ $currency }}{{ number_format($invoices->where('status', 'overdue')->sum('amount'), 2) }}</small>
          </div>
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
<!--end::App Content-->

<script>
function recordPayment(invoiceId) {
  // TODO: Implement payment recording modal
  alert('Payment recording functionality will be implemented here');
}

function printInvoice(invoiceId) {
  // TODO: Implement invoice printing
  alert('Invoice printing functionality will be implemented here');
}
</script>

@endsection

@push('styles')
<style>
.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(45deg, #3B82F6, #1D4ED8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 14px;
}
</style>
@endpush