<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pemesanan,
            'kode_booking' => $this->kode_booking,
            'tanggal_pemesanan' => $this->tanggal_pemesanan?->toIso8601String(),
            'tanggal_kunjungan' => $this->tanggal_kunjungan?->toDateString(),
            'status' => $this->status_pemesanan,
            'total_harga' => (float) $this->total_harga,
            'batas_waktu_pembayaran' => $this->batas_waktu_pembayaran?->toIso8601String(),
            'ketua' => [
                'nama' => $this->ketua_nama,
                'email' => $this->ketua_email,
                'no_hp' => $this->ketua_no_hp,
            ],
            'anggota' => $this->anggota_names ?? [],
            'destinasi' => $this->whenLoaded('destinasi', fn () => [
                'id' => $this->destinasi?->id_destinasi,
                'nama' => $this->destinasi?->nama_wisata,
                'foto_utama' => PublicImageUrl::resolve($request, $this->destinasi?->foto_utama),
                'alamat' => $this->destinasi?->alamat,
            ]),
            'customer' => $this->whenLoaded('customer', fn () => $this->customer === null ? null : [
                'id' => $this->customer->id_customer,
                'nama' => $this->customer->nama,
                'email' => $this->customer->email,
                'no_hp' => $this->customer->no_hp,
            ]),
            'detail' => $this->whenLoaded('detailPemesanan', fn () => $this->detailPemesanan->map(fn ($detail): array => [
                'id' => $detail->id_detail,
                'jumlah' => $detail->jumlah,
                'subtotal' => (float) $detail->subtotal,
                'jenis_tiket' => [
                    'id' => $detail->jenisTiket?->id_jenis_tiket,
                    'nama' => $detail->jenisTiket?->nama_jenis,
                    'harga' => $detail->jenisTiket === null ? null : (float) $detail->jenisTiket->harga,
                ],
            ])->values()),
            'pembayaran' => $this->whenLoaded('pembayaran', fn () => $this->pembayaran === null ? null : [
                'status' => $this->pembayaran->status_pembayaran,
                'metode' => $this->pembayaran->payment_type ?: $this->pembayaran->metode_pembayaran,
                'nominal' => (float) $this->pembayaran->nominal,
                'waktu_pembayaran' => $this->pembayaran->waktu_pembayaran?->toIso8601String(),
            ]),
            'tiket' => $this->whenLoaded('tiket', fn () => $this->tiket->map(fn ($ticket): array => [
                'id' => $ticket->id_tiket,
                'kode_qr' => $ticket->kode_qr,
                'status' => $ticket->status_tiket,
                'waktu_verifikasi' => $ticket->waktu_verifikasi?->toIso8601String(),
            ])->values()),
        ];
    }
}
