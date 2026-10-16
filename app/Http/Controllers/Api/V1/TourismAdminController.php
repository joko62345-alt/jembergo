<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\AdminPariwisata;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class TourismAdminController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $admin = $this->admin($request);
        Pemesanan::expirePendingPayments($admin->id_destinasi);
        Pemesanan::expireTicketsPastVisitDate($admin->id_destinasi);

        $orders = Pemesanan::query()->where('id_destinasi', $admin->id_destinasi);

        return response()->json([
            'data' => [
                'destinasi' => [
                    'id' => $admin->destinasi->id_destinasi,
                    'nama' => $admin->destinasi->nama_wisata,
                    'status_aktif' => $admin->destinasi->status_aktif,
                    'kuota_harian_aktif' => $admin->destinasi->kuota_harian_aktif === 'aktif',
                    'kuota_harian' => $admin->destinasi->kuota_harian,
                ],
                'ringkasan' => [
                    'total_pemesanan' => (clone $orders)->count(),
                    'pemesanan_lunas' => (clone $orders)->where('status_pemesanan', 'PAID')->count(),
                    'tiket_aktif' => Tiket::query()->where('status_tiket', 'ACTIVE')->whereHas('pemesanan', fn ($query) => $query->where('id_destinasi', $admin->id_destinasi))->count(),
                    'tiket_terverifikasi' => Tiket::query()->where('status_tiket', 'USED')->whereHas('pemesanan', fn ($query) => $query->where('id_destinasi', $admin->id_destinasi))->count(),
                    'pendapatan' => (float) (clone $orders)->where('status_pemesanan', 'PAID')->sum('total_harga'),
                ],
            ],
        ]);
    }

    public function bookings(Request $request): AnonymousResourceCollection
    {
        $admin = $this->admin($request);
        $filters = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'status' => ['nullable', 'in:PENDING,PAID,EXPIRED,FAILED'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);

        $bookings = Pemesanan::query()
            ->where('id_destinasi', $admin->id_destinasi)
            ->with(['customer', 'destinasi', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket'])
            ->when(isset($filters['tanggal']), fn ($query) => $query->whereDate('tanggal_kunjungan', $filters['tanggal']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status_pemesanan', $filters['status']))
            ->latest('tanggal_pemesanan')
            ->paginate((int) ($filters['per_page'] ?? 15));

        return BookingResource::collection($bookings);
    }

    public function updateQuota(Request $request): JsonResponse
    {
        $admin = $this->admin($request);
        $data = $request->validate([
            'aktif' => ['required', 'boolean'],
            'kuota_harian' => ['nullable', 'integer', 'min:1', 'max:100000', 'required_if:aktif,true'],
        ]);
        $destination = DestinasiWisata::query()->findOrFail($admin->id_destinasi);
        $destination->update([
            'kuota_harian_aktif' => $request->boolean('aktif') ? 'aktif' : 'nonaktif',
            'kuota_harian' => $request->boolean('aktif') ? $data['kuota_harian'] : null,
        ]);

        return response()->json([
            'data' => [
                'aktif' => $destination->kuota_harian_aktif === 'aktif',
                'kuota_harian' => $destination->kuota_harian,
            ],
        ]);
    }

    public function verifyTicket(Request $request): JsonResponse
    {
        $admin = $this->admin($request);
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:740'],
            'id_tiket' => ['nullable', 'integer'],
        ]);
        $code = trim($data['kode']);
        $ticket = str_starts_with(strtoupper($code), 'JGO-')
            ? null
            : Tiket::query()->where('kode_qr', $code)->first();
        $bookingQuery = Pemesanan::query()
            ->where('id_destinasi', $admin->id_destinasi)
            ->with(['destinasi', 'customer', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket']);

        if ($ticket !== null) {
            $bookingQuery->whereKey($ticket->id_pemesanan);
        } else {
            $bookingQuery->where('kode_booking', strtoupper($code));
        }

        $booking = $bookingQuery->firstOrFail();
        $booking->expireTicketsIfPastVisitDate();
        abort_if($booking->pembayaran?->status_pembayaran !== 'PAID', 422, 'Tiket belum dibayar.');

        $visitDate = Carbon::parse((string) $booking->tanggal_kunjungan)->toDateString();
        abort_if($visitDate < today()->toDateString(), 422, 'Tiket sudah kadaluarsa karena tanggal kunjungan telah lewat.');
        abort_if($visitDate > today()->toDateString(), 422, 'Tiket belum dapat diverifikasi sebelum tanggal kunjungan.');

        if ($ticket !== null) {
            abort_if($ticket->id_pemesanan !== $booking->id_pemesanan, 422, 'QR tiket tidak sesuai dengan pemesanan.');
            $ticketIds = [$ticket->id_tiket];
        } elseif (isset($data['id_tiket'])) {
            $ticketIds = [$data['id_tiket']];
        } else {
            $ticketIds = $booking->tiket->where('status_tiket', 'ACTIVE')->pluck('id_tiket')->all();
        }

        abort_if($ticketIds === [], 422, 'Tidak ada tiket aktif yang dapat diverifikasi.');
        $updated = DB::transaction(function () use ($booking, $ticketIds) {
            $query = $booking->tiket()->whereIn('id_tiket', $ticketIds)->where('status_tiket', 'ACTIVE');
            $updatedCount = $query->update(['status_tiket' => 'USED', 'waktu_verifikasi' => now('UTC')]);
            abort_if($updatedCount !== count($ticketIds), 422, 'Tiket sudah digunakan atau dibatalkan.');

            return $booking->fresh(['destinasi', 'customer', 'detailPemesanan.jenisTiket', 'pembayaran', 'tiket']);
        });

        return response()->json([
            'data' => (new BookingResource($updated))->resolve(),
            'message' => 'Tiket berhasil diverifikasi.',
        ]);
    }

    private function admin(Request $request): AdminPariwisata
    {
        $user = $request->user('sanctum');
        abort_unless($user instanceof AdminPariwisata, 403, 'Akses hanya untuk admin pariwisata.');
        abort_unless($user->destinasi !== null, 403, 'Admin belum memiliki destinasi yang dikelola.');

        return $user->loadMissing('destinasi');
    }
}
