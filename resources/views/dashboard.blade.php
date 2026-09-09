<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        .app-shell { min-height: 100vh; background: #f6fafb; }
        .app-sidebar { width: 250px; min-height: 100vh; background: #263238; }
        .app-sidebar .nav-link { color: #b8c8cb; border-radius: .75rem; padding: .75rem 1rem; }
        .app-sidebar .nav-link:hover, .app-sidebar .nav-link.active { color: #263238; background: #ffc22f; }
        .app-content { min-width: 0; }
        .stat-panel { border: 0; border-radius: 1.25rem; box-shadow: 0 8px 25px rgba(38,50,56,.06); }
        .stat-icon { display: grid; place-items: center; width: 44px; height: 44px; color: #f58b05; background: #fff3dc; border-radius: .9rem; }
        @media (max-width: 767.98px) { .app-sidebar { width: 72px; } .app-sidebar .brand-label, .app-sidebar .nav-label, .app-sidebar .sidebar-caption { display: none; } .app-sidebar .nav-link { text-align: center; padding: .8rem .4rem; } .app-sidebar .nav-link i { margin: 0 !important; font-size: 1.15rem; } }
    </style>
</head>
<body>
<div class="app-shell d-flex">
    <aside class="app-sidebar flex-shrink-0 p-3">
        <a href="{{ route('home') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5"><span class="brand-mark"><i class="bi bi-compass"></i></span><span class="brand-label fw-bold fs-5">Jember<span class="brand-accent">Go</span></span></a>
        <small class="sidebar-caption text-uppercase text-white-50 px-2">Menu utama</small>
        <nav class="nav flex-column gap-2 mt-2">
            <a href="{{ route('dashboard') }}" class="nav-link active"><i class="bi bi-grid-1x2 me-2"></i><span class="nav-label">Dashboard</span></a>
            @if($role === 'CUSTOMER')
                <a href="{{ route('destinations.index') }}" class="nav-link"><i class="bi bi-compass me-2"></i><span class="nav-label">Destinasi</span></a>
                <a href="{{ route('customer.orders') }}" class="nav-link"><i class="bi bi-ticket-perforated me-2"></i><span class="nav-label">Pesanan saya</span></a>
                <a href="{{ route('customer.profile') }}" class="nav-link"><i class="bi bi-person me-2"></i><span class="nav-label">Profil</span></a>
            @elseif($role === 'ADMIN_PARIWISATA')
                <a href="{{ route('admin.verification') }}" class="nav-link"><i class="bi bi-qr-code-scan me-2"></i><span class="nav-label">Verifikasi</span></a>
                <a href="{{ route('admin.verification.history') }}" class="nav-link"><i class="bi bi-clock-history me-2"></i><span class="nav-label">Riwayat</span></a>
            @else
                <a href="{{ route('superadmin.destinations') }}" class="nav-link"><i class="bi bi-geo-alt me-2"></i><span class="nav-label">Destinasi</span></a>
                <a href="{{ route('superadmin.management') }}" class="nav-link"><i class="bi bi-sliders me-2"></i><span class="nav-label">Management</span></a>
                <a href="{{ route('superadmin.report') }}" class="nav-link"><i class="bi bi-bar-chart me-2"></i><span class="nav-label">Laporan</span></a>
            @endif
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-5">@csrf<button class="nav-link w-100 border-0 bg-transparent text-start"><i class="bi bi-box-arrow-left me-2"></i><span class="nav-label">Keluar</span></button></form>
    </aside>
    <section class="app-content flex-grow-1">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center"><div><span class="text-secondary small">{{ now()->translatedFormat('l, d F Y') }}</span></div><div class="d-flex align-items-center gap-3"><span class="d-none d-sm-inline text-end"><strong class="d-block small">{{ session('jg_user_name') }}</strong><small class="text-secondary">{{ str_replace('_', ' ', $role) }}</small></span><span class="rounded-circle bg-warning-subtle p-2"><i class="bi bi-person text-warning"></i></span></div></header>
        <main class="container-fluid p-4 p-lg-5"><div class="mb-4"><span class="text-warning text-uppercase small fw-bold">Ruang kerja JemberGo</span><h1 class="display-6 fw-bold mb-1">Halo, {{ session('jg_user_name') }}.</h1><p class="text-secondary mb-0">Berikut ringkasan aktivitas untuk peran {{ str_replace('_', ' ', $role) }}.</p></div>
            @if(session('success'))<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>@endif
            <div class="row g-3 mb-4">@php($cards = $role === 'SUPER_ADMIN' ? [['destinasi','Destinasi aktif','bi-geo-alt',route('superadmin.destinations')],['admin','Admin aktif','bi-person-badge','#'],['customer','Customer','bi-people','#'],['pemesanan','Pemesanan','bi-receipt',route('superadmin.report')]] : ($role === 'ADMIN_PARIWISATA' ? [['tiket','Tiket terverifikasi','bi-qr-code-scan',route('admin.verification.history')],['pemesanan','Pemesanan destinasi','bi-calendar-check',route('admin.verification')]] : [['tiket','Tiket digunakan','bi-ticket-perforated',route('customer.orders')],['pemesanan','Total pemesanan','bi-bag-check',route('customer.orders')]]))@foreach($cards as $card)<div class="col-sm-6 col-xl-3"><a href="{{ $card[3] }}" class="text-decoration-none"><div class="card stat-panel h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-start"><span class="text-secondary small">{{ $card[1] }}</span><span class="stat-icon"><i class="bi {{ $card[2] }}"></i></span></div><strong class="display-6 d-block mt-3 text-dark">{{ $counts[$card[0]] }}</strong><small class="text-warning">Lihat detail <i class="bi bi-arrow-up-right"></i></small></div></div></a></div>@endforeach</div>
            <div class="row g-4"><div class="col-lg-8"><div class="card stat-panel h-100"><div class="card-body p-4 p-lg-5"><div class="d-flex justify-content-between align-items-center mb-4"><div><h2 class="h5 fw-bold mb-1">Langkah berikutnya</h2><p class="text-secondary small mb-0">Akses cepat ke aktivitas utama.</p></div><i class="bi bi-stars text-warning fs-3"></i></div>@if($role === 'ADMIN_PARIWISATA')<a href="{{ route('admin.verification') }}" class="btn btn-warning rounded-pill me-2">Verifikasi tiket <i class="bi bi-arrow-right ms-1"></i></a><a href="{{ route('admin.verification.history') }}" class="btn btn-outline-secondary rounded-pill">Riwayat</a>@elseif($role === 'CUSTOMER')<a href="{{ route('destinations.index') }}" class="btn btn-warning rounded-pill me-2">Jelajahi destinasi <i class="bi bi-arrow-right ms-1"></i></a><a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary rounded-pill">Pesanan saya</a>@else<a href="{{ route('superadmin.management') }}" class="btn btn-warning rounded-pill me-2">Buka management <i class="bi bi-arrow-right ms-1"></i></a><a href="{{ route('superadmin.report') }}" class="btn btn-outline-secondary rounded-pill">Laporan</a>@endif</div></div></div><div class="col-lg-4"><div class="card stat-panel h-100 bg-dark text-white"><div class="card-body p-4"><span class="text-warning small text-uppercase fw-bold">JemberGo</span><h2 class="h4 mt-3">Kelola perjalanan dengan lebih sederhana.</h2><p class="text-white-50 small mt-3 mb-0">Semua informasi wisata, tiket, dan aktivitasmu tersimpan di satu tempat.</p></div></div></div></div>
        </main>
    </section>
</div>
</body></html>