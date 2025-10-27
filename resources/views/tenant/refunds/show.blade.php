@extends('tenant.layouts.app')

@section('title', 'Refund Details')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="fas fa-undo-alt"></i> Refund Details
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.refunds.index') }}">Refunds</a></li>
          <li class="breadcrumb-item active" aria-current="page">Refund #{{ $refund->id }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

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

    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card card-info card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-undo-alt me-2"></i>Refund #{{ $refund->id }}
            </h5>
          </div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-sm-4">Amount</dt>
              <dd class="col-sm-8">R {{ number_format($refund->amount, 2) }}</dd>

              <dt class="col-sm-4">Reason</dt>
              <dd class="col-sm-8">{{ $refund->reason }}</dd>

              <dt class="col-sm-4">Status</dt>
              <dd class="col-sm-8">
                <span class="badge bg-{{ $refund->status === 'approved' ? 'success' : ($refund->status === 'rejected' ? 'danger' : 'warning') }}">
                  {{ ucfirst($refund->status) }}
                </span>
              </dd>

              <dt class="col-sm-4">Related Invoice</dt>
              <dd class="col-sm-8">
                @if($refund->invoice)
                  <a href="{{ route('tenant.booking-invoices.show', $refund->invoice) }}">Invoice #{{ $refund->invoice->invoice_number }}</a>
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </dd>

              <dt class="col-sm-4">Related Payment</dt>
              <dd class="col-sm-8">
                @if($refund->payment)
                  <a href="{{ route('tenant.invoice-payments.show', [$refund->invoice, $refund->payment]) }}">Payment #{{ $refund->payment->id }}</a>
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </dd>

              <dt class="col-sm-4">Requested By</dt>
              <dd class="col-sm-8">
                @if($refund->user)
                  {{ $refund->user->name }}
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </dd>

              <dt class="col-sm-4">Created At</dt>
              <dd class="col-sm-8">{{ $refund->created_at->format('M d, Y \a\t g:i A') }}</dd>

              <dt class="col-sm-4">Last Updated</dt>
              <dd class="col-sm-8">{{ $refund->updated_at->format('M d, Y \a\t g:i A') }}</dd>

              <dt class="col-sm-4">Gateway Response</dt>
              <dd class="col-sm-8">
                <pre class="mb-0">{{ $refund->gateway_response ?? 'N/A' }}</pre>
              </dd>
            </dl>
          </div>
          <div class="card-footer text-end">
            @can('edit refunds')
            <a href="{{ route('tenant.refunds.edit', $refund) }}" class="btn btn-info">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            @endcan
            @can('delete refunds')
            <form action="{{ route('tenant.refunds.destroy', $refund) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this refund?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Delete
              </button>
            </form>
            @endcan
            <a href="{{ route('tenant.refunds.index') }}" class="btn btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to Refunds
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
