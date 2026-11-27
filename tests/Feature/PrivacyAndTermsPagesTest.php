<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyAndTermsPagesTest extends TestCase
{
    public function test_privacy_policy_is_public_and_includes_privacy_contact(): void
    {
        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember')
            ->assertSee('kpcjoko@gmail.com');
    }

    public function test_terms_of_service_is_public_and_includes_service_conditions(): void
    {
        $this->get(route('terms-of-service'))
            ->assertOk()
            ->assertSee('Persyaratan Layanan')
            ->assertSee('Tiket yang telah dipesan tidak dapat dibatalkan')
            ->assertSee('kpcjoko@gmail.com');
    }
}
