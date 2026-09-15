<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\Tiket;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class BookingTicketService
{
    public function issue(Pemesanan $booking): void
    {
        $booking->loadMissing(['destinasi', 'detailPemesanan']);
        $ticket = $booking->tiket()->orderBy('id_tiket')->first();

        if (! $ticket) {
            $ticket = Tiket::create([
                'id_pemesanan' => $booking->id_pemesanan,
                'kode_qr' => 'pending-' . Str::uuid(),
                'status_tiket' => 'ACTIVE',
            ]);
        }

        $ticket->update(['kode_qr' => Crypt::encryptString(json_encode([
            'booking_code' => $booking->kode_booking,
            'destination' => $booking->destinasi->nama_wisata,
            'visit_date' => date('Y-m-d', strtotime((string) $booking->tanggal_kunjungan)),
            'leader' => $booking->ketua_nama,
            'members' => $booking->anggota_names ?? [],
            'scope' => 'GROUP',
        ], JSON_THROW_ON_ERROR))]);
    }
}