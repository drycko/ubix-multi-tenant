<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubix Central - Login</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo h1 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .login-logo p {
            color: #7f8c8d;
            font-size: 1rem;
            margin: 0;
        }
        
        .form-floating .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 1rem 0.75rem;
        }
        
        .form-floating .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            color: #2d3436;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <h1><i class="bi bi-building-gear"></i> Ubix</h1>
            <div class="admin-badge">
                <i class="bi bi-shield-check"></i> Central Administration
            </div>
            <p>Platform Management Portal</p>
        </div>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Display success messages -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('central.login') }}">
            @csrf

            <div class="form-floating mb-3">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autocomplete="email" 
                       autofocus
                       placeholder="name@example.com">
                <label for="email"><i class="bi bi-envelope"></i> Email Address</label>
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       placeholder="Password">
                <label for="password"><i class="bi bi-lock"></i> Password</label>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" 
                       type="checkbox" 
                       name="remember" 
                       id="remember" 
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login text-white">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In to Central Portal
                </button>
            </div>
        </form>

        <div class="footer-text">
            <p><strong>Authorized Personnel Only</strong></p>
            <p>This is a restricted system for platform administrators.</p>
            <p class="mt-3">
                <small>
                    <i class="bi bi-shield-lock"></i> 
                    Secure connection established
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on email field
            document.getElementById('email').focus();
            
            // Add loading state to login button
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.btn-login');
            
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Signing In...';
                submitBtn.disabled = true;
            });
        });
        
        // Add spinning animation for loading
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>