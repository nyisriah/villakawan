@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-4">

    @php
        $images = is_array($villa->images) ? $villa->images : [];
        $facilities = is_array($villa->facilities ?? null) ? $villa->facilities : [];
    @endphp

    <!-- 🔥 HERO SLIDER -->
    <div class="relative h-[500px] rounded-3xl overflow-hidden mb-10 shadow-xl">

        @forelse ($images as $i => $img)
            <img src="{{ asset('storage/' . $img) }}"
                class="hero-slide absolute w-full h-full object-cover transition-opacity duration-700 {{ $i === 0 ? 'opacity-100' : 'opacity-0' }}">
        @empty
            <img src="https://picsum.photos/1200/600" class="w-full h-full object-cover">
        @endforelse

        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

        <!-- Arrow -->
        <button id="prevHero" class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-3xl">❮</button>
        <button id="nextHero" class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-3xl">❯</button>

        <!-- Text -->
        <div class="absolute bottom-8 left-8 text-white">
            <h1 class="text-4xl font-bold mb-2">{{ $villa->name }}</h1>
            <p class="text-lg opacity-90">{{ $villa->location }}</p>

            <div class="flex gap-4 mt-3 text-sm">
                <span>👥 {{ $villa->max_guests }} tamu</span>
                <span>🛏 {{ $villa->bedrooms }} kamar</span>
            </div>
        </div>

    </div>

    <!-- 📸 THUMBNAIL 1 BARIS -->
    <div class="flex gap-3 overflow-x-auto mb-10">
        @foreach ($images as $i => $img)
            <img src="{{ asset('storage/' . $img) }}"
                class="thumb h-24 w-40 object-cover rounded-xl cursor-pointer hover:scale-105 transition"
                data-index="{{ $i }}">
        @endforeach
    </div>

    <!-- 🔍 LIGHTBOX -->
    <div id="lightbox" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50">
        <button id="closeLightbox" class="absolute top-6 right-6 text-white text-3xl">✕</button>
        <button id="prevLight" class="absolute left-6 text-white text-3xl">❮</button>

        <img id="lightboxImg" class="max-h-[90%] max-w-[90%] rounded-xl">

        <button id="nextLight" class="absolute right-6 text-white text-3xl">❯</button>
    </div>

    <!-- GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

        <!-- LEFT -->
        <div class="lg:col-span-2">

            <div class="bg-white/80 backdrop-blur p-6 rounded-3xl shadow-lg mb-6 border">
                <h2 class="text-2xl font-semibold mb-4">Tentang Villa</h2>
                <p class="text-gray-600 leading-relaxed">
                    {{ $villa->description }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-lg mb-6 border">
                <h2 class="text-2xl font-semibold mb-4">Fasilitas</h2>

                @if(count($facilities))
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-gray-600">
                        @foreach ($facilities as $f)
                            <div class="flex items-center gap-2">
                                <span class="text-primary">✔</span>
                                <span>{{ ucfirst($f) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400">Belum ada fasilitas</p>
                @endif
            </div>

        </div>

        <!-- RIGHT -->
        <div>

            <!-- 💰 PRICE -->
            <div class="bg-gradient-to-br from-primary to-green-600 text-white p-6 rounded-3xl shadow-lg mb-6">

                <h2 class="text-xl font-semibold mb-2">Harga</h2>

                <p class="text-3xl font-bold">
                    Rp {{ number_format($villa->weekday_price, 0, ',', '.') }}
                    <span class="text-sm font-normal">/ malam</span>
                </p>

                <p class="text-sm opacity-90 mt-2">
                    Weekend: Rp {{ number_format($villa->weekend_price, 0, ',', '.') }}
                </p>

                <!-- 🔥 SCARCITY -->
                <p class="text-red-200 text-xs mt-2">
                    * slot bulan ini tinggal sedikit
                </p>

            </div>

            <!-- 🧾 BOOKING -->
            <div class="bg-white p-6 rounded-3xl shadow-xl sticky top-6 border">

                <h2 class="text-xl font-semibold mb-4">Booking Sekarang</h2>

                @auth

                <form action="{{ route('bookings.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <input type="hidden" name="villa_id" value="{{ $villa->id }}">

                   <input type="text" name="name"
                value="{{ auth()->user()->name }}"
                class="w-full border rounded-full px-4 py-2 bg-gray-100"
                readonly>

                <input type="email" name="email"
                value="{{ auth()->user()->email }}"
                class="w-full border rounded-full px-4 py-2 bg-gray-100"
                readonly>   

                    <!-- ✅ PHONE ANGKA ONLY -->
                    <input type="text" name="phone"
                        value="{{ auth()->user()->phone ?? '' }}"
                        placeholder="Nomor HP"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full border rounded-full px-4 py-2">

                    <input type="number" name="guest"
                        min="1" max="{{ $villa->max_guests }}"
                        placeholder="Jumlah tamu"
                        class="w-full border rounded-full px-4 py-2" required>

                    <input type="date" id="check_in" name="check_in"
                        min="{{ now()->format('Y-m-d') }}"
                        class="w-full border rounded-full px-4 py-2" required>

                    <input type="date" id="check_out" name="check_out"
                        min="{{ now()->format('Y-m-d') }}"
                        class="w-full border rounded-full px-4 py-2" required>

                    <div id="pricePreview" class="hidden bg-green-50 p-3 rounded-xl text-center">
                        <p>Total Harga</p>
                        <p id="totalPrice" class="font-bold text-primary"></p>
                    </div>

                    <div class="flex gap-2 text-sm">
                        <input type="checkbox" id="terms">
                        <label>Saya setuju dengan syarat dan ketentuan </label>
                    </div>

                    <button id="bookingBtn"
                        type="submit"
                        class="w-full bg-gray-300 text-white py-3 rounded-full font-semibold transition">
                        Booking Sekarang
                    </button>

                </form>

                @else
                    <a href="/login"
                        class="block bg-primary text-white py-3 rounded-full text-center">
                        Login untuk booking
                    </a>
                @endauth

            </div>

        </div>

    </div>

</div>

<!-- SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    // HERO SLIDER
    let slides = document.querySelectorAll('.hero-slide');
    let index = 0;

    function show(i){
        slides.forEach(s => s.classList.replace('opacity-100','opacity-0'));
        slides[i].classList.replace('opacity-0','opacity-100');
    }

    document.getElementById('nextHero').onclick = () => {
        index = (index + 1) % slides.length;
        show(index);
    };

    document.getElementById('prevHero').onclick = () => {
        index = (index - 1 + slides.length) % slides.length;
        show(index);
    };

    setInterval(() => {
        if (!slides.length) return;
        index = (index + 1) % slides.length;
        show(index);
    }, 4000);

    // LIGHTBOX
    let thumbs = document.querySelectorAll('.thumb');
    let lightbox = document.getElementById('lightbox');
    let lightImg = document.getElementById('lightboxImg');
    let current = 0;

    thumbs.forEach((t,i)=>{
        t.onclick = () => {
            current = i;
            lightImg.src = t.src;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        }
    });

    document.getElementById('closeLightbox').onclick = () => {
        lightbox.classList.add('hidden');
    };

    document.getElementById('nextLight').onclick = () => {
        current = (current + 1) % thumbs.length;
        lightImg.src = thumbs[current].src;
    };

    document.getElementById('prevLight').onclick = () => {
        current = (current - 1 + thumbs.length) % thumbs.length;
        lightImg.src = thumbs[current].src;
    };

    // =====================
    // 💰 PRICE CALCULATION
    // =====================
    const checkIn = document.getElementById('check_in');
    const checkOut = document.getElementById('check_out');
    const priceBox = document.getElementById('pricePreview');
    const totalText = document.getElementById('totalPrice');

    const weekday = {{ $villa->weekday_price }};
    const weekend = {{ $villa->weekend_price }};

    function calculatePrice() {
        if (!checkIn.value || !checkOut.value) return;

        let start = new Date(checkIn.value);
        let end = new Date(checkOut.value);
        let total = 0;

        while (start < end) {
            let day = start.getDay();
            total += (day === 0 || day === 6) ? weekend : weekday;
            start.setDate(start.getDate() + 1);
        }

        if (total > 0) {
            totalText.innerText = 'Rp ' + total.toLocaleString();
            priceBox.classList.remove('hidden');
        }
    }

    checkIn?.addEventListener('change', () => {
        if (checkIn.value) {
            checkOut.min = checkIn.value;
        }
        calculatePrice();
    });

    checkOut?.addEventListener('change', calculatePrice);


    // =====================
    // ✅ TERMS CHECKBOX
    // =====================
    const btn = document.getElementById('bookingBtn');
    const terms = document.getElementById('terms');

    terms?.addEventListener('change', () => {
    if (terms.checked) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
});

});
</script>

@endsection