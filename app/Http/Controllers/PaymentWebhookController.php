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
            'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
            'gross_amount' => ['nullable', 'numeric'],
        ]);

        try {
            $verified = app(MidtransGateway::class)->verify($data['order_id']);
        } catch (\Throwable $exception) {
            Log::error('Midtrans verification failed.', ['order_id' => $data['order_id'], 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Verification failed.'], 502);
        }

        $transactionStatus = strtolower((string) ($verified['transaction_status'] ?? $data['transaction_status']));
        $paymentType = strtolower((string) ($verified['payment_type'] ?? $data['payment_type'] ?? ''));
        if ($paymentType !== 'qris') {
            return response()->json(['message' => 'Only QRIS transactions are accepted.'], 422);
        }
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
        if ($payment->status_pembayaran === 'PAID') {
            return response()->json(['message' => 'Already processed.']);
        }
        DB::transaction(function () use ($booking, $payment, $status, $transactionStatus, $transactionId, $verified): void {
            $payment->update([
                'status_pembayaran' => $status,
                'transaction_status' => $transactionStatus,
                'payment_type' => 'qris',
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
