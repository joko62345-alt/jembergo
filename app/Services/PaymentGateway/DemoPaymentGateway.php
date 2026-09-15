<?php

namespace App\Services\PaymentGateway;

use App\Models\Pemesanan;
use Illuminate\Support\Str;

class DemoPaymentGateway implements PaymentGateway
{
    public function createPayment(Pemesanan $booking, string $method): array
    {
        return ['status' => 'PAID', 'reference' => 'DEMO-' . strtoupper(Str::random(12)), 'method' => 'QRIS'];
    }
}