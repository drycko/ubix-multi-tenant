@extends('tenant.layouts.app')

@section('title', 'Edit Refund')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="fas fa-undo-alt"></i> Edit Refund
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.refunds.index') }}">Refunds</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Refund</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    {{-- Error Messages --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h6>Please correct the following errors:</h6>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-xl-8 col-lg-7">
        <div class="card card-info card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-plus me-2"></i>Edit Refund
            </h5>
          </div>
          <form action="{{ route('tenant.refunds.update', $refund) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
              <div class="mb-3">
                <label for="payment_id" class="form-label">Related Payment <span class="text-danger">*</span></label>
                <select name="payment_id" id="payment_id" class="form-select @error('payment_id') is-invalid @enderror" readonly disabled>
                  <option value="" disabled>Select Payment</option>
                  @foreach($payments as $payment)
                    <option value="{{ $payment->id }}" {{ old('payment_id', $refund->payment_id) == $payment->id ? 'selected' : '' }} data-invid="{{ $payment->booking_invoice_id }}" data-amount="{{ $payment->refundable_amount }}">#{{ $payment->id }} - {{ $currency }} {{ number_format($payment->amount, 2) }}</option>
                  @endforeach
                </select>
                @error('payment_id')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $refund->amount) }}" required >
                @error('amount')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                <textarea name="reason" id="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason', $refund->reason) }}</textarea>
                @error('reason')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="invoice_id" class="form-label">Related Invoice (readonly)</label>
                <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" readonly disabled>
                  <option value="" disabled>Select Invoice</option>
                  @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}" {{ old('invoice_id', $refund->invoice_id) == $invoice->id ? 'selected' : '' }}>#{{ $invoice->invoice_number }} - {{ $currency }} {{ number_format($invoice->amount, 2) }}</option>
                  @endforeach
                </select>
                @error('invoice_id')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                  <option value="pending" {{ old('status', $refund->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                  <option value="approved" {{ old('status', $refund->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                  <option value="rejected" {{ old('status', $refund->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                @error('status')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-info">
                <i class="fas fa-save me-1"></i>Save Refund
              </button>
              <a href="{{ route('tenant.refunds.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
      <!-- Refund Guidelines -->
      <div class="col-xl-4 col-lg-5">
        <div class="card card-info card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle me-2"></i>Refund Guidelines
            </h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled mb-0">
              <li class="mb-2 border-bottom pb-2">
                <i class="bi bi-check text-primary me-2"></i>
                <strong>Fields marked with <span class="text-danger">*</span></strong> are required.
              </li>
              <li class="mb-2 border-bottom pb-2">
                <i class="bi bi-check text-primary me-2"></i>
                <strong>Amount:</strong> Enter the exact amount to be refunded. Ensure it does not exceed the original payment amount.
              </li>
              <li class="mb-2 border-bottom pb-2">
                <i class="bi bi-check text-primary me-2"></i>
                <strong>Reason:</strong> Provide a clear and concise reason for the refund request.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const paymentSelect = document.getElementById('payment_id');
    const invoiceSelect = document.getElementById('invoice_id');

    paymentSelect.addEventListener('change', function () {
      const selectedOption = this.options[this.selectedIndex];
      const relatedInvoiceId = selectedOption.getAttribute('data-invid');

      if (relatedInvoiceId) {
        invoiceSelect.value = relatedInvoiceId;
      } else {
        invoiceSelect.value = '';
      }

      // set amount field to max refundable amount
      const amountInput = document.getElementById('amount');
      amountInput.value = selectedOption.getAttribute('data-amount');
    });
  });
</script>
@endpush
@endsection
