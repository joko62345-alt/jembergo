<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeArticleListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_three_latest_articles_and_links_to_all_articles(): void
    {
        $superAdmin = SuperAdmin::query()->create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);

        foreach (range(1, 4) as $daysAgo) {
            Artikel::query()->create([
                'id_superadmin' => $superAdmin->id_superadmin,
                'judul' => 'Artikel '.$daysAgo,
                'isi' => 'Konten artikel '.$daysAgo,
                'tanggal_publikasi' => now()->subDays($daysAgo),
                'status' => 'PUBLISHED',
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Artikel 1')
            ->assertSee('Artikel 2')
            ->assertSee('Artikel 3')
            ->assertDontSee('Artikel 4')
            ->assertSee(route('articles.index'), false)
            ->assertSee('Lihat semua artikel');

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Artikel 4');
    }
}
