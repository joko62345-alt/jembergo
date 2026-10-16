<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdditionalPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_additional_member_payment_uses_the_same_qris_checkout_page_as_a_new_booking(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.is_production' => false,
        ]);
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'QRIS transaction is created',
                'order_id' => 'JGO-JGO-TEST-001-CHG-ABCDEFGH',
                'transaction_id' => 'sandbox-additional-transaction',
                'transaction_status' => 'pending',
                'payment_type' => 'qris',
                'gross_amount' => '25000.00',
                'expiry_time' => now()->addHours(2)->toDateTimeString(),
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'url' => 'https://api.sandbox.midtrans.com/v2/qris/gopay/test/qr-code',
                    ],
                ],
            ], 201),
            'api.sandbox.midtrans.com/v2/qris/*/qr-code' => Http::response('qr-image', 200, [
                'Content-Type' => 'image/png',
            ]),
            'api.sandbox.midtrans.com/v2/*/status' => Http::sequence()
                ->push([
                    'transaction_status' => 'pending',
                    'payment_type' => 'qris',
                ])
                ->push([
                    'status_code' => '401',
                    'status_message' => 'Access denied due to unauthorized',
                ], 401)
                ->push([
                    'transaction_status' => 'settlement',
                    'transaction_id' => 'sandbox-additional-transaction',
                    'payment_type' => 'qris',
                    'gross_amount' => '25000.00',
                ]),
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
            'tanggal_kunjungan' => now()->addDay(),
            'total_harga' => 25000,
            'status_pemesanan' => 'PAID',
        ]);
        $booking->pembayaran()->create([
            'nominal' => 25000,
            'status_pembayaran' => 'PAID',
        ]);
        $booking->tiket()->create([
            'kode_qr' => 'QR-TEST-001',
            'status_tiket' => 'ACTIVE',
        ]);
        $token = $customer->createToken('mobile', ['CUSTOMER'])->plainTextToken;
        $apiBasePath = '/api/v1/customer/pesanan/'.$booking->id_pemesanan;

        $this->withToken($token)
            ->getJson($apiBasePath.'/kelola')
            ->assertOk()
            ->assertJsonPath('data.booking.code', $booking->kode_booking)
            ->assertJsonPath('data.booking.participants.0.name', $customer->nama)
            ->assertJsonPath('data.ticket_types.0.price', 25000)
            ->assertJsonPath('data.remaining_slots', 9);

        $changeResponse = $this->withToken($token)->postJson($apiBasePath.'/kelola', [
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'anggota' => [
                [
                    'nama' => 'Sari Wijaya',
                    'id_jenis_tiket' => $ticketType->id_jenis_tiket,
                ],
            ],
        ]);
        $changeResponse->assertCreated()
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.amount', 25000);
        $change = $booking->perubahan()->latest('id_perubahan')->firstOrFail();
        $change->update(['snap_token' => 'legacy-snap-token']);

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ]);

        $this->get(route('customer.change.checkout', [
            $booking->id_pemesanan,
            $change->id_perubahan,
        ]))
            ->assertOk()
            ->assertSee('Lanjut ke Pembayaran')
            ->assertDontSee('snap.js');

        $response = $this->post(route('customer.change.payment', [
            $booking->id_pemesanan,
            $change->id_perubahan,
        ]));

        $response->assertRedirect(route('customer.change.qris', [
            $booking->id_pemesanan,
            $change->id_perubahan,
        ]));
        $this->assertDatabaseHas('perubahan_pemesanan', [
            'id_perubahan' => $change->id_perubahan,
            'order_id' => 'JGO-JGO-TEST-001-CHG-ABCDEFGH',
            'nominal' => 25000,
            'gross_amount' => 25000,
            'status' => 'PENDING',
            'snap_token' => null,
            'qris_url' => 'https://api.sandbox.midtrans.com/v2/qris/gopay/test/qr-code',
        ]);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.sandbox.midtrans.com/v2/charge'
            && $request['payment_type'] === 'qris'
            && $request['transaction_details']['gross_amount'] === 25000);

        $this->get(route('customer.change.qris', [
            $booking->id_pemesanan,
            $change->id_perubahan,
        ]))
            ->assertOk()
            ->assertSee('Scan dan selesaikan pembayaran.')
            ->assertSee(asset('images/logo-qris-transparent.png'), false)
            ->assertSee('Salin tautan Midtrans')
            ->assertSee('https://api.sandbox.midtrans.com/v2/qris/gopay/test/qr-code')
            ->assertSee(route('customer.change.qris.image', [
                $booking->id_pemesanan,
                $change->id_perubahan,
            ]), false);

        $paymentResponse = $this->withToken($token)->postJson(
            $apiBasePath.'/perubahan/'.$change->id_perubahan.'/pembayaran',
        );
        $paymentResponse->assertOk()
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.nominal', 25000);
        $qrImageUrl = $paymentResponse->json('data.qris_image_url');
        $this->withToken($token)->get($qrImageUrl)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent('qr-image');

        $this->withToken($token)->getJson(
            $apiBasePath.'/perubahan/'.$change->id_perubahan.'/pembayaran',
        )
            ->assertStatus(502)
            ->assertJsonPath('message', 'Status pembayaran belum dapat diverifikasi oleh Midtrans. Coba periksa koneksi server dan credential Midtrans.');

        $this->withToken($token)->getJson(
            $apiBasePath.'/perubahan/'.$change->id_perubahan.'/pembayaran',
        )
            ->assertOk()
            ->assertJsonPath('data.status', 'PAID');

        $this->assertDatabaseHas('perubahan_pemesanan', [
            'id_perubahan' => $change->id_perubahan,
            'status' => 'PAID',
            'transaction_status' => 'settlement',
        ]);
        $this->assertDatabaseHas('pemesanan', [
            'id_pemesanan' => $booking->id_pemesanan,
            'total_harga' => 50000,
        ]);
        $this->assertDatabaseHas('tiket', [
            'id_pemesanan' => $booking->id_pemesanan,
            'status_tiket' => 'ACTIVE',
        ]);

        $this->withSession([
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ])->getJson(route('customer.change.qris', [
            $booking->id_pemesanan,
            $change->id_perubahan,
        ]))
            ->assertOk()
            ->assertJsonPath('status', 'PAID')
            ->assertJsonMissingPath('data.status');
    }
}
