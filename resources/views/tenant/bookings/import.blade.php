@extends('tenant.layouts.app')

@section('title', 'Import Bookings')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Import Bookings</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Import Bookings</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
{{-- <div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Import Bookings</h1>
</div> --}}

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
    
    {{-- form error messages --}}
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
        <h5 class="card-title">Import from CSV file</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('tenant.bookings.import.post') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label for="csv_file" class="form-label">CSV File</label>
            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
          </div>
          <button type="submit" class="btn btn-sm btn-success">Import</button>
        </form>
        <hr>
        <h5>CSV Format</h5>
        <p>The CSV file should have the following columns:</p>
        <ul>
          <li><strong>Booking Code</strong></li>
          <li><strong>Guest First Name</strong></li>
          <li><strong>Guest Last Name</strong></li>
          <li><strong>Room Number</strong></li>
          <li><strong>Room Type</strong></li>
          <li><strong>Arrival Date</strong></li>
          <li><strong>Departure Date</strong></li>
          <li><strong>Nights</strong></li>
          <li><strong>Total Amount</strong></li>
        </ul>
      </div>
    </div>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->
@endsection