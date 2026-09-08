<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="JemberGo, panduan digital untuk menemukan dan memesan pengalaman wisata terbaik di Kabupaten Jember.">
    <title>JemberGo | Jelajahi Pesona Jember</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
<x-public-navbar />
{{--
<nav class="navbar navbar-expand-lg fixed-top jg-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#beranda"><span class="brand-mark"><i class="bi bi-compass"></i></span>Jember<span class="brand-accent">Go</span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-label="Buka menu"><i class="bi bi-list fs-3"></i></button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav mx-auto gap-lg-3"><li class="nav-item"><a class="nav-link active" href="#beranda">Beranda</a></li><li class="nav-item"><a class="nav-link" href="#destinasi">Destinasi</a></li><li class="nav-item"><a class="nav-link" href="{{ route('articles.index') }}">Artikel</a></li><li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>@if(session('jg_role') === 'CUSTOMER')<li class="nav-item"><a class="nav-link" href="{{ route('customer.profile') }}"><i class="bi bi-person me-1"></i>Profil</a></li><li class="nav-item"><a class="nav-link" href="{{ route('customer.tickets') }}"><i class="bi bi-ticket-perforated me-1"></i>Tiket saya</a></li>@endif</ul>
            <div class="d-flex align-items-center gap-2"> <a href="#destinasi" class="btn btn-jg-primary px-4">Jelajahi Wisata <i class="bi bi-arrow-up-right ms-1"></i></a>@if(session('jg_role') === 'CUSTOMER')<form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf<button class="btn btn-link text-dark p-1" title="Keluar" aria-label="Keluar"><i class="bi bi-box-arrow-right fs-5"></i></button></form>@elseif(session('jg_role'))<a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill">Dashboard</a>@else<a href="{{ route('login') }}" class="btn btn-link text-dark">Masuk</a><a href="{{ route('register') }}" class="btn btn-outline-secondary rounded-pill">Daftar</a>@endif</div>
        </div>
    </div>
</nav> --}}

