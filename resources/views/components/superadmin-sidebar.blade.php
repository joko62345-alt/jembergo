<aside id="superadmin-sidebar" class="superadmin-sidebar">
    <a href="{{ route('home') }}" class="superadmin-brand">
        <span class="superadmin-brand-mark">J</span>
        <span class="superadmin-brand-name">Jember<span>Go</span></span>
    </a>
    <small class="superadmin-caption">{{ session('jg_role') === 'SUPER_ADMIN' ? 'Workspace' : 'Menu utama' }}</small>
    <nav class="superadmin-nav" aria-label="Navigasi Super Admin">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2" aria-hidden="true"></i><span>Dashboard</span></a>
        @if(session('jg_role') === 'SUPER_ADMIN')
            <a href="{{ route('superadmin.destinations') }}" class="nav-link {{ request()->routeIs('superadmin.destinations*') ? 'active' : '' }}"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Destinasi wisata</span></a>
            @php($managementOpen = request()->routeIs('superadmin.management*'))
            <div class="sidebar-dropdown {{ $managementOpen ? 'is-open' : '' }}">
                <button type="button" class="nav-link sidebar-dropdown-toggle {{ $managementOpen ? 'active' : '' }}" aria-expanded="{{ $managementOpen ? 'true' : 'false' }}" aria-controls="management-submenu">
                    <i class="bi bi-people" aria-hidden="true"></i><span>Manajemen akun</span><i class="bi bi-chevron-down sidebar-chevron" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="management-submenu">
                    <a href="{{ route('superadmin.management.admins') }}" class="nav-link {{ request()->routeIs('superadmin.management.admins') ? 'active' : '' }}"><i class="bi bi-person" aria-hidden="true"></i><span>Admin Pariwisata</span></a>
                    <a href="{{ route('superadmin.management.customers') }}" class="nav-link {{ request()->routeIs('superadmin.management.customers') ? 'active' : '' }}"><i class="bi bi-person" aria-hidden="true"></i><span>Customer</span></a>
                </div>
            </div>
            <a href="{{ route('superadmin.articles') }}" class="nav-link {{ request()->routeIs('superadmin.articles*') ? 'active' : '' }}"><i class="bi bi-journal-text" aria-hidden="true"></i><span>Artikel</span></a>
            <a href="{{ route('superadmin.report') }}" class="nav-link {{ request()->routeIs('superadmin.report*') ? 'active' : '' }}"><i class="bi bi-bar-chart" aria-hidden="true"></i><span>Laporan</span></a>
        @elseif(session('jg_role') === 'ADMIN_PARIWISATA')
            <a href="{{ route('admin.verification') }}" class="nav-link {{ request()->routeIs('admin.verification') ? 'active' : '' }}"><i class="bi bi-qr-code-scan" aria-hidden="true"></i><span>Verifikasi</span></a>
            <a href="{{ route('admin.bookings') }}" class="nav-link {{ request()->routeIs('admin.bookings') ? 'active' : '' }}"><i class="bi bi-calendar-check" aria-hidden="true"></i><span>Pemesanan destinasi</span></a>
            <a href="{{ route('admin.verification.history') }}" class="nav-link {{ request()->routeIs('admin.verification.history') ? 'active' : '' }}"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Riwayat verifikasi</span></a>
        @else
            <a href="{{ route('destinations.index') }}" class="nav-link"><i class="bi bi-compass" aria-hidden="true"></i><span>Destinasi</span></a>
            <a href="{{ route('customer.orders') }}" class="nav-link"><i class="bi bi-ticket-perforated" aria-hidden="true"></i><span>Pesanan saya</span></a>
            <a href="{{ route('customer.profile') }}" class="nav-link"><i class="bi bi-person" aria-hidden="true"></i><span>Profil</span></a>
        @endif
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="superadmin-logout">@csrf<button class="nav-link" type="submit"><i class="bi bi-box-arrow-left" aria-hidden="true"></i><span>Keluar</span></button></form>
</aside>
<script>
    document.querySelectorAll('.sidebar-dropdown-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const dropdown = toggle.closest('.sidebar-dropdown');
            const open = dropdown.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open);
        });
    });
</script>