<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_shows_email_error_when_email_is_not_found(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'unknown@example.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Email tidak sesuai.');
    }

    public function test_login_shows_password_error_when_email_exists_but_password_is_wrong(): void
    {
        Customer::query()->create([
            'nama' => 'Customer Test',
            'email' => 'customer@example.com',
            'no_hp' => '08123456789',
            'password' => Hash::make('correct-password'),
            'auth_provider' => 'manual',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'customer@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Password salah.');
    }
}
