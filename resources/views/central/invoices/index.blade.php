@extends('central.layouts.app')

@section('title', 'Subscription Invoices')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-file-invoice-dollar me-2"></i>Subscription Invoices
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Invoices</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->
<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">

    {{-- Summary Stats Cards --}}
    @php
      $totalPaid = $invoices->where('status', 'paid')->count();
      $totalPending = $invoices->where('status', 'pending')->count();
      $totalOverdue = $invoices->where('status', 'overdue')->count();
      $totalCancelled = $invoices->where('status', 'cancelled')->count();
      $totalAmount = $invoices->sum('amount');
      $paidAmount = $invoices->where('status', 'paid')->sum('amount');
      $pendingAmount = $invoices->whereIn('status', ['pending', 'overdue'])->sum('amount');
    @endphp

    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-check-circle fa-3x opacity-75"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h3 class="mb-0">{{ $totalPaid }}</h3>
                <p class="mb-0 small">Paid Invoices</p>
                <small class="opacity-75">{{ format_price($paidAmount) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-dark">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-clock fa-3x opacity-75"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h3 class="mb-0">{{ $totalPending }}</h3>
                <p class="mb-0 small">Pending Invoices</p>
                <small class="opacity-75">{{ format_price($pendingAmount) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-3x opacity-75"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h3 class="mb-0">{{ $totalOverdue }}</h3>
                <p class="mb-0 small">Overdue Invoices</p>
                <small class="opacity-75">Requires Attention</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <i class="fas fa-dollar-sign fa-3x opacity-75"></i>
              </div>
              <div class="flex-grow-1 ms-3">
                <h3 class="mb-0">{{ $invoices->total() }}</h3>
                <p class="mb-0 small">Total Invoices</p>
                <small class="opacity-75">{{ format_price($totalAmount) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Messages from redirect --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Filters and Search --}}
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" action="{{ route('central.invoices.index') }}" class="row g-3">
          <div class="col-md-3">
            <label class="form-label"><i class="fas fa-search me-1"></i>Search Invoice</label>
            <input type="text" class="form-control" name="search" placeholder="Invoice number, tenant..." value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label"><i class="fas fa-filter me-1"></i>Status</label>
            <select class="form-select" name="status">
              <option value="">All Statuses</option>
              <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
              <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
              <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label"><i class="fas fa-calendar me-1"></i>From Date</label>
            <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label"><i class="fas fa-calendar-check me-1"></i>To Date</label>
            <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Filter
              </button>
              @if(request()->hasAny(['search', 'status', 'from_date', 'to_date']))
              <a href="{{ route('central.invoices.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Clear
              </a>
              @endif
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Invoices Table --}}
    <div class="card card-primary card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-list me-2"></i>Invoice Management
        </h5>
        <div class="card-tools">
          @can('view subscriptions')
          <a href="{{ route('central.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Subscriptions
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">#</th>
                <th><i class="fas fa-hashtag me-2 text-primary"></i>Invoice Number</th>
                <th><i class="fas fa-building me-2 text-info"></i>Tenant</th>
                <th><i class="fas fa-box me-2 text-success"></i>Plan</th>
                <th><i class="fas fa-calendar me-2"></i>Issue Date</th>
                <th><i class="fas fa-calendar-check me-2"></i>Due Date</th>
                <th><i class="fas fa-dollar-sign me-2 text-success"></i>Amount</th>
                <th><i class="fas fa-info-circle me-2 text-warning"></i>Status</th>
                <th class="text-center" style="width: 200px;"><i class="fas fa-cogs me-2"></i>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($invoices as $invoice)
              <tr>
                <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                <td>
                  <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                </td>
                <td>
                  <a href="{{ route('central.tenants.show', $invoice->tenant->id) }}" class="text-decoration-none">
                    <i class="fas fa-external-link-alt me-1"></i>{{ $invoice->tenant ? $invoice->tenant->name : 'N/A' }}
                  </a>
                </td>
                <td>
                  @if($invoice->subscription)
                  <a href="{{ route('central.subscriptions.show', $invoice->subscription->id) }}" class="text-decoration-none">
                    <span class="badge bg-info">{{ $invoice->subscription->plan->name ?? 'N/A' }}</span>
                  </a>
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>
                  <small class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>{{ $invoice->invoice_date->format('M d, Y') }}
                  </small>
                </td>
                <td>
                  <small class="text-muted">
                    <i class="fas fa-calendar-check me-1"></i>{{ $invoice->due_date->format('M d, Y') }}
                  </small>
                  @if($invoice->status !== 'paid' && $invoice->due_date->isPast())
                    <br><span class="badge bg-danger mt-1"><i class="fas fa-exclamation-circle"></i> Overdue</span>
                  @endif
                </td>
                <td>
                  <strong class="text-success">{{ format_price($invoice->amount, $invoice->currency) }}</strong>
                </td>
                <td>
                  @if($invoice->status === 'paid')
                  <span class="badge bg-success">
                    <i class="fas fa-check-circle"></i> Paid
                  </span>
                  @if($invoice->paid_at)
                    <br><small class="text-muted">{{ $invoice->paid_at->format('M d, Y') }}</small>
                  @endif
                  @elseif($invoice->status === 'pending')
                  <span class="badge bg-warning text-dark">
                    <i class="fas fa-clock"></i> Pending
                  </span>
                  @elseif($invoice->status === 'overdue')
                  <span class="badge bg-danger">
                    <i class="fas fa-exclamation-triangle"></i> Overdue
                  </span>
                  @elseif($invoice->status === 'cancelled')
                  <span class="badge bg-secondary">
                    <i class="fas fa-ban"></i> Cancelled
                  </span>
                  @else
                  <span class="badge bg-dark">
                    <i class="fas fa-question-circle"></i> {{ ucfirst($invoice->status) }}
                  </span>
                  @endif
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    @can('view invoices')
                    <a href="{{ route('central.invoices.show', $invoice->id) }}" 
                       class="btn btn-outline-info" 
                       data-bs-toggle="tooltip" 
                       title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                    @endcan
                    
                    @can('download invoices')
                    <a href="{{ route('central.invoices.print', $invoice->id) }}" 
                       class="btn btn-outline-secondary" 
                       target="_blank"
                       data-bs-toggle="tooltip" 
                       title="Print Invoice">
                      <i class="fas fa-print"></i>
                    </a>
                    <a href="{{ route('central.invoices.download', $invoice->id) }}" 
                       class="btn btn-outline-primary" 
                       data-bs-toggle="tooltip" 
                       title="Download PDF">
                      <i class="fas fa-download"></i>
                    </a>
                    @endcan
                    
                    @if($invoice->status === 'pending' || $invoice->status === 'overdue')
                      @can('manage invoices')
                      <button type="button" 
                              class="btn btn-outline-success" 
                              data-bs-toggle="modal" 
                              data-bs-target="#payInvoiceModal" 
                              data-invoice-id="{{ $invoice->id }}"
                              data-invoice-number="{{ $invoice->invoice_number }}"
                              data-invoice-amount="{{ format_price($invoice->amount, $invoice->currency) }}"
                              title="Mark as Paid">
                        <i class="fas fa-check-circle"></i>
                      </button>
                      <button type="button" 
                              class="btn btn-outline-danger" 
                              data-bs-toggle="modal" 
                              data-bs-target="#cancelInvoiceModal" 
                              data-invoice-id="{{ $invoice->id }}"
                              data-invoice-number="{{ $invoice->invoice_number }}"
                              title="Cancel Invoice">
                        <i class="fas fa-ban"></i>
                      </button>
                      @endcan
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center py-5">
                  <i class="fas fa-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                  <h5 class="text-muted mt-3">No invoices found</h5>
                  <p class="text-muted">Try adjusting your filters or search criteria.</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      
      {{-- Pagination links --}}
      @if($invoices->hasPages())
      <div class="card-footer bg-light py-3">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-muted">
              <i class="fas fa-info-circle me-1"></i>
              Showing <strong>{{ $invoices->firstItem() }}</strong> to <strong>{{ $invoices->lastItem() }}</strong> of <strong>{{ $invoices->total() }}</strong> entries
            </p>
          </div>
          <div class="col-md-6 d-flex justify-content-end">
            {{ $invoices->links('vendor.pagination.bootstrap-5') }}
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
<!--end::App Content-->

{{-- Pay Invoice Modal --}}
<div class="modal fade" id="payInvoiceModal" tabindex="-1" aria-labelledby="payInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="payInvoiceModalLabel">
          <i class="fas fa-check-circle me-2"></i>Mark Invoice as Paid
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="payInvoiceForm" method="POST" action="">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            You are about to manually mark invoice <strong id="payInvoiceNumber"></strong> as paid.
          </div>
          
          <div class="mb-3">
            <label class="form-label">Invoice Amount</label>
            <input type="text" class="form-control" id="payInvoiceAmount" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
            <select class="form-select" name="payment_method" required>
              <option value="">Select payment method...</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="credit_card">Credit Card</option>
              <option value="debit_card">Debit Card</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="paypal">PayPal</option>
              <option value="stripe">Stripe</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Payment Reference/Transaction ID</label>
            <input type="text" class="form-control" name="payment_reference" placeholder="Enter transaction reference...">
            <small class="text-muted">Optional: Bank reference, transaction ID, check number, etc.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="paid_at" value="{{ now()->format('Y-m-d') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Any additional notes about this payment..."></textarea>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="confirmPayment" required>
            <label class="form-check-label" for="confirmPayment">
              I confirm that payment has been received for this invoice
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check-circle me-2"></i>Mark as Paid
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Cancel Invoice Modal --}}
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-labelledby="cancelInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="cancelInvoiceModalLabel">
          <i class="fas fa-ban me-2"></i>Cancel Invoice
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="cancelInvoiceForm" method="POST" action="">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            You are about to cancel invoice <strong id="cancelInvoiceNumber"></strong>.
          </div>
          
          <p class="mb-3">Are you sure you want to cancel this invoice? This action cannot be undone.</p>

          <div class="mb-3">
            <label class="form-label">Reason for Cancellation</label>
            <textarea class="form-control" name="cancellation_reason" rows="3" placeholder="Please provide a reason for cancelling this invoice..." required></textarea>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="confirmCancel" required>
            <label class="form-check-label" for="confirmCancel">
              I confirm that I want to cancel this invoice
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Close
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-ban me-2"></i>Yes, Cancel Invoice
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Initialize tooltips
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });

  // Pay Invoice Modal
  var payInvoiceModal = document.getElementById('payInvoiceModal');
  payInvoiceModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var invoiceId = button.getAttribute('data-invoice-id');
    var invoiceNumber = button.getAttribute('data-invoice-number');
    var invoiceAmount = button.getAttribute('data-invoice-amount');
    
    var form = document.getElementById('payInvoiceForm');
    form.action = '/central/invoices/' + invoiceId + '/pay';
    
    document.getElementById('payInvoiceNumber').textContent = invoiceNumber;
    document.getElementById('payInvoiceAmount').value = invoiceAmount;
  });

  // Cancel Invoice Modal
  var cancelInvoiceModal = document.getElementById('cancelInvoiceModal');
  cancelInvoiceModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var invoiceId = button.getAttribute('data-invoice-id');
    var invoiceNumber = button.getAttribute('data-invoice-number');
    
    var form = document.getElementById('cancelInvoiceForm');
    form.action = '/central/invoices/' + invoiceId + '/cancel';
    
    document.getElementById('cancelInvoiceNumber').textContent = invoiceNumber;
  });
</script>
@endsection