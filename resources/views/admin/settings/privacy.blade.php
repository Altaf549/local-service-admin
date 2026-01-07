@extends('admin.layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Privacy Policy</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update-privacy') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="privacy" class="form-label">Content</label>
                <textarea class="form-control" id="privacy" name="value" rows="15" required>{{ $privacy->value ?? '' }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Privacy Policy</button>
        </form>
    </div>
</div>
@endsection

