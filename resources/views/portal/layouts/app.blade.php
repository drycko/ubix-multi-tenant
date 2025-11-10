<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>{{ config('app.name', 'Ubix') }} - Billing Portal</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}"/>
  {{-- bootstrap icons --}}
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/images/site.webmanifest') }}">
  <style>
    body {
      font-family: 'Figtree', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      min-height: 100vh;
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    .sidebar .nav-link {
      color: rgba(255,255,255,0.8);
      padding: 0.75rem 1.5rem;
      border-left: 3px solid transparent;
      transition: all 0.3s;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      color: #fff;
      background-color: rgba(255,255,255,0.1);
      border-left-color: #3498db;
    }
    .sidebar .nav-link i {
      width: 20px;
      margin-right: 10px;
    }
    .portal-header {
      background: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.08);
      padding: 1rem 0;
    }
    .ghost-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: none;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }
    .ghost-card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .ghost-card-header.primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .ghost-card-header.success {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .ghost-card-header.warning {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .ghost-card-header.info {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .ghost-card-icon {
      width: 50px;
      height: 50px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
    .ghost-card-body {
      padding: 1.5rem;
    }
    .stat-card {
      text-align: center;
      padding: 1.5rem;
      border-radius: 10px;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .stat-icon {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    .stat-value {
      font-size: 1.75rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    .stat-label {
      color: #6c757d;
      font-size: 0.875rem;
    }
  </style>
  
  @stack('styles')
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 px-0 sidebar">
        <div class="text-center py-4">
          <h4 class="text-white mb-0">
            <i class="fas fa-building"></i> {{ config('app.name') }} Portal
          </h4>
          <p class="text-white-50 small mb-0">{{ auth('tenant_admin')->user()->tenant->name ?? 'Billing Portal' }}</p>
        </div>
        
        <nav class="nav flex-column">
          <a href="{{ route('portal.dashboard') }}" class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Dashboard
          </a>
          
          @if(auth('tenant_admin')->user()->canManageBilling())
          <a href="{{ route('portal.subscription') }}" class="nav-link {{ request()->routeIs('portal.subscription*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Subscription
          </a>
          
          <a href="{{ route('portal.invoices') }}" class="nav-link {{ request()->routeIs('portal.invoices*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Invoices
          </a>
          @endif
          
          <a href="{{ route('portal.settings') }}" class="nav-link {{ request()->routeIs('portal.settings*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Settings
          </a>
          
          <hr class="border-light my-3">
          
          <form action="{{ route('portal.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </nav>
      </div>
      
      <!-- Main Content -->
      <div class="col-md-9 col-lg-10 px-0">
        <!-- Header -->
        <div class="portal-header">
          <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
              <div>
                <span class="text-muted">{{ auth('tenant_admin')->user()->name }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Content -->
        <div class="container-fluid px-4 py-4">
          @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif
          
          @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif
          
          @if($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0 mt-2">
              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif
          
          @yield('content')
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
