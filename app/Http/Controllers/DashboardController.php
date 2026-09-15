<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Models\Tiket;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $role = session('jg_role');

        if ($role === 'ADMIN_PARIWISATA') {
            $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
            $destination = DestinasiWisata::findOrFail($admin->id_destinasi);
            $destinationOrders = Pemesanan::query()->where('id_destinasi', $admin->id_destinasi);
            $recentBookings = (clone $destinationOrders)->with(['customer', 'pembayaran', 'tiket'])->latest('tanggal_pemesanan')->limit(6)->get();
            $pendingVerification = (clone $destinationOrders)
                ->with(['customer', 'pembayaran', 'tiket'])
                ->whereHas('tiket', fn ($query) => $query->where('status_tiket', 'ACTIVE'))
                ->where(function ($query) {
                    $query->whereHas('pembayaran', fn ($payment) => $payment->where('status_pembayaran', 'PAID'))
                        ->orWhereHas('pembayaran', fn ($payment) => $payment->where('metode_pembayaran', 'CASH')->where('status_pembayaran', 'PENDING'));
                })
                ->latest('tanggal_kunjungan')
                ->limit(5)
                ->get();
            $counts = [
                'destinasi' => 1,
                'admin' => 0,
                'customer' => 0,
                'pemesanan' => Pemesanan::where('id_destinasi', $admin->id_destinasi)->count(),
                'tiket' => Tiket::where('status_tiket', 'USED')->whereHas('pemesanan', fn ($query) => $query->where('id_destinasi', $admin->id_destinasi))->count(),
            ];
            $adminStats = [
                'orders' => (clone $destinationOrders)->count(),
                'pending' => (clone $destinationOrders)->whereHas('tiket', fn ($query) => $query->where('status_tiket', 'ACTIVE'))->count(),
                'verified' => (clone $destinationOrders)->whereHas('tiket', fn ($query) => $query->where('status_tiket', 'USED'))->count(),
                'revenue' => (float) (clone $destinationOrders)->where('status_pemesanan', 'PAID')->sum('total_harga'),
            ];

            return view('admin.dashboard', compact('role', 'counts', 'admin', 'destination', 'adminStats', 'recentBookings', 'pendingVerification'));
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

        $analytics = $this->superAdminAnalytics();

        return view('dashboard', compact('role', 'counts', 'analytics'));
    }

    private function superAdminAnalytics(): array
    {
        $empty = ['favorite' => [], 'months' => [], 'transactions' => [], 'revenue' => [], 'totalTransactions' => 0, 'totalRevenue' => 0, 'totalTickets' => 0, 'favoriteName' => null];

        if (! Schema::hasTable('pemesanan') || ! Schema::hasTable('detail_pemesanan') || ! Schema::hasTable('destinasi_wisata')) {
            return $empty;
        }

        $paidOrders = DB::table('pemesanan')->where('status_pemesanan', 'PAID');
        $empty['totalTransactions'] = (clone $paidOrders)->count();
        $empty['totalRevenue'] = (float) (clone $paidOrders)->sum('total_harga');
        $empty['totalTickets'] = (int) DB::table('detail_pemesanan')->join('pemesanan', 'pemesanan.id_pemesanan', '=', 'detail_pemesanan.id_pemesanan')->where('pemesanan.status_pemesanan', 'PAID')->sum('detail_pemesanan.jumlah');
        $empty['favorite'] = DB::table('detail_pemesanan')
            ->join('pemesanan', 'pemesanan.id_pemesanan', '=', 'detail_pemesanan.id_pemesanan')
            ->join('destinasi_wisata', 'destinasi_wisata.id_destinasi', '=', 'pemesanan.id_destinasi')
            ->where('pemesanan.status_pemesanan', 'PAID')
            ->select('destinasi_wisata.nama_wisata as name', DB::raw('SUM(detail_pemesanan.jumlah) as tickets'))
            ->groupBy('destinasi_wisata.id_destinasi', 'destinasi_wisata.nama_wisata')
            ->orderByDesc('tickets')
            ->limit(5)
            ->get()
            ->map(fn ($item) => ['name' => $item->name, 'tickets' => (int) $item->tickets])
            ->all();
        $empty['favoriteName'] = $empty['favorite'][0]['name'] ?? null;

        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(CarbonPeriod::create($start, '1 month', now()->startOfMonth()))->mapWithKeys(fn (Carbon $month) => [$month->format('Y-m') => ['label' => $month->translatedFormat('M'), 'transactions' => 0, 'revenue' => 0]])->all();
        $monthlyOrders = (clone $paidOrders)->where('tanggal_pemesanan', '>=', $start)->get(['tanggal_pemesanan', 'total_harga']);

        foreach ($monthlyOrders as $order) {
            $key = Carbon::parse($order->tanggal_pemesanan)->format('Y-m');
            if (isset($months[$key])) {
                $months[$key]['transactions']++;
                $months[$key]['revenue'] += (float) $order->total_harga;
            }
        }

        $empty['months'] = array_values($months);
        $empty['transactions'] = array_column($empty['months'], 'transactions');
        $empty['revenue'] = array_column($empty['months'], 'revenue');

        return $empty;
    }
}