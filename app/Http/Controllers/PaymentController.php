<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        // ✅ VALIDASI INPUT
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        // 🔐 SECURITY: Validasi booking milik user yang login (CRITICAL!)
        if ($booking->user_id !== $user->id) {
            abort(403, 'Unauthorized: Booking bukan milik Anda');
        }

        // 🔐 SECURITY: Validasi status harus 'approved' (tidak boleh bypass)
        if ($booking->status !== 'approved') {
            return back()->withErrors(['error' => 'Booking belum disetujui admin. Status saat ini: ' . $booking->status]);
        }

        // 🔐 SECURITY: Cek apakah payment sudah pernah dibuat untuk booking ini?
        if ($booking->payment()->exists()) {
            return back()->withErrors(['error' => 'Booking ini sudah memiliki payment. Hubungi admin untuk bantuan.']);
        }

        // 🔐 SECURITY: Validasi file upload
        if (!$request->hasFile('proof')) {
            return back()->withErrors(['proof' => 'File bukti pembayaran harus diupload']);
        }

        // ✅ SIMPAN FILE
        $proofPath = $request->file('proof')->store('payments', 'public');

        // ✅ BUAT PAYMENT (only user yang login yang bisa)
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'proof' => $proofPath,
            'status' => 'pending',
            'payment_method' => 'manual_transfer', // Default untuk user upload
        ]);

        // 🔐 SECURITY: Update booking status ke 'paid' otomatis
        $booking->update(['status' => 'paid']);

        return redirect('/payment/' . $payment->id)->with('success', 'Bukti pembayaran berhasil diupload. Tunggu verifikasi admin.');
    }

    public function create($booking_id)
    {
        $user = auth()->user();
        $booking = \App\Models\Booking::with('villa')->findOrFail($booking_id);

        // 🔐 SECURITY: Validasi ownership - user hanya bisa akses booking sendiri
        if ($booking->user_id !== $user->id) {
            abort(403, 'Unauthorized: Booking bukan milik Anda');
        }

        // 🔐 SECURITY: Validasi status harus 'approved' (tidak boleh bypass)
        if ($booking->status !== 'approved') {
            return redirect('/dashboard')->withErrors(['error' => 'Booking belum disetujui admin. Status saat ini: ' . $booking->status]);
        }

        return view('payment.create', compact('booking'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $payment = Payment::with('booking.villa')->findOrFail($id);

        // 🔐 SECURITY: Validasi ownership - user hanya bisa lihat payment sendiri
        if ($payment->booking->user_id !== $user->id) {
            abort(403, 'Unauthorized: Payment bukan milik Anda');
        }

        return view('payment.show', compact('payment'));
    }
}