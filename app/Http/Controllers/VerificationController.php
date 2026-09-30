<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Pemesanan;
use App\Models\Tiket;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(): View
    {
        return view('admin.verification-page');
    }

    public function ticketScan(Request $request): View
    {
        return view('public.ticket-scan', ['scanData' => $this->ticketScanData($request)]);
    }

    public function ticketData(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->ticketScanData($request)]);
    }

    private function ticketScanData(Request $request): array
    {
        $data = $request->validate(['kode_qr' => ['required', 'string', 'max:740']]);
        $ticket = Tiket::query()
            ->with(['pemesanan.destinasi', 'pemesanan.pembayaran', 'pemesanan.detailPemesanan.jenisTiket'])
            ->where('kode_qr', $data['kode_qr'])
            ->firstOrFail();
        $booking = $ticket->pemesanan;
        $participants = collect($booking->anggota_names)
            ->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])
            ->values()
            ->map(function (array $participant) use ($booking): array {
                $ticketTypeId = (int) ($participant['id_jenis_tiket'] ?? 0);

                return [
                    'nama' => $participant['nama'],
                    'jenis_tiket' => $booking->detailPemesanan
                        ->firstWhere('id_jenis_tiket', $ticketTypeId)?->jenisTiket?->nama_jenis,
                ];
            });

        return [
            'kode_booking' => $booking->kode_booking,
            'destinasi' => $booking->destinasi?->nama_wisata,
            'tanggal_kunjungan' => $booking->tanggal_kunjungan?->toDateString(),
            'status_pembayaran' => $booking->pembayaran?->status_pembayaran,
            'total_harga' => (float) $booking->total_harga,
            'peserta' => $participants,
            'status_tiket' => $ticket->status_tiket,
            'waktu_verifikasi' => $ticket->waktu_verifikasi?->toIso8601String(),
        ];
    }

    public function lookup(Request $request): View|RedirectResponse
    {
        $data = $request->validate(['kode_booking' => ['required', 'string', 'max:5000']]);
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $rawInput = trim($data['kode_booking']);
        $scannedTicket = str_starts_with(strtoupper($rawInput), 'JGO-')
            ? null
            : Tiket::with('pemesanan')->where('kode_qr', $rawInput)->first();

        if ($scannedTicket) {
            $inputDetails = ['booking_code' => $scannedTicket->pemesanan->kode_booking, 'ticket_id' => null];
        } else {
            $inputDetails = $this->bookingDetailsFromInput($rawInput);
        }
        $bookingCode = $inputDetails['booking_code'];

        if (! $bookingCode) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Kode booking atau QR grup tidak valid.');
        }

        $booking = Pemesanan::with(['customer', 'destinasi', 'pembayaran', 'tiket', 'detailPemesanan.jenisTiket'])
            ->where('kode_booking', $bookingCode)
            ->where('id_destinasi', $admin->id_destinasi)
            ->first();

        if (! $booking) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Booking tidak valid atau bukan milik destinasi ini. Periksa kembali QR grup atau kode booking.');
        }

        $booking->expireTicketsIfPastVisitDate();
        $visitDate = $booking->tanggal_kunjungan
            ? Carbon::parse($booking->tanggal_kunjungan)->toDateString()
            : null;

        if ($visitDate !== null && $visitDate < today()->toDateString()) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket sudah kadaluarsa karena tanggal kunjungan telah lewat.');
        }

        if ($visitDate === today()->toDateString()) {
            $closingTime = $booking->destinasi?->closingTime();
            if ($closingTime !== null && now()->gte(Carbon::parse($visitDate)->setTimeFromTimeString($closingTime))) {
                return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket sudah kadaluarsa karena jam operasional destinasi telah berakhir.');
            }
        }

        if (! $booking->pembayaran || $booking->pembayaran->status_pembayaran !== 'PAID') {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket belum dibayar.');
        }

        if ($visitDate !== null && $visitDate > today()->toDateString()) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket belum dapat diverifikasi. Jadwal kunjungan baru pada '.date('d/m/Y', strtotime((string) $booking->tanggal_kunjungan)).'.');
        }

        $ticket = $inputDetails['ticket_id']
            ? $booking->tiket->firstWhere('id_tiket', $inputDetails['ticket_id'])
            : $booking->tiket->firstWhere('status_tiket', 'ACTIVE');

        if (! $ticket) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Kode tiket tidak sesuai dengan booking yang ditemukan.');
        }

        if ($ticket->status_tiket !== 'ACTIVE') {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket tidak valid karena sudah digunakan atau telah dibatalkan.');
        }

        return view('admin.verification-page', ['booking' => $booking, 'selectedTicketId' => $inputDetails['ticket_id']]);
    }

    private function bookingDetailsFromInput(string $input): array
    {
        $input = trim($input);

        if (str_starts_with(strtoupper($input), 'JGO-')) {
            return ['booking_code' => strtoupper($input), 'ticket_id' => null];
        }

        try {
            $payload = json_decode(Crypt::decryptString($input), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return ['booking_code' => null, 'ticket_id' => null];
        }

        $bookingCode = $payload['booking_code'] ?? null;

        return [
            'booking_code' => is_string($bookingCode) && str_starts_with(strtoupper($bookingCode), 'JGO-') ? strtoupper($bookingCode) : null,
            'ticket_id' => null,
        ];
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['id_pemesanan' => ['required', 'integer'], 'id_tiket' => ['nullable', 'integer']]);
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $booking = Pemesanan::with(['customer', 'destinasi', 'pembayaran', 'tiket'])
            ->where('id_pemesanan', $data['id_pemesanan'])
            ->where('id_destinasi', $admin->id_destinasi)
            ->firstOrFail();

        $booking->expireTicketsIfPastVisitDate();
        if (date('Y-m-d', strtotime((string) $booking->tanggal_kunjungan)) < today()->toDateString()) {
            return redirect()->route('admin.verification')->with('verification_error', 'Tiket sudah kadaluarsa karena tanggal kunjungan telah lewat.');
        }
        abort_if(! $booking->pembayaran || $booking->pembayaran->status_pembayaran !== 'PAID', 422, 'Tiket belum dibayar.');
        if (today()->lt($booking->tanggal_kunjungan)) {
            return redirect()->route('admin.verification')->with('verification_error', 'Tiket belum dapat diverifikasi. Jadwal kunjungan baru pada '.date('d/m/Y', strtotime((string) $booking->tanggal_kunjungan)).'.');
        }
        $ticketQuery = $booking->tiket()->where('status_tiket', 'ACTIVE');
        if (! empty($data['id_tiket'])) {
            $ticketQuery->where('id_tiket', $data['id_tiket']);
        }
        abort_if(! $ticketQuery->exists(), 422, 'Tiket sudah digunakan atau dibatalkan.');

        $ticketQuery->update(['status_tiket' => 'USED', 'waktu_verifikasi' => now('UTC')]);

        return redirect()->route('admin.verification')->with([
            'verification_success' => empty($data['id_tiket'])
                ? 'Booking valid. Semua tiket aktif telah berubah menjadi USED.'
                : 'Tiket valid. Status tiket telah berubah menjadi USED.',
            'receipt_booking_id' => $booking->id_pemesanan,
        ]);
    }

    public function receipt(int $id): View
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $booking = Pemesanan::with(['customer', 'destinasi', 'pembayaran', 'tiket', 'detailPemesanan.jenisTiket'])
            ->where('id_pemesanan', $id)
            ->where('id_destinasi', $admin->id_destinasi)
            ->whereHas('tiket', fn ($query) => $query->where('status_tiket', 'USED'))
            ->firstOrFail();

        return view('admin.verification-receipt', compact('booking'));
    }

    public function bookings(): View
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        Pemesanan::expirePendingPayments($admin->id_destinasi);
        Pemesanan::expireTicketsPastVisitDate($admin->id_destinasi);
        $destinationBooking = fn ($query) => $query->where('id_destinasi', $admin->id_destinasi);
        $verifiedBookingIds = Tiket::where('status_tiket', 'USED')
            ->whereHas('pemesanan', $destinationBooking)
            ->distinct()
            ->pluck('id_pemesanan');
        $report = [
            'tickets' => Tiket::where('status_tiket', 'USED')->whereHas('pemesanan', $destinationBooking)->count(),
            'bookings' => $verifiedBookingIds->count(),
            'revenue' => Pemesanan::whereIn('id_pemesanan', $verifiedBookingIds)->sum('total_harga'),
        ];
        $bookings = Pemesanan::with([
            'customer',
            'pembayaran',
            'tiket' => fn ($query) => $query->orderBy('id_tiket'),
        ])
            ->where('id_destinasi', $admin->id_destinasi)
            ->where('status_pemesanan', 'PAID')
            ->latest('tanggal_pemesanan')
            ->paginate(20);

        return view('admin.bookings', compact('bookings', 'report'));
    }
}
