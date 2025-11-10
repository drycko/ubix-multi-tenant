@extends('central.layouts.app')

@section('title', 'Tenants')

@section('content')

<!--begin::App Content-->
<div class="app-content mt-3">
  <!--begin::Container-->
  <div class="container-fluid">
    
    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Header Card with Actions -->
    <div class="ghost-card mb-4">
      <div class="ghost-card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
              <i class="fas fa-building fa-2x"></i>
            </div>
            <div>
              <h5 class="card-title mb-0 text-muted">All Tenants</h5><br>
              <small class="text-muted">Manage your tenant organizations</small>
            </div>
          </div>
          <div class="btn-group" role="group">
            <a href="{{ route('central.tenants.create') }}" class="btn btn-success">
              <i class="fas fa-plus me-1"></i>New Tenant
            </a>
          </div>
        </div>
      </div>
     
      <div class="ghost-card-body bg-transparent">
        <!-- Filters Section -->
        <div class="card card-outline mb-3">
          <div class="card-body">
            <form method="GET" action="{{ route('central.tenants.index') }}" id="filterForm">
              <div class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                  <label for="search" class="form-label"><i class="fas fa-search me-1"></i>Search</label>
                  <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name, email, contact...">
                </div>
                
                <!-- Status Filter -->
                <div class="col-md-2">
                  <label for="status" class="form-label"><i class="fas fa-toggle-on me-1"></i>Status</label>
                  <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                  </select>
                </div>
                
                <!-- Plan Filter -->
                <div class="col-md-2">
                  <label for="plan" class="form-label"><i class="fas fa-box me-1"></i>Plan</label>
                  <select class="form-select" id="plan" name="plan">
                    <option value="">All Plans</option>
                    @foreach($plans as $plan)
                      <option value="{{ $plan }}" {{ request('plan') === $plan ? 'selected' : '' }}>{{ $plan }}</option>
                    @endforeach
                  </select>
                </div>
                
                <!-- Date From -->
                <div class="col-md-2">
                  <label for="date_from" class="form-label"><i class="fas fa-calendar me-1"></i>From Date</label>
                  <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                
                <!-- Date To -->
                <div class="col-md-2">
                  <label for="date_to" class="form-label"><i class="fas fa-calendar me-1"></i>To Date</label>
                  <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                
                <!-- Filter Buttons -->
                <div class="col-md-1 d-flex align-items-end">
                  <div class="btn-group w-100" role="group">
                    <button type="submit" class="btn btn-primary" title="Apply Filters">
                      <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('central.tenants.index') }}" class="btn btn-secondary" title="Clear Filters">
                      <i class="fas fa-times"></i>
                    </a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card card-outline">
          {{-- <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-list me-2"></i>Tenant Directory
            </h5>
          </div> --}}
          <div >
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle">
                <thead class="table-light">
                  <tr>
                    <th><i class="fas fa-building me-1"></i>Tenant Name</th>
                    <th><i class="fas fa-globe me-1"></i>Primary Domain</th>
                    <th><i class="fas fa-database me-1"></i>Database</th>
                    <th><i class="fas fa-user me-1"></i>Contact</th>
                    <th><i class="fas fa-box me-1"></i>Plan</th>
                    <th><i class="fas fa-toggle-on me-1"></i>Status</th>
                    <th><i class="fas fa-calendar me-1"></i>Created</th>
                    <th class="text-center"><i class="fas fa-cog me-1"></i>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($tenants as $tenant)
                  <tr>
                    <td>
                      <strong>{{ $tenant->name }}</strong>
                    </td>
                    <td>
                      <a href="https://{{ $tenant->domains->where('is_primary', true)->pluck('domain')->join(', ') }}" target="_blank" class="text-decoration-none">
                        {{ $tenant->domains->where('is_primary', true)->pluck('domain')->join(', ') }}
                        <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                      </a>
                    </td>
                    <td>
                      <code class="text-muted">{{ $tenant->database }}</code>
                    </td>
                    <td>
                      <div>{{ $tenant->contact_person }}</div>
                      <small class="text-muted">
                        <i class="fas fa-envelope fa-xs me-1"></i>{{ $tenant->email }}
                      </small>
                    </td>
                    <td>
                      @if($tenant->plan)
                        <span class="badge bg-primary">{{ $tenant->plan }}</span>
                      @else
                        <span class="badge bg-secondary">No Plan</span>
                      @endif
                    </td>
                    <td>
                      @if($tenant->is_active)
                        <span class="badge bg-success">
                          <i class="fas fa-check-circle me-1"></i>Active
                        </span>
                      @else
                        <span class="badge bg-secondary">
                          <i class="fas fa-pause-circle me-1"></i>Inactive
                        </span>
                      @endif
                    </td>
                    <td>
                      <div>{{ $tenant->created_at->format('M d, Y') }}</div>
                      <small class="text-muted">{{ $tenant->created_at->diffForHumans() }}</small>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        @can('view tenants')
                        <a href="{{ route('central.tenants.show', $tenant->id) }}?return_page={{ request('page', 1) }}" class="btn btn-outline-primary" title="View Details">
                          <i class="fas fa-eye"></i>
                        </a>
                        @endcan
                        @can('manage tenants')
                        <a href="{{ route('central.tenants.edit', $tenant->id) }}?return_page={{ request('page', 1) }}" class="btn btn-outline-warning" title="Edit Tenant">
                          <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" title="Delete Tenant" 
                          onclick="if(confirm('Are you sure you want to delete this tenant? This action cannot be undone.')) { document.getElementById('delete-form-{{ $tenant->id }}').submit(); }">
                          <i class="fas fa-trash"></i>
                        </button>
                        <form id="delete-form-{{ $tenant->id }}" action="{{ route('central.tenants.destroy', $tenant->id) }}" method="POST" class="d-none">
                          @csrf
                          @method('DELETE')
                          <input type="hidden" name="return_page" value="{{ request('page', 1) }}">
                        </form>
                        @endcan
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center py-4">
                      <div class="text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p class="mb-0">No tenants found.</p>
                        <small>Create your first tenant to get started.</small>
                      </div>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            </div>
          </div>
          {{-- Pagination links --}}
          @if($tenants->hasPages())
          <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
              <div class="col-md-6">
                <p class="mb-0 text-muted">
                  <i class="fas fa-info-circle me-1"></i>
                  Showing <strong>{{ $tenants->firstItem() }}</strong> to <strong>{{ $tenants->lastItem() }}</strong> of <strong>{{ $tenants->total() }}</strong> entries
                </p>
              </div>
              <div class="col-md-6 d-flex justify-content-end">
                {{ $tenants->links('vendor.pagination.bootstrap-5') }}
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

{{-- Store current page in session for pagination preservation --}}
<script>
  // Store the current page in session storage
  @if(request('page'))
    sessionStorage.setItem('tenants_page', '{{ request('page') }}');
  @endif

  // Optional: Auto-submit form when filters change (except search which needs manual submit)
  document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('status');
    const planFilter = document.getElementById('plan');
    const dateFromFilter = document.getElementById('date_from');
    const dateToFilter = document.getElementById('date_to');
    const filterForm = document.getElementById('filterForm');

    // Auto-submit on filter change (optional - remove if you prefer manual filtering)
    [statusFilter, planFilter, dateFromFilter, dateToFilter].forEach(filter => {
      if (filter) {
        filter.addEventListener('change', function() {
          // Optional: Uncomment next line to enable auto-submit on change
          // filterForm.submit();
        });
      }
    });

    // Show active filters count
    const activeFilters = document.querySelectorAll('#filterForm input[value]:not([value=""]), #filterForm select option:checked:not([value=""])').length;
    if (activeFilters > 0) {
      const filterBtn = document.querySelector('button[type="submit"]');
      if (filterBtn) {
        filterBtn.innerHTML = '<i class="fas fa-filter"></i> (' + activeFilters + ')';
      }
    }
  });
</script>
@endsection