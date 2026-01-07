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

<!-- Edit Achievement Modal -->
<div class="modal fade" id="editAchievementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Achievement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAchievementForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_achievement_id" name="id">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_achieved_date" class="form-label">Achieved Date</label>
                        <input type="date" class="form-control" id="edit_achieved_date" name="achieved_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Achievement</button>
                </div>
            </form>
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
            { data: 'achieved_date', name: 'achieved_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    window.editAchievement = function(id) {
        $.ajax({
            url: "{{ url('admin/serviceman-achievements') }}/" + id,
            type: 'GET',
            success: function(response) {
                var ach = response.data;
                $('#edit_achievement_id').val(ach.id);
                $('#edit_title').val(ach.title);
                $('#edit_description').val(ach.description);
                $('#edit_achieved_date').val(ach.achieved_date);
                $('#editAchievementModal').modal('show');
            }
        });
    };

    $('#editAchievementForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_achievement_id').val();
        var formData = $(this).serialize();

        $.ajax({
            url: "{{ url('admin/serviceman-achievements') }}/" + id,
            type: 'POST',
            data: formData + '&_method=PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#editAchievementModal').modal('hide');
                table.ajax.reload();
                alert('Achievement updated successfully');
            },
            error: function(xhr) {
                alert('An error occurred: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });

    window.deleteAchievement = function(id) {
        if (confirm('Are you sure you want to delete this achievement?')) {
            $.ajax({
                url: "{{ url('admin/serviceman-achievements') }}/" + id + '/delete',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    table.ajax.reload();
                    alert('Achievement deleted successfully');
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

