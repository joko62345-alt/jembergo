<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\DetailPemesanan;
use App\Models\Fasilitas;
use App\Models\GaleriDestinasi;
use App\Models\JenisTiket;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\PerubahanPemesanan;
use App\Models\Review;
use App\Models\Tiket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationPermanentDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_archiving_destination_preserves_transactions_and_related_records(): void
    {
        $customer = Customer::create([
            'nama' => 'Pelanggan Test',
            'email' => 'pelanggan@example.com',
            'no_hp' => '081234567890',
            'alamat' => 'Jember',
            'password' => bcrypt('password'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Pantai Test',
            'deskripsi' => 'Destinasi untuk pengujian',
            'kategori' => 'Bahari',
            'latitude' => -8.123,
            'longitude' => 113.456,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00 - 17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $admin = AdminPariwisata::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama' => 'Admin Pantai Test',
            'email' => 'admin.pantai.test@gmail.com',
            'no_hp' => '081234567891',
            'password' => bcrypt('password'),
            'status_akun' => 'AKTIF',
        ]);
        $facility = Fasilitas::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_fasilitas' => 'Toilet',
        ]);
        $gallery = GaleriDestinasi::create([
            'id_destinasi' => $destination->id_destinasi,
            'url_foto' => 'images/pantai-test.jpg',
            'keterangan' => 'Foto pantai',
        ]);
        $ticketType = JenisTiket::create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Tiket Dewasa',
            'harga' => 25000,
        ]);
        $booking = Pemesanan::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'ketua_jenis_tiket' => $ticketType->id_jenis_tiket,
            'kode_booking' => 'BOOKING-DESTINATION-DELETE',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'total_harga' => 25000,
            'status_pemesanan' => 'PAID',
        ]);
        $ticket = Tiket::create([
            'id_pemesanan' => $booking->id_pemesanan,
            'kode_qr' => 'TICKET-DESTINATION-DELETE',
            'status_tiket' => 'ACTIVE',
        ]);
        $detail = DetailPemesanan::create([
            'id_pemesanan' => $booking->id_pemesanan,
            'id_tiket' => $ticket->id_tiket,
            'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            'jumlah' => 1,
            'subtotal' => 25000,
        ]);
        $payment = Pembayaran::create([
            'id_pemesanan' => $booking->id_pemesanan,
            'nominal' => 25000,
            'status_pembayaran' => 'SUCCESS',
        ]);
        $change = PerubahanPemesanan::create([
            'id_pemesanan' => $booking->id_pemesanan,
            'anggota_baru' => ['Anggota Test'],
            'nominal' => 0,
            'status' => 'APPROVED',
        ]);
        $review = Review::create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'id_tiket' => $ticket->id_tiket,
            'rating' => 5,
            'ulasan' => 'Bagus',
            'tanggal_review' => now(),
        ]);

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->from(route('superadmin.destinations'))
            ->delete(route('superadmin.destinations.destroy', $destination->id_destinasi))
            ->assertRedirect(route('superadmin.destinations'))
            ->assertSessionHas('success', 'Destinasi berhasil diarsipkan. Semua transaksi dan data terkait tetap tersimpan.');

        $this->assertSoftDeleted('destinasi_wisata', ['id_destinasi' => $destination->id_destinasi]);
        $this->assertDatabaseHas('admin_pariwisata', [
            'id_admin' => $admin->id_admin,
            'status_akun' => 'NONAKTIF',
        ]);
        $this->assertDatabaseHas('fasilitas', ['id_fasilitas' => $facility->id_fasilitas]);
        $this->assertDatabaseHas('galeri_destinasi', ['id_galeri' => $gallery->id_galeri]);
        $this->assertDatabaseHas('jenis_tiket', ['id_jenis_tiket' => $ticketType->id_jenis_tiket]);
        $this->assertDatabaseHas('pemesanan', ['id_pemesanan' => $booking->id_pemesanan]);
        $this->assertDatabaseHas('detail_pemesanan', ['id_detail' => $detail->id_detail]);
        $this->assertDatabaseHas('tiket', ['id_tiket' => $ticket->id_tiket]);
        $this->assertDatabaseHas('pembayaran', ['id_pembayaran' => $payment->id_pembayaran]);
        $this->assertDatabaseHas('perubahan_pemesanan', ['id_perubahan' => $change->id_perubahan]);
        $this->assertDatabaseHas('review', ['id_review' => $review->id_review]);

        $this->assertNull(DestinasiWisata::query()->find($destination->id_destinasi));
        $this->assertNotNull(DestinasiWisata::withTrashed()->find($destination->id_destinasi));
        $this->assertSame('Pantai Test', $booking->fresh()->destinasi->nama_wisata);
        $this->assertSame('Pantai Test', $review->fresh()->destinasi->nama_wisata);

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->get(route('superadmin.destinations.archived'))
            ->assertOk()
            ->assertSee('Destinasi diarsipkan')
            ->assertSee('Pulihkan');

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->from(route('superadmin.destinations'))
            ->post(route('superadmin.destinations.restore', $destination->id_destinasi))
            ->assertRedirect(route('superadmin.destinations'))
            ->assertSessionHas('success', 'Destinasi berhasil dipulihkan dan diaktifkan kembali.');

        $this->assertDatabaseHas('destinasi_wisata', [
            'id_destinasi' => $destination->id_destinasi,
            'deleted_at' => null,
            'status_aktif' => 'aktif',
        ]);
        $this->assertDatabaseHas('admin_pariwisata', [
            'id_admin' => $admin->id_admin,
            'status_akun' => 'AKTIF',
        ]);
        $this->assertDatabaseHas('pembayaran', ['id_pembayaran' => $payment->id_pembayaran]);
    }
}
