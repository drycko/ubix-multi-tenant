@extends('tenant.layouts.app')

@section('title', 'Import Rooms')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-upload"></i>
          <small class="text-muted">Import Rooms</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.rooms.index', ['property_id' => current_property()->id]) }}">Rooms</a></li>
          <li class="breadcrumb-item active" aria-current="page">Import</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<!--begin::App Content-->
<div class="app-content">
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
        <ul>
          @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row">
      <!-- Import Form -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-file-earmark-spreadsheet"></i> Upload CSV File
            </h3>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.rooms.import.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              
              <div class="mb-4">
                <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                <input type="file" 
                       class="form-control @error('csv_file') is-invalid @enderror" 
                       id="csv_file" 
                       name="csv_file" 
                       accept=".csv"
                       required>
                @error('csv_file')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Upload a CSV file with room data. Maximum file size: 5MB</div>
              </div>

              <!-- Import Options -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="skip_header" name="skip_header" value="1" checked>
                    <label class="form-check-label" for="skip_header">
                      <strong>Skip Header Row</strong>
                      <div class="form-text">First row contains column headers</div>
                    </label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing" value="1">
                    <label class="form-check-label" for="update_existing">
                      <strong>Update Existing Rooms</strong>
                      <div class="form-text">Update rooms with matching numbers</div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-upload"></i> Import Rooms
                </button>
                <a href="{{ route('tenant.rooms.index', ['property_id' => current_property()->id]) }}" 
                   class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Back to Rooms
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Instructions & Template -->
      <div class="col-md-4">
        <!-- CSV Format Instructions -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-info-circle"></i> CSV Format
            </h3>
          </div>
          <div class="card-body">
            <p class="mb-3">Your CSV file should contain the following columns:</p>
            
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Column</th>
                    <th>Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><code>number</code></td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>name</code></td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>short_code</code></td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>room_type_code</code></td>
                    <td><span class="badge bg-danger">Yes</span></td>
                  </tr>
                  <tr>
                    <td><code>floor</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>legacy_room_code</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>description</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>web_description</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>notes</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>is_enabled</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>is_featured</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                  <tr>
                    <td><code>display_order</code></td>
                    <td><span class="badge bg-secondary">No</span></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="alert alert-info mt-3">
              <strong>Note:</strong> The <code>room_type_code</code> should match the legacy code of existing room types in your system.
            </div>
          </div>
        </div>

        <!-- Download Template -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-download"></i> Sample Template
            </h3>
          </div>
          <div class="card-body">
            <p>Download a sample CSV template to get started:</p>
            <a href="{{ route('tenant.rooms.template') }}" class="btn btn-outline-primary">
              <i class="bi bi-file-earmark-arrow-down"></i> Download Template
            </a>
          </div>
        </div>

        <!-- Import Tips -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-lightbulb"></i> Import Tips
            </h3>
          </div>
          <div class="card-body">
            <ul class="mb-0">
              <li>Ensure room numbers are unique</li>
              <li>Room type codes must exist in your system</li>
              <li>Boolean fields accept: <code>1/0</code>, <code>true/false</code>, <code>yes/no</code></li>
              <li>Empty cells will use default values</li>
              <li>Backup your data before importing</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // File input validation
  document.getElementById('csv_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const fileSize = file.size / 1024 / 1024; // Convert to MB
      const maxSize = 5; // 5MB limit
      
      if (fileSize > maxSize) {
        alert(`File size (${fileSize.toFixed(2)}MB) exceeds the maximum allowed size of ${maxSize}MB.`);
        this.value = '';
        return;
      }
      
      if (!file.name.toLowerCase().endsWith('.csv')) {
        alert('Please select a CSV file.');
        this.value = '';
        return;
      }
      
      console.log('CSV file selected:', file.name, `(${fileSize.toFixed(2)}MB)`);
    }
  });
});
</script>

@endsection