@extends('portal.layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="row mb-4">
  <!-- Subscription Status Card -->
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon text-primary">
        <i class="fas fa-credit-card"></i>
      </div>
      <div class="stat-value">
        @if($currentSubscription)
        <span class="badge bg-{{ $currentSubscription->status === 'active' ? 'success' : 'warning' }}">
          {{ ucfirst($currentSubscription->status) }}
        </span>
        @else
        <span class="badge bg-secondary">No Plan</span>
        @endif
      </div>
      <div class="stat-label">Subscription Status</div>
    </div>
  </div>
  
  <!-- Current Plan Card -->
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon text-success">
        <i class="fas fa-box"></i>
      </div>
      <div class="stat-value">{{ $currentSubscription->plan->name ?? 'N/A' }}</div>
      <div class="stat-label">Current Plan</div>
    </div>
  </div>
  
  <!-- Next Billing Card -->
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon text-warning">
        <i class="fas fa-calendar-alt"></i>
      </div>
      <div class="stat-value">
        @if($currentSubscription && $currentSubscription->end_date)
        {{ $currentSubscription->end_date->format('M d, Y') }}
        @else
        N/A
        @endif
      </div>
      <div class="stat-label">Next Billing</div>
    </div>
  </div>
  
  <!-- Monthly Cost Card -->
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon text-info">
        <i class="fas fa-dollar-sign"></i>
      </div>
      <div class="stat-value">
        @if($currentSubscription)
        {{ $currency }} {{ number_format($currentSubscription->price, 2) }}
        @else
        {{ $currency }} 0.00
        @endif
      </div>
      <div class="stat-label">
        @if($currentSubscription)
        {{ ucfirst($currentSubscription->billing_cycle) }}
        @else
        Monthly Cost
        @endif
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Tenant Information Card -->
  <div class="col-md-6">
    <div class="ghost-card">
      <div class="ghost-card-header primary">
        <div class="ghost-card-icon">
          <i class="fas fa-building"></i>
        </div>
        <div>
          <h5 class="mb-0">Organization Details</h5>
          <small>Your account information</small>
        </div>
      </div>
      <div class="ghost-card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-building me-2"></i>Name
          </dt>
          <dd class="col-sm-8">{{ $tenant->name }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-envelope me-2"></i>Email
          </dt>
          <dd class="col-sm-8">{{ $tenant->email }}</dd>
          
          @if($tenant->phone)
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-phone me-2"></i>Phone
          </dt>
          <dd class="col-sm-8">{{ $tenant->phone }}</dd>
          @endif
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-globe me-2"></i>Domain
          </dt>
          <dd class="col-sm-8">
            @if($tenant->domains->count() > 0)
            @foreach($tenant->domains as $domain)
            <a href="https://{{ $domain->domain }}" target="_blank" class="text-decoration-none">
              {{ $domain->domain }} <i class="fas fa-external-link-alt fa-xs"></i>
            </a>
            @if(!$loop->last)<br>@endif
            @endforeach
            @else
            <span class="text-muted">No domain configured</span>
            @endif
          </dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-calendar-plus me-2"></i>Member Since
          </dt>
          <dd class="col-sm-8 mb-0">{{ $tenant->created_at->format('F d, Y') }}</dd>
        </dl>
      </div>
    </div>
  </div>
  
  <!-- Subscription Details Card -->
  <div class="col-md-6">
    <div class="ghost-card">
      <div class="ghost-card-header success">
        <div class="ghost-card-icon">
          <i class="fas fa-credit-card"></i>
        </div>
        <div>
          <h5 class="mb-0">Subscription Details</h5>
          <small>Current plan and billing</small>
        </div>
      </div>
      <div class="ghost-card-body">
        @if($currentSubscription)
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-box me-2"></i>Plan
          </dt>
          <dd class="col-sm-8">{{ $currentSubscription->plan->name }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-dollar-sign me-2"></i>Price
          </dt>
          <dd class="col-sm-8">{{ $currency }} {{ number_format($currentSubscription->price, 2) }} / {{ $currentSubscription->billing_cycle }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-calendar-check me-2"></i>Started
          </dt>
          <dd class="col-sm-8">{{ $currentSubscription->start_date->format('F d, Y') }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-calendar-alt me-2"></i>Renews
          </dt>
          <dd class="col-sm-8 mb-0">{{ $currentSubscription->end_date->format('F d, Y') }}</dd>
        </dl>
        
        @if($admin->canManageBilling())
        <div class="mt-3 pt-3 border-top">
          <a href="{{ route('portal.subscription') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-up me-2"></i>Upgrade Plan
          </a>
        </div>
        @endif
        @else
        <div class="alert alert-warning mb-0">
          <i class="fas fa-exclamation-triangle me-2"></i>
          No active subscription. Please contact your administrator.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

@if($admin->canManageBilling())
<div class="row">
  <!-- Recent Invoices Card -->
  <div class="col-md-12">
    <div class="ghost-card">
      <div class="ghost-card-header info">
        <div class="ghost-card-icon">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
          <h5 class="mb-0">Recent Invoices</h5>
          <small>Your latest billing history</small>
        </div>
      </div>
      <div class="ghost-card-body">
        @if($recentInvoices->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recentInvoices as $invoice)
              <tr>
                <td>
                  <code>{{ $invoice->invoice_number }}</code>
                </td>
                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                <td><strong>{{ $currency }} {{ number_format($invoice->amount, 2) }}</strong></td>
                <td>
                  @if($invoice->status === 'paid')
                  <span class="badge bg-success">Paid</span>
                  @elseif($invoice->status === 'pending')
                  <span class="badge bg-warning">Pending</span>
                  @elseif($invoice->status === 'overdue')
                  <span class="badge bg-danger">Overdue</span>
                  @else
                  <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                  @endif
                </td>
                <td>
                  <a href="#" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        
        <div class="mt-3 pt-3 border-top text-end">
          <a href="{{ route('portal.invoices') }}" class="btn btn-outline-primary btn-sm">
            View All Invoices <i class="fas fa-arrow-right ms-2"></i>
          </a>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class="fas fa-info-circle me-2"></i>
          No invoices yet.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endif
@endsection
