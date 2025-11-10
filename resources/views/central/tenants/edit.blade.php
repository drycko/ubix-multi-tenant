@extends('central.layouts.app')

@section('title', 'Edit Tenant')

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

		{{-- Validation Errors --}}
		@if($errors->any())
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
			<ul class="mb-0">
				@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
				@endforeach
			</ul>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		@endif

		<!-- Header Card with Actions -->
		<div class="ghost-card mb-4">
			<div class="ghost-card-header">
				<div class="d-flex justify-content-between align-items-center">
					<div class="d-flex align-items-center">
						<div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
							<i class="fas fa-edit fa-2x"></i>
						</div>
						<div>
							<h5 class="card-title mb-0 text-muted">Edit Tenant</h5><br>
							<small class="text-muted">{{ $tenant->name }}</small>
						</div>
					</div>
					<div class="btn-group" role="group">
						<a href="{{ route('central.tenants.show', $tenant->id) }}?return_page={{ request('return_page', 1) }}" class="btn btn-secondary">
							<i class="fas fa-arrow-left me-1"></i>Cancel
						</a>
						<button type="submit" form="editTenantForm" class="btn btn-warning">
							<i class="fas fa-save me-1"></i>Update Tenant
						</button>
					</div>
				</div>
			</div>
			<div class="ghost-card-body bg-transparent">
		<div class="card card-warning card-outline mb-4">
			<div class="card-header">
				<h5 class="card-title mb-0">
					<i class="fas fa-building me-2"></i>Tenant Information
				</h5>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('central.tenants.update', $tenant->id) }}" id="editTenantForm">
					@csrf
					@method('PUT')
					<input type="hidden" name="return_page" value="{{ request('return_page', 1) }}">

					{{-- Basic Information Section --}}
					<div class="mb-4">
						<h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
						<div class="row mb-3">
							<div class="col-md-4">
								<label for="name" class="form-label">Tenant Name <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-building"></i></span>
									<input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $tenant->name) }}" required>
									@error('name')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							{{-- This should be a dropdown to select from tenant domains --}}
							<div class="col-md-4">
								<label for="domain" class="form-label">Primary Domain <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-globe"></i></span>
									<select class="form-select @error('domain') is-invalid @enderror" id="domain" name="domain" required>
										<option value="">Select a domain</option>
										@foreach($tenant->domains as $domain)
										<option value="{{ $domain->domain }}" {{ old('domain', $tenant->primary_domain) == $domain->domain ? 'selected' : '' }}>{{ $domain->domain }}</option>
										@endforeach
									</select>
									@error('domain')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">Select from existing tenant domains.</small>
							</div>
							{{-- database should not be changed because the tenant is already using it --}}
							<div class="col-md-4">
								<label for="database" class="form-label">Database</label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-database"></i></span>
									<select class="form-select @error('database') is-invalid @enderror" id="database" name="database">
										<option value="">Auto Assign from Pool</option>
										<option value="{{ $tenant->tenancy_db_name }}" selected>{{ $tenant->tenancy_db_name }}</option>
										@foreach($availableDbs as $dbName)
										<option value="{{ $dbName }}" {{ old('database', $tenant->tenancy_db_name) == $dbName ? 'selected' : '' }}>{{ $dbName }}</option>
										@endforeach
									</select>
									@error('database')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">Database cannot be changed for existing tenant.</small>
							</div>
						</div>
					</div>
					{{-- Contact Information Section --}}
					<div class="mb-4">
						<h6 class="text-muted mb-3"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
						<div class="row mb-3">
							<div class="col-md-4">
								<label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-user"></i></span>
									<input type="text" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person" value="{{ old('contact_person', $tenant->contact_person) }}" required>
									@error('contact_person')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							<div class="col-md-4">
								<label for="email" class="form-label">Email <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-envelope"></i></span>
									<input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $tenant->email) }}" required>
									@error('email')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							<div class="col-md-4">
								<label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-phone"></i></span>
									<input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $tenant->phone) }}" required>
									@error('phone')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-12">
								<label for="address" class="form-label">Address</label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
									<textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $tenant->address) }}</textarea>
									@error('address')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>
					</div>
					
					{{-- Regional Settings Section --}}
					<div class="mb-4">
						<h6 class="text-muted mb-3"><i class="fas fa-globe-africa me-2"></i>Regional Settings</h6>
						<div class="row mb-3">
							<div class="col-md-4">
								<label for="locale" class="form-label">Locale <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-language"></i></span>
									<input type="text" class="form-control @error('locale') is-invalid @enderror" id="locale" name="locale" value="{{ old('locale', $tenant->locale) }}" required>
									@error('locale')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">e.g., en_US, en_ZA</small>
							</div>
							<div class="col-md-4">
								<label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-clock"></i></span>
									<input type="text" class="form-control @error('timezone') is-invalid @enderror" id="timezone" name="timezone" value="{{ old('timezone', $tenant->timezone) }}" required>
									@error('timezone')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">e.g., Africa/Johannesburg</small>
							</div>
							<div class="col-md-4">
								<label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-money-bill"></i></span>
									<input type="text" class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency" value="{{ old('currency', $tenant->currency) }}" required>
									@error('currency')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">e.g., ZAR, USD</small>
							</div>
						</div>
					</div>
					
					{{-- Subscription & Status Section --}}
					<div class="mb-4">
						<h6 class="text-muted mb-3"><i class="fas fa-credit-card me-2"></i>Subscription & Status</h6>
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="plan" class="form-label">Current Plan</label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-box"></i></span>
									<select class="form-select @error('plan') is-invalid @enderror" id="plan" name="plan" required>
										<option value="" disabled selected>Select a plan</option>
										@foreach($plans as $plan)
										<optgroup label="{{ $plan->name }}">
											<option value="{{ $plan->name }}" {{ old('plan', $tenant->current_plan_name) == $plan->name ? 'selected' : '' }}>
												Monthly - {{ $currency }} {{ number_format($plan->monthly_price, 2) }}/month
											</option>
											<option value="{{ $plan->name }}_yearly" {{ old('plan', $tenant->current_plan_name) == $plan->name.'_yearly' ? 'selected' : '' }}>
												Yearly - {{ $currency }} {{ number_format($plan->yearly_price, 2) }}/year
											</option>
										</optgroup>
										@endforeach
									</select>
									@error('plan')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">Select billing cycle for subscription.</small>
							</div>
							<div class="col-md-6">
								<label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
									<select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
										<option value="1" {{ old('is_active', $tenant->is_active) == '1' ? 'selected' : '' }}>Active</option>
										<option value="0" {{ old('is_active', $tenant->is_active) == '0' ? 'selected' : '' }}>Trialing</option>
									</select>
									@error('is_active')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
								<small class="form-text text-muted">Current tenant status.</small>
							</div>
						</div>
					</div>

				</form>
			</div>
			<div class="card-footer">
				<div class="d-flex justify-content-between align-items-center">
					<a href="{{ route('central.tenants.show', $tenant->id) }}?return_page={{ request('return_page', 1) }}" class="btn btn-secondary">
						<i class="fas fa-arrow-left me-1"></i>Cancel
					</a>
					<button type="submit" form="editTenantForm" class="btn btn-warning">
						<i class="fas fa-save me-1"></i>Update Tenant
					</button>
				</div>
			</div>
		</div>
		<!--end::Main form card-->
			</div>
		</div>

	</div>
	<!--end::Container-->
</div>
<!--end::App Content-->

{{-- script to auto-generate domain from name --}}
<script>
	// limit to alphanumeric and hyphens
	// also convert to lowercase (for now we will not use this because we want to select from existing domains)
	// document.getElementById('name').addEventListener('input', function() {
	// 	var name = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
	// 	// only with max string length of 50 characters
	// 	name = name.substring(0, 50);
	// 	// append central domain from config
	// 	if(name) {
	// 		document.getElementById('domain').value = name + "{{ $centralDomain ? '.' . $centralDomain : '.ubixcentral.local' }}";
	// 	} else {
	// 		document.getElementById('domain').value = '';
	// 	}
	// });
</script>
@endsection