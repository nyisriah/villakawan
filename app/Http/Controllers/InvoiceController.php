<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show($id)
    {
        $booking = Booking::with('user', 'villa')->findOrFail($id);

        // Check if user owns the booking or is admin
        if ($booking->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Check if status is confirmed
        if ($booking->status !== 'confirmed') {
            abort(404);
        }

        return view('invoice.show', compact('booking'));
    }
}