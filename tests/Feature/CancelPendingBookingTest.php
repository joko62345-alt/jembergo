<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CancelPendingBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_a_pending_booking_before_payment_starts(): void
    {
        ['customer' => $customer, 'booking' => $booking] = $this->createPendingBooking();

        $this->withSession($this->customerSession($customer))
            ->get(route('customer.checkout', $booking->id_pemesanan))
            ->assertOk()
            ->assertSee('Batalkan pesanan')
            ->assertSee('cancelBookingModal')
            ->assertSee('Tidak, kembali');

        $this->withSession($this->customerSession($customer))
            ->get(route('customer.orders'))
            ->assertOk()
            ->assertDontSee('Batalkan');

        $this->withSession($this->customerSession($customer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Pesanan berhasil dibatalkan.');

        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pemesanan' => 'CANCELLED',
        ]);
        $this->assertDatabaseHas('pembayaran', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pembayaran' => 'CANCELLED',
        ]);
        $this->assertDatabaseCount('tiket', 0);
        $this->withSession($this->customerSession($customer))
            ->get(route('customer.orders'))
            ->assertOk()
            ->assertSee('Dibatalkan')
            ->assertDontSee('Menunggu pembayaran')
            ->assertDontSee('order-action primary');

        $this->get(route('customer.checkout', $booking->id_pemesanan))->assertGone();
    }

    public function test_customer_can_rebook_after_cancelling_an_unpaid_booking(): void
    {
        ['customer' => $customer, 'booking' => $booking, 'destination' => $destination, 'ticketType' => $ticketType] = $this->createPendingBooking();
        $this->withSession($this->customerSession($customer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertRedirect(route('home'));

        $this->post(route('customer.booking.store', $destination->id_destinasi), [
            'tanggal_kunjungan' => $booking->tanggal_kunjungan->toDateString(),
            'ketua_nama' => $customer->nama,
            'ketua_email' => $customer->email,
            'ketua_no_hp' => $customer->no_hp,
            'peserta' => [[
                'nama' => $customer->nama,
                'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseCount('pemesanan', 2);
    }

    public function test_customer_cannot_cancel_a_booking_after_payment_has_started(): void
    {
        ['customer' => $customer, 'booking' => $booking] = $this->createPendingBooking('JGO-PENDING-001');

        $this->withSession($this->customerSession($customer))
            ->get(route('customer.qris', $booking->id_pemesanan))
            ->assertOk()
            ->assertDontSee('Batalkan pesanan');
        $this->withSession($this->customerSession($customer))
            ->get(route('customer.checkout', $booking->id_pemesanan))
            ->assertOk()
            ->assertDontSee('Batalkan pesanan');
        $this->withSession($this->customerSession($customer))
            ->get(route('customer.orders'))
            ->assertOk()
            ->assertDontSee('Batalkan');

        $this->withSession($this->customerSession($customer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertStatus(422)
            ->assertSee('Pesanan hanya dapat dibatalkan sebelum pembayaran dimulai.');

        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pemesanan' => 'PENDING',
        ]);
        Http::assertNothingSent();
    }

    public function test_customer_cannot_cancel_another_customers_booking(): void
    {
        ['booking' => $booking] = $this->createPendingBooking();
        $otherCustomer = Customer::create([
            'nama' => 'Budi Wijaya',
            'email' => 'budi@gmail.com',
            'no_hp' => '081234567891',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);

        $this->withSession($this->customerSession($otherCustomer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertNotFound();

        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pemesanan' => 'PENDING',
        ]);
    }

    public function test_checkout_cannot_cancel_once_a_gateway_transaction_exists(): void
    {
        ['customer' => $customer, 'booking' => $booking] = $this->createPendingBooking('JGO-PENDING-001');

        $this->withSession($this->customerSession($customer))
            ->get(route('customer.checkout', $booking->id_pemesanan))
            ->assertOk()
            ->assertDontSee('Batalkan pesanan');

        $this->withSession($this->customerSession($customer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertStatus(422);

        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pemesanan' => 'PENDING',
        ]);
        $this->assertDatabaseHas('pembayaran', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pembayaran' => 'PENDING',
        ]);
        Http::assertNothingSent();
    }

    public function test_paid_booking_cannot_be_cancelled(): void
    {
        ['customer' => $customer, 'booking' => $booking] = $this->createPendingBooking();
        $booking->update(['status_pemesanan' => 'PAID']);
        $booking->pembayaran()->update(['status_pembayaran' => 'PAID']);
        $booking->tiket()->create(['kode_qr' => 'QR-PAID', 'status_tiket' => 'ACTIVE']);

        $this->withSession($this->customerSession($customer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertStatus(422);

        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pemesanan' => 'PAID',
        ]);
    }

    public function test_late_payment_notification_does_not_restore_a_cancelled_booking(): void
    {
        $serverKey = 'sandbox-server-key';
        config([
            'services.midtrans.server_key' => $serverKey,
            'services.midtrans.is_production' => false,
            'payment_gateway.driver' => 'midtrans',
        ]);
        Http::fake([
            'api.sandbox.midtrans.com/v2/JGO-PENDING-001/status' => Http::response([
                'transaction_status' => 'settlement',
                'transaction_id' => 'late-settlement',
                'payment_type' => 'qris',
                'gross_amount' => '25000.00',
            ]),
        ]);
        ['customer' => $customer, 'booking' => $booking] = $this->createPendingBooking();

        $this->withSession($this->customerSession($customer))
            ->post(route('customer.booking.cancel', $booking->id_pemesanan))
            ->assertRedirect(route('home'));

        $booking->pembayaran()->update([
            'order_id' => 'JGO-PENDING-001',
            'qris_url' => 'https://api.sandbox.midtrans.com/v2/qris/test',
        ]);
        $amount = '25000.00';
        $this->postJson(route('payment.midtrans.notification'), [
            'order_id' => 'JGO-PENDING-001',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'transaction_id' => 'late-settlement',
            'payment_type' => 'qris',
            'gross_amount' => $amount,
            'signature_key' => hash('sha512', 'JGO-PENDING-001200'.$amount.$serverKey),
        ])->assertOk()->assertJsonPath('message', 'Already processed.');

        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_pemesanan' => 'CANCELLED',
        ]);
        $this->assertDatabaseCount('tiket', 0);
    }

    /**
     * @return array{customer: Customer, destination: DestinasiWisata, ticketType: JenisTiket, booking: Pemesanan}
     */
    private function createPendingBooking(?string $orderId = null): array
    {
        $customer = Customer::create([
            'nama' => 'Andi Wijaya',
            'email' => 'andi@gmail.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Pantai Kuta',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Alam',
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
            'harga' => 25000,
        ]);
        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'ketua_nama' => $customer->nama,
            'ketua_email' => $customer->email,
            'ketua_no_hp' => $customer->no_hp,
            'ketua_jenis_tiket' => $ticketType->id_jenis_tiket,
            'anggota_names' => [],
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-TEST-001',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'total_harga' => 25000,
            'status_pemesanan' => 'PENDING',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);
        $booking->detailPemesanan()->create([
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'jumlah' => 1,
            'subtotal' => 25000,
        ]);
        $booking->pembayaran()->create([
            'order_id' => $orderId,
            'qris_url' => $orderId === null ? null : 'https://api.sandbox.midtrans.com/v2/qris/test',
            'nominal' => 25000,
            'gross_amount' => $orderId === null ? null : 25000,
            'status_pembayaran' => 'PENDING',
        ]);

        return compact('customer', 'destination', 'ticketType', 'booking');
    }

    /**
     * @return array{jg_user_id: int, jg_role: string}
     */
    private function customerSession(Customer $customer): array
    {
        return [
            'jg_user_id' => $customer->id_customer,
            'jg_role' => 'CUSTOMER',
        ];
    }
}
