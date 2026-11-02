<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Support\StatusLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_payment_status_is_displayed_as_failed(): void
    {
        $this->assertSame('Gagal', StatusLabel::payment('EXPIRED'));
        $this->assertSame('Gagal', StatusLabel::order('EXPIRED'));
    }

    public function test_admin_report_is_limited_to_its_destination(): void
    {
        $destination = $this->createDestination('Wisata Admin');
        $otherDestination = $this->createDestination('Wisata Lain');
        $admin = $this->createAdmin($destination);
        $customer = $this->createCustomer();
        $this->createBooking($customer, $destination, 'BOOKING-ADMIN-REPORT');
        $this->createBooking($customer, $destination, 'BOOKING-EXPIRED-REPORT', 'EXPIRED');
        $this->createBooking($customer, $otherDestination, 'BOOKING-OTHER-REPORT');
        $oldBooking = $this->createBooking($customer, $destination, 'BOOKING-OLD-REPORT');
        $oldBooking->update(['tanggal_pemesanan' => now()->subDay()]);

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->get(route('admin.report', [
                'id_destinasi' => $otherDestination->id_destinasi,
                'from' => today()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Wisata Admin')
            ->assertSee('BOOKING-ADMIN-REPORT')
            ->assertSee('BOOKING-EXPIRED-REPORT')
            ->assertSee('Gagal')
            ->assertSeeInOrder(['Nilai seluruh transaksi', 'Rp 50.000'])
            ->assertSeeInOrder(['Pendapatan', 'Rp 25.000'])
            ->assertDontSee('BOOKING-OTHER-REPORT')
            ->assertDontSee('BOOKING-OLD-REPORT')
            ->assertDontSee('Wisata Lain');

        $previewResponse = $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->get(route('admin.report.preview', ['id_destinasi' => $otherDestination->id_destinasi]))
            ->assertOk()
            ->assertSee('Wisata Admin')
            ->assertSee('BOOKING-ADMIN-REPORT')
            ->assertDontSee('BOOKING-OTHER-REPORT');
        $previewText = preg_replace('/\s+/', ' ', preg_replace('/<[^>]+>/', ' ', $previewResponse->getContent()));
        $this->assertStringContainsString('TOTAL PENDAPATAN Rp 50.000', $previewText);

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])
            ->get(route('admin.report.pdf', ['id_destinasi' => $otherDestination->id_destinasi]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_super_admin_report_counts_only_paid_bookings_as_revenue(): void
    {
        $destination = $this->createDestination('Wisata Admin');
        $customer = $this->createCustomer();
        $this->createBooking($customer, $destination, 'BOOKING-PAID-REPORT');
        $this->createBooking($customer, $destination, 'BOOKING-EXPIRED-REPORT', 'EXPIRED');

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->get(route('superadmin.report', ['id_destinasi' => $destination->id_destinasi]))
            ->assertOk()
            ->assertSee('BOOKING-PAID-REPORT')
            ->assertSee('BOOKING-EXPIRED-REPORT')
            ->assertSee('Gagal')
            ->assertSeeInOrder(['Total pendapatan', 'Rp 25.000']);

        $previewResponse = $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->get(route('superadmin.report.preview', ['id_destinasi' => $destination->id_destinasi]))
            ->assertOk();
        $previewText = preg_replace('/\s+/', ' ', preg_replace('/<[^>]+>/', ' ', $previewResponse->getContent()));
        $this->assertStringContainsString('TOTAL PENDAPATAN Rp 25.000', $previewText);
    }

    public function test_admin_csv_report_is_limited_to_its_destination(): void
    {
        $destination = $this->createDestination('Wisata Admin');
        $otherDestination = $this->createDestination('Wisata Lain');
        $admin = $this->createAdmin($destination);
        $customer = $this->createCustomer();
        $this->createBooking($customer, $destination, 'BOOKING-ADMIN-REPORT');
        $this->createBooking($customer, $otherDestination, 'BOOKING-OTHER-REPORT');

        $response = $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
        ])->get(route('admin.report.export', ['id_destinasi' => $otherDestination->id_destinasi]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('BOOKING-ADMIN-REPORT', $csv);
        $this->assertStringNotContainsString('BOOKING-OTHER-REPORT', $csv);
    }

    private function createDestination(string $name): DestinasiWisata
    {
        return DestinasiWisata::create([
            'nama_wisata' => $name,
            'deskripsi' => 'Destinasi untuk pengujian',
            'kategori' => 'Bahari',
            'latitude' => -8.123,
            'longitude' => 113.456,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00 - 17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
    }

    private function createAdmin(DestinasiWisata $destination): AdminPariwisata
    {
        return AdminPariwisata::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama' => 'Admin Wisata',
            'email' => 'admin.wisata@gmail.com',
            'no_hp' => '081234567891',
            'password' => bcrypt('password'),
            'status_akun' => 'AKTIF',
        ]);
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'nama' => 'Pelanggan Test',
            'email' => 'pelanggan@example.com',
            'password' => bcrypt('password'),
            'auth_provider' => 'manual',
        ]);
    }

    private function createBooking(
        Customer $customer,
        DestinasiWisata $destination,
        string $code,
        string $status = 'PAID',
    ): Pemesanan {
        return Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => $code,
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'total_harga' => 25000,
            'status_pemesanan' => $status,
        ]);
    }
}
