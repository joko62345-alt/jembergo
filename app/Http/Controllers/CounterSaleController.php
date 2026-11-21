<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Services\BookingTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'status_pemesanan' => 'PAID',
                'batas_waktu_pembayaran' => null,
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
                'status_pembayaran' => 'PAID',
                'payment_type' => strtolower($method),
                'waktu_pembayaran' => $now,
                'paid_at' => $now,
            ]);

            app(BookingTicketService::class)->issue($booking);
            $verifiedCount = $booking->tiket()
                ->where('status_tiket', 'ACTIVE')
                ->update([
                    'status_tiket' => 'USED',
                    'waktu_verifikasi' => $now->copy()->utc(),
                ]);
            if ($verifiedCount !== 1) {
                throw new \RuntimeException('Tiket loket tidak dapat ditandai sebagai sudah digunakan.');
            }

            return $booking->fresh(['destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket']);
        });

        return redirect()->route('admin.counter-sales.show', $booking->id_pemesanan);
    }

    public function show(int $id): View
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $booking = Pemesanan::query()
            ->where('id_destinasi', $admin->id_destinasi)
            ->where('kanal_penjualan', 'LOKET')
            ->with(['destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket'])
            ->findOrFail($id);

        return view('admin.verification-receipt', compact('booking'));
    }
}
