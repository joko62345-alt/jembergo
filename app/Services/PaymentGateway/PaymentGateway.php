<?php

namespace App\Services\PaymentGateway;

use App\Models\Pemesanan;

interface PaymentGateway
{
    public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array;

    public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array;
}
