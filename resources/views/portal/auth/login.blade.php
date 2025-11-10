<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>{{ config('app.name', 'Ubix') }} - Tenant Portal Login</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Figtree', sans-serif;
      /* background: white; */
      background: linear-gradient(135deg, #ffffff 0%, #9f9f9f 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    a {
      color: #349e1d!important;
    }
    .login-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      overflow: hidden;
      max-width: 450px;
      width: 100%;
      margin: 0 auto;
    }
    .login-header {
      background: linear-gradient(135deg, #66eaa6 0%, #349e1d 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    .login-header i {
      font-size: 3rem;
      margin-bottom: 1rem;
    }
    .login-body {
      padding: 2rem;
    }
    .form-control:focus {
      border-color: #66eaa6;
      box-shadow: 0 0 0 0.2rem rgba(102, 234, 166, 0.25);
    }
    .btn-login {
      background: linear-gradient(135deg, #66eaa6 0%, #349e1d 100%);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
    }
    .btn-login:hover {
      opacity: 0.9;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 234, 166, 0.4);
    }
    .input-group-text {
      background-color: #f8f9fa;
      border-right: none;
    }
    .form-control {
      border-left: none;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <i class="fas fa-building"></i>
      <h3 class="mb-1">Tenant Portal</h3>
      <p class="mb-0 opacity-75">Manage your subscription and billing</p>
    </div>
    
    <div class="login-body">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif
      
      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif
      
      <form method="POST" action="{{ route('portal.login') }}">
        @csrf
        
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-envelope"></i>
            </span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
            name="email" value="{{ old('email') }}" required autofocus>
          </div>
          @error('email')
          <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-lock"></i>
            </span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
            name="password" required>
          </div>
          @error('password')
          <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="remember" name="remember">
          <label class="form-check-label" for="remember">
            Remember me
          </label>
        </div>
        
        <button type="submit" class="btn btn-success btn-login w-100 mb-3">
          <i class="fas fa-sign-in-alt me-2"></i> Login
        </button>
        
        <div class="text-center">
          <a href="{{ route('portal.password.request') }}" class="text-decoration-none">
            <i class="fas fa-question-circle me-1"></i> Forgot your password?
          </a>
        </div>
        
        <hr class="my-4">
        
        <div class="text-center text-muted small">
          <p class="mb-1">Need help? Contact your administrator</p>
          <a href="{{ route('home') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Back to main site
          </a>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
