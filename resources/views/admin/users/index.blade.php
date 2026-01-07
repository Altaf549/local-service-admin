@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Users</h5>
    </div>
    <div class="card-body">
        <table id="usersTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Email Verified</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.users.index') }}",
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                console.error('Response:', xhr.responseText);
                alert('An error occurred while loading data. Please check the console for details.');
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'email_verified_at', name: 'email_verified_at'},
            {data: 'created_at', name: 'created_at'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

});

function toggleStatus(id) {
    $.ajax({
        url: "{{ route('admin.users.toggle-status', ':id') }}".replace(':id', id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#usersTable').DataTable().draw();
            alert(response.success);
        },
        error: function(xhr) {
            $('#usersTable').DataTable().draw();
            var errorMessage = xhr.responseJSON?.error;
            alert(errorMessage || 'An error occurred');
        }
    });
}

function viewDetails(id) {
    $.ajax({
        url: "{{ route('admin.users.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(response) {
            var html = '<div class="row">';
            
            // Profile Photo
            if (response.profile_photo) {
                html += '<div class="col-md-12 text-center mb-3">';
                html += '<img src="' + response.profile_photo + '" alt="Profile Photo" class="img-thumbnail" style="max-width: 200px; cursor: pointer;" onclick="viewImage(\'' + response.profile_photo + '\', \'Profile Photo\')">';
                html += '</div>';
            }
            
            html += '<div class="col-md-6"><strong>ID:</strong> ' + response.id + '</div>';
            html += '<div class="col-md-6"><strong>Name:</strong> ' + (response.name || 'N/A') + '</div>';
            html += '<div class="col-md-6"><strong>Email:</strong> ' + (response.email || 'N/A') + '</div>';
            html += '<div class="col-md-6"><strong>Mobile Number:</strong> ' + (response.mobile_number || 'N/A') + '</div>';
            html += '<div class="col-md-12"><strong>Address:</strong> ' + (response.address || 'N/A') + '</div>';
            html += '<div class="col-md-6"><strong>Role:</strong> ' + (response.role || 'N/A') + '</div>';
            html += '<div class="col-md-6"><strong>Status:</strong> <span class="badge bg-' + (response.status === 'active' ? 'success' : 'danger') + '">' + (response.status || 'N/A') + '</span></div>';
            html += '<div class="col-md-6"><strong>Email Verified:</strong> ' + (response.email_verified_at || 'Not Verified') + '</div>';
            html += '<div class="col-md-6"><strong>Created At:</strong> ' + (response.created_at || 'N/A') + '</div>';
            html += '<div class="col-md-6"><strong>Updated At:</strong> ' + (response.updated_at || 'N/A') + '</div>';
            html += '</div>';
            
            $('#detailsModalBody').html(html);
            $('#detailsModalTitle').text('User Details');
            $('#detailsModal').modal('show');
        },
        error: function(xhr) {
            alert('Error loading details: ' + (xhr.responseJSON?.error || 'An error occurred'));
        }
    });
}

function viewImage(imageUrl, title) {
    $('#imageModalBody').html('<img src="' + imageUrl + '" class="img-fluid" alt="' + title + '">');
    $('#imageModalTitle').text(title);
    $('#imageModal').modal('show');
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: "{{ route('admin.users.destroy', ':id') }}".replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#usersTable').DataTable().draw();
                alert(response.success);
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.error || 'An error occurred'));
            }
        });
    }
}

</script>
@endpush

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalTitle">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="imageModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

