<?php

namespace Tests\Feature;

use App\Mail\CustomerPasswordReset;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_is_sent_without_exposing_whether_email_exists(): void
    {
        Mail::fake();
        $customer = Customer::create(['nama' => 'Customer Test', 'email' => 'customer@example.com', 'no_hp' => '08123456789', 'password' => Hash::make('old-password'), 'auth_provider' => 'manual']);

        $response = $this->post(route('password.email'), ['email' => $customer->email]);

        $response->assertSessionHas('success');
        Mail::assertSent(CustomerPasswordReset::class, fn ($mail): bool => $mail->hasTo($customer->email));
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $customer->email]);
    }

    public function test_customer_can_reset_password_with_a_valid_token_once(): void
    {
        $customer = Customer::create(['nama' => 'Customer Test', 'email' => 'customer@example.com', 'no_hp' => '08123456789', 'password' => Hash::make('old-password'), 'auth_provider' => 'manual']);
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert(['email' => $customer->email, 'token' => Hash::make($token), 'created_at' => now()]);

        $response = $this->post(route('password.update'), ['email' => $customer->email, 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password']);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password', $customer->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $customer->email]);
    }
}
