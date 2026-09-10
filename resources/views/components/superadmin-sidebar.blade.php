<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    .superadmin-shell { min-height: 100vh; background: #f6fafb; }
    .superadmin-sidebar { position: fixed; inset: 0 auto 0 0; z-index: 1030; width: 250px; padding: 1.5rem; background: #263238; }
    .superadmin-sidebar .nav-link { color: #b8c8cb; border-radius: .75rem; padding: .75rem 1rem; }
    .superadmin-sidebar .nav-link:hover { color: #263238; background: #ffc22f; }
    .superadmin-main, body > main.container { min-height: 100vh; margin-left: 250px; max-width: calc(100% - 250px); }
    @media (max-width: 767.98px) { .superadmin-sidebar { width: 72px; padding: .75rem; } .superadmin-sidebar .brand-label, .superadmin-sidebar .nav-label, .superadmin-sidebar .sidebar-caption { display: none; } .superadmin-sidebar .nav-link { text-align: center; padding: .8rem .4rem; } .superadmin-sidebar .nav-link i { margin: 0 !important; font-size: 1.15rem; } .superadmin-main, body > main.container { margin-left: 72px; max-width: calc(100% - 72px); } }
</style>
<aside class="superadmin-sidebar">
    <a href="{{ route('home') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5"><span class="brand-wordmark fs-5">Jember<span class="brand-accent">Go</span></span></a>
    <small class="sidebar-caption text-uppercase text-white-50 px-2">Super Admin</small>
    <nav class="nav flex-column gap-2 mt-2">
        <a href="{{ route('dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2 me-2"></i><span class="nav-label">Dashboard</span></a>
        <a href="{{ route('superadmin.destinations') }}" class="nav-link"><i class="bi bi-geo-alt me-2"></i><span class="nav-label">Destinasi</span></a>
        <a href="{{ route('superadmin.management') }}" class="nav-link"><i class="bi bi-sliders me-2"></i><span class="nav-label">Management</span></a>
        <a href="{{ route('superadmin.articles') }}" class="nav-link"><i class="bi bi-journal-text me-2"></i><span class="nav-label">Artikel</span></a>
        <a href="{{ route('superadmin.report') }}" class="nav-link"><i class="bi bi-bar-chart me-2"></i><span class="nav-label">Laporan</span></a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="mt-5">@csrf<button class="nav-link w-100 border-0 bg-transparent text-start"><i class="bi bi-box-arrow-left me-2"></i><span class="nav-label">Keluar</span></button></form>
</aside>