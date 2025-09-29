@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<form method="POST" action="{{ route('tenant.password.email') }}">
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
        <button type="submit" class="btn btn-auth w-100">
            <i class="fas fa-paper-plane me-2"></i>Send Password Reset Link
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('tenant.login') }}" class="text-decoration-none">
            Back to Login
        </a>
    </div>
</form>
@endsection