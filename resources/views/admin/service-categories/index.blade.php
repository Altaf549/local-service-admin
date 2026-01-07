@extends('admin.layouts.app')

@section('title', 'Service Categories')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Service Categories</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>
    <div class="card-body">
        <table id="categoriesTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="category_id" name="id">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#categoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.service-categories.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'category_name', name: 'category_name'},
            {data: 'image', name: 'image', orderable: false, searchable: false},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = $('#category_id').val();
        var url = id ? "{{ route('admin.service-categories.update', ':id') }}".replace(':id', id) : "{{ route('admin.service-categories.store') }}";
        
        // Only add _method for update
        if (id) {
            formData.append('_method', 'PUT');
        }

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
                $('#categoryModal').modal('hide');
                table.draw();
                alert(response.success);
                resetForm();
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                var errors = xhr.responseJSON?.errors;
                var errorMessage = xhr.responseJSON?.error;
                if (errors) {
                    var errorText = Object.values(errors).flat().join('\n');
                    alert('Validation errors:\n' + errorText);
                } else if (errorMessage) {
                    alert('Error: ' + errorMessage);
                } else {
                    alert('An error occurred. Please check the console for details.');
                }
            }
        });
    });
});

function resetForm() {
    $('#categoryForm')[0].reset();
    $('#category_id').val('');
    $('#imagePreview').html('');
    $('#modalTitle').text('Add Category');
}

function editCategory(id) {
    $.ajax({
        url: "{{ route('admin.service-categories.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(data) {
            $('#category_id').val(data.id);
            $('#category_name').val(data.category_name);
            if (data.image_url) {
                $('#imagePreview').html('<img src="' + data.image_url + '" width="100" height="100">');
            } else {
                $('#imagePreview').html('');
            }
            $('#modalTitle').text('Edit Category');
            $('#categoryModal').modal('show');
        }
    });
}

function toggleStatus(id) {
    $.ajax({
        url: "{{ route('admin.service-categories.toggle-status', ':id') }}".replace(':id', id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#categoriesTable').DataTable().draw();
            alert(response.success);
        },
        error: function(xhr) {
            var errorMessage = xhr.responseJSON?.error;
            alert(errorMessage || 'An error occurred');
        }
    });
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        $.ajax({
            url: "{{ route('admin.service-categories.destroy', ':id') }}".replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#categoriesTable').DataTable().draw();
                alert(response.success);
            }
        });
    }
}
</script>
@endpush

