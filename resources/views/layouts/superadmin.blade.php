<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | JemberGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/superadmin.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body class="superadmin-body">
    <div class="superadmin-app">
        @include('components.superadmin-sidebar')
        <div class="superadmin-main">
            <header class="superadmin-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="icon-button d-lg-none" type="button" data-sidebar-toggle aria-label="Buka menu navigasi" aria-controls="superadmin-sidebar" aria-expanded="false">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                    <div>
                        <span class="topbar-eyebrow">Ruang kerja JemberGo</span>
                        <span class="topbar-page">@yield('page_label', 'Super Admin')</span>
                    </div>
                </div>
                <div class="user-chip">
                    <span class="user-avatar" aria-hidden="true">{{ strtoupper(substr(session('jg_user_name', 'SA'), 0, 1)) }}</span>
                    <span class="d-none d-sm-block text-start"><strong>{{ session('jg_user_name', 'Pengguna') }}</strong><small>{{ str_replace('_', ' ', session('jg_role', 'USER')) }}</small></span>
                </div>
            </header>
            <main class="superadmin-content">
                @yield('content')
            </main>
        </div>
    </div>
    <div class="sidebar-scrim" data-sidebar-toggle></div>
    <script>
        document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const sidebar = document.getElementById('superadmin-sidebar');
                const open = sidebar.classList.toggle('is-open');
                document.body.classList.toggle('sidebar-open', open);
                document.querySelector('[aria-controls="superadmin-sidebar"]')?.setAttribute('aria-expanded', open);
            });
        });

        document.querySelectorAll('.alert:not([data-dismissible])').forEach((alert) => {
            alert.dataset.dismissible = 'true';
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'alert-close';
            close.setAttribute('aria-label', 'Tutup notifikasi');
            close.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
            close.addEventListener('click', () => alert.remove());
            alert.appendChild(close);
        });
    </script>
    @stack('scripts')
</body>
</html>
