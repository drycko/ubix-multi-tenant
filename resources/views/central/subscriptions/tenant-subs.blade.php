@extends('central.layouts.app')

@section('title', 'Tenant Subscriptions')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
	<!--begin::Container-->
	<div class="container-fluid">
		<!--begin::Row-->
		<div class="row">
			<div class="col-sm-6">
			<h3 class="mb-0">Tenant Subscriptions</h3>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-end">
					<li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
					<li class="breadcrumb-item"><a href="{{ route('central.tenants.index') }}">All Tenants</a></li>
					<li class="breadcrumb-item active" aria-current="page">Tenant Subscriptions</li>
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
				<h5 class="card-title">Subscriptions for Tenant: {{ $tenant->name }}</h5>
				<div class="card-tools float-end">
					<a href="{{ route('central.tenants.show', $tenant->id) }}" class="btn btn-sm btn-outline-success me-2">
						<i class="fas fa-arrow-left me-2"></i>Back to Tenant Details
					</a>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Plan Name</th>
								<th>Price</th>
								<th>Start Date</th>
								<th>End Date</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($subscriptions as $subscription)
							<tr>
								<td>{{ $subscription->plan->name }}</td>
								<td>{{ number_format($subscription->price, 2) }} {{ $currency }}</td>
								<td>{{ $subscription->start_date->format('d M Y') }}</td>
								<td>{{ $subscription->end_date ? $subscription->end_date->format('d M Y') : 'N/A' }}</td>
								<td><span class=" badge {{$subscription->status == 'active' ? 'text-bg-success' : ($subscription->status == 'canceled' ? 'text-bg-danger' : 'text-bg-warning')}}">{{ ucfirst($subscription->status) }}</span></td>
								<td>
									<a href="{{ route('central.subscriptions.show', [$subscription->id]) }}" class="btn btn-sm btn-outline-success">
									  <i class="fas fa-eye"></i> View
									</a>
									{{-- cancel subscription --}}
                  @if($subscription->status === 'active' || $subscription->status === 'trial')
                  <form action="{{ route('central.subscriptions.cancel', [$subscription->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this subscription?');">
                      <i class="fas fa-times"></i> Cancel
                    </button>
                  </form>
                  @else
                  {{-- make it delete if not active or trial --}}
                  <form action="{{ route('central.subscriptions.destroy', [$subscription->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this subscription?');">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </form>
                  @endif
                  {{-- Add more actions as needed --}}
								</td>
							</tr>
              @empty
              <tr>
                <td colspan="6" class="text-center">No subscriptions found for this tenant.</td>
              </tr>
							@endforelse
						</tbody>
					</table>
				</div>
				{{-- Pagination links --}}
        @if($subscriptions->hasPages())
        <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0 text-muted">
                        Showing {{ $subscriptions->firstItem() }} to {{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }} entries
                    </p>
                </div>
                <div class="col-md-4 float-end">
                    {{ $subscriptions->links('vendor.pagination.bootstrap-5') }} {{-- I want to align the links to the end of the column --}}
                </div>
            </div>
        </div>
        @endif
			</div>
		</div>
	</div>
</div>
<!--end::App Content-->
@endsection