@extends('admin.layouts.app')

@section('title', 'About Us')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">About Us</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update-about') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="about" class="form-label">Content</label>
                <textarea class="form-control" id="about" name="value" rows="15" required>{{ $about->value ?? '' }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update About Us</button>
        </form>
    </div>
</div>
@endsection

