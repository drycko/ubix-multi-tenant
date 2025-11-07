@extends('tenant.layouts.auth')

@section('title', 'Change Password')

@section('content')
<div class="text-center mb-4">
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Password Change Required</strong>
        <p class="mb-0 mt-2">For security reasons, you must change your password before continuing.</p>
    </div>
</div>

<form method="POST" action="{{ route('tenant.password.change.store') }}">
    @csrf

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Current Password -->
    <div class="mb-3">
        <label for="current_password" class="form-label">Current Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="current_password" type="password" 
                   class="form-control @error('current_password') is-invalid @enderror" 
                   name="current_password" required autocomplete="current-password"
                   placeholder="Enter your current password">
            <button type="button" class="input-group-text" onclick="togglePassword('current_password', this)">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
        @error('current_password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-key"></i></span>
            <input id="password" type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="new-password"
                   placeholder="Enter your new password">
            <button type="button" class="input-group-text" onclick="togglePassword('password', this)">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
        <small class="text-muted">
            Password must be at least 8 characters with uppercase, lowercase, numbers, and symbols.
        </small>
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
            <input id="password_confirmation" type="password" 
                   class="form-control" 
                   name="password_confirmation" required autocomplete="new-password"
                   placeholder="Confirm your new password">
            <button type="button" class="input-group-text" onclick="togglePassword('password_confirmation', this)">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <!-- Password Strength Indicator -->
    <div class="mb-3">
        <div class="password-strength">
            <div class="progress" style="height: 5px;">
                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
            <small id="password-strength-text" class="text-muted"></small>
        </div>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-auth w-100">
            <i class="fas fa-sync-alt me-2"></i>Change Password
        </button>
    </div>

    <div class="text-center">
        <form method="POST" action="{{ route('tenant.logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-link text-decoration-none">
                <i class="fas fa-sign-out-alt me-1"></i>Sign Out
            </button>
        </form>
    </div>
</form>

<script>
    function togglePassword(inputId, button) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = button.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        }
    }

    // Password strength checker
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 20;
        if (password.length >= 12) strength += 10;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 40) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Weak password';
            strengthText.className = 'text-danger';
        } else if (strength < 70) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Medium password';
            strengthText.className = 'text-warning';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Strong password';
            strengthText.className = 'text-success';
        }
    });
</script>
@endsection
