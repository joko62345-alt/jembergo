<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateBookingPreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_leader_identity_cannot_make_a_new_booking_on_the_same_date(): void
    {
        $customer = Customer::create([
            'nama' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);

        $destination = DestinasiWisata::create([
            'id_superadmin' => null,
            'nama_wisata' => 'Pantai Kuta',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Wisata Alam',
            'latitude' => -8.7,
            'longitude' => 115.2,
            'alamat' => 'Jl. Contoh No. 1',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
            'kuota_harian' => 50,
        ]);

        $ticketType = JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 100000,
        ]);

        $payload = [
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'ketua_nama' => 'Andi Wijaya',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567890',
            'peserta' => [
                ['nama' => 'Andi Wijaya', 'id_jenis_tiket' => $ticketType->id_jenis_tiket],
                ['nama' => 'Sari', 'id_jenis_tiket' => $ticketType->id_jenis_tiket],
            ],
        ];

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ]);

        $firstResponse = $this->post(route('customer.booking.store', $destination->id_destinasi), $payload);
        $firstResponse->assertRedirect();
        $this->assertDatabaseCount('pemesanan', 1);

        $secondResponse = $this->post(route('customer.booking.store', $destination->id_destinasi), $payload);
        $secondResponse->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('pemesanan', 1);
    }

    public function test_same_identity_is_allowed_on_a_different_date(): void
    {
        $customer = Customer::create([
            'nama' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);

        $destination = DestinasiWisata::create([
            'id_superadmin' => null,
            'nama_wisata' => 'Pantai Kuta',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Wisata Alam',
            'latitude' => -8.7,
            'longitude' => 115.2,
            'alamat' => 'Jl. Contoh No. 1',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
            'kuota_harian' => 50,
        ]);

        $ticketType = JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 100000,
        ]);

        $firstPayload = [
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'ketua_nama' => 'Andi Wijaya',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567890',
            'peserta' => [
                ['nama' => 'Andi Wijaya', 'id_jenis_tiket' => $ticketType->id_jenis_tiket],
                ['nama' => 'Sari', 'id_jenis_tiket' => $ticketType->id_jenis_tiket],
            ],
        ];

        $secondPayload = $firstPayload;
        $secondPayload['tanggal_kunjungan'] = now()->addDays(2)->toDateString();

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ]);

        $this->post(route('customer.booking.store', $destination->id_destinasi), $firstPayload)->assertRedirect();
        $this->post(route('customer.booking.store', $destination->id_destinasi), $secondPayload)->assertRedirect();
        $this->assertDatabaseCount('pemesanan', 2);
    }

    public function test_same_identity_is_allowed_on_the_same_date_for_a_different_destination(): void
    {
        $customer = Customer::create([
            'nama' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);

        $firstDestination = DestinasiWisata::create([
            'id_superadmin' => null,
            'nama_wisata' => 'Pantai Kuta',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Wisata Alam',
            'latitude' => -8.7,
            'longitude' => 115.2,
            'alamat' => 'Jl. Contoh No. 1',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
            'kuota_harian' => 50,
        ]);

        $secondDestination = DestinasiWisata::create([
            'id_superadmin' => null,
            'nama_wisata' => 'Bromo',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Wisata Alam',
            'latitude' => -7.9,
            'longitude' => 112.9,
            'alamat' => 'Jl. Contoh No. 2',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
            'kuota_harian' => 50,
        ]);

        $firstTicket = JenisTiket::create([
            'id_destinasi' => $firstDestination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 100000,
        ]);

        $secondTicket = JenisTiket::create([
            'id_destinasi' => $secondDestination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 120000,
        ]);

        $sameDate = now()->addDay()->toDateString();

        $firstPayload = [
            'tanggal_kunjungan' => $sameDate,
            'ketua_nama' => 'Andi Wijaya',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567890',
            'peserta' => [
                ['nama' => 'Andi Wijaya', 'id_jenis_tiket' => $firstTicket->id_jenis_tiket],
                ['nama' => 'Sari', 'id_jenis_tiket' => $firstTicket->id_jenis_tiket],
            ],
        ];

        $secondPayload = [
            'tanggal_kunjungan' => $sameDate,
            'ketua_nama' => 'Andi Wijaya',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567890',
            'peserta' => [
                ['nama' => 'Andi Wijaya', 'id_jenis_tiket' => $secondTicket->id_jenis_tiket],
                ['nama' => 'Lia', 'id_jenis_tiket' => $secondTicket->id_jenis_tiket],
            ],
        ];

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ]);

        $this->post(route('customer.booking.store', $firstDestination->id_destinasi), $firstPayload)->assertRedirect();
        $this->post(route('customer.booking.store', $secondDestination->id_destinasi), $secondPayload)->assertRedirect();
        $this->assertDatabaseCount('pemesanan', 2);
    }
}
