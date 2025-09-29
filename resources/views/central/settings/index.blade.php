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
      {{-- <h3 class="mb-0">General Settings</h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
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

    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title">General Settings</h5>
        <div class="card-tools float-end">
          
          
          @can('manage settings')
          <button type="submit" form="settingsForm" class="btn btn-sm btn-outline-success">
            <i class="fas fa-save me-2"></i>Save Settings
          </button>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <form id="settingsForm" action="{{ route('central.settings.update') }}" enctype="multipart/form-data" method="POST">
          @csrf
          @method('PUT')
          <div class="row">
            @foreach($settings as $key => $value)
            {{-- logo must be a file upload --}}
              <div class="col-md-6 mb-3">
                <label for="{{ $key }}" class="form-label text-capitalize">{{ str_replace('_', ' ', $key) }}</label>
                <input type="{{ str_contains($key, 'logo') ? 'file' : 'text' }}" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $value) }}">
                @error($key)
                  <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>
            @endforeach
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->
@endsection