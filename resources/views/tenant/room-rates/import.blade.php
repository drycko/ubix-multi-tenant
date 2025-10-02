@extends('tenant.layouts.app')

@section('title', 'Import Room Rates')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">Import Room Rates</h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-rates.index') }}">Room Rates</a></li>
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
    
    <div class="row">
      <div class="col-lg-6 mx-auto">
        
        <!-- Import Instructions -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-info-circle"></i> Import Instructions
            </h5>
            
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#importInstructions" aria-expanded="false" aria-controls="importInstructions">
                <i class="bi bi-arrows-collapse"></i>
              </button>
            </div>
          </div>
          <div class="card-body collapse" id="importInstructions">
            <div class="alert alert-info">
              <h6><i class="bi bi-lightbulb"></i> CSV Format Requirements</h6>
              <p class="mb-3">Your CSV file should have the following format:</p>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead class="table-dark">
                    <tr>
                      <th>TR</th>
                      <th>RMCODE</th>
                      <th>RMTYPE</th>
                      <th>DATE</th>
                      <th>RATEA</th>
                      <th>RATEB</th>
                      <th>RATEC</th>
                      <th>RATED</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1</td>
                      <td>2</td>
                      <td>ST</td>
                      <td>05/09/2025</td>
                      <td>2800</td>
                      <td>2350</td>
                      <td>2250</td>
                      <td>1900</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div class="row mt-3">
                <div class="col-md-6">
                  <h6>Column Definitions:</h6>
                  <ul class="mb-0">
                    <li><strong>TR:</strong> Transaction/Row number</li>
                    <li><strong>RMCODE:</strong> Room code</li>
                    <li><strong>RMTYPE:</strong> Room type legacy code</li>
                    <li><strong>DATE:</strong> Effective date (DD/MM/YYYY)</li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6>Rate Columns:</h6>
                  <ul class="mb-0">
                    <li><strong>RATEA:</strong> Standard single rate</li>
                    <li><strong>RATEB:</strong> Standard shared rate</li>
                    <li><strong>RATEC:</strong> Off-season single rate</li>
                    <li><strong>RATED:</strong> Off-season shared rate</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <div class="alert alert-warning">
              <h6><i class="bi bi-exclamation-triangle"></i> Important Notes</h6>
              <ul class="mb-0">
                <li>Date format must be DD/MM/YYYY (e.g., 05/09/2025)</li>
                <li>Room types must exist with matching legacy codes</li>
                <li>Rates will be created/updated based on room type, date, and rate type</li>
                <li>Existing rates with same criteria will be updated</li>
                <li>Maximum file size: 2MB</li>
                <li>Supported formats: CSV, TXT</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mx-auto">

        <!-- Upload Form -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-upload"></i> Upload CSV File
            </h5>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.room-rates.import.post') }}" method="POST" enctype="multipart/form-data">
              @csrf
              
              <div class="mb-4">
                <label for="csv_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                <input type="file" class="form-control @error('csv_file') is-invalid @enderror" 
                       id="csv_file" name="csv_file" accept=".csv,.txt" required>
                @error('csv_file')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Select a CSV file containing room rate data</div>
              </div>

              <!-- Progress indicator (hidden by default) -->
              <div id="upload-progress" class="progress mb-3" style="display: none;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%">
                  <span class="sr-only">0% Complete</span>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success" id="import-btn">
                  <i class="bi bi-upload"></i> Import Room Rates
                </button>
                <a href="{{ route('tenant.room-rates.index') }}" class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Cancel
                </a>
              </div>
            </form>
          </div>
        </div>

        <!-- Sample Data Download -->
        <div class="card mt-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-download"></i> Sample Data
            </h5>
          </div>
          <div class="card-body">
            <p>Download a sample CSV file to use as a template:</p>
            <a href="#" class="btn btn-outline-info" id="download-sample">
              <i class="bi bi-file-earmark-arrow-down"></i> Download Sample CSV
            </a>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

{{-- @push('scripts') --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const importBtn = document.getElementById('import-btn');
    const progressBar = document.getElementById('upload-progress');
    const fileInput = document.getElementById('csv_file');

    // Show progress on form submit
    form.addEventListener('submit', function() {
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Importing...';
        progressBar.style.display = 'block';
        
        // Simulate progress (since we can't track real progress with standard form submit)
        let progress = 0;
        const interval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            
            const progressBarEl = progressBar.querySelector('.progress-bar');
            progressBarEl.style.width = progress + '%';
            progressBarEl.querySelector('.sr-only').textContent = Math.round(progress) + '% Complete';
        }, 200);
        
        // Clear interval after 10 seconds (fallback)
        setTimeout(() => clearInterval(interval), 10000);
    });

    // File validation
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            const allowedTypes = ['text/csv', 'text/plain'];
            if (!allowedTypes.includes(file.type) && !file.name.endsWith('.csv')) {
                alert('Please select a CSV file');
                this.value = '';
                return;
            }
        }
    });

    // Download sample CSV
    document.getElementById('download-sample').addEventListener('click', function(e) {
        e.preventDefault();
        
        const csvContent = 'TR,RMCODE,RMTYPE,DATE,RATEA,RATEB,RATEC,RATED\n' +
                          '1,2,ST,05/09/2025,2800,2350,2250,1900\n' +
                          '2,3,ST,05/09/2025,2800,2350,2250,1900\n' +
                          '3,4,ST,05/09/2025,2800,2350,2250,1900';
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'sample_room_rates.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
});
</script>
{{-- @endpush --}}
@endsection