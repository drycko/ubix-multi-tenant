@extends('central.layouts.app')

@section('title', 'View Tax')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-percentage"></i>
          {{ $tax->name }}
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.taxes.index') }}">Taxes</a></li>
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
    
    {{-- Success Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="row mb-3">
      <div class="col-md-12">
        <a href="{{ route('central.taxes.edit', $tax) }}" class="btn btn-warning">
          <i class="fas fa-edit"></i> Edit Tax
        </a>
        <a href="{{ route('central.taxes.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <button type="button" class="btn btn-danger" onclick="deleteTax()">
          <i class="fas fa-trash"></i> Delete Tax
        </button>
      </div>
    </div>

    <div class="row">
      <!-- Tax Details Card -->
      <div class="col-md-8">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-info-circle"></i> Tax Details
            </h3>
          </div>
          <div class="card-body">
            <dl class="row">
              <dt class="col-sm-3">Tax Name:</dt>
              <dd class="col-sm-9">
                <h5>{{ $tax->name }}</h5>
              </dd>

              <dt class="col-sm-3">Tax Rate:</dt>
              <dd class="col-sm-9">
                <h4 class="text-success">{{ $tax->formatted_rate }}</h4>
              </dd>

              <dt class="col-sm-3">Tax Type:</dt>
              <dd class="col-sm-9">
                <span class="badge bg-{{ $tax->type === 'percentage' ? 'primary' : 'warning' }} badge-lg">
                  {{ ucfirst($tax->type) }}
                </span>
              </dd>

              <dt class="col-sm-3">Application:</dt>
              <dd class="col-sm-9">
                @if($tax->is_inclusive)
                  <span class="badge bg-success badge-lg">
                    <i class="fas fa-check me-1"></i>Inclusive Tax
                  </span>
                  <p class="mb-0 mt-2 text-muted">
                    <small>Tax is already included in the price and is shown for transparency.</small>
                  </p>
                @else
                  <span class="badge bg-secondary badge-lg">
                    <i class="fas fa-plus me-1"></i>Additional Tax
                  </span>
                  <p class="mb-0 mt-2 text-muted">
                    <small>Tax is added on top of the subtotal (Total = Subtotal + Tax).</small>
                  </p>
                @endif
              </dd>

              <dt class="col-sm-3">Status:</dt>
              <dd class="col-sm-9">
                @if($tax->is_active)
                  <span class="badge bg-success badge-lg">
                    <i class="fas fa-check-circle"></i> Active
                  </span>
                @else
                  <span class="badge bg-danger badge-lg">
                    <i class="fas fa-times-circle"></i> Inactive
                  </span>
                @endif
              </dd>

              <dt class="col-sm-3">Display Order:</dt>
              <dd class="col-sm-9">
                <span class="badge bg-light text-dark">{{ $tax->display_order }}</span>
              </dd>

              @if($tax->description)
              <dt class="col-sm-3">Description:</dt>
              <dd class="col-sm-9">
                <p class="mb-0">{{ $tax->description }}</p>
              </dd>
              @endif

              <dt class="col-sm-3">Created:</dt>
              <dd class="col-sm-9">
                {{ $tax->created_at->format('M d, Y h:i A') }}
                <small class="text-muted">({{ $tax->created_at->diffForHumans() }})</small>
              </dd>

              <dt class="col-sm-3">Last Updated:</dt>
              <dd class="col-sm-9">
                {{ $tax->updated_at->format('M d, Y h:i A') }}
                <small class="text-muted">({{ $tax->updated_at->diffForHumans() }})</small>
              </dd>
            </dl>
          </div>
        </div>

        <!-- Tax Calculation Examples -->
        <div class="card card-info card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-calculator"></i> Calculation Examples
            </h3>
          </div>
          <div class="card-body">
            @if($tax->type === 'percentage')
              @if($tax->is_inclusive)
                <!-- Inclusive Percentage Tax Examples -->
                <h5>Inclusive Tax Calculation</h5>
                <p>For inclusive taxes, the tax is already included in the total price:</p>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead>
                      <tr>
                        <th>Total Amount</th>
                        <th>Tax Component ({{ $tax->formatted_rate }})</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>R 1,000.00</td>
                        <td class="text-success">R {{ number_format($tax->calculateInclusiveTaxAmount(1000), 2) }}</td>
                        <td>R {{ number_format(1000 - $tax->calculateInclusiveTaxAmount(1000), 2) }}</td>
                      </tr>
                      <tr>
                        <td>R 5,000.00</td>
                        <td class="text-success">R {{ number_format($tax->calculateInclusiveTaxAmount(5000), 2) }}</td>
                        <td>R {{ number_format(5000 - $tax->calculateInclusiveTaxAmount(5000), 2) }}</td>
                      </tr>
                      <tr>
                        <td>R 10,000.00</td>
                        <td class="text-success">R {{ number_format($tax->calculateInclusiveTaxAmount(10000), 2) }}</td>
                        <td>R {{ number_format(10000 - $tax->calculateInclusiveTaxAmount(10000), 2) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              @else
                <!-- Additional Percentage Tax Examples -->
                <h5>Additional Tax Calculation</h5>
                <p>For additional taxes, the tax is added on top of the subtotal:</p>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead>
                      <tr>
                        <th>Subtotal</th>
                        <th>Tax ({{ $tax->formatted_rate }})</th>
                        <th>Total Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>R 1,000.00</td>
                        <td class="text-success">R {{ number_format($tax->calculateTaxAmount(1000), 2) }}</td>
                        <td>R {{ number_format(1000 + $tax->calculateTaxAmount(1000), 2) }}</td>
                      </tr>
                      <tr>
                        <td>R 5,000.00</td>
                        <td class="text-success">R {{ number_format($tax->calculateTaxAmount(5000), 2) }}</td>
                        <td>R {{ number_format(5000 + $tax->calculateTaxAmount(5000), 2) }}</td>
                      </tr>
                      <tr>
                        <td>R 10,000.00</td>
                        <td class="text-success">R {{ number_format($tax->calculateTaxAmount(10000), 2) }}</td>
                        <td>R {{ number_format(10000 + $tax->calculateTaxAmount(10000), 2) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              @endif
            @else
              <!-- Fixed Tax Examples -->
              <h5>Fixed Tax Amount</h5>
              <p>This tax applies a fixed amount of <strong class="text-success">R {{ number_format($tax->rate, 2) }}</strong> regardless of the invoice total.</p>
              @if(!$tax->is_inclusive)
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>Subtotal</th>
                      <th>Tax</th>
                      <th>Total Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>R 1,000.00</td>
                      <td class="text-success">R {{ number_format($tax->rate, 2) }}</td>
                      <td>R {{ number_format(1000 + $tax->rate, 2) }}</td>
                    </tr>
                    <tr>
                      <td>R 5,000.00</td>
                      <td class="text-success">R {{ number_format($tax->rate, 2) }}</td>
                      <td>R {{ number_format(5000 + $tax->rate, 2) }}</td>
                    </tr>
                    <tr>
                      <td>R 10,000.00</td>
                      <td class="text-success">R {{ number_format($tax->rate, 2) }}</td>
                      <td>R {{ number_format(10000 + $tax->rate, 2) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              @endif
            @endif
          </div>
        </div>
      </div>

      <!-- Statistics and Usage Card -->
      <div class="col-md-4">
        <!-- Usage Statistics -->
        <div class="card card-success card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-bar"></i> Usage Statistics
            </h3>
          </div>
          <div class="card-body">
            <div class="info-box bg-light">
              <span class="info-box-icon bg-success"><i class="fas fa-file-invoice"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Invoices Using This Tax</span>
                <span class="info-box-number">{{ $tax->subscriptionInvoices()->count() }}</span>
              </div>
            </div>

            @if($tax->subscriptionInvoices()->count() > 0)
            <div class="info-box bg-light">
              <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Tax Collected</span>
                <span class="info-box-number">
                  R {{ number_format($tax->subscriptionInvoices()->sum('tax_amount'), 2) }}
                </span>
              </div>
            </div>
            @endif
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card card-secondary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-bolt"></i> Quick Actions
            </h3>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('central.taxes.edit', $tax) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit This Tax
              </a>
              
              <button type="button" class="btn btn-{{ $tax->is_active ? 'danger' : 'success' }}" 
                      onclick="toggleStatus()">
                <i class="fas fa-{{ $tax->is_active ? 'ban' : 'check' }}"></i>
                {{ $tax->is_active ? 'Deactivate' : 'Activate' }} Tax
              </button>

              @if($tax->subscriptionInvoices()->count() === 0)
              <button type="button" class="btn btn-danger" onclick="deleteTax()">
                <i class="fas fa-trash"></i> Delete Tax
              </button>
              @else
              <button type="button" class="btn btn-outline-danger" disabled title="Cannot delete tax that's in use">
                <i class="fas fa-trash"></i> Delete Tax (In Use)
              </button>
              @endif

              <a href="{{ route('central.taxes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the tax <strong>{{ $tax->name }}</strong>?</p>
        <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('central.taxes.destroy', $tax) }}" method="POST" id="deleteForm">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Delete Tax</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function deleteTax() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleStatus() {
    if(confirm('Are you sure you want to {{ $tax->is_active ? "deactivate" : "activate" }} this tax?')) {
        fetch('{{ route("central.taxes.toggle-status", $tax) }}', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Show success message and reload
                alert(data.message);
                window.location.reload();
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
