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

    public function test_customer_cannot_book_more_than_two_months_in_future(): void
    {
        $customer = Customer::create([
            'nama' => 'Citra',
            'email' => 'citra@example.com',
            'no_hp' => '081234567892',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Taman Botani',
            'deskripsi' => 'Wisata alam edukasi',
            'kategori' => 'Buatan',
            'latitude' => -8.150,
            'longitude' => 113.710,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 30000,
        ]);

        $response = $this->withSession(['jg_user_id' => $customer->id_customer, 'jg_role' => 'CUSTOMER'])
            ->post(route('customer.booking.store', $destination->id_destinasi), [
                'tanggal_kunjungan' => now()->addMonths(3)->toDateString(),
                'ketua_nama' => 'Citra',
                'ketua_email' => 'citra@gmail.com',
                'ketua_no_hp' => '081234567892',
                'peserta' => [[
                    'nama' => 'Citra',
                    'id_jenis_tiket' => 1,
                ]],
            ]);

        $response->assertSessionHasErrors('tanggal_kunjungan');
        $this->assertDatabaseCount('pemesanan', 0);
    }

    public function test_ticket_expires_after_destination_closing_time_on_visit_day(): void
    {
        $customer = Customer::create([
            'nama' => 'Dewi',
            'email' => 'dewi@example.com',
            'no_hp' => '081234567893',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Kebun Raya',
            'deskripsi' => 'Wisata edukasi',
            'kategori' => 'Buatan',
            'latitude' => -8.160,
            'longitude' => 113.720,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00 - 17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 35000,
        ]);

        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'ketua_nama' => 'Dewi',
            'ketua_email' => 'dewi@example.com',
            'ketua_no_hp' => '081234567893',
            'ketua_jenis_tiket' => 1,
            'anggota_names' => [],
            'kode_booking' => 'JGO-TEST-EXPIRE',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->toDateString(),
            'total_harga' => 35000,
            'status_pemesanan' => 'PAID',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);

        $ticket = $booking->tiket()->create([
            'kode_qr' => 'QR-EXPIRE',
            'status_tiket' => 'ACTIVE',
            'waktu_verifikasi' => null,
        ]);

        $this->travelTo(now()->setTime(18, 0));
        $booking->expireTicketsIfPastVisitDate();

        $this->assertSame('EXPIRED', $ticket->fresh()->status_tiket);
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
