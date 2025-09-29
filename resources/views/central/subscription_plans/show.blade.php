@extends('central.layouts.app')

@section('title', 'Show Plan | ' . $subscriptionPlan->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
      {{-- <h3 class="mb-0">Show Plan</h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.plans.index') }}">Subscription Plans</a></li>
          <li class="breadcrumb-item active" aria-current="page">Show Plan</li>
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
        <h5 class="card-title">Show Plan</h5>
        <div class="card-tools float-end">
          @can('view subscriptions')
          <a href="{{ route('central.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Go to Subscriptions
          </a>
          @endcan
          @can('manage plans')
          <a href="{{ route('central.plans.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Go to Plans
          </a>
          <a href="{{ route('central.plans.edit', [$subscriptionPlan->id]) }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-edit me-2"></i>Edit Plan
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <tbody>
              <tr>
                <th>Name</th>
                <td>{{ $subscriptionPlan->name }}</td>
              </tr>
              <tr>
                <th>Description</th>
                <td>{{ $subscriptionPlan->description }}</td>
              </tr>
              <tr>
                <th>Price</th>
                <td>{{ number_format($subscriptionPlan->price, 2) }} {{ $currency }}</td>
              </tr>
              <tr>
                <th>Billing Cycle</th>
                <td>{{ ucfirst($subscriptionPlan->billing_cycle) }}</td>
              </tr>
              <tr>
                <th>Trial Period (days)</th>
                <td>{{ $subscriptionPlan->trial_period_days ?? 'N/A' }}</td>
              </tr>
              <tr>
                <th>Created At</th>
                <td>{{ $subscriptionPlan->created_at->format('d M Y, H:i') }}</td>
              </tr>
              <tr>
                <th>Updated At</th>
                <td>{{ $subscriptionPlan->updated_at->format('d M Y, H:i') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->
@endsection