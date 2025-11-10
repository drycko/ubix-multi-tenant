@extends('central.layouts.app')

@section('title', 'Edit Tax')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="fas fa-edit"></i>
          Edit Tax
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.taxes.index') }}">Taxes</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h5><i class="icon fas fa-ban"></i> Validation Error!</h5>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-md-8 mx-auto">
        <div class="card card-warning">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-percentage"></i> Tax Information
            </h3>
          </div>
          
          <form action="{{ route('central.taxes.update', $tax) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
              <!-- Tax Name -->
              <div class="row mb-3">
                <div class="col-md-12">
                  <label for="name" class="form-label">
                    Tax Name <span class="text-danger">*</span>
                  </label>
                  <input type="text" 
                         class="form-control @error('name') is-invalid @enderror" 
                         id="name" 
                         name="name" 
                         value="{{ old('name', $tax->name) }}" 
                         placeholder="e.g., VAT, Sales Tax, GST"
                         required>
                  @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Descriptive name for this tax.</small>
                </div>
              </div>

              <!-- Tax Type and Rate -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="type" class="form-label">
                    Tax Type <span class="text-danger">*</span>
                  </label>
                  <select class="form-select @error('type') is-invalid @enderror" 
                          id="type" 
                          name="type" 
                          required 
                          onchange="updateRateLabel()">
                    <option value="">Select Type</option>
                    <option value="percentage" {{ old('type', $tax->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed" {{ old('type', $tax->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                  </select>
                  @error('type')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="rate" class="form-label">
                    <span id="rate-label">Tax Rate</span> <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input type="number" 
                           class="form-control @error('rate') is-invalid @enderror" 
                           id="rate" 
                           name="rate" 
                           value="{{ old('rate', $tax->rate) }}" 
                           step="0.0001" 
                           min="0" 
                           placeholder="0.00"
                           required>
                    <span class="input-group-text" id="rate-suffix">%</span>
                  </div>
                  @error('rate')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted" id="rate-help">Enter the tax rate as a percentage (e.g., 15 for 15%).</small>
                </div>
              </div>

              <!-- Application Method -->
              <div class="row mb-3">
                <div class="col-md-12">
                  <label class="form-label">
                    Application Method <span class="text-danger">*</span>
                  </label>
                  <div class="form-check">
                    <input class="form-check-input" 
                           type="radio" 
                           name="is_inclusive" 
                           id="is_inclusive_0" 
                           value="0" 
                           {{ old('is_inclusive', $tax->is_inclusive) == '0' ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_inclusive_0">
                      <strong>Additional Tax</strong> - Tax is added on top of the subtotal
                      <br><small class="text-muted">Total = Subtotal + Tax</small>
                    </label>
                  </div>
                  <div class="form-check mt-2">
                    <input class="form-check-input" 
                           type="radio" 
                           name="is_inclusive" 
                           id="is_inclusive_1" 
                           value="1" 
                           {{ old('is_inclusive', $tax->is_inclusive) == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_inclusive_1">
                      <strong>Inclusive Tax</strong> - Tax is already included in the price
                      <br><small class="text-muted">Tax is calculated from the total amount</small>
                    </label>
                  </div>
                  @error('is_inclusive')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Status -->
              <div class="row mb-3">
                <div class="col-md-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $tax->is_active) == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                      <strong>Active</strong> - Enable this tax for use
                    </label>
                  </div>
                  <small class="form-text text-muted">Only active taxes can be applied to subscription invoices.</small>
                </div>
              </div>

              <!-- Display Order -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="display_order" class="form-label">
                    Display Order
                  </label>
                  <input type="number" 
                         class="form-control @error('display_order') is-invalid @enderror" 
                         id="display_order" 
                         name="display_order" 
                         value="{{ old('display_order', $tax->display_order) }}" 
                         min="0">
                  @error('display_order')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="form-text text-muted">Lower numbers appear first.</small>
                </div>
              </div>

              <!-- Description -->
              <div class="row mb-3">
                <div class="col-md-12">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3" 
                            placeholder="Optional description of this tax...">{{ old('description', $tax->description) }}</textarea>
                  @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Warning Alert for existing invoices -->
              @if($tax->subscriptionInvoices()->count() > 0)
              <div class="alert alert-warning">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Warning!</h5>
                <p class="mb-0">This tax is currently used in <strong>{{ $tax->subscriptionInvoices()->count() }}</strong> subscription invoice(s). Changing the tax details may affect the calculation of future invoices.</p>
              </div>
              @endif

              <!-- Information Alert -->
              <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> Tax Information</h5>
                <ul class="mb-0">
                  <li>This tax will be available for all subscription invoices.</li>
                  <li>Percentage taxes are calculated based on the invoice subtotal.</li>
                  <li>Fixed taxes are applied as a flat amount regardless of invoice total.</li>
                  <li>Inclusive taxes are already included in the price and are shown for transparency.</li>
                </ul>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Update Tax
              </button>
              <a href="{{ route('central.taxes.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
              </a>
              <a href="{{ route('central.taxes.show', $tax) }}" class="btn btn-outline-primary">
                <i class="fas fa-eye"></i> View
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->
@endsection

@push('scripts')
<script>
function updateRateLabel() {
    const typeSelect = document.getElementById('type');
    const rateLabel = document.getElementById('rate-label');
    const rateSuffix = document.getElementById('rate-suffix');
    const rateHelp = document.getElementById('rate-help');
    
    if (typeSelect.value === 'percentage') {
        rateLabel.textContent = 'Tax Rate (%)';
        rateSuffix.textContent = '%';
        rateHelp.textContent = 'Enter the tax rate as a percentage (e.g., 15 for 15%).';
    } else if (typeSelect.value === 'fixed') {
        rateLabel.textContent = 'Tax Amount';
        rateSuffix.textContent = 'R';
        rateHelp.textContent = 'Enter the fixed tax amount in Rands.';
    } else {
        rateLabel.textContent = 'Tax Rate';
        rateSuffix.textContent = '%';
        rateHelp.textContent = 'Select a tax type first.';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRateLabel();
});
</script>
@endpush
