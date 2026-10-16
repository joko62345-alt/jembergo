<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\TourismAdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/media/{path}', [ContentController::class, 'publicImage'])
        ->where('path', '.*')
        ->name('media.show');
    Route::get('/destinasi', [ContentController::class, 'destinations'])->name('destinations.index');
    Route::get('/destinasi/{id}', [ContentController::class, 'destination'])->whereNumber('id')->name('destinations.show');
    Route::get('/artikel', [ContentController::class, 'articles'])->name('articles.index');
    Route::get('/artikel/{id}', [ContentController::class, 'article'])->whereNumber('id')->name('articles.show');

    Route::get('/auth/google/config', [AuthController::class, 'googleConfig'])->name('auth.google.config');
    Route::post('/auth/google', [AuthController::class, 'googleLogin'])->middleware('throttle:10,1')->name('auth.google');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('auth.login');

    Route::middleware(['auth:sanctum', 'api.role:CUSTOMER'])->prefix('customer')->name('customer.')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::delete('/auth/token', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/profil', [CustomerController::class, 'profile'])->name('profile.show');
        Route::put('/profil', [CustomerController::class, 'updateProfile'])->name('profile.update');
        Route::get('/pesanan', [CustomerController::class, 'orders'])->name('orders.index');
        Route::get('/pesanan/{id}', [CustomerController::class, 'booking'])->whereNumber('id')->name('orders.show');
        Route::get('/pesanan/{id}/e-ticket/pdf', [BookingController::class, 'ticketPdf'])->whereNumber('id')->name('orders.ticket-pdf');
        Route::get('/pesanan/{id}/kelola', [BookingController::class, 'apiManage'])->whereNumber('id')->name('orders.manage');
        Route::post('/pesanan/{id}/kelola', [BookingController::class, 'reviewChanges'])->whereNumber('id')->name('orders.manage.review');
        Route::post('/pesanan/{id}/perubahan/{change}/pembayaran', [BookingController::class, 'payAdditional'])->whereNumber('id')->whereNumber('change')->name('change-payments.store');
        Route::get('/pesanan/{id}/perubahan/{change}/pembayaran', [BookingController::class, 'additionalQrisPayment'])->whereNumber('id')->whereNumber('change')->name('change-payments.show');
        Route::get('/pesanan/{id}/perubahan/{change}/pembayaran/qr-image', [BookingController::class, 'apiAdditionalQrisImage'])->whereNumber('id')->whereNumber('change')->name('change-payments.qr-image');
        Route::get('/destinasi/{id}/kuota', [BookingController::class, 'quotaAvailability'])->whereNumber('id')->name('destinations.quota');
        Route::post('/destinasi/{id}/pemesanan', [BookingController::class, 'store'])->whereNumber('id')->name('orders.store');
        Route::post('/pesanan/{id}/pembayaran', [BookingController::class, 'pay'])->whereNumber('id')->name('payments.store');
        Route::get('/pesanan/{id}/pembayaran/qr-image', [BookingController::class, 'apiQrisImage'])->whereNumber('id')->name('payments.qr-image');
        Route::get('/pesanan/{id}/pembayaran', [BookingController::class, 'checkout'])->whereNumber('id')->name('payments.show');
        Route::post('/tiket/{id}/ulasan', [ReviewController::class, 'store'])->whereNumber('id')->name('reviews.store');
    });

    Route::middleware(['auth:sanctum', 'api.role:ADMIN_PARIWISATA'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::delete('/auth/token', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/dashboard', [TourismAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/pemesanan', [TourismAdminController::class, 'bookings'])->name('orders.index');
        Route::put('/kuota', [TourismAdminController::class, 'updateQuota'])->name('quota.update');
        Route::post('/verifikasi-tiket', [TourismAdminController::class, 'verifyTicket'])->name('tickets.verify');
    });
});
