@extends('central.layouts.app')

@section('title', 'Tax Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="fas fa-percentage"></i>
          Tax Management
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tax Management</li>
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

    <!-- Control Panel -->
    <div class="row mb-3">
      <div class="col-md-6">
        <form method="GET" action="{{ route('central.taxes.index') }}" class="d-flex">
          <input type="text" 
                 name="search" 
                 class="form-control me-2" 
                 placeholder="Search taxes..." 
                 value="{{ request('search') }}">
          <button type="submit" class="btn btn-outline-primary">
            <i class="fas fa-search"></i>
          </button>
          @if(request('search') || request('status'))
          <a href="{{ route('central.taxes.index') }}" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-times"></i>
          </a>
          @endif
        </form>
      </div>
      <div class="col-md-6 text-end">
        <a href="{{ route('central.taxes.create') }}" class="btn btn-success">
          <i class="fas fa-plus"></i> Create Tax
        </a>
      </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ route('central.taxes.index') }}" class="row g-3">
              <input type="hidden" name="search" value="{{ request('search') }}">

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="">All Status</option>
                  <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>

              <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('central.taxes.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-undo"></i> Reset
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Taxes List -->
    <div class="card card-success card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list"></i> Tax Configuration for Subscription Invoices
        </h3>
      </div>
      <div class="card-body">
        @if($taxes->count() > 0)
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Tax Name</th>
                <th>Rate</th>
                <th>Type</th>
                <th>Application</th>
                <th>Status</th>
                <th>Display Order</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($taxes as $tax)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="me-3">
                      <i class="fas fa-percentage text-success"></i>
                    </div>
                    <div>
                      <h6 class="mb-0">{{ $tax->name }}</h6>
                      @if($tax->description)
                        <small class="text-muted">{{ Str::limit($tax->description, 50) }}</small>
                      @endif
                    </div>
                  </div>
                </td>
                <td>
                  <strong class="text-success">{{ $tax->formatted_rate }}</strong>
                </td>
                <td>
                  <span class="badge bg-{{ $tax->type === 'percentage' ? 'primary' : 'warning' }}">
                    {{ ucfirst($tax->type) }}
                  </span>
                </td>
                <td>
                  @if($tax->is_inclusive)
                    <span class="badge bg-success">
                      <i class="fas fa-check me-1"></i>Inclusive
                    </span>
                  @else
                    <span class="badge bg-secondary">
                      <i class="fas fa-plus me-1"></i>Additional
                    </span>
                  @endif
                </td>
                <td>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           {{ $tax->is_active ? 'checked' : '' }}
                           onchange="toggleTaxStatus({{ $tax->id }}, this.checked)">
                  </div>
                </td>
                <td>
                  <span class="badge bg-light text-dark">{{ $tax->display_order }}</span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('central.taxes.show', $tax) }}" 
                       class="btn btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('central.taxes.edit', $tax) }}" 
                       class="btn btn-outline-warning" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger" 
                            title="Delete" onclick="deleteTax({{ $tax->id }})">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted">
            Showing {{ $taxes->firstItem() }} to {{ $taxes->lastItem() }} of {{ $taxes->total() }} results
          </div>
          {{ $taxes->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-percentage fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No taxes configured yet</h5>
          <p class="text-muted">Start by creating your first tax configuration for subscription invoices.</p>
          <a href="{{ route('central.taxes.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Create First Tax
          </a>
        </div>
        @endif
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
        <p>Are you sure you want to delete this tax? This action cannot be undone.</p>
        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This may affect existing invoices that reference this tax.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Delete Tax</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let deleteUrl = '';

function deleteTax(taxId) {
    deleteUrl = `/central/taxes/${taxId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        
        if (data.message) {
            // Show success toast/alert
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.app-content .container-fluid').insertBefore(alert, document.querySelector('.app-content .container-fluid').firstElementChild);
            
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            An error occurred while deleting the tax.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.app-content .container-fluid').insertBefore(alert, document.querySelector('.app-content .container-fluid').firstElementChild);
    });
});

function toggleTaxStatus(taxId, isActive) {
    fetch(`/central/taxes/${taxId}/toggle-status`, {
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
            // Show success toast
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.app-content .container-fluid').insertBefore(alert, document.querySelector('.app-content .container-fluid').firstElementChild);
            
            // Auto dismiss after 3 seconds
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert the switch if there was an error
        event.target.checked = !isActive;
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            An error occurred while updating the tax status.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.app-content .container-fluid').insertBefore(alert, document.querySelector('.app-content .container-fluid').firstElementChild);
    });
}
</script>
@endpush
@endsection
