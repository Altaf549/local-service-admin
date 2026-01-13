<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'service', 'puja', 'serviceman', 'brahman'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'service', 'puja', 'serviceman', 'brahman'])
            ->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
            'payment_status' => 'sometimes|in:pending,paid',
        ]);

        $booking = Booking::findOrFail($id);

        $booking->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status ?? $booking->payment_status,
        ]);

        return redirect()->back()
            ->with('success', 'Booking status updated successfully');
    }
}
