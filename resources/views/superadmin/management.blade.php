@extends('layouts.superadmin')
@section('title', 'Manajemen')
@section('page_label', 'Manajemen')
@section('content')
    <div class="page-heading"><span class="page-kicker">Administrasi sistem</span>
        <h1>Manajemen JemberGo</h1>
        <p>Kelola akun dan data utama aplikasi pariwisata.</p>
    </div>
    <h1 class="h2 fw-bold mb-1">Management JemberGo</h1>
    <p class="text-secondary mb-4">Kelola data utama aplikasi.</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5">Akun Admin Pariwisata</h2>
                    <p class="text-secondary">Buat dan lihat akun admin.</p><a
                        href="{{ route('superadmin.management.admin.create') }}"
                        class="btn btn-warning rounded-pill me-2">Buat akun</a><a
                        href="{{ route('superadmin.management.admins') }}"
                        class="btn btn-outline-dark rounded-pill">Lihat akun</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5">Akun Customer</h2>
                    <p class="text-secondary">Lihat customer terdaftar.</p><a
                        href="{{ route('superadmin.management.customers') }}"
                        class="btn btn-outline-dark rounded-pill">Lihat customer</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5">Laporan transaksi</h2>
                    <p class="text-secondary">Pantau transaksi dan pendapatan.</p><a
                        href="{{ route('superadmin.report') }}"
                        class="btn btn-outline-dark rounded-pill">Buka laporan</a>
                </div>
            </div>
        </div>
    </div>
@endsection
