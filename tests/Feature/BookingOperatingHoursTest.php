<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use App\Support\StatusLabel;
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

    public function test_pending_booking_expires_after_two_hours_and_does_not_consume_quota(): void
    {
        $this->travelTo(now()->startOfMinute());
        [$customer, $destination, $ticketType] = $this->createBookingFixture();
        $destination->update([
            'kuota_harian_aktif' => true,
            'kuota_harian' => 10,
        ]);
        $booking = Pemesanan::query()->create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-PENDING-EXPIRY',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => today()->addDay(),
            'total_harga' => $ticketType->harga * 3,
            'status_pemesanan' => 'PENDING',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);
        $booking->detailPemesanan()->create([
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'jumlah' => 3,
            'subtotal' => $ticketType->harga * 3,
        ]);
        $payment = $booking->pembayaran()->create([
            'nominal' => $booking->total_harga,
            'status_pembayaran' => 'PENDING',
        ]);

        $this->travel(119)->minutes();
        Pemesanan::expirePendingPayments();
        $this->assertSame('PENDING', $booking->fresh()->status_pemesanan);
        $this->assertSame(0, $destination->bookedTicketsForDate(today()->addDay()->toDateString()));

        $this->travel(1)->minutes();
        Pemesanan::expirePendingPayments();

        $this->assertSame('EXPIRED', $booking->fresh()->status_pemesanan);
        $this->assertSame('EXPIRED', $payment->fresh()->status_pembayaran);
        $this->assertSame('expire', $payment->fresh()->transaction_status);
        $this->assertSame('Gagal', StatusLabel::order($booking->fresh()->status_pemesanan));
        $this->assertSame(0, $destination->bookedTicketsForDate(today()->addDay()->toDateString()));

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_role' => 'CUSTOMER',
        ])->get(route('customer.orders'))
            ->assertOk()
            ->assertSee('Gagal')
            ->assertSee('order-status danger', false);
    }

    public function test_only_paid_bookings_reduce_daily_destination_quota(): void
    {
        [$customer, $destination, $ticketType] = $this->createBookingFixture();
        $destination->update([
            'kuota_harian_aktif' => true,
            'kuota_harian' => 10,
        ]);
        $visitDate = today()->addDay()->toDateString();

        foreach (['PENDING', 'EXPIRED', 'PAID'] as $index => $status) {
            $booking = Pemesanan::query()->create([
                'id_customer' => $customer->id_customer,
                'id_destinasi' => $destination->id_destinasi,
                'kode_booking' => 'JGO-QUOTA-'.$index,
                'tanggal_pemesanan' => now(),
                'tanggal_kunjungan' => $visitDate,
                'total_harga' => $ticketType->harga * 2,
                'status_pemesanan' => $status,
            ]);
            $booking->detailPemesanan()->create([
                'id_jenis_tiket' => $ticketType->id_jenis_tiket,
                'jumlah' => 2,
                'subtotal' => $ticketType->harga * 2,
            ]);
        }

        $this->assertSame(2, $destination->bookedTicketsForDate($visitDate));
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
