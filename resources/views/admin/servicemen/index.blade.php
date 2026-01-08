@extends('admin.layouts.app')

@section('title', 'Servicemen')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Servicemen</h5>
    </div>
    <div class="card-body">
        <table id="servicemenTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Category</th>
                    <th>Experience</th>
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
    var table = $('#servicemenTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.servicemen.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'phone', name: 'phone'},
            {data: 'category', name: 'category'},
            {data: 'experience', name: 'experience'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

});

function toggleStatus(id) {
    $.ajax({
        url: "{{ route('admin.servicemen.toggle-status', ':id') }}".replace(':id', id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#servicemenTable').DataTable().draw();
            alert(response.success);
        },
        error: function(xhr) {
            $('#servicemenTable').DataTable().draw();
            var errorMessage = xhr.responseJSON?.error;
            alert(errorMessage || 'An error occurred');
        }
    });
}

function showImageModal(imageUrl, title) {
    $('#imageModalBody').html('<img src="' + imageUrl + '" class="img-fluid" style="max-width: 100%; height: auto;">');
    $('#imageModalTitle').text(title);
    $('#imageModal').modal('show');
}

function viewDetails(id) {
    $.ajax({
        url: "{{ route('admin.servicemen.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(response) {
            console.log("response", response);
            var html = '<div class="row">';
            html += '<div class="col-md-6"><strong>ID:</strong> ' + response.id + '</div>';
            html += '<div class="col-md-6"><strong>Name:</strong> ' + response.name + '</div>';
            html += '<div class="col-md-6"><strong>Email:</strong> ' + response.email + '</div>';
            html += '<div class="col-md-6"><strong>Mobile Number:</strong> ' + response.mobile_number + '</div>';
            html += '<div class="col-md-6"><strong>Phone:</strong> ' + response.phone + '</div>';
            html += '<div class="col-md-6"><strong>Experience:</strong> ' + response.experience + '</div>';
            html += '<div class="col-md-6"><strong>Status:</strong> ' + response.status + '</div>';
            html += '<div class="col-md-6"><strong>Availability:</strong> ' + response.availability_status + '</div>';
            html += '<div class="col-md-6"><strong>Government ID:</strong> ' + response.government_id + '</div>';
            html += '<div class="col-md-12"><strong>Address:</strong> ' + response.address + '</div>';
            
            if (response.profile_photo) {
                html += '<div class="col-md-6"><strong>Profile Photo:</strong><br><img src="' + response.profile_photo + '" style="max-width: 200px; max-height: 200px; cursor: pointer;" class="img-thumbnail" onclick="showImageModal(\'' + response.profile_photo + '\', \'Profile Photo\')"></div>';
            }
            if (response.id_proof_image) {
                html += '<div class="col-md-6"><strong>ID Proof:</strong><br><img src="' + response.id_proof_image + '" style="max-width: 200px; max-height: 200px; cursor: pointer;" class="img-thumbnail" onclick="showImageModal(\'' + response.id_proof_image + '\', \'ID Proof\')"></div>';
            }
            
            if (response.experiences && response.experiences.length > 0) {
                html += '<div class="col-md-12 mt-3"><strong>Experiences:</strong><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Title</th><th>Company</th><th>Years</th><th>Period</th><th>Current</th></tr></thead><tbody>';
                response.experiences.forEach(function(exp) {
                    var period = (exp.start_date || 'N/A') + ' - ' + (exp.is_current ? 'Present' : (exp.end_date || 'N/A'));
                    html += '<tr><td>' + (exp.title || 'N/A') + '</td><td>' + (exp.company || 'N/A') + '</td><td>' + (exp.years || 'N/A') + '</td><td>' + period + '</td><td>' + (exp.is_current ? 'Yes' : 'No') + '</td></tr>';
                });
                html += '</tbody></table></div></div>';
            }
            
            if (response.achievements && response.achievements.length > 0) {
                html += '<div class="col-md-12 mt-3"><strong>Achievements:</strong><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Title</th><th>Description</th><th>Achieved Date</th></tr></thead><tbody>';
                response.achievements.forEach(function(ach) {
                    html += '<tr><td>' + (ach.title || 'N/A') + '</td><td>' + (ach.description || 'N/A') + '</td><td>' + (ach.achieved_date || 'N/A') + '</td></tr>';
                });
                html += '</tbody></table></div></div>';
            }
            
            if (response.services && response.services.length > 0) {
                html += '<div class="col-md-12"><strong>Assigned Services:</strong><ul>';
                response.services.forEach(function(service) {
                    html += '<li>' + service.service_name + '</li>';
                });
                html += '</ul></div>';
            }
            
            html += '<div class="col-md-6"><strong>Created At:</strong> ' + response.created_at + '</div>';
            html += '<div class="col-md-6"><strong>Updated At:</strong> ' + response.updated_at + '</div>';
            html += '</div>';
            
            $('#detailsModalBody').html(html);
            $('#detailsModalTitle').text('Serviceman Details');
            $('#detailsModal').modal('show');
        },
        error: function(xhr) {
            alert('Error loading details: ' + (xhr.responseJSON?.error || 'An error occurred'));
        }
    });
}

</script>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalTitle">Details</h5>
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
@endpush

