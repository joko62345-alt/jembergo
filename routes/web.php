<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminManagementController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/tentang', fn () => redirect('/#tentang'))->name('about');
Route::get('/destinasi', [PublicController::class, 'destinations'])->name('destinations.index');
Route::get('/destinasi/cari', [PublicController::class, 'destinations'])->name('destinations.search');
Route::get('/destinasi/{id}', [PublicController::class, 'destination'])->whereNumber('id')->name('destinations.show');
Route::get('/artikel', [PublicController::class, 'articles'])->name('articles.index');
Route::get('/artikel/{id}', [PublicController::class, 'article'])->whereNumber('id')->name('articles.show');
Route::post('/payment/webhook', [PaymentWebhookController::class, 'handle'])->name('payment.webhook');

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('login.store');
	Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
	Route::post('/register', [AuthController::class, 'register'])->name('register.store');
	Route::get('/forgot-password', [PasswordController::class, 'forgot'])->name('password.request');
	Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
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
	Route::post('/pemesanan/{id}', [BookingController::class, 'store'])->whereNumber('id')->name('booking.store');
	Route::get('/pemesanan/{id}/checkout', [BookingController::class, 'checkout'])->whereNumber('id')->name('checkout');
	Route::post('/pemesanan/{id}/bayar', [BookingController::class, 'pay'])->whereNumber('id')->name('payment');
	Route::get('/pemesanan/{id}/e-ticket', [BookingController::class, 'ticket'])->whereNumber('id')->name('ticket');
	Route::post('/pemesanan/{id}/batalkan', [BookingController::class, 'cancel'])->whereNumber('id')->name('cancel');
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
	Route::get('/management/artikel/tulis', [SuperAdminManagementController::class, 'createArticle'])->name('management.article.create');
	Route::post('/management/fasilitas', [SuperAdminManagementController::class, 'facility'])->name('management.facility');
	Route::post('/management/galeri', [SuperAdminManagementController::class, 'gallery'])->name('management.gallery');
	Route::post('/management/jenis-tiket', [SuperAdminManagementController::class, 'ticketType'])->name('management.ticket-type');
	Route::delete('/management/fasilitas/{id}', [SuperAdminManagementController::class, 'destroyFacility'])->whereNumber('id')->name('management.facility.destroy');
	Route::delete('/management/galeri/{id}', [SuperAdminManagementController::class, 'destroyGallery'])->whereNumber('id')->name('management.gallery.destroy');
	Route::delete('/management/jenis-tiket/{id}', [SuperAdminManagementController::class, 'destroyTicketType'])->whereNumber('id')->name('management.ticket-type.destroy');
	Route::post('/management/artikel', [SuperAdminManagementController::class, 'article'])->name('management.article');
	Route::delete('/management/artikel/{id}', [SuperAdminManagementController::class, 'destroyArticle'])->whereNumber('id')->name('management.article.destroy');
	Route::post('/management/admin', [SuperAdminManagementController::class, 'admin'])->name('management.admin');
	Route::delete('/management/admin/{id}', [SuperAdminManagementController::class, 'destroyAdmin'])->whereNumber('id')->name('management.admin.destroy');
});
