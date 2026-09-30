<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use App\Services\BookingTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class BookingIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_form_prefills_customer_details_and_allows_edits(): void
    {
        $customer = Customer::create([
            'nama' => 'Citra Wijaya',
            'email' => 'citra@gmail.com',
            'no_hp' => '081234567892',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Air Terjun Tancak',
            'deskripsi' => 'Wisata alam',
            'kategori' => 'Alam',
            'latitude' => -8.036,
            'longitude' => 113.607,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $ticketType = JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);

        $this->withSession(['jg_user_id' => $customer->id_customer, 'jg_role' => 'CUSTOMER'])
            ->get(route('customer.booking.create', $destination->id_destinasi))
            ->assertOk()
            ->assertSee('value="Citra Wijaya"', false)
            ->assertSee('value="citra@gmail.com"', false)
            ->assertSee('value="081234567892"', false);

        $this->withSession(['jg_user_id' => $customer->id_customer, 'jg_role' => 'CUSTOMER'])
            ->post(route('customer.booking.store', $destination->id_destinasi), [
                'ketua_nama' => 'Citra Sari',
                'ketua_email' => 'citra.sari@gmail.com',
                'ketua_no_hp' => '081234567893',
                'tanggal_kunjungan' => now()->addDay()->toDateString(),
                'peserta' => [[
                    'nama' => 'Citra Sari',
                    'id_jenis_tiket' => $ticketType->id_jenis_tiket,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pemesanan', [
            'id_customer' => $customer->id_customer,
            'ketua_nama' => 'Citra Sari',
            'ketua_email' => 'citra.sari@gmail.com',
            'ketua_no_hp' => '081234567893',
        ]);
    }

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

    public function test_ticket_qr_api_returns_minimal_booking_data_for_a_public_scan(): void
    {
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Pantai Papuma',
            'deskripsi' => 'Wisata pantai',
            'kategori' => 'Bahari',
            'latitude' => -8.152,
            'longitude' => 113.719,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $ticketType = JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);
        $customer = Customer::create([
            'nama' => 'Andi',
            'email' => 'andi@example.com',
            'no_hp' => '081234567891',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'ketua_nama' => 'Andi',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567891',
            'ketua_jenis_tiket' => $ticketType->id_jenis_tiket,
            'anggota_names' => [['nama' => 'Budi', 'id_jenis_tiket' => $ticketType->id_jenis_tiket]],
            'kode_booking' => 'JGO-TEST-QR-001',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'total_harga' => 50000,
            'status_pemesanan' => 'PAID',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);
        $qrCode = Crypt::encryptString(json_encode(['booking_code' => $booking->kode_booking], JSON_THROW_ON_ERROR));
        $booking->tiket()->create(['kode_qr' => $qrCode, 'status_tiket' => 'ACTIVE']);

        $this->getJson(route('api.tickets.scan', ['kode_qr' => $qrCode]))
            ->assertOk()
            ->assertJsonPath('data.kode_booking', 'JGO-TEST-QR-001')
            ->assertJsonPath('data.destinasi', 'Pantai Papuma')
            ->assertJsonPath('data.peserta.0.nama', 'Andi')
            ->assertJsonPath('data.peserta.1.nama', 'Budi')
            ->assertJsonPath('data.status_tiket', 'ACTIVE')
            ->assertJsonMissing(['email' => 'andi@example.com'])
            ->assertJsonMissing(['no_hp' => '081234567891']);

        $this->get(route('ticket.scan', ['kode_qr' => $qrCode]))
            ->assertOk()
            ->assertSee('Data Pemesanan')
            ->assertSee('JGO-TEST-QR-001')
            ->assertSee('Andi')
            ->assertSee('Budi')
            ->assertSee('Times New Roman');

        $this->getJson(route('api.tickets.scan', ['kode_qr' => 'invalid-token']))
            ->assertNotFound();
    }
}
