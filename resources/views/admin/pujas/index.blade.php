@extends('admin.layouts.app')

@section('title', 'Pujas')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pujas</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pujaModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Add Puja
        </button>
    </div>
    <div class="card-body">
        <table id="pujasTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Puja Name</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="pujaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Puja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pujaForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="puja_id" name="id">
                    <div class="mb-3">
                        <label for="puja_name" class="form-label">Puja Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="puja_name" name="puja_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="puja_type_id" class="form-label">Puja Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="puja_type_id" name="puja_type_id" required>
                            <option value="">Select Type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration</label>
                        <input type="text" class="form-control" id="duration" name="duration">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                    <input type="hidden" id="status" name="status" value="active">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Puja Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewContent">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#pujasTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.pujas.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'puja_name', name: 'puja_name'},
            {data: 'type', name: 'type'},
            {data: 'duration', name: 'duration'},
            {data: 'price', name: 'price'},
            {data: 'image', name: 'image', orderable: false, searchable: false},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#pujaForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = $('#puja_id').val();
        var url = id ? "{{ route('admin.pujas.update', ':id') }}".replace(':id', id) : "{{ route('admin.pujas.store') }}";
        formData.append('_method', id ? 'PUT' : 'POST');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#pujaModal').modal('hide');
                table.draw();
                alert(response.success);
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    alert(Object.values(errors).flat().join('\n'));
                } else {
                    alert('An error occurred');
                }
            }
        });
    });
});

function resetForm() {
    $('#pujaForm')[0].reset();
    $('#puja_id').val('');
    $('#imagePreview').html('');
    $('#modalTitle').text('Add Puja');
}

function editPuja(id) {
    $.ajax({
        url: "{{ route('admin.pujas.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(data) {
            $('#puja_id').val(data.id);
            $('#puja_name').val(data.puja_name);
            $('#puja_type_id').val(data.puja_type_id);
            $('#duration').val(data.duration);
            $('#price').val(data.price);
            $('#description').val(data.description);
            if (data.image_url) {
                $('#imagePreview').html('<img src="' + data.image_url + '" width="100" height="100">');
            }
            $('#modalTitle').text('Edit Puja');
            $('#pujaModal').modal('show');
        }
    });
}

function viewPuja(id) {
    $.ajax({
        url: "{{ route('admin.pujas.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(data) {
            var html = '<p><strong>Name:</strong> ' + data.puja_name + '</p>';
            html += '<p><strong>Type:</strong> ' + (data.puja_type?.type_name || 'N/A') + '</p>';
            html += '<p><strong>Duration:</strong> ' + (data.duration || 'N/A') + '</p>';
            html += '<p><strong>Price:</strong> ₹' + parseFloat(data.price).toFixed(2) + '</p>';
            html += '<p><strong>Description:</strong> ' + (data.description || 'N/A') + '</p>';
            html += '<p><strong>Status:</strong> ' + data.status + '</p>';
            if (data.image_url) {
                html += '<p><strong>Image:</strong><br><img src="' + data.image_url + '" class="img-fluid"></p>';
            }
            $('#viewContent').html(html);
            $('#viewModal').modal('show');
        }
    });
}

function toggleStatus(id) {
    $.ajax({
        url: "{{ route('admin.pujas.toggle-status', ':id') }}".replace(':id', id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#pujasTable').DataTable().draw();
            alert(response.success);
        },
        error: function(xhr) {
            var errorMessage = xhr.responseJSON?.error;
            alert(errorMessage || 'An error occurred');
        }
    });
}

function deletePuja(id) {
    if (confirm('Are you sure you want to delete this puja?')) {
        $.ajax({
            url: "{{ route('admin.pujas.destroy', ':id') }}".replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#pujasTable').DataTable().draw();
                alert(response.success);
            }
        });
    }
}
</script>
@endpush

