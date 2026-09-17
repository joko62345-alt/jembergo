<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\Tiket;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class BookingTicketService
{
    public function issue(Pemesanan $booking): void
    {
        if ($booking->status_pemesanan !== 'PAID') {
            return;
        }

        app(DatabaseManager::class)->transaction(function () use ($booking): void {
            $booking->refresh();
            $booking->loadMissing(['destinasi', 'detailPemesanan']);
            $tickets = $booking->tiket()->lockForUpdate()->orderBy('id_tiket')->get();
            $activeTicket = $tickets->first();

            if (! $activeTicket) {
                $activeTicket = Tiket::create([
                    'id_pemesanan' => $booking->id_pemesanan,
                    'kode_qr' => 'pending-'.Str::uuid(),
                    'status_tiket' => 'ACTIVE',
                ]);
            }

            $payload = json_encode([
                'booking_code' => $booking->kode_booking,
                'destination' => $booking->destinasi->nama_wisata,
                'visit_date' => date('Y-m-d', strtotime((string) $booking->tanggal_kunjungan)),
                'leader' => $booking->ketua_nama,
                'members' => $booking->anggota_names ?? [],
                'scope' => 'GROUP',
            ], JSON_THROW_ON_ERROR);

            $activeTicket->update([
                'kode_qr' => Crypt::encryptString($payload),
                'status_tiket' => 'ACTIVE',
                'waktu_verifikasi' => null,
            ]);

            foreach ($tickets->skip(1) as $duplicateTicket) {
                $duplicateTicket->delete();
            }
        });
    }
}
