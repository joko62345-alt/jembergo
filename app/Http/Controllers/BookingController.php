<?php

namespace App\Http\Controllers;

use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Models\Tiket;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(int $id): View
    {
        $destination = DestinasiWisata::with('jenisTiket')->findOrFail($id);
        $ticketOptions = $destination->jenisTiket->map(function ($ticket): array {
            return [
                'id' => $ticket->id_jenis_tiket,
                'label' => $ticket->nama_jenis . ' - Rp ' . number_format($ticket->harga, 0, ',', '.'),
            ];
        })->values()->all();

        return view('customer.booking-create', compact('destination', 'ticketOptions'));
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        if (! Schema::hasTable('pemesanan') || ! Schema::hasColumn('pemesanan', 'ketua_nama') || ! Schema::hasColumn('pemesanan', 'anggota_names') || ! Schema::hasColumn('pemesanan', 'ketua_jenis_tiket')) {
            return back()->withInput()->withErrors(['booking' => 'Struktur database tiket kelompok belum diperbarui. Jalankan php artisan migrate terlebih dahulu.']);
        }

        $destination = DestinasiWisata::with('jenisTiket')->findOrFail($id);
        $data = $request->validate([
            'tanggal_kunjungan' => ['required', 'date', 'after_or_equal:today'],
            'ketua_nama' => ['required', 'string', 'max:150'],
            'ketua_email' => ['required', 'email', 'max:150'],
            'ketua_no_hp' => ['required', 'string', 'max:30'],
            'peserta' => ['required', 'array', 'min:1', 'max:10'],
            'peserta.*.nama' => ['required', 'string', 'max:150'],
            'peserta.*.id_jenis_tiket' => ['required', 'integer', 'exists:jenis_tiket,id_jenis_tiket'],
        ]);
        $participants = collect($data['peserta']);
        $availableTypes = $destination->jenisTiket->keyBy('id_jenis_tiket');
        abort_if($participants->contains(fn (array $participant) => ! $availableTypes->has((int) $participant['id_jenis_tiket'])), 422, 'Jenis tiket tidak tersedia untuk destinasi ini.');
        $members = $participants->skip(1)->map(fn (array $participant) => ['nama' => $participant['nama'], 'id_jenis_tiket' => (int) $participant['id_jenis_tiket']])->values()->all();
        $total = $participants->sum(fn (array $participant) => $availableTypes[(int) $participant['id_jenis_tiket']]->harga);

        $booking = Pemesanan::create([
            'id_customer' => session('jg_user_id'),
            'ketua_nama' => $data['ketua_nama'],
            'ketua_email' => $data['ketua_email'],
            'ketua_no_hp' => $data['ketua_no_hp'],
            'ketua_jenis_tiket' => (int) $participants->first()['id_jenis_tiket'],
            'anggota_names' => $members,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'tanggal_pemesanan' => now(), 'tanggal_kunjungan' => $data['tanggal_kunjungan'],
            'total_harga' => $total, 'status_pemesanan' => 'PENDING',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);
        foreach ($participants->groupBy('id_jenis_tiket') as $typeId => $group) {
            $price = $availableTypes[(int) $typeId]->harga;
            $booking->detailPemesanan()->create(['id_jenis_tiket' => $typeId, 'jumlah' => $group->count(), 'subtotal' => $price * $group->count()]);
        }
        $booking->pembayaran()->create(['nominal' => $booking->total_harga, 'status_pembayaran' => 'PENDING']);

        return redirect()->route('customer.checkout', $booking->id_pemesanan);
    }

    public function checkout(int $id): View
    {
        return view('customer.checkout', ['booking' => $this->ownedBooking($id)]);
    }

    public function pay(Request $request, int $id): RedirectResponse
    {
        $booking = $this->ownedBooking($id);
        abort_if($booking->status_pemesanan === 'PAID' || $booking->tiket()->exists(), 422, 'Pesanan ini sudah dibayar dan e-ticket sudah dibuat.');
        $request->validate(['metode_pembayaran' => ['required', 'in:QRIS,TRANSFER_BANK,E_WALLET']]);
        $payment = app(PaymentGateway::class)->createPayment($booking, $request->string('metode_pembayaran')->toString());
        if (strtoupper((string) ($payment['status'] ?? 'PENDING')) !== 'PAID') {
            $booking->pembayaran()->update(['metode_pembayaran' => $payment['method'] ?? $request->string('metode_pembayaran')->toString(), 'status_pembayaran' => $payment['status'] ?? 'PENDING', 'referensi_gateway' => $payment['reference'] ?? null]);

            return back()->with('success', 'Pembayaran sedang diproses. E-ticket akan muncul setelah pembayaran terkonfirmasi.');
        }
        app(DatabaseManager::class)->transaction(function () use ($booking, $payment): void {
            $booking->update(['status_pemesanan' => 'PAID']);
            $booking->pembayaran()->update(['metode_pembayaran' => $payment['method'], 'status_pembayaran' => $payment['status'], 'waktu_pembayaran' => now(), 'referensi_gateway' => $payment['reference']]);
            $details = $booking->detailPemesanan->keyBy('id_jenis_tiket');
            $participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values();

            foreach ($participants as $participant) {
                $detail = $details->get((int) $participant['id_jenis_tiket']);
                $ticket = Tiket::create(['id_pemesanan' => $booking->id_pemesanan, 'kode_qr' => 'pending-' . Str::uuid(), 'status_tiket' => 'ACTIVE']);
                $ticket->update(['kode_qr' => Crypt::encryptString(json_encode([
                    'ticket_id' => $ticket->id_tiket,
                    'booking_code' => $booking->kode_booking,
                    'destination' => $booking->destinasi->nama_wisata,
                    'visit_date' => date('Y-m-d', strtotime((string) $booking->tanggal_kunjungan)),
                    'participant' => $participant['nama'],
                    'ticket_type_id' => (int) $participant['id_jenis_tiket'],
                    'leader' => $booking->ketua_nama,
                    'members' => $booking->anggota_names ?? [],
                ], JSON_THROW_ON_ERROR))]);
                if ($detail && ! $detail->id_tiket) { $detail->update(['id_tiket' => $ticket->id_tiket]); }
            }
        });

        return redirect()->route('customer.ticket', $booking->id_pemesanan)->with('success', 'Pembayaran berhasil. E-ticket sudah aktif.');
    }

    public function ticket(int $id): View
    {
        $booking = $this->ownedBooking($id)->load(['tiket.review', 'tiket.detailPemesanan.jenisTiket', 'detailPemesanan.jenisTiket', 'pembayaran']);

        return view('customer.ticket', compact('booking'));
    }

    public function cancel(int $id): RedirectResponse
    {
        $booking = $this->ownedBooking($id)->load('tiket', 'pembayaran');

        abort_if($booking->status_pemesanan === 'PAID', 422, 'Tiket yang sudah dibayar tidak dapat dibatalkan dan tidak dapat di-refund.');

        return back()->with('error', 'Pembatalan dan refund tidak tersedia untuk pesanan ini.');
    }

    private function ownedBooking(int $id): Pemesanan
    {
        return Pemesanan::query()->where('id_pemesanan', $id)->where('id_customer', session('jg_user_id'))->with(['destinasi', 'detailPemesanan.jenisTiket'])->firstOrFail();
    }
}