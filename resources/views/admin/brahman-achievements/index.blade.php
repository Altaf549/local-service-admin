@extends('admin.layouts.app')

@section('title', 'Brahman Achievements')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Brahman Achievements</h2>
            <p class="text-muted">Manage achievements added by brahmans</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="achievementsTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Brahman</th>
                                <th>Phone</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Achieved Date</th>
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
    var table = $('#achievementsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.brahman-achievements.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'brahman_name', name: 'brahman_name' },
            { data: 'brahman_phone', name: 'brahman_phone' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'achieved_date', name: 'achieved_date' }
        ]
    });
});
</script>
@endpush

