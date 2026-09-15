<?php

namespace App\Services\PaymentGateway;

use App\Models\Pemesanan;
use Illuminate\Support\Str;

class DemoPaymentGateway implements PaymentGateway
{
    public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array
    {
        return $this->pendingPayment($booking, $amount ?? (float) $booking->total_harga, 'JGO-'.$booking->kode_booking);
    }

    public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array
    {
        return $this->pendingPayment($booking, $amount, 'JGO-'.$booking->kode_booking.'-CHG-'.strtoupper(Str::random(8)));
    }

    private function pendingPayment(Pemesanan $booking, float $amount, string $orderId): array
    {
        $reference = 'DEMO-'.strtoupper(Str::random(12));

        return [
            'status' => 'PENDING',
            'order_id' => $orderId,
            'transaction_id' => $reference,
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
            'gross_amount' => $amount,
            'reference' => $reference,
            'method' => 'QRIS',
            'qris_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='.rawurlencode('JEMBERGO-DEMO-QRIS|'.$orderId.'|'.number_format($amount, 2, '.', '')),
            'qris_expires_at' => now()->addHours(2)->toIso8601String(),
        ];
    }
}
