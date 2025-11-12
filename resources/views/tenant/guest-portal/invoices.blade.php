@extends('tenant.layouts.guest')

@section('title', 'My Invoices')

@section('content')
<div class="container-fluid py-5">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="fw-bold text-success mb-2">
            <i class="bi bi-receipt me-2"></i>
            My Invoices
          </h2>
          <p class="text-muted mb-0">View and manage your invoices</p>
        </div>
        <div>
          <a href="{{ route('tenant.guest-portal.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i> Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="row mb-4">
    <div class="col-12">
      <ul class="nav nav-pills">
        <li class="nav-item">
          <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.invoices', ['status' => 'all']) }}">
            All Invoices
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.invoices', ['status' => 'pending']) }}">
            <i class="bi bi-hourglass-split me-1"></i> Pending
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $status === 'partially_paid' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.invoices', ['status' => 'partially_paid']) }}">
            <i class="bi bi-coin me-1"></i> Partially Paid
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $status === 'paid' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.invoices', ['status' => 'paid']) }}">
            <i class="bi bi-check-circle me-1"></i> Paid
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $status === 'overdue' ? 'active' : '' }}" 
             href="{{ route('tenant.guest-portal.invoices', ['status' => 'overdue']) }}">
            <i class="bi bi-exclamation-triangle me-1"></i> Overdue
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Invoices List -->
  @if($invoices->count() > 0)
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Invoice #</th>
                  <th>Booking Code</th>
                  <th>Room</th>
                  <th>Amount</th>
                  <th>Paid</th>
                  <th>Balance</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($invoices as $invoice)
                <tr>
                  <td>
                    <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                  </td>
                  <td>{{ $invoice->booking->bcode ?? 'N/A' }}</td>
                  <td>
                    <div>
                      <strong>{{ $invoice->booking->room->type->name ?? 'N/A' }}</strong>
                      <br>
                      <small class="text-muted">Room {{ $invoice->booking->room->number ?? 'N/A' }}</small>
                    </div>
                  </td>
                  <td>{{ tenant_currency() }} {{ number_format($invoice->amount, 2) }}</td>
                  <td>{{ tenant_currency() }} {{ number_format($invoice->total_paid, 2) }}</td>
                  <td class="{{ $invoice->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                    <strong>{{ tenant_currency() }} {{ number_format($invoice->remaining_balance, 2) }}</strong>
                  </td>
                  <td>
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
                  </td>
                  <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') }}</td>
                  <td>
                    <div class="btn-group" role="group">
                      <a href="{{ route('tenant.guest-portal.invoices.show', $invoice->id) }}" 
                         class="btn btn-sm btn-outline-primary"
                         title="View Invoice">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="{{ route('tenant.guest-portal.invoices.download', $invoice->id) }}" 
                         class="btn btn-sm btn-outline-success"
                         title="Download PDF">
                        <i class="bi bi-download"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-3">
            {{ $invoices->appends(['status' => $status])->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
  @else
  <!-- No Invoices Message -->
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
          <i class="bi bi-receipt display-1 text-muted mb-3"></i>
          <h4>No Invoices Found</h4>
          <p class="text-muted mb-4">
            @if($status === 'all')
              You don't have any invoices yet.
            @else
              No {{ $status }} invoices found.
            @endif
          </p>
          <a href="{{ route('tenant.guest-portal.bookings') }}" class="btn btn-primary">
            <i class="bi bi-list-check me-2"></i>
            View Bookings
          </a>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
