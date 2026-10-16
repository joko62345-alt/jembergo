<?php

namespace Tests\Unit;

use App\Models\Pemesanan;
use App\Services\PaymentGateway\MidtransGateway;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MidtransGatewayTest extends TestCase
{
    public function test_creates_a_qris_mpm_transaction_and_returns_its_qr_url(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.is_production' => false,
        ]);
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'QRIS transaction is created',
                'order_id' => 'JGO-JGO-20261016-ABCDE-ABCDEFGH',
                'transaction_id' => 'sandbox-transaction',
                'transaction_status' => 'pending',
                'payment_type' => 'qris',
                'gross_amount' => '39000.00',
                'expiry_time' => '2026-10-16 16:00:00',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'url' => 'https://api.sandbox.midtrans.com/v2/qris/qr-code',
                    ],
                ],
            ], 201),
        ]);

        $booking = new Pemesanan;
        $booking->kode_booking = 'JGO-20261016-ABCDE';
        $booking->total_harga = 39000;

        $payment = app(MidtransGateway::class)->createPayment($booking, 'QRIS');

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.sandbox.midtrans.com/v2/charge'
            && $request['payment_type'] === 'qris'
            && $request['qris']['acquirer'] === 'gopay'
            && $request['transaction_details']['gross_amount'] === 39000
            && str_starts_with($request['transaction_details']['order_id'], 'JGO-JGO-20261016-ABCDE-'));

        $this->assertSame('PENDING', $payment['status']);
        $this->assertSame('qris', $payment['payment_type']);
        $this->assertSame('https://api.sandbox.midtrans.com/v2/qris/qr-code', $payment['qris_url']);
        $this->assertSame('sandbox-transaction', $payment['transaction_id']);
    }

    public function test_rejects_qris_response_without_a_qr_code_url(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.is_production' => false,
        ]);
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'QRIS transaction is created',
                'actions' => [],
            ], 201),
        ]);

        $booking = new Pemesanan;
        $booking->kode_booking = 'JGO-20261016-ABCDE';
        $booking->total_harga = 39000;

        $this->expectException(RuntimeException::class);

        app(MidtransGateway::class)->createPayment($booking, 'QRIS');
    }

    public function test_fetches_qris_image_from_midtrans_with_server_authentication(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.is_production' => false,
        ]);
        Http::fake([
            'api.sandbox.midtrans.com/v2/qris/gopay/*/qr-code' => Http::response('image-data', 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $response = app(MidtransGateway::class)->fetchQrCodeImage(
            'https://api.sandbox.midtrans.com/v2/qris/gopay/example/qr-code',
        );

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.sandbox.midtrans.com/v2/qris/gopay/example/qr-code'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sandbox-server-key:')));
        $this->assertSame('image/png', $response->header('Content-Type'));
        $this->assertSame('image-data', $response->body());
    }

    public function test_rejects_qris_image_urls_outside_the_configured_midtrans_environment(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.is_production' => false,
        ]);

        $this->expectException(RuntimeException::class);

        app(MidtransGateway::class)->fetchQrCodeImage(
            'https://example.test/v2/qris/gopay/example/qr-code',
        );
    }
}
