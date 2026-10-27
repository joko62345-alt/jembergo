<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdminPariwisata;
use App\Models\Customer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

        $customer = Customer::query()->firstOrCreate(
            ['email' => $email],
            [
                'nama' => is_string($claims['name'] ?? null) && $claims['name'] !== ''
                    ? $claims['name']
                    : $email,
                'password' => null,
                'auth_provider' => 'google',
            ],
        );

        if ($customer->auth_provider !== 'google') {
            $customer->update(['auth_provider' => 'google']);
        }

        return $this->tokenResponse(
            $customer,
            'CUSTOMER',
            $data['device_name'] ?? 'flutter',
        );
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

        $customer = Customer::query()->create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'password' => Hash::make($data['password']),
            'auth_provider' => 'manual',
        ]);

        return $this->tokenResponse($customer, 'CUSTOMER', $data['device_name'] ?? 'flutter', 201);
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
