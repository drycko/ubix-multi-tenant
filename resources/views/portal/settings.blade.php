@extends('portal.layouts.app')

@section('page-title', 'Account Settings')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Profile Information -->
        <div class="ghost-card mb-4">
            <div class="ghost-card-header primary">
                <div class="ghost-card-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div>
                    <h5 class="mb-0">Profile Information</h5>
                    <p class="mb-0 opacity-75 small">Update your account details</p>
                </div>
            </div>
            <div class="ghost-card-body">
                <form action="{{ route('portal.settings.update') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user text-primary me-2"></i>Full Name
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope text-info me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone text-success me-2"></i>Phone Number
                            </label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $admin->phone) }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">
                                <i class="fas fa-building text-warning me-2"></i>Company Name
                            </label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" value="{{ old('company_name', $admin->company_name) }}">
                            @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>Address
                        </label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3">{{ old('address', $admin->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="ghost-card">
            <div class="ghost-card-header warning">
                <div class="ghost-card-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h5 class="mb-0">Change Password</h5>
                    <p class="mb-0 opacity-75 small">Update your account password</p>
                </div>
            </div>
            <div class="ghost-card-body">
                <form action="{{ route('portal.password.change') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock text-secondary me-2"></i>Current Password
                        </label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock text-primary me-2"></i>New Password
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required minlength="8">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock text-success me-2"></i>Confirm New Password
                            </label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required minlength="8">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Password Requirements:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Minimum 8 characters</li>
                            <li>Mix of uppercase and lowercase letters recommended</li>
                            <li>Include numbers and special characters for better security</li>
                        </ul>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Account Overview -->
        <div class="ghost-card mb-4">
            <div class="ghost-card-header info">
                <div class="ghost-card-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h6 class="mb-0">Account Overview</h6>
                </div>
            </div>
            <div class="ghost-card-body">
                <dl class="row mb-0">
                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-building text-primary me-2"></i>Organization
                    </dt>
                    <dd class="col-12 mb-3">
                        <strong>{{ $tenant->name }}</strong>
                    </dd>

                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-envelope text-info me-2"></i>Contact Email
                    </dt>
                    <dd class="col-12 mb-3">
                        {{ $tenant->email }}
                    </dd>

                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-phone text-success me-2"></i>Contact Number
                    </dt>
                    <dd class="col-12 mb-3">
                        {{ $tenant->contact_number ?? 'Not provided' }}
                    </dd>

                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-calendar text-warning me-2"></i>Member Since
                    </dt>
                    <dd class="col-12 mb-0">
                        {{ $tenant->created_at->format('F Y') }}
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Permissions -->
        <div class="ghost-card mb-4">
            <div class="ghost-card-header success">
                <div class="ghost-card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h6 class="mb-0">Your Permissions</h6>
                </div>
            </div>
            <div class="ghost-card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        @if($admin->can_manage_billing)
                            <i class="fas fa-check-circle text-success me-2"></i>
                        @else
                            <i class="fas fa-times-circle text-danger me-2"></i>
                        @endif
                        Manage Billing
                    </li>
                    <li class="mb-2">
                        @if($admin->can_manage_users)
                            <i class="fas fa-check-circle text-success me-2"></i>
                        @else
                            <i class="fas fa-times-circle text-danger me-2"></i>
                        @endif
                        Manage Users
                    </li>
                    <li class="mb-0">
                        @if($admin->can_manage_settings)
                            <i class="fas fa-check-circle text-success me-2"></i>
                        @else
                            <i class="fas fa-times-circle text-danger me-2"></i>
                        @endif
                        Manage Settings
                    </li>
                </ul>
            </div>
        </div>

        <!-- Activity -->
        <div class="ghost-card">
            <div class="ghost-card-header warning">
                <div class="ghost-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h6 class="mb-0">Recent Activity</h6>
                </div>
            </div>
            <div class="ghost-card-body">
                <dl class="row mb-0">
                    @if($admin->last_login_at)
                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-sign-in-alt text-success me-2"></i>Last Login
                    </dt>
                    <dd class="col-12 mb-3">
                        {{ $admin->last_login_at->diffForHumans() }}
                        <br>
                        <small class="text-muted">{{ $admin->last_login_at->format('M d, Y h:i A') }}</small>
                    </dd>
                    @endif

                    @if($admin->last_login_ip)
                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-network-wired text-info me-2"></i>Last IP Address
                    </dt>
                    <dd class="col-12 mb-3">
                        <code>{{ $admin->last_login_ip }}</code>
                    </dd>
                    @endif

                    <dt class="col-12 text-muted small mb-2">
                        <i class="fas fa-user-clock text-warning me-2"></i>Account Created
                    </dt>
                    <dd class="col-12 mb-0">
                        {{ $admin->created_at->format('M d, Y') }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
