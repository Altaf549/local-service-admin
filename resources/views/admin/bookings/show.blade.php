@extends('admin.layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Booking #{{ $booking->id }}</h5>
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <h6>Booking Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Booking ID:</strong></td>
                        <td>#{{ $booking->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td>
                            <span class="badge bg-{{ $booking->booking_type === 'service' ? 'primary' : 'success' }}">
                                {{ ucfirst($booking->booking_type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td>{{ $booking->booking_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Time:</strong></td>
                        <td>{{ $booking->booking_time }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ getStatusColor($booking->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Payment Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                            <small class="text-muted"> (COD)</small>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total Amount:</strong></td>
                        <td>₹{{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Customer Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $booking->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $booking->user->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Mobile:</strong></td>
                        <td>{{ $booking->mobile_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>{{ $booking->address }}</td>
                    </tr>
                    @if($booking->notes)
                    <tr>
                        <td><strong>Notes:</strong></td>
                        <td>{{ $booking->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h6>Service/Puja Details</h6>
                @if($booking->booking_type === 'service')
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Service:</strong></td>
                            <td>{{ $booking->service->service_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Category:</strong></td>
                            <td>{{ $booking->service->category->category_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>{{ $booking->service->description ?? 'N/A' }}</td>
                        </tr>
                    </table>
                @else
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Puja:</strong></td>
                            <td>{{ $booking->puja->puja_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td>{{ $booking->puja->pujaType->puja_type_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>{{ $booking->puja->description ?? 'N/A' }}</td>
                        </tr>
                    </table>
                @endif
            </div>
            <div class="col-md-6">
                <h6>Provider Details</h6>
                @if($booking->booking_type === 'service')
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Serviceman:</strong></td>
                            <td>{{ $booking->serviceman->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $booking->serviceman->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Mobile:</strong></td>
                            <td>{{ $booking->serviceman->mobile_number ?? 'N/A' }}</td>
                        </tr>
                    </table>
                @else
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Brahman:</strong></td>
                            <td>{{ $booking->brahman->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $booking->brahman->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Mobile:</strong></td>
                            <td>{{ $booking->brahman->mobile_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Specialization:</strong></td>
                            <td>{{ $booking->brahman->specialization ?? 'N/A' }}</td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-12">
                <h6>Update Status</h6>
                <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Booking Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="in_progress" {{ $booking->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="pending" {{ $booking->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    function getStatusColor($status) {
        $colors = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger'
        ];
        return $colors[$status] ?? 'secondary';
    }
@endphp
@endsection
