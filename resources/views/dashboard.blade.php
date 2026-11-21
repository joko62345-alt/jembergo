@extends('layouts.superadmin')

@section('title', 'Dashboard')
@section('page_label', 'Dashboard overview')

@section('content')
    @php
        $isSuperAdmin = $role === 'SUPER_ADMIN';
        $cards = $isSuperAdmin
            ? [
                [
                    'key' => 'destinasi',
                    'label' => 'Destinasi aktif',
                    'icon' => 'bi-geo-alt',
                    'route' => route('superadmin.destinations'),
                ],
                [
                    'key' => 'admin',
                    'label' => 'Admin aktif',
                    'icon' => 'bi-person-badge',
                    'route' => route('superadmin.management.admins'),
                ],
                [
                    'key' => 'customer',
                    'label' => 'Customer terdaftar',
                    'icon' => 'bi-people',
                    'route' => route('superadmin.management.customers'),
                ],
                [
                    'key' => 'pemesanan',
                    'label' => 'Total pemesanan',
                    'icon' => 'bi-receipt',
                    'route' => route('superadmin.report'),
                ],
            ]
            : ($role === 'ADMIN_PARIWISATA'
                ? [
                    [
                        'key' => 'tiket',
                        'label' => 'Tiket terverifikasi',
                        'icon' => 'bi-qr-code-scan',
                        'route' => route('admin.verification.history'),
                    ],
                    [
                        'key' => 'pemesanan',
                        'label' => 'Pemesanan destinasi',
                        'icon' => 'bi-calendar-check',
                        'route' => route('admin.verification'),
                    ],
                ]
                : [
                    [
                        'key' => 'tiket',
                        'label' => 'Tiket digunakan',
                        'icon' => 'bi-ticket-perforated',
                        'route' => route('customer.orders'),
                    ],
                    [
                        'key' => 'pemesanan',
                        'label' => 'Total pemesanan',
                        'icon' => 'bi-bag-check',
                        'route' => route('customer.orders'),
                    ],
                ]);
    @endphp

    <div class="page-heading">
        <span class="page-kicker">Ringkasan sistem</span>
        <h1>Selamat datang, {{ session('jg_user_name') }}.</h1>
        <p>Pantau operasional pariwisata Jember dan akses pekerjaan penting dari satu ruang kerja.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <section class="row g-3 mb-4" aria-label="Statistik utama">
        @foreach ($cards as $card)
            <div class="col-sm-6 col-xl-3">
                <a href="{{ $card['route'] }}" class="text-decoration-none">
                    <article class="card stat-panel h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <span class="stat-label">{{ $card['label'] }}</span>
                                <span class="stat-icon"><i class="bi {{ $card['icon'] }}"
                                        aria-hidden="true"></i></span>
                            </div>
                            <strong
                                class="stat-value">{{ number_format($counts[$card['key']]) }}</strong>
                            <span class="small text-warning-emphasis">Buka rincian <i
                                    class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></span>
                        </div>
                    </article>
                </a>
            </div>
        @endforeach
    </section>

    @if ($isSuperAdmin)
        @include('components.dashboard-analytics', [
            'analytics' => $analytics,
            'isSuperAdmin' => true,
            'destinationName' => null,
            'reportRoute' => route('superadmin.report'),
        ])
    @endif

    <section class="row g-4">
        <div class="col-lg-8">
            <article class="card h-100">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="section-title mb-1">Pusat tindakan</h2>
                            <p class="section-subtitle mb-0">Akses cepat ke pekerjaan yang paling sering
                                digunakan.</p>
                        </div>
                        <span class="stat-icon"><i class="bi bi-lightning-charge"
                                aria-hidden="true"></i></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($role === 'ADMIN_PARIWISATA')
                            <a href="{{ route('admin.verification') }}"
                                class="btn btn-warning">Verifikasi tiket <i
                                    class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.verification.history') }}"
                                class="btn btn-outline-dark">Riwayat verifikasi</a>
                        @elseif($role === 'CUSTOMER')
                            <a href="{{ route('destinations.index') }}"
                                class="btn btn-warning">Jelajahi destinasi <i
                                    class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                            <a href="{{ route('customer.orders') }}"
                                class="btn btn-outline-dark">Pesanan saya</a>
                        @else
                            <a href="{{ route('superadmin.destinations') }}"
                                class="btn btn-warning">Kelola destinasi <i
                                    class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                            <a href="{{ route('superadmin.report') }}"
                                class="btn btn-outline-dark">Lihat laporan</a>
                        @endif
                    </div>
                </div>
            </article>
        </div>
        <div class="col-lg-4">
            <article class="card h-100 border-0"
                style="background: var(--jg-navy) !important; color: white;">
                <div class="card-body p-4 p-lg-5">
                    <span class="page-kicker">JemberGo workspace</span>
                    <h2 class="h5 fw-bold text-white mt-3">Informasi wisata Jember, lebih terarah.</h2>
                    <p class="small mb-0" style="color: #b7c8da;">Kelola destinasi, pengguna, artikel,
                        dan transaksi tanpa kehilangan konteks.</p>
                </div>
            </article>
        </div>
    </section>
@endsection
