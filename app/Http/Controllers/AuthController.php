<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\SuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan masuk untuk melanjutkan.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['jg_user_id', 'jg_user_name', 'jg_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}