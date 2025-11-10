@extends('portal.layouts.app')

@section('page-title', 'Invoice Details - ' . $invoice->invoice_number)

@section('content')
<div class="row mb-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <a href="{{ route('portal.invoices') }}" class="btn btn-outline-secondary mb-3">
          <i class="fas fa-arrow-left me-2"></i>Back to Invoices
        </a>
      </div>
      <div class="d-flex gap-2">
        @if($invoice->status === 'pending' && $invoice->remaining_balance || $invoice->status === 'overdue')
        {{-- PayFast Payment Button --}}
        {!! $payfastForm !!}
        {{-- End PayFast Payment Button --}}
        {{-- <a href="#" class="btn btn-danger" onclick="alert('Payment integration coming soon!'); return false;">
          <i class="fas fa-credit-card me-2"></i>Pay Now
        </a> --}}
        {{-- End Pay Now Button --}}
        @endif
        <a href="{{ route('portal.invoices.print', $invoice) }}" target="_blank" class="btn btn-primary">
          <i class="fas fa-print me-2"></i>Print
        </a>
        <a href="{{ route('portal.invoices.download', $invoice) }}" class="btn btn-success">
          <i class="fas fa-download me-2"></i>Download PDF
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Invoice Header Card -->
<div class="ghost-card mb-4">
  <div class="ghost-card-header primary">
    <div class="ghost-card-icon">
      <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div class="flex-grow-1">
      <h3 class="mb-1">Invoice {{ $invoice->invoice_number }}</h3>
      <p class="mb-0 opacity-75">
        Invoice Date: {{ $invoice->invoice_date->format('F d, Y') }} | 
        Due Date: {{ $invoice->due_date->format('F d, Y') }}
      </p>
    </div>
    <div>
      @if($invoice->status === 'paid')
      <span class="badge bg-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
        <i class="fas fa-check-circle"></i> Paid
      </span>
      @elseif($invoice->status === 'pending')
      <span class="badge bg-warning text-dark" style="font-size: 1rem; padding: 0.5rem 1rem;">
        <i class="fas fa-clock"></i> Pending
      </span>
      @elseif($invoice->status === 'overdue')
      <span class="badge bg-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">
        <i class="fas fa-exclamation-circle"></i> Overdue
      </span>
      @elseif($invoice->status === 'cancelled')
      <span class="badge bg-secondary" style="font-size: 1rem; padding: 0.5rem 1rem;">
        <i class="fas fa-ban"></i> Cancelled
      </span>
      @else
      <span class="badge bg-dark" style="font-size: 1rem; padding: 0.5rem 1rem;">
        <i class="fas fa-question-circle"></i> {{ ucfirst($invoice->status) }}
      </span>
      @endif
    </div>
  </div>
</div>

