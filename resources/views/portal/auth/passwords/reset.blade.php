<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>{{ config('app.name', 'Ubix') }} - Reset Password</title>
  
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Figtree', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .reset-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      overflow: hidden;
      max-width: 500px;
      width: 100%;
      margin: 0 auto;
    }
    .reset-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    .reset-header i {
      font-size: 3rem;
      margin-bottom: 1rem;
    }
    .reset-body {
      padding: 2rem;
    }
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-reset {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
    }
    .btn-reset:hover {
      opacity: 0.9;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
  <div class="reset-card">
    <div class="reset-header">
      <i class="fas fa-lock"></i>
      <h3 class="mb-1">Set New Password</h3>
      <p class="mb-0 opacity-75">Enter your new password below</p>
    </div>
    
    <div class="reset-body">
      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>There were some problems:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif
      
      <form method="POST" action="{{ route('portal.password.update') }}">
        @csrf
        
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-envelope"></i>
            </span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
            name="email" value="{{ $email ?? old('email') }}" required autofocus>
          </div>
          @error('email')
          <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">New Password</label>
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
          <small class="text-muted">Minimum 8 characters</small>
        </div>
        
        <div class="mb-4">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-lock"></i>
            </span>
            <input id="password_confirmation" type="password" class="form-control" 
            name="password_confirmation" required>
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-reset w-100 mb-3">
          <i class="fas fa-check me-2"></i> Reset Password
        </button>
        
        <div class="text-center">
          <a href="{{ route('portal.login') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Back to login
          </a>
        </div>
      </form>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
