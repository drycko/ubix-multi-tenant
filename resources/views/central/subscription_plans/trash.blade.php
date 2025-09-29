@extends('central.layouts.app')

@section('title', 'Trashed Subscription Plans')

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
          <li class="breadcrumb-item"><a href="{{ route('central.subscriptions.index') }}">Subscription Plans</a></li>
          <li class="breadcrumb-item active" aria-current="page">Trashed Subscription Plans</li>
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
        <h5 class="card-title">Trashed Subscription Plans</h5>
        <div class="card-tools float-end">
          @can('view subscriptions')
          <a href="{{ route('central.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Go to Subscriptions
          </a>
          @endcan
          @can('manage plans')
          <a href="{{ route('central.plans.restore-all') }}" class="btn btn-sm btn-outline-success me-2">
            <i class="fas fa-undo me-2"></i>Restore All Plans
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="plansTable" class="table table-hover">
            <thead>
              <tr>
                <th>Plan Name</th>
                <th>Slug</th>
                <th>Created At</th>
                <th>Deleted At</th>
                <th>Deleted By</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($trashedPlans as $plan)
              <tr>
                <td>{{ $plan->name }}</td>
                <td>{{ $plan->slug }}</td>
                <td>{{ $plan->created_at->format('Y-m-d') }}</td>
                <td>{{ $plan->deleted_at->format('Y-m-d') }}</td>
                <td>{{ $plan->deletedByAdminName ?? 'N/A' }}</td>
                <td>
                  @can('manage plans')
                  <!-- Restore Button -->
                  <form action="{{ route('central.plans.restore', $plan->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" title="Restore Plan">
                      <i class="fas fa-undo"></i> Restore
                    </button>
                  </form>

                  <!-- Permanently Delete Button -->
                  <form action="{{ route('central.plans.destroy', $plan->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this plan? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete Plan">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
                  </form>
                  @endcan
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center">No trashed subscription plans found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->
@endsection