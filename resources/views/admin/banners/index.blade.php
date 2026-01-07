@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Banners</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Add Banner
        </button>
    </div>
    <div class="card-body">
        <table id="bannersTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Link</th>
                    <th>Order</th>
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
<div class="modal fade" id="bannerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bannerForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="banner_id" name="id">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Link (URL)</label>
                        <input type="url" class="form-control" id="link" name="link">
                    </div>
                    <div class="mb-3">
                        <label for="order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="order" name="order" min="0" value="0">
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
    var table = $('#bannersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.banners.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'title', name: 'title'},
            {data: 'image', name: 'image', orderable: false, searchable: false},
            {data: 'link', name: 'link'},
            {data: 'order', name: 'order'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#bannerForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var id = $('#banner_id').val();
        var url = id ? "{{ route('admin.banners.update', ':id') }}".replace(':id', id) : "{{ route('admin.banners.store') }}";
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
                $('#bannerModal').modal('hide');
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
    $('#bannerForm')[0].reset();
    $('#banner_id').val('');
    $('#imagePreview').html('');
    $('#modalTitle').text('Add Banner');
    $('#image').prop('required', true);
}

function editBanner(id) {
    $.ajax({
        url: "{{ route('admin.banners.show', ':id') }}".replace(':id', id),
        type: 'GET',
        success: function(data) {
            $('#banner_id').val(data.id);
            $('#title').val(data.title);
            $('#link').val(data.link);
            $('#order').val(data.order);
            if (data.image_url) {
                $('#imagePreview').html('<img src="' + data.image_url + '" width="200" height="100">');
                $('#image').prop('required', false);
            }
            $('#modalTitle').text('Edit Banner');
            $('#bannerModal').modal('show');
        }
    });
}

function toggleStatus(id) {
    $.ajax({
        url: "{{ route('admin.banners.toggle-status', ':id') }}".replace(':id', id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#bannersTable').DataTable().draw();
            alert(response.success);
        },
        error: function(xhr) {
            $('#bannersTable').DataTable().draw();
            var errorMessage = xhr.responseJSON?.error;
            alert(errorMessage || 'An error occurred');
        }
    });
}

function deleteBanner(id) {
    if (confirm('Are you sure you want to delete this banner?')) {
        $.ajax({
            url: "{{ route('admin.banners.destroy', ':id') }}".replace(':id', id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#bannersTable').DataTable().draw();
                alert(response.success);
            }
        });
    }
}
</script>
@endpush

