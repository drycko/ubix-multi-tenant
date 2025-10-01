@extends('tenant.layouts.app')

@section('title', 'Room Types')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
            {{-- <h3 class="mb-0">Room Types</h3> --}}
            </div>
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Room Types</li>
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
		{{-- messages from redirect --}}
		@if(session('success'))
			<div class="alert alert-success">
				{{ session('success') }}
			</div>
		@endif
		@if(session('error'))
			<div class="alert alert-danger">
				{{ session('error') }}
			</div>
		@endif

		<div class="card card-success card-outline">
			<div class="card-header">
				<h5 class="card-title">Room Types</h5>
				<div class="card-tools">
          @can('manage room types')
					<a href="{{ route('tenant.room-types.create') }}" class="btn btn-sm btn-outline-success">
						<i class="fas fa-plus me-2"></i>Add New
					</a>
          @endcan
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Name</th>
								<th>Code</th>
								<th>Description</th>
								<th>Base Capacity</th>
								<th>Max Capacity</th>
								<th>Total Rooms</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($roomTypes as $type)
							<tr>
								<td>{{ $type->name }}</td>
								<td>{{ $type->code }}</td>
								<td>{{ $type->description }}</td>
								<td>{{ $type->base_capacity }}</td>
								<td>{{ $type->max_capacity }}</td>
								<td>{{ $type->rooms_count }}</td>
								<td>
									@if($type->is_active)
										<span class="badge bg-success">Active</span>
									@else
										<span class="badge bg-secondary">Inactive</span>
									@endif
								</td>
								<td>
                  @can('manage room types')
									<a href="{{ route('tenant.room-types.edit', $type) }}" class="btn btn-sm btn-outline-success">
										<i class="fas fa-edit"></i>Edit
									</a>
                  @endcan
                  @can('delete rooms')
									<form action="{{ route('tenant.room-types.destroy', $type) }}" method="POST" class="d-inline">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this room type?')">
											<i class="fas fa-trash"></i>Delete
										</button>
									</form>
                  @endcan
								</td>
							</tr>
							@empty
							<tr>
								<td colspan="8" class="text-center">No room types found.</td>
							</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				{{-- Pagination links --}}
        {{-- {{ $roomTypes->links() }} --}}
        {{-- Beautiful pagination --}}
        @if($roomTypes->hasPages())
        <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        Showing {{ $roomTypes->firstItem() }} to {{ $roomTypes->lastItem() }} of {{ $roomTypes->total() }} entries
                    </p>
                </div>
                <div class="col-md-6 float-end">
                    {{ $roomTypes->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
			</div>
		</div>
	</div>
	<!--end::Container-->
</div>
<!--end::App Content-->
@endsection