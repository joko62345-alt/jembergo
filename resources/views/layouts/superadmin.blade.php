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
<body class="superadmin-body" data-theme="light">
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
                <div class="d-flex align-items-center gap-2">
                    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" title="Aktifkan mode gelap">
                        <i class="bi bi-moon-stars" aria-hidden="true"></i><span>Mode gelap</span>
                    </button>
                    <div class="user-chip">
                    <span class="user-avatar" aria-hidden="true">{{ strtoupper(substr(session('jg_user_name', 'SA'), 0, 1)) }}</span>
                    <span class="d-none d-sm-block text-start"><strong>{{ session('jg_user_name', 'Pengguna') }}</strong><small>{{ str_replace('_', ' ', session('jg_role', 'USER')) }}</small></span>
                    </div>
                </div>
            </header>
            <main class="superadmin-content">
                @yield('content')
            </main>
        </div>
    </div>
    <div class="sidebar-scrim" data-sidebar-toggle></div>
    <script>
        (() => {
            const body = document.body;
            const toggle = document.querySelector('[data-theme-toggle]');
            const savedTheme = localStorage.getItem('superadmin-theme') || 'light';

            const setTheme = (theme) => {
                const isDark = theme === 'dark';
                body.dataset.theme = theme;
                toggle?.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
                toggle?.setAttribute('title', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
                if (toggle) toggle.innerHTML = `<i class="bi bi-${isDark ? 'sun' : 'moon-stars'}" aria-hidden="true"></i><span>${isDark ? 'Mode terang' : 'Mode gelap'}</span>`;
            };

            setTheme(savedTheme);
            toggle?.addEventListener('click', () => {
                const theme = body.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('superadmin-theme', theme);
                setTheme(theme);
            });
        })();

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

        document.addEventListener('click', (event) => {
            const alertClose = event.target.closest('.alert-close');
            if (alertClose) {
                alertClose.closest('.alert')?.remove();
                return;
            }

            const toastClose = event.target.closest('[data-dismiss-toast]');
            if (toastClose) {
                toastClose.closest('.destination-toast')?.remove();
                return;
            }

            const dialogClose = event.target.closest('[data-close-dialog], [data-close-article-dialog]');
            if (dialogClose) {
                const dialog = dialogClose.closest('dialog');
                if (dialog?.open) dialog.close();
            }
        });

        const revealTargets = document.querySelectorAll('.superadmin-content > *, .superadmin-content .card, .superadmin-content .stat-panel, .superadmin-content .analytics-card, .superadmin-content .analytics-mini-card, .superadmin-content .admin-stat-card, .superadmin-content .admin-panel, .superadmin-content .destination-card, .superadmin-content .article-list-card, .superadmin-content .article-editor-card');
        revealTargets.forEach((element) => element.classList.add('scroll-reveal'));
        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -36px' });
            revealTargets.forEach((element) => revealObserver.observe(element));
        } else {
            revealTargets.forEach((element) => element.classList.add('is-visible'));
        }
    </script>
    @stack('scripts')
</body>
</html>
