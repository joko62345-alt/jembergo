<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\DetailPemesanan;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_analytics_are_limited_to_the_admins_destination(): void
    {
        $destination = $this->createDestination('Pantai Admin');
        $otherDestination = $this->createDestination('Pantai Lain');
        $admin = $this->createAdmin($destination);
        $customer = $this->createCustomer();
        $ticketType = $this->createTicketType($destination, 'Tiket Admin');
        $otherTicketType = $this->createTicketType($otherDestination, 'Tiket Lain');

        $booking = $this->createBooking($customer, $destination, 'BOOKING-ADMIN-ANALYTICS', 25000);
        DetailPemesanan::create([
            'id_pemesanan' => $booking->id_pemesanan,
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'jumlah' => 2,
            'subtotal' => 25000,
        ]);

        $otherBooking = $this->createBooking($customer, $otherDestination, 'BOOKING-OTHER-ANALYTICS', 900000);
        DetailPemesanan::create([
            'id_pemesanan' => $otherBooking->id_pemesanan,
            'id_jenis_tiket' => $otherTicketType->id_jenis_tiket,
            'jumlah' => 90,
            'subtotal' => 900000,
        ]);

        $this->withSession([
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
            'jg_user_name' => $admin->nama,
        ])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Jenis tiket terlaris')
            ->assertSeeText('Tiket Admin')
            ->assertDontSeeText('Tiket Lain')
            ->assertSeeText('Rp 25.000')
            ->assertDontSee('class="analytics-summary-row"', false)
            ->assertSee('data-chart="transactions"', false)
            ->assertSee('data-chart="revenue"', false);
    }

    public function test_super_admin_dashboard_keeps_its_analytics_charts(): void
    {
        $this->withSession([
            'jg_role' => 'SUPER_ADMIN',
            'jg_user_name' => 'Super Admin',
        ])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Destinasi favorit')
            ->assertSee('class="analytics-summary-row"', false)
            ->assertSee('data-chart="transactions"', false)
            ->assertSee('data-chart="revenue"', false);
    }

    private function createDestination(string $name): DestinasiWisata
    {
        return DestinasiWisata::create([
            'nama_wisata' => $name,
            'deskripsi' => 'Destinasi untuk pengujian analitik',
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
            'nama' => 'Admin Analitik',
            'email' => 'admin.analytics@gmail.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
            'status_akun' => 'AKTIF',
        ]);
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'nama' => 'Pelanggan Analitik',
            'email' => 'customer.analytics@gmail.com',
            'password' => bcrypt('password'),
            'auth_provider' => 'manual',
        ]);
    }

    private function createTicketType(DestinasiWisata $destination, string $name): JenisTiket
    {
        return JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => $name,
            'harga' => 12500,
        ]);
    }

    private function createBooking(
        Customer $customer,
        DestinasiWisata $destination,
        string $code,
        int $amount,
    ): Pemesanan {
        return Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => $code,
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'total_harga' => $amount,
            'status_pemesanan' => 'PAID',
        ]);
    }
}
