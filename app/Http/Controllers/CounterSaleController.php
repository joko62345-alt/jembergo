<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Services\BookingTicketService;
use App\Services\PaymentGateway\MidtransGateway;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CounterSaleController extends Controller
{
    public function create(): View
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $destination = DestinasiWisata::with('jenisTiket')->findOrFail($admin->id_destinasi);
        $quotaEnabled = $destination->kuota_harian_aktif === 'aktif';
        $remainingQuota = $quotaEnabled
            ? max(0, (int) $destination->kuota_harian - $destination->bookedTicketsForDate(today()->toDateString()))
            : null;
        $saleIsOpen = $destination->isOpenAt(now());

        return view('admin.counter-sale', compact('admin', 'destination', 'quotaEnabled', 'remainingQuota', 'saleIsOpen'));
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $data = $request->validate([
            'jumlah' => ['required', 'array'],
            'jumlah.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'metode_pembayaran' => ['required', 'in:TUNAI,QRIS'],
            'uang_diterima' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'required_if:metode_pembayaran,TUNAI'],
        ]);
        $quantities = collect($data['jumlah'])
            ->mapWithKeys(fn (mixed $quantity, string|int $ticketTypeId): array => [(int) $ticketTypeId => (int) ($quantity ?? 0)])
            ->filter(fn (int $quantity): bool => $quantity > 0);

        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages([
                'jumlah' => 'Masukkan jumlah minimal satu tiket.',
            ]);
        }
        if ($quantities->sum() > 100000) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah tiket dalam satu transaksi maksimal 100.000.',
            ]);
        }

        $booking = DB::transaction(function () use ($admin, $data, $quantities): Pemesanan {
            $destination = DestinasiWisata::query()
                ->lockForUpdate()
                ->findOrFail($admin->id_destinasi);
            abort_if($destination->status_aktif !== 'aktif', 422, 'Destinasi ini sedang tidak aktif.');
            $now = now();
            if (! $destination->isOpenAt($now)) {
                throw ValidationException::withMessages([
                    'jam_operasional' => 'Penjualan tiket hanya tersedia selama jam operasional destinasi.',
                ]);
            }

            $ticketTypes = $destination->jenisTiket()
                ->whereIn('id_jenis_tiket', $quantities->keys())
                ->get()
                ->keyBy('id_jenis_tiket');
            if ($ticketTypes->count() !== $quantities->count()) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jenis tiket yang dipilih tidak tersedia untuk destinasi ini.',
                ]);
            }

            $ticketCount = $quantities->sum();
            if ($destination->kuota_harian_aktif === 'aktif') {
                $alreadyBooked = $destination->bookedTicketsForDate(today()->toDateString());
                $remaining = max(0, (int) $destination->kuota_harian - $alreadyBooked);
                if ($ticketCount > $remaining) {
                    throw ValidationException::withMessages([
                        'jumlah' => "Kuota hari ini tersisa {$remaining} tiket.",
                    ]);
                }
            }

            $total = $quantities->sum(
                fn (int $quantity, int $ticketTypeId): float => (float) $ticketTypes[$ticketTypeId]->harga * $quantity
            );
            $cashReceived = $data['metode_pembayaran'] === 'TUNAI'
                ? round((float) $data['uang_diterima'], 2)
                : null;
            if ($cashReceived !== null && $cashReceived < $total) {
                throw ValidationException::withMessages([
                    'uang_diterima' => 'Uang yang diterima kurang dari total pembayaran.',
                ]);
            }

            $booking = Pemesanan::create([
                'id_customer' => null,
                'id_destinasi' => $destination->id_destinasi,
                'kanal_penjualan' => 'LOKET',
                'id_admin_pariwisata' => $admin->id_admin,
                'kode_booking' => 'JGO-'.$now->format('Ymd').'-'.strtoupper(Str::random(5)),
                'tanggal_pemesanan' => $now,
                'tanggal_kunjungan' => today()->toDateString(),
                'total_harga' => $total,
                'status_pemesanan' => $data['metode_pembayaran'] === 'TUNAI' ? 'PAID' : 'PENDING',
                'batas_waktu_pembayaran' => $data['metode_pembayaran'] === 'QRIS' ? $now->copy()->addHours(2) : null,
            ]);

            foreach ($quantities as $ticketTypeId => $quantity) {
                $booking->detailPemesanan()->create([
                    'id_jenis_tiket' => $ticketTypeId,
                    'jumlah' => $quantity,
                    'subtotal' => (float) $ticketTypes[$ticketTypeId]->harga * $quantity,
                ]);
            }

            $method = $data['metode_pembayaran'] === 'QRIS' ? 'QRIS' : 'TUNAI';
            $booking->pembayaran()->create([
                'metode_pembayaran' => $method,
                'nominal' => $total,
                'uang_diterima' => $cashReceived,
                'uang_kembalian' => $cashReceived === null ? null : round($cashReceived - $total, 2),
                'gross_amount' => $total,
                'status_pembayaran' => $data['metode_pembayaran'] === 'TUNAI' ? 'PAID' : 'PENDING',
                'payment_type' => strtolower($method),
                'waktu_pembayaran' => $data['metode_pembayaran'] === 'TUNAI' ? $now : null,
                'paid_at' => $data['metode_pembayaran'] === 'TUNAI' ? $now : null,
            ]);

            if ($data['metode_pembayaran'] === 'TUNAI') {
                app(BookingTicketService::class)->issueForCounterSale($booking);
            }

            return $booking->fresh(['destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket']);
        });

        if ($data['metode_pembayaran'] === 'QRIS') {
            try {
                $payment = app(PaymentGateway::class)->createPayment($booking, 'QRIS');
                if (empty($payment['qris_url'])) {
                    throw new \RuntimeException('Midtrans belum menyiapkan QRIS untuk pembayaran ini.');
                }
            } catch (\Throwable $exception) {
                report($exception);
                $booking->update(['status_pemesanan' => 'FAILED']);
                $booking->pembayaran()->update([
                    'status_pembayaran' => 'FAILED',
                    'transaction_status' => 'failure',
                ]);

                return redirect()->route('admin.counter-sales.create')
                    ->withInput()
                    ->withErrors(['payment' => 'Pembayaran QRIS Midtrans belum dapat dibuat: '.$exception->getMessage()]);
            }

            $booking->pembayaran()->update([
                'order_id' => $payment['order_id'],
                'snap_token' => $payment['snap_token'] ?? null,
                'transaction_id' => $payment['transaction_id'] ?? null,
                'metode_pembayaran' => 'QRIS',
                'status_pembayaran' => 'PENDING',
                'transaction_status' => $payment['transaction_status'] ?? 'pending',
                'payment_type' => $payment['payment_type'] ?? 'qris',
                'gross_amount' => $payment['gross_amount'] ?? $booking->total_harga,
                'referensi_gateway' => $payment['reference'] ?? null,
                'qris_url' => $payment['qris_url'],
                'qris_expires_at' => $payment['qris_expires_at'] ?? null,
            ]);

            return redirect()->route('admin.counter-sales.qris', $booking->id_pemesanan);
        }

        return redirect()->route('admin.counter-sales.show', $booking->id_pemesanan);
    }

    public function qrisPayment(Request $request, int $id): View|JsonResponse|RedirectResponse
    {
        $booking = $this->counterBooking($id);
        $payment = $booking->pembayaran;
        abort_if(! $payment || $payment->metode_pembayaran !== 'QRIS', 404);

        if ($request->expectsJson()) {
            if (! $this->syncQrisPaymentStatus($booking)) {
                return response()->json([
                    'message' => 'Status pembayaran belum dapat diverifikasi oleh Midtrans. Coba lagi sebentar.',
                ], 502);
            }

            return response()->json(['status' => $payment->fresh()->status_pembayaran]);
        }

        if ($payment->status_pembayaran === 'PAID') {
            return redirect()->route('admin.counter-sales.show', $id);
        }

        abort_if(! $payment->qris_url, 404, 'Kode QRIS pembayaran tidak ditemukan.');

        return view('admin.counter-sale-qris', [
            'booking' => $booking,
            'payment' => $payment,
            'change' => null,
        ]);
    }

    public function qrisImage(int $id): Response
    {
        $booking = $this->counterBooking($id);
        abort_if(! $booking->pembayaran?->qris_url, 404, 'Gambar kode pembayaran tidak ditemukan.');

        $image = app(MidtransGateway::class)->fetchQrCodeImage($booking->pembayaran->qris_url);

        return response($image->body(), 200, [
            'Content-Type' => $image->header('Content-Type'),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function show(int $id): View|RedirectResponse
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $booking = Pemesanan::query()
            ->where('id_destinasi', $admin->id_destinasi)
            ->where('kanal_penjualan', 'LOKET')
            ->with(['destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket'])
            ->findOrFail($id);
        if ($booking->pembayaran?->metode_pembayaran === 'QRIS' && $booking->status_pemesanan !== 'PAID') {
            return redirect()->route('admin.counter-sales.qris', $id);
        }

        return view('admin.verification-receipt', compact('booking'));
    }

    private function counterBooking(int $id): Pemesanan
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));

        return Pemesanan::query()
            ->where('id_destinasi', $admin->id_destinasi)
            ->where('kanal_penjualan', 'LOKET')
            ->with(['destinasi', 'detailPemesanan', 'pembayaran', 'tiket'])
            ->findOrFail($id);
    }

    private function syncQrisPaymentStatus(Pemesanan $booking): bool
    {
        $payment = $booking->pembayaran;
        if (! $payment?->order_id || in_array($booking->status_pemesanan, ['PAID', 'CANCELLED'], true)) {
            return true;
        }

        try {
            $verified = app(MidtransGateway::class)->verify($payment->order_id);
        } catch (\Throwable $exception) {
            Log::warning('Midtrans counter sale status check failed.', [
                'order_id' => $payment->order_id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        $transactionStatus = strtolower((string) ($verified['transaction_status'] ?? 'pending'));
        $status = match ($transactionStatus) {
            'settlement', 'capture' => 'PAID',
            'expire' => 'EXPIRED',
            'cancel' => 'CANCELLED',
            'deny', 'failure' => 'FAILED',
            default => 'PENDING',
        };
        if ($status === 'PENDING' && $payment->status_pembayaran === 'PENDING') {
            return true;
        }

        DB::transaction(function () use ($booking, $payment, $verified, $status, $transactionStatus): void {
            $lockedBooking = Pemesanan::query()->whereKey($booking->id_pemesanan)->lockForUpdate()->firstOrFail();
            $lockedPayment = $lockedBooking->pembayaran()->lockForUpdate()->firstOrFail();
            if (in_array($lockedPayment->status_pembayaran, ['PAID', 'CANCELLED'], true)) {
                return;
            }

            $lockedPayment->update([
                'status_pembayaran' => $status,
                'transaction_status' => $transactionStatus,
                'transaction_id' => $verified['transaction_id'] ?? $payment->transaction_id,
                'payment_type' => $verified['payment_type'] ?? $payment->payment_type,
                'gross_amount' => $verified['gross_amount'] ?? $payment->gross_amount,
                'referensi_gateway' => $verified['transaction_id'] ?? $payment->referensi_gateway,
                'paid_at' => $status === 'PAID' ? now() : null,
                'waktu_pembayaran' => $status === 'PAID' ? now() : null,
            ]);
            $lockedBooking->update(['status_pemesanan' => $status]);
            if ($status === 'PAID') {
                app(BookingTicketService::class)->issueForCounterSale($lockedBooking->fresh());
            }
        });

        return true;
    }
}