<main>
    <section id="beranda" class="hero-section"><div class="container position-relative z-1"><div class="row align-items-center g-5">
        <div class="col-lg-6 hero-copy"><span class="eyebrow"><i class="bi bi-geo-alt-fill me-2"></i>Kabupaten Jember</span><h1 class="mt-3">Jelajahi pesona yang <em>lebih dekat.</em></h1><p class="lead">Temukan destinasi terbaik, rencanakan kunjunganmu, dan pesan tiket wisata Jember dalam satu langkah sederhana.</p><div class="d-flex flex-wrap gap-3 mt-4"><a href="#destinasi" class="btn btn-jg-primary btn-lg">Mulai Eksplorasi <i class="bi bi-arrow-right ms-2"></i></a><a href="#tentang" class="btn btn-link btn-lg text-decoration-none text-dark">Tentang JemberGo <i class="bi bi-play-circle ms-1"></i></a></div><div class="hero-note mt-5"><span class="note-line"></span><span>Perjalanan yang baik dimulai dari tempat yang tepat.</span></div></div>
        <div class="col-lg-6 hero-visual"><div class="hero-photo"><img src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=85" alt="Pemandangan alam untuk pengalaman wisata Jember"><div class="photo-caption"><span class="caption-dot"></span><span>Papuma, Jember</span><strong>01 / 05</strong></div></div><div class="floating-stamp"><i class="bi bi-stars"></i><span>Jember<br>itu indah</span></div></div>
    </div></div><div class="hero-rip"></div></section>

    <section class="intro-strip"><div class="container"><div class="row align-items-center g-4"><div class="col-lg-5"><span class="eyebrow text-orange">Jember, untuk semua</span><h2 class="mt-3">Satu tempat untuk<br><em>semua rencana.</em></h2></div><div class="col-lg-7"><p class="intro-text">Dari pantai yang tenang hingga perbukitan yang menantang, JemberGo membantu kamu menemukan sisi Jember yang paling cocok untuk cerita perjalananmu.</p><div class="stat-row"><div><strong>50+</strong><span>Destinasi</span></div><div><strong>24/7</strong><span>Inspirasi</span></div><div><strong>100%</strong><span>Jember</span></div></div></div></div></div></section>

    <section id="destinasi" class="section-pad destinations-section"><div class="container"><div class="section-heading d-flex justify-content-between align-items-end mb-5"><div><span class="eyebrow text-orange">Temukan tempat baru</span><h2 class="mt-3">Destinasi pilihan<br><em>untukmu.</em></h2></div><a href="{{ route('destinations.index') }}" class="text-dark text-decoration-none fw-semibold d-none d-md-block">Lihat semua <i class="bi bi-arrow-up-right ms-1"></i></a></div><div class="row g-4">
        @forelse ($destinations as $destination)
            @php($image = $destination->foto_utama)
            @php($fallback = 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80')
            <div class="col-md-6 col-lg-4"><article class="destination-card"><div class="destination-image"><img src="{{ $image ?: $fallback }}" onerror="this.onerror=null;this.src='{{ $fallback }}';" alt="{{ $destination->nama_wisata }}"><span class="category-pill">{{ $destination->kategori }}</span><button class="save-button" aria-label="Simpan destinasi"><i class="bi bi-bookmark"></i></button></div><div class="destination-body"><div class="d-flex justify-content-between gap-3"><h3>{{ $destination->nama_wisata }}</h3><span class="rating"><i class="bi bi-star-fill"></i> 4.8</span></div><p><i class="bi bi-geo-alt me-1"></i>{{ $destination->alamat }}</p><div class="d-flex justify-content-between align-items-center mt-3"><span class="price">Mulai <strong>Rp {{ number_format($destination->jenisTiket->min('harga') ?? 0, 0, ',', '.') }}</strong></span><a href="{{ route('destinations.show', $destination->id_destinasi) }}" class="card-arrow" aria-label="Lihat detail {{ $destination->nama_wisata }}"><i class="bi bi-arrow-up-right"></i></a></div></div></article></div>
        @empty
            <div class="col-12"><div class="empty-state">Destinasi sedang disiapkan. Segera kembali untuk menemukan tempat favoritmu.</div></div>
        @endforelse
    </div></div></section>

    <section id="tentang" class="category-section section-pad"><div class="container"><div class="row align-items-center g-5"><div class="col-lg-5"><span class="eyebrow text-orange">Pilih suasanamu</span><h2 class="mt-3">Jember punya<br><em>tiga wajah.</em></h2><p class="mt-4 text-muted">Temukan wisata alam, bahari, dan buatan sesuai rencana perjalananmu.</p></div><div class="col-lg-7"><div class="category-grid">@foreach ([['bi-tree','Alam','Air Terjun Tancak & Kali Jompo'],['bi-water','Bahari','Papuma & Teluk Love'],['bi-building','Buatan','Taman Botani']] as $category)<a href="#destinasi" class="category-item"><i class="bi {{ $category[0] }}"></i><strong>{{ $category[1] }}</strong><small>{{ $category[2] }}</small><span><i class="bi bi-arrow-up-right"></i></span></a>@endforeach</div></div></div></div></section>

    <section id="artikel" class="section-pad article-section"><div class="container"><div class="section-heading d-flex justify-content-between align-items-end mb-5"><div><span class="eyebrow text-orange">Catatan perjalanan</span><h2 class="mt-3">Cerita dari<br><em>tanah Jember.</em></h2></div><a href="{{ route('articles.index') }}" class="text-dark text-decoration-none fw-semibold">Lihat semua <i class="bi bi-arrow-up-right ms-1"></i></a></div><div class="row g-4">@forelse ($articles as $article)<div class="col-md-4"><article class="article-card"><div class="article-image"><img src="{{ $article->gambar && str_starts_with($article->gambar, 'http') ? $article->gambar : 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $article->judul }}"></div><div class="pt-3"><small>{{ optional($article->tanggal_publikasi)->translatedFormat('d F Y') }}</small><h3>{{ $article->judul }}</h3><a href="{{ route('articles.show', $article->id_artikel) }}" class="read-link">Baca selengkapnya <i class="bi bi-arrow-up-right"></i></a></div></article></div>@empty<div class="col-12"><div class="empty-state">Artikel perjalanan akan segera hadir.</div></div>@endforelse</div></div></section>

    <section class="cta-section"><div class="container"><div class="cta-box"><div><span class="eyebrow">Waktunya berangkat</span><h2 class="mt-2">Siap menjelajahi<br><em>Jember?</em></h2></div><a href="#destinasi" class="btn btn-light btn-lg">Mulai eksplorasi <i class="bi bi-arrow-up-right ms-2"></i></a></div></div></section>
</main>

<footer class="footer"><div class="container"><div class="row g-4 justify-content-between"><div class="col-lg-4"><a class="navbar-brand text-white" href="#beranda"><span class="brand-mark brand-mark-light"><i class="bi bi-compass"></i></span> Jember<span class="brand-accent">Go</span></a><p class="mt-3">Teman perjalananmu untuk menemukan pesona Kabupaten Jember.</p></div><div class="col-6 col-lg-2"><strong>Jelajahi</strong><a href="#destinasi">Destinasi</a><a href="#artikel">Artikel</a></div><div class="col-6 col-lg-2"><strong>Ikuti kami</strong><a href="#"><i class="bi bi-instagram me-2"></i>Instagram</a><a href="#"><i class="bi bi-facebook me-2"></i>Facebook</a></div></div><div class="footer-bottom mt-5"><span>© {{ date('Y') }} JemberGo</span><span>Dibuat untuk Jember.</span></div></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>