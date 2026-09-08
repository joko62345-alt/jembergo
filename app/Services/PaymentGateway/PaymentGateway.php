<?php

namespace App\Services\PaymentGateway;

use App\Models\Pemesanan;

interface PaymentGateway
{
    public function createPayment(Pemesanan $booking, string $method): array;
}