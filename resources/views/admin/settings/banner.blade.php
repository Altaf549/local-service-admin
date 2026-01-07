@extends('admin.layouts.app')

@section('title', 'Banner Management')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Banner Management</h5>
    </div>
    <div class="card-body">
        <p class="mb-3">Manage banners from the <a href="{{ route('admin.banners.index') }}" class="btn btn-primary btn-sm">Banners Section</a></p>
    </div>
</div>
@endsection

