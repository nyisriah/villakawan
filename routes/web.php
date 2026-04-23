<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VillaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureEmailIsVerified;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🏠 Homepage (pakai controller, bukan welcome lagi)
Route::get('/', [HomeController::class, 'index']);


// 🔐 Redirect login
Route::redirect('/admin/login', '/login');
Route::redirect('/user/login', '/login');


// 🔐 Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});


// 🔓 Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


// ✉️ Email Verification Routes (Built-in Laravel)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verification.verify');
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('throttle:6,1')->name('verification.send');
});


// 🏡 Villa Routes
Route::get('/villas', [VillaController::class, 'index'])->name('villas.index');
Route::get('/villas/{villa:slug}', [VillaController::class, 'show'])->name('villas.show');

Route::middleware(['auth', EnsureEmailIsVerified::class])->group(function () {
    // 📅 Booking Routes
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/booking/{id}', [BookingController::class, 'show'])->name('bookings.show');

    // 💳 Payment Routes
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payment/{id}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payment/booking/{booking_id}', [PaymentController::class, 'create'])->name('payments.create');

    // Dashboard and invoice
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/invoice/{id}', [InvoiceController::class, 'show'])->name('invoice.show');

    // 👤 Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});

