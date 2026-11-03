<?php

namespace Tests\Feature;

use App\Mail\CustomerOtp;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_customer_only_after_email_otp_is_verified(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'nama' => 'Customer Test',
            'email' => 'customer@example.com',
            'no_hp' => '08123456789',
            'password' => 'password123',
        ])
            ->assertRedirect(route('register', ['stage' => 'otp']))
            ->assertSessionHas('customer_verification_intent', 'registration')
            ->assertSessionHas('customer_verification_email', 'customer@example.com');

        $this->assertDatabaseMissing('customer', ['email' => 'customer@example.com']);
        $this->get(route('register', ['stage' => 'otp']))
            ->assertOk()
            ->assertSee('name="otp"', false)
            ->assertSee('customer@example.com')
            ->assertDontSee('name="no_hp"', false);

        $mail = Mail::sent(CustomerOtp::class)->first();
        $this->assertNotNull($mail);
        $this->assertTrue($mail->isEmailVerification);
        $this->assertSame(
            'Verifikasi email JemberGo',
            $mail->envelope()->subject,
        );
        $this->assertTrue(Hash::check(
            $mail->otp,
            DB::table('customer_email_verification_tokens')
                ->where('email', 'customer@example.com')
                ->value('token'),
        ));

        $this->post(route('register.otp.verify'), ['otp' => $mail->otp])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Email berhasil diverifikasi. Silakan masuk.');

        $customer = Customer::query()->where('email', 'customer@example.com')->firstOrFail();
        $this->assertSame('Customer Test', $customer->nama);
        $this->assertSame('08123456789', $customer->no_hp);
        $this->assertSame('manual', $customer->auth_provider);
        $this->assertTrue(Hash::check('password123', $customer->password));
        $this->assertDatabaseMissing('customer_email_verification_tokens', ['email' => $customer->email]);
    }

    public function test_registration_does_not_create_customer_for_invalid_or_expired_otp(): void
    {
        $this->withSession([
            'customer_verification_email' => 'customer@example.com',
            'customer_verification_intent' => 'registration',
            'customer_verification_data' => [
                'nama' => 'Customer Test',
                'no_hp' => '08123456789',
                'password' => Hash::make('password123'),
            ],
        ]);
        DB::table('customer_email_verification_tokens')->insert([
            'email' => 'customer@example.com',
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->post(route('register.otp.verify'), ['otp' => '123456'])
            ->assertRedirect(route('register', ['stage' => 'otp']))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('customer', ['email' => 'customer@example.com']);
    }

    public function test_first_google_login_requires_otp_then_logs_customer_in(): void
    {
        Mail::fake();
        $this->fakeGoogleAccount('google-customer@example.com', 'Google Customer');

        $this->withSession(['google_oauth_state' => 'google-state'])
            ->get(route('google.callback', ['state' => 'google-state', 'code' => 'google-code']))
            ->assertRedirect(route('register', ['stage' => 'otp']))
            ->assertSessionHas('customer_verification_intent', 'google');

        $this->assertDatabaseMissing('customer', ['email' => 'google-customer@example.com']);
        $mail = Mail::sent(CustomerOtp::class)->first();
        $this->assertNotNull($mail);
        $this->assertTrue($mail->isEmailVerification);

        $this->post(route('register.otp.verify'), ['otp' => $mail->otp])
            ->assertRedirect(route('home'))
            ->assertSessionHas('jg_role', 'CUSTOMER');

        $customer = Customer::query()->where('email', 'google-customer@example.com')->firstOrFail();
        $this->assertSame('google', $customer->auth_provider);
        $this->assertNotNull($customer->google_verified_at);
        $this->assertDatabaseMissing('customer_email_verification_tokens', ['email' => $customer->email]);
    }

    public function test_first_google_login_for_existing_customer_requires_otp_and_preserves_account_data(): void
    {
        Mail::fake();
        $customer = Customer::query()->create([
            'nama' => 'Nama Akun Manual',
            'email' => 'manual@example.com',
            'no_hp' => '08123456789',
            'password' => Hash::make('password123'),
            'auth_provider' => 'manual',
        ]);
        $this->fakeGoogleAccount('manual@example.com', 'Nama Google');

        $this->withSession(['google_oauth_state' => 'google-state'])
            ->get(route('google.callback', ['state' => 'google-state', 'code' => 'google-code']))
            ->assertRedirect(route('register', ['stage' => 'otp']));

        $this->assertSame('manual', $customer->fresh()->auth_provider);
        $mail = Mail::sent(CustomerOtp::class)->first();
        $this->assertNotNull($mail);

        $this->post(route('register.otp.verify'), ['otp' => $mail->otp])
            ->assertRedirect(route('home'))
            ->assertSessionHas('jg_user_id', $customer->id_customer);

        $customer->refresh();
        $this->assertSame('Nama Akun Manual', $customer->nama);
        $this->assertSame('08123456789', $customer->no_hp);
        $this->assertTrue(Hash::check('password123', $customer->password));
        $this->assertSame('google', $customer->auth_provider);
        $this->assertNotNull($customer->google_verified_at);
    }

    public function test_google_account_that_has_already_completed_otp_does_not_receive_another_code(): void
    {
        Mail::fake();
        $customer = Customer::query()->create([
            'nama' => 'Google Customer',
            'email' => 'google-customer@example.com',
            'password' => null,
            'auth_provider' => 'google',
            'google_verified_at' => now(),
        ]);
        $this->fakeGoogleAccount('google-customer@example.com', 'Google Customer');

        $this->withSession(['google_oauth_state' => 'google-state'])
            ->get(route('google.callback', ['state' => 'google-state', 'code' => 'google-code']))
            ->assertRedirect(route('home'))
            ->assertSessionHas('jg_user_id', $customer->id_customer);

        Mail::assertNothingSent();
    }

    private function fakeGoogleAccount(string $email, string $name): void
    {
        config([
            'services.google.client_id' => 'test.apps.googleusercontent.com',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => route('google.callback'),
        ]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => $email,
                'email_verified' => true,
                'name' => $name,
            ]),
        ]);
    }
}
