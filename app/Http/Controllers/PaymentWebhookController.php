<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Services\BookingTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-JemberGo-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), (string) config('services.payment_gateway.webhook_secret'));
        abort_unless($signature && hash_equals($expected, $signature), 401, 'Signature webhook tidak valid.');

        $data = $request->validate(['kode_booking' => ['required', 'string'], 'status' => ['required', 'in:PAID,FAILED,EXPIRED'], 'referensi_gateway' => ['nullable', 'string']]);
        $booking = Pemesanan::where('kode_booking', $data['kode_booking'])->firstOrFail();
        DB::transaction(function () use ($booking, $data): void {
            $booking->update(['status_pemesanan' => $data['status']]);
            $booking->pembayaran()->update([
                'status_pembayaran' => $data['status'],
                'referensi_gateway' => $data['referensi_gateway'] ?? null,
                'waktu_pembayaran' => $data['status'] === 'PAID' ? now() : null,
            ]);

            if ($data['status'] === 'PAID') {
                app(BookingTicketService::class)->issue($booking);
            }
        });

        return response()->json(['message' => 'Webhook diterima.']);
    }
}