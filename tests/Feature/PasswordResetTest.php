<?php

namespace Tests\Feature;

use App\Mail\CustomerOtp;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_code_is_sent_without_exposing_whether_email_exists(): void
    {
        Mail::fake();
        $customer = Customer::create(['nama' => 'Customer Test', 'email' => 'customer@example.com', 'no_hp' => '08123456789', 'password' => Hash::make('old-password'), 'auth_provider' => 'manual']);

        $response = $this->post(route('password.email'), ['email' => $customer->email]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('password_reset_email', $customer->email);
        $response->assertRedirect(route('password.request', ['stage' => 'otp']));
        Mail::assertSent(
            CustomerOtp::class,
            fn ($mail): bool => $mail->hasTo($customer->email)
                && preg_match('/^\d{6}$/', $mail->otp) === 1
                && Hash::check($mail->otp, DB::table('password_reset_tokens')->where('email', $customer->email)->value('token')),
        );
    }

    public function test_customer_can_reset_password_after_verifying_otp(): void
    {
        Mail::fake();
        $customer = Customer::create(['nama' => 'Customer Test', 'email' => 'customer@example.com', 'no_hp' => '08123456789', 'password' => Hash::make('old-password'), 'auth_provider' => 'manual']);

        $this->post(route('password.email'), ['email' => $customer->email]);
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertDontSee('name="otp"', false);
        $this->get(route('password.request', ['stage' => 'otp']))
            ->assertOk()
            ->assertSee('name="otp"', false);
        $mail = Mail::sent(CustomerOtp::class)->first();
        $this->assertNotNull($mail);

        $this->post(route('password.otp.verify'), ['otp' => $mail->otp])
            ->assertRedirect(route('password.request', ['stage' => 'password']))
            ->assertSessionHas('password_reset_verified_email', $customer->email);
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertDontSee('id="password"', false);
        $this->get(route('password.request', ['stage' => 'password']))
            ->assertOk()
            ->assertSee('name="password"', false)
            ->assertSee('data-target="password"', false)
            ->assertSee('data-target="password_confirmation"', false)
            ->assertSee('Reset Password')
            ->assertDontSee('name="otp"', false);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $customer->email]);

        $this->post(route('password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $customer->fresh()->password));
        $this->assertFalse(session()->has('password_reset_verified_email'));

        $this->followingRedirects()
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Password berhasil diubah. Silakan masuk kembali.')
            ->assertDontSee('Halaman login');
    }

    public function test_customer_cannot_verify_an_expired_otp(): void
    {
        $customer = Customer::create(['nama' => 'Customer Test', 'email' => 'customer@example.com', 'no_hp' => '08123456789', 'password' => Hash::make('old-password'), 'auth_provider' => 'manual']);
        DB::table('password_reset_tokens')->insert([
            'email' => $customer->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->withSession(['password_reset_email' => $customer->email])
            ->post(route('password.otp.verify'), ['otp' => '123456'])
            ->assertRedirect(route('password.request', ['stage' => 'otp']))
            ->assertSessionHas('error');

        $this->withSession(['password_reset_email' => $customer->email])
            ->post(route('password.update'), [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error');

        $this->assertTrue(Hash::check('old-password', $customer->fresh()->password));
    }

    public function test_unknown_email_receives_the_same_reset_response_without_sending_email(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertSessionHas('success')
            ->assertSessionHas('password_reset_email', 'unknown@example.com');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_customer_is_told_when_reset_code_email_cannot_be_sent(): void
    {
        $customer = Customer::create([
            'nama' => 'Customer Test',
            'email' => 'customer@example.com',
            'no_hp' => '08123456789',
            'password' => Hash::make('old-password'),
            'auth_provider' => 'google',
        ]);
        Mail::shouldReceive('to')
            ->once()
            ->with($customer->email)
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $this->post(route('password.email'), ['email' => $customer->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error', 'Kode OTP gagal dikirim. Silakan coba lagi beberapa saat.')
            ->assertSessionMissing('password_reset_email');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $customer->email]);
    }

    public function test_registration_success_alert_keeps_its_login_link(): void
    {
        $this->withSession(['success' => 'Registrasi berhasil. Silakan ke halaman login.'])
            ->get(route('register'))
            ->assertOk()
            ->assertSee('Halaman login')
            ->assertSee(route('login'));
    }
}
