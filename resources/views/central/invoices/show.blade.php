@extends('central.layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 text-muted">
        <h3 class="mb-0">Invoice #{{ $invoice->invoice_number }}</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.invoices.index') }}">Invoices</a></li>
          <li class="breadcrumb-item active">Invoice #{{ $invoice->invoice_number }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
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
    
    {{-- let's add a manage buttons card here --}}
    <div class="card card-success card-outline mb-4">
      <div class="card-body row">
        <div class="col-6 d-flex justify-content-between">
          <a class="btn btn-sm btn-outline-secondary me-2" href="{{ route('central.invoices.index') }}">
            <i class="fas fa-arrow-left me-2"></i>Back to Invoices
          </a>
        </div>
        <div class="col-6 d-flex justify-content-end">
          
          @can('download invoices')
          <a href="{{ route('central.invoices.download', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-download me-2"></i>Download PDF
          </a>
          <a href="{{ route('central.invoices.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-primary me-2">
            <i class="fas fa-print me-2"></i>Print
          </a>
          @endcan
          @can('manage invoices')
          @if($invoice->payments->isEmpty())
          <a href="{{ route('central.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-outline-warning me-2">
            <i class="fas fa-edit me-2"></i>Edit
          </a>
          <form action="{{ route('central.invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="fas fa-trash-alt me-2"></i>Delete
            </button>
          </form>
          @endif
          @endcan
        </div>
      </div>
    </div>
    
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <!-- Invoice Header -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h2 class="mb-1">{{ $app_name }}</h2>
                <div class="text-muted fs-6">
                  {{ $app_address_line_1 }}<br>
                  @if($app_address_line_2){{ $app_address_line_2 }}<br>@endif
                  {{ $app_address_city }}, {{ $app_address_state }} {{ $app_address_zip }}
                </div>
              </div>
              <div class="text-end">
                <h5 class="text-uppercase text-muted mb-3">Invoice</h5>
                <h6 class="mb-1">#{{ $invoice->invoice_number }}</h6>
                <div class="text-muted">Issued: {{ $invoice->created_at->format('M d, Y') }}</div>
                <div class="text-muted">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
              </div>
            </div>
          </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Billing Info -->
        <div class="row mb-4">
          <div class="col-md-6">
            <h6 class="text-uppercase text-muted mb-3">Billed To:</h6>
            <h5 class="mb-2">{{ $tenant_name }}</h5>
            <div class="text-muted">
              @if($tenant_address_line_1){{ $tenant_address_line_1 }}<br>@endif
              @if($tenant_address_line_2){{ $tenant_address_line_2 }}<br>@endif
              @if($tenant_address_city || $tenant_address_state)
              {{ $tenant_address_city }}@if($tenant_address_state), {{ $tenant_address_state }}@endif {{ $tenant_address_zip }}<br>
              @endif
              @if($invoice->tenant->phone)Phone: {{ $invoice->tenant->phone }}<br>@endif
              Email: {{ $invoice->tenant->email }}
            </div>
          </div>
          <div class="col-md-6 text-md-end">
            <h6 class="text-uppercase text-muted mb-3">Invoice Details:</h6>
            <div class="text-muted">
              <div class="mb-1">
                <span class="text-dark">Subscription ID:</span> {{ $invoice->subscription->id ?? 'N/A' }}
              </div>
              <div class="mb-1">
                <span class="text-dark">Plan:</span> 
                {{ $invoice->subscription->plan->name ?? 'N/A' }}
              </div>
              <div class="mb-1">
                <span class="text-dark">Billing Cycle:</span> 
                {{ ucfirst($invoice->subscription->billing_cycle ?? 'N/A') }}
              </div>
              @if($invoice->status !== 'paid')
              @can('manage invoices')
              <div class="mt-3">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payInvoiceModal">
                  <i class="fas fa-credit-card me-2"></i>Pay Now
                </button>
              </div>
              @endcan
              @endif
              
            </div>
          </div>
        </div>
        
        <!-- Invoice Items -->
        <div class="table-responsive mb-4">
          <table class="table table-hover align-middle">
            <thead class="bg-light">
              <tr>
                <th scope="col" class="text-uppercase text-muted fs-6 fw-semibold">Description</th>
                <th scope="col" class="text-uppercase text-muted fs-6 fw-semibold text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <h6 class="mb-1">{{ $invoice->subscription->plan->name ?? 'Subscription Plan' }}</h6>
                  <div class="text-muted">
                    {{ ucfirst($invoice->subscription->billing_cycle ?? '') }} billing cycle
                    ({{ $invoice->subscription->start_date ? \Carbon\Carbon::parse($invoice->subscription->start_date)->format('M d, Y') : 'N/A' }} 
                    - {{ $invoice->subscription->end_date ? \Carbon\Carbon::parse($invoice->subscription->end_date)->format('M d, Y') : 'N/A' }})
                  </div>
                </td>
                <td class="text-end">{{ format_price($invoice->amount, $invoice->currency) }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-light">
              @if($invoice->tax_amount > 0)
              <tr>
                <td class="text-end">Subtotal:</td>
                <td class="text-end">{{ format_price($invoice->subtotal_amount, $invoice->currency) }}</td>
              </tr>
              <tr>
                <td class="text-end">
                  Tax ({{ $invoice->tax_name }}
                  @if($invoice->tax_type === 'percentage')
                  - {{ number_format($invoice->tax_rate, 2) }}%
                  @else
                  - Fixed Amount
                  @endif
                  @if($invoice->tax_inclusive)
                  <small class="text-info">- Inclusive</small>
                  @endif
                  ):
                </td>
                <td class="text-end">{{ format_price($invoice->tax_amount, $invoice->currency) }}</td>
              </tr>
              @endif
              <tr>
                <td class="text-end text-uppercase fs-6 fw-semibold">Total</td>
                <td class="text-end fs-5 fw-bold">{{ format_price($invoice->amount, $invoice->currency) }}</td>
              </tr>
              @if($invoice->payments && $invoice->payments->where('status', 'completed')->count() > 0)
              @php
              $totalPaid = $invoice->payments->where('status', 'completed')->sum('amount');
              $balance = $invoice->amount - $totalPaid;
              @endphp
              <tr>
                <td class="text-end">Amount Paid:</td>
                <td class="text-end text-success">{{ format_price($totalPaid, $invoice->currency) }}</td>
              </tr>
              <tr>
                <td class="text-end fw-semibold">Balance Due:</td>
                <td class="text-end fw-bold text-{{ $balance > 0 ? 'danger' : 'success' }}">{{ format_price($balance, $invoice->currency) }}</td>
              </tr>
              @endif
            </tfoot>
          </table>
        </div>
        
        <!-- Status and Actions -->
        <div class="row align-items-center">
          <div class="col-md-6">
            <div class="mb-3 mb-md-0">
              <h6 class="text-uppercase text-muted mb-2">Status</h6>
              @if($invoice->status === 'paid')
              <span class="badge bg-success fs-6">Paid</span>
              @elseif($invoice->status === 'pending')
              <span class="badge bg-warning text-dark fs-6">Pending</span>
              @elseif($invoice->status === 'overdue')
              <span class="badge bg-danger fs-6">Overdue</span>
              @else
              <span class="badge bg-secondary fs-6">{{ ucfirst($invoice->status) }}</span>
              @endif
            </div>
          </div>
          <div class="col-md-6">
            <div class="text-md-end">
              {{-- @can('manage invoices')
              @if($invoice->payments->isEmpty())
              <a href="{{ route('central.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-edit me-2"></i>Edit
              </a>
              @endif
              @endcan
              @can('download invoices')
              <a href="{{ route('central.invoices.download', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download me-2"></i>Download PDF
              </a>
              <a href="{{ route('central.invoices.print', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">
                <i class="fas fa-print me-2"></i>Print
              </a>
              @endcan --}}
            </div>
          </div>
        </div>
        
        @if($invoice->notes)
        <div class="mt-4">
          <h6 class="text-uppercase text-muted mb-2">Notes</h6>
          <div class="text-muted">{{ $invoice->notes }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

<!-- Pay Invoice Modal -->
<div class="modal fade" id="payInvoiceModal" tabindex="-1" aria-labelledby="payInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="payInvoiceModalLabel">Pay Invoice #{{ $invoice->invoice_number }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('central.invoices.pay', $invoice->id) }}" method="POST">
          @csrf
          <p>This allocates {{ format_price($invoice->amount, $invoice->currency) }} to the invoice without a payment gateway.</p>
          
          <div class="mb-3">
            <label for="payment_method" class="form-label">Select Payment Method <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_method" name="payment_method" required>
              <option value="" disabled selected>Select a payment method</option>
              @foreach($paymentMethods as $method)
              <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group mb-3">
            <label for="amount" class="form-label">Amount to Pay <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ $invoice->amount }}" required>
              <span class="input-group-text">{{ $invoice->currency }}</span>
            </div>
            <small class="text-muted">Maximum amount: {{ format_price($invoice->amount, $invoice->currency) }}</small>
          </div>
          <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
          </div>
          <div class="mb-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-credit-card me-2"></i>Process Payment
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  @media print {
    .app-content-header,
    .btn,
    .alert {
      display: none !important;
    }
    .card {
      box-shadow: none !important;
      border: none !important;
    }
    .card-body {
      padding: 0 !important;
    }
  }
</style>
@endpush
@endsection