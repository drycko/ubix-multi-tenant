@extends('tenant.layouts.app')

@section('title', session('error') ? 'Access Restricted' : 'Error Page')

@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ session('error') ? 'Access Restricted' : '404 Error Page' }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ session('error') ? 'Access Restricted' : '404 Error Page' }}</li>
                </ol>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">

  <!-- Main content -->
  <section class="content">
    <div class="error-page">
      @if(session('error'))
        <!-- Subscription/Access Error -->
        <h2 class="headline text-danger"><i class="fas fa-ban"></i></h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Access Restricted</h3>

          <div class="alert alert-danger">
            {{ session('error') }}
          </div>

          <p>
            Your account access has been restricted due to subscription issues.
            Please contact your administrator or visit the billing portal to resolve this.
          </p>

          <div class="mt-4">
            <a href="https://nexusflow.ubix.co.za/p/dashboard" class="btn btn-success" target="_blank">
              <i class="fas fa-credit-card"></i> Manage Subscription
            </a>
            <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary">
              <i class="fas fa-home"></i> Try Dashboard
            </a>
            <a href="{{ route('tenant.logout') }}" class="btn btn-secondary" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('tenant.logout') }}" method="POST" class="d-none">
              @csrf
            </form>
          </div>
        </div>
      @else
        <!-- 404 Error -->
        <h2 class="headline text-warning"> 404</h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Page not found.</h3>

          <p>
            We could not find the page you were looking for.
            Meanwhile, you may <a href="{{ route('tenant.dashboard') }}">return to dashboard</a> or try using the search form.
          </p>
        </div>
      @endif
      <!-- /.error-content -->
    </div>
    <!-- /.error-page -->
  </section>
  <!-- /.content -->
</div>
@endsection