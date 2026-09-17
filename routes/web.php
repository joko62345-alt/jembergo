<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminManagementController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/assets/jembergo-logo.png', fn () => response()->file(resource_path('images/logo.png')))->name('assets.logo');
Route::get('/assets/jembergo-hero.png', fn () => response()->file(resource_path('images/hero.png')))->name('assets.hero');
Route::get('/assets/jembergo-hero-2.png', fn () => response()->file(resource_path('images/hero2.png')))->name('assets.hero2');
Route::get('/assets/jembergo-hero-3.png', fn () => response()->file(resource_path('images/hero3.png')))->name('assets.hero3');
Route::get('/assets/jembergo-hero-4.png', fn () => response()->file(resource_path('images/hero4.png')))->name('assets.hero4');
Route::get('/assets/jembergo-hero-6.png', fn () => response()->file(resource_path('images/hero6.png')))->name('assets.hero6');
Route::get('/assets/jembergo-background.png', fn () => response()->file(resource_path('images/bg.png')))->name('assets.background');
Route::get('/assets/jembergo-login.png', fn () => response()->file(resource_path('images/login.png')))->name('assets.login');
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/tentang', fn () => redirect('/#tentang'))->name('about');
Route::get('/destinasi', [PublicController::class, 'destinations'])->name('destinations.index');
Route::get('/destinasi/cari', [PublicController::class, 'destinations'])->name('destinations.search');
Route::get('/destinasi/{id}', [PublicController::class, 'destination'])->whereNumber('id')->name('destinations.show');
Route::get('/artikel', [PublicController::class, 'articles'])->name('articles.index');
Route::get('/artikel/{id}', [PublicController::class, 'article'])->whereNumber('id')->name('articles.show');
Route::post('/payment/midtrans/notification', [PaymentWebhookController::class, 'handle'])->name('payment.midtrans.notification');
Route::post('/api/midtrans/notification', [PaymentWebhookController::class, 'handle'])->name('api.midtrans.notification');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [PasswordController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('role:SUPER_ADMIN,ADMIN_PARIWISATA')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('role:SUPER_ADMIN,ADMIN_PARIWISATA,CUSTOMER')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('role:CUSTOMER')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/pesanan', [CustomerDashboardController::class, 'orders'])->name('orders');
    Route::get('/tiket-saya', [CustomerDashboardController::class, 'activeTickets'])->name('tickets');
    Route::get('/riwayat-tiket', [CustomerDashboardController::class, 'ticketHistory'])->name('ticket-history');
    Route::get('/profil', [CustomerDashboardController::class, 'profile'])->name('profile');
    Route::put('/profil', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/pemesanan/{id}/buat', [BookingController::class, 'create'])->whereNumber('id')->name('booking.create');
    Route::get('/destinasi/{id}/kuota', [BookingController::class, 'quotaAvailability'])->whereNumber('id')->name('booking.quota');
    Route::post('/pemesanan/{id}', [BookingController::class, 'store'])->whereNumber('id')->name('booking.store');
    Route::get('/pemesanan/{id}/checkout', [BookingController::class, 'checkout'])->whereNumber('id')->name('checkout');
    Route::post('/pemesanan/{id}/bayar', [BookingController::class, 'pay'])->whereNumber('id')->name('payment');
    Route::get('/pemesanan/{id}/qris', [BookingController::class, 'qrisPayment'])->whereNumber('id')->name('qris');
    Route::post('/pemesanan/{id}/reschedule', [BookingController::class, 'reschedule'])->whereNumber('id')->name('reschedule');
    Route::post('/pemesanan/{id}/anggota', [BookingController::class, 'addMembers'])->whereNumber('id')->name('members.add');
    Route::get('/pemesanan/{id}/kelola', [BookingController::class, 'manage'])->whereNumber('id')->name('manage');
    Route::post('/pemesanan/{id}/kelola/review', [BookingController::class, 'reviewChanges'])->whereNumber('id')->name('manage.review');
    Route::get('/pemesanan/{id}/perubahan/{change}/bayar', [BookingController::class, 'additionalCheckout'])->whereNumber('id')->whereNumber('change')->name('change.checkout');
    Route::post('/pemesanan/{id}/perubahan/{change}/bayar', [BookingController::class, 'payAdditional'])->whereNumber('id')->whereNumber('change')->name('change.payment');
    Route::get('/pemesanan/{id}/perubahan/{change}/qris', [BookingController::class, 'additionalQrisPayment'])->whereNumber('id')->whereNumber('change')->name('change.qris');
    Route::get('/pemesanan/{id}/e-ticket', [BookingController::class, 'ticket'])->whereNumber('id')->name('ticket');
    Route::get('/pemesanan/{id}/e-ticket/pdf', [BookingController::class, 'ticketPdf'])->whereNumber('id')->name('ticket.pdf');
    Route::get('/tiket/{id}/review', [ReviewController::class, 'create'])->whereNumber('id')->name('review.create');
    Route::post('/tiket/{id}/review', [ReviewController::class, 'store'])->whereNumber('id')->name('review.store');
});

