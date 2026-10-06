<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleDuplicateValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_with_duplicate_title_cannot_be_created(): void
    {
        [$superAdmin] = $this->createSuperAdminAndArticle();

        $this->withSession([
            'jg_role' => 'SUPER_ADMIN',
            'jg_user_id' => $superAdmin->id_superadmin,
        ])->from(route('superadmin.articles.create'))
            ->post(route('superadmin.articles.store'), [
                'judul' => 'Judul Artikel',
                'isi' => 'Isi artikel baru yang berbeda.',
                'status' => 'DRAFT',
            ])
            ->assertRedirect(route('superadmin.articles.create'))
            ->assertSessionHasErrors('judul');
    }

    public function test_article_with_duplicate_content_cannot_be_created(): void
    {
        [$superAdmin] = $this->createSuperAdminAndArticle();

        $this->withSession([
            'jg_role' => 'SUPER_ADMIN',
            'jg_user_id' => $superAdmin->id_superadmin,
        ])->from(route('superadmin.articles.create'))
            ->post(route('superadmin.articles.store'), [
                'judul' => 'Judul Artikel Baru',
                'isi' => 'Isi artikel yang sudah ada.',
                'status' => 'DRAFT',
            ])
            ->assertRedirect(route('superadmin.articles.create'))
            ->assertSessionHasErrors('isi');
    }

    public function test_article_can_be_updated_without_changing_its_own_title_or_content(): void
    {
        [$superAdmin, $article] = $this->createSuperAdminAndArticle();

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->put(route('superadmin.articles.update', $article->id_artikel), [
                'judul' => $article->judul,
                'isi' => $article->isi,
                'status' => $article->status,
            ])
            ->assertRedirect(route('superadmin.articles'))
            ->assertSessionHasNoErrors();
    }

    public function test_article_cannot_be_updated_with_another_articles_title_or_content(): void
    {
        [$superAdmin, $article] = $this->createSuperAdminAndArticle();
        $duplicateArticle = $this->createArticle($superAdmin, 'Judul lain', 'Isi artikel lain.');

        $this->withSession(['jg_role' => 'SUPER_ADMIN'])
            ->from(route('superadmin.articles.edit', $article->id_artikel))
            ->put(route('superadmin.articles.update', $article->id_artikel), [
                'judul' => $duplicateArticle->judul,
                'isi' => $duplicateArticle->isi,
                'status' => $article->status,
            ])
            ->assertRedirect(route('superadmin.articles.edit', $article->id_artikel))
            ->assertSessionHasErrors(['judul', 'isi']);
    }

    /**
     * @return array{0: SuperAdmin, 1: Artikel}
     */
    private function createSuperAdminAndArticle(): array
    {
        $superAdmin = SuperAdmin::create([
            'nama' => 'Super Admin Test',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);

        return [$superAdmin, $this->createArticle($superAdmin, 'Judul Artikel', 'Isi artikel yang sudah ada.')];
    }

    private function createArticle(SuperAdmin $superAdmin, string $title, string $content): Artikel
    {
        return Artikel::create([
            'id_superadmin' => $superAdmin->id_superadmin,
            'judul' => $title,
            'isi' => $content,
            'status' => 'DRAFT',
        ]);
    }
}
