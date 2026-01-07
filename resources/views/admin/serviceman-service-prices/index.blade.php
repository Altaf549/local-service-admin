@extends('admin.layouts.app')

@section('title', 'Serviceman Service Prices')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Serviceman Service Prices</h2>
            <p class="text-muted">Manage custom prices set by servicemen for services</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="pricesTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Serviceman</th>
                                <th>Phone</th>
                                <th>Service</th>
                                <th>Default Price</th>
                                <th>Custom Price</th>
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

<!-- Edit Price Modal -->
<div class="modal fade" id="editPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPriceForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_price_id" name="id">
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit_price" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Price</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#pricesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.serviceman-service-prices.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'serviceman_name', name: 'serviceman_name' },
            { data: 'serviceman_phone', name: 'serviceman_phone' },
            { data: 'service_name', name: 'service_name' },
            { data: 'default_price', name: 'default_price' },
            { data: 'price', name: 'price' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    window.editPrice = function(id) {
        $.ajax({
            url: "{{ url('admin/serviceman-service-prices') }}/" + id,
            type: 'GET',
            success: function(response) {
                $('#edit_price_id').val(response.data.id);
                $('#edit_price').val(response.data.price);
                $('#editPriceModal').modal('show');
            }
        });
    };

    $('#editPriceForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_price_id').val();
        var formData = $(this).serialize();

        $.ajax({
            url: "{{ url('admin/serviceman-service-prices') }}/" + id,
            type: 'POST',
            data: formData + '&_method=PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#editPriceModal').modal('hide');
                table.ajax.reload();
                alert('Price updated successfully');
            },
            error: function(xhr) {
                alert('An error occurred: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });

    window.deletePrice = function(id) {
        if (confirm('Are you sure you want to delete this price?')) {
            $.ajax({
                url: "{{ url('admin/serviceman-service-prices') }}/" + id + '/delete',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    table.ajax.reload();
                    alert('Price deleted successfully');
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

