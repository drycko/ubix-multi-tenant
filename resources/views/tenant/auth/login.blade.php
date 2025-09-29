@extends('tenant.layouts.auth')

@section('title', 'Tenant Login')

@section('content')
<form method="POST" action="{{ route('tenant.login') }}">
    @csrf

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
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

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                   placeholder="Enter your email">
        </div>
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="current-password"
                   placeholder="Enter your password">
            <button type="button" class="input-group-text" onclick="togglePassword(this)">
                <i class="fas fa-eye-slash" id="toggleIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">
                Remember Me
            </label>
        </div>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-auth w-100">
            <i class="fas fa-sign-in-alt me-2"></i>Sign In
        </button>
    </div>

    <div class="text-center">
        @if(Route::has('tenant.password.request'))
            <a href="{{ route('tenant.password.request') }}" class="text-decoration-none">
                Forgot Your Password?
            </a>
        @endif
    </div>
</form>

<script>
    function togglePassword(button) {
        const passwordInput = document.getElementById('password');
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
</script>
@endsection