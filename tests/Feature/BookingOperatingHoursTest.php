<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingOperatingHoursTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_book_for_today_after_closing_but_can_book_for_tomorrow(): void
    {
        $this->travelTo(now()->setTime(18, 0));
        [$customer, $destination, $ticketType] = $this->createBookingFixture();
        $session = ['jg_user_id' => $customer->id_customer, 'jg_role' => 'CUSTOMER'];
        $payload = [
            'ketua_nama' => 'Citra Wijaya',
            'ketua_email' => 'citra@gmail.com',
            'ketua_no_hp' => '081234567890',
            'peserta' => [[
                'nama' => 'Citra Wijaya',
                'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            ]],
        ];

        $this->withSession($session)
            ->from(route('customer.booking.create', $destination->id_destinasi))
            ->post(route('customer.booking.store', $destination->id_destinasi), [
                ...$payload,
                'tanggal_kunjungan' => today()->toDateString(),
            ])
            ->assertRedirect(route('customer.booking.create', $destination->id_destinasi))
            ->assertSessionHasErrors('tanggal_kunjungan');

        $this->assertDatabaseCount('pemesanan', 0);

        $this->withSession($session)
            ->post(route('customer.booking.store', $destination->id_destinasi), [
                ...$payload,
                'tanggal_kunjungan' => today()->addDay()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('pemesanan', 1);
    }

    public function test_ticket_expiration_tracks_updated_destination_closing_time(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        [$customer, $destination, $ticketType] = $this->createBookingFixture();
        $booking = Pemesanan::query()->create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-OPERATING-HOURS',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => today(),
            'total_harga' => $ticketType->harga,
            'status_pemesanan' => 'PAID',
        ]);
        $booking->pembayaran()->create([
            'nominal' => $ticketType->harga,
            'status_pembayaran' => 'PAID',
        ]);
        $ticket = $booking->tiket()->create([
            'kode_qr' => 'QR-OPERATING-HOURS',
            'status_tiket' => 'ACTIVE',
        ]);

        $this->travelTo(now()->setTime(18, 0));
        Pemesanan::expireTicketsPastVisitDate();
        $this->assertSame('EXPIRED', $ticket->fresh()->status_tiket);

        $destination->update(['jam_operasional' => '08:00-19:00']);
        Pemesanan::expireTicketsPastVisitDate();
        $this->assertSame('ACTIVE', $ticket->fresh()->status_tiket);

        $destination->update(['jam_operasional' => '08:00-17:30']);
        Pemesanan::expireTicketsPastVisitDate();
        $this->assertSame('EXPIRED', $ticket->fresh()->status_tiket);
    }

    /**
     * @return array{0: Customer, 1: DestinasiWisata, 2: JenisTiket}
     */
    private function createBookingFixture(): array
    {
        $customer = Customer::query()->create([
            'nama' => 'Citra Wijaya',
            'email' => 'citra@gmail.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::query()->create([
            'nama_wisata' => 'Pantai Jember',
            'deskripsi' => 'Destinasi wisata',
            'kategori' => 'Bahari',
            'latitude' => -8.152,
            'longitude' => 113.719,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $ticketType = JenisTiket::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Reguler',
            'harga' => 25000,
        ]);

        return [$customer, $destination, $ticketType];
    }
}
