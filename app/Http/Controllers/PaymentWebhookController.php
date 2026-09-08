<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-JemberGo-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), (string) config('services.payment_gateway.webhook_secret'));
        abort_unless($signature && hash_equals($expected, $signature), 401, 'Signature webhook tidak valid.');

        $data = $request->validate(['kode_booking' => ['required', 'string'], 'status' => ['required', 'in:PAID,FAILED,EXPIRED'], 'referensi_gateway' => ['nullable', 'string']]);
        $booking = Pemesanan::where('kode_booking', $data['kode_booking'])->firstOrFail();
        $booking->update(['status_pemesanan' => $data['status']]);
        $booking->pembayaran()->update(['status_pembayaran' => $data['status'], 'referensi_gateway' => $data['referensi_gateway'] ?? null, 'waktu_pembayaran' => $data['status'] === 'PAID' ? now() : null]);

        return response()->json(['message' => 'Webhook diterima.']);
    }
}