@extends('tenant.layouts.app')

@section('title', 'Tax Details')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-percentage"></i>
          Tax Details
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.taxes.index') }}">Tax Management</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $tax->name }}</li>
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percentage me-2"></i>{{ $tax->name }}
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-soft-{{ $tax->is_active ? 'success' : 'danger' }} fs-6">
                            {{ $tax->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Property</h6>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-xs">
                                            <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                <i class="fas fa-building"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0">{{ $tax->property->name }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Tax Rate</h6>
                                <h4 class="mb-0 text-primary">{{ $tax->formatted_rate }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Tax Type</h6>
                                <span class="badge bg-soft-{{ $tax->type === 'percentage' ? 'primary' : 'warning' }} fs-6">
                                    {{ ucfirst($tax->type) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Tax Application</h6>
                                <span class="badge bg-soft-{{ $tax->is_inclusive ? 'success' : 'secondary' }} fs-6">
                                    <i class="fas fa-{{ $tax->is_inclusive ? 'check' : 'plus' }} me-1"></i>
                                    {{ $tax->is_inclusive ? 'Tax Inclusive' : 'Tax Additional' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Display Order</h6>
                                <span class="badge bg-soft-secondary fs-6">{{ $tax->display_order }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Created</h6>
                                <p class="mb-0">{{ $tax->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($tax->description)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Description</h6>
                                    <p class="mb-0">{{ $tax->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tax Calculation Examples -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-calculator me-1"></i>Tax Calculation Examples
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-2">For $100.00 invoice:</h6>
                                        @if($tax->type === 'percentage')
                                            @if($tax->is_inclusive)
                                                @php
                                                    $taxAmount = 100 - (100 / (1 + ($tax->rate / 100)));
                                                    $subtotal = 100 - $taxAmount;
                                                @endphp
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Total Amount: $100.00</li>
                                                    <li>• Tax Portion: ${{ number_format($taxAmount, 2) }}</li>
                                                    <li>• Subtotal: ${{ number_format($subtotal, 2) }}</li>
                                                </ul>
                                            @else
                                                @php
                                                    $taxAmount = 100 * ($tax->rate / 100);
                                                    $total = 100 + $taxAmount;
                                                @endphp
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Subtotal: $100.00</li>
                                                    <li>• Tax Amount: ${{ number_format($taxAmount, 2) }}</li>
                                                    <li>• Total: ${{ number_format($total, 2) }}</li>
                                                </ul>
                                            @endif
                                        @else
                                            @if($tax->is_inclusive)
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Total Amount: $100.00</li>
                                                    <li>• Fixed Tax: ${{ number_format($tax->rate, 2) }}</li>
                                                    <li>• Subtotal: ${{ number_format(100 - $tax->rate, 2) }}</li>
                                                </ul>
                                            @else
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Subtotal: $100.00</li>
                                                    <li>• Fixed Tax: ${{ number_format($tax->rate, 2) }}</li>
                                                    <li>• Total: ${{ number_format(100 + $tax->rate, 2) }}</li>
                                                </ul>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-2">For $250.00 invoice:</h6>
                                        @if($tax->type === 'percentage')
                                            @if($tax->is_inclusive)
                                                @php
                                                    $taxAmount = 250 - (250 / (1 + ($tax->rate / 100)));
                                                    $subtotal = 250 - $taxAmount;
                                                @endphp
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Total Amount: $250.00</li>
                                                    <li>• Tax Portion: ${{ number_format($taxAmount, 2) }}</li>
                                                    <li>• Subtotal: ${{ number_format($subtotal, 2) }}</li>
                                                </ul>
                                            @else
                                                @php
                                                    $taxAmount = 250 * ($tax->rate / 100);
                                                    $total = 250 + $taxAmount;
                                                @endphp
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Subtotal: $250.00</li>
                                                    <li>• Tax Amount: ${{ number_format($taxAmount, 2) }}</li>
                                                    <li>• Total: ${{ number_format($total, 2) }}</li>
                                                </ul>
                                            @endif
                                        @else
                                            @if($tax->is_inclusive)
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Total Amount: $250.00</li>
                                                    <li>• Fixed Tax: ${{ number_format($tax->rate, 2) }}</li>
                                                    <li>• Subtotal: ${{ number_format(250 - $tax->rate, 2) }}</li>
                                                </ul>
                                            @else
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Subtotal: $250.00</li>
                                                    <li>• Fixed Tax: ${{ number_format($tax->rate, 2) }}</li>
                                                    <li>• Total: ${{ number_format(250 + $tax->rate, 2) }}</li>
                                                </ul>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('tenant.taxes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Taxes
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-{{ $tax->is_active ? 'warning' : 'success' }}" 
                                    onclick="toggleTaxStatus({{ $tax->id }}, {{ $tax->is_active ? 'false' : 'true' }})">
                                <i class="fas fa-power-off me-1"></i>
                                {{ $tax->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <a href="{{ route('tenant.taxes.edit', $tax) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Edit Tax
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-1"></i>Tax Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                <i class="fas fa-percentage" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <h5 class="mb-1">{{ $tax->display_name }}</h5>
                        <p class="text-muted mb-0">{{ $tax->property->name }}</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Tax ID:</td>
                                    <td class="text-end fw-semibold">#{{ $tax->id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Type:</td>
                                    <td class="text-end">
                                        <span class="badge bg-soft-{{ $tax->type === 'percentage' ? 'primary' : 'warning' }}">
                                            {{ ucfirst($tax->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Application:</td>
                                    <td class="text-end">
                                        <span class="badge bg-soft-{{ $tax->is_inclusive ? 'success' : 'secondary' }}">
                                            {{ $tax->is_inclusive ? 'Inclusive' : 'Additional' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status:</td>
                                    <td class="text-end">
                                        <span class="badge bg-soft-{{ $tax->is_active ? 'success' : 'danger' }}">
                                            {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Order:</td>
                                    <td class="text-end fw-semibold">{{ $tax->display_order }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created:</td>
                                    <td class="text-end">{{ $tax->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Updated:</td>
                                    <td class="text-end">{{ $tax->updated_at->format('M d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleTaxStatus(taxId, newStatus) {
    if (confirm(`Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this tax?`)) {
        fetch(`/taxes/${taxId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.row'));
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the tax status.');
        });
    }
}
</script>
@endpush