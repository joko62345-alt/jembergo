<aside id="superadmin-sidebar" class="superadmin-sidebar">
    <a href="{{ route('home') }}" class="superadmin-brand">
        <span class="superadmin-brand-name">Jember<span>Go</span></span>
    </a>
    <button type="button" class="sidebar-collapse-toggle" data-sidebar-collapse
        aria-label="Ciutkan menu navigasi" aria-controls="superadmin-sidebar" aria-expanded="true"
        title="Ciutkan menu navigasi"><i class="bi bi-layout-sidebar-inset"
            aria-hidden="true"></i></button>
    <small
        class="superadmin-caption">{{ session('jg_role') === 'SUPER_ADMIN' ? 'Workspace' : 'Menu utama' }}</small>
    <nav class="superadmin-nav" aria-label="Navigasi Super Admin">
        <a href="{{ route('dashboard') }}"
            class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            title="Dashboard"><i class="bi bi-grid-1x2"
                aria-hidden="true"></i><span>Dashboard</span></a>
        @if (session('jg_role') === 'SUPER_ADMIN')
            <a href="{{ route('superadmin.destinations') }}"
                class="nav-link {{ request()->routeIs('superadmin.destinations*') ? 'active' : '' }}"
                title="Destinasi wisata"><i class="bi bi-geo-alt"
                    aria-hidden="true"></i><span>Destinasi wisata</span></a>
            @php($managementOpen = request()->routeIs('superadmin.management*'))
            <div class="sidebar-dropdown {{ $managementOpen ? 'is-open' : '' }}">
                <button type="button"
                    class="nav-link sidebar-dropdown-toggle {{ $managementOpen ? 'active' : '' }}"
                    aria-expanded="{{ $managementOpen ? 'true' : 'false' }}"
                    aria-controls="management-submenu" title="Manajemen akun">
                    <i class="bi bi-people" aria-hidden="true"></i><span>Manajemen akun</span><i
                        class="bi bi-chevron-down sidebar-chevron" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="management-submenu">
                    <a href="{{ route('superadmin.management.admins') }}"
                        class="nav-link {{ request()->routeIs('superadmin.management.admins') ? 'active' : '' }}"
                        title="Admin Pariwisata"><i class="bi bi-person"
                            aria-hidden="true"></i><span>Admin Pariwisata</span></a>
                    <a href="{{ route('superadmin.management.customers') }}"
                        class="nav-link {{ request()->routeIs('superadmin.management.customers') ? 'active' : '' }}"
                        title="Customer"><i class="bi bi-person"
                            aria-hidden="true"></i><span>Customer</span></a>
                </div>
            </div>
            <a href="{{ route('superadmin.articles') }}"
                class="nav-link {{ request()->routeIs('superadmin.articles*') ? 'active' : '' }}"
                title="Artikel"><i class="bi bi-journal-text"
                    aria-hidden="true"></i><span>Artikel</span></a>
            <a href="{{ route('superadmin.report') }}"
                class="nav-link {{ request()->routeIs('superadmin.report*') ? 'active' : '' }}"
                title="Laporan"><i class="bi bi-bar-chart"
                    aria-hidden="true"></i><span>Laporan</span></a>
        @elseif(session('jg_role') === 'ADMIN_PARIWISATA')
            <a href="{{ route('admin.verification') }}"
                class="nav-link {{ request()->routeIs('admin.verification') ? 'active' : '' }}"
                title="Verifikasi"><i class="bi bi-qr-code-scan"
                    aria-hidden="true"></i><span>Verifikasi</span></a>
            <a href="{{ route('admin.bookings') }}"
                class="nav-link {{ request()->routeIs('admin.bookings') ? 'active' : '' }}"
                title="Pemesanan destinasi"><i class="bi bi-calendar-check"
                    aria-hidden="true"></i><span>Pemesanan destinasi</span></a>
        @else
            <a href="{{ route('destinations.index') }}" class="nav-link" title="Destinasi"><i
                    class="bi bi-compass" aria-hidden="true"></i><span>Destinasi</span></a>
            <a href="{{ route('customer.orders') }}" class="nav-link" title="Pesanan saya"><i
                    class="bi bi-ticket-perforated" aria-hidden="true"></i><span>Pesanan
                    saya</span></a>
            <a href="{{ route('customer.profile') }}" class="nav-link" title="Profil"><i
                    class="bi bi-person" aria-hidden="true"></i><span>Profil</span></a>
        @endif
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="superadmin-logout">@csrf<button
            class="nav-link" type="submit" title="Keluar"><i class="bi bi-box-arrow-left"
                aria-hidden="true"></i><span>Keluar</span></button></form>
</aside>
<script>
    const sidebarCollapseToggle = document.querySelector('[data-sidebar-collapse]');
    const sidebar = document.getElementById('superadmin-sidebar');
    const sidebarApp = sidebar?.parentElement;
    if (sidebarCollapseToggle && sidebar && localStorage.getItem('superadmin-sidebar-collapsed') ===
        'true') {
        sidebar.classList.add('is-collapsed');
        sidebarApp?.classList.add('is-sidebar-collapsed');
        sidebarCollapseToggle.setAttribute('aria-expanded', 'false');
        sidebarCollapseToggle.setAttribute('aria-label', 'Lebarkan menu navigasi');
        sidebarCollapseToggle.setAttribute('title', 'Lebarkan menu navigasi');
        sidebarCollapseToggle.innerHTML = '<i class="bi bi-layout-sidebar" aria-hidden="true"></i>';
    }
    sidebarCollapseToggle?.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('is-collapsed');
        sidebarApp?.classList.toggle('is-sidebar-collapsed', collapsed);
        localStorage.setItem('superadmin-sidebar-collapsed', collapsed);
        sidebarCollapseToggle.setAttribute('aria-expanded', String(!collapsed));
        sidebarCollapseToggle.setAttribute('aria-label', collapsed ?
            'Lebarkan menu navigasi' : 'Ciutkan menu navigasi');
        sidebarCollapseToggle.setAttribute('title', collapsed ? 'Lebarkan menu navigasi' :
            'Ciutkan menu navigasi');
        sidebarCollapseToggle.innerHTML =
            `<i class="bi bi-layout-sidebar${collapsed ? '' : '-inset'}" aria-hidden="true"></i>`;
    });

    document.querySelectorAll('.sidebar-dropdown-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const dropdown = toggle.closest('.sidebar-dropdown');
            const open = dropdown.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open);
        });
    });
</script>
