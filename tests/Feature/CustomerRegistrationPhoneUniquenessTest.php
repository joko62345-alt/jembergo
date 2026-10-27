<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRegistrationPhoneUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_registration_rejects_a_phone_number_already_in_use(): void
    {
        Customer::query()->create([
            'nama' => 'Akun Pertama',
            'email' => 'pertama@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('password123'),
            'auth_provider' => 'manual',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'nama' => 'Akun Kedua',
                'email' => 'kedua@example.com',
                'no_hp' => '081234567890',
                'password' => 'password123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'no_hp' => 'Nomor telepon tersebut sudah terdaftar.',
            ]);

        $this->assertDatabaseCount('customer', 1);
    }

    public function test_mobile_registration_rejects_a_phone_number_already_in_use(): void
    {
        Customer::query()->create([
            'nama' => 'Akun Pertama',
            'email' => 'pertama@example.com',
            'no_hp' => '081234567890',
            'password' => bcrypt('password123'),
            'auth_provider' => 'manual',
        ]);

        $this->postJson('/api/v1/auth/register', [
            'nama' => 'Akun Kedua',
            'email' => 'kedua@example.com',
            'no_hp' => '081234567890',
            'password' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['no_hp']);

        $this->assertDatabaseCount('customer', 1);
    }
}
