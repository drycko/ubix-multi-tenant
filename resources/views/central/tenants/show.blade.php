@extends('central.layouts.app')

@section('title', 'Show Tenant')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
	<!--begin::Container-->
	<div class="container-fluid">
		<!--begin::Row-->
		<div class="row">
			<div class="col-sm-6">
			{{-- <h3 class="mb-0">Tenant Details</h3> --}}
			</div>
			<div class="col-sm-6">
			<ol class="breadcrumb float-sm-end">
				<li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
				<li class="breadcrumb-item"><a href="{{ route('central.tenants.index') }}">All Tenants</a></li>
				<li class="breadcrumb-item active" aria-current="page">Tenant Details</li>
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

		@if($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0">
				@foreach($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
				</ul>
			</div>
		@endif

		<!--begin::Main details card-->
		<div class="card card-success card-outline mb-4">
			<div class="card-header">
				<h5 class="card-title">Tenant Details</h5>
				<div class="card-tools float-end">
					<a href="{{ route('central.tenants.edit', $tenant->id) }}" class="btn btn-sm btn-outline-success me-2">
						<i class="fas fa-edit me-2"></i>Edit Tenant
					</a>
					<form action="{{ route('central.tenants.destroy', $tenant->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.');">
						@csrf
						@method('DELETE')
						<button type="submit" class="btn btn-sm btn-outline-danger">
							<i class="fas fa-trash-alt me-2"></i>Delete Tenant
						</button>
					</form>
				</div>
			</div>
			<div class="card-body row">
				<div class="col-md-6 mb-4">
					<h5>Overview</h5>
					<hr>
					<p><strong>ID:</strong> {{ $tenant->id }}</br>
					<p><strong>Name:</strong> {{ $tenant->name }}</br>
					<p><strong>Email:</strong> {{ $tenant->email }}</br>
					<p><strong>Created At:</strong> {{ date('d M Y', strtotime($tenant->created_at)) }}</br>
					<p><strong>Updated At:</strong> {{ date('d M Y', strtotime($tenant->updated_at)) }}</br>
					<p><strong>Primary Domain:</strong> {{ $tenant->primary_domain }}</br>
					<p><strong>Database:</strong> {{ $tenant->tenancy_db_name }}</br>
				</div>
				<div class="col-md-6 mb-4">
					<h5>Current Subscription</h5>
					<hr>
					@if($tenant->current_plan)
					<p><strong>Current Plan:</strong> {{ $tenant->current_plan ? $tenant->current_plan->plan->name : 'N/A' }}</br>
					<p><strong>Plan Status:</strong> {{ $tenant->current_plan->status ?? 'N/A' }}</br>
					<p><strong>Plan Expiry:</strong> {{ $tenant->current_plan->end_date ? date('d M Y', strtotime($tenant->current_plan->end_date)) : 'N/A' }}</br>
					{{-- if this is a trial plan, show trial days left --}}
					@if($tenant->current_plan && $tenant->current_plan->status === 'trial')
						<p><strong>Trial Ends At:</strong> {{ $tenant->current_plan->trial_ends_at ? date('d M Y', strtotime($tenant->current_plan->trial_ends_at)) : 'N/A' }}</br>
						<p><strong>Trial Days Left:</strong> {{ $tenant->current_plan->trial_days_left ?? 'N/A' }} days</br></br>
						{{-- buttons to modals to cancel trial and switch to paid plan --}}
						<a href="#" class="btn btn-sm btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#switchToPremiumModal">
							<i class="fas fa-exchange-alt me-2"></i>Switch to Paid Plan
						</a>
						<a href="{{ route('central.subscriptions.show', $tenant->current_plan->id) }}" class="btn btn-sm btn-outline-primary me-2">
							<i class="fas fa-eye me-2"></i>View Subscription
						</a>
					@endif
					@else
					<p>No active subscription plan.</p>
					{{-- button to open modal to switch to premium plan --}}
					<a href="#" class="btn btn-sm btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#switchToPremiumModal">
						<i class="fas fa-exchange-alt me-2"></i>Switch to Paid Plan
					</a>
					@endif
				</div>
			</div>
			<div class="card-footer">
				<div class="row">
					<div class="col-md-6">
						<a href="{{ route('central.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
							<i class="fas fa-arrow-left me-2"></i>Back to All Tenants
						</a>
					</div>
					<div class="col-md-6">
						<div class="float-end">
							{{-- buttons to manage domains and subscriptions --}}
							<a href="{{ route('central.tenants.domains', $tenant->id) }}" class="btn btn-sm btn-primary">
								<i class="fas fa-globe me-2"></i> Domains
							</a>
							<a href="{{ route('central.tenants.subscriptions', $tenant->id) }}" class="btn btn-sm btn-success">
								<i class="fas fa-credit-card me-2"></i> Subscriptions
							</a>
						</div>
					</div>
				</div>

			</div>
		</div>
		<!--end::Main details card-->
	</div>
	<!--end::Container-->
</div>
<!--end::App Content-->

<!--start::Switch to Premium Plan Modal (we have to select the subscription plan)-->
<div class="modal fade" id="switchToPremiumModal" tabindex="-1" aria-labelledby="switchToPremiumModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form action="{{ route('central.tenants.switch-to-premium', $tenant->id) }}" method="POST">
			@csrf
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="switchToPremiumModalLabel">Switch to Premium Plan</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p>Please select a subscription plan to switch to:</p>
					{{-- dropdown to select subscription plan --}}
					<select class="form-select" aria-label="Select Subscription Plan" name="plan_name" required>
						<option value="" disabled selected>Select a plan</option>
							@foreach($availablePlans as $plan)
							{{-- make sure we have both yearly and monthly price in the plans as separate options --}}
							<option value="{{ $plan->name }}" {{ old('plan_name') == $plan->name ? 'selected' : '' }}>{{ $plan->name }} - {{ $currency }} {{ $plan->monthly_price }} / month</option>
							<option value="{{ $plan->name }}_yearly" {{ old('plan_name') == $plan->name.'_yearly' ? 'selected' : '' }}>{{ $plan->name }} - {{ $currency }} {{ $plan->yearly_price }} / year</option>
							@endforeach
					</select>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-sm btn-outline-success">Switch to Plan</button>
				</div>
			</div>
		</form>
	</div>
</div>
<!--end::Switch to Premium Plan Modal-->

<!--start::Cancel Current Subscription Modal-->
{{-- <div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="cancelSubscriptionModalLabel">Cancel Current Subscription</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to cancel the current subscription for this tenant? This action cannot be undone.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<a href="#" class="btn btn-danger">Yes, Cancel Subscription</a>
			</div>
		</div>
	</div>
</div> --}}
@endsection