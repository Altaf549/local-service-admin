@extends('admin.layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Terms & Conditions</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update-terms') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="terms" class="form-label">Content</label>
                <textarea class="form-control" id="terms" name="value" rows="15" required>{{ $terms->value ?? '' }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Terms & Conditions</button>
        </form>
    </div>
</div>
@endsection

