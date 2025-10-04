@extends('tenant.layouts.app')

@section('title', 'Create Guest Club')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-crown"></i>
          Create Guest Club
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guest-clubs.index') }}">Guest Clubs</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('tenant.guest-clubs.store') }}">
      @csrf
      
      <div class="row">
        <!-- Basic Information -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-info-circle"></i> Basic Information
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Club Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Tier Level</label>
                    <input type="text" 
                           class="form-control @error('tier_level') is-invalid @enderror" 
                           name="tier_level" 
                           value="{{ old('tier_level') }}" 
                           placeholder="e.g., Bronze, Silver, Gold, VIP">
                    @error('tier_level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          name="description" 
                          rows="3" 
                          placeholder="Describe the guest club and its purpose">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Tier Priority</label>
                    <input type="number" 
                           class="form-control @error('tier_priority') is-invalid @enderror" 
                           name="tier_priority" 
                           value="{{ old('tier_priority', 0) }}" 
                           min="0" 
                           max="100"
                           placeholder="0-100">
                    <small class="text-muted">Higher numbers = higher priority</small>
                    @error('tier_priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Badge Color</label>
                    <input type="color" 
                           class="form-control form-control-color @error('badge_color') is-invalid @enderror" 
                           name="badge_color" 
                           value="{{ old('badge_color', '#3B82F6') }}">
                    @error('badge_color')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Icon (FontAwesome)</label>
                    <input type="text" 
                           class="form-control @error('icon') is-invalid @enderror" 
                           name="icon" 
                           value="{{ old('icon') }}" 
                           placeholder="fas fa-crown">
                    <small class="text-muted">Optional FontAwesome class</small>
                    @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Membership Requirements -->
          <div class="card mt-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-user-check"></i> Membership Requirements
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Minimum Bookings</label>
                    <input type="number" 
                           class="form-control @error('min_bookings') is-invalid @enderror" 
                           name="min_bookings" 
                           value="{{ old('min_bookings', 0) }}" 
                           min="0">
                    <small class="text-muted">Required confirmed bookings to join</small>
                    @error('min_bookings')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Minimum Spend</label>
                    <input type="number" 
                           class="form-control @error('min_spend') is-invalid @enderror" 
                           name="min_spend" 
                           value="{{ old('min_spend', 0) }}" 
                           min="0" 
                           step="0.01">
                    <small class="text-muted">Required total spend to join</small>
                    @error('min_spend')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Club Benefits -->
          <div class="card mt-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-gift"></i> Club Benefits
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Discount Benefits -->
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Booking Discount (%)</label>
                    <input type="number" 
                           class="form-control @error('benefits.discount_percentage') is-invalid @enderror" 
                           name="benefits[discount_percentage]" 
                           value="{{ old('benefits.discount_percentage') }}" 
                           min="0" 
                           max="100" 
                           step="0.1">
                    @error('benefits.discount_percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Spa Discount (%)</label>
                    <input type="number" 
                           class="form-control @error('benefits.spa_discount') is-invalid @enderror" 
                           name="benefits[spa_discount]" 
                           value="{{ old('benefits.spa_discount') }}" 
                           min="0" 
                           max="100" 
                           step="0.1">
                    @error('benefits.spa_discount')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Restaurant Discount (%)</label>
                    <input type="number" 
                           class="form-control @error('benefits.restaurant_discount') is-invalid @enderror" 
                           name="benefits[restaurant_discount]" 
                           value="{{ old('benefits.restaurant_discount') }}" 
                           min="0" 
                           max="100" 
                           step="0.1">
                    @error('benefits.restaurant_discount')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              
              <!-- Boolean Benefits -->
              <div class="row">
                <div class="col-md-4">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[late_checkout]" 
                           value="1" 
                           id="late_checkout"
                           {{ old('benefits.late_checkout') ? 'checked' : '' }}>
                    <label class="form-check-label" for="late_checkout">
                      Late Checkout
                    </label>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[early_checkin]" 
                           value="1" 
                           id="early_checkin"
                           {{ old('benefits.early_checkin') ? 'checked' : '' }}>
                    <label class="form-check-label" for="early_checkin">
                      Early Check-in
                    </label>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[complimentary_wifi]" 
                           value="1" 
                           id="complimentary_wifi"
                           {{ old('benefits.complimentary_wifi') ? 'checked' : '' }}>
                    <label class="form-check-label" for="complimentary_wifi">
                      Complimentary WiFi
                    </label>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[complimentary_breakfast]" 
                           value="1" 
                           id="complimentary_breakfast"
                           {{ old('benefits.complimentary_breakfast') ? 'checked' : '' }}>
                    <label class="form-check-label" for="complimentary_breakfast">
                      Complimentary Breakfast
                    </label>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[room_upgrade]" 
                           value="1" 
                           id="room_upgrade"
                           {{ old('benefits.room_upgrade') ? 'checked' : '' }}>
                    <label class="form-check-label" for="room_upgrade">
                      Priority Room Upgrades
                    </label>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[airport_shuttle]" 
                           value="1" 
                           id="airport_shuttle"
                           {{ old('benefits.airport_shuttle') ? 'checked' : '' }}>
                    <label class="form-check-label" for="airport_shuttle">
                      Airport Shuttle
                    </label>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[priority_booking]" 
                           value="1" 
                           id="priority_booking"
                           {{ old('benefits.priority_booking') ? 'checked' : '' }}>
                    <label class="form-check-label" for="priority_booking">
                      Priority Booking Access
                    </label>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="benefits[concierge_service]" 
                           value="1" 
                           id="concierge_service"
                           {{ old('benefits.concierge_service') ? 'checked' : '' }}>
                    <label class="form-check-label" for="concierge_service">
                      Dedicated Concierge
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-cog"></i> Settings
              </h5>
            </div>
            <div class="card-body">
              <div class="form-check mb-3">
                <input class="form-check-input" 
                       type="checkbox" 
                       name="is_active" 
                       value="1" 
                       id="is_active"
                       {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  <strong>Active Club</strong>
                  <br><small class="text-muted">Members can join and use benefits</small>
                </label>
              </div>
            </div>
          </div>
          
          <!-- Preview Card -->
          <div class="card mt-3">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="fas fa-eye"></i> Preview
              </h5>
            </div>
            <div class="card-body text-center">
              <div id="preview-badge" class="club-badge mx-auto mb-3" style="background-color: #3B82F6;">
                <i id="preview-icon" class="fas fa-crown" style="display: none;"></i>
                <span id="preview-text">GC</span>
              </div>
              <h6 id="preview-name">Guest Club</h6>
              <small id="preview-tier" class="text-muted"></small>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="card mt-3">
            <div class="card-body">
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Create Guest Club
                </button>
                <a href="{{ route('tenant.guest-clubs.index') }}" class="btn btn-outline-secondary">
                  <i class="fas fa-times"></i> Cancel
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>

  </div>
</div>
<!--end::App Content-->

@endsection

@push('styles')
<style>
.club-badge {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 16px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const nameInput = document.querySelector('input[name="name"]');
  const tierInput = document.querySelector('input[name="tier_level"]');
  const colorInput = document.querySelector('input[name="badge_color"]');
  const iconInput = document.querySelector('input[name="icon"]');
  
  const previewBadge = document.getElementById('preview-badge');
  const previewIcon = document.getElementById('preview-icon');
  const previewText = document.getElementById('preview-text');
  const previewName = document.getElementById('preview-name');
  const previewTier = document.getElementById('preview-tier');
  
  function updatePreview() {
    // Update name
    previewName.textContent = nameInput.value || 'Guest Club';
    
    // Update tier
    previewTier.textContent = tierInput.value ? `${tierInput.value} Tier` : '';
    
    // Update color
    previewBadge.style.backgroundColor = colorInput.value;
    
    // Update icon or text
    if (iconInput.value.trim()) {
      previewIcon.className = iconInput.value;
      previewIcon.style.display = 'block';
      previewText.style.display = 'none';
    } else {
      previewIcon.style.display = 'none';
      previewText.style.display = 'block';
      const name = nameInput.value || 'Guest Club';
      previewText.textContent = name.substring(0, 2).toUpperCase();
    }
  }
  
  // Add event listeners
  nameInput.addEventListener('input', updatePreview);
  tierInput.addEventListener('input', updatePreview);
  colorInput.addEventListener('input', updatePreview);
  iconInput.addEventListener('input', updatePreview);
  
  // Initial update
  updatePreview();
});
</script>
@endpush