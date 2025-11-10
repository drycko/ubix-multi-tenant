@extends('central.layouts.app')

@section('title', 'Tenant Details - ' . $tenant->name)

@section('content')

<!--begin::App Content Header-->
{{-- <div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-building"></i> Tenant Details
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.tenants.index') }}">Tenants</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $tenant->name }}</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div> --}}
<!--end::App Content Header-->
<!--begin::App Content-->
<div class="app-content mt-3">
  <!--begin::Container-->
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

    <!-- Header Card with Actions -->
    <div class="ghost-card mb-4">
      <div class="ghost-card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
              <i class="fas fa-building fa-2x"></i>
            </div>
            <div>
              <h5 class="card-title mb-0 text-muted">
                {{ $tenant->name }}
                @if($tenant->current_plan)
                  @if($tenant->current_plan->status === 'active')
                    <span class="badge bg-success ms-2">Active</span>
                  @elseif($tenant->current_plan->status === 'trial')
                    <span class="badge bg-info ms-2">Trial</span>
                  @else
                    <span class="badge bg-warning ms-2">{{ ucfirst($tenant->current_plan->status) }}</span>
                  @endif
                @else
                  <span class="badge bg-secondary ms-2">No Plan</span>
                @endif
              </h5><br>
              <small class="text-muted">{{ $tenant->email }}</small>
            </div>
          </div>
          <div class="btn-group" role="group">
            <a href="{{ route('central.tenants.edit', $tenant->id) }}?return_page={{ request('return_page', 1) }}" class="btn btn-warning">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-cog me-1"></i>Actions
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a href="{{ route('central.tenants.login-as-tenant', $tenant->id) }}" class="dropdown-item" target="_blank">
                    <i class="fas fa-user-secret me-2"></i>Test as Tenant
                  </a>
                </li>
                <li>
                  <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sendTenantEmailModal">
                    <i class="fas fa-envelope me-2"></i>Send Welcome Email
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('central.tenants.destroy', $tenant->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="return_page" value="{{ request('return_page', 1) }}">
                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.')">
                      <i class="fas fa-trash me-2"></i>Delete Tenant
                    </button>
                  </form>
                </li>
              </ul>
            </div>
            <a href="{{ route('central.tenants.index', ['page' => request('return_page', 1)]) }}" class="btn btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
          </div>
        </div>
      </div>
			<div class="ghost-card-body bg-transparent">
				    
				<div class="row">
					<!-- Tenant Information -->
					<div class="col-md-6">
						<div class="card card-info card-outline mb-4">
							<div class="card-header">
								<h5 class="card-title mb-0">
									<i class="fas fa-info-circle me-2"></i>Tenant Information
								</h5>
							</div>
							<div class="card-body">
								<dl class="row">
									<dt class="col-sm-4">Tenant ID:</dt>
									<dd class="col-sm-8">
										<code>{{ $tenant->id }}</code>
									</dd>

									<dt class="col-sm-4">Name:</dt>
									<dd class="col-sm-8">{{ $tenant->name }}</dd>

									<dt class="col-sm-4">Email:</dt>
									<dd class="col-sm-8">
										<a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a>
									</dd>

									<dt class="col-sm-4">Primary Domain:</dt>
									<dd class="col-sm-8">
										<a href="http://{{ $tenant->primary_domain }}" target="_blank">
											{{ $tenant->primary_domain }} <i class="fas fa-external-link-alt fa-xs"></i>
										</a>
									</dd>

									<dt class="col-sm-4">Database:</dt>
									<dd class="col-sm-8">
										<code>{{ $tenant->tenancy_db_name }}</code>
									</dd>

									<dt class="col-sm-4">Created:</dt>
									<dd class="col-sm-8">
										{{ $tenant->created_at->format('M d, Y \a\t g:i A') }}
										<small class="text-muted d-block">{{ $tenant->created_at->diffForHumans() }}</small>
									</dd>

									<dt class="col-sm-4">Last Updated:</dt>
									<dd class="col-sm-8">
										{{ $tenant->updated_at->format('M d, Y \a\t g:i A') }}
										<small class="text-muted d-block">{{ $tenant->updated_at->diffForHumans() }}</small>
									</dd>
								</dl>
							</div>
							<div class="card-footer">
								<a href="{{ route('central.tenants.domains', $tenant->id) }}" class="btn btn-sm btn-primary">
									<i class="fas fa-globe me-1"></i>Manage Domains
								</a>
							</div>
						</div>
					</div>

					<!-- Subscription Information -->
					<div class="col-md-6">
						<div class="card card-success card-outline mb-4">
							<div class="card-header">
								<h5 class="card-title mb-0">
									<i class="fas fa-credit-card me-2"></i>Subscription Details
								</h5>
							</div>
							<div class="card-body">
								@if($tenant->current_plan)
								<dl class="row">
									<dt class="col-sm-4">Current Plan:</dt>
									<dd class="col-sm-8">
										<span class="badge bg-primary">{{ $tenant->current_plan->plan->name }}</span>
									</dd>

									<dt class="col-sm-4">Status:</dt>
									<dd class="col-sm-8">
										@if($tenant->current_plan->status === 'active')
											<span class="badge bg-success">Active</span>
										@elseif($tenant->current_plan->status === 'trial')
											<span class="badge bg-info">Trial</span>
										@elseif($tenant->current_plan->status === 'cancelled')
											<span class="badge bg-danger">Cancelled</span>
										@else
											<span class="badge bg-warning">{{ ucfirst($tenant->current_plan->status) }}</span>
										@endif
									</dd>

									@if($tenant->current_plan->status === 'trial')
									<dt class="col-sm-4">Trial Ends:</dt>
									<dd class="col-sm-8">
										{{ $tenant->current_plan->trial_ends_at ? date('M d, Y', strtotime($tenant->current_plan->trial_ends_at)) : 'N/A' }}
										@if($tenant->current_plan->trial_days_left)
											<small class="text-muted d-block">{{ $tenant->current_plan->trial_days_left }} days left</small>
										@endif
									</dd>
									@endif

									<dt class="col-sm-4">Plan Expiry:</dt>
									<dd class="col-sm-8">
										{{ $tenant->current_plan->end_date ? date('M d, Y', strtotime($tenant->current_plan->end_date)) : 'N/A' }}
									</dd>
								</dl>
								@else
								<div class="alert alert-warning" role="alert">
									<i class="fas fa-exclamation-triangle me-2"></i>
									No active subscription plan.
								</div>
								@endif
							</div>
							<div class="card-footer">
								<div class="btn-group" role="group">
									<a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#switchToPremiumModal">
										<i class="fas fa-exchange-alt me-1"></i>{{ $tenant->current_plan ? 'Change Plan' : 'Assign Plan' }}
									</a>
									@if($tenant->current_plan)
									<a href="{{ route('central.subscriptions.show', $tenant->current_plan->id) }}" class="btn btn-sm btn-primary">
										<i class="fas fa-eye me-1"></i>View Details
									</a>
									@endif
									<a href="{{ route('central.tenants.subscriptions', $tenant->id) }}" class="btn btn-sm btn-info">
										<i class="fas fa-list me-1"></i>All Subscriptions
									</a>
								</div>
							</div>
						</div>
            {{-- tenant admin user --}}
            <div class="card card-secondary card-outline mb-4">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="fas fa-user-cog me-2"></i>Tenant Admin User
                </h5>
              </div>
              <div class="card-body">
                @if($tenant->admin)
                <dl class="row">
                  <dt class="col-sm-4">Name:</dt>
                  <dd class="col-sm-8">{{ $tenant->admin->name }}</dd>

                  <dt class="col-sm-4">Email:</dt>
                  <dd class="col-sm-8">
                    <a href="mailto:{{ $tenant->admin->email }}">{{ $tenant->admin->email }}</a>
                  </dd>

                  <dt class="col-sm-4">Created At:</dt>
                  <dd class="col-sm-8">
                    {{ $tenant->admin->created_at->format('M d, Y \a\t g:i A') }}
                    <small class="text-muted
                      d-block">{{ $tenant->admin->created_at->diffForHumans() }}</small>
                  </dd>
                </dl>
                @else
                <div class="alert alert-warning" role="alert">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  No admin user found for this tenant.
                </div>
                @endif
              </div>
            </div>
					</div>
				</div>
			</div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

{{-- Send Tenant Welcome Email Modal --}}
<div class="modal fade" id="sendTenantEmailModal" tabindex="-1" aria-labelledby="sendTenantEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendTenantEmailModalLabel">
          <i class="fas fa-envelope me-2"></i>Send Welcome Email
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('central.tenants.send-email', $tenant->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <p>Are you sure you want to send a welcome email to <strong>{{ $tenant->email }}</strong>?</p>
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            This will send login credentials and setup information to the tenant.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane me-1"></i>Send Email
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Switch to Premium Plan Modal --}}
<div class="modal fade" id="switchToPremiumModal" tabindex="-1" aria-labelledby="switchToPremiumModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="switchToPremiumModalLabel">
          <i class="fas fa-exchange-alt me-2"></i>{{ $tenant->current_plan ? 'Change Subscription Plan' : 'Assign Subscription Plan' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('central.tenants.switch-to-premium', $tenant->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="plan_name" class="form-label">Select Subscription Plan <span class="text-danger">*</span></label>
            <select class="form-select" id="plan_name" name="plan_name" required>
              <option value="" disabled selected>Choose a plan...</option>
              @foreach($availablePlans as $plan)
              <optgroup label="{{ $plan->name }}">
                <option value="{{ $plan->name }}" {{ old('plan_name') == $plan->name ? 'selected' : '' }}>
                  Monthly - {{ $currency }} {{ number_format($plan->monthly_price, 2) }}/month
                </option>
                <option value="{{ $plan->name }}_yearly" {{ old('plan_name') == $plan->name.'_yearly' ? 'selected' : '' }}>
                  Yearly - {{ $currency }} {{ number_format($plan->yearly_price, 2) }}/year 
                  <span class="text-success">(Save {{ number_format((1 - ($plan->yearly_price / ($plan->monthly_price * 12))) * 100, 0) }}%)</span>
                </option>
              </optgroup>
              @endforeach
            </select>
            <small class="form-text text-muted">Select a billing cycle for the subscription plan.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check me-1"></i>{{ $tenant->current_plan ? 'Change Plan' : 'Assign Plan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection