@extends('central.layouts.app')

@section('title', 'Users')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
      {{-- <h3 class="mb-0">Users</h3> --}}
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
      </div>
    </div>
  </div>
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

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="card card-success card-outline mb-4">
      <div class="card-header">
        <h5 class="card-title">Users</h5>
        <div class="card-tools float-end">
          @can('manage users')
          <a href="{{ route('central.users.create') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-plus me-2"></i>Add New User
          </a>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Created At</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>
                  @can('manage users')
                  <a href="{{ route('central.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary me-2">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>
                  @endcan
                  @can('delete users')
                  @if(auth()->id() !== $user->id) <!-- Prevent self-deletion -->
                  <form action="{{ route('central.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash me-1"></i>Delete
                    </button>
                  </form>
                  @endif
                  @endcan
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        {{-- Pagination links --}}
        @if($users->hasPages())
        <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0 text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                    </p>
                </div>
                <div class="col-md-4 float-end">
                    {{ $users->links('vendor.pagination.bootstrap-5') }} {{-- I want to align the links to the end of the column --}}
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