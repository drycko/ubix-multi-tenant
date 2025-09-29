@extends('central.layouts.app')

@section('title', 'Invoices')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
      {{-- <h3 class="mb-0">Invoices</h3> --}}
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

    {{-- messages from redirect --}}
    @if(session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">
        {{ session('error') }}
      </div>
    @endif
    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title">Invoices</h5>
        <div class="card-tools float-end">
          @can('view subscriptions')
          <a href="{{ route('central.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Go to Subscriptions
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Invoice Number</th>
                <th scope="col">Tenant</th>
                <th scope="col">Subscription</th>
                <th scope="col">Amount</th>
                <th scope="col">Status</th>
                <th scope="col">Date</th>
                <th scope="col" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($invoices as $invoice)
              <tr>
                <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td><a href="{{ route('central.tenants.show', $invoice->tenant->id) }}">{{ $invoice->tenant ? $invoice->tenant->name : 'N/A' }}</a></td>
                <td>
                  @if($invoice->subscription)
                  <a href="{{ route('central.subscriptions.show', $invoice->subscription->id) }}">
                    {{ $invoice->subscription->plan->name ?? $invoice->subscription->id }}
                  </a>
                  @else
                  N/A
                  @endif
                </td>
                <td>{{ format_price($invoice->amount, $invoice->currency) }}</td>
                <td>
                  @if($invoice->status === 'paid')
                  <span class="badge bg-success">Paid</span>
                  @elseif($invoice->status === 'pending')
                  <span class="badge bg-warning text-dark">Pending</span>
                  @elseif($invoice->status === 'failed')
                  <span class="badge bg-danger">Failed</span>
                  @else
                  <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                  @endif
                </td>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                <td class="text-start">
                  @can('view invoices')
                  <a href="{{ route('central.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-success me-2" tooltip="View Details">
                    <i class="fas fa-eye"></i>
                  </a>
                  @endcan
                  @can('download invoices')
                  <a href="{{ route('central.invoices.download', $invoice->id) }}" class="btn btn-sm btn-outline-primary me-2" tooltip="Download PDF">
                    <i class="fas fa-download"></i>
                  </a>
                  @endcan
                  @if($invoice->status === 'pending')
                  {{-- only show pay and cancel buttons if invoice is pending --}}
                  {{-- use modal to confirm payment --}}
                  @can('manage invoices')
                  {{-- use modal to confirm cancellation --}}
                  <a href="#" class="btn btn-sm btn-outline-danger me-2" tooltip="Cancel Invoice" data-bs-toggle="modal" data-bs-target="#cancelInvoiceModal" data-invoice-id="{{ $invoice->id }}">
                    <i class="fas fa-trash"></i>
                  </a>
                  @endcan
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center">No invoices found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
          {{-- Pagination links --}}
          @if($invoices->hasPages())
          <div class="card-footer bg-light py-3">
              <div class="row align-items-center">
                  <div class="col-md-8">
                      <p class="mb-0 text-muted">
                          Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} entries
                      </p>
                  </div>
                  <div class="col-md-4 float-end">
                      {{ $invoices->links('vendor.pagination.bootstrap-5') }} {{-- I want to align the links to the end of the column --}}
                  </div>
              </div>
          </div>
          @endif
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

{{-- pay invoice modal --}}
<div class="modal fade" id="payInvoiceModal" tabindex="-1" aria-labelledby="payInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="payInvoiceModalLabel">Pay Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="payInvoiceForm" method="POST" action="">
        @csrf
        <div class="modal-body">
          <p>Are you sure you want to mark this invoice as paid?</p>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- cancel invoice modal --}}
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-labelledby="cancelInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cancelInvoiceModalLabel">Cancel Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="cancelInvoiceForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete this invoice? </p>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-sm btn-danger">Yes, Delete</button>
          <a type="button" class="btn btn-sm btn-secondary" >Cancel Invoice</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // pass the invoice id to the modal form action
  var payInvoiceModal = document.getElementById('payInvoiceModal');
  payInvoiceModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var invoiceId = button.getAttribute('data-invoice-id');
    var form = document.getElementById('payInvoiceForm');
    form.action = '/central/invoices/' + invoiceId + '/pay';
  });

  var deleteInvoiceModal = document.getElementById('deleteInvoiceModal');
  deleteInvoiceModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var invoiceId = button.getAttribute('data-invoice-id');
    var form = document.getElementById('deleteInvoiceForm');
    form.action = '/central/invoices/' + invoiceId;
  });

</script>
@endsection