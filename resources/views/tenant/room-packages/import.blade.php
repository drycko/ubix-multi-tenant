@extends('tenant.layouts.app')

@section('title', 'Import Packages')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-upload"></i>
          <small class="text-muted">Import Packages</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-packages.index') }}">Room Packages</a></li>
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
        <ul class="mb-0">
          @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row">
      <!-- Main Import Form -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-file-earmark-spreadsheet"></i> Upload CSV File
            </h3>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.room-packages.import.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              
              <div class="mb-4">
                <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                <input type="file" 
                       class="form-control @error('csv_file') is-invalid @enderror" 
                       id="csv_file" 
                       name="csv_file" 
                       accept=".csv,.txt"
                       required>
                @error('csv_file')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Please select a CSV file containing package data (Max: 2MB)</div>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('tenant.room-packages.index') }}" 
                   class="btn btn-outline-secondary me-md-2">
                  <i class="bi bi-arrow-left"></i> Back to Packages
                </a>
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-upload"></i> Import Packages
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Instructions Sidebar (make all content collapsible, collapsed by default) -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-info-circle"></i> Import Instructions
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#importInstructions" aria-expanded="false" aria-controls="importInstructions">
                <i class="bi bi-arrows-collapse"></i>
              </button>
            </div>
          </div>
          <div class="card-body collapse" id="importInstructions">
            <h6>CSV Format Requirements:</h6>
            <p class="small text-muted mb-3">Your CSV file should contain the following columns in this exact order:</p>
            
            <ol class="small">
              <li><strong>pkg_id</strong> - Package ID (optional)</li>
              <li><strong>pkg_name</strong> - Package Name (required)</li>
              <li><strong>pkg_sub_title</strong> - Sub Title (optional)</li>
              <li><strong>pkg_description</strong> - Description (optional)</li>
              <li><strong>pkg_number_of_nights</strong> - Number of nights (required)</li>
              <li><strong>pkg_checkin_days</strong> - Check-in days (JSON format)</li>
              <li><strong>pkg_status</strong> - Status (active/inactive)</li>
              <li><strong>pkg_enterby</strong> - Created by (optional)</li>
              <li><strong>deleted</strong> - Deleted flag (optional)</li>
              <li><strong>pkg_image</strong> - Image filename (optional)</li>
            </ol>

            <hr>

            <h6>Status Mapping:</h6>
            <ul class="small mb-3">
              <li><code>available</code> → <span class="badge bg-success">active</span></li>
              <li><code>unavailable</code> → <span class="badge bg-secondary">inactive</span></li>
              <li><code>active</code> → <span class="badge bg-success">active</span></li>
              <li><code>inactive</code> → <span class="badge bg-secondary">inactive</span></li>
            </ul>

            <hr>

            <h6>Sample CSV Data:</h6>
            <div class="bg-light p-2 rounded small">
              <code>
                pkg_id,pkg_name,pkg_sub_title,pkg_description,pkg_number_of_nights,pkg_checkin_days,pkg_status,pkg_enterby,deleted,pkg_image<br>
                1,"Weekend Getaway","2 Night Escape","Perfect weekend package",2,"[""Friday"",""Saturday""]",active,admin,0,weekend.jpg<br>
                2,"Family Package","3 Night Family Fun","Great for families",3,"[""Friday""]",active,admin,0,family.jpg
              </code>
            </div>

            <div class="alert alert-warning mt-3">
              <small>
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Important:</strong> Make sure your CSV file includes the header row and follows the exact column order shown above.
              </small>
            </div>
          </div>
        </div>

        <!-- Download Template -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-download"></i> Download Template
            </h3>
          </div>
          <div class="card-body">
            <p class="small text-muted">Download a sample CSV template to get started:</p>
            <button type="button" class="btn btn-outline-primary w-100" id="downloadTemplate">
              <i class="bi bi-file-earmark-arrow-down"></i> Download CSV Template
            </button>
          </div>
        </div>

        <!-- Tips -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-lightbulb"></i> Tips
            </h3>
          </div>
          <div class="card-body">
            <ul class="small mb-0">
              <li>Ensure all required fields are filled</li>
              <li>Use proper JSON format for check-in days</li>
              <li>Keep file size under 2MB</li>
              <li>Use UTF-8 encoding for special characters</li>
              <li>Test with a small file first</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Download CSV template functionality
    document.getElementById('downloadTemplate').addEventListener('click', function() {
        const csvContent = [
            'pkg_id,pkg_name,pkg_sub_title,pkg_description,pkg_number_of_nights,pkg_checkin_days,pkg_status,pkg_enterby,deleted,pkg_image',
            '1,"Weekend Getaway","2 Night Escape","Perfect weekend package for couples",2,"[""Friday"",""Saturday""]",active,admin,0,weekend.jpg',
            '2,"Family Package","3 Night Family Fun","Great package for families with children",3,"[""Friday""]",active,admin,0,family.jpg',
            '3,"Business Trip","1 Night Stay","Quick overnight stay for business travelers",1,"[""Monday"",""Tuesday"",""Wednesday"",""Thursday""]",active,admin,0,business.jpg'
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'packages_template.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // File input validation
    document.getElementById('csv_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // Convert to MB
            const fileName = file.name.toLowerCase();
            
            // Check file size
            if (fileSize > 2) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            // Check file extension
            if (!fileName.endsWith('.csv') && !fileName.endsWith('.txt')) {
                alert('Please select a CSV or TXT file');
                this.value = '';
                return;
            }
            
            console.log('File selected:', file.name, 'Size:', fileSize.toFixed(2) + 'MB');
        }
    });
});
</script>
@endsection

{{-- @push('scripts') --}}
{{-- @endpush --}}