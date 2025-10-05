@extends('tenant.layouts.app')

@section('title', 'Cleaning Schedule Calendar')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Cleaning Schedule Calendar</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.housekeeping.index') }}">Housekeeping</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.cleaning-schedule.index') }}">Cleaning Schedules</a></li>
          <li class="breadcrumb-item active" aria-current="page">Calendar</li>
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

    <!-- Property Selector -->
    @include('tenant.components.property-selector')
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Calendar Controls -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Calendar Navigation</h3>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-4">
                <div class="btn-group" role="group">
                  <a href="{{ route('tenant.cleaning-schedule.calendar', ['date' => $weekStart->copy()->subWeek()->format('Y-m-d'), 'property_id' => $propertyId]) }}" 
                     class="btn btn-outline-primary">
                    <i class="bi bi-chevron-left"></i> Previous Week
                  </a>
                  <a href="{{ route('tenant.cleaning-schedule.calendar', ['date' => today()->format('Y-m-d'), 'property_id' => $propertyId]) }}" 
                     class="btn btn-outline-secondary">
                    Today
                  </a>
                  <a href="{{ route('tenant.cleaning-schedule.calendar', ['date' => $weekStart->copy()->addWeek()->format('Y-m-d'), 'property_id' => $propertyId]) }}" 
                     class="btn btn-outline-primary">
                    Next Week <i class="bi bi-chevron-right"></i>
                  </a>
                </div>
              </div>
              <div class="col-md-4">
                <h5 class="text-center mb-0">
                  {{ $weekStart->format('M j') }} - {{ $weekEnd->format('M j, Y') }}
                </h5>
              </div>
              <div class="col-md-4">
                <form method="GET" action="{{ route('tenant.cleaning-schedule.calendar') }}" class="d-flex">
                  <select name="property_id" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">All Properties</option>
                    @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ $propertyId == $property->id ? 'selected' : '' }}>
                      {{ $property->name }}
                    </option>
                    @endforeach
                  </select>
                  <input type="hidden" name="date" value="{{ $viewDate->format('Y-m-d') }}">
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            <a href="{{ route('tenant.cleaning-schedule.index') }}" class="btn btn-outline-primary btn-sm me-2">
              <i class="bi bi-list-check"></i> View Checklists
            </a>
            <a href="{{ route('tenant.housekeeping.create') }}" class="btn btn-outline-success btn-sm">
              <i class="bi bi-plus-circle"></i> Create Task
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Weekly Calendar -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-calendar3"></i> Weekly Schedule
              @if($propertyId)
                <span class="badge bg-primary ms-2">
                  {{ $properties->find($propertyId)->name ?? 'Property' }}
                </span>
              @endif
            </h3>
            <div class="card-tools">
              <span class="badge bg-secondary">
                {{ collect($weeklyTasks)->flatten()->count() }} tasks this week
              </span>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              @for($i = 0; $i < 7; $i++)
                @php
                  $day = $weekStart->copy()->addDays($i);
                  $dayTasks = $weeklyTasks[$day->format('Y-m-d')] ?? collect();
                  $isToday = $day->isToday();
                  $isPast = $day->isPast() && !$isToday;
                @endphp
                <div class="col-md-1_7 mb-3" style="flex: 0 0 14.285714%; max-width: 14.285714%;">
                  <div class="card h-100 {{ $isToday ? 'border-primary' : ($isPast ? 'border-secondary' : '') }}">
                    <div class="card-header p-2 {{ $isToday ? 'bg-primary text-white' : ($isPast ? 'bg-transparent' : '') }}">
                      <h6 class="card-title mb-0 text-center">
                        <div>{{ $day->format('D') }}</div>
                        <div class="fw-bold">{{ $day->format('j') }}</div>
                      </h6>
                    </div>
                    <div class="card-body p-2" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                      @if($dayTasks->count() > 0)
                        @foreach($dayTasks as $task)
                        <div class="mb-2">
                          <div class="card border-{{ $task->status_color }} card-sm">
                            <div class="card-body p-2">
                              <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                  <h6 class="card-title mb-1" style="font-size: 0.75rem;">
                                    <a href="{{ route('tenant.housekeeping.show', $task) }}" 
                                       class="text-decoration-none text-dark">
                                      Room {{ $task->room->number }}
                                    </a>
                                  </h6>
                                  <p class="card-text mb-1" style="font-size: 0.7rem;">
                                    <span class="badge bg-{{ $task->status_color }} badge-sm">
                                      {{ ucfirst($task->status) }}
                                    </span>
                                  </p>
                                  <p class="card-text text-muted mb-1" style="font-size: 0.65rem;">
                                    <i class="bi bi-{{ 
                                      match($task->task_type) {
                                        'cleaning' => 'house-fill',
                                        'maintenance' => 'tools', 
                                        'inspection' => 'search',
                                        'deep_clean' => 'sparkles',
                                        'setup' => 'gear-fill',
                                        default => 'list-check'
                                      }
                                    }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $task->task_type)) }}
                                  </p>
                                  @if($task->assignedTo)
                                  <p class="card-text text-muted mb-0" style="font-size: 0.65rem;">
                                    <i class="bi bi-person"></i> {{ $task->assignedTo->name }}
                                  </p>
                                  @endif
                                  @if($task->scheduled_for)
                                  <p class="card-text text-muted mb-0" style="font-size: 0.65rem;">
                                    <i class="bi bi-clock"></i> {{ $task->scheduled_for->format('H:i') }}
                                  </p>
                                  @endif
                                </div>
                                <div class="dropdown">
                                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                          data-bs-toggle="dropdown" style="font-size: 0.7rem;">
                                    <i class="bi bi-three-dots"></i>
                                  </button>
                                  <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                      <a class="dropdown-item" href="{{ route('tenant.housekeeping.show', $task) }}">
                                        <i class="bi bi-eye"></i> View
                                      </a>
                                    </li>
                                    @can('edit housekeeping tasks')
                                    <li>
                                      <a class="dropdown-item" href="{{ route('tenant.housekeeping.edit', $task) }}">
                                        <i class="bi bi-pencil"></i> Edit
                                      </a>
                                    </li>
                                    @endcan
                                    @if($task->canStart())
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                      <form method="POST" action="{{ route('tenant.housekeeping.start', $task) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-success">
                                          <i class="bi bi-play-circle"></i> Start Task
                                        </button>
                                      </form>
                                    </li>
                                    @endif
                                  </ul>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        @endforeach
                      @else
                        <div class="text-center text-muted mt-4">
                          <i class="bi bi-calendar-x" style="font-size: 1.5rem;"></i>
                          <p class="small mt-2">No tasks scheduled</p>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @endfor
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Weekly Summary -->
    <div class="row mt-4">
      <div class="col-md-4">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ collect($weeklyTasks)->flatten()->count() }}</h4>
                <p class="mb-0">Total Tasks</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-list-check" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ collect($weeklyTasks)->flatten()->where('status', 'completed')->count() }}</h4>
                <p class="mb-0">Completed</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="mb-0">{{ collect($weeklyTasks)->flatten()->where('status', 'pending')->count() }}</h4>
                <p class="mb-0">Pending</p>
              </div>
              <div class="align-self-center">
                <i class="bi bi-clock" style="font-size: 2rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($staff->count() > 0)
    <!-- Staff Workload -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Staff Workload This Week</h3>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($staff as $member)
                @php
                  $memberTasks = collect($weeklyTasks)->flatten()->where('assigned_to', $member->id);
                @endphp
                <div class="col-md-3 mb-3">
                  <div class="card">
                    <div class="card-body">
                      <h6 class="card-title">{{ $member->name }}</h6>
                      <div class="d-flex justify-content-between">
                        <span class="badge bg-primary">{{ $memberTasks->count() }} tasks</span>
                        <span class="badge bg-success">{{ $memberTasks->where('status', 'completed')->count() }} done</span>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

@endsection

@push('styles')
<style>
.card-sm .card-body {
  padding: 0.5rem !important;
}

.badge-sm {
  font-size: 0.6rem !important;
}

/* Custom column width for 7-day layout */
.col-md-1_7 {
  position: relative;
  width: 100%;
  padding-right: 7.5px;
  padding-left: 7.5px;
}

@media (min-width: 768px) {
  .col-md-1_7 {
    flex: 0 0 14.285714%;
    max-width: 14.285714%;
  }
}

/* Ensure equal height columns */
.h-100 {
  height: 100% !important;
}

/* Task card hover effect */
.card-sm:hover {
  transform: translateY(-1px);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
  transition: all 0.15s ease-in-out;
}
</style>
@endpush