@extends('central.layouts.app')

@section('title', 'Tenant Reports')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Tenant Reports</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.reports.index') }}">Reports</a></li>
          <li class="breadcrumb-item active" aria-current="page">Tenants</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    
    <!-- Summary Statistics -->
    <div class="row mb-4">
      <div class="col-md-3 col-6">
        <div class="small-box text-bg-primary">
          <div class="inner">
            <h3>{{ number_format($summary['total_tenants']) }}</h3>
            <p>Total Tenants</p>
          </div>
          <i class="bi bi-building small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="small-box text-bg-success">
          <div class="inner">
            <h3>{{ number_format($summary['active_subscription_tenants']) }}</h3>
            <p>Active Subscriptions</p>
          </div>
          <i class="bi bi-check-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="small-box text-bg-warning">
          <div class="inner">
            <h3>{{ number_format($summary['trial_tenants']) }}</h3>
            <p>Trial Tenants</p>
          </div>
          <i class="bi bi-hourglass-split small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="small-box text-bg-danger">
          <div class="inner">
            <h3>{{ number_format($summary['inactive_tenants']) }}</h3>
            <p>Inactive</p>
          </div>
          <i class="bi bi-x-circle small-box-icon" style="font-size:2.5rem; opacity:0.5;"></i>
        </div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="card card-secondary card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-download me-2"></i>Export Options
            </h5>
          </div>
          <div class="card-body">
            <div class="d-flex gap-2">
              <a href="{{ route('central.reports.export', 'csv') }}?report_type=tenants&{{ request()->getQueryString() }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text me-2"></i>Export as CSV
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Filters and Table -->
    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-filter me-2"></i>Filter Tenants
            </h5>
          </div>
          <div class="card-body">
            <form action="{{ route('central.reports.tenants') }}" method="GET">
              <div class="row">
                <div class="col-md-3">
                  <div class="mb-3">
                    <label for="dateFrom" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="dateFrom" name="date_from" 
                    value="{{ request('date_from') }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label for="dateTo" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="dateTo" name="date_to" 
                    value="{{ request('date_to') }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                      <option value="">All Statuses</option>
                      <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Filter
                      </button>
                      <a href="{{ route('central.reports.tenants') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Tenants Table -->
    <div class="row">
      <div class="col-12">
        <div class="card card-success card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-table me-2"></i>Tenant Details
            </h5>
            <div class="card-tools">
              <span class="badge bg-info">{{ $tenants->total() }} tenants</span>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover table-sm">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Domain</th>
                    <th>Subscriptions</th>
                    <th>Total Invoices</th>
                    <th>Created</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($tenants as $tenant)
                  <tr>
                    <td>{{ $tenant->id }}</td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                        style="width: 24px; height: 24px;">
                        <i class="bi bi-building text-white" style="font-size: 12px;"></i>
                      </div>
                      <span>{{ $tenant->name }}</span>
                    </div>
                  </td>
                  <td>{{ $tenant->email }}</td>
                  <td>
                    @if($tenant->domains && $tenant->domains->count() > 0)
                    <span class="badge bg-info">{{ $tenant->domains->first()->domain }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @php
                    $activeCount = $tenant->subscriptions->where('status', 'active')->count();
                    $totalCount = $tenant->subscriptions->count();
                    @endphp
                    @if($activeCount > 0)
                    <span class="badge bg-success">{{ $activeCount }} Active</span>
                    @endif
                    @if($totalCount > $activeCount)
                    <span class="badge bg-secondary">{{ $totalCount - $activeCount }} Other</span>
                    @endif
                    @if($totalCount === 0)
                    <span class="text-muted">No subscriptions</span>
                    @endif
                  </td>
                  <td>
                    @php
                    $invoiceCount = $tenant->invoices->count();
                    $paidCount = $tenant->invoices->where('status', 'paid')->count();
                    @endphp
                    {{ $invoiceCount }} 
                    @if($paidCount > 0)
                    <small class="text-muted">({{ $paidCount }} paid)</small>
                    @endif
                  </td>
                  <td>
                    <small class="text-muted">{{ $tenant->created_at->format('M d, Y') }}</small>
                    <br>
                    <small class="text-muted">{{ $tenant->created_at->diffForHumans() }}</small>
                  </td>
                  <td>
                    <a href="{{ route('central.tenants.show', $tenant->id) }}" 
                      class="btn btn-sm btn-info" title="View Tenant">
                      <i class="bi bi-eye"></i>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No tenants found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer clearfix">
          {{ $tenants->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    // Auto-submit on status change
    $('#status').on('change', function() {
      $(this).closest('form').submit();
    });
  });
</script>
@endpush
