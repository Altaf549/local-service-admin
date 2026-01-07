@extends('admin.layouts.app')

@section('title', 'Brahman Experiences')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Brahman Experiences</h2>
            <p class="text-muted">Manage experiences added by brahmans</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="experiencesTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Brahman</th>
                                <th>Phone</th>
                                <th>Title</th>
                                <th>Organization</th>
                                <th>Years</th>
                                <th>Period</th>
                                <th>Current</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#experiencesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.brahman-experiences.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'brahman_name', name: 'brahman_name' },
            { data: 'brahman_phone', name: 'brahman_phone' },
            { data: 'title', name: 'title' },
            { data: 'organization', name: 'organization' },
            { data: 'years', name: 'years' },
            { data: 'period', name: 'period' },
            { data: 'is_current', name: 'is_current' }
        ]
    });
});
</script>
@endpush

