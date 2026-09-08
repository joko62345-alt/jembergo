@props(['title' => 'JemberGo', 'active' => ''])
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    <style>
        .app-shell { min-height: 100vh; background: #f6fafb; }
        .app-sidebar { width: 250px; min-height: 100vh; background: #263238; }
        .app-sidebar .nav-link { color: #b8c8cb; border-radius: .75rem; padding: .75rem 1rem; }
        .app-sidebar .nav-link:hover, .app-sidebar .nav-link.active { color: #263238; background: #ffc22f; }
        .app-content { min-width: 0; }
        @media (max-width: 767.98px) { .app-sidebar { width: 72px; } .app-sidebar .brand-label, .app-sidebar .nav-label, .app-sidebar .sidebar-caption { display: none; } .app-sidebar .nav-link { text-align: center; padding: .8rem .4rem; } .app-sidebar .nav-link i { margin: 0 !important; font-size: 1.15rem; } }
    </style>
</head>
<body>
<div class="app-shell d-flex">
    <aside class="app-sidebar flex-shrink-0 p-3">
        <a href="{{ route('home') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5"><span><i class="bi bi-compass"></i></span><span class="brand-label fw-bold fs-5">Jember<span class="text-warning">Go</span></span></a>
        <small class="sidebar-caption text-uppercase text-white-50 px-2">Menu utama</small>
        <nav class="nav flex-column gap-2 mt-2">
            <a href="{{ route('dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2 me-2"></i><span class="nav-label">Dashboard</span></a>
            <a href="{{ route('admin.verification') }}" class="nav-link {{ $active === 'verification' ? 'active' : '' }}"><i class="bi bi-qr-code-scan me-2"></i><span class="nav-label">Verifikasi</span></a>
            <a href="{{ route('admin.bookings') }}" class="nav-link {{ $active === 'bookings' ? 'active' : '' }}"><i class="bi bi-calendar-check me-2"></i><span class="nav-label">Pemesanan destinasi</span></a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-5">@csrf<button class="nav-link w-100 border-0 bg-transparent text-start"><i class="bi bi-box-arrow-left me-2"></i><span class="nav-label">Keluar</span></button></form>
    </aside>
    <section class="app-content flex-grow-1">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center"><span class="text-secondary small">{{ now()->translatedFormat('l, d F Y H:i') }}</span><div class="d-flex align-items-center gap-3"><span class="d-none d-sm-inline text-end"><strong class="d-block small">{{ session('jg_user_name') }}</strong><small class="text-secondary">Admin Pariwisata</small></span><span class="rounded-circle bg-warning-subtle p-2"><i class="bi bi-person text-warning"></i></span></div></header>
        <main class="container-fluid p-4 p-lg-5">{{ $slot }}</main>
    </section>
</div>
</body>
</html>