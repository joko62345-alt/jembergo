<?php

namespace App\Http\Controllers;

use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Models\PerubahanPemesanan;
use App\Services\BookingTicketService;
use App\Services\PaymentGateway\PaymentGateway;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(int $id): View
    {
        $destination = DestinasiWisata::with('jenisTiket')->findOrFail($id);
        $quota = $destination->kuota_harian_aktif ? $destination->kuota_harian : null;
        $ticketOptions = $destination->jenisTiket->map(function ($ticket): array {
            return [
                'id' => $ticket->id_jenis_tiket,
                'label' => $ticket->nama_jenis.' - Rp '.number_format($ticket->harga, 0, ',', '.'),
            ];
        })->values()->all();

        return view('customer.booking-create', compact('destination', 'ticketOptions', 'quota'));
    }

    public function quotaAvailability(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['date' => ['required', 'date', 'after_or_equal:today']]);
        $destination = DestinasiWisata::findOrFail($id);

        return response()->json([
            'enabled' => $destination->kuota_harian_aktif,
            'remaining' => $destination->kuota_harian_aktif
                ? max(0, $destination->kuota_harian - $destination->bookedTicketsForDate($data['date']))
                : null,
        ]);
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

        if ($destination->kuota_harian_aktif) {
            $booked = $destination->bookedTicketsForDate($data['tanggal_kunjungan']);
            if ($booked + $participants->count() > (int) $destination->kuota_harian) {
                return back()->withInput()->withErrors(['tanggal_kunjungan' => 'Kuota pada tanggal tersebut tersisa '.max(0, (int) $destination->kuota_harian - $booked).' peserta.']);
            }
        }

        $booking = Pemesanan::create([
            'id_customer' => session('jg_user_id'),
            'ketua_nama' => $data['ketua_nama'],
            'ketua_email' => $data['ketua_email'],
            'ketua_no_hp' => $data['ketua_no_hp'],
            'ketua_jenis_tiket' => (int) $participants->first()['id_jenis_tiket'],
            'anggota_names' => $members,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
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
        $existingPayment = $booking->pembayaran;
        if ($existingPayment?->status_pembayaran === 'PENDING' && $existingPayment->order_id && $existingPayment->qris_url && $existingPayment->qris_expires_at?->isFuture()) {
            return redirect()->route('customer.qris', $id);
        }
        $payment = app(PaymentGateway::class)->createPayment($booking, 'QRIS');
        $booking->pembayaran()->update([
            'order_id' => $payment['order_id'], 'transaction_id' => $payment['transaction_id'],
            'metode_pembayaran' => 'QRIS', 'status_pembayaran' => 'PENDING',
            'transaction_status' => $payment['transaction_status'], 'payment_type' => 'qris',
            'gross_amount' => $payment['gross_amount'], 'referensi_gateway' => $payment['reference'],
            'qris_url' => $payment['qris_url'], 'qris_expires_at' => $payment['qris_expires_at'],
        ]);

        return redirect()->route('customer.qris', $booking->id_pemesanan);
    }

    public function reschedule(Request $request, int $id): RedirectResponse
    {
        return redirect()->route('customer.manage', $id)->withInput($request->only('tanggal_kunjungan'));
    }

    public function addMembers(Request $request, int $id): RedirectResponse
    {
        return redirect()->route('customer.manage', $id)->withInput($request->all());
    }

    public function manage(int $id): View
    {
        $booking = $this->ownedBooking($id);
        $this->ensureActivePaidBooking($booking);
        $pending = $booking->perubahan()->where('status', 'PENDING')->latest('id_perubahan')->first();

        return view('customer.manage-trip', compact('booking', 'pending'));
    }

    public function reviewChanges(Request $request, int $id): RedirectResponse
    {
        $booking = $this->ownedBooking($id);
        $this->ensureActivePaidBooking($booking);
        $remaining = 10 - (1 + count($booking->anggota_names ?? []));
        $data = $request->validate([
            'tanggal_kunjungan' => ['required', 'date', 'after_or_equal:today'],
            'anggota' => ['nullable', 'array', 'max:'.max(0, $remaining)],
            'anggota.*.nama' => ['nullable', 'string', 'max:150'],
            'anggota.*.id_jenis_tiket' => ['nullable', 'integer', 'exists:jenis_tiket,id_jenis_tiket'],
        ]);
        $destination = $booking->destinasi()->with('jenisTiket')->firstOrFail();
        $availableTypes = $destination->jenisTiket->keyBy('id_jenis_tiket');
        $rawMembers = collect($data['anggota'] ?? []);
        abort_if($rawMembers->contains(fn (array $member): bool => (bool) ($member['nama'] ?? '') !== (bool) ($member['id_jenis_tiket'] ?? '')), 422, 'Nama dan jenis tiket anggota harus diisi lengkap.');
        $members = $rawMembers->filter(fn (array $member): bool => filled($member['nama'] ?? null) && filled($member['id_jenis_tiket'] ?? null))->map(fn (array $member): array => [
            'nama' => $member['nama'],
            'id_jenis_tiket' => (int) $member['id_jenis_tiket'],
            'harga' => (float) $availableTypes[(int) $member['id_jenis_tiket']]->harga,
        ])->values();
        abort_if($members->contains(fn (array $member): bool => ! $availableTypes->has($member['id_jenis_tiket'])), 422, 'Jenis tiket tidak tersedia untuk destinasi ini.');
        if ($data['tanggal_kunjungan'] === date('Y-m-d', strtotime((string) $booking->tanggal_kunjungan)) && $members->isEmpty()) {
            return redirect()->route('customer.manage', $id)->withInput()->with('change_error', 'Pilih tanggal baru atau tambahkan minimal satu tiket terlebih dahulu.');
        }
        abort_if($booking->perubahan()->where('status', 'PENDING')->exists(), 422, 'Selesaikan perubahan sebelumnya terlebih dahulu.');

        $change = PerubahanPemesanan::create([
            'id_pemesanan' => $booking->id_pemesanan,
            'anggota_baru' => ['tanggal_kunjungan' => $data['tanggal_kunjungan'], 'anggota' => $members->all()],
            'nominal' => $members->sum('harga'),
            'status' => 'PENDING',
        ]);

        return redirect()->route('customer.change.checkout', [$id, $change->id_perubahan]);
    }

    public function additionalCheckout(int $id, int $change): View
    {
        $booking = $this->ownedBooking($id);
        $this->ensureActivePaidBooking($booking);
        $change = $this->ownedChange($booking, $change);
        abort_if(! in_array($change->status, ['PENDING', 'FAILED', 'EXPIRED'], true), 422, 'Tagihan tambahan ini sudah diproses.');
        $changeData = $this->changeData($change);

        return view('customer.change-checkout', compact('booking', 'change', 'changeData'));
    }

    public function payAdditional(Request $request, int $id, int $change): RedirectResponse
    {
        $booking = $this->ownedBooking($id);
        $this->ensureActivePaidBooking($booking);
        $change = $this->ownedChange($booking, $change);
        abort_if(! in_array($change->status, ['PENDING', 'FAILED', 'EXPIRED'], true), 422, 'Tagihan tambahan ini sudah diproses.');
        if ($change->order_id && $change->qris_url && $change->qris_expires_at?->isFuture()) {
            return redirect()->route('customer.change.qris', [$id, $change->id_perubahan]);
        }
        if ((float) $change->nominal <= 0) {
            $this->applyChange($booking, $change, ['status' => 'CONFIRMED', 'method' => null, 'reference' => null]);

            return redirect()->route('customer.ticket', $id)->with('success', 'Perubahan perjalanan berhasil dikonfirmasi.');
        }
        $gateway = app(PaymentGateway::class);
        $payment = call_user_func([$gateway, 'createPaymentForAmount'], $booking, 'QRIS', (float) $change->nominal);
        $change->update([
            'order_id' => $payment['order_id'], 'transaction_id' => $payment['transaction_id'],
            'metode_pembayaran' => 'QRIS', 'status' => 'PENDING',
            'transaction_status' => $payment['transaction_status'], 'payment_type' => 'qris',
            'gross_amount' => $payment['gross_amount'], 'referensi_gateway' => $payment['reference'],
            'qris_url' => $payment['qris_url'], 'qris_expires_at' => $payment['qris_expires_at'],
        ]);

        return redirect()->route('customer.change.qris', [$id, $change->id_perubahan]);
    }

    public function qrisPayment(int $id): View|JsonResponse
    {
        $booking = $this->ownedBooking($id);
        $payment = $booking->pembayaran;
        if (request()->expectsJson()) {
            return response()->json(['status' => $payment?->status_pembayaran ?? 'PENDING']);
        }
        abort_if(! $payment || $payment->status_pembayaran === 'PAID', 422, 'Transaksi QRIS ini sudah selesai.');

        return view('customer.qris-payment', ['booking' => $booking, 'payment' => $payment, 'change' => null]);
    }

    public function additionalQrisPayment(int $id, int $change): View|JsonResponse
    {
        $booking = $this->ownedBooking($id);
        $change = $this->ownedChange($booking, $change);
        if (request()->expectsJson()) {
            return response()->json(['status' => $change->status === 'PAID' ? 'PAID' : $change->status]);
        }
        abort_if($change->status === 'PAID', 422, 'Transaksi QRIS ini sudah selesai.');

        return view('customer.qris-payment', ['booking' => $booking, 'payment' => $change, 'change' => $change]);
    }

    public function simulatePayment(int $id): RedirectResponse
    {
        $booking = $this->ownedBooking($id);
        $payment = $booking->pembayaran;
        abort_if(! $payment || $payment->status_pembayaran === 'PAID', 422, 'Pembayaran ini sudah diproses.');

        app(DatabaseManager::class)->transaction(function () use ($booking, $payment): void {
            $payment->update([
                'status_pembayaran' => 'PAID',
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
                'paid_at' => now(),
                'waktu_pembayaran' => now(),
            ]);
            $booking->update(['status_pemesanan' => 'PAID']);
            app(BookingTicketService::class)->issue($booking->fresh());
        });

        return redirect()->route('customer.ticket', $id)->with('success', 'Pembayaran berhasil disimulasikan. E-ticket sudah aktif.');
    }

    public function simulateAdditionalPayment(int $id, int $change): RedirectResponse
    {
        $booking = $this->ownedBooking($id);
        $change = $this->ownedChange($booking, $change);
        abort_if($change->status === 'PAID', 422, 'Pembayaran ini sudah diproses.');
        $this->applyChange($booking, $change, ['status' => 'PAID', 'method' => 'QRIS', 'reference' => $change->transaction_id]);

        return redirect()->route('customer.ticket', $id)->with('success', 'Pembayaran berhasil disimulasikan. Perjalanan sudah diperbarui.');
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
            ->download('e-ticket-'.$booking->kode_booking.'.pdf');
    }

    private function ownedBooking(int $id): Pemesanan
    {
        return Pemesanan::query()->where('id_pemesanan', $id)->where('id_customer', session('jg_user_id'))->with(['destinasi', 'detailPemesanan.jenisTiket'])->firstOrFail();
    }

    private function ownedChange(Pemesanan $booking, int $change): PerubahanPemesanan
    {
        return $booking->perubahan()->whereKey($change)->firstOrFail();
    }

    private function ensureActivePaidBooking(Pemesanan $booking): void
    {
        abort_if($booking->pembayaran?->status_pembayaran !== 'PAID' || ! $booking->tiket()->where('status_tiket', 'ACTIVE')->exists(), 422, 'Perubahan hanya tersedia untuk tiket yang sudah dibayar dan masih aktif.');
    }

    private function changeData(PerubahanPemesanan $change): array
    {
        $data = $change->anggota_baru ?? [];

        return array_key_exists('anggota', $data) ? $data : ['tanggal_kunjungan' => null, 'anggota' => $data];
    }

    public function applyChange(Pemesanan $booking, PerubahanPemesanan $change, array $payment): void
    {
        app(DatabaseManager::class)->transaction(function () use ($booking, $change, $payment): void {
            $data = $this->changeData($change);
            $members = collect($booking->anggota_names ?? [])->concat($data['anggota'] ?? [])->values()->all();
            $updates = ['anggota_names' => $members];
            if (! empty($data['tanggal_kunjungan'])) {
                $updates['tanggal_kunjungan'] = $data['tanggal_kunjungan'];
            }
            if ((float) $change->nominal > 0) {
                $updates['total_harga'] = (float) $booking->total_harga + (float) $change->nominal;
            }
            $booking->update($updates);
            foreach (collect($data['anggota'] ?? [])->groupBy('id_jenis_tiket') as $typeId => $group) {
                $detail = $booking->detailPemesanan->firstWhere('id_jenis_tiket', $typeId);
                if ($detail) {
                    $detail->increment('jumlah', $group->count());
                    $detail->update(['subtotal' => (float) $detail->subtotal + $group->sum('harga')]);
                } else {
                    $booking->detailPemesanan()->create(['id_jenis_tiket' => $typeId, 'jumlah' => $group->count(), 'subtotal' => $group->sum('harga')]);
                }
            }
            $change->update(['status' => $payment['status'], 'metode_pembayaran' => $payment['method'], 'referensi_gateway' => $payment['reference'], 'waktu_pembayaran' => $payment['status'] === 'PAID' ? now() : null]);
            app(BookingTicketService::class)->issue($booking->fresh());
        });
    }
}
