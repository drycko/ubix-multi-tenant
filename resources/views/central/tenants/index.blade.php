@extends('central.layouts.app')

@section('title', 'Tenants')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
            <h3 class="mb-0">All Tenants</h3>
            </div>
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Tenants</li>
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

        <div class="card card-success card-outline">
            <div class="card-header">
                <h5 class="card-title">Tenants</h5>
                {{-- Need to add a button to create a new tenant to the left --}}
                <div class="card-tools float-end">
                    <a href="{{ route('central.tenants.create') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-plus me-2"></i>New Tenant
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
								<th>Tenant Name</th>
								<th>Primary Domain</th>
								<th>Database</th>
								<th>Plan</th>
								<th>Status</th>
								<th>Created At</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($tenants as $tenant)
							<tr>
								<td>{{ $tenant->name }}</td>
								<td><a href="http://{{ $tenant->domains->where('is_primary', true)->pluck('domain')->join(', ') }}">{{ $tenant->domains->where('is_primary', true)->pluck('domain')->join(', ') }}</a></td>
                                <td>{{ $tenant->database }}</td>
								<td>{{ $tenant->plan }}</td>
								<td>
									@if($tenant->is_active)
										<span class="badge bg-success">Active</span>
									@else
										<span class="badge bg-secondary">Inactive</span>
									@endif
								</td>
								<td>{{ $tenant->created_at->format('Y-m-d') }}</td>
								<td>
									<a href="{{ route('central.tenants.show', $tenant->id) }}" class="btn btn-sm btn-outline-success">
										<i class="fas fa-eye"></i> View
									</a>
									<a href="{{ route('central.tenants.edit', $tenant->id) }}" class="btn btn-sm btn-outline-warning">
										<i class="fas fa-edit"></i> Edit
									</a>
								</td>
							</tr>
							@empty
							<tr>
								<td colspan="6" class="text-center">No tenants found.</td>
							</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				{{-- Pagination links --}}
                @if($tenants->hasPages())
                <div class="card-footer bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-0 text-muted">
                                Showing {{ $tenants->firstItem() }} to {{ $tenants->lastItem() }} of {{ $tenants->total() }} entries
                            </p>
                        </div>
                        <div class="col-md-4 float-end">
                            {{ $tenants->links('vendor.pagination.bootstrap-5') }} {{-- I want to align the links to the end of the column --}}
                        </div>
                    </div>
                </div>
                @endif
			</div>
		</div>
	</div>
	<!--end::Container-->
</div>
<!--end::App Content-->
@endsection