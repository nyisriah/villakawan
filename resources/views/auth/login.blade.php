@extends('layouts.app')

@section('content')

<div class="min-h-screen flex items-center justify-center relative">

    <!-- 🌄 BACKGROUND -->
    <div class="absolute inset-0">
        <img src="https://picsum.photos/1920/1080?random=99"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/60"></div>
    </div>

    <!-- ✨ CONTENT -->
    <div class="relative z-10 w-full max-w-6xl grid grid-cols-1 md:grid-cols-2 gap-8 items-center px-6">

        <!-- 📝 LEFT TEXT -->
        <div class="text-white hidden md:block">
            <h1 class="text-4xl font-bold mb-4 leading-tight">
                Selamat Datang Kembali 👋
            </h1>
            <p class="text-gray-200 text-lg">
                Masuk untuk melanjutkan perjalanan liburan Anda bersama kami.
            </p>
        </div>

        <!-- 🔐 LOGIN FORM -->
        <div class="backdrop-blur-md bg-white/10 border border-white/20 p-8 rounded-3xl shadow-xl">

            <h2 class="text-2xl font-bold text-white mb-6 text-center">
                Login
            </h2>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-200 text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-4">
                @csrf

                <!-- EMAIL -->
                <input type="email" name="email" placeholder="Email"
                    class="w-full px-4 py-3 rounded-full bg-white/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 @error('email') ring-2 ring-red-500 @enderror"
                    value="{{ old('email') }}"
                    required>
                @error('email')<span class="text-red-300 text-sm">{{ $message }}</span>@enderror

                <!-- PASSWORD -->
                <input type="password" name="password" placeholder="Password"
                    class="w-full px-4 py-3 rounded-full bg-white/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 @error('password') ring-2 ring-red-500 @enderror"
                    required>
                @error('password')<span class="text-red-300 text-sm">{{ $message }}</span>@enderror

                <!-- ACTION -->
                <div class="flex justify-between items-center text-sm text-gray-200">
                    <a href="/forgot-password" class="hover:underline">
                        Lupa Password?
                    </a>
                </div>
                <div class="text-center text-sm text-gray-200 mt-4">
    Belum punya akun?
    <a href="/register" class="text-white font-semibold hover:underline">
        Daftar sekarang
    </a>
</div>
                <!-- BUTTON -->
                <button class="w-full bg-primary text-white py-3 rounded-full hover:bg-green-700 transition shadow-lg">
                    Login
                </button>
            </form>

        </div>

    </div>

</div>

@endsection