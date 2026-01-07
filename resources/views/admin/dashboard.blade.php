@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Service Categories</h5>
                <h2 class="card-text">{{ \App\Models\ServiceCategory::count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Services</h5>
                <h2 class="card-text">{{ \App\Models\Service::count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Pujas</h5>
                <h2 class="card-text">{{ \App\Models\Puja::count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Servicemen</h5>
                <h2 class="card-text">{{ \App\Models\Serviceman::count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Users</h5>
                <h2 class="card-text">{{ \App\Models\User::where('role', '!=', 'admin')->count() }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection

