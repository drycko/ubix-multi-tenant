<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'UBIX') }} - Property Management System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .landing-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .landing-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            text-align: center;
        }
        
        .logo {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .error-message {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .error-message i {
            font-size: 3rem;
            color: #ff9800;
            margin-bottom: 1rem;
        }
        
        .feature-list {
            text-align: left;
            margin: 2rem 0;
        }
        
        .feature-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .feature-item:last-child {
            border-bottom: none;
        }
        
        .feature-item i {
            color: #667eea;
            margin-right: 0.75rem;
            width: 20px;
        }
        
        .btn-custom {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 10px;
            margin: 0.5rem;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            background: white;
        }
        
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <div class="landing-card">
            <div class="logo">
                <i class="fas fa-building"></i> UBIX
            </div>
            
            <h1 class="mb-3">Welcome to UBIX Property Management</h1>
            <p class="subtitle">Your Complete Property & Booking Management Solution</p>
            
            @if(request()->has('error') && request()->get('error') === 'tenant_not_found')
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle d-block"></i>
                    <h4>Property Not Found</h4>
                    <p class="mb-0">The property domain you're trying to access doesn't exist or has been deactivated. Please check the URL or contact support.</p>
                </div>
            @endif
            
            <div class="feature-list">
                <div class="feature-item">
                    <i class="fas fa-calendar-check"></i>
                    <strong>Booking Management</strong> - Streamline reservations and guest check-ins
                </div>
                <div class="feature-item">
                    <i class="fas fa-users"></i>
                    <strong>Guest Portal</strong> - Self-service portal for your guests
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <strong>Analytics & Reports</strong> - Track performance and revenue
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt"></i>
                    <strong>Mobile Ready</strong> - Manage your property from anywhere
                </div>
                <div class="feature-item">
                    <i class="fas fa-lock"></i>
                    <strong>Secure & Reliable</strong> - Enterprise-grade security
                </div>
            </div>
            
            <div class="mt-4">
                <a href="https://nexusflow.ubix.co.za/p/register" class="btn btn-primary-custom btn-custom">
                    <i class="fas fa-rocket"></i> Get Started
                </a>
                <a href="https://nexusflow.ubix.co.za/p/login" class="btn btn-outline-custom btn-custom">
                    <i class="fas fa-sign-in-alt"></i> Login to Portal
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    Already have a property? Contact your administrator for access.
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
