@extends('central.layouts.app')

@section('title', 'PayFast Settings')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0 text-muted">
          <i class="fas fa-credit-card"></i> PayFast Settings
        </h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.settings.index') }}">Settings</a></li>
          <li class="breadcrumb-item active" aria-current="page">PayFast</li>
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
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form action="{{ route('central.settings.payfast.update') }}" method="POST">
      @csrf
      @method('POST')
      <div class="row justify-content-center">
        <div class="col-md-10">
          <div class="card card-warning card-outline">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-credit-card me-2"></i>PayFast Payment Gateway Configuration
              </h5>
            </div>
            <div class="card-body">
              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> These credentials will be used for central subscription payments. 
                Configure your PayFast merchant account at <a href="https://www.payfast.co.za" target="_blank">payfast.co.za</a>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="merchant_id" class="form-label required">Merchant ID</label>
                  <input type="text" class="form-control @error('merchant_id') is-invalid @enderror" 
                         id="merchant_id" name="merchant_id" 
                         value="{{ old('merchant_id', $settings['merchant_id']) }}" required>
                  @error('merchant_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Your PayFast merchant ID</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="merchant_key" class="form-label required">Merchant Key</label>
                  <input type="text" class="form-control @error('merchant_key') is-invalid @enderror" 
                         id="merchant_key" name="merchant_key" 
                         value="{{ old('merchant_key', $settings['merchant_key']) }}" required autocomplete="new-password">
                  @error('merchant_key')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Your merchant key is stored securely and encrypted</div>
                </div>
                <div class="col-md-12 mb-3">
                  <label for="passphrase" class="form-label">Passphrase</label>
                  <input type="text" class="form-control @error('passphrase') is-invalid @enderror" 
                         id="passphrase" name="passphrase" 
                         value="{{ old('passphrase', $settings['passphrase']) }}" autocomplete="new-password">
                  @error('passphrase')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Leave blank if not configured in your PayFast account. This is stored securely and encrypted.</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="is_test" class="form-label required">Environment Mode</label>
                  <select class="form-select @error('is_test') is-invalid @enderror" id="is_test" name="is_test" required>
                    <option value="1" {{ old('is_test', $settings['is_test']) == '1' ? 'selected' : '' }}>
                      <i class="fas fa-flask"></i> Test Mode (Sandbox)
                    </option>
                    <option value="0" {{ old('is_test', $settings['is_test']) == '0' ? 'selected' : '' }}>
                      <i class="fas fa-check-circle"></i> Live Mode (Production)
                    </option>
                  </select>
                  @error('is_test')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">
                    <strong class="text-warning">⚠️ Warning:</strong> Use Test Mode for development/testing. 
                    Switch to Live Mode only for production.
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="is_default" class="form-label required">Default Payment Gateway</label>
                  <select class="form-select @error('is_default') is-invalid @enderror" id="is_default" name="is_default" required>
                    <option value="1" {{ old('is_default', $settings['is_default']) == '1' ? 'selected' : '' }}>
                      Yes - Use as Default
                    </option>
                    <option value="0" {{ old('is_default', $settings['is_default']) == '0' ? 'selected' : '' }}>
                      No - Alternative Gateway
                    </option>
                  </select>
                  @error('is_default')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Set this as the default payment method for subscriptions</div>
                </div>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="reset" class="btn btn-outline-secondary">
                <i class="fas fa-undo me-1"></i>Reset
              </button>
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-save me-1"></i>Update PayFast Settings
              </button>
              <a href="{{ route('central.settings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Settings
              </a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<!--end::App Content-->
@endsection
