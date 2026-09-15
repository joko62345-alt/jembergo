<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Pemesanan;
use App\Models\Tiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Illuminate\Contracts\Encryption\DecryptException;

class VerificationController extends Controller
{
    public function index(): View
    {
        return view('admin.verification-page');
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
        if (date('Y-m-d', strtotime((string) $booking->tanggal_kunjungan)) < today()->toDateString()) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket sudah kadaluarsa karena tanggal kunjungan telah lewat.');
        }
        if (! $booking->pembayaran || $booking->pembayaran->status_pembayaran !== 'PAID') {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket belum dibayar.');
        }

        if (today()->lt($booking->tanggal_kunjungan)) {
            return redirect()->route('admin.verification')->withInput()->with('verification_error', 'Tiket belum dapat diverifikasi. Jadwal kunjungan baru pada ' . date('d/m/Y', strtotime((string) $booking->tanggal_kunjungan)) . '.');
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
        } catch (DecryptException | \JsonException) {
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
            return redirect()->route('admin.verification')->with('verification_error', 'Tiket belum dapat diverifikasi. Jadwal kunjungan baru pada ' . date('d/m/Y', strtotime((string) $booking->tanggal_kunjungan)) . '.');
        }
        $ticketQuery = $booking->tiket()->where('status_tiket', 'ACTIVE');
        if (! empty($data['id_tiket'])) {
            $ticketQuery->where('id_tiket', $data['id_tiket']);
        }
        abort_if(! $ticketQuery->exists(), 422, 'Tiket sudah digunakan atau dibatalkan.');

        $ticketQuery->update(['status_tiket' => 'USED', 'waktu_verifikasi' => now('UTC')]);

        return redirect()->route('admin.verification')->with('verification_success', empty($data['id_tiket'])
            ? 'Booking valid. Semua tiket aktif telah berubah menjadi USED.'
            : 'Tiket valid. Status tiket telah berubah menjadi USED.');
    }

    public function bookings(): View
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        Pemesanan::expirePendingPayments($admin->id_destinasi);
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

    public function history(): View
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
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
            'destinasi',
            'tiket' => fn ($query) => $query->where('status_tiket', 'USED')->orderBy('id_tiket'),
        ])
            ->whereIn('id_pemesanan', $verifiedBookingIds)
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.verification-history', compact('bookings', 'report'));
    }
}