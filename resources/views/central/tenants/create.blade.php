@extends('central.layouts.app')

@section('title', 'Create Tenant')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
            {{-- <h3 class="mb-0">Create New Tenant</h3> --}}
            </div>
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
				<li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
				<li class="breadcrumb-item"><a href="{{ route('central.tenants.index') }}">All Tenants</a></li>
				<li class="breadcrumb-item active" aria-current="page">Create Tenant</li>
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

		@if($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0">
						@foreach($errors->all() as $error)
								<li>{{ $error }}</li>
						@endforeach
				</ul>
			</div>
		@endif

		<div class="card card-success card-outline">
			<div class="card-header">
				<h5 class="card-title">Create Tenant</h5>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('central.tenants.store') }}">
					@csrf
					<div class="row mb-3">
						<div class="col-md-4">
							<label for="name" class="form-label">Tenant Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
							@error('name')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-4">
							<label for="domain" class="form-label">Primary Domain <span class="text-danger">*</span></label>
							<input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain') }}" placeholder="E.g. example.nexusflow.co.za" required>
							@error('domain')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						{{-- database should be optional, if not provided, we will assign one from the pool --}}
						<div class="col-md-4">
							<label for="database" class="form-label">Database</label>
							<select class="form-select @error('database') is-invalid @enderror" id="database" name="database">
								<option value="" selected>Auto Assign from Pool</option>
								@foreach($availableDbs as $dbName)
								<option value="{{ $dbName }}" {{ old('database') == $dbName ? 'selected' : '' }}>{{ $dbName }}</option>
								@endforeach
							</select>
							@error('database')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="email" class="form-label">Email <span class="text-danger">*</span></label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
							@error('email')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6">
							<label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
							@error('phone')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
					
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="locale" class="form-label">Locale <span class="text-danger">*</span></label>
							<input type="text" class="form-control @error('locale') is-invalid @enderror" id="locale" name="locale" value="{{ old('locale') }}" required>
							@error('locale')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6">
							<label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
							<input type="text" class="form-control @error('timezone') is-invalid @enderror" id="timezone" name="timezone" value="{{ old('timezone') }}" required>
							@error('timezone')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
					
					<div class="row mb-3">
						<div class="col-md-4">
							<label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
							<input type="text" class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency" value="{{ old('currency') }}" required>
							@error('currency')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-4">
							<label for="plan" class="form-label">Plan</label>
							<select class="form-select @error('plan') is-invalid @enderror" id="plan" name="plan" required>
								<option value="" disabled selected>Select a plan</option>
								@foreach($plans as $plan)
								{{-- make sure we have both yearly and monthly price in the plans as separate options --}}
								<option value="{{ $plan->name }}" {{ old('plan') == $plan->name ? 'selected' : '' }}>{{ $plan->name }} - {{ $currency }} {{ $plan->monthly_price }} / month</option>
								<option value="{{ $plan->name }}_yearly" {{ old('plan') == $plan->name.'_yearly' ? 'selected' : '' }}>{{ $plan->name }} - {{ $currency }} {{ $plan->yearly_price }} / year</option>
								@endforeach
							</select>
							@error('plan')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-4">
							<label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
							<select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
								<option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
								<option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Trialing</option>
							</select>
							@error('is_active')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>
					<div class="mb-3">
						<label for="address" class="form-label">Address</label>
						<textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address') }}</textarea>
						@error('address')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<button type="submit" class="btn btn-success">Create Tenant</button>
				</form>
			</div>
		</div>
	</div>
	<!--end::Container-->
</div>
<!--end::App Content-->

{{-- script to auto-generate domain from name --}}
<script>
	// limit to alphanumeric and hyphens
	// also convert to lowercase
	document.getElementById('name').addEventListener('input', function() {
		var name = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
		// only with max string length of 50 characters
		name = name.substring(0, 50);
		// append central domain from config
		if(name) {
			document.getElementById('domain').value = name + "{{ $centralDomain ? '.' . $centralDomain : '.ubixcentral.local' }}";
		} else {
			document.getElementById('domain').value = '';
		}
	});
</script>
@endsection