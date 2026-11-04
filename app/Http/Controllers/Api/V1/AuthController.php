<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\CustomerOtp;
use App\Models\AdminPariwisata;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function googleConfig(): JsonResponse
    {
        $serverClientId = config('services.google.client_id');
        if (! is_string($serverClientId) || blank($serverClientId)) {
            return response()->json([
                'message' => 'Login Google belum dikonfigurasi oleh administrator.',
            ], 503);
        }

        return response()->json([
            'data' => [
                'server_client_id' => $serverClientId,
            ],
        ]);
    }

    public function googleLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string', 'max:5000'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $serverClientId = config('services.google.client_id');
        if (! is_string($serverClientId) || blank($serverClientId)) {
            return response()->json([
                'message' => 'Login Google belum dikonfigurasi oleh administrator.',
            ], 503);
        }

        try {
            $response = Http::timeout(10)->get(
                'https://oauth2.googleapis.com/tokeninfo',
                ['id_token' => $data['id_token']],
            );
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Tidak dapat memverifikasi akun Google saat ini.',
            ], 503);
        }

        $claims = $response->json();
        if (
            ! $response->successful()
            || ! is_array($claims)
            || ($claims['aud'] ?? null) !== $serverClientId
            || ! in_array($claims['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
            || ! is_numeric($claims['exp'] ?? null)
            || (int) $claims['exp'] <= now()->timestamp
            || ! in_array($claims['email_verified'] ?? null, [true, 'true'], true)
            || ! is_string($claims['email'] ?? null)
            || ! filter_var($claims['email'], FILTER_VALIDATE_EMAIL)
        ) {
            return response()->json([
                'message' => 'Token Google tidak valid atau email belum diverifikasi.',
            ], 401);
        }

        $email = strtolower($claims['email']);
        if (AdminPariwisata::query()->where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Akun administrator tidak dapat masuk menggunakan Google.',
            ], 403);
        }

        $customer = Customer::query()->where('email', $email)->first();
        if ($customer?->google_verified_at !== null) {
            if ($customer->auth_provider !== 'google') {
                $customer->update(['auth_provider' => 'google']);
            }

            return $this->tokenResponse(
                $customer,
                'CUSTOMER',
                $data['device_name'] ?? 'flutter',
            );
        }

        $name = is_string($claims['name'] ?? null) && $claims['name'] !== ''
            ? $claims['name']
            : $email;

        return $this->beginEmailVerification($email, $name, 'google', ['nama' => $name]);
    }

    public function verifyRegistration(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'otp' => ['required', 'digits:6'],
        ]);
        $email = strtolower($data['email']);

        return DB::transaction(function () use ($email, $data): JsonResponse {
            $challenge = DB::table('mobile_auth_challenges')
                ->where('email', $email)
                ->where('purpose', 'registration')
                ->lockForUpdate()
                ->first();

            if (! $this->isValidChallenge($challenge, $data['otp'])) {
                return $this->invalidOtpResponse();
            }

            $pendingData = $this->challengePayload($challenge);
            if (
                ! is_string($pendingData['nama'] ?? null)
                || ! is_string($pendingData['no_hp'] ?? null)
                || ! is_string($pendingData['password'] ?? null)
            ) {
                return $this->invalidOtpResponse();
            }

            if (
                Customer::query()->where('email', $email)->orWhere('no_hp', $pendingData['no_hp'])->exists()
                || AdminPariwisata::query()->where('email', $email)->exists()
            ) {
                DB::table('mobile_auth_challenges')->where('id', $challenge->id)->delete();

                return response()->json([
                    'message' => 'Email atau nomor telepon sudah terdaftar. Silakan daftar kembali dengan data lain.',
                ], 409);
            }

            $customer = Customer::query()->create([
                'nama' => $pendingData['nama'],
                'email' => $email,
                'no_hp' => $pendingData['no_hp'],
                'password' => $pendingData['password'],
                'auth_provider' => 'manual',
            ]);

            DB::table('mobile_auth_challenges')->where('id', $challenge->id)->delete();

            return response()->json([
                'message' => 'Email berhasil diverifikasi. Silakan masuk.',
                'data' => ['id' => $customer->id_customer, 'email' => $email],
            ]);
        });
    }

    public function verifyGoogle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'otp' => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);
        $email = strtolower($data['email']);

        return DB::transaction(function () use ($email, $data): JsonResponse {
            $challenge = DB::table('mobile_auth_challenges')
                ->where('email', $email)
                ->where('purpose', 'google')
                ->lockForUpdate()
                ->first();

            if (! $this->isValidChallenge($challenge, $data['otp'])) {
                return $this->invalidOtpResponse();
            }

            $pendingData = $this->challengePayload($challenge);
            if (! is_string($pendingData['nama'] ?? null)) {
                return $this->invalidOtpResponse();
            }

            $customer = Customer::query()->firstOrCreate(
                ['email' => $email],
                [
                    'nama' => $pendingData['nama'],
                    'password' => null,
                    'auth_provider' => 'google',
                ],
            );
            $customer->update([
                'auth_provider' => 'google',
                'google_verified_at' => now(),
            ]);
            DB::table('mobile_auth_challenges')->where('id', $challenge->id)->delete();

            return $this->tokenResponse(
                $customer,
                'CUSTOMER',
                $data['device_name'] ?? 'flutter',
            );
        });
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'purpose' => ['required', 'in:registration,google'],
        ]);
        $challenge = DB::table('mobile_auth_challenges')
            ->where('email', strtolower($data['email']))
            ->where('purpose', $data['purpose'])
            ->first();

        if (! $challenge) {
            return response()->json([
                'message' => 'Tidak ada proses verifikasi yang perlu dikirim ulang.',
            ], 404);
        }

        $pendingData = $this->challengePayload($challenge);
        if (! is_string($pendingData['nama'] ?? null)) {
            return $this->invalidOtpResponse();
        }

        return $this->beginEmailVerification(
            $challenge->email,
            $pendingData['nama'],
            $challenge->purpose,
            $pendingData,
        );
    }

    public function requestPasswordResetOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:150']]);
        $email = strtolower($data['email']);
        $customer = Customer::query()->where('email', $email)->first();

        if (! $customer) {
            return response()->json([
                'message' => 'Jika email terdaftar, kode OTP telah dikirim ke Gmail Anda.',
            ], 202);
        }

        return $this->beginEmailVerification(
            $email,
            $customer->nama,
            'password_reset',
            [],
            false,
        );
    }

    public function verifyPasswordResetOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'otp' => ['required', 'digits:6'],
        ]);
        $email = strtolower($data['email']);

        return DB::transaction(function () use ($email, $data): JsonResponse {
            $challenge = DB::table('mobile_auth_challenges')
                ->where('email', $email)
                ->where('purpose', 'password_reset')
                ->lockForUpdate()
                ->first();

            if (! $this->isValidChallenge($challenge, $data['otp'])) {
                return $this->invalidOtpResponse();
            }

            $resetToken = Str::random(64);
            DB::table('mobile_auth_challenges')->where('id', $challenge->id)->delete();
            DB::table('mobile_auth_challenges')->updateOrInsert(
                ['email' => $email, 'purpose' => 'password_reset_verified'],
                [
                    'token' => Hash::make($resetToken),
                    'payload' => '{}',
                    'created_at' => now(),
                ],
            );

            return response()->json([
                'data' => ['reset_token' => $resetToken],
            ]);
        });
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'reset_token' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $email = strtolower($data['email']);

        return DB::transaction(function () use ($email, $data): JsonResponse {
            $challenge = DB::table('mobile_auth_challenges')
                ->where('email', $email)
                ->where('purpose', 'password_reset_verified')
                ->lockForUpdate()
                ->first();

            if (! $this->isValidChallenge($challenge, $data['reset_token'])) {
                return $this->invalidOtpResponse();
            }

            $customer = Customer::query()->where('email', $email)->first();
            if (! $customer) {
                DB::table('mobile_auth_challenges')->where('id', $challenge->id)->delete();

                return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
            }

            $customer->update(['password' => Hash::make($data['password'])]);
            DB::table('mobile_auth_challenges')->where('id', $challenge->id)->delete();

            return response()->json(['message' => 'Password berhasil diubah.']);
        });
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'email' => ['required', 'email', 'max:150', 'unique:customer,email', 'unique:admin_pariwisata,email'],
            'no_hp' => ['required', 'regex:/^(?:[0-9]{10,12}|\+[0-9]{10,15})$/', 'unique:customer,no_hp'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);
        $email = strtolower($data['email']);

        return $this->beginEmailVerification(
            $email,
            $data['nama'],
            'registration',
            [
                'nama' => $data['nama'],
                'no_hp' => $data['no_hp'],
                'password' => Hash::make($data['password']),
            ],
        );
    }

    private function beginEmailVerification(
        string $email,
        string $customerName,
        string $purpose,
        array $pendingData,
        bool $isEmailVerification = true,
    ): JsonResponse {
        $otp = (string) random_int(100000, 999999);
        DB::table('mobile_auth_challenges')->updateOrInsert(
            ['email' => $email, 'purpose' => $purpose],
            [
                'token' => Hash::make($otp),
                'payload' => json_encode($pendingData, JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ],
        );

        try {
            Mail::to($email)->send(new CustomerOtp($otp, $customerName, $isEmailVerification));
        } catch (\Throwable $exception) {
            DB::table('mobile_auth_challenges')
                ->where('email', $email)
                ->where('purpose', $purpose)
                ->delete();
            report($exception);

            return response()->json([
                'message' => 'Kode OTP gagal dikirim. Silakan periksa konfigurasi email lalu coba lagi.',
            ], 503);
        }

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke Gmail Anda.',
            'data' => [
                'email' => $email,
                'purpose' => $purpose,
            ],
        ], 202);
    }

    private function isValidChallenge(?object $challenge, string $otp): bool
    {
        if (! $challenge || ! Hash::check($otp, $challenge->token)) {
            return false;
        }

        $createdAt = Carbon::parse($challenge->created_at);

        return $createdAt->greaterThanOrEqualTo(now()->subMinutes(10))
            && $createdAt->lessThanOrEqualTo(now());
    }

    /**
     * @return array<string, mixed>
     */
    private function challengePayload(object $challenge): array
    {
        $payload = json_decode($challenge->payload, true);

        return is_array($payload) ? $payload : [];
    }

    private function invalidOtpResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Kode OTP tidak valid atau sudah kadaluarsa.',
        ], 422);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = Customer::query()->where('email', $data['email'])->first();
        if ($customer && $customer->password && Hash::check($data['password'], $customer->password)) {
            return $this->tokenResponse($customer, 'CUSTOMER', $data['device_name'] ?? 'flutter');
        }

        $admin = AdminPariwisata::query()->with('destinasi')->where('email', $data['email'])->first();
        if (
            $admin
            && $admin->status_akun === 'AKTIF'
            && $admin->destinasi?->status_aktif === 'aktif'
            && Hash::check($data['password'], $admin->password)
        ) {
            return $this->tokenResponse($admin, 'ADMIN_PARIWISATA', $data['device_name'] ?? 'flutter');
        }

        return response()->json([
            'message' => 'Email atau password tidak sesuai, atau akun tidak aktif.',
        ], 401);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $role = $user instanceof Customer ? 'CUSTOMER' : 'ADMIN_PARIWISATA';

        return response()->json([
            'data' => $this->userData($user, $role),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('sanctum')->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    private function tokenResponse(Customer|AdminPariwisata $user, string $role, string $deviceName, int $status = 200): JsonResponse
    {
        $token = $user->createToken(
            $deviceName,
            [$role],
            now()->addMinutes((int) config('sanctum.expiration'))
        );

        return response()->json([
            'data' => [
                'user' => $this->userData($user, $role),
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            ],
        ], $status);
    }

    /**
     * @return array<string, mixed>
     */
    private function userData(Customer|AdminPariwisata $user, string $role): array
    {
        return [
            'id' => $user->getKey(),
            'nama' => $user->nama,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
            'role' => $role,
            'destinasi' => $user instanceof AdminPariwisata
                ? ['id' => $user->destinasi?->id_destinasi, 'nama' => $user->destinasi?->nama_wisata]
                : null,
        ];
    }
}
