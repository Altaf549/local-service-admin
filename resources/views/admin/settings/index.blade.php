@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Banner Management</h5>
            </div>
            <div class="card-body">
                <p>Manage banners from the <a href="{{ route('admin.banners.index') }}">Banners</a> section.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Terms & Conditions</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update-terms') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="terms" class="form-label">Content</label>
                        <textarea class="form-control" id="terms" name="value" rows="10" required>{{ $terms->value ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Terms & Conditions</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Privacy Policy</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update-privacy') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="privacy" class="form-label">Content</label>
                        <textarea class="form-control" id="privacy" name="value" rows="10" required>{{ $privacy->value ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Privacy Policy</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">About Us</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update-about') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="about" class="form-label">Content</label>
                        <textarea class="form-control" id="about" name="value" rows="10" required>{{ $about->value ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update About Us</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

