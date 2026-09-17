<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use App\Services\BookingTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_book_inactive_destination(): void
    {
        $customer = Customer::create([
            'nama' => 'Andi',
            'email' => 'andi@example.com',
            'no_hp' => '081234567890',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Pantai Papuma',
            'deskripsi' => 'Wisata pantai',
            'kategori' => 'Bahari',
            'latitude' => -8.152,
            'longitude' => 113.719,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => false,
            'kuota_harian_aktif' => false,
        ]);
        JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);

        $response = $this->withSession(['jg_user_id' => $customer->id_customer, 'jg_role' => 'CUSTOMER'])
            ->from(route('home'))
            ->post(route('customer.booking.store', $destination->id_destinasi), [
                'tanggal_kunjungan' => now()->addDay()->toDateString(),
                'ketua_nama' => 'Andi',
                'ketua_email' => 'andi@example.com',
                'ketua_no_hp' => '081234567890',
                'peserta' => [[
                    'nama' => 'Andi',
                    'id_jenis_tiket' => 1,
                ]],
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('pemesanan', 0);
    }

    public function test_ticket_issue_is_idempotent_for_same_booking(): void
    {
        $customer = Customer::create([
            'nama' => 'Budi',
            'email' => 'budi@example.com',
            'no_hp' => '081234567891',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Gunung Raung',
            'deskripsi' => 'Wisata alam',
            'kategori' => 'Alam',
            'latitude' => -8.156,
            'longitude' => 113.711,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);

        JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);

        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'ketua_nama' => 'Budi',
            'ketua_email' => 'budi@example.com',
            'ketua_no_hp' => '081234567891',
            'ketua_jenis_tiket' => 1,
            'anggota_names' => [],
            'kode_booking' => 'JGO-TEST-001',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'total_harga' => 25000,
            'status_pemesanan' => 'PAID',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);

        $service = app(BookingTicketService::class);
        $service->issue($booking);
        $service->issue($booking);

        $this->assertDatabaseCount('tiket', 1);
        $this->assertSame($booking->id_pemesanan, $booking->fresh()->tiket()->first()->id_pemesanan);
    }
}
