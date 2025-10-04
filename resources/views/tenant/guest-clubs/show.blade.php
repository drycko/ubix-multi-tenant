@extends('tenant.layouts.app')

@section('title', 'Guest Club - ' . $guestClub->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-crown"></i>
          Guest Club Details
          <small class="text-muted">{{ $guestClub->name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guest-clubs.index') }}">Guest Clubs</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $guestClub->name }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">

    <div class="row">
      <!-- Main Content -->
      <div class="col-md-8">
        <!-- Club Overview -->
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <div class="d-flex align-items-center">
                <div class="club-badge me-3" style="background-color: {{ $guestClub->badge_color }};">
                  @if($guestClub->icon)
                  <i class="{{ $guestClub->icon }}"></i>
                  @else
                  <span>{{ strtoupper(substr($guestClub->name, 0, 2)) }}</span>
                  @endif
                </div>
                <div>
                  <div class="fw-bold">{{ $guestClub->name }}</div>
                  @if($guestClub->tier_level)
                  <small class="text-muted">{{ $guestClub->tier_level }} Tier</small>
                  @endif
                </div>
              </div>
            </h5>
            <div class="d-flex gap-2">
              <span class="badge bg-{{ $guestClub->is_active ? 'success' : 'secondary' }}">
                {{ $guestClub->is_active ? 'Active' : 'Inactive' }}
              </span>
              @if($guestClub->tier_priority)
              <span class="badge bg-info">Priority: {{ $guestClub->tier_priority }}</span>
              @endif
            </div>
          </div>
          <div class="card-body">
            @if($guestClub->description)
            <p class="text-muted mb-0">{{ $guestClub->description }}</p>
            @else
            <p class="text-muted mb-0 fst-italic">No description provided.</p>
            @endif
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
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-muted">Minimum Bookings:</span>
                  <span class="fw-bold">{{ $guestClub->min_bookings ?? 'No requirement' }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-muted">Minimum Spend:</span>
                  <span class="fw-bold">
                    @if($guestClub->min_spend)
                    ${{ number_format($guestClub->min_spend, 2) }}
                    @else
                    No requirement
                    @endif
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Club Benefits -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-gift"></i> Member Benefits
            </h5>
          </div>
          <div class="card-body">
            @if($guestClub->benefits && count($guestClub->benefits) > 0)
            <div class="row">
              @foreach($guestClub->benefits as $key => $value)
                @if($value)
                <div class="col-md-6 mb-3">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <span>{{ $guestClub->formatBenefit($key, $value) }}</span>
                  </div>
                </div>
                @endif
              @endforeach
            </div>
            @else
            <p class="text-muted mb-0 fst-italic">No benefits configured yet.</p>
            @endif
          </div>
        </div>

        <!-- Recent Members -->
        <div class="card mt-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="fas fa-users"></i> Recent Members
            </h5>
            <a href="{{ route('tenant.guest-clubs.members', $guestClub) }}" class="btn btn-sm btn-outline-primary">
              View All Members
            </a>
          </div>
          <div class="card-body">
            @if($recentMembers && $recentMembers->count() > 0)
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Guest</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Total Bookings</th>
                    <th>Total Spend</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentMembers as $member)
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="me-2">
                          <i class="fas fa-user-circle text-muted"></i>
                        </div>
                        <div>
                          <div class="fw-medium">{{ $member->guest->first_name }} {{ $member->guest->last_name }}</div>
                          <small class="text-muted">{{ $member->guest->email }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <small>{{ $member->joined_at->format('M j, Y') }}</small>
                    </td>
                    <td>
                      <span class="badge bg-{{ $member->status === 'active' ? 'success' : ($member->status === 'suspended' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($member->status) }}
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark">{{ $member->guest->bookings_count ?? 0 }}</span>
                    </td>
                    <td>
                      <span class="text-success fw-medium">
                        ${{ number_format($member->guest->total_spend ?? 0, 2) }}
                      </span>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center py-4">
              <i class="fas fa-users text-muted fa-3x mb-3"></i>
              <h6 class="text-muted">No Members Yet</h6>
              <p class="text-muted mb-0">This club doesn't have any members yet.</p>
            </div>
            @endif
          </div>
        </div>
      </div>
      
      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-bar"></i> Club Statistics
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <div class="border-end">
                  <h4 class="text-primary mb-1">{{ $guestClub->members_count ?? 0 }}</h4>
                  <small class="text-muted">Total Members</small>
                </div>
              </div>
              <div class="col-6">
                <h4 class="text-success mb-1">{{ $guestClub->active_members_count ?? 0 }}</h4>
                <small class="text-muted">Active Members</small>
              </div>
            </div>
            
            <hr class="my-3">
            
            <div class="row text-center">
              <div class="col-12 mb-2">
                <h5 class="text-info mb-1">${{ number_format($guestClub->total_member_spend ?? 0, 2) }}</h5>
                <small class="text-muted">Total Member Spend</small>
              </div>
            </div>
            
            @if($guestClub->created_at)
            <hr class="my-3">
            <div class="text-center">
              <small class="text-muted">
                <i class="fas fa-calendar"></i>
                Created {{ $guestClub->created_at->format('M j, Y') }}
              </small>
            </div>
            @endif
          </div>
        </div>
        
        <!-- Actions -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-cog"></i> Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.guest-clubs.edit', $guestClub) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Club
              </a>
              
              <a href="{{ route('tenant.guest-clubs.members', $guestClub) }}" class="btn btn-outline-info">
                <i class="fas fa-users"></i> Manage Members
              </a>
              
              @if($guestClub->is_active)
              <form method="POST" action="{{ route('tenant.guest-clubs.toggle-status', $guestClub) }}" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-warning w-100" 
                        onclick="return confirm('Are you sure you want to deactivate this club?')">
                  <i class="fas fa-pause"></i> Deactivate Club
                </button>
              </form>
              @else
              <form method="POST" action="{{ route('tenant.guest-clubs.toggle-status', $guestClub) }}" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-success w-100">
                  <i class="fas fa-play"></i> Activate Club
                </button>
              </form>
              @endif
              
              <hr>
              
              <form method="POST" action="{{ route('tenant.guest-clubs.destroy', $guestClub) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100" 
                        onclick="return confirm('Are you sure you want to delete this club? This action cannot be undone.')">
                  <i class="fas fa-trash"></i> Delete Club
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Member Eligibility Check (hide for now) -->
        <div hidden class="card mt-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-search"></i> Check Guest Eligibility
            </h5>
          </div>
          <div class="card-body">
            <form id="eligibility-form">
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Guest email" id="guest-email">
                <button type="submit" class="btn btn-outline-primary">
                  <i class="fas fa-search"></i>
                </button>
              </div>
              <small class="text-muted">Check if a guest qualifies for this club</small>
            </form>
            <div id="eligibility-result" class="mt-3" style="display: none;"></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!--end::App Content-->

@endsection

@push('styles')
<style>
.club-badge {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 14px;
}

.benefit-item {
  padding: 0.5rem;
  background-color: #f8f9fa;
  border-radius: 0.375rem;
  margin-bottom: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const eligibilityForm = document.getElementById('eligibility-form');
  const eligibilityResult = document.getElementById('eligibility-result');
  
  eligibilityForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('guest-email').value;
    if (!email) {
      showEligibilityResult('Please enter a guest email address.', 'warning');
      return;
    }
    
    // Show loading
    showEligibilityResult('Checking eligibility...', 'info');
    
    // Simulate API call (you can implement actual AJAX call here)
    setTimeout(() => {
      // This is a placeholder - implement actual eligibility check
      showEligibilityResult(
        `<strong>Guest Found:</strong> ${email}<br>
         <span class="text-success">✓ Qualifies for this club</span><br>
         <small class="text-muted">Total bookings: 5 | Total spend: $2,450</small>`,
        'success'
      );
    }, 1000);
  });
  
  function showEligibilityResult(message, type) {
    eligibilityResult.innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
    eligibilityResult.style.display = 'block';
  }
});
</script>
@endpush