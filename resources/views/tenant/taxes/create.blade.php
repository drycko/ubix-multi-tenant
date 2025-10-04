@extends('tenant.layouts.app')

@section('title', 'Create New Tax')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-percentage"></i>
          Create New Tax
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.taxes.index') }}">Tax Management</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">
    
    {{-- Success/Error Messages --}}
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

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tax Form -->
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-plus"></i> Tax Information
            </h3>
          </div>

          <form action="{{ route('tenant.taxes.store') }}" method="POST">
            @csrf
            <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
                                        <option value="">Select Property</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                                {{ $property->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('property_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tax Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" 
                                           placeholder="e.g., Sales Tax, VAT, Service Tax" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tax Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required onchange="updateRateLabel()">
                                        <option value="">Select Type</option>
                                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rate" class="form-label">
                                        <span id="rate-label">Rate</span> <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="rate-prefix">%</span>
                                        <input type="number" name="rate" id="rate" 
                                               class="form-control @error('rate') is-invalid @enderror"
                                               value="{{ old('rate') }}" 
                                               placeholder="0.00" 
                                               step="0.0001" min="0" max="9999.9999" required>
                                    </div>
                                    @error('rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" name="display_order" id="display_order" 
                                           class="form-control @error('display_order') is-invalid @enderror"
                                           value="{{ old('display_order', 0) }}" 
                                           min="0" placeholder="0">
                                    @error('display_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="3" placeholder="Optional description for this tax">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_inclusive" id="is_inclusive" 
                                               value="1" {{ old('is_inclusive') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_inclusive">
                                            Tax Inclusive
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Check if tax is already included in the price, uncheck if tax should be added to the price.
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Only active taxes will be applied to invoices.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Preview -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-1"></i>Tax Preview
                            </h6>
                            <div id="tax-preview">
                                <p class="mb-0">Configure the tax settings above to see a preview.</p>
                            </div>
            </div>

            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <a href="{{ route('tenant.taxes.index') }}" class="btn btn-secondary">
                  <i class="fas fa-arrow-left"></i> Back to Taxes
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Create Tax
                </button>
              </div>
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
    const type = document.getElementById('type').value;
    const rateLabel = document.getElementById('rate-label');
    const ratePrefix = document.getElementById('rate-prefix');
    
    if (type === 'percentage') {
        rateLabel.textContent = 'Rate (%)';
        ratePrefix.textContent = '%';
    } else if (type === 'fixed') {
        rateLabel.textContent = 'Amount ($)';
        ratePrefix.textContent = '$';
    } else {
        rateLabel.textContent = 'Rate';
        ratePrefix.textContent = '%';
    }
    
    updatePreview();
}

function updatePreview() {
    const name = document.getElementById('name').value;
    const type = document.getElementById('type').value;
    const rate = parseFloat(document.getElementById('rate').value) || 0;
    const isInclusive = document.getElementById('is_inclusive').checked;
    const isActive = document.getElementById('is_active').checked;
    
    const preview = document.getElementById('tax-preview');
    
    if (name && type && rate > 0) {
        let rateDisplay = '';
        let example = '';
        
        if (type === 'percentage') {
            rateDisplay = rate.toFixed(2) + '%';
            if (isInclusive) {
                example = `For a $100.00 total amount, tax portion would be $${(100 - (100 / (1 + (rate / 100)))).toFixed(2)}`;
            } else {
                example = `For a $100.00 subtotal, tax would be $${(100 * (rate / 100)).toFixed(2)}, total: $${(100 + (100 * (rate / 100))).toFixed(2)}`;
            }
        } else if (type === 'fixed') {
            rateDisplay = '$' + rate.toFixed(2);
            if (isInclusive) {
                example = `Fixed tax of $${rate.toFixed(2)} is included in all prices`;
            } else {
                example = `Fixed tax of $${rate.toFixed(2)} will be added to all invoices`;
            }
        }
        
        preview.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${name} (${rateDisplay})</h6>
                    <p class="mb-2 text-muted">${example}</p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-soft-${type === 'percentage' ? 'primary' : 'warning'}">${type.charAt(0).toUpperCase() + type.slice(1)}</span>
                        <span class="badge bg-soft-${isInclusive ? 'success' : 'secondary'}">${isInclusive ? 'Tax Inclusive' : 'Tax Additional'}</span>
                        <span class="badge bg-soft-${isActive ? 'success' : 'danger'}">${isActive ? 'Active' : 'Inactive'}</span>
                    </div>
                </div>
            </div>
        `;
    } else {
        preview.innerHTML = '<p class="mb-0">Configure the tax settings above to see a preview.</p>';
    }
}

// Add event listeners
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('type').addEventListener('change', updatePreview);
document.getElementById('rate').addEventListener('input', updatePreview);
document.getElementById('is_inclusive').addEventListener('change', updatePreview);
document.getElementById('is_active').addEventListener('change', updatePreview);

// Initialize
updateRateLabel();
updatePreview();
</script>
@endpush