<!-- Invoice Details Grid -->
<div class="row">
  <!-- Billing Information -->
  <div class="col-md-6 mb-4">
    <div class="ghost-card h-100">
      <div class="ghost-card-header info">
        <div class="ghost-card-icon">
          <i class="fas fa-building"></i>
        </div>
        <h5 class="mb-0">Billing Information</h5>
      </div>
      <div class="ghost-card-body">
        <h6 class="text-primary mb-3">{{ $tenant->name }}</h6>
        <div class="mb-2">
          <i class="fas fa-user text-muted me-2"></i>
          <strong>Contact Person:</strong> {{ $tenant->contact_person ?? 'N/A' }}
        </div>
        <div class="mb-2">
          <i class="fas fa-envelope text-muted me-2"></i>
          <strong>Email:</strong> {{ $tenant->email }}
        </div>
        @if($tenant->contact_number)
        <div class="mb-2">
          <i class="fas fa-phone text-muted me-2"></i>
          <strong>Phone:</strong> {{ $tenant->contact_number }}
        </div>
        @endif
        @if($tenant->address)
        <div class="mb-2">
          <i class="fas fa-map-marker-alt text-muted me-2"></i>
          <strong>Address:</strong> {{ $tenant->address }}
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Subscription Information -->
  <div class="col-md-6 mb-4">
    <div class="ghost-card h-100">
      <div class="ghost-card-header success">
        <div class="ghost-card-icon">
          <i class="fas fa-box"></i>
        </div>
        <h5 class="mb-0">Subscription Details</h5>
      </div>
      <div class="ghost-card-body">
        @if($invoice->subscription)
        <h6 class="text-success mb-3">{{ $invoice->subscription->plan->name ?? 'N/A' }}</h6>
        <div class="mb-2">
          <i class="fas fa-sync text-muted me-2"></i>
          <strong>Billing Cycle:</strong> {{ ucfirst($invoice->subscription->billing_cycle ?? 'N/A') }}
        </div>
        <div class="mb-2">
          <i class="fas fa-calendar-alt text-muted me-2"></i>
          <strong>Start Date:</strong> {{ $invoice->subscription->start_date?->format('F d, Y') ?? 'N/A' }}
        </div>
        <div class="mb-2">
          <i class="fas fa-calendar-check text-muted me-2"></i>
          <strong>End Date:</strong> {{ $invoice->subscription->end_date?->format('F d, Y') ?? 'N/A' }}
        </div>
        <div class="mb-2">
          <i class="fas fa-info-circle text-muted me-2"></i>
          <strong>Status:</strong> 
          <span class="badge bg-{{ $invoice->subscription->status === 'active' ? 'success' : 'secondary' }}">
            {{ ucfirst($invoice->subscription->status ?? 'N/A') }}
          </span>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class="fas fa-info-circle me-2"></i>No subscription linked to this invoice.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Invoice Items -->
