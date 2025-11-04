@extends('tenant.layouts.app')

@section('title', 'PayGate Settings')

@section('content')
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 text-muted">
          <i class="fas fa-credit-card"></i> PayGate Settings
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Payment Gateway</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
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

    <form action="{{ route('tenant.settings.paygate.update') }}" method="POST">
      @csrf
      @method('POST')
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-warning card-outline">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-credit-card me-2"></i>PayGate Credentials
              </h5>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label for="merchant_id" class="form-label required">Paygate ID <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('merchant_id') is-invalid @enderror" id="merchant_id" name="merchant_id" value="{{ old('merchant_id', $settings['merchant_id']) }}" required>
                @error('merchant_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-3">
                <label for="merchant_key" class="form-label required">Encryption Key <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('merchant_key') is-invalid @enderror" id="merchant_key" name="merchant_key" value="{{ old('merchant_key', $settings['merchant_key']) }}" required>
                @error('merchant_key')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Your encryption key is stored securely.</div>
              </div>
              <div class="mb-3">
                <label for="passphrase" class="form-label">Passphrase</label>
                <input type="text" class="form-control @error('passphrase') is-invalid @enderror" id="passphrase" name="passphrase" value="{{ old('passphrase', $settings['passphrase']) }}" autocomplete="new-password">
                @error('passphrase')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Leave blank if not required by your PayGate account.</div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6 mb-md-0">
                  <label for="is_test" class="form-label required">Environment</label>
                  <select class="form-select @error('is_test') is-invalid @enderror" id="is_test" name="is_test" required>
                    <option value="1" {{ old('is_test', $settings['is_test']) == '1' ? 'selected' : '' }}>Test Mode</option>
                    <option value="0" {{ old('is_test', $settings['is_test']) == '0' ? 'selected' : '' }}>Live Mode</option>
                  </select>
                  @error('is_test')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Select 'Test Mode' for sandbox/testing environment or 'Live Mode' for production.</div>
                </div>
                <div class="col-md-6">
                  <label for="is_default" class="form-label required">Default Payment Method</label>
                  <select class="form-select @error('is_default') is-invalid @enderror" id="is_default" name="is_default" required>
                    <option value="1" {{ old('is_default', $settings['is_default']) == '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('is_default', $settings['is_default']) == '0' ? 'selected' : '' }}>No</option>
                  </select>
                  @error('is_default')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div class="form-text">Select 'Yes' to make PayGate the default payment method.</div>
                </div>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="reset" class="btn btn-outline-warning">
                <i class="fas fa-undo me-1"></i>Reset
              </button>
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-save me-1"></i>Update Settings
              </button>
              <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
              </a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
