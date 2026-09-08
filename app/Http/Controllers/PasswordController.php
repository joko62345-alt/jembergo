<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function forgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $customer = Customer::where('email', $data['email'])->first();

        if ($customer) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(['email' => $customer->email], ['token' => Hash::make($token), 'created_at' => now()]);
            Log::info('JemberGo password reset link', ['email' => $customer->email, 'url' => route('password.reset', ['token' => $token, 'email' => $customer->email])]);
        }

        return back()->with('success', 'Jika email terdaftar, tautan reset password telah dikirim.');
    }

    public function resetForm(string $token, Request $request): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->string('email')->toString()]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'token' => ['required'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        abort_unless($record && Hash::check($data['token'], $record->token) && now()->diffInMinutes($record->created_at) <= 60, 422, 'Tautan reset password tidak valid atau sudah kedaluwarsa.');
        Customer::where('email', $data['email'])->update(['password' => Hash::make($data['password'])]);
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk kembali.');
    }
}