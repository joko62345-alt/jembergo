<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PerubahanPemesanan;
use App\Services\BookingTicketService;
use App\Services\PaymentGateway\MidtransGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
            'gross_amount' => ['required', 'numeric'],
            'signature_key' => ['required', 'string'],
        ]);

        $expectedSignature = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].config('services.midtrans.server_key'));
        if (! hash_equals($expectedSignature, $data['signature_key'])) {
            return response()->json(['message' => 'Invalid notification signature.'], 403);
        }

        try {
            $verified = app(MidtransGateway::class)->verify($data['order_id']);
        } catch (\Throwable $exception) {
            Log::error('Midtrans verification failed.', ['order_id' => $data['order_id'], 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Verification failed.'], 502);
        }

        $transactionStatus = strtolower((string) ($verified['transaction_status'] ?? $data['transaction_status']));
        $paymentType = strtolower((string) ($verified['payment_type'] ?? $data['payment_type'] ?? ''));
        $status = $this->mapStatus($transactionStatus, $verified['fraud_status'] ?? null);
        $transactionId = $verified['transaction_id'] ?? $data['transaction_id'] ?? null;
        $change = PerubahanPemesanan::where('order_id', $data['order_id'])->with('pemesanan')->first();

        if ($change) {
            if ($change->status === 'PAID') {
                return response()->json(['message' => 'Already processed.']);
            }
            $change->update([
                'status' => $status,
                'transaction_status' => $transactionStatus,
                'payment_type' => 'qris',
                'transaction_id' => $transactionId,
                'gross_amount' => $verified['gross_amount'] ?? $change->gross_amount,
                'referensi_gateway' => $transactionId,
                'paid_at' => $status === 'PAID' ? now() : null,
                'waktu_pembayaran' => $status === 'PAID' ? now() : null,
            ]);
            if ($status === 'PAID') {
                app(BookingController::class)->applyChange($change->pemesanan->fresh(['destinasi', 'detailPemesanan.jenisTiket']), $change->fresh(), ['status' => 'PAID', 'method' => 'QRIS', 'reference' => $transactionId]);
            }

            return response()->json(['message' => 'Notification processed.']);
        }

        $payment = Pembayaran::where('order_id', $data['order_id'])->with('pemesanan')->first();
        if (! $payment) {
            return response()->json(['message' => 'Order not found.'], 404);
        }
        $booking = $payment->pemesanan;
        $verifiedAmount = (float) ($verified['gross_amount'] ?? $data['gross_amount'] ?? 0);
        if (abs($verifiedAmount - (float) $payment->nominal) > 0.01) {
            return response()->json(['message' => 'Transaction amount mismatch.'], 422);
        }
        if ($payment->status_pembayaran === 'PAID') {
            return response()->json(['message' => 'Already processed.']);
        }
        DB::transaction(function () use ($booking, $payment, $status, $transactionStatus, $paymentType, $transactionId, $verified): void {
            $payment->update([
                'status_pembayaran' => $status,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType ?: null,
                'transaction_id' => $transactionId,
                'gross_amount' => $verified['gross_amount'] ?? $payment->gross_amount,
                'referensi_gateway' => $transactionId,
                'paid_at' => $status === 'PAID' ? now() : null,
                'waktu_pembayaran' => $status === 'PAID' ? now() : null,
            ]);
            $booking->update(['status_pemesanan' => $status]);
            if ($status === 'PAID') {
                app(BookingTicketService::class)->issue($booking->fresh());
            }
        });

        return response()->json(['message' => 'Notification processed.']);
    }

    private function mapStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'settlement' => 'PAID',
            'capture' => strtolower((string) $fraudStatus) === 'challenge' ? 'PENDING' : 'PAID',
            'expire' => 'EXPIRED',
            'deny', 'cancel', 'failure' => 'FAILED',
            default => 'PENDING',
        };
    }
}
