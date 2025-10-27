@extends('tenant.layouts.app')

@section('title', 'Refunds')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="fas fa-undo-alt"></i> Refunds
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Refunds</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
    {{-- messages from redirect --}}
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

    <div class="card card-info card-outline">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-list me-2"></i>All Refunds
        </h5>
        <div class="card-tools">
          @can('create refunds')
          <a href="{{ route('tenant.refunds.create') }}" class="btn btn-info">
            <i class="fas fa-plus me-1"></i>New Refund
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body p-0">
        @if(!$refunds->isEmpty())
        <div class="table-responsive">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Invoice</th>
                <th>Payment</th>
                <th>Requested By</th>
                <th>Created At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($refunds as $refund)
              <tr>
                <td>{{ $refund->id }}</td>
                <td>{{ $currency }} {{ number_format($refund->amount, 2) }}</td>
                <td>{{ Str::limit($refund->reason, 40) }}</td>
                <td>
                  <span class="badge bg-{{ $refund->status === 'approved' ? 'success' : ($refund->status === 'rejected' ? 'danger' : 'warning') }}">
                    {{ ucfirst($refund->status) }}
                  </span>
                </td>
                <td>
                  @if($refund->invoice)
                    <a href="{{ route('tenant.booking-invoices.show', $refund->invoice) }}">#{{ $refund->invoice->invoice_number }}</a>
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>
                  @if($refund->payment)
                    <a href="{{ route('tenant.invoice-payments.show', [$refund->invoice, $refund->payment]) }}">#{{ $refund->payment->id }}</a>
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>
                  @if($refund->user)
                    {{ $refund->user->name }}
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>{{ $refund->created_at->format('M d, Y') }}</td>
                <td>
                  @can('view refunds')
                  <a href="{{ route('tenant.refunds.show', $refund) }}" class="btn btn-sm btn-outline-info" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  @endcan
                  @can('edit refunds')
                  <a href="{{ route('tenant.refunds.edit', $refund) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  @endcan
                  @can('delete refunds')
                  <form action="{{ route('tenant.refunds.destroy', $refund) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this refund?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                  @endcan
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No refunds found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-dollar-sign fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No Refunds Found</h5>
          <p class="text-muted">No refunds match your current filters.</p>
          
        </div>
        @endif
      </div>
      {{-- Pagination links --}}
      {{-- Beautiful pagination --}}
      @if($refunds->hasPages())
      <div class="container-fluid py-3">
        <div class="row align-items-center">
            <div class="col-md-12 float-end">
                {{ $refunds->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
