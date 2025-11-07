@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<form method="POST" action="{{ route('tenant.password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                   name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" autofocus
                   placeholder="Enter your email">
        </div>
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="new-password"
                   placeholder="Enter your new password">
            <button type="button" class="input-group-text" onclick="togglePassword('password')">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password_confirmation" type="password" class="form-control" 
                   name="password_confirmation" required autocomplete="new-password"
                   placeholder="Confirm your new password">
            <button type="button" class="input-group-text" onclick="togglePassword('password_confirmation')">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-auth w-100">
            <i class="fas fa-key me-2"></i>Reset Password
        </button>
    </div>
</form>

<script>
    function togglePassword(id) {
        const passwordInput = document.getElementById(id);
        const eyeIcon = passwordInput.nextElementSibling.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    }
</script>
@endsection