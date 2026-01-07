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
                                <th>Action</th>
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

<!-- Edit Experience Modal -->
<div class="modal fade" id="editExperienceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editExperienceForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_experience_id" name="id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_years" class="form-label">Years</label>
                            <input type="number" min="0" class="form-control" id="edit_years" name="years">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="edit_company" name="company">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_current" name="is_current" value="1">
                            <label class="form-check-label" for="edit_is_current">
                                Current Experience
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Experience</button>
                </div>
            </form>
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
            { data: 'is_current', name: 'is_current' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    window.editExperience = function(id) {
        $.ajax({
            url: "{{ url('admin/serviceman-experiences') }}/" + id,
            type: 'GET',
            success: function(response) {
                var exp = response.data;
                $('#edit_experience_id').val(exp.id);
                $('#edit_title').val(exp.title);
                $('#edit_description').val(exp.description);
                $('#edit_years').val(exp.years);
                $('#edit_company').val(exp.company);
                $('#edit_start_date').val(exp.start_date);
                $('#edit_end_date').val(exp.end_date);
                $('#edit_is_current').prop('checked', exp.is_current);
                $('#editExperienceModal').modal('show');
            }
        });
    };

    $('#editExperienceForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_experience_id').val();
        var formData = $(this).serialize();
        formData += '&is_current=' + ($('#edit_is_current').is(':checked') ? 1 : 0);

        $.ajax({
            url: "{{ url('admin/serviceman-experiences') }}/" + id,
            type: 'POST',
            data: formData + '&_method=PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#editExperienceModal').modal('hide');
                table.ajax.reload();
                alert('Experience updated successfully');
            },
            error: function(xhr) {
                alert('An error occurred: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });

    window.deleteExperience = function(id) {
        if (confirm('Are you sure you want to delete this experience?')) {
            $.ajax({
                url: "{{ url('admin/serviceman-experiences') }}/" + id + '/delete',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    table.ajax.reload();
                    alert('Experience deleted successfully');
                },
                error: function(xhr) {
                    alert('An error occurred: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }
    };
});
</script>
@endpush

