<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Models\Tiket;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $role = session('jg_role');

        if ($role === 'ADMIN_PARIWISATA') {
            $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
            $counts = [
                'destinasi' => 1,
                'admin' => 0,
                'customer' => 0,
                'pemesanan' => Pemesanan::where('id_destinasi', $admin->id_destinasi)->count(),
                'tiket' => Tiket::where('status_tiket', 'USED')->whereHas('pemesanan', fn ($query) => $query->where('id_destinasi', $admin->id_destinasi))->count(),
            ];

            return view('dashboard', compact('role', 'counts'));
        }

        if ($role === 'CUSTOMER') {
            $counts = [
                'destinasi' => 0,
                'admin' => 0,
                'customer' => 0,
                'pemesanan' => Pemesanan::where('id_customer', session('jg_user_id'))->count(),
                'tiket' => Tiket::where('status_tiket', 'USED')->whereHas('pemesanan', fn ($query) => $query->where('id_customer', session('jg_user_id')))->count(),
            ];

            return view('dashboard', compact('role', 'counts'));
        }

        $counts = [
            'destinasi' => Schema::hasTable('destinasi_wisata') ? DestinasiWisata::where('status_aktif', true)->count() : 0,
            'admin' => Schema::hasTable('admin_pariwisata') ? AdminPariwisata::where('status_akun', 'AKTIF')->count() : 0,
            'customer' => Schema::hasTable('customer') ? Customer::count() : 0,
            'pemesanan' => Schema::hasTable('pemesanan') ? Pemesanan::count() : 0,
            'tiket' => Schema::hasTable('tiket') ? Tiket::where('status_tiket', 'USED')->count() : 0,
        ];

        return view('dashboard', compact('role', 'counts'));
    }
}