Route::middleware('role:CUSTOMER')->group(function () {
    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'orders'])->name('customer.dashboard');
    Route::get('/customer/profil/edit', [CustomerDashboardController::class, 'profile'])->name('profile.edit');
});

Route::middleware('role:ADMIN_PARIWISATA')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verifikasi-tiket', [VerificationController::class, 'index'])->name('verification');
    Route::post('/verifikasi-tiket', [VerificationController::class, 'lookup'])->name('verification.lookup');
    Route::post('/verifikasi-tiket/konfirmasi', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::get('/pemesanan-destinasi', [VerificationController::class, 'bookings'])->name('bookings');
    Route::get('/riwayat-verifikasi', [VerificationController::class, 'history'])->name('verification.history');
    Route::put('/pengaturan-kuota', [DashboardController::class, 'updateQuota'])->name('quota.update');
});

Route::middleware('role:SUPER_ADMIN')->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/destinasi', [SuperAdminController::class, 'destinations'])->name('destinations');
    Route::post('/destinasi', [SuperAdminController::class, 'storeDestination'])->name('destinations.store');
    Route::put('/destinasi/{id}', [SuperAdminController::class, 'updateDestination'])->whereNumber('id')->name('destinations.update');
    Route::delete('/destinasi/{id}', [SuperAdminController::class, 'destroyDestination'])->whereNumber('id')->name('destinations.destroy');
    Route::get('/laporan', [SuperAdminController::class, 'report'])->name('report');
    Route::get('/laporan/export', [SuperAdminManagementController::class, 'export'])->name('report.export');
    Route::get('/management', [SuperAdminManagementController::class, 'index'])->name('management');
    Route::get('/management/admin/buat', [SuperAdminManagementController::class, 'createAdmin'])->name('management.admin.create');
    Route::get('/management/admin', [SuperAdminManagementController::class, 'admins'])->name('management.admins');
    Route::get('/management/customer', [SuperAdminManagementController::class, 'customers'])->name('management.customers');
    Route::get('/artikel/tulis', [SuperAdminManagementController::class, 'createArticle'])->name('articles.create');
    Route::get('/artikel', [SuperAdminManagementController::class, 'articles'])->name('articles');
    Route::get('/artikel/{id}/edit', [SuperAdminManagementController::class, 'editArticle'])->whereNumber('id')->name('articles.edit');
    Route::put('/artikel/{id}', [SuperAdminManagementController::class, 'updateArticle'])->whereNumber('id')->name('articles.update');
    Route::post('/management/fasilitas', [SuperAdminManagementController::class, 'facility'])->name('management.facility');
    Route::post('/management/galeri', [SuperAdminManagementController::class, 'gallery'])->name('management.gallery');
    Route::delete('/management/fasilitas/{id}', [SuperAdminManagementController::class, 'destroyFacility'])->whereNumber('id')->name('management.facility.destroy');
    Route::delete('/management/galeri/{id}', [SuperAdminManagementController::class, 'destroyGallery'])->whereNumber('id')->name('management.gallery.destroy');
    Route::post('/artikel', [SuperAdminManagementController::class, 'article'])->name('articles.store');
    Route::delete('/artikel/{id}', [SuperAdminManagementController::class, 'destroyArticle'])->whereNumber('id')->name('articles.destroy');
    Route::post('/management/admin', [SuperAdminManagementController::class, 'admin'])->name('management.admin');
    Route::delete('/management/admin/{id}', [SuperAdminManagementController::class, 'destroyAdmin'])->whereNumber('id')->name('management.admin.destroy');
});
