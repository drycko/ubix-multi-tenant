@extends('central.layouts.app')

@section('title', 'Show Subscription')

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
				<li class="breadcrumb-item"><a href="{{ route('central.subscriptions.index') }}">All Subscriptions</a></li>
				<li class="breadcrumb-item active" aria-current="page">Tenant Subscription</li>
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
        <h5 class="card-title">Subscription Details</h5>
        <div class="card-tools float-end">
          @if($subscription->hasInvoice())

          {{-- only show cancel subscription button if the subscription is active --}}
          {{-- if there are any outstanding invoices for this subscription --}}
          {{-- only show pay invoice button if there is an outstanding invoice and the subscription is not canceled or trial --}}
          @if($subscription->hasOutstandingInvoices && $subscription->status !== 'canceled' && $subscription->status !== 'trial')
          {{-- if the current user can manage subscriptions --}}
          @can('manage subscriptions')
          
          {{-- pay last invoice modal for this subscription --}}
          {{-- <a href="#" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#payInvoiceModal">
            <i class="fas fa-credit-card me-2"></i> Pay Invoice
          </a> --}}
          
          {{-- view last invoice for this subscription --}}
          <a href="{{ route('central.invoices.show', $subscription->latestInvoice()) }}" class="btn btn-sm btn-info">
            <i class="fas fa-file-invoice me-2"></i> View Invoice
          </a>
          @endcan
          {{-- end can manage subscriptions --}}
          @endif
          {{-- end if has outstanding invoices --}}
          @endif
          {{-- end if has invoice --}}
        </div>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <tr>
            <th>Plan Name</th>
            <td>{{ $subscription->plan ? $subscription->plan->name : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Tenant</th>
            <td>{{ $subscription->tenant ? $subscription->tenant->name : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Price</th>
            <td> {{ number_format($subscription->price, 2) }} {{ $currency }} ({{ucfirst($subscription->billing_cycle)}})</td>
          </tr>
          <tr>
            <th>Start Date</th>
            <td>{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d') : 'N/A' }}</td>
          </tr>
          <tr>
            <th>End Date</th>
            <td>{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d') : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Status</th>
            <td><span class=" badge {{$subscription->status == 'active' ? 'text-bg-success' : ($subscription->status == 'canceled' ? 'text-bg-danger' : 'text-bg-warning')}}">{{ ucfirst($subscription->status) }}</span></td>
          </tr>
          <tr>
            <th>Created At</th>
            <td>{{ $subscription->created_at ? $subscription->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Updated At</th>
            <td>{{ $subscription->updated_at ? $subscription->updated_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
          </tr>
        </table>
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col-md-6">
            <a href="{{ route('central.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-arrow-left me-2"></i>Back to All Subscriptions
            </a>
          </div>
          
          <div class="col-md-6">
            <div class="float-end">
              {{-- buttons to manage domains and subscriptions --}}
              <a href="#" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-edit me-2"></i> Edit Subscription
              </a>
              <form action="{{ route('central.subscriptions.cancel', [$subscription->id]) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this subscription? This action cannot be undone.');">
                  <i class="fas fa-times me-2"></i> Cancel Subscription
                </button>
              </form>
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

<!-- Pay Invoice Modal -->

@endsection
      