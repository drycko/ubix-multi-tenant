@extends('portal.layouts.app')

@section('page-title', 'Invoices')

@section('content')
<!-- Summary Cards at Top -->
@if($invoices->count() > 0)
<div class="row mb-4">
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-icon text-success">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-value">{{ $invoices->where('status', 'paid')->count() }}</div>
      <div class="stat-label">Paid Invoices</div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-icon text-warning">
        <i class="fas fa-clock"></i>
      </div>
      <div class="stat-value">{{ $invoices->where('status', 'pending')->count() }}</div>
      <div class="stat-label">Pending Invoices</div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-icon text-danger">
        <i class="fas fa-exclamation-circle"></i>
      </div>
      <div class="stat-value">{{ $invoices->where('status', 'overdue')->count() }}</div>
      <div class="stat-label">Overdue Invoices</div>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="stat-card">
      <div class="stat-icon text-primary">
        <i class="fas fa-dollar-sign"></i>
      </div>
      <div class="stat-value text-truncate">{{ $currency }} {{ number_format($invoices->sum('amount'), 2) }}</div>
      <div class="stat-label">Total Billed</div>
    </div>
  </div>
</div>
@endif

<!-- Invoices Table -->
<div class="ghost-card">
  <div class="ghost-card-header warning">
    <div class="ghost-card-icon">
      <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div class="flex-grow-1">
      <h4 class="mb-1">Invoice History</h4>
      <p class="mb-0 opacity-75">View and download your billing history</p>
    </div>
  </div>
  <div class="ghost-card-body p-0">
    @if($invoices->count() > 0)
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>
              <i class="fas fa-hashtag me-2 text-primary"></i>Invoice Number
            </th>
            <th>
              <i class="fas fa-calendar me-2 text-info"></i>Date
            </th>
            <th>
              <i class="fas fa-calendar-check me-2 text-success"></i>Due Date
            </th>
            <th>
              <i class="fas fa-dollar-sign me-2 text-success"></i>Amount
            </th>
            <th>
              <i class="fas fa-info-circle me-2 text-warning"></i>Status
            </th>
            <th class="text-center">
              <i class="fas fa-cog me-2"></i>Actions
            </th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoices as $invoice)
          <tr>
            <td>
              <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
            </td>
            <td>
              <span class="text-nowrap">
                <i class="fas fa-calendar-alt text-muted me-1"></i>
                {{ $invoice->invoice_date->format('M d, Y') }}
              </span>
            </td>
            <td>
              <span class="text-nowrap">
                <i class="fas fa-calendar-check text-muted me-1"></i>
                {{ $invoice->due_date->format('M d, Y') }}
              </span>
              @if($invoice->status !== 'paid' && $invoice->due_date->isPast())
              <br><span class="badge bg-danger mt-1">Overdue</span>
              @endif
            </td>
            <td>
              <strong class="text-success text-nowrap">{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong>
            </td>
            <td>
              @if($invoice->status === 'paid')
              <span class="badge bg-success">
                <i class="fas fa-check-circle"></i> Paid
              </span>
              @elseif($invoice->status === 'pending')
              <span class="badge bg-warning text-dark">
                <i class="fas fa-clock"></i> Pending
              </span>
              @elseif($invoice->status === 'overdue')
              <span class="badge bg-danger">
                <i class="fas fa-exclamation-circle"></i> Overdue
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
                <a href="{{ route('portal.invoices.show', $invoice) }}" class="btn btn-outline-info" title="View Details">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('portal.invoices.download', $invoice) }}" class="btn btn-outline-primary" title="Download PDF">
                  <i class="fas fa-download"></i>
                </a>
                <a href="{{ route('portal.invoices.print', $invoice) }}" class="btn btn-outline-secondary" target="_blank" title="Print Invoice">
                  <i class="fas fa-print"></i>
                </a>
                @if($invoice->status === 'pending' || $invoice->status === 'overdue')
                <a href="#" class="btn btn-outline-success" title="Pay Now" onclick="alert('Payment integration coming soon!'); return false;">
                  <i class="fas fa-credit-card"></i>
                </a>
                @endif
              </div>
                <!-- Invoice Detail Modal (make dynamic) -->
                <div class="modal fade" id="invoiceDetailModal{{ $invoice->id }}" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header bg-light">
                        <h5 class="modal-title">
                          <i class="fas fa-file-invoice me-2"></i>Invoice Details - {{ $invoice->invoice_number }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row mb-4">
                          <div class="col-md-6">
                            <h6 class="text-muted small mb-2">BILLED TO</h6>
                            <p class="mb-1"><strong>{{ $tenant->name }}</strong></p>
                            <p class="mb-1">{{ $tenant->contact_person }}</p>
                            <p class="mb-1">{{ $tenant->email }}</p>
                            @if($tenant->contact_number)
                            <p class="mb-0">{{ $tenant->contact_number }}</p>
                            @endif
                          </div>
                          <div class="col-md-6 text-md-end">
                            <h6 class="text-muted small mb-2">INVOICE INFORMATION</h6>
                            <p class="mb-1"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                            <p class="mb-1"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F d, Y') }}</p>
                            <p class="mb-1"><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</p>
                            <p class="mb-0">
                              <strong>Status:</strong>
                              @if($invoice->status === 'paid')
                              <span class="badge bg-success">Paid</span>
                              @else
                              <span class="badge bg-warning">{{ ucfirst($invoice->status) }}</span>
                              @endif
                            </p>
                          </div>
                        </div>
                        
                        <div class="table-responsive mb-4">
                          <table class="table table-bordered">
                            <thead class="table-light">
                              <tr>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>
                                  {{ $invoice->notes ?? 'Subscription Payment' }}
                                  @if($invoice->subscription)
                                  <br>
                                  <small class="text-muted">
                                    Plan: {{ $invoice->subscription->plan->name ?? 'N/A' }}
                                  </small>
                                  @endif
                                </td>
                                <td class="text-end">
                                  <strong>{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong>
                                </td>
                              </tr>
                            </tbody>
                            <tfoot class="table-light">
                              <tr>
                                <th class="text-end">Total Amount:</th>
                                <th class="text-end">
                                  <strong class="text-success">{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong>
                                </th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                        
                        @if($invoice->payment_reference)
                        <div class="alert alert-info">
                          <strong><i class="fas fa-receipt me-2"></i>Payment Reference:</strong> {{ $invoice->payment_reference }}
                        </div>
                        @endif
                        
                        @if($invoice->status === 'paid' && $invoice->paid_at)
                        <div class="alert alert-success">
                          <strong><i class="fas fa-check-circle me-2"></i>Paid On:</strong> {{ $invoice->paid_at->format('F d, Y \a\t h:i A') }}
                        </div>
                        @endif
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                          <i class="fas fa-times me-2"></i>Close
                        </button>
                        <a href="{{ route('portal.invoices.show', $invoice) }}" class="btn btn-info">
                          <i class="fas fa-eye me-2"></i>View Full Details
                        </a>
                        <a href="{{ route('portal.invoices.download', $invoice) }}" class="btn btn-primary">
                          <i class="fas fa-download me-2"></i>Download PDF
                        </a>
                        @if($invoice->status === 'pending' || $invoice->status === 'overdue')
                        <a href="#" class="btn btn-success" onclick="alert('Payment integration coming soon!'); return false;">
                          <i class="fas fa-credit-card me-2"></i>Pay Now
                        </a>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
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
    @else
    <div class="text-center py-5">
      <i class="fas fa-inbox text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
      <h5 class="text-muted mt-4">No Invoices Found</h5>
      <p class="text-muted mb-4">You don't have any invoices yet.</p>
      <a href="{{ route('portal.subscription') }}" class="btn btn-primary">
        <i class="fas fa-credit-card me-2"></i>View Subscription Plans
      </a>
    </div>
    @endif
  </div>
</div>

@endsection
