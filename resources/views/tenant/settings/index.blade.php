@extends('tenant.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0 text-muted">
          <i class="fas fa-cogs"></i> Settings
        </h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Settings</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card card-secondary card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-cogs me-2"></i>Settings Modules
            </h5>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              {{-- Payment Gateways --}}
              @foreach ($paymentGateways as $gateway => $settings)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  <i class="fas fa-credit-card me-2 text-success"></i>{{ ucfirst($gateway) }} Payment Gateway
                  @if ($settings['is_default'])
                  <span class="badge bg-primary ms-2">Default</span>
                  @endif
                </span>
                <a href="{{ route('tenant.settings.' . $gateway . '.edit') }}" class="btn btn-sm btn-success">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
              </li>
              @endforeach
              {{-- Paygate settings --}}
              {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-credit-card me-2 text-success"></i>PayGate Payment Gateway</span>
                <a href="{{ route('tenant.settings.paygate.edit') }}" class="btn btn-sm btn-success">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
              </li> --}}
              {{-- Add more settings modules here as needed --}}
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
