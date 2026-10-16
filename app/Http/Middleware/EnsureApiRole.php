<?php

namespace App\Http\Middleware;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('sanctum');
        $role = match (true) {
            $user instanceof Customer => 'CUSTOMER',
            $user instanceof AdminPariwisata => 'ADMIN_PARIWISATA',
            default => null,
        };

        if ($role === null || ($roles !== [] && ! in_array($role, $roles, true))) {
            return new JsonResponse(['message' => 'Anda tidak memiliki akses ke endpoint ini.'], 403);
        }

        if ($user instanceof AdminPariwisata) {
            $user->loadMissing('destinasi');
            if ($user->status_akun !== 'AKTIF' || $user->destinasi?->status_aktif !== 'aktif') {
                $user->tokens()->delete();

                return new JsonResponse(['message' => 'Akun admin atau destinasi yang dikelola sedang tidak aktif.'], 403);
            }
        }

        $accessToken = $user->currentAccessToken();
        if ($accessToken === null || ! $accessToken->can($role)) {
            return new JsonResponse(['message' => 'Token tidak memiliki izin untuk peran ini.'], 403);
        }

        return $next($request);
    }
}
