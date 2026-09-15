<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Pemesanan;
use App\Models\Tiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function orders(?string $filter = null): View
    {
        $filter ??= 'all';
        Pemesanan::expirePendingPayments();
        Tiket::where('status_tiket', 'ACTIVE')
            ->whereHas('pemesanan', fn ($query) => $query->whereDate('tanggal_kunjungan', '<', today()))
            ->update(['status_tiket' => 'EXPIRED']);
        $orders = Pemesanan::with(['destinasi', 'tiket', 'pembayaran'])
            ->where('id_customer', session('jg_user_id'))
            ->when($filter === 'active', fn ($query) => $query->whereHas('tiket', fn ($tickets) => $tickets->where('status_tiket', 'ACTIVE')))
            ->when($filter === 'history', fn ($query) => $query->whereHas('tiket', fn ($tickets) => $tickets->whereIn('status_tiket', ['USED', 'CANCELLED', 'EXPIRED'])))
            ->latest('tanggal_pemesanan')
            ->paginate(10);

        return view('customer.orders', compact('orders', 'filter'));
    }

    public function activeTickets(): View
    {
        return $this->orders('active');
    }

    public function ticketHistory(): View
    {
        return $this->orders('history');
    }

    public function profile(): View
    {
        abort_unless(Schema::hasTable('customer') && session()->has('jg_user_id'), 401, 'Sesi customer tidak ditemukan. Silakan login kembali.');

        return view('customer.profile', ['customer' => Customer::where('id_customer', session('jg_user_id'))->firstOrFail()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('customer') && session()->has('jg_user_id'), 401, 'Sesi customer tidak ditemukan. Silakan login kembali.');
        $customer = Customer::where('id_customer', session('jg_user_id'))->firstOrFail();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:customer,email,' . $customer->id_customer . ',id_customer'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        if (! empty($data['password'])) { $data['password'] = Hash::make($data['password']); } else { unset($data['password']); }
        $customer->update($data);
        $request->session()->put('jg_user_name', $customer->nama);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}