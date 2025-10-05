@extends('tenant.layouts.app')

@section('title', 'Cleaning Checklist Details')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Cleaning Checklist Details</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.cleaning-schedule.index') }}">Cleaning Schedule</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $checklist->name }}</li>
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

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <!-- Checklist Details -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-clipboard-check"></i> {{ $checklist->name }}
              <span class="badge badge-{{ $checklist->checklist_type === 'standard' ? 'primary' : ($checklist->checklist_type === 'deep_clean' ? 'warning' : 'info') }} ms-2">
                {{ ucfirst(str_replace('_', ' ', $checklist->checklist_type)) }}
              </span>
            </h3>
            <div class="card-tools">
              <a href="{{ route('tenant.cleaning-schedule.edit', $checklist) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <a href="{{ route('tenant.cleaning-schedule.duplicate', $checklist) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-copy"></i> Duplicate
              </a>
            </div>
          </div>
          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-6">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><strong>Property:</strong></td>
                      <td>{{ $checklist->property->name }}</td>
                    </tr>
                    @if($checklist->roomType)
                    <tr>
                      <td><strong>Room Type:</strong></td>
                      <td>{{ $checklist->roomType->name }}</td>
                    </tr>
                    @endif
                    <tr>
                      <td><strong>Type:</strong></td>
                      <td>
                        <span class="badge badge-{{ $checklist->checklist_type === 'standard' ? 'primary' : ($checklist->checklist_type === 'deep_clean' ? 'warning' : 'info') }}">
                          {{ ucfirst(str_replace('_', ' ', $checklist->checklist_type)) }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Estimated Time:</strong></td>
                      <td>{{ $checklist->estimated_minutes ?? 'Not specified' }} {{ $checklist->estimated_minutes ? 'minutes' : '' }}</td>
                    </tr>
                    <tr>
                      <td><strong>Status:</strong></td>
                      <td>
                        <span class="badge badge-{{ $checklist->is_active ? 'success' : 'secondary' }}">
                          {{ $checklist->is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                @if($checklist->description)
                <h6>Description</h6>
                <p class="text-muted">{{ $checklist->description }}</p>
                @endif
              </div>
            </div>

            <!-- Checklist Items -->
            <h5><i class="bi bi-list-check"></i> Checklist Items ({{ count($checklist->items) }})</h5>
            @if(count($checklist->items) > 0)
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th width="50">#</th>
                    <th>Item</th>
                    <th width="100" class="text-center">Required</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($checklist->items as $index => $item)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['item'] }}</td>
                    <td class="text-center">
                      @if($item['required'] ?? false)
                        <span class="badge badge-danger">Required</span>
                      @else
                        <span class="badge badge-secondary">Optional</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> No checklist items defined.
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clock-history"></i> Recent Tasks</h3>
          </div>
          <div class="card-body">
            @if($recentTasks->count() > 0)
            <div class="timeline scrollable-content" style="height: 500px;">
              @foreach($recentTasks as $task)
              <div class="timeline-item mb-3">
                <div class="timeline-marker bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'info') }}"></div>
                <div class="timeline-content">
                  <h6 class="timeline-title">{{ $task->title }}</h6>
                  <p class="timeline-text text-muted mb-1">
                    Room {{ $task->room->number ?? 'N/A' }}
                    @if($task->assignedTo)
                    - {{ $task->assignedTo->name }}
                    @endif
                  </p>
                  <small class="text-muted">
                    <i class="bi bi-calendar"></i> {{ $task->created_at->format('M d, Y H:i') }}
                  </small>
                </div>
              </div>
              @endforeach
            </div>
            @else
            <div class="text-center text-muted">
              <i class="bi bi-inbox display-4"></i>
              <p>No recent tasks found</p>
            </div>
            @endif
          </div>
          <div class="card-footer">
            {{-- legend for task status --}}
            <div class="d-flex justify-content-between">
              <div>
                <span class="badge bg-success">Completed</span>
                <span class="badge bg-warning">In Progress</span>
                <span class="badge bg-info">Pending</span>
              </div>
              <div>
                <small class="text-muted">Status Legend</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="card mt-3">
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-gear"></i> Actions</h3>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.cleaning-schedule.edit', $checklist) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Checklist
              </a>
              <a href="{{ route('tenant.cleaning-schedule.duplicate', $checklist) }}" class="btn btn-outline-secondary">
                <i class="bi bi-copy"></i> Duplicate Checklist
              </a>
              <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                <i class="bi bi-trash"></i> Delete Checklist
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the checklist <strong>"{{ $checklist->name }}"</strong>?</p>
        <p class="text-danger">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" action="{{ route('tenant.cleaning-schedule.destroy', $checklist) }}" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>
<style>
.timeline {
  position: relative;
  padding-left: 20px;
}

.timeline-item {
  position: relative;
  padding-left: 25px;
}

.timeline-marker {
  position: absolute;
  left: -8px;
  top: 5px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #fff;
  box-shadow: 0 0 0 2px #dee2e6;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #dee2e6;
}
</style>
@endsection

@push('scripts')
<script>
function confirmDelete() {
  const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
  modal.show();
}
</script>
@endpush

{{-- @push('styles') --}}

{{-- @endpush --}}