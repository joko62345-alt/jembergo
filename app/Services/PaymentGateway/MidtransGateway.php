<?php

namespace App\Services\PaymentGateway;

use App\Models\Pemesanan;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransGateway implements PaymentGateway
{
    public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array
    {
        return $this->createSnapPayment($booking, $amount ?? (float) $booking->total_harga);
    }

    public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array
    {
        return $this->createSnapPayment($booking, $amount, 'JGO-'.$booking->kode_booking.'-CHG-'.strtoupper(Str::random(8)));
    }

    public function verify(string $orderId): array
    {
        $response = $this->client()->get('/v2/'.rawurlencode($orderId).'/status');
        if ($response->failed()) {
            throw new RuntimeException('Midtrans gagal memverifikasi transaksi.');
        }

        return $response->json();
    }

    private function createSnapPayment(Pemesanan $booking, float $amount, ?string $customOrderId = null): array
    {
        abort_if($amount <= 0, 422, 'Nominal pembayaran harus lebih besar dari nol.');
        abort_if(! trim((string) config('services.midtrans.server_key')), 503, 'Midtrans belum dikonfigurasi. Isi credential Sandbox di .env.');

        $booking->loadMissing(['customer', 'detailPemesanan.jenisTiket']);
        $orderId = $customOrderId ?? 'JGO-'.$booking->kode_booking;
        $response = $this->client()->post('/snap/v1/transactions', [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $booking->ketua_nama,
                'email' => $booking->ketua_email,
                'phone' => $booking->ketua_no_hp,
            ],
            'item_details' => $booking->detailPemesanan->map(fn ($detail): array => [
                'id' => (string) $detail->id_jenis_tiket,
                'price' => (int) round($detail->subtotal / max(1, $detail->jumlah)),
                'quantity' => (int) $detail->jumlah,
                'name' => $detail->jenisTiket->nama_jenis,
            ])->values()->all(),
        ]);

        if ($response->failed() || ! $response->json('token')) {
            throw new RuntimeException((string) ($response->json('error_messages.0') ?: 'Snap token gagal dibuat di Midtrans.'));
        }

        return [
            'status' => 'PENDING',
            'order_id' => $orderId,
            'snap_token' => $response->json('token'),
            'transaction_id' => null,
            'transaction_status' => 'pending',
            'payment_type' => null,
            'gross_amount' => $amount,
            'reference' => null,
            'method' => 'SNAP',
            'qris_url' => null,
            'qris_expires_at' => null,
        ];
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
            throw new RuntimeException((string) ($payload['status_message'] ?: 'Transaksi QRIS gagal dibuat di Midtrans.'));
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
