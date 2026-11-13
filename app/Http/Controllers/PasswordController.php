<?php

namespace App\Http\Controllers;

use App\Mail\CustomerOtp;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function forgot(Request $request): View
    {
        $email = $request->session()->get('password_reset_email');
        $verifiedEmail = $request->session()->get('password_reset_verified_email');
        $verifiedAt = $request->session()->get('password_reset_verified_at');
        $hasValidOtpVerification = is_string($email)
            && $verifiedEmail === $email
            && is_numeric($verifiedAt)
            && (int) $verifiedAt >= now()->subMinutes(10)->timestamp
            && (int) $verifiedAt <= now()->timestamp;

        if (! $hasValidOtpVerification) {
            $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_at']);
        }

        $isOtpVerified = $request->query('stage') === 'password' && $hasValidOtpVerification;
        $stage = $request->query('stage');
        $showOtpForm = $stage === 'otp' && is_string($email);

        return view('auth.forgot-password', [
            'email' => $showOtpForm ? $email : null,
            'isOtpVerified' => $isOtpVerified,
            'showOtpForm' => $showOtpForm,
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower($data['email']);
        $customer = Customer::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        $request->session()->put('password_reset_email', $email);
        $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_at']);

        if ($customer) {
            $otp = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $customer->email],
                ['token' => Hash::make($otp), 'created_at' => now()],
            );

            try {
                Mail::to($customer->email)->send(new CustomerOtp($otp, $customer->nama));
            } catch (\Throwable $exception) {
                DB::table('password_reset_tokens')->where('email', $customer->email)->delete();
                report($exception);

                $request->session()->forget([
                    'password_reset_email',
                    'password_reset_verified_email',
                    'password_reset_verified_at',
                ]);

                return redirect()->route('password.request')
                    ->with('error', 'Kode OTP gagal dikirim. Silakan coba lagi beberapa saat.');
            }
        }

        return redirect()->route('password.request', ['stage' => 'otp'])->with('success', 'Jika email terdaftar, kode OTP telah dikirim ke Gmail Anda.');
    }

    public function verifyResetCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']]);
        $email = $request->session()->get('password_reset_email');
        $record = is_string($email)
            ? DB::table('password_reset_tokens')->where('email', $email)->first()
            : null;

        $createdAt = $record ? Carbon::parse($record->created_at) : null;
        $isValid = $record
            && Hash::check($data['otp'], $record->token)
            && $createdAt->greaterThanOrEqualTo(now()->subMinutes(10))
            && $createdAt->lessThanOrEqualTo(now());

        if (! $isValid) {
            return redirect()->route('password.request', ['stage' => 'otp'])->with('error', 'Kode OTP tidak valid atau sudah kadaluarsa.');
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->put([
            'password_reset_verified_email' => $email,
            'password_reset_verified_at' => now()->timestamp,
        ]);

        return redirect()->route('password.request', ['stage' => 'password'])->with('success', 'OTP berhasil diverifikasi. Silakan buat password baru.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        $verifiedEmail = $request->session()->get('password_reset_verified_email');
        $verifiedAt = $request->session()->get('password_reset_verified_at');
        $isOtpVerified = is_string($email)
            && $verifiedEmail === $email
            && is_numeric($verifiedAt)
            && (int) $verifiedAt >= now()->subMinutes(10)->timestamp
            && (int) $verifiedAt <= now()->timestamp;

        if (! $isOtpVerified) {
            $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_at']);

            return redirect()->route('password.request')->with('error', 'Silakan verifikasi OTP terlebih dahulu.');
        }

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $customer = Customer::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

        if (! $customer) {
            $request->session()->forget([
                'password_reset_email',
                'password_reset_verified_email',
                'password_reset_verified_at',
            ]);

            return redirect()->route('password.request')->with('error', 'Akun tidak ditemukan. Silakan mulai ulang proses reset password.');
        }

        $customer->update(['password' => Hash::make($data['password'])]);
        $request->session()->forget([
            'password_reset_email',
            'password_reset_verified_email',
            'password_reset_verified_at',
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk kembali.');
    }
}
