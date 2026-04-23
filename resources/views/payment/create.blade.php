@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <div class="bg-yellow-50 p-4 rounded-xl mb-4 border border-yellow-200">

    <h3 class="font-semibold text-lg mb-3">💳 Instruksi Pembayaran</h3>

    <!-- 🏦 BANK TRANSFER -->
    <div class="mb-4">
        <p class="font-semibold text-gray-700">Transfer Bank (BCA)</p>

        <p>
            No Rekening:
            <span class="font-bold text-primary text-lg">
                7361140200
            </span>
        </p>

        <p>
            Atas Nama:
            <span class="font-bold">
                Irpan Nurdin
            </span>
        </p>
    </div>

    <hr class="my-3">

    <!-- 📱 DANA -->
    <div>
        <p class="font-semibold text-gray-700">Pembayaran via DANA</p>

        <p>
            No HP:
            <span class="font-bold text-primary text-lg">
                0815-993-8353
            </span>
        </p>

        <p>
            Atas Nama:
            <span class="font-bold">
                Irpan Nurdin
            </span>
        </p>
    </div>

    <p class="text-sm text-gray-500 mt-3">
        *Pastikan nominal transfer sesuai dengan total pembayaran
    </p>

</div>
    <div class="bg-white rounded-2xl shadow p-6">

        <h1 class="text-2xl font-bold mb-6">
            Upload Bukti Pembayaran
        </h1>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">
                {{ $booking->villa->name }}
            </h2>
            <p class="text-gray-600">
                Check-in: {{ $booking->checkin_date->format('d M Y') }}<br>
                Check-out: {{ $booking->checkout_date->format('d M Y') }}<br>
                Total: Rp {{ number_format($booking->total_price, 0, ',', '.') }}
            </p>
        </div>

        <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

            <div>
                <label for="proof" class="block text-sm font-medium text-gray-700">Upload Bukti Pembayaran</label>
                <input type="file" name="proof" id="proof" accept="image/*" required class="mt-1 block w-full border-gray-300 rounded-md">
            </div>

            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-full">
                Upload Bukti Pembayaran
            </button>
        </form>

        <div class="mt-6">
            <a href="{{ route('dashboard') }}" class="text-primary underline">Kembali ke Dashboard</a>
        </div>

    </div>

</div>
@endsection