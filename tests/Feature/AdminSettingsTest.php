<?php

namespace Tests\Feature;

use App\Models\AdminPariwisata;
use App\Models\DestinasiWisata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_settings_show_account_and_quota_controls_in_sidebar_page(): void
    {
        $destination = $this->createDestination();
        $admin = $this->createAdmin($destination);

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('nav-link active')
            ->assertSeeText('Profil petugas dan keamanan akun')
            ->assertSeeText($admin->nama)
            ->assertSeeText($admin->email)
            ->assertSeeText($destination->nama_wisata)
            ->assertSeeText($destination->alamat)
            ->assertSeeText('Keamanan akun')
            ->assertSeeText('Password saat ini')
            ->assertSee('aria-label="Tampilkan password saat ini"', false)
            ->assertSee('aria-label="Tampilkan password baru"', false)
            ->assertSee('aria-label="Tampilkan konfirmasi password baru"', false)
            ->assertSeeText('Kuota tiket harian')
            ->assertDontSeeText('Masukkan batas antara 1–100.000 tiket.')
            ->assertSee('inputmode="numeric" pattern="[1-9][0-9]{0,4}|100000" maxlength="6"', false)
            ->assertSeeInOrder([
                'class="admin-panel admin-settings-quota"',
                'class="admin-panel admin-settings-account-security"',
            ], false)
            ->assertSeeInOrder(['<span>Laporan</span>', '<span>Pengaturan</span>'], false);
    }

    public function test_admin_can_change_password_after_entering_the_current_password(): void
    {
        $destination = $this->createDestination();
        $admin = $this->createAdmin($destination);

        $this->withSession($this->adminSession($admin))
            ->from(route('admin.settings'))
            ->put(route('admin.settings.password'), [
                'current_password' => 'current-password',
                'new_password' => 'new-secure-password',
                'new_password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('password_success');

        $this->assertTrue(Hash::check('new-secure-password', $admin->fresh()->password));
    }

    public function test_admin_cannot_change_password_with_an_incorrect_current_password(): void
    {
        $destination = $this->createDestination();
        $admin = $this->createAdmin($destination);

        $this->withSession($this->adminSession($admin))
            ->from(route('admin.settings'))
            ->put(route('admin.settings.password'), [
                'current_password' => 'incorrect-password',
                'new_password' => 'new-secure-password',
                'new_password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_admin_can_update_quota_from_settings_and_dashboard_no_longer_contains_quota_form(): void
    {
        $destination = $this->createDestination();
        $admin = $this->createAdmin($destination);
        $session = $this->adminSession($admin);

        $this->withSession($session)
            ->from(route('admin.settings'))
            ->put(route('admin.quota.update'), [
                'kuota_harian_aktif' => '1',
                'kuota_harian' => 80,
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('success');

        $this->assertSame('aktif', $destination->fresh()->kuota_harian_aktif);
        $this->assertSame(80, $destination->fresh()->kuota_harian);

        $this->withSession($session)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Kuota tiket harian')
            ->assertDontSee('Maksimal peserta per hari')
            ->assertSeeText('Pengaturan');
    }

    public function test_active_quota_requires_a_positive_integer_limit(): void
    {
        $destination = $this->createDestination();
        $admin = $this->createAdmin($destination);

        foreach (['', '12abc', '-5'] as $quota) {
            $this->withSession($this->adminSession($admin))
                ->from(route('admin.settings'))
                ->put(route('admin.quota.update'), [
                    'kuota_harian_aktif' => '1',
                    'kuota_harian' => $quota,
                ])
                ->assertRedirect(route('admin.settings'))
                ->assertSessionHasErrors('kuota_harian');
        }
    }

    private function createDestination(): DestinasiWisata
    {
        return DestinasiWisata::create([
            'nama_wisata' => 'Destinasi Pengaturan',
            'deskripsi' => 'Destinasi untuk pengujian pengaturan',
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
            'nama' => 'Admin Pengaturan',
            'email' => 'admin.settings@gmail.com',
            'no_hp' => '081234567890',
            'password' => Hash::make('current-password'),
            'status_akun' => 'AKTIF',
        ]);
    }

    private function adminSession(AdminPariwisata $admin): array
    {
        return [
            'jg_role' => 'ADMIN_PARIWISATA',
            'jg_user_id' => $admin->id_admin,
            'jg_user_name' => $admin->nama,
        ];
    }
}
