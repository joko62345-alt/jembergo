<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CounterSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sell_at_the_counter_and_sales_reduce_daily_quota(): void
    {
        $destination = $this->createDestination(quota: 4);
        $ticketType = $this->createTicketType($destination);
        $admin = $this->createAdmin($destination);

        $response = $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])->post(route('admin.counter-sales.store'), [
            'jumlah' => [$ticketType->id_jenis_tiket => 2],
            'metode_pembayaran' => 'TUNAI',
            'uang_diterima' => 25000,
        ]);

        $booking = Pemesanan::query()->where('kanal_penjualan', 'LOKET')->firstOrFail();
        $response->assertRedirect(route('admin.counter-sales.show', $booking->id_pemesanan));
        $this->assertNull($booking->id_customer);
        $this->assertSame($admin->id_admin, $booking->id_admin_pariwisata);
        $this->assertSame('PAID', $booking->status_pemesanan);
        $this->assertSame(2, (int) $booking->detailPemesanan()->sum('jumlah'));
        $this->assertSame('TUNAI', $booking->pembayaran->metode_pembayaran);
        $this->assertSame('PAID', $booking->pembayaran->status_pembayaran);
        $this->assertSame(25000.0, (float) $booking->pembayaran->uang_diterima);
        $this->assertSame(5000.0, (float) $booking->pembayaran->uang_kembalian);
        $this->assertSame(1, $booking->tiket()->count());
        $this->assertSame('USED', $booking->tiket()->firstOrFail()->status_tiket);
        $this->assertNotNull($booking->tiket()->firstOrFail()->waktu_verifikasi);
        $this->assertSame(2, $destination->fresh()->bookedTicketsForDate(today()->toDateString()));

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->get(route('admin.counter-sales.show', $booking->id_pemesanan))
            ->assertOk()
            ->assertSee($booking->kode_booking)
            ->assertSeeText('BUKTI VERIFIKASI TIKET')
            ->assertSeeText('VERIFIKASI BERHASIL')
            ->assertSeeText('Rp 10.000')
            ->assertSeeText('Uang diterima')
            ->assertSeeText('Rp 25.000')
            ->assertSeeText('Kembalian')
            ->assertSeeText('Rp 5.000');

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->get(route('admin.counter-sales.create'))
            ->assertOk()
            ->assertSee('nav-link active')
            ->assertSeeText('Penjualan tiket di tempat')
            ->assertSeeText('Sisa kuota hari ini 2 dari 4 tiket')
            ->assertSeeText('Uang diterima')
            ->assertSeeText('Kembalian dihitung otomatis')
            ->assertSeeText('Simpan transaksi');

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->from(route('admin.counter-sales.create'))
            ->post(route('admin.counter-sales.store'), [
                'jumlah' => [$ticketType->id_jenis_tiket => 1],
                'metode_pembayaran' => 'TUNAI',
                'uang_diterima' => 9999,
            ])
            ->assertRedirect(route('admin.counter-sales.create'))
            ->assertSessionHasErrors('uang_diterima');

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->from(route('admin.counter-sales.create'))
            ->post(route('admin.counter-sales.store'), [
                'jumlah' => [$ticketType->id_jenis_tiket => 3],
                'metode_pembayaran' => 'QRIS',
            ])
            ->assertRedirect(route('admin.counter-sales.create'))
            ->assertSessionHasErrors('jumlah');

        $this->assertSame(1, Pemesanan::query()->where('kanal_penjualan', 'LOKET')->count());
    }

    public function test_counter_sales_are_blocked_outside_destination_opening_hours(): void
    {
        $destination = $this->createDestination();
        $destination->update(['jam_operasional' => '08:00 - 17:00']);
        $ticketType = $this->createTicketType($destination);
        $admin = $this->createAdmin($destination);
        $this->travelTo(today()->setTime(7, 59));

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->post(route('admin.counter-sales.store'), [
                'jumlah' => [$ticketType->id_jenis_tiket => 1],
                'metode_pembayaran' => 'TUNAI',
                'uang_diterima' => 10000,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('jam_operasional');

        $this->travelTo(today()->setTime(17, 0));
        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->post(route('admin.counter-sales.store'), [
                'jumlah' => [$ticketType->id_jenis_tiket => 1],
                'metode_pembayaran' => 'TUNAI',
                'uang_diterima' => 10000,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('jam_operasional');

        $this->assertSame(0, Pemesanan::query()->where('kanal_penjualan', 'LOKET')->count());
    }

    public function test_qris_counter_sales_use_midtrans_and_verify_the_ticket_after_payment(): void
    {
        config([
            'services.midtrans.server_key' => 'sandbox-server-key',
            'services.midtrans.is_production' => false,
        ]);
        $destination = $this->createDestination();
        $ticketType = $this->createTicketType($destination);
        $admin = $this->createAdmin($destination);
        $this->app->instance(PaymentGateway::class, new class implements PaymentGateway
        {
            public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array
            {
                return [
                    'order_id' => 'JGO-COUNTER-ORDER',
                    'transaction_id' => 'counter-transaction',
                    'transaction_status' => 'pending',
                    'payment_type' => 'qris',
                    'gross_amount' => (float) $booking->total_harga,
                    'reference' => 'counter-transaction',
                    'qris_url' => 'https://api.sandbox.midtrans.com/v2/qris/gopay/counter/qr-code',
                    'qris_expires_at' => now()->addHour()->toIso8601String(),
                ];
            }

            public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array
            {
                return [];
            }
        });
        Http::fake([
            'api.sandbox.midtrans.com/v2/JGO-COUNTER-ORDER/status' => Http::sequence()
                ->push(['transaction_status' => 'pending', 'gross_amount' => '10000.00'], 200)
                ->push([
                    'transaction_status' => 'settlement',
                    'transaction_id' => 'counter-transaction',
                    'payment_type' => 'qris',
                    'gross_amount' => '10000.00',
                ], 200),
            'api.sandbox.midtrans.com/v2/qris/gopay/counter/qr-code' => Http::response('qr-image', 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $response = $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])->post(route('admin.counter-sales.store'), [
            'jumlah' => [$ticketType->id_jenis_tiket => 1],
            'metode_pembayaran' => 'QRIS',
        ]);

        $booking = Pemesanan::query()->where('kanal_penjualan', 'LOKET')->firstOrFail();
        $response->assertRedirect(route('admin.counter-sales.qris', $booking->id_pemesanan));
        $this->assertSame('PENDING', $booking->status_pemesanan);
        $this->assertSame('PENDING', $booking->pembayaran->status_pembayaran);
        $this->assertSame('JGO-COUNTER-ORDER', $booking->pembayaran->order_id);
        $this->assertSame(0, $booking->tiket()->count());

        $adminSession = [
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ];
        $this->withSession($adminSession)
            ->get(route('admin.counter-sales.qris', $booking->id_pemesanan))
            ->assertOk()
            ->assertSeeText('Scan dan selesaikan pembayaran.')
            ->assertSee(route('admin.counter-sales.qris.image', $booking->id_pemesanan))
            ->assertSee('qris-code-card')
            ->assertSee('copyMidtransLink')
            ->assertSee('paymentSuccessModal')
            ->assertSee('bootstrap.bundle.min.js')
            ->assertSeeText('Penjualan tiket di tempat')
            ->assertSeeText('1 tiket');
        $this->withSession($adminSession)
            ->get(route('admin.counter-sales.show', $booking->id_pemesanan))
            ->assertRedirect(route('admin.counter-sales.qris', $booking->id_pemesanan));

        $this->withSession($adminSession)
            ->getJson(route('admin.counter-sales.qris', $booking->id_pemesanan))
            ->assertOk()
            ->assertJsonPath('status', 'PENDING');

        $this->withSession($adminSession)
            ->get(route('admin.counter-sales.qris.image', $booking->id_pemesanan))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent('qr-image');

        $grossAmount = '10000.00';
        $serverKey = 'sandbox-server-key';
        $this->postJson(route('payment.midtrans.notification'), [
            'order_id' => 'JGO-COUNTER-ORDER',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'transaction_id' => 'counter-transaction',
            'payment_type' => 'qris',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', 'JGO-COUNTER-ORDER200'.$grossAmount.$serverKey),
        ])->assertOk()->assertJsonPath('message', 'Notification processed.');

        $this->assertSame('PAID', $booking->fresh()->status_pemesanan);
        $this->assertSame('PAID', $booking->pembayaran->fresh()->status_pembayaran);
        $this->assertSame('USED', $booking->tiket()->firstOrFail()->status_tiket);
        $this->assertNotNull($booking->tiket()->firstOrFail()->waktu_verifikasi);
    }

    public function test_counter_sale_does_not_issue_tickets_when_qris_creation_fails(): void
    {
        $destination = $this->createDestination();
        $ticketType = $this->createTicketType($destination);
        $admin = $this->createAdmin($destination);
        $this->app->instance(PaymentGateway::class, new class implements PaymentGateway
        {
            public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array
            {
                return [];
            }

            public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array
            {
                return [];
            }
        });

        $adminSession = [
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ];
        $this->withSession($adminSession)
            ->from(route('admin.counter-sales.create'))
            ->post(route('admin.counter-sales.store'), [
                'jumlah' => [$ticketType->id_jenis_tiket => 1],
                'metode_pembayaran' => 'QRIS',
            ])
            ->assertRedirect(route('admin.counter-sales.create'))
            ->assertSessionHasErrors('payment');

        $this->withSession($adminSession)
            ->get(route('admin.counter-sales.create'))
            ->assertOk()
            ->assertSeeText('Lanjut pembayaran');

        $booking = Pemesanan::query()->where('kanal_penjualan', 'LOKET')->firstOrFail();
        $this->assertSame('FAILED', $booking->status_pemesanan);
        $this->assertSame('FAILED', $booking->pembayaran->status_pembayaran);
        $this->assertSame(0, $booking->tiket()->count());
    }

    public function test_admin_and_super_admin_can_filter_reports_by_sales_channel(): void
    {
        $destination = $this->createDestination();
        $ticketType = $this->createTicketType($destination);
        $admin = $this->createAdmin($destination);
        $customer = Customer::create([
            'nama' => 'Pembeli Online',
            'email' => 'online@example.com',
            'password' => bcrypt('password'),
            'auth_provider' => 'manual',
        ]);
        $onlineBooking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'ONLINE-REPORT-001',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => today(),
            'total_harga' => 10000,
            'status_pemesanan' => 'PAID',
        ]);
        $onlineBooking->detailPemesanan()->create([
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'jumlah' => 1,
            'subtotal' => 10000,
        ]);

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])->post(route('admin.counter-sales.store'), [
            'jumlah' => [$ticketType->id_jenis_tiket => 1],
            'metode_pembayaran' => 'TUNAI',
            'uang_diterima' => 10000,
        ])->assertRedirect();
        $counterBookingCode = Pemesanan::query()->where('kanal_penjualan', 'LOKET')->value('kode_booking');

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->get(route('admin.report', ['kanal_penjualan' => 'LOKET']))
            ->assertOk()
            ->assertSee('Beli di tempat')
            ->assertDontSee('ONLINE-REPORT-001');

        $adminExport = $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])->get(route('admin.report.export', ['kanal_penjualan' => 'LOKET']));
        $adminExport->assertOk();
        $this->assertStringContainsString($counterBookingCode, $adminExport->streamedContent());
        $this->assertStringNotContainsString('ONLINE-REPORT-001', $adminExport->streamedContent());

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->get(route('superadmin.report', [
                'id_destinasi' => $destination->id_destinasi,
                'kanal_penjualan' => 'ONLINE',
            ]))
            ->assertOk()
            ->assertSee('ONLINE-REPORT-001')
            ->assertDontSee($counterBookingCode);

        $superAdminExport = $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->get(route('superadmin.report.export', [
                'id_destinasi' => $destination->id_destinasi,
                'kanal_penjualan' => 'ONLINE',
            ]));
        $superAdminExport->assertOk();
        $this->assertStringContainsString('ONLINE-REPORT-001', $superAdminExport->streamedContent());
        $this->assertStringNotContainsString($counterBookingCode, $superAdminExport->streamedContent());
    }

    private function createDestination(?int $quota = null): DestinasiWisata
    {
        return DestinasiWisata::create([
            'nama_wisata' => 'Destinasi Loket',
            'deskripsi' => 'Destinasi untuk pengujian',
            'kategori' => 'Bahari',
            'latitude' => -8.123,
            'longitude' => 113.456,
            'alamat' => 'Jember',
            'jam_operasional' => '00:01 - 23:59',
            'status_aktif' => true,
            'kuota_harian_aktif' => $quota !== null,
            'kuota_harian' => $quota,
        ]);
    }

    private function createTicketType(DestinasiWisata $destination): JenisTiket
    {
        return JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Tiket Umum',
            'harga' => 10000,
        ]);
    }

    private function createAdmin(DestinasiWisata $destination): AdminPariwisata
    {
        return AdminPariwisata::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama' => 'Admin Loket',
            'email' => 'admin.loket@gmail.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
            'status_akun' => 'AKTIF',
        ]);
    }
}
