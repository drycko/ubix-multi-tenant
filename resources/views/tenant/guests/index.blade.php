@extends('tenant.layouts.app')

@section('title', 'Guests Management')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-users"></i>
          <small class="text-muted">Guests Management</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Guests</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
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
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Action Bar --}}
    <div class="row mb-3">
      <div class="col-md-6">
        <a href="{{ route('tenant.guests.create') }}" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add New Guest
        </a>
      </div>
      <div class="col-md-6">
        <!-- Quick Stats -->
        <div class="d-flex justify-content-end">
          <div class="text-center me-3">
            <div class="fw-bold">{{ $guests->total() }}</div>
            <small class="text-muted">Total Guests</small>
          </div>
          <div class="text-center">
            <div class="fw-bold text-success">{{ $guests->where('is_active', true)->count() }}</div>
            <small class="text-muted">Active</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('tenant.guests.index') }}" class="row g-3">
          <div class="col-md-4">
            <label for="search" class="form-label">Search</label>
            <input type="text" 
                   name="search" 
                   id="search" 
                   class="form-control" 
                   value="{{ request('search') }}" 
                   placeholder="Name, email, phone, ID...">
          </div>
          
          <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
              <option value="">All Status</option>
              <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
          </div>
          
          <div class="col-md-2">
            <label for="nationality" class="form-label">Nationality</label>
            <select name="nationality" id="nationality" class="form-select">
              <option value="">All Nationalities</option>
              @foreach($nationalities as $nationality)
                <option value="{{ $nationality }}" {{ request('nationality') === $nationality ? 'selected' : '' }}>
                  {{ $nationality }}
                </option>
              @endforeach
            </select>
          </div>
          
          <div class="col-md-2">
            <label for="guest_club" class="form-label">Guest Club</label>
            <select name="guest_club" id="guest_club" class="form-select">
              <option value="">All Clubs</option>
              @foreach($guestClubs as $club)
                <option value="{{ $club->id }}" {{ request('guest_club') == $club->id ? 'selected' : '' }}>
                  {{ $club->name }}
                </option>
              @endforeach
            </select>
          </div>
          
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <div class="d-flex gap-1">
              <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-search"></i>
              </button>
              <a href="{{ route('tenant.guests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Guests Table --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list"></i> Guests List
        </h3>
        <div class="card-tools">
          <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
              <i class="fas fa-sort"></i> Sort
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'first_name', 'sort_direction' => 'asc']) }}">Name A-Z</a></li>
              <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'first_name', 'sort_direction' => 'desc']) }}">Name Z-A</a></li>
              <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => 'desc']) }}">Newest First</a></li>
              <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => 'asc']) }}">Oldest First</a></li>
              <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'nationality', 'sort_direction' => 'asc']) }}">Nationality</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        @if($guests->count() > 0)
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-light">
              <tr>
                <th>Guest</th>
                <th>Contact</th>
                <th>Nationality</th>
                <th>Bookings</th>
                <th>Last Visit</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($guests as $guest)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3">
                      {{ strtoupper(substr($guest->first_name, 0, 1) . substr($guest->last_name, 0, 1)) }}
                    </div>
                    <div>
                      <div class="fw-bold">{{ $guest->full_name }}</div>
                      @if($guest->id_number)
                      <small class="text-muted">ID: {{ $guest->id_number }}</small>
                      @endif
                    </div>
                  </div>
                </td>
                <td>
                  <div>
                    <i class="fas fa-envelope text-muted"></i> {{ $guest->email }}
                  </div>
                  <div>
                    <i class="fas fa-phone text-muted"></i> {{ $guest->phone }}
                  </div>
                </td>
                <td>
                  <span class="badge bg-info">{{ $guest->nationality ?: 'N/A' }}</span>
                </td>
                <td>
                  <div class="text-center">
                    <span class="badge bg-primary rounded-pill">{{ $guest->total_bookings ?? 0 }}</span>
                  </div>
                </td>
                <td>
                  @if($guest->last_booking_date)
                    <div>{{ \Carbon\Carbon::parse($guest->last_booking_date)->format('M j, Y') }}</div>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($guest->last_booking_date)->diffForHumans() }}</small>
                  @else
                    <span class="text-muted">Never</span>
                  @endif
                </td>
                <td>
                  <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           {{ $guest->is_active ? 'checked' : '' }}
                           onchange="toggleGuestStatus({{ $guest->id }})"
                           id="status{{ $guest->id }}">
                    <label class="form-check-label" for="status{{ $guest->id }}">
                      <span class="badge bg-{{ $guest->is_active ? 'success' : 'secondary' }}">
                        {{ $guest->is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </label>
                  </div>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('tenant.guests.show', $guest) }}" 
                       class="btn btn-outline-primary" 
                       title="View Guest">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('tenant.guests.edit', $guest) }}" 
                       class="btn btn-outline-warning" 
                       title="Edit Guest">
                      <i class="fas fa-edit"></i>
                    </a>
                    <div class="btn-group btn-group-sm">
                      <button type="button" 
                              class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                              data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('tenant.guests.bookings', $guest) }}">
                          <i class="fas fa-bed"></i> View Bookings
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('tenant.guests.invoices', $guest) }}">
                          <i class="fas fa-file-invoice"></i> View Invoices
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('tenant.guests.payments', $guest) }}">
                          <i class="fas fa-credit-card"></i> View Payments
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <form action="{{ route('tenant.guests.destroy', $guest) }}" 
                                method="POST" 
                                onsubmit="return confirm('Are you sure you want to delete this guest?')"
                                style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                              <i class="fas fa-trash"></i> Delete Guest
                            </button>
                          </form>
                        </li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Pagination links --}}
        {{-- Beautiful pagination --}}
        @if($guests->hasPages())
        <div class="container-fluid py-3">
          <div class="row align-items-center">
              <div class="col-md-12 float-end">
                  {{ $guests->links('vendor.pagination.bootstrap-5') }}
              </div>
          </div>
        </div>
        @endif
        @else
        <div class="text-center py-5">
          <i class="fas fa-users fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No guests found</h5>
          <p class="text-muted">Start by adding your first guest to the system.</p>
          <a href="{{ route('tenant.guests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add First Guest
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

@push('styles')
<style>
.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(45deg, #3B82F6, #1D4ED8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
function toggleGuestStatus(guestId) {
  fetch(`/guests/${guestId}/toggle-status`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    },
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert('Failed to update guest status');
      // Revert checkbox
      document.getElementById(`status${guestId}`).checked = !document.getElementById(`status${guestId}`).checked;
    }
  })
  .catch(error => {
    alert('Error updating guest status');
    // Revert checkbox
    document.getElementById(`status${guestId}`).checked = !document.getElementById(`status${guestId}`).checked;
  });
}
</script>
@endpush
@endsection