<div class="ghost-card mb-4">
  <div class="ghost-card-header warning">
    <div class="ghost-card-icon">
      <i class="fas fa-list"></i>
    </div>
    <h5 class="mb-0">Invoice Items</h5>
  </div>
  <div class="ghost-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Description</th>
            <th class="text-center">Invoice Date</th>
            <th class="text-center">Due Date</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <strong>{{ $invoice->notes ?? 'Subscription Payment' }}</strong>
              @if($invoice->subscription && $invoice->subscription->plan)
              <br>
              <small class="text-muted">
                {{ $invoice->subscription->plan->name }} - {{ ucfirst($invoice->subscription->billing_cycle) }} Plan
              </small>
              @endif
            </td>
            <td class="text-center">
              <span class="text-nowrap">
                {{ $invoice->invoice_date->format('M d, Y') }}
              </span>
            </td>
            <td class="text-center">
              <span class="text-nowrap">
                {{ $invoice->due_date->format('M d, Y') }}
              </span>
            </td>
            <td class="text-end">
              <strong class="text-success">{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong>
            </td>
          </tr>
        </tbody>
        <tfoot class="table-light">
          @if($invoice->tax_amount > 0)
          <tr class="text-muted">
            <td colspan="3" class="text-end">Subtotal:</td>
            <td class="text-end">{{ $currency }} {{ number_format($invoice->subtotal_amount, 2) }}</td>
          </tr>
          <tr class="text-muted">
            <td colspan="3" class="text-end">
              Tax ({{ $invoice->tax_name }} 
              @if($invoice->tax_type === 'percentage')
                {{ number_format($invoice->tax_rate, 2) }}%
              @else
                - Fixed
              @endif
              @if($invoice->tax_inclusive)
                <small class="text-info">- Inclusive</small>
              @endif
              ):
            </td>
            <td class="text-end">{{ $currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
          </tr>
          @endif
          <tr>
            <td colspan="3" class="text-end"><strong>TOTAL AMOUNT:</strong></td>
            <td class="text-end">
              <strong class="text-success" style="font-size: 1.25rem;">{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong>
            </td>
          </tr>
          @if($invoice->payments && $invoice->payments->where('status', 'completed')->count() > 0)
          @php
            $totalPaid = $invoice->payments->where('status', 'completed')->sum('amount');
            $balance = $invoice->amount - $totalPaid;
          @endphp
          <tr>
            <td colspan="3" class="text-end">Amount Paid:</td>
            <td class="text-end">
              <span class="text-success">{{ $currency }} {{ number_format($totalPaid, 2) }}</span>
            </td>
          </tr>
          <tr>
            <td colspan="3" class="text-end"><strong>Balance Due:</strong></td>
            <td class="text-end">
              <strong class="text-{{ $balance > 0 ? 'danger' : 'success' }}" style="font-size: 1.25rem;">
                {{ $currency }} {{ number_format($balance, 2) }}
              </strong>
            </td>
          </tr>
          @endif
        </tfoot>
      </table>
    </div>
  </div>
</div>

<!-- Payment History -->
@if($invoice->payments && $invoice->payments->count() > 0)
<div class="ghost-card mb-4">
  <div class="ghost-card-header success">
    <div class="ghost-card-icon">
      <i class="fas fa-credit-card"></i>
    </div>
    <h5 class="mb-0">Payment History</h5>
  </div>
  <div class="ghost-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Reference</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->payments as $payment)
          <tr>
            <td>
              <i class="fas fa-calendar text-muted me-2"></i>
              {{ $payment->created_at->format('M d, Y h:i A') }}
            </td>
            <td>
              <strong class="text-success">{{ $currency }} {{ number_format($payment->amount, 2) }}</strong>
            </td>
            <td>
              @if($payment->payment_method)
              <i class="fas fa-credit-card text-muted me-2"></i>
              {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
              @else
              <span class="text-muted">N/A</span>
              @endif
            </td>
            <td>
              @if($payment->payment_reference)
              <code>{{ $payment->payment_reference }}</code>
              @else
              <span class="text-muted">N/A</span>
              @endif
            </td>
            <td>
              @if($payment->status === 'completed')
              <span class="badge bg-success">
                <i class="fas fa-check-circle"></i> Completed
              </span>
              @elseif($payment->status === 'pending')
              <span class="badge bg-warning text-dark">
                <i class="fas fa-clock"></i> Pending
              </span>
              @else
              <span class="badge bg-danger">
                <i class="fas fa-times-circle"></i> {{ ucfirst($payment->status) }}
              </span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

<!-- Notes Section -->
@if($invoice->notes)
<div class="ghost-card">
  <div class="ghost-card-header warning">
    <div class="ghost-card-icon">
      <i class="fas fa-sticky-note"></i>
    </div>
    <h5 class="mb-0">Notes</h5>
  </div>
  <div class="ghost-card-body">
    <p class="mb-0">{{ $invoice->notes }}</p>
  </div>
</div>
@endif

<!-- Action Buttons -->
@if($invoice->status === 'pending' || $invoice->status === 'overdue')
<div class="row mt-4">
  <div class="col-12 text-center">
    <div class="alert alert-warning">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <strong>Payment Required:</strong> This invoice is currently {{ $invoice->status }}. Please complete payment to avoid service interruption.
    </div>
    @if($invoice->status === 'pending' && $invoice->remaining_balance || $invoice->status === 'overdue')
    {{-- PayFast Payment Button --}}
    {!! $payfastForm !!}
    {{-- End PayFast Payment Button --}}
    {{-- <a href="#" class="btn btn-danger" onclick="alert('Payment integration coming soon!'); return false;">
      <i class="fas fa-credit-card me-2"></i>Pay Now
    </a> --}}
    {{-- End Pay Now Button --}}
    @endif
    {{-- <a href="#" class="btn btn-success btn-lg" onclick="alert('Payment integration coming soon!'); return false;">
      <i class="fas fa-credit-card me-2"></i>Pay Now
    </a> --}}
  </div>
</div>
@endif

@endsection
