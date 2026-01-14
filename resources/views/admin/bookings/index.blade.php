@extends('admin.layouts.app')

@section('title', 'Bookings')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Bookings</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped" id="bookingsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Service/Puja</th>
                        <th>Provider</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>
                                {{ $booking->user->name ?? 'N/A' }}
                                <br>
                                <small class="text-muted">{{ $booking->user->email ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $booking->booking_type === 'service' ? 'primary' : 'success' }}">
                                    {{ ucfirst($booking->booking_type) }}
                                </span>
                            </td>
                            <td>
                                @if($booking->booking_type === 'service')
                                    {{ $booking->service->service_name ?? 'N/A' }}
                                @else
                                    {{ $booking->puja->puja_name ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if($booking->booking_type === 'service')
                                    {{ $booking->serviceman->name ?? 'N/A' }}
                                @else
                                    {{ $booking->brahman->name ?? 'N/A' }}
                                @endif
                            </td>
                            <td>{{ $booking->booking_date->format('M d, Y') }}</td>
                            <td>{{ $booking->booking_time }}</td>
                            <td>
                                <span class="badge bg-{{ getStatusColor($booking->status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                                <br>
                                <small class="text-muted">COD</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#bookingsTable').DataTable({
            order: [[0, 'desc']]
        });
    });
</script>
@endpush
@endsection
