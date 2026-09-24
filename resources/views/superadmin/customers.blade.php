@extends('layouts.superadmin')
@section('title', 'Akun Customer')
@section('page_label', 'Manajemen / Customer')
@section('content')
    <div class="page-heading"><span class="page-kicker">Manajemen pengguna</span>
        <h1>Akun Customer</h1>
        <p>Daftar customer yang terdaftar di JemberGo.</p>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor HP</th>
                        <th>Provider</th>
                        <th>Tanggal daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="fw-semibold">{{ $customer->nama }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->no_hp ?: '-' }}</td>
                            <td><span class="badge text-bg-light">{{ $customer->auth_provider }}</span>
                            </td>
                            <td>{{ $customer->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">Belum ada customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
