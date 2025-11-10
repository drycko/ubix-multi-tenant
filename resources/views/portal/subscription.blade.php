@extends('portal.layouts.app')

@section('page-title', 'Manage Subscription')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <!-- Current Subscription -->
    <div class="ghost-card mb-4">
      <div class="ghost-card-header success">
        <div class="ghost-card-icon">
          <i class="fas fa-credit-card"></i>
        </div>
        <div>
          <h5 class="mb-0">Current Subscription</h5>
        </div>
      </div>
      <div class="ghost-card-body">
        @if($currentSubscription)
        <div class="row">
          <div class="col-md-6 mb-3">
            <h6 class="text-muted small mb-2">
              <i class="fas fa-box text-primary me-2"></i>PLAN NAME
            </h6>
            <h4 class="mb-0">{{ $currentSubscription->plan->name }}</h4>
          </div>
          <div class="col-md-6 mb-3">
            <h6 class="text-muted small mb-2">
              <i class="fas fa-dollar-sign text-success me-2"></i>PRICE
            </h6>
            <h4 class="mb-0">
              {{ $currency }} {{ number_format($currentSubscription->price, 2) }}
              <small class="text-muted">/ {{ ucfirst($currentSubscription->billing_cycle) }}</small>
            </h4>
          </div>
        </div>
        
        <hr>
        
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-calendar-check text-info me-2"></i>Start Date
          </dt>
          <dd class="col-sm-8">{{ $currentSubscription->start_date->format('F d, Y') }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-calendar-times text-warning me-2"></i>End Date
          </dt>
          <dd class="col-sm-8">{{ $currentSubscription->end_date->format('F d, Y') }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-sync text-secondary me-2"></i>Billing Cycle
          </dt>
          <dd class="col-sm-8">{{ ucfirst($currentSubscription->billing_cycle) }}</dd>
          
          <dt class="col-sm-4 text-muted">
            <i class="fas fa-info-circle text-primary me-2"></i>Status
          </dt>
          <dd class="col-sm-8">
            @if($currentSubscription->status === 'active')
            <span class="badge bg-success">
              <i class="fas fa-check-circle"></i> Active
            </span>
            @else
            <span class="badge bg-warning">
              <i class="fas fa-exclamation-circle"></i> {{ ucfirst($currentSubscription->status) }}
            </span>
            @endif
          </dd>
        </dl>
        
        @if($currentSubscription->plan->description)
        <div class="alert alert-light mt-3 mb-0">
          <i class="fas fa-info-circle me-2"></i>
          {{ $currentSubscription->plan->description }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
          <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3 mb-0">No active subscription found.</p>
          <p class="text-muted">Choose a plan below to get started.</p>
        </div>
        @endif
      </div>
    </div>
    
    <!-- Available Plans -->
    <div class="ghost-card">
      <div class="ghost-card-header primary">
        <div class="ghost-card-icon">
          <i class="fas fa-crown"></i>
        </div>
        <div>
          <h5 class="mb-0">Available Plans</h5>
          <p class="mb-0 opacity-75 small">Choose the plan that fits your needs</p>
        </div>
      </div>
      <div class="ghost-card-body">
        <div class="row">
          @forelse($availablePlans as $plan)
          <div class="col-md-6 mb-4">
            <div class="card h-100 {{ $currentSubscription && $currentSubscription->subscription_plan_id == $plan->id ? 'border-success' : '' }}">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h5 class="card-title mb-1">{{ $plan->name }}</h5>
                    @if($currentSubscription && $currentSubscription->subscription_plan_id == $plan->id)
                    <span class="badge bg-success">Current Plan</span>
                    @endif
                  </div>
                  <div class="text-end">
                    <h4 class="mb-0 text-primary">{{ $currency }} {{ number_format($plan->monthly_price, 2) }}</h4>
                    <small class="text-muted">per month</small>
                  </div>
                </div>
                
                @if($plan->description)
                <p class="text-muted small">{{ $plan->description }}</p>
                @endif
                
                @if($plan->features)
                <ul class="list-unstyled mb-3">
                  @foreach((array)$plan->features as $feature)
                  <li class="mb-2">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    {{ is_string($feature) ? trim($feature) : $feature }}
                  </li>
                  @endforeach
                </ul>
                @endif
                
                <hr>
                
                <div class="mb-3">
                  <small class="text-muted d-block mb-2">
                    <i class="fas fa-tag me-1"></i>
                    Yearly: {{ $currency }} {{ number_format($plan->yearly_price, 2) }}
                    <span class="badge bg-success ms-1">Save {{ round((1 - ($plan->yearly_price / ($plan->monthly_price * 12))) * 100) }}%</span>
                  </small>
                </div>
                
                @if(!$currentSubscription || $currentSubscription->subscription_plan_id != $plan->id)
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#upgradePlanModal{{ $plan->id }}">
                  <i class="fas fa-arrow-up me-2"></i>
                  @if($currentSubscription)
                  Switch to this Plan
                  @else
                  Subscribe Now
                  @endif
                </button>
                @else
                <button type="button" class="btn btn-outline-success w-100" disabled>
                  <i class="fas fa-check me-2"></i>Your Current Plan
                </button>
                @endif
              </div>
            </div>
            
            <!-- Upgrade Modal -->
            <div class="modal fade" id="upgradePlanModal{{ $plan->id }}" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">
                      <i class="fas fa-arrow-up me-2"></i>
                      @if($currentSubscription)
                      Switch to {{ $plan->name }}
                      @else
                      Subscribe to {{ $plan->name }}
                      @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <form action="{{ route('portal.subscription.upgrade') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    
                    <div class="modal-body">
                      <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Choose your billing cycle:</strong>
                      </div>
                      
                      <div class="form-check mb-3 p-3 border rounded">
                        <input class="form-check-input" type="radio" name="billing_cycle" id="monthly{{ $plan->id }}" value="monthly" checked>
                        <label class="form-check-label w-100" for="monthly{{ $plan->id }}">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <strong>Monthly Billing</strong>
                              <p class="mb-0 text-muted small">Billed every month</p>
                            </div>
                            <div class="text-end">
                              <strong class="text-primary">{{ $currency }} {{ number_format($plan->monthly_price, 2) }}</strong>
                              <p class="mb-0 text-muted small">per month</p>
                            </div>
                          </div>
                        </label>
                      </div>
                      
                      <div class="form-check p-3 border rounded">
                        <input class="form-check-input" type="radio" name="billing_cycle" id="yearly{{ $plan->id }}" value="yearly">
                        <label class="form-check-label w-100" for="yearly{{ $plan->id }}">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <strong>Yearly Billing</strong>
                              <span class="badge bg-success ms-2">Save {{ round((1 - ($plan->yearly_price / ($plan->monthly_price * 12))) * 100) }}%</span>
                              <p class="mb-0 text-muted small">Billed once per year</p>
                            </div>
                            <div class="text-end">
                              <strong class="text-success">{{ $currency }} {{ number_format($plan->yearly_price, 2) }}</strong>
                              <p class="mb-0 text-muted small">per year</p>
                            </div>
                          </div>
                        </label>
                      </div>
                      
                      @if($currentSubscription)
                      <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Your current subscription will remain active until payment is confirmed for the new plan.
                      </div>
                      @endif
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                      </button>
                      <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>Proceed to Payment
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          @empty
          <div class="col-12">
            <div class="text-center py-5">
              <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
              <p class="text-muted mt-3 mb-0">No subscription plans available at this time.</p>
            </div>
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
  
  <!-- Sidebar -->
  <div class="col-lg-4">
    <!-- Quick Stats -->
    <div class="ghost-card mb-4">
      <div class="ghost-card-header info">
        <div class="ghost-card-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div>
          <h6 class="mb-0">Quick Stats</h6>
        </div>
      </div>
      <div class="ghost-card-body">
        <div class="mb-3">
          <small class="text-muted">Current Plan</small>
          <h5 class="mb-0">{{ $currentSubscription->plan->name ?? 'No Active Plan' }}</h5>
        </div>
        
        @if($currentSubscription)
        <div class="mb-3">
          <small class="text-muted">Next Billing Date</small>
          <h5 class="mb-0">{{ $currentSubscription->end_date->format('M d, Y') }}</h5>
        </div>
        
        <div class="mb-3">
          <small class="text-muted">Days Remaining</small>
          <h5 class="mb-0">
            @if($currentSubscription->end_date->isFuture())
            {{ intval(now()->diffInDays($currentSubscription->end_date, false)) }} days
            @else
            <span class="text-danger">Expired</span>
            @endif
          </h5>
        </div>
        
        <div>
          <small class="text-muted">Monthly Cost</small>
          <h5 class="mb-0 text-success">{{ $currency }} {{ number_format($currentSubscription->price, 2) }}</h5>
        </div>
        @endif
      </div>
    </div>
    
    <!-- Need Help? -->
    <div class="ghost-card">
      <div class="ghost-card-header warning">
        <div class="ghost-card-icon">
          <i class="fas fa-question-circle"></i>
        </div>
        <div>
          <h6 class="mb-0">Need Help?</h6>
        </div>
      </div>
      <div class="ghost-card-body">
        <p class="text-muted small mb-3">
          Have questions about our plans or need assistance with your subscription?
        </p>
        <a href="mailto:support@{{ config('app.domain', 'ubix.com') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
          <i class="fas fa-envelope me-2"></i>Contact Support
        </a>
        <a href="{{ route('portal.invoices') }}" class="btn btn-outline-secondary btn-sm w-100">
          <i class="fas fa-file-invoice me-2"></i>View Invoices
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
