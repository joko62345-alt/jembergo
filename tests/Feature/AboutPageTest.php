<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_explains_jembergo_booking_steps_and_ticket_policy(): void
    {
        $response = $this->get(route('about'));

        $response
            ->assertOk()
            ->assertSee('Apa itu')
            ->assertSee('JemberGo?')
            ->assertSee('platform informasi dan layanan pariwisata')
            ->assertSee('Wisata Alam')
            ->assertSee('Wisata Bahari')
            ->assertSee('Wisata Buatan')
            ->assertSee('Nikmati ruang terbuka dan suasana alam yang menyegarkan.')
            ->assertSee('Rasakan suasana pesisir dan ragam aktivitas tepi laut.')
            ->assertSee('Temukan pilihan rekreasi, edukasi, dan hiburan untuk semua.')
            ->assertDontSee('Air Terjun Tancak')
            ->assertDontSee('Kali Jompo')
            ->assertDontSee('Pantai Papuma')
            ->assertDontSee('Taman Botani')
            ->assertSee('Layanan JemberGo')
            ->assertSee('Informasi destinasi')
            ->assertSee('Artikel & tips wisata')
            ->assertSee('Tiket wisata')
            ->assertSee('akses e-ticket, ubah tanggal, atau tambahkan anggota perjalanan')
            ->assertSee('Pesan tiket dalam beberapa langkah')
            ->assertSee('Tiket tidak bisa dibatalkan atau refund')
            ->assertSee('Bisa reschedule dan tambah anggota')
            ->assertSee(route('about'), false);

        $this->assertSame(3, substr_count((string) $response->getContent(), 'class="about-service-card"'));
    }

    public function test_homepage_about_section_links_to_about_page(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Lihat selengkapnya')
            ->assertSee('Nikmati ruang terbuka dan suasana alam yang menyegarkan.')
            ->assertSee('Rasakan suasana pesisir dan ragam aktivitas tepi laut.')
            ->assertSee('Temukan pilihan rekreasi, edukasi, dan hiburan untuk semua.')
            ->assertDontSee('Air Terjun Tancak')
            ->assertDontSee('Kali Jompo')
            ->assertDontSee('Pantai Papuma')
            ->assertDontSee('Taman Botani')
            ->assertSee('href="'.route('about').'"', false);
    }
}
