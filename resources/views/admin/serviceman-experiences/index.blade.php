@extends('admin.layouts.app')

@section('title', 'Serviceman Experiences')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Serviceman Experiences</h2>
            <p class="text-muted">Manage experiences added by servicemen</p>
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
                                <th>Serviceman</th>
                                <th>Phone</th>
                                <th>Title</th>
                                <th>Company</th>
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
            url: "{{ route('admin.serviceman-experiences.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'serviceman_name', name: 'serviceman_name' },
            { data: 'serviceman_phone', name: 'serviceman_phone' },
            { data: 'title', name: 'title' },
            { data: 'company', name: 'company' },
            { data: 'years', name: 'years' },
            { data: 'period', name: 'period' },
            { data: 'is_current', name: 'is_current' }
        ]
    });
});
</script>
@endpush

