@extends('central.layouts.app')

@section('title', 'General Settings')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0 text-muted">
          <i class="fas fa-sliders-h"></i> General Settings
        </h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('central.settings.index') }}">Settings</a></li>
          <li class="breadcrumb-item active" aria-current="page">General Settings</li>
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

    <form action="{{ route('central.settings.general.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="row justify-content-center">
        <div class="col-md-10">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-sliders-h me-2"></i>General Configuration
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="site_name" class="form-label required">Site Name</label>
                  <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                         id="site_name" name="site_name" 
                         value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
                  @error('site_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label for="site_email" class="form-label required">Site Email</label>
                  <input type="email" class="form-control @error('site_email') is-invalid @enderror" 
                         id="site_email" name="site_email" 
                         value="{{ old('site_email', $settings['site_email'] ?? '') }}" required>
                  @error('site_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label for="support_email" class="form-label">Support Email</label>
                  <input type="email" class="form-control @error('support_email') is-invalid @enderror" 
                         id="support_email" name="support_email" 
                         value="{{ old('support_email', $settings['support_email'] ?? '') }}">
                  @error('support_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label for="contact_email" class="form-label">Contact Email</label>
                  <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                         id="contact_email" name="contact_email" 
                         value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                  @error('contact_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label for="site_phone" class="form-label">Site Phone</label>
                  <input type="text" class="form-control @error('site_phone') is-invalid @enderror" 
                         id="site_phone" name="site_phone" 
                         value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
                  @error('site_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label for="address" class="form-label">Address</label>
                  <textarea class="form-control @error('address') is-invalid @enderror" 
                            id="address" name="address" rows="3">{{ old('address', $settings['address'] ?? '') }}</textarea>
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="reset" class="btn btn-outline-secondary">
                <i class="fas fa-undo me-1"></i>Reset
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Settings
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
