@extends('tenant.layouts.guest')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="fw-bold text-success mb-2">
            <i class="bi bi-receipt me-2"></i>
            Invoice Details
          </h2>
          <p class="text-muted mb-0">Invoice #: <strong>{{ $invoice->invoice_number }}</strong></p>
        </div>
        <div>
          <a href="{{ route('tenant.guest-portal.invoices') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
          </a>
          <a href="{{ route('tenant.guest-portal.invoices.download', $invoice->id) }}" class="btn btn-success">
            <i class="bi bi-download me-1"></i> Download PDF
          </a>
          <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i> Print
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Invoice Card -->
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card shadow-sm border-0" id="invoice-content">
        <div class="card-body p-5">
          <!-- Invoice Header -->
          <div class="row mb-4">
            <div class="col-6">
              <h3 class="text-success mb-3">INVOICE</h3>
              <p class="mb-1"><strong>{{ $invoice->booking->property->name ?? 'Property' }}</strong></p>
              @if($invoice->booking->property)
              <p class="mb-0 text-muted">
                {{ $invoice->booking->property->street_address ?? '' }}<br>
                {{ $invoice->booking->property->city ?? '' }}, {{ $invoice->booking->property->province ?? '' }} {{ $invoice->booking->property->postal_code ?? '' }}
              </p>
              @endif
            </div>
            <div class="col-6 text-end">
              <h4 class="mb-3">{{ $invoice->invoice_number }}</h4>
              <p class="mb-1"><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') }}</p>
              <p class="mb-1">
                <strong>Status:</strong>
                @php
                  $statusBadges = [
                    'pending' => 'warning',
                    'partially_paid' => 'info',
                    'paid' => 'success',
                    'overdue' => 'danger',
                    'cancelled' => 'secondary',
                  ];
                  $badgeClass = $statusBadges[$invoice->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $badgeClass }}">
                  {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
              </p>
            </div>
          </div>

          <hr>

          <!-- Bill To -->
          <div class="row mb-4">
            <div class="col-6">
              <h6 class="text-muted mb-2">BILL TO:</h6>
              @if($invoice->booking->guests && $invoice->booking->guests->count() > 0)
              @php $primaryGuest = $invoice->booking->guests->first(); @endphp
              <p class="mb-1"><strong>{{ $primaryGuest->full_name }}</strong></p>
              <p class="mb-1">{{ $primaryGuest->email }}</p>
              <p class="mb-0">{{ $primaryGuest->phone_number ?? 'N/A' }}</p>
              @else
              <p class="mb-0">Guest information not available</p>
              @endif
            </div>
            <div class="col-6">
              <h6 class="text-muted mb-2">BOOKING DETAILS:</h6>
              <p class="mb-1"><strong>Booking Code:</strong> {{ $invoice->booking->bcode }}</p>
              <p class="mb-1"><strong>Room:</strong> {{ $invoice->booking->room->type->name ?? 'N/A' }} - {{ $invoice->booking->room->number ?? 'N/A' }}</p>
              <p class="mb-1"><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($invoice->booking->arrival_date)->format('M d, Y') }}</p>
              <p class="mb-0"><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($invoice->booking->departure_date)->format('M d, Y') }}</p>
            </div>
          </div>

          <!-- Invoice Items -->
          <div class="table-responsive mb-4">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Description</th>
                  <th class="text-end">Quantity</th>
                  <th class="text-end">Rate</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <strong>{{ $invoice->booking->room->type->name ?? 'Room' }}</strong> - Room {{ $invoice->booking->room->number ?? 'N/A' }}
                    <br>
                    <small class="text-muted">
                      {{ \Carbon\Carbon::parse($invoice->booking->arrival_date)->format('M d') }} - 
                      {{ \Carbon\Carbon::parse($invoice->booking->departure_date)->format('M d, Y') }}
                    </small>
                  </td>
                  <td class="text-end">{{ $invoice->booking->nights }} night(s)</td>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($invoice->booking->daily_rate, 2) }}</td>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($invoice->subtotal_amount, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals -->
          <div class="row">
            <div class="col-6 offset-6">
              <table class="table table-sm">
                <tr>
                  <th>Subtotal:</th>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($invoice->subtotal_amount, 2) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                  <th>Tax ({{ $invoice->tax->rate ?? 0 }}%):</th>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="table-light">
                  <th class="fs-5">Total:</th>
                  <td class="text-end fs-5">
                    <strong>{{ tenant_currency() }} {{ number_format($invoice->amount, 2) }}</strong>
                  </td>
                </tr>
                <tr class="table-success">
                  <th>Paid:</th>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($invoice->total_paid, 2) }}</td>
                </tr>
                @if($invoice->total_refunded > 0)
                <tr class="table-warning">
                  <th>Refunded:</th>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($invoice->total_refunded, 2) }}</td>
                </tr>
                @endif
                <tr class="table-{{ $invoice->remaining_balance > 0 ? 'danger' : 'success' }}">
                  <th class="fs-5">Balance Due:</th>
                  <td class="text-end fs-5">
                    <strong>{{ tenant_currency() }} {{ number_format($invoice->remaining_balance, 2) }}</strong>
                  </td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Payment History -->
          @if($invoice->invoicePayments && $invoice->invoicePayments->count() > 0)
          <hr>
          <h6 class="mb-3">Payment History</h6>
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Payment Method</th>
                  <th>Reference</th>
                  <th>Status</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                @foreach($invoice->invoicePayments as $payment)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y H:i') }}</td>
                  <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                  <td>{{ $payment->payment_reference ?? 'N/A' }}</td>
                  <td>
                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                      {{ ucfirst($payment->status) }}
                    </span>
                  </td>
                  <td class="text-end">{{ tenant_currency() }} {{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif

          <!-- Notes -->
          <div class="mt-4 pt-4 border-top">
            <p class="text-muted mb-0">
              <small>
                <strong>Note:</strong> Thank you for your business. If you have any questions about this invoice, 
                please contact our support team.
              </small>
            </p>
          </div>
        </div>
      </div>

      <!-- Action Buttons (Print Hidden) -->
      <div class="text-center mt-4 no-print">
        @if($invoice->remaining_balance > 0 && $invoice->status !== 'cancelled')
        <a href="{{ route('tenant.guest-portal.bookings.show', $invoice->booking->id) }}" class="btn btn-warning btn-lg">
          <i class="bi bi-credit-card me-2"></i>
          Make a Payment
        </a>
        @endif
        <a href="{{ route('tenant.guest-portal.bookings.show', $invoice->booking->id) }}" class="btn btn-primary btn-lg">
          <i class="bi bi-eye me-2"></i>
          View Booking
        </a>
      </div>
    </div>
  </div>
</div>

<style>
  @media print {
    .no-print {
      display: none !important;
    }
    .card {
      border: none !important;
      box-shadow: none !important;
    }
  }
</style>
@endsection
