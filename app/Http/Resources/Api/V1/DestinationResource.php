<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DestinationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_destinasi,
            'nama' => $this->nama_wisata,
            'foto_utama' => PublicImageUrl::resolve($request, $this->foto_utama),
            'deskripsi' => $this->deskripsi,
            'kategori' => $this->kategori,
            'alamat' => $this->alamat,
            'latitude' => $this->latitude === null ? null : (float) $this->latitude,
            'longitude' => $this->longitude === null ? null : (float) $this->longitude,
            'jam_operasional' => $this->jam_operasional,
            'kuota' => [
                'aktif' => $this->kuota_harian_aktif === 'aktif',
                'harian' => $this->kuota_harian,
            ],
            'fasilitas' => $this->whenLoaded('fasilitas', fn () => $this->fasilitas->map(fn ($facility): array => [
                'id' => $facility->id_fasilitas,
                'nama' => $facility->nama_fasilitas,
            ])->values()),
            'galeri' => $this->whenLoaded('galeri', fn () => $this->galeri->map(fn ($image): array => [
                'id' => $image->id_galeri,
                'url' => PublicImageUrl::resolve($request, $image->url_foto),
                'keterangan' => $image->keterangan,
            ])->values()),
            'jenis_tiket' => $this->whenLoaded('jenisTiket', fn () => $this->jenisTiket->map(fn ($ticketType): array => [
                'id' => $ticketType->id_jenis_tiket,
                'nama' => $ticketType->nama_jenis,
                'harga' => (float) $ticketType->harga,
            ])->values()),
            'rating_rata_rata' => $this->when(
                array_key_exists('review_avg_rating', $this->resource->getAttributes()),
                fn () => $this->review_avg_rating === null ? null : (float) $this->review_avg_rating
            ),
            'ulasan_count' => $this->when(
                array_key_exists('review_count', $this->resource->getAttributes()),
                fn () => (int) $this->review_count
            ),
            'ulasan' => $this->whenLoaded('review', fn () => $this->review->sortByDesc('tanggal_review')->take(5)->values()->map(fn ($review): array => [
                'id' => $review->id_review,
                'rating' => $review->rating,
                'ulasan' => $review->ulasan,
                'tanggal' => $review->tanggal_review?->toIso8601String(),
                'nama_customer' => $review->customer?->nama,
            ])->values()),
        ];
    }
}
