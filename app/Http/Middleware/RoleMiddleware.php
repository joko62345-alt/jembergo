<?php

namespace App\Http\Middleware;

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

        return $next($request);
    }
}