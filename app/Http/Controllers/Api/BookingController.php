<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Puja;
use App\Models\Serviceman;
use App\Models\Brahman;
use App\Models\ServicemanServicePrice;
use App\Models\BrahmanPujaPrice;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    // Create Service Booking
    public function createServiceBooking(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'serviceman_id' => 'required|exists:servicemen,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|string',
            'address' => 'required|string',
            'mobile_number' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $service = Service::find($request->service_id);
        $serviceman = Serviceman::find($request->serviceman_id);

        // Check if serviceman is available
        if ($serviceman->availability_status !== 'available') {
            return response()->json([
                'message' => 'Serviceman is not available for booking',
                'errors' => [
                    'serviceman_id' => ['Serviceman is not available for booking']
                ]
            ], 400);
        }

        // Check if user already has an active booking for this service
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('service_id', $request->service_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'message' => 'You already book this service',
                'errors' => [
                    'service_id' => ['You already book this service']
                ]
            ], 400);
        }

        // Get price from serviceman_service_prices table
        $servicePrice = ServicemanServicePrice::where('serviceman_id', $request->serviceman_id)
            ->where('service_id', $request->service_id)
            ->first();

        $totalAmount = $servicePrice ? $servicePrice->price : 0;

        $booking = Booking::create([
            'user_id' => $user->id,
            'booking_type' => 'service',
            'service_id' => $request->service_id,
            'serviceman_id' => $request->serviceman_id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'notes' => $request->notes,
            'total_amount' => $totalAmount,
            'payment_method' => 'cod',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service booking created successfully',
            'data' => [
                'booking' => $booking->load(['user', 'service', 'serviceman']),
            ],
        ], 201);
    }

    // Create Puja Booking
    public function createPujaBooking(Request $request)
    {
        $request->validate([
            'puja_id' => 'required|exists:pujas,id',
            'brahman_id' => 'required|exists:brahmans,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|string',
            'address' => 'required|string',
            'mobile_number' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $puja = Puja::find($request->puja_id);
        $brahman = Brahman::find($request->brahman_id);

        // Check if brahman is available
        if ($brahman->availability_status !== 'available') {
            return response()->json([
                'message' => 'Brahman is not available for booking',
                'errors' => [
                    'brahman_id' => ['Brahman is not available for booking']
                ]
            ], 400);
        }

        // Check if user already has an active booking for this puja
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('puja_id', $request->puja_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'message' => 'You already book this puja',
                'errors' => [
                    'puja_id' => ['You already book this puja']
                ]
            ], 400);
        }

        // Get price from brahman_puja_prices table
        $pujaPrice = BrahmanPujaPrice::where('brahman_id', $request->brahman_id)
            ->where('puja_id', $request->puja_id)
            ->first();

        $totalAmount = $pujaPrice ? $pujaPrice->price : 0;

        $booking = Booking::create([
            'user_id' => $user->id,
            'booking_type' => 'puja',
            'puja_id' => $request->puja_id,
            'brahman_id' => $request->brahman_id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'notes' => $request->notes,
            'total_amount' => $totalAmount,
            'payment_method' => 'cod',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Puja booking created successfully',
            'data' => [
                'booking' => $booking->load(['user', 'puja', 'brahman']),
            ],
        ], 201);
    }

    // Get User Bookings
    public function getUserBookings(Request $request)
    {
        $user = $request->user();
        
        $bookings = Booking::with(['user', 'service', 'puja', 'serviceman', 'brahman'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings,
            ],
        ]);
    }

    // Get Booking Details
    public function getBookingDetails(Request $request, $id)
    {
        $user = $request->user();
        
        $booking = Booking::with(['user', 'service', 'puja', 'serviceman', 'brahman'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $booking,
            ],
        ]);
    }

    // Update Booking
    public function updateBooking(Request $request, $id)
    {
        $request->validate([
            'booking_date' => 'sometimes|required|date|after_or_equal:today',
            'booking_time' => 'sometimes|required|string',
            'address' => 'sometimes|required|string',
            'mobile_number' => 'sometimes|required|string',
            'notes' => 'sometimes|nullable|string',
        ]);

        $user = $request->user();
        
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if booking can be updated (only pending bookings can be updated)
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update booking that is ' . str_replace('_', ' ', $booking->status),
            ], 400);
        }

        // Update booking details
        $booking->update($request->only([
            'booking_date',
            'booking_time', 
            'address',
            'mobile_number',
            'notes'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => [
                'booking' => $booking->load(['user', 'service', 'puja', 'serviceman', 'brahman']),
            ],
        ]);
    }

    // Cancel Booking (Enhanced version)
    public function cancelBooking(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'sometimes|nullable|string|max:500',
        ]);

        $user = $request->user();
        
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled',
            ], 400);
        }

        if (in_array($booking->status, ['confirmed', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel booking that is ' . str_replace('_', ' ', $booking->status),
            ], 400);
        }

        // Update booking with cancellation reason if provided
        $updateData = ['status' => 'cancelled'];
        if ($request->has('cancellation_reason')) {
            $updateData['notes'] = ($booking->notes ? $booking->notes . "\n\n" : '') . 
                               "Cancellation Reason: " . $request->cancellation_reason;
        }

        $booking->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => [
                'booking' => $booking->load(['user', 'service', 'puja', 'serviceman', 'brahman']),
            ],
        ]);
    }

    // Accept Booking (For Serviceman/Brahman)
    public function acceptBooking(Request $request, $id)
    {
        $user = $request->user();
        
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if user is the assigned serviceman or brahman
        if ($booking->booking_type === 'service' && $booking->serviceman_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this booking',
            ], 403);
        }

        if ($booking->booking_type === 'puja' && $booking->brahman_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this booking',
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be accepted in current status',
            ], 400);
        }

        $booking->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Booking accepted successfully',
            'data' => [
                'booking' => $booking->load(['user', 'service', 'puja', 'serviceman', 'brahman']),
            ],
        ]);
    }

    // Complete Booking (For Serviceman/Brahman)
    public function completeBooking(Request $request, $id)
    {
        $user = $request->user();
        
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if user is the assigned serviceman or brahman
        if ($booking->booking_type === 'service' && $booking->serviceman_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this booking',
            ], 403);
        }

        if ($booking->booking_type === 'puja' && $booking->brahman_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this booking',
            ], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed bookings can be completed',
            ], 400);
        }

        $booking->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Booking completed successfully',
            'data' => [
                'booking' => $booking->load(['user', 'service', 'puja', 'serviceman', 'brahman']),
            ],
        ]);
    }

    // Get All Bookings (Admin)
    public function getAllBookings(Request $request)
    {
        $bookings = Booking::with(['user', 'service', 'puja', 'serviceman', 'brahman'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => $bookings,
            ],
        ]);
    }

    // Update Booking Status (Admin)
    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        $booking->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'data' => [
                'booking' => $booking->load(['user', 'service', 'puja', 'serviceman', 'brahman']),
            ],
        ]);
    }
}
