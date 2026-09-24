<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use App\Services\PaymentGateway\MidtransGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingMemberNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_anggota_names_are_normalized_to_a_list(): void
    {
        $bookingFromList = new Pemesanan;
        $bookingFromList->anggota_names = [
            ['nama' => 'Rehan', 'id_jenis_tiket' => 5],
            ['nama' => 'Dina', 'id_jenis_tiket' => 3],
        ];

        $this->assertSame([
            ['nama' => 'Rehan', 'id_jenis_tiket' => 5],
            ['nama' => 'Dina', 'id_jenis_tiket' => 3],
        ], $bookingFromList->anggota_names);

        $bookingFromSingleObject = new Pemesanan;
        $bookingFromSingleObject->anggota_names = ['nama' => 'Rehan', 'id_jenis_tiket' => 5];

        $this->assertSame([
            ['nama' => 'Rehan', 'id_jenis_tiket' => 5],
        ], $bookingFromSingleObject->anggota_names);

        $bookingFromJsonString = new Pemesanan;
        $bookingFromJsonString->anggota_names = '{"nama":"Rehan","id_jenis_tiket":5}';

        $this->assertSame([
            ['nama' => 'Rehan', 'id_jenis_tiket' => 5],
        ], $bookingFromJsonString->anggota_names);

        $bookingFromMalformedJsonString = new Pemesanan;
        $bookingFromMalformedJsonString->anggota_names = '[{"nama":"Rehan","id_jenis_tiket":5},{"nama":"Farel","id_jenis_tiket":5}]';

        $this->assertSame([
            ['nama' => 'Rehan', 'id_jenis_tiket' => 5],
            ['nama' => 'Farel', 'id_jenis_tiket' => 5],
        ], $bookingFromMalformedJsonString->anggota_names);

        $this->assertSame('Rehan, Farel', $bookingFromMalformedJsonString->anggota_names_text);
    }

    public function test_successful_payment_redirects_directly_to_ticket_without_alert_popup(): void
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

        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'ketua_nama' => 'Andi Wijaya',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567890',
            'ketua_jenis_tiket' => $ticketType->id_jenis_tiket,
            'anggota_names' => [['nama' => 'Sari', 'id_jenis_tiket' => $ticketType->id_jenis_tiket]],
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-TEST-001',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay(),
            'total_harga' => 200000,
            'status_pemesanan' => 'PENDING',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);

        $booking->pembayaran()->create([
            'nominal' => 200000,
            'snap_token' => 'snap-token-test',
            'status_pembayaran' => 'PENDING',
        ]);

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ]);

        $response = $this->get(route('customer.checkout', $booking->id_pemesanan));

        $response->assertOk();
        $response->assertSee("window.alert('Pembayaran berhasil. Anda akan diarahkan ke tiket saya.')");
        $response->assertSee('window.location.href = ticketUrl;');
    }

    public function test_additional_payment_payload_matches_total_charge(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'snap-token-123'], 200),
        ]);

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

        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'ketua_nama' => 'Andi Wijaya',
            'ketua_email' => 'andi@example.com',
            'ketua_no_hp' => '081234567890',
            'ketua_jenis_tiket' => $ticketType->id_jenis_tiket,
            'anggota_names' => [['nama' => 'Sari', 'id_jenis_tiket' => $ticketType->id_jenis_tiket]],
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-TEST-002',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay(),
            'total_harga' => 200000,
            'status_pemesanan' => 'PENDING',
            'batas_waktu_pembayaran' => now()->addHours(2),
        ]);

        $booking->detailPemesanan()->create([
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'jumlah' => 2,
            'subtotal' => 200000,
        ]);

        $gateway = new MidtransGateway;
        $gateway->createPaymentForAmount($booking, 'QRIS', 25000);

        Http::assertSent(fn ($request) => (int) $request['transaction_details']['gross_amount'] === 25000
            && collect($request['item_details'])->sum(fn ($item) => (int) $item['price'] * (int) $item['quantity']) === 25000
        );
    }
}
