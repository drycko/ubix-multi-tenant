@extends('tenant.layouts.app')

@section('title', 'Guest Payments - ' . $guest->full_name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-credit-card"></i>
          Guest Payments
          <small class="text-muted">{{ $guest->full_name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.index') }}">Guests</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guests.show', $guest) }}">{{ $guest->full_name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Payments</li>
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
          <a href="{{ route('tenant.guests.invoices', $guest) }}" class="btn btn-outline-warning">
            <i class="fas fa-file-invoice"></i> View Invoices
          </a>
          <a href="{{ route('tenant.guests.bookings', $guest) }}" class="btn btn-outline-info">
            <i class="fas fa-bed"></i> View Bookings
          </a>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Total Payments</h5>
            <h3 class="text-primary">{{ $payments->count() }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Total Amount</h5>
            <h3 class="text-success">{{ $currency }}{{ number_format($payments->sum('amount'), 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">Completed Payments</h5>
            <h3 class="text-info">{{ $currency }}{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments List -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-credit-card"></i> Payment History
            </h3>
          </div>
          <div class="card-body p-0">
            @if($payments->count() > 0)
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Payment Date</th>
                    <th>Invoice</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($payments as $payment)
                  <tr>
                    <td>
                      <strong>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</strong>
                      <br><small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                      @if($payment->bookingInvoice)
                      <strong>{{ $payment->bookingInvoice->invoice_number }}</strong>
                      @if($payment->bookingInvoice->booking)
                      <br><small class="text-muted">Booking #{{ $payment->bookingInvoice->booking->booking_number }}</small>
                      @if($payment->bookingInvoice->booking->room)
                      <br><small class="text-muted">Room {{ $payment->bookingInvoice->booking->room->number }}</small>
                      @endif
                      @endif
                      @else
                      <span class="text-muted">Invoice not found</span>
                      @endif
                    </td>
                    <td>
                      @php
                        $methodIcons = [
                          'cash_payment' => 'fas fa-money-bill',
                          'credit_card' => 'fas fa-credit-card',
                          'bank_transfer' => 'fas fa-university',
                          'paypal' => 'fab fa-paypal',
                          'stripe' => 'fab fa-stripe'
                        ];
                        $icon = $methodIcons[$payment->payment_method] ?? 'fas fa-credit-card';
                      @endphp
                      <i class="{{ $icon }}"></i>
                      {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                    </td>
                    <td>
                      <strong class="text-success">{{ $currency }}{{ number_format($payment->amount, 2) }}</strong>
                    </td>
                    <td>
                      @if($payment->reference_number)
                      <code>{{ $payment->reference_number }}</code>
                      @else
                      <span class="text-muted">No reference</span>
                      @endif
                    </td>
                    <td>
                      @php
                        $statusColors = [
                          'completed' => 'success',
                          'pending' => 'warning',
                          'failed' => 'danger'
                        ];
                        $statusColor = $statusColors[$payment->status] ?? 'secondary';
                      @endphp
                      <span class="badge bg-{{ $statusColor }}">
                        {{ ucfirst($payment->status) }}
                      </span>
                    </td>
                    <td>
                      @if($payment->recordedBy)
                      <strong>{{ $payment->recordedBy->name }}</strong>
                      <br><small class="text-muted">{{ $payment->created_at->format('M d, Y') }}</small>
                      @else
                      <span class="text-muted">System</span>
                      @endif
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        @if($payment->bookingInvoice)
                        <a href="{{ route('tenant.guests.invoices', $guest) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View Invoice">
                          <i class="fas fa-file-invoice"></i>
                        </a>
                        @endif
                        
                        @if($payment->bookingInvoice && $payment->bookingInvoice->booking)
                        <a href="{{ route('tenant.bookings.show', $payment->bookingInvoice->booking) }}" 
                           class="btn btn-sm btn-outline-info" 
                           title="View Booking">
                          <i class="fas fa-bed"></i>
                        </a>
                        @endif
                        
                        @if($payment->status === 'completed')
                        <button type="button" 
                                class="btn btn-sm btn-outline-success" 
                                title="Print Receipt"
                                onclick="printReceipt({{ $payment->id }})">
                          <i class="fas fa-print"></i>
                        </button>
                        @endif
                      </div>
                    </td>
                  </tr>
                  @if($payment->notes)
                  <tr class="table-light">
                    <td colspan="8">
                      <small class="text-muted">
                        <i class="fas fa-sticky-note"></i> <strong>Notes:</strong> {{ $payment->notes }}
                      </small>
                    </td>
                  </tr>
                  @endif
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center py-5">
              <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
              <h5 class="text-muted">No Payments Found</h5>
              <p class="text-muted">This guest has no payment records yet.</p>
              <div class="mt-3">
                <a href="{{ route('tenant.guests.invoices', $guest) }}" class="btn btn-primary me-2">
                  <i class="fas fa-file-invoice"></i> View Invoices
                </a>
                <a href="{{ route('tenant.guests.bookings', $guest) }}" class="btn btn-outline-primary">
                  <i class="fas fa-bed"></i> View Bookings
                </a>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Method Summary -->
    @if($payments->count() > 0)
    <div class="row mt-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-chart-pie"></i> Payment Summary by Method
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($payments->groupBy('payment_method') as $method => $methodPayments)
              <div class="col-md-3 mb-3">
                <div class="card text-center border-0 bg-light">
                  <div class="card-body">
                    @php
                      $methodIcons = [
                        'cash_payment' => 'fas fa-money-bill text-success',
                        'credit_card' => 'fas fa-credit-card text-primary',
                        'bank_transfer' => 'fas fa-university text-info',
                        'paypal' => 'fab fa-paypal text-warning',
                        'stripe' => 'fab fa-stripe text-purple'
                      ];
                      $icon = $methodIcons[$method] ?? 'fas fa-credit-card text-secondary';
                    @endphp
                    <i class="{{ $icon }} fa-2x mb-2"></i>
                    <h6 class="card-title">{{ ucfirst(str_replace('_', ' ', $method)) }}</h6>
                    <p class="card-text">
                      <strong>{{ $methodPayments->count() }}</strong> payments<br>
                      <span class="text-success">{{ $currency }}{{ number_format($methodPayments->sum('amount'), 2) }}</span>
                    </p>
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

  </div>
</div>
<!--end::App Content-->

<script>
function printReceipt(paymentId) {
  // TODO: Implement receipt printing
  alert('Receipt printing functionality will be implemented here');
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

.text-purple {
  color: #6f42c1 !important;
}
</style>
@endpush