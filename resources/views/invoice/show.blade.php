@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10">

    <div class="bg-white rounded-2xl shadow p-6">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">Invoice</h1>
            <p class="text-gray-600">Booking #{{ $booking->id }}</p>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Detail Booking</h2>
            <div class="space-y-2">
                <p><strong>Villa:</strong> {{ $booking->villa->name }}</p>
                <p><strong>Check-in:</strong> {{ $booking->check_in->format('d M Y') }}</p>
                <p><strong>Check-out:</strong> {{ $booking->check_out->format('d M Y') }}</p>
                <p><strong>Guest:</strong> {{ $booking->guest }} orang</p>
                <p><strong>Status:</strong> <span class="text-green-600">Paid & Confirmed</span></p>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Detail User</h2>
            <div class="space-y-2">
                <p><strong>Nama:</strong> {{ $booking->user->name }}</p>
                <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                <p><strong>Phone:</strong> {{ $booking->user->phone }}</p>
            </div>
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">Total:</span>
                <span class="text-lg font-bold">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button onclick="window.print()" class="bg-primary text-white px-6 py-2 rounded-full">
                Download Invoice
            </button>
        </div>

    </div>

</div>
<div class="mt-6 p-4 bg-gray-50 rounded-xl">

    <h3 class="font-semibold mb-2">📍 Informasi Villa</h3>

    <!-- NOMOR PENJAGA -->
    <p class="mb-2">
        <strong>📞 Kontak Penjaga:</strong><br>
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->villa->contact_phone) }}"
           class="text-green-600 underline"
           target="_blank">
            {{ $booking->villa->contact_pengelola_villa }}
        </a>
    </p>

    <!-- GOOGLE MAPS -->
    <p>
        <strong>🗺️ Lokasi Villa:</strong><br>
        <a href="{{ $booking->villa->google_maps_link }}"
           target="_blank"
           class="text-blue-600 underline">
            Buka di Google Maps
        </a>
    </p>

</div>
@endsection