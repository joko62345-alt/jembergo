<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSeeText('Kembalian dihitung otomatis');

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
            'metode_pembayaran' => 'QRIS',
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
