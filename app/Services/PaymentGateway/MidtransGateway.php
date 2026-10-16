<?php

namespace App\Services\PaymentGateway;

use App\Models\Pemesanan;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransGateway implements PaymentGateway
{
    public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array
    {
        $orderId = 'JGO-'.$booking->kode_booking.'-'.strtoupper(Str::random(8));

        return $this->charge($booking, $amount ?? (float) $booking->total_harga, $orderId);
    }

    public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array
    {
        return $this->charge($booking, $amount, 'JGO-'.$booking->kode_booking.'-CHG-'.strtoupper(Str::random(8)));
    }

    public function verify(string $orderId): array
    {
        $response = $this->client()->get('/v2/'.rawurlencode($orderId).'/status');
        if ($response->failed()) {
            throw new RuntimeException('Midtrans gagal memverifikasi transaksi.');
        }

        return $response->json();
    }

    public function fetchQrCodeImage(string $url): Response
    {
        $parsedUrl = parse_url($url);
        $expectedHost = config('services.midtrans.is_production')
            ? 'api.midtrans.com'
            : 'api.sandbox.midtrans.com';
        $path = $parsedUrl['path'] ?? '';

        if (
            ($parsedUrl['scheme'] ?? null) !== 'https'
            || ($parsedUrl['host'] ?? null) !== $expectedHost
            || ! str_starts_with($path, '/v2/qris/')
        ) {
            throw new RuntimeException('URL kode pembayaran dari Midtrans tidak valid.');
        }

        $requestPath = $path.(isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '');
        $response = $this->client()->get($requestPath);
        if ($response->failed() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
            throw new RuntimeException('Gambar kode pembayaran tidak dapat diambil dari Midtrans.');
        }

        return $response;
    }

    public function checkoutUrl(string $snapToken): string
    {
        $host = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        return $host.'/snap/v2/vtweb/'.rawurlencode($snapToken);
    }

    private function charge(Pemesanan $booking, float $amount, string $orderId): array
    {
        abort_if($amount <= 0, 422, 'Nominal pembayaran harus lebih besar dari nol.');
        abort_if(! trim((string) config('services.midtrans.server_key')), 503, 'Midtrans belum dikonfigurasi. Isi credential Sandbox di .env.');

        $response = $this->client()->post('/v2/charge', [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'qris' => ['acquirer' => 'gopay'],
            'custom_expiry' => ['expiry_duration' => 2, 'unit' => 'hour'],
        ]);

        $payload = $response->json();
        if ($response->failed() || (string) ($payload['status_code'] ?? '200') !== '201') {
            throw new RuntimeException((string) ($payload['status_message'] ?? 'Transaksi QRIS gagal dibuat di Midtrans.'));
        }

        $qrAction = collect($payload['actions'] ?? [])->firstWhere('name', 'generate-qr-code');
        $qrUrl = $qrAction['url'] ?? null;
        if (! $qrUrl) {
            throw new RuntimeException('Midtrans tidak mengembalikan QR Code QRIS.');
        }

        return [
            'status' => 'PENDING',
            'order_id' => $payload['order_id'] ?? $orderId,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? 'pending',
            'payment_type' => 'qris',
            'gross_amount' => (float) ($payload['gross_amount'] ?? $amount),
            'reference' => $payload['transaction_id'] ?? null,
            'method' => 'QRIS',
            'qris_url' => $qrUrl,
            'qris_expires_at' => $payload['expiry_time'] ?? now()->addHours(2)->toIso8601String(),
        ];
    }

    private function client(): PendingRequest
    {
        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        return Http::baseUrl($baseUrl)
            ->withBasicAuth(trim((string) config('services.midtrans.server_key')), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
