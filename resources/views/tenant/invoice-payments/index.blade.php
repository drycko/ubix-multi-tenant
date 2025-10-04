@extends('tenant.layouts.app')

@section('title', 'Invoice Payments - ' . $bookingInvoice->invoice_number)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-credit-card"></i>
          Invoice Payments
          <small class="text-muted">{{ $bookingInvoice->invoice_number }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.booking-invoices.index') }}">Invoices</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.booking-invoices.show', $bookingInvoice) }}">{{ $bookingInvoice->invoice_number }}</a></li>
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
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Invoice Summary -->
    <div class="row mb-3">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <h5>Invoice: {{ $bookingInvoice->invoice_number }}</h5>
            <div class="row">
              <div class="col-md-3">
                <strong>Total Amount:</strong><br>
                {{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}
              </div>
              <div class="col-md-3">
                <strong>Amount Paid:</strong><br>
                <span class="text-success">{{ $currency }} {{ number_format($bookingInvoice->total_paid, 2) }}</span>
              </div>
              <div class="col-md-3">
                <strong>Balance Due:</strong><br>
                <span class="{{ $bookingInvoice->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                  {{ $currency }} {{ number_format($bookingInvoice->remaining_balance, 2) }}
                </span>
              </div>
              <div class="col-md-3">
                <strong>Status:</strong><br>
                @php
                  $statusColors = ['pending' => 'warning', 'paid' => 'success', 'partially_paid' => 'info', 'cancelled' => 'danger'];
                @endphp
                <span class="badge bg-{{ $statusColors[$bookingInvoice->status] ?? 'secondary' }}">
                  {{ ucfirst(str_replace('_', ' ', $bookingInvoice->status)) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-grid gap-2">
          @if($bookingInvoice->remaining_balance > 0)
          <a href="{{ route('tenant.invoice-payments.create', $bookingInvoice) }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Record New Payment
          </a>
          @endif
          <a href="{{ route('tenant.booking-invoices.show', $bookingInvoice) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Invoice
          </a>
        </div>
      </div>
    </div>

    <!-- Payments List -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list"></i> Payment History
        </h3>
      </div>
      <div class="card-body">
        @if($payments->count() > 0)
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Payment Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Recorded By</th>
                <th>Notes</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($payments as $payment)
              <tr>
                <td>{{ $payment->payment_date->format('M j, Y') }}</td>
                <td>{{ $payment->payment_method_label }}</td>
                <td>{{ $payment->reference_number ?? '-' }}</td>
                <td>{{ $currency }} {{ number_format($payment->amount, 2) }}</td>
                <td>
                  <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                    {{ $payment->status_label }}
                  </span>
                </td>
                <td>{{ $payment->recordedBy->name ?? 'N/A' }}</td>
                <td>{{ Str::limit($payment->notes, 50) ?? '-' }}</td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('tenant.invoice-payments.show', [$bookingInvoice, $payment]) }}" 
                       class="btn btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('tenant.invoice-payments.edit', [$bookingInvoice, $payment]) }}" 
                       class="btn btn-outline-warning" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('tenant.invoice-payments.destroy', [$bookingInvoice, $payment]) }}" 
                          method="POST" 
                          style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to delete this payment?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        {{ $payments->links() }}
        @else
        <div class="text-center py-4">
          <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No payments recorded yet</h5>
          <p class="text-muted">Payments will appear here once they are recorded.</p>
          @if($bookingInvoice->remaining_balance > 0)
          <a href="{{ route('tenant.invoice-payments.create', $bookingInvoice) }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Record First Payment
          </a>
          @endif
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->
@endsection