<?php

namespace App\Http\Middleware;

use App\Models\AdminPariwisata;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->session()->get('jg_role');

        if (! $role) {
            $login = redirect()->route('login');

            if ($request->is('customer/*')) {
                $request->session()->put('customer_redirect', $request->fullUrl());
            }

            return $login->with('error', 'Silakan masuk terlebih dahulu untuk memilih tiket.');
        }

        if ($roles !== [] && ! in_array($role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($role === 'ADMIN_PARIWISATA') {
            $admin = AdminPariwisata::query()->with('destinasi')->find($request->session()->get('jg_user_id'));

            if (! $admin || $admin->status_akun !== 'AKTIF' || $admin->destinasi?->status_aktif !== 'aktif') {
                $request->session()->forget(['jg_user_id', 'jg_user_name', 'jg_role']);

                return redirect()->route('login')->with('error', 'Akses admin dinonaktifkan karena destinasi yang dikelola sedang nonaktif.');
            }
        }

        return $next($request);
    }
}
