<?php

namespace App\Http\Controllers;

use App\Mail\CustomerPasswordReset;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            $url = route('password.reset', ['token' => $token, 'email' => $customer->email]);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $customer->email],
                ['token' => Hash::make($token), 'created_at' => now()],
            );

            try {
                Mail::to($customer->email)->send(new CustomerPasswordReset($url));
            } catch (\Throwable $exception) {
                DB::table('password_reset_tokens')->where('email', $customer->email)->delete();
                report($exception);
            }
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

        $isValid = $record
            && Hash::check($data['token'], $record->token)
            && now()->subHour()->lessThanOrEqualTo($record->created_at);

        if (! $isValid) {
            return redirect()->route('login')->with('error', 'Tautan reset password tidak valid atau sudah kedaluwarsa.');
        }

        Customer::where('email', $data['email'])->update(['password' => Hash::make($data['password'])]);
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk kembali.');
    }
}
