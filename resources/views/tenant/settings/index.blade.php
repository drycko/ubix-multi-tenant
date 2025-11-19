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
              {{-- General Settings --}}
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  <i class="fas fa-sliders-h me-2 text-primary"></i>General Settings
                </span>
                <a href="{{ route('tenant.settings.general') }}" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
              </li>

              {{-- Payment Gateways --}}
              <li class="list-group-item">
                <h6 class="mb-3 text-muted">
                  <i class="fas fa-credit-card me-2"></i>Payment Gateways
                </h6>
                <div class="row">
                  @foreach ($paymentGateways as $gateway => $settings)
                  <div class="col-md-6 mb-2">
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                      <span>
                        <i class="fas fa-money-check-alt me-2 text-success"></i>{{ ucfirst($gateway) }}
                        @if ($settings['is_default'])
                        <span class="badge bg-primary ms-2">Default</span>
                        @endif
                      </span>
                      <a href="{{ route('tenant.settings.' . $gateway . '.edit') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-edit me-1"></i>Configure
                      </a>
                    </div>
                  </div>
                  @endforeach
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
