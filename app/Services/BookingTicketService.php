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
        $details = $booking->detailPemesanan->keyBy('id_jenis_tiket');
        $participant = ['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket];
        $ticket = $booking->tiket()->orderBy('id_tiket')->first();

        if (! $ticket) {
            $ticket = Tiket::create([
                'id_pemesanan' => $booking->id_pemesanan,
                'kode_qr' => 'pending-' . Str::uuid(),
                'status_tiket' => 'ACTIVE',
            ]);
        }

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

        $detail = $details->get((int) $participant['id_jenis_tiket']);
        if ($detail && ! $detail->id_tiket) {
            $detail->update(['id_tiket' => $ticket->id_tiket]);
        }
    }
}