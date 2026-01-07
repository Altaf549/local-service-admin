@extends('admin.layouts.app')

@section('title', 'Serviceman Achievements')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Serviceman Achievements</h2>
            <p class="text-muted">Manage achievements added by servicemen</p>
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
                                <th>Serviceman</th>
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
            url: "{{ route('admin.serviceman-achievements.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'serviceman_name', name: 'serviceman_name' },
            { data: 'serviceman_phone', name: 'serviceman_phone' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'achieved_date', name: 'achieved_date' }
        ]
    });
});
</script>
@endpush

