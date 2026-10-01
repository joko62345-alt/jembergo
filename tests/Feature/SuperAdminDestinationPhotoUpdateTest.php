<?php

namespace Tests\Feature;

use App\Models\DestinasiWisata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminDestinationPhotoUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_a_destination_main_photo(): void
    {
        Storage::fake('public');
        $destination = DestinasiWisata::create([
            'nama_wisata' => 'Pantai Test',
            'foto_utama' => '/storage/destinations/main/old-photo.jpg',
            'deskripsi' => 'Destinasi untuk pengujian',
            'kategori' => 'Bahari',
            'latitude' => -8.123,
            'longitude' => 113.456,
            'alamat' => 'Jember',
            'jam_operasional' => '08:00 - 17:00',
            'status_aktif' => true,
            'kuota_harian_aktif' => false,
        ]);

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->put(route('superadmin.destinations.update', $destination->id_destinasi), [
                'nama_wisata' => 'Pantai Test',
                'foto_utama' => UploadedFile::fake()->image('new-photo.jpg'),
                'deskripsi' => 'Destinasi untuk pengujian',
                'kategori' => 'Bahari',
                'latitude' => -8.123,
                'longitude' => 113.456,
                'alamat' => 'Jember',
                'jam_operasional' => '08:00 - 17:00',
                'jam_buka' => '08:00',
                'jam_tutup' => '17:00',
                'status_aktif' => '1',
            ])
            ->assertRedirect();

        $destination->refresh();

        $this->assertStringStartsWith('/storage/destinations/main/', $destination->foto_utama);
        $this->assertTrue(Storage::disk('public')->exists(substr($destination->foto_utama, strlen('/storage/'))));
        $this->get($destination->foto_utama)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
    }
}
