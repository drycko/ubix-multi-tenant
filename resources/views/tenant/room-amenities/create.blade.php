@extends('tenant.layouts.app')

@section('title', 'Create Room Amenity')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h4 class="mb-0">Create Room Amenity</h4>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.room-amenities.index') }}">Room Amenities</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
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
    
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-star"></i> New Room Amenity
            </h5>
          </div>
          <div class="card-body">
            <form action="{{ route('tenant.room-amenities.store') }}" method="POST">
              @csrf
              <input type="hidden" name="property_id" value="{{ $propertyId }}">

              <!-- Basic Information -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-info-circle"></i> Basic Information
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Amenity Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" 
                         id="name" name="name" value="{{ old('name') }}" 
                         placeholder="e.g., Ensuite Bathroom" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                         id="slug" name="slug" value="{{ old('slug') }}" 
                         placeholder="e.g., ensuite_bathroom" required>
                  <div class="form-text">Lowercase letters, numbers, and underscores only. Must be unique.</div>
                  @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="3" 
                            placeholder="Brief description of the amenity...">{{ old('description') }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Icon Selection -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-palette"></i> Icon Selection
                  </h6>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="icon" class="form-label">Bootstrap Icon Class <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                         id="icon" name="icon" value="{{ old('icon') }}" 
                         placeholder="e.g., bi bi-door-open" required>
                  <div class="form-text">Visit <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> to find icon classes</div>
                  @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Icon Preview</label>
                  <div class="border rounded p-3 text-center">
                    <i id="icon-preview" class="fs-1 text-success"></i>
                    <div class="mt-2">
                      <small class="text-muted" id="icon-class-display">Enter icon class to see preview</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Common Icons -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted border-bottom pb-2 mb-3">
                    <i class="bi bi-grid"></i> Quick Select Common Icons
                  </h6>
                </div>
                
                <div class="col-12">
                  <div class="row g-2">
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-door-open">
                        <i class="bi bi-door-open"></i><br><small>Door Open</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-tv">
                        <i class="bi bi-tv"></i><br><small>Television</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-droplet">
                        <i class="bi bi-droplet"></i><br><small>Bathroom</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-fire">
                        <i class="bi bi-fire"></i><br><small>Fireplace</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-snow">
                        <i class="bi bi-snow"></i><br><small>Air Con</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-cup-hot">
                        <i class="bi bi-cup-hot"></i><br><small>Tea/Coffee</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-wifi">
                        <i class="bi bi-wifi"></i><br><small>WiFi</small>
                      </button>
                    </div>
                    <div class="col-6 col-md-3">
                      <button type="button" class="btn btn-outline-secondary w-100 icon-select-btn" data-icon="bi bi-tree">
                        <i class="bi bi-tree"></i><br><small>Garden View</small>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit Buttons -->
              <div class="row">
                <div class="col-12">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                      <i class="bi bi-check-circle"></i> Create Amenity
                    </button>
                    <a href="{{ route('tenant.room-amenities.index', ['property_id' => $propertyId]) }}" 
                       class="btn btn-outline-secondary">
                      <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- @push('scripts') --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    const iconClassDisplay = document.getElementById('icon-class-display');
    const iconSelectBtns = document.querySelectorAll('.icon-select-btn');

    // Auto-generate slug from name
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.value === generateSlug(nameInput.value)) {
            slugInput.value = generateSlug(this.value);
        }
    });

    function generateSlug(text) {
        return text.toLowerCase()
                   .replace(/[^a-z0-9\s]/g, '')
                   .replace(/\s+/g, '_')
                   .replace(/^_+|_+$/g, '');
    }

    // Icon preview
    iconInput.addEventListener('input', function() {
        updateIconPreview(this.value);
    });

    function updateIconPreview(iconClass) {
        if (iconClass) {
            iconPreview.className = iconClass + ' fs-1 text-success';
            iconClassDisplay.textContent = iconClass;
        } else {
            iconPreview.className = 'fs-1 text-muted';
            iconClassDisplay.textContent = 'Enter icon class to see preview';
        }
    }

    // Quick select icons
    iconSelectBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const iconClass = this.dataset.icon;
            iconInput.value = iconClass;
            updateIconPreview(iconClass);
            
            // Remove active class from all buttons
            iconSelectBtns.forEach(b => b.classList.remove('btn-primary'));
            iconSelectBtns.forEach(b => b.classList.add('btn-outline-secondary'));
            
            // Add active class to clicked button
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary');
        });
    });

    // Initial icon preview if value exists
    if (iconInput.value) {
        updateIconPreview(iconInput.value);
    }
});
</script>
{{-- @endpush --}}
@endsection