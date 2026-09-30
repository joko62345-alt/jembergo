@extends('layouts.superadmin')
@section('title', 'Akun Admin')
@section('page_label', 'Manajemen / Admin Pariwisata')
@section('content')
    <div class="page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div><span class="page-kicker">Manajemen pengguna</span>
            <h1>Akun Admin Pariwisata</h1>
            <p>Daftar admin yang bertugas mengelola destinasi.</p>
        </div>
        <a href="{{ route('superadmin.management.admin.create') }}" class="btn btn-warning"><i
                class="bi bi-plus-lg me-1" aria-hidden="true"></i>Buat akun admin</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor HP</th>
                        <th>Destinasi tugas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td class="fw-semibold">{{ $admin->nama }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->no_hp }}</td>
                            <td>{{ $admin->destinasi->nama_wisata }}</td>
                            <td><span
                                    class="badge text-bg-success">{{ \App\Support\StatusLabel::account($admin->status_akun) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">Belum ada akun
                                admin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $admins->links() }}</div>
@endsection
