<?php

namespace Database\Seeders;

use App\Models\AdminPariwisata;
use App\Models\Artikel;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\Fasilitas;
use App\Models\GaleriDestinasi;
use App\Models\JenisTiket;
use App\Models\SuperAdmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = SuperAdmin::create([
            'nama' => 'Super Admin JemberGo',
            'email' => 'superadmin@jembergo.test',
            'password' => Hash::make('JemberGo!2026'),
        ]);

        $destinations = [
            ['nama_wisata' => 'Air Terjun Tancak', 'kategori' => 'Alam', 'alamat' => 'Desa Suci, Panti, Jember', 'latitude' => -8.0367000, 'longitude' => 113.6075000, 'jam_operasional' => '07:00 - 16:00'],
            ['nama_wisata' => 'Kali Jompo', 'kategori' => 'Alam', 'alamat' => 'Kecamatan Semboro, Jember', 'latitude' => -8.1726000, 'longitude' => 113.4874000, 'jam_operasional' => '07:00 - 17:00'],
            ['nama_wisata' => 'Pantai Papuma', 'kategori' => 'Bahari', 'alamat' => 'Desa Lojejer, Wuluhan, Jember', 'latitude' => -8.3693000, 'longitude' => 113.5557000, 'jam_operasional' => '06:00 - 17:00'],
            ['nama_wisata' => 'Teluk Love', 'kategori' => 'Bahari', 'alamat' => 'Ambulu, Jember', 'latitude' => -8.3829000, 'longitude' => 113.5659000, 'jam_operasional' => '06:00 - 17:00'],
            ['nama_wisata' => 'Taman Botani', 'kategori' => 'Buatan', 'alamat' => 'Jl. Mujahir, Sukorambi, Jember', 'latitude' => -8.1265000, 'longitude' => 113.6469000, 'jam_operasional' => '08:00 - 17:00'],
        ];

        foreach ($destinations as $destinationData) {
            $destination = DestinasiWisata::create([
                ...$destinationData,
                'id_superadmin' => $superAdmin->id_superadmin,
                'deskripsi' => 'Destinasi pilihan Kabupaten Jember dengan pengalaman wisata yang berkesan.',
                'status_aktif' => true,
            ]);

            foreach (['Parkir', 'Toilet', 'Mushola'] as $facilityName) {
                Fasilitas::create(['id_destinasi' => $destination->id_destinasi, 'nama_fasilitas' => $facilityName]);
            }

            GaleriDestinasi::create([
                'id_destinasi' => $destination->id_destinasi,
                'url_foto' => 'images/destinations/' . str($destination->nama_wisata)->slug() . '.jpg',
                'keterangan' => 'Pemandangan utama ' . $destination->nama_wisata,
            ]);

            JenisTiket::create(['id_destinasi' => $destination->id_destinasi, 'nama_jenis' => 'Tiket Dewasa', 'harga' => 25000]);
            JenisTiket::create(['id_destinasi' => $destination->id_destinasi, 'nama_jenis' => 'Tiket Anak', 'harga' => 15000]);
        }

        foreach ([
            ['nama' => 'Admin Papuma', 'email' => 'admin.papuma@jembergo.test', 'no_hp' => '081234567890', 'id_destinasi' => 3],
            ['nama' => 'Admin Tancak', 'email' => 'admin.tancak@jembergo.test', 'no_hp' => '081234567891', 'id_destinasi' => 1],
        ] as $adminData) {
            AdminPariwisata::create([...$adminData, 'password' => Hash::make('JemberGo!2026'), 'status_akun' => 'AKTIF']);
        }

        foreach (range(1, 5) as $index) {
            Customer::create([
                'nama' => 'Customer JemberGo ' . $index,
                'email' => 'customer' . $index . '@jembergo.test',
                'no_hp' => '08200000000' . $index,
                'alamat' => 'Kabupaten Jember',
                'password' => Hash::make('JemberGo!2026'),
                'auth_provider' => 'manual',
            ]);
        }

        Artikel::create([
            'id_superadmin' => $superAdmin->id_superadmin,
            'judul' => 'Menjelajah Pesona Wisata Jember',
            'isi' => 'Jember menyimpan banyak destinasi alam dan bahari yang siap untuk dijelajahi.',
            'tanggal_publikasi' => now(),
            'status' => 'PUBLISHED',
        ]);

    }
}
