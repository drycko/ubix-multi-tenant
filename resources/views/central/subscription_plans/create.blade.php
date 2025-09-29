@extends('central.layouts.app')

@section('title', 'Create Plan')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
      {{-- <h3 class="mb-0">Create Subscription Plan</h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item" aria-current="page">Subscription Plans</li>
          <li class="breadcrumb-item active" aria-current="page">Create Plan</li>
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

    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title">Create New Subscription Plan</h5>
        <div class="card-tools float-end">
          <a href="{{ route('central.plans.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i> Back to Plans
          </a>
        </div>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('central.plans.store') }}">
          @csrf
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="name" class="form-label">Plan Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="name" class="form-label">Slug <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" required>
              <small class="form-text text-muted">Unique identifier, no spaces, use underscores (_)</small>
              @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="monthly_price" class="form-label">Monthly Price ({{ config('app.currency', 'USD') }}) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control @error('monthly_price') is-invalid @enderror" id="monthly_price" name="monthly_price" value="{{ old('monthly_price') }}" required>
              @error('monthly_price')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="yearly_price" class="form-label">Yearly Price ({{ config('app.currency', 'USD') }})</label>
              <input type="number" step="0.01" class="form-control @error('yearly_price') is-invalid @enderror" id="yearly_price" name="yearly_price" value="{{ old('yearly_price') }}" required>
              @error('yearly_price')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="max_properties" class="form-label">Max Properties <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('max_properties') is-invalid @enderror" id="max_properties" name="max_properties" value="{{ old('max_properties', 1) }}" required>
              @error('max_properties')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="max_rooms" class="form-label">Max Rooms <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('max_rooms') is-invalid @enderror" id="max_rooms" name="max_rooms" value="{{ old('max_rooms', 1) }}" required>
              @error('max_rooms')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="max_users" class="form-label">Max Users <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('max_users') is-invalid @enderror" id="max_users" name="max_users" value="{{ old('max_users', 1) }}" required>
              @error('max_users')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="max_bookings" class="form-label">Max Bookings <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('max_bookings') is-invalid @enderror" id="max_bookings" name="max_bookings" value="{{ old('max_bookings', 1) }}" required>
              @error('max_bookings')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="max_guests" class="form-label">Max Guests <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('max_guests') is-invalid @enderror" id="max_guests" name="max_guests" value="{{ old('max_guests', 1) }}" required>
              @error('max_guests')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          {{-- Additional features multiselect with select2 --}}
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="features" class="form-label">Additional Features</label>
              <select class="form-control select2-multi @error('features') is-invalid @enderror" id="features" name="features[]" multiple>
                {{-- These come in key value pairs from the SubscriptionPlan model --}}
                @foreach($additionalFeatures as $feature => $featureText)
                  <option value="{{ $feature }}" {{ (collect(old('features'))->contains($feature)) ? 'selected':'' }}>{{ ucfirst($featureText) }}</option>
                @endforeach
              </select>
              @error('features')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="description" class="form-label">Plan Description (optional)</label>
              <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          {{-- Submit buttons --}}
          <div class="d-flex justify-content-end">
          <a href="{{ route('central.plans.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
          <button type="submit" class="btn btn-success">Create Plan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

{{-- script to create a slug from the name field --}}
<script>
  document.getElementById('name').addEventListener('input', function() {
    var name = this.value;
    var slug = name.toLowerCase().replace(/ /g, '_').replace(/[^\w-]+/g, '');
    document.getElementById('slug').value = slug;
  });
</script>
@endsection