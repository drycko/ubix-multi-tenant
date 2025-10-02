@extends('tenant.layouts.app')

@section('title', 'Create Room Type')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        {{-- <h3 class="mb-0">Create Room Type</h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-types.index') }}">Room Types</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create Room Type</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    {{-- messages from session --}}
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
    
    {{-- validation errors --}}
    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="card card-success card-outline">
      <div class="card-header">
        <h5 class="card-title">New Room Type</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('tenant.room-types.store') }}" method="POST">
          @csrf
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
              @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="code" class="form-label">Short Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
              @error('code')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="base_capacity" class="form-label">Base Capacity <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('base_capacity') is-invalid @enderror" id="base_capacity" name="base_capacity" value="{{ old('base_capacity') }}" min="1" required>
              @error('base_capacity')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="max_capacity" class="form-label">Max Capacity <span class="text-danger">*</span></label>
              <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" id="max_capacity" name="max_capacity" value="{{ old('max_capacity') }}" min="1" required>
              @error('max_capacity')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
              @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label for="amenities" class="form-label">Amenities <small>(multi select)</small></label>
              <select class="form-control select2-multi @error('amenities') is-invalid @enderror" id="amenities" name="amenities[]" multiple>
                @foreach($allAmenities as $amenity)
                <option value="{{ $amenity->slug }}" {{ (collect(old('amenities'))->contains($amenity->slug)) ? 'selected':'' }}>{{ $amenity->name }}</option>
                @endforeach
              </select>
              @error('amenities')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 float-end text-end mb-3">
              <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-save me-2"></i>Save
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->
@endsection