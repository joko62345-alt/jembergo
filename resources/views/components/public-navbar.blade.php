<nav class="navbar navbar-expand-lg jg-navbar">
    <div class="d-flex align-items-center justify-content-between w-100 px-3">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ route('assets.logo') }}" class="jg-logo jg-logo-navbar" alt="JemberGo">
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-label="Buka menu">
            <i class="bi bi-list fs-3 text-dark"></i>
        </button>

        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav mx-auto gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#tentang">
                        Tentang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('destinations.*') ? 'active' : '' }}" href="{{ route('destinations.index') }}">
                        Destinasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}">
                        Artikel
                    </a>
                </li>

                @if (session('jg_role'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> Akun Saya
                        </a>
                        <ul class="dropdown-menu border-0 shadow-lg rounded-3 mt-2 p-2" style="min-width: 220px;">
                            @if (session('jg_role') === 'CUSTOMER')
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 px-3 d-flex align-items-center gap-2" href="{{ route('customer.profile') }}">
                                        <i class="bi bi-person text-orange"></i>
                                        <span>Profil Saya</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 px-3 d-flex align-items-center gap-2" href="{{ route('customer.tickets') }}">
                                        <i class="bi bi-ticket-perforated text-orange"></i>
                                        <span>Tiket Saya</span>
                                    </a>
                                </li>
                            @elseif (session('jg_role') === 'ADMIN_PARIWISATA' || session('jg_role') === 'SUPER_ADMIN')
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 px-3 d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer2 text-orange"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item rounded-2 py-2 px-3 d-flex align-items-center gap-2 text-danger w-100 border-0 bg-transparent" type="submit">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                @if (session('jg_role'))
                    <span class="d-none d-md-inline text-muted small me-2">
                        Halo, <strong class="text-dark">{{ session('jg_user_name', 'User') }}</strong>
                    </span>
                @else
                    <a href="{{ route('login') }}" class="btn jg-login-button">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-jg-primary btn-sm px-4">
                        Daftar
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>
