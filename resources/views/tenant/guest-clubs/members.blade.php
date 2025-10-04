@extends('tenant.layouts.app')

@section('title', 'Club Members - ' . $guestClub->name)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-users"></i>
          Club Members
          <small class="text-muted">{{ $guestClub->name }}</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guest-clubs.index') }}">Guest Clubs</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.guest-clubs.show', $guestClub) }}">{{ $guestClub->name }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Members</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">

    <!-- Club Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="club-badge me-3" style="background-color: {{ $guestClub->badge_color }};">
                  @if($guestClub->icon)
                  <i class="{{ $guestClub->icon }}"></i>
                  @else
                  <span>{{ strtoupper(substr($guestClub->name, 0, 2)) }}</span>
                  @endif
                </div>
                <div>
                  <h5 class="mb-1">{{ $guestClub->name }}</h5>
                  <div class="text-muted">
                    @if($guestClub->tier_level)
                    {{ $guestClub->tier_level }} Tier • 
                    @endif
                    {{ $members->total() }} {{ Str::plural('member', $members->total()) }}
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2">
                <a href="{{ route('tenant.guest-clubs.show', $guestClub) }}" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left"></i> Back to Club
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                  <i class="fas fa-user-plus"></i> Add Member
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ route('tenant.guest-clubs.members', $guestClub) }}">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Search Members</label>
                  <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                         placeholder="Search by name or email...">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Status</label>
                  <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Sort By</label>
                  <select class="form-select" name="sort">
                    <option value="joined_desc" {{ request('sort') === 'joined_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="joined_asc" {{ request('sort') === 'joined_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                    <option value="spend_desc" {{ request('sort') === 'spend_desc' ? 'selected' : '' }}>Highest Spend</option>
                    <option value="bookings_desc" {{ request('sort') === 'bookings_desc' ? 'selected' : '' }}>Most Bookings</option>
                  </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <div class="d-grid w-100">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-search"></i> Filter
                    </button>
                  </div>
                </div>
              </div>
              @if(request()->hasAny(['search', 'status', 'sort']))
              <div class="mt-2">
                <a href="{{ route('tenant.guest-clubs.members', $guestClub) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-times"></i> Clear Filters
                </a>
              </div>
              @endif
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Members List -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              Club Members
              @if($members->total() > 0)
              <span class="badge bg-primary ms-2">{{ $members->total() }}</span>
              @endif
            </h5>
            
            @if($members->total() > 0)
            <div class="dropdown">
              <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i> Actions
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportMembers()">
                  <i class="fas fa-download"></i> Export Members
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="bulkAction('activate')">
                  <i class="fas fa-check"></i> Bulk Activate
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="bulkAction('suspend')">
                  <i class="fas fa-pause"></i> Bulk Suspend
                </a></li>
              </ul>
            </div>
            @endif
          </div>
          <div class="card-body p-0">
            @if($members->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th width="40">
                      <input type="checkbox" id="select-all" class="form-check-input">
                    </th>
                    <th>Guest</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Bookings</th>
                    <th>Total Spend</th>
                    <th>Benefits Used</th>
                    <th width="120">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($members as $member)
                  <tr>
                    <td>
                      <input type="checkbox" class="form-check-input member-checkbox" value="{{ $member->id }}">
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                          <div class="avatar-title bg-light text-muted rounded-circle">
                            {{ strtoupper(substr($member->guest->first_name, 0, 1) . substr($member->guest->last_name, 0, 1)) }}
                          </div>
                        </div>
                        <div>
                          <div class="fw-medium">
                            {{ $member->guest->first_name }} {{ $member->guest->last_name }}
                          </div>
                          <small class="text-muted">{{ $member->guest->email }}</small>
                          @if($member->guest->phone)
                          <br><small class="text-muted">{{ $member->guest->phone }}</small>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td>
                      <div>{{ $member->joined_at->format('M j, Y') }}</div>
                      <small class="text-muted">{{ $member->joined_at->diffForHumans() }}</small>
                    </td>
                    <td>
                      <span class="badge bg-{{ $member->status === 'active' ? 'success' : ($member->status === 'suspended' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($member->status) }}
                      </span>
                      @if($member->canReceiveBenefits())
                      <br><small class="text-success"><i class="fas fa-check-circle"></i> Benefits Active</small>
                      @else
                      <br><small class="text-muted"><i class="fas fa-times-circle"></i> Benefits Inactive</small>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-light text-dark">{{ $member->guest->bookings_count ?? 0 }}</span>
                      @if($member->guest->confirmed_bookings_count)
                      <br><small class="text-success">{{ $member->guest->confirmed_bookings_count }} confirmed</small>
                      @endif
                    </td>
                    <td>
                      <span class="text-success fw-medium">
                        ${{ number_format($member->guest->total_spend ?? 0, 2) }}
                      </span>
                      @if($member->guest->last_booking_date)
                      <br><small class="text-muted">Last: {{ $member->guest->last_booking_date->format('M j') }}</small>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-info">{{ $member->benefits_used_count ?? 0 }}</span>
                      @if($member->last_benefit_used_at)
                      <br><small class="text-muted">{{ $member->last_benefit_used_at->format('M j') }}</small>
                      @endif
                    </td>
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                          <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="{{ route('tenant.guests.show', $member->guest) }}">
                            <i class="fas fa-eye"></i> View Guest
                          </a></li>
                          <li><hr class="dropdown-divider"></li>
                          @if($member->status === 'active')
                          <li><a class="dropdown-item" href="#" onclick="changeMemberStatus({{ $member->id }}, 'suspended')">
                            <i class="fas fa-pause text-warning"></i> Suspend
                          </a></li>
                          <li><a class="dropdown-item" href="#" onclick="changeMemberStatus({{ $member->id }}, 'inactive')">
                            <i class="fas fa-stop text-secondary"></i> Deactivate
                          </a></li>
                          @elseif($member->status === 'suspended')
                          <li><a class="dropdown-item" href="#" onclick="changeMemberStatus({{ $member->id }}, 'active')">
                            <i class="fas fa-play text-success"></i> Activate
                          </a></li>
                          <li><a class="dropdown-item" href="#" onclick="changeMemberStatus({{ $member->id }}, 'inactive')">
                            <i class="fas fa-stop text-secondary"></i> Deactivate
                          </a></li>
                          @else
                          <li><a class="dropdown-item" href="#" onclick="changeMemberStatus({{ $member->id }}, 'active')">
                            <i class="fas fa-play text-success"></i> Activate
                          </a></li>
                          @endif
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item text-danger" href="#" onclick="removeMember({{ $member->id }})">
                            <i class="fas fa-trash"></i> Remove from Club
                          </a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            
            @if($members->hasPages())
            <div class="card-footer">
              {{ $members->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-5">
              <i class="fas fa-users text-muted fa-4x mb-4"></i>
              <h5 class="text-muted">No Members Found</h5>
              @if(request()->hasAny(['search', 'status']))
              <p class="text-muted mb-3">Try adjusting your filters or search criteria.</p>
              <a href="{{ route('tenant.guest-clubs.members', $guestClub) }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear Filters
              </a>
              @else
              <p class="text-muted mb-3">This club doesn't have any members yet.</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="fas fa-user-plus"></i> Add First Member
              </button>
              @endif
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!--end::App Content-->

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Member to {{ $guestClub->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="addMemberForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Guest</label>
            <select class="form-control" name="guest_id" required>
              <option value="">Choose a guest...</option>
              @foreach($eligibleGuests ?? [] as $guest)
              <option value="{{ $guest->id }}">
                {{ $guest->first_name }} {{ $guest->last_name }} ({{ $guest->email }})
              </option>
              @endforeach
            </select>
            <small class="text-muted">Only guests who meet the club requirements are shown.</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Any notes about this membership..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Member</button>
        </div>
      </form>
    </div>
  </div>
</div>

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

.avatar-sm {
  width: 40px;
  height: 40px;
}

.avatar-title {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Select all functionality
  const selectAllCheckbox = document.getElementById('select-all');
  const memberCheckboxes = document.querySelectorAll('.member-checkbox');
  
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      memberCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
    });
  }
  
  // Update select all when individual checkboxes change
  memberCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      const checkedCount = document.querySelectorAll('.member-checkbox:checked').length;
      selectAllCheckbox.checked = checkedCount === memberCheckboxes.length;
      selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < memberCheckboxes.length;
    });
  });
  
  // Add member form
  const addMemberForm = document.getElementById('addMemberForm');
  if (addMemberForm) {
    addMemberForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      // Show loading state
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
      submitBtn.disabled = true;
      
      fetch(`{{ route('tenant.guest-clubs.add-member', $guestClub) }}`, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Simple modal close and success handling
          const modalElement = document.getElementById('addMemberModal');
          modalElement.style.display = 'none';
          modalElement.classList.remove('show');
          document.body.classList.remove('modal-open');
          
          // Remove backdrop
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
          
          // Show success message and reload
          alert('Member added successfully!');
          location.reload();
        } else {
          alert('Error adding member: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the member: ' + error.message);
      })
      .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    });
  }
});

function changeMemberStatus(memberId, status) {
  if (confirm(`Are you sure you want to change this member's status to ${status}?`)) {
    fetch(`{{ route('tenant.guest-clubs.change-member-status', $guestClub) }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        member_id: memberId,
        status: status
      })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while changing the member status: ' + error.message);
    });
  }
}

function removeMember(memberId) {
  if (confirm('Are you sure you want to remove this member from the club? This action cannot be undone.')) {
    fetch(`{{ route('tenant.guest-clubs.remove-member', $guestClub) }}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        member_id: memberId
      })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while removing the member: ' + error.message);
    });
  }
}

function bulkAction(action) {
  const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
  if (checkedBoxes.length === 0) {
    alert('Please select at least one member.');
    return;
  }
  
  const memberIds = Array.from(checkedBoxes).map(cb => cb.value);
  
  if (confirm(`Are you sure you want to ${action} ${memberIds.length} selected members?`)) {
    fetch(`{{ route('tenant.guest-clubs.bulk-action', $guestClub) }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        member_ids: memberIds,
        action: action
      })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while performing the bulk action: ' + error.message);
    });
  }
}

function exportMembers() {
  window.location.href = `{{ route('tenant.guest-clubs.export-members', $guestClub) }}`;
}
</script>
@endsection