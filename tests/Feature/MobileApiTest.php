<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\Artikel;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\Fasilitas;
use App\Models\GaleriDestinasi;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use App\Models\SuperAdmin;
use App\Services\PaymentGateway\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_use_a_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'nama' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'device_name' => 'android',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.role', 'CUSTOMER')
            ->assertJsonPath('data.user.email', 'siti@example.com')
            ->assertJsonPath('data.user.no_hp', '081234567890')
            ->assertJsonPath('data.token_type', 'Bearer');
        $this->assertNotNull($response->json('data.expires_at'));

        $token = $response->json('data.token');
        $this->withToken($token)
            ->getJson('/api/v1/customer/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'CUSTOMER');

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_customer_can_log_in_and_revoke_only_the_current_token(): void
    {
        Customer::query()->create([
            'nama' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('password123'),
            'auth_provider' => 'manual',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'dewi@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonPath('data.user.role', 'CUSTOMER');
        $token = $response->json('data.token');

        $this->withToken($token)->deleteJson('/api/v1/customer/auth/token')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/customer/me')->assertUnauthorized();
    }

    public function test_customer_can_sign_in_with_a_verified_google_id_token(): void
    {
        config(['services.google.client_id' => 'jembergo-web-client-id']);
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'jembergo-web-client-id',
                'iss' => 'https://accounts.google.com',
                'exp' => (string) now()->addHour()->timestamp,
                'email_verified' => 'true',
                'email' => 'siti@example.com',
                'name' => 'Siti Aminah',
            ]),
        ]);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid-google-id-token',
            'device_name' => 'android',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.role', 'CUSTOMER')
            ->assertJsonPath('data.user.email', 'siti@example.com')
            ->assertJsonPath('data.user.nama', 'Siti Aminah')
            ->assertJsonPath('data.token_type', 'Bearer');
        $this->assertDatabaseHas('customer', [
            'email' => 'siti@example.com',
            'auth_provider' => 'google',
        ]);
    }

    public function test_google_sign_in_rejects_tokens_for_another_client(): void
    {
        config(['services.google.client_id' => 'jembergo-web-client-id']);
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'another-client-id',
                'iss' => 'https://accounts.google.com',
                'exp' => (string) now()->addHour()->timestamp,
                'email_verified' => 'true',
                'email' => 'attacker@example.com',
            ]),
        ]);

        $this->postJson('/api/v1/auth/google', [
            'id_token' => 'wrong-audience-id-token',
        ])->assertUnauthorized();

        $this->assertDatabaseMissing('customer', [
            'email' => 'attacker@example.com',
        ]);
    }

    public function test_google_sign_in_rejects_unverified_or_expired_tokens(): void
    {
        config(['services.google.client_id' => 'jembergo-web-client-id']);
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'jembergo-web-client-id',
                'iss' => 'https://accounts.google.com',
                'exp' => (string) now()->subMinute()->timestamp,
                'email_verified' => 'false',
                'email' => 'unverified@example.com',
            ]),
        ]);

        $this->postJson('/api/v1/auth/google', [
            'id_token' => 'expired-google-id-token',
        ])->assertUnauthorized();

        $this->assertDatabaseMissing('customer', [
            'email' => 'unverified@example.com',
        ]);
    }

    public function test_google_sign_in_config_exposes_only_the_public_server_client_id(): void
    {
        config([
            'services.google.client_id' => 'jembergo-web-client-id',
            'services.google.client_secret' => 'never-return-this-secret',
        ]);

        $this->getJson('/api/v1/auth/google/config')
            ->assertOk()
            ->assertExactJson([
                'data' => ['server_client_id' => 'jembergo-web-client-id'],
            ])
            ->assertDontSee('never-return-this-secret');
    }

    public function test_mobile_api_requires_authentication_for_customer_data(): void
    {
        $this->getJson('/api/v1/customer/profil')->assertUnauthorized();
        $this->getJson('/api/v1/admin/dashboard')->assertUnauthorized();
    }

    public function test_public_destinations_are_available_as_json(): void
    {
        $destination = DestinasiWisata::query()->create([
            'nama_wisata' => 'Pantai Papuma',
            'foto_utama' => '/storage/destinations/main/papuma.jpg',
            'deskripsi' => 'Wisata pantai',
            'kategori' => 'Bahari',
            'latitude' => -8.431,
            'longitude' => 113.548,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        Fasilitas::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_fasilitas' => 'Area Parkir',
        ]);
        GaleriDestinasi::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'url_foto' => '/storage/destinations/papuma-gallery.jpg',
            'keterangan' => 'Pantai Papuma',
        ]);
        JenisTiket::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 15000,
        ]);

        $this->getJson('/api/v1/destinasi')
            ->assertOk()
            ->assertJsonPath('data.0.nama', 'Pantai Papuma')
            ->assertJsonPath('data.0.foto_utama', 'http://localhost:8000/api/v1/media/destinations/main/papuma.jpg')
            ->assertJsonPath('data.0.kategori', 'Bahari')
            ->assertJsonPath('data.0.latitude', -8.431)
            ->assertJsonPath('data.0.longitude', 113.548)
            ->assertJsonPath('data.0.jam_operasional', '08:00-17:00')
            ->assertJsonPath('data.0.ulasan_count', 0);

        $this->getJson('/api/v1/destinasi/'.$destination->id_destinasi)
            ->assertOk()
            ->assertJsonPath('data.alamat', 'Jember')
            ->assertJsonPath('data.fasilitas.0.nama', 'Area Parkir')
            ->assertJsonPath('data.galeri.0.url', 'http://localhost:8000/api/v1/media/destinations/papuma-gallery.jpg')
            ->assertJsonPath('data.jenis_tiket.0.nama', 'Dewasa')
            ->assertJsonPath('data.jenis_tiket.0.harga', 15000);
    }

    public function test_destination_images_are_served_through_the_cors_enabled_api(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('destinations/main/papuma.jpg', 'test-image-content');
        Storage::disk('public')->put('articles/festival.jpg', 'test-article-image');

        $response = $this->get('/api/v1/media/destinations/main/papuma.jpg', [
            'Origin' => 'http://localhost:53000',
        ])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->assertSame('test-image-content', $response->streamedContent());

        $this->get('/api/v1/media/articles/festival.jpg', [
            'Origin' => 'http://localhost:53000',
        ])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->get('/api/v1/media/../.env')->assertNotFound();
    }

    public function test_published_articles_are_available_to_mobile_with_public_image_urls(): void
    {
        $superAdmin = SuperAdmin::query()->create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        Artikel::query()->create([
            'id_superadmin' => $superAdmin->id_superadmin,
            'judul' => 'Festival Wisata Jember',
            'isi' => 'Berita terbaru dari website.',
            'gambar' => '/storage/articles/festival.jpg',
            'tanggal_publikasi' => now(),
            'status' => 'PUBLISHED',
        ]);
        Artikel::query()->create([
            'id_superadmin' => $superAdmin->id_superadmin,
            'judul' => 'Artikel draft',
            'isi' => 'Belum terbit.',
            'status' => 'DRAFT',
        ]);

        $this->getJson('/api/v1/artikel?per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'Festival Wisata Jember')
            ->assertJsonPath('data.0.isi', 'Berita terbaru dari website.')
            ->assertJsonPath('data.0.gambar', 'http://localhost:8000/api/v1/media/articles/festival.jpg');
    }

    public function test_access_tokens_expire_after_the_configured_period(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'nama' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'no_hp' => '081234567890',
            'password' => 'password123',
        ])->assertCreated();
        $token = $response->json('data.token');

        $this->travel(31)->days();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/customer/me')->assertUnauthorized();
        $this->travelBack();
    }

    public function test_customer_can_download_the_same_e_ticket_pdf_as_the_website(): void
    {
        $customer = Customer::query()->create([
            'nama' => 'Citra Wijaya',
            'email' => 'citra@gmail.com',
            'no_hp' => '081234567892',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $otherCustomer = Customer::query()->create([
            'nama' => 'Siti Aminah',
            'email' => 'siti@gmail.com',
            'no_hp' => '081234567893',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::query()->create([
            'nama_wisata' => 'Pantai Papuma',
            'deskripsi' => 'Wisata pantai',
            'kategori' => 'Bahari',
            'latitude' => -8.431,
            'longitude' => 113.548,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $ticketType = JenisTiket::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);
        $booking = Pemesanan::query()->create([
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'kode_booking' => 'JGO-20261016-PDF01',
            'tanggal_pemesanan' => now(),
            'tanggal_kunjungan' => now()->addDays(2),
            'total_harga' => 25000,
            'status_pemesanan' => 'PAID',
            'ketua_nama' => 'Citra Wijaya',
            'ketua_email' => 'citra@gmail.com',
            'ketua_no_hp' => '081234567892',
            'ketua_jenis_tiket' => $ticketType->id_jenis_tiket,
            'anggota_names' => [],
        ]);
        $token = $customer->createToken('mobile', ['CUSTOMER'])->plainTextToken;
        $otherToken = $otherCustomer->createToken('mobile', ['CUSTOMER'])->plainTextToken;
        $path = '/api/v1/customer/pesanan/'.$booking->id_pemesanan.'/e-ticket/pdf';

        $this->get($path)->assertUnauthorized();

        $response = $this->withToken($token)->get($path);
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('e-ticket-'.$booking->kode_booking.'.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $this->app['auth']->forgetGuards();
        $this->withToken($otherToken)->get($path)->assertNotFound();
    }

    public function test_customer_can_create_a_booking_through_the_api(): void
    {
        $customer = Customer::query()->create([
            'nama' => 'Citra Wijaya',
            'email' => 'citra@gmail.com',
            'no_hp' => '081234567892',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::query()->create([
            'nama_wisata' => 'Air Terjun Tancak',
            'foto_utama' => '/storage/destinations/main/tancak.jpg',
            'deskripsi' => 'Wisata alam',
            'kategori' => 'Alam',
            'latitude' => -8.036,
            'longitude' => 113.607,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $ticketType = JenisTiket::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);
        $token = $customer->createToken('test', ['CUSTOMER'])->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/customer/destinasi/'.$destination->id_destinasi.'/pemesanan', [
            'ketua_nama' => 'Citra Sari',
            'ketua_email' => 'citra.sari@gmail.com',
            'ketua_no_hp' => '+6281234567893',
            'tanggal_kunjungan' => now()->addDay()->toDateString(),
            'peserta' => [[
                'nama' => 'Citra Sari',
                'id_jenis_tiket' => $ticketType->id_jenis_tiket,
            ]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.destinasi.foto_utama', 'http://localhost:8000/api/v1/media/destinations/main/tancak.jpg')
            ->assertJsonPath('data.ketua.nama', 'Citra Sari')
            ->assertJsonPath('data.anggota', []);
        $this->assertDatabaseHas('pemesanan', [
            'id_customer' => $customer->id_customer,
            'id_destinasi' => $destination->id_destinasi,
            'status_pemesanan' => 'PENDING',
        ]);
    }

    public function test_customer_can_create_and_reuse_the_same_qris_payment_as_the_website(): void
    {
        $customer = Customer::query()->create([
            'nama' => 'Citra Wijaya',
            'email' => 'citra@gmail.com',
            'no_hp' => '081234567892',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $destination = DestinasiWisata::query()->create([
            'nama_wisata' => 'Air Terjun Tancak',
            'deskripsi' => 'Wisata alam',
            'kategori' => 'Alam',
            'latitude' => -8.036,
            'longitude' => 113.607,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $ticketType = JenisTiket::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama_jenis' => 'Dewasa',
            'harga' => 25000,
        ]);
        $token = $customer->createToken('test', ['CUSTOMER'])->plainTextToken;
        $booking = $this->withToken($token)->postJson(
            '/api/v1/customer/destinasi/'.$destination->id_destinasi.'/pemesanan',
            [
                'ketua_nama' => 'Citra Sari',
                'ketua_email' => 'citra.sari@gmail.com',
                'ketua_no_hp' => '+6281234567893',
                'tanggal_kunjungan' => now()->addDay()->toDateString(),
                'peserta' => [[
                    'nama' => 'Citra Sari',
                    'id_jenis_tiket' => $ticketType->id_jenis_tiket,
                ]],
            ],
        )->assertCreated();
        $bookingId = $booking->json('data.id');

        $this->app->instance(PaymentGateway::class, new class implements PaymentGateway
        {
            public function createPayment(Pemesanan $booking, string $method, ?float $amount = null): array
            {
                return [
                    'status' => 'PENDING',
                    'order_id' => 'JGO-'.$booking->kode_booking,
                    'transaction_id' => 'midtrans-transaction-id',
                    'transaction_status' => 'pending',
                    'payment_type' => 'qris',
                    'gross_amount' => $amount ?? (float) $booking->total_harga,
                    'reference' => 'midtrans-transaction-id',
                    'method' => 'QRIS',
                    'qris_url' => 'https://api.sandbox.midtrans.com/v2/qris/qr-code',
                    'qris_expires_at' => now()->addHours(2)->toIso8601String(),
                ];
            }

            public function createPaymentForAmount(Pemesanan $booking, string $method, float $amount): array
            {
                return $this->createPayment($booking, $method, $amount);
            }
        });

        $this->withToken($token)
            ->postJson('/api/v1/customer/pesanan/'.$bookingId.'/pembayaran')
            ->assertCreated()
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.order_id', 'JGO-'.$booking->json('data.kode_booking'))
            ->assertJsonPath('data.snap_token', null)
            ->assertJsonPath('data.checkout_url', null)
            ->assertJsonPath('data.qris_url', 'https://api.sandbox.midtrans.com/v2/qris/qr-code')
            ->assertJsonPath('data.qris_image_url', url('/api/v1/customer/pesanan/'.$bookingId.'/pembayaran/qr-image'));

        $this->assertDatabaseHas('pembayaran', [
            'id_pemesanan' => $bookingId,
            'snap_token' => null,
            'qris_url' => 'https://api.sandbox.midtrans.com/v2/qris/qr-code',
            'status_pembayaran' => 'PENDING',
        ]);

        Http::fake([
            'api.sandbox.midtrans.com/v2/qris/qr-code' => Http::response('test-png', 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);
        $this->withToken($token)
            ->get('/api/v1/customer/pesanan/'.$bookingId.'/pembayaran/qr-image')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertContent('test-png');

        $this->withToken($token)
            ->postJson('/api/v1/customer/pesanan/'.$bookingId.'/pembayaran')
            ->assertOk()
            ->assertJsonPath('data.qris_url', 'https://api.sandbox.midtrans.com/v2/qris/qr-code');
    }

    public function test_tourism_admin_can_access_only_the_admin_api(): void
    {
        $destination = DestinasiWisata::query()->create([
            'nama_wisata' => 'Pantai Papuma',
            'deskripsi' => 'Wisata pantai',
            'kategori' => 'Bahari',
            'latitude' => -8.431,
            'longitude' => 113.548,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00-17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);
        $admin = AdminPariwisata::query()->create([
            'id_destinasi' => $destination->id_destinasi,
            'nama' => 'Admin Destinasi',
            'email' => 'admin@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('password123'),
            'status_akun' => 'AKTIF',
        ]);
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('data.user.role', 'ADMIN_PARIWISATA');
        $token = $login->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.destinasi.nama', 'Pantai Papuma');

        $this->withToken($token)
            ->putJson('/api/v1/admin/kuota', ['aktif' => true, 'kuota_harian' => 40])
            ->assertOk()
            ->assertJsonPath('data.kuota_harian', 40);

        $this->withToken($token)->getJson('/api/v1/customer/me')->assertForbidden();
    }
}
