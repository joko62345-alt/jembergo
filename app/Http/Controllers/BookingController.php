<?php

namespace App\Http\Controllers;

use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Services\BookingTicketService;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $method = $request->validate(['metode_pembayaran' => ['required', 'in:QRIS']])['metode_pembayaran'];

        $payment = app(PaymentGateway::class)->createPayment($booking, $method);
        if (strtoupper((string) ($payment['status'] ?? 'PENDING')) !== 'PAID') {
            $booking->pembayaran()->update(['metode_pembayaran' => $payment['method'] ?? $request->string('metode_pembayaran')->toString(), 'status_pembayaran' => $payment['status'] ?? 'PENDING', 'referensi_gateway' => $payment['reference'] ?? null]);

            return back()->with('success', 'Pembayaran sedang diproses. E-ticket akan muncul setelah pembayaran terkonfirmasi.');
        }
        app(DatabaseManager::class)->transaction(function () use ($booking, $payment): void {
            $booking->update(['status_pemesanan' => 'PAID']);
            $booking->pembayaran()->update(['metode_pembayaran' => $payment['method'], 'status_pembayaran' => $payment['status'], 'waktu_pembayaran' => now(), 'referensi_gateway' => $payment['reference']]);
            app(BookingTicketService::class)->issue($booking);
        });

        return redirect()->route('customer.ticket', $booking->id_pemesanan)->with('success', 'Pembayaran berhasil. E-ticket sudah aktif.');
    }

    public function ticket(int $id): View
    {
        $booking = $this->ownedBooking($id);
        $booking->expireTicketsIfPastVisitDate();
        $booking->load(['tiket.review', 'tiket.detailPemesanan.jenisTiket', 'detailPemesanan.jenisTiket', 'pembayaran']);

        return view('customer.ticket', compact('booking'));
    }

    public function ticketPdf(int $id)
    {
        $booking = $this->ownedBooking($id);
        $booking->expireTicketsIfPastVisitDate();
        $booking->load(['destinasi', 'pembayaran', 'tiket.detailPemesanan.jenisTiket', 'detailPemesanan.jenisTiket']);

        return Pdf::loadView('customer.ticket-pdf', compact('booking'))
            ->setPaper('a4')
            ->setOption(['isRemoteEnabled' => true])
            ->download('e-ticket-' . $booking->kode_booking . '.pdf');
    }

    private function ownedBooking(int $id): Pemesanan
    {
        return Pemesanan::query()->where('id_pemesanan', $id)->where('id_customer', session('jg_user_id'))->with(['destinasi', 'detailPemesanan.jenisTiket'])->firstOrFail();
    }
}