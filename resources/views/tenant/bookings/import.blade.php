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
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item active" aria-current="page">Import</li>
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
    
    {{-- Success/Error Messages --}}
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
    
    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h6 class="alert-heading">Please fix the following errors:</h6>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-md-8 mb-3">
        <!-- Import Form -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-upload me-2"></i>Upload CSV File
            </h5>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.bookings.import.post') }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="mb-4">
                <label for="csv_file" class="form-label">
                  <i class="fas fa-file-csv me-1"></i>Choose CSV File
                </label>
                <input type="file" 
                       class="form-control @error('csv_file') is-invalid @enderror" 
                       id="csv_file" 
                       name="csv_file" 
                       accept=".csv,.txt" 
                       required>
                @error('csv_file')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                  Select a CSV file containing booking data. Maximum file size: 10MB
                </div>
              </div>
              
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-upload me-1"></i>Import Bookings
                </button>
                <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- CSV Format Information -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-info-circle me-2"></i>CSV Format Requirements
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#importInstructions" aria-expanded="false" aria-controls="importInstructions">
                <i class="bi bi-arrows-collapse"></i>
              </button>
            </div>
          </div>
          <div class="card-body collapse" id="importInstructions">
            <p class="mb-3">The CSV file must contain the following columns in this exact order:</p>
            
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="table-dark">
                  <tr>
                    <th>Column</th>
                    <th>Description</th>
                    <th>Example</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><code>BCODE</code></td>
                    <td>Unique booking code</td>
                    <td>20241002001001</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>GSTNAME</code></td>
                    <td>Guest full name</td>
                    <td>John Doe</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>GSTEMAIL</code></td>
                    <td>Guest email address</td>
                    <td>john@example.com</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>GSTTELNO</code></td>
                    <td>Guest phone number</td>
                    <td>+27123456789</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>GSTTITLE</code></td>
                    <td>Guest title</td>
                    <td>Mr/Ms/Dr</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>NATIONALITY</code></td>
                    <td>Guest nationality</td>
                    <td>South African</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>GSTIDNO</code></td>
                    <td>Guest ID number</td>
                    <td>1234567890123</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>ROOMNO</code></td>
                    <td>Room number</td>
                    <td>101</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>ARDATE</code></td>
                    <td>Arrival date (YYYY-MM-DD)</td>
                    <td>2024-10-15</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>DPDATE</code></td>
                    <td>Departure date (YYYY-MM-DD)</td>
                    <td>2024-10-17</td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>DAILYTARIFF</code></td>
                    <td>Daily room rate</td>
                    <td>150.00</td>
                    <td><span class="badge bg-success">Auto-calculated</span></td>
                  </tr>
                  <tr>
                    <td><code>STATUS</code></td>
                    <td>Booking status</td>
                    <td>Confirmed/Pending/Cancelled</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>PACKAGE</code></td>
                    <td>Package ID (if applicable)</td>
                    <td>1</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>ISSHARES</code></td>
                    <td>Shared room (Y/N)</td>
                    <td>Y or N</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>TIMEARRIVE</code></td>
                    <td>Arrival time</td>
                    <td>15:00</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>INVOICE_NO</code></td>
                    <td>Invoice number</td>
                    <td>INV001</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                  <tr>
                    <td><code>META_DATA</code></td>
                    <td>Additional data (JSON)</td>
                    <td>{}</td>
                    <td><span class="badge bg-warning">Optional</span></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Important Notes:</strong>
              <ul class="mb-0 mt-2">
                <li>The first row should contain column headers</li>
                <li>All dates must be in YYYY-MM-DD format</li>
                <li>Room numbers must exist in your system</li>
                <li>Duplicate booking codes will be skipped</li>
                <li>Invalid data will be skipped with error logging</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-tools me-2"></i>Quick Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.bookings.template') }}" class="btn btn-outline-primary">
                <i class="fas fa-download me-1"></i>Download Template
              </a>
              <a href="{{ route('tenant.bookings.export') }}" class="btn btn-outline-success">
                <i class="fas fa-file-export me-1"></i>Export Current Data
              </a>
              <hr>
              <a href="{{ route('tenant.bookings.create') }}" class="btn btn-outline-info">
                <i class="fas fa-plus me-1"></i>Add Single Booking
              </a>
              <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i>View All Bookings
              </a>
            </div>
          </div>
        </div>

        <!-- Import Statistics -->
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="card-title">
              <i class="fas fa-chart-line me-2"></i>Import Tips
            </h5>
          </div>
          <div class="card-body">
            <div class="small">
              <div class="mb-2">
                <i class="fas fa-check-circle text-success me-1"></i>
                Valid data will be imported automatically
              </div>
              <div class="mb-2">
                <i class="fas fa-exclamation-circle text-warning me-1"></i>
                Missing guests will be created automatically
              </div>
              <div class="mb-2">
                <i class="fas fa-times-circle text-danger me-1"></i>
                Invalid data will be skipped with errors logged
              </div>
              <div class="mb-0">
                <i class="fas fa-info-circle text-info me-1"></i>
                You'll receive a summary report after import
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->
@endsection