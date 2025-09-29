@extends('central.layouts.app')

@section('title', 'Subscription Plans')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
      {{-- <h3 class="mb-0">Subscription Plans</h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Subscription Plans</li>
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
        <h5 class="card-title">Subscription Plans</h5>
        <div class="card-tools float-end">
          @can('view subscriptions')
          <a href="{{ route('central.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Go to Subscriptions
          </a>
          @endcan
          @can('manage plans')
          <a href="{{ route('central.plans.create') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-plus me-2"></i>Add New Plan
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Plan Name</th>
                <th>Price ({{ $currency }})</th>
                <th>Max Properties</th>
                <th>Max Users</th>
                <th>Max Rooms</th>
                <th>Max Bookings</th>
                <th>Max Guests</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($subscriptionPlans as $plan)
              <tr>
                <td>{{ $plan->name }}</td>
                <td>Monthly: {{ number_format($plan->monthly_price, 2) }}<br>Yearly: {{ number_format($plan->yearly_price, 2) }}</td>
                <td>{{ $plan->max_properties }}</td>
                <td>{{ $plan->max_users }}</td>
                <td>{{ $plan->max_rooms }}</td>
                <td>{{ $plan->max_bookings }}</td>
                <td>{{ $plan->max_guests }}</td>
                <td>
                  @can('manage plans')
                  <a href="{{ route('central.plans.edit', $plan) }}" class="btn btn-sm btn-outline-success me-2">
                    <i class="fas fa-edit me-2"></i>Edit
                  </a>
                  {{-- use modal to confirm deletion or soft deletion --}}
                  <a href="#" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deletePlanModal" data-plan-id="{{ $plan->id }}">
                    <i class="fas fa-trash me-2"></i>Delete
                  </a>
                  @endcan
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center">No subscription plans found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{-- Pagination links --}}
        @if($subscriptionPlans->hasPages())
        <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0 text-muted">
                        Showing {{ $subscriptionPlans->firstItem() }} to {{ $subscriptionPlans->lastItem() }} of {{ $subscriptionPlans->total() }} entries
                    </p>
                </div>
                <div class="col-md-4 float-end">
                    {{ $subscriptionPlans->links('vendor.pagination.bootstrap-5') }} {{-- I want to align the links to the end of the column --}}
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

{{-- Delete modal (ask if user want to destroy or soft delete) --}}
<div class="modal fade" id="deletePlanModal" tabindex="-1" aria-labelledby="deletePlanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deletePlanModalLabel">Delete Subscription Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this subscription plan? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        {{-- Force delete form --}}
        <form id="deletePlanForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <input type="hidden" name="plan_id" id="plan_id" value="">
          <button type="submit" class="btn btn-sm btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // pass the plan id to the modal form action
  var deletePlanModal = document.getElementById('deletePlanModal');
  
  deletePlanModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var planId = button.getAttribute('data-plan-id');

    // Update the modal's form action using simple URL construction
    var deleteForm = deletePlanModal.querySelector('#deletePlanForm');

    // Build URLs that match the route patterns exactly
    deleteForm.action = '/central/plans/' + planId;

    // Also set the hidden input values
    deletePlanModal.querySelector('#plan_id').value = planId;
    
    // Debug: log the URLs to console
    console.log('Delete URL:', deleteForm.action);
  });
</script>
@endsection