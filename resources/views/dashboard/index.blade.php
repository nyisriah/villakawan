@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="max-w-6xl mx-auto py-10">

    <h1 class="text-2xl font-bold mb-6">
        Dashboard Saya
    </h1>

    <div class="bg-white rounded-2xl shadow p-6">

        <h2 class="text-lg font-semibold mb-4">
            Riwayat Booking
        </h2>

        @forelse($bookings as $booking)
            <div class="border-b py-4 flex justify-between items-center">

                <div>
                    <p class="font-semibold">
                        {{ $booking->villa->name ?? 'Villa' }}
                    </p>

                    <p class="text-sm text-gray-500">
                        {{ $booking->checkin_date->format('d M Y') }} - {{ $booking->checkout_date->format('d M Y') }}
                    </p>
                </div>

                <div class="text-right">

                    <!-- STATUS -->
                    @if($booking->status == 'pending')
                        <span class="text-yellow-500 text-sm">
                            Menunggu konfirmasi
                        </span>

                    @elseif($booking->status == 'approved')
                        <a href="{{ route('payments.create', $booking->id) }}"
                           class="bg-primary text-white px-4 py-1 rounded-full text-sm">
                            Bayar Sekarang
                        </a>

                    @elseif($booking->status == 'paid')
                        <span class="text-blue-500 text-sm">
                            Menunggu verifikasi
                        </span>

                    @elseif($booking->status == 'confirmed')
                        <a href="{{ route('invoice.show', $booking->id) }}"
                           class="text-green-600 text-sm underline">
                            Download Invoice
                        </a>

                    @elseif($booking->status == 'rejected')
                        <span class="text-red-500 text-sm">
                            Ditolak
                        </span>
                    @endif

                </div>

            </div>
        @empty
            <p class="text-gray-500">
                Belum ada booking
            </p>
        @endforelse

    </div>

</div>
@endsection