@extends('admin.layouts.app')

@section('title', 'Services')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Services</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Add Service
        </button>
    </div>
    <div class="card-body">
        <table id="servicesTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>Category</th>
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
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="serviceForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="service_id" name="id">
                    <div class="mb-3">
                        <label for="service_name" class="form-label">Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="service_name" name="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
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
                <h5 class="modal-title">Service Details</h5>
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
    var table = $('#servicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.services.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'service_name', name: 'service_name'},
            {data: 'category', name: 'category'},
            {data: 'price', name: 'price'},
            {data: 'image', name: 'image', orderable: false, searchable: false},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#serviceForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = $('#service_id').val();
        var url = id ? "{{ route('admin.services.update', ':id') }}".replace(':id', id) : "{{ route('admin.services.store') }}";
        var method = id ? 'POST' : 'POST';
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
                $('#serviceModal').modal('hide');
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
    $('#serviceForm')[0].reset();
    $('#service_id').val('');
    $('#imagePreview').html('');
    $('#modalTitle').text('Add Service');
}

function editService(id) {
    $.ajax({
        url: "{{ route('admin.services.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(data) {
            $('#service_id').val(data.id);
            $('#service_name').val(data.service_name);
            $('#category_id').val(data.category_id);
            $('#price').val(data.price);
            $('#description').val(data.description);
            if (data.image_url) {
                $('#imagePreview').html('<img src="' + data.image_url + '" width="100" height="100">');
            }
            $('#modalTitle').text('Edit Service');
            $('#serviceModal').modal('show');
        }
    });
}

function viewService(id) {
    $.ajax({
        url: "{{ route('admin.services.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(data) {
            var html = '<p><strong>Name:</strong> ' + data.service_name + '</p>';
            html += '<p><strong>Category:</strong> ' + (data.category?.category_name || 'N/A') + '</p>';
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
        url: "{{ route('admin.services.toggle-status', ':id') }}".replace(':id', id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#servicesTable').DataTable().draw();
            alert(response.success);
        },
        error: function(xhr) {
            var errorMessage = xhr.responseJSON?.error;
            alert(errorMessage || 'An error occurred');
        }
    });
}

function deleteService(id) {
    if (confirm('Are you sure you want to delete this service?')) {
        $.ajax({
            url: "{{ route('admin.services.destroy', ':id') }}".replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#servicesTable').DataTable().draw();
                alert(response.success);
            }
        });
    }
}
</script>
@endpush

