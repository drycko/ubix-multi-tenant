@extends('central.layouts.app')

@section('title', 'Stats')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6">
            <h3 class="mb-0">Stats</h3>
            </div>
            <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stats</li>
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
		<div class="card card-success card-outline mb-4">
			<div class="card-header">
				<h5 class="card-title">Stats</h5>
			</div>
			<div class="card-body">
				<p>Stats page graphs goes here.</p>
			</div>
		</div>
	</div>
	<!--end::Container-->
</div>
<!--end::App Content-->
@endsection