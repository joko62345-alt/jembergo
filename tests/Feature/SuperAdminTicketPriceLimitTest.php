<?php

namespace Tests\Feature;

use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTicketPriceLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_set_ticket_price_to_999999(): void
    {
        $destination = $this->createDestination();
        $ticketType = $destination->jenisTiket()->create([
            'nama_jenis' => 'Tiket Dewasa',
            'harga' => 45000,
        ]);

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->put(
                route('superadmin.destinations.update', $destination->id_destinasi),
                $this->destinationUpdatePayload($ticketType, 999999),
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('jenis_tiket', [
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'harga' => '999999.00',
        ]);
    }

    public function test_super_admin_cannot_set_ticket_price_above_999999(): void
    {
        $destination = $this->createDestination();
        $ticketType = $destination->jenisTiket()->create([
            'nama_jenis' => 'Tiket Dewasa',
            'harga' => 45000,
        ]);

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->put(
                route('superadmin.destinations.update', $destination->id_destinasi),
                $this->destinationUpdatePayload($ticketType, 1000000),
            )
            ->assertSessionHasErrors(['jenis_tiket.0.harga']);

        $this->assertDatabaseHas('jenis_tiket', [
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'harga' => '45000.00',
        ]);
    }

    private function createDestination(): DestinasiWisata
    {
        return DestinasiWisata::create([
            'nama_wisata' => 'Pantai Harga Test',
            'deskripsi' => 'Destinasi untuk pengujian.',
            'kategori' => 'Bahari',
            'latitude' => -8.123,
            'longitude' => 113.456,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00 - 17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
    }

    private function destinationUpdatePayload(JenisTiket $ticketType, int $price): array
    {
        return [
            'nama_wisata' => 'Pantai Harga Test',
            'deskripsi' => 'Destinasi untuk pengujian.',
            'kategori' => 'Bahari',
            'latitude' => -8.123,
            'longitude' => 113.456,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00 - 17:00',
            'jam_buka' => '08:00',
            'jam_tutup' => '17:00',
            'status_aktif' => '1',
            'jenis_tiket' => [[
                'id' => $ticketType->id_jenis_tiket,
                'nama_jenis' => 'Tiket Dewasa',
                'harga_display' => (string) $price,
                'harga' => (string) $price,
            ]],
        ];
    }
}
