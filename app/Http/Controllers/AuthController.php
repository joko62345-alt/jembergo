<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\SuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        if ($request->query('redirect') && str_starts_with($request->query('redirect'), url('/customer/'))) {
            $request->session()->put('customer_redirect', $request->query('redirect'));
        }

        return view('auth.login');
    }

    public function showRegistration(): View
    {
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $accounts = [
            ['table' => 'super_admin', 'model' => SuperAdmin::class, 'role' => 'SUPER_ADMIN', 'id' => 'id_superadmin', 'name' => 'nama'],
            ['table' => 'admin_pariwisata', 'model' => AdminPariwisata::class, 'role' => 'ADMIN_PARIWISATA', 'id' => 'id_admin', 'name' => 'nama'],
            ['table' => 'customer', 'model' => Customer::class, 'role' => 'CUSTOMER', 'id' => 'id_customer', 'name' => 'nama'],
        ];

        foreach ($accounts as $account) {
            if (! Schema::hasTable($account['table'])) {
                continue;
            }

            $user = $account['model']::query()->where('email', $credentials['email'])->first();

            if ($user && Hash::check($credentials['password'], $user->password) && ($account['role'] !== 'ADMIN_PARIWISATA' || $user->status_akun === 'AKTIF')) {
                $request->session()->regenerate();
                $request->session()->put([
                    'jg_user_id' => $user->{$account['id']},
                    'jg_user_name' => $user->{$account['name']},
                    'jg_role' => $account['role'],
                ]);

                if ($account['role'] === 'CUSTOMER') {
                    return redirect()->to($request->session()->pull('customer_redirect', route('home')));
                }

                return redirect()->route('dashboard');
            }
        }

        return back()->withInput($request->only('email'))->with('error', 'Email atau password tidak sesuai.');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if (! $this->googleIsConfigured()) {
            return redirect()->route('login')->with('error', 'Login Google belum dikonfigurasi oleh administrator.');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);
        $redirectUri = $this->googleRedirectUri();
        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $state = $request->session()->pull('google_oauth_state');
        if (! $state || ! hash_equals($state, (string) $request->query('state'))) {
            return redirect()->route('login')->with('error', 'Sesi login Google sudah kedaluwarsa. Silakan coba lagi.');
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->with('error', 'Login dengan Google dibatalkan.');
        }

        try {
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $request->string('code')->toString(),
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->googleRedirectUri(),
                'grant_type' => 'authorization_code',
            ])->throw()->json();
            $profile = Http::withToken($tokenResponse['access_token'] ?? '')->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', 'Login dengan Google gagal. Silakan coba lagi.');
        }

        if (empty($profile['email']) || ($profile['email_verified'] ?? false) !== true) {
            return redirect()->route('login')->with('error', 'Email Google belum terverifikasi.');
        }

        $customer = Customer::query()->firstOrCreate(
            ['email' => $profile['email']],
            ['nama' => $profile['name'] ?? $profile['email'], 'password' => null, 'auth_provider' => 'google']
        );
        $customer->update(['auth_provider' => 'google']);
        $request->session()->regenerate();
        $request->session()->put(['jg_user_id' => $customer->id_customer, 'jg_user_name' => $customer->nama, 'jg_role' => 'CUSTOMER']);

        return redirect()->to($request->session()->pull('customer_redirect', route('home')));
    }

    public function register(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('customer') || ! Schema::hasTable('sessions')) {
            return back()->withInput($request->except('password', 'password_confirmation'))->with('error', 'Database JemberGo belum siap. Jalankan php artisan migrate --seed terlebih dahulu.');
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:customer,email'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $customer = Customer::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'auth_provider' => 'manual',
        ]);

        return redirect()->route('register')->with('success', 'Registrasi berhasil. Silakan ke halaman login.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['jg_user_id', 'jg_user_name', 'jg_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function googleRedirectUri(): string
    {
        return str_starts_with((string) config('services.google.redirect'), 'http')
            ? (string) config('services.google.redirect')
            : url((string) config('services.google.redirect'));
    }

    private function googleIsConfigured(): bool
    {
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');

        return filled($clientId)
            && filled($clientSecret)
            && str_ends_with($clientId, '.apps.googleusercontent.com')
            && ! in_array($clientSecret, ['...', 'your-client-secret'], true);
    }
}
