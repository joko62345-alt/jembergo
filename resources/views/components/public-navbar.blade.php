<nav class="navbar navbar-expand-lg fixed-top jg-navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}#beranda">
            <span class="brand-mark"><i class="bi bi-compass"></i></span>
            Jember<span class="brand-accent">Go</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-label="Buka menu">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav mx-auto gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#beranda">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('destinations.index') }}">Destinasi</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('articles.index') }}">Artikel</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#tentang">Tentang</a></li>
                @if (session('jg_role') === 'CUSTOMER')
                    <li class="nav-item"><a class="nav-link" href="{{ route('customer.profile') }}"><i class="bi bi-person me-1"></i>Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('customer.tickets') }}"><i class="bi bi-ticket-perforated me-1"></i>Tiket Saya</a></li>
                @endif
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('destinations.index') }}" class="btn btn-jg-primary px-4">Jelajahi Wisata <i class="bi bi-arrow-up-right ms-1"></i></a>
                @if (session('jg_role') === 'CUSTOMER')
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf<button class="btn btn-link text-dark p-1" title="Keluar" aria-label="Keluar"><i class="bi bi-box-arrow-right fs-5"></i></button></form>
                @elseif (session('jg_role'))
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-link text-dark">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary rounded-pill">Daftar</a>
                @endif
            </div>
        </div>
    </div>
</nav>
