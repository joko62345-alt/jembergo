@extends('layouts.app')

@section('title', 'JemberGo - Informasi & Layanan Pariwisata Kabupaten Jember')

@section('content')
@include('components.public-navbar')

<main class="landing-main">
    <!-- Hero Section -->
    <section id="beranda" class="hero-section" style="background-image: url('{{ route('assets.background') }}');">
        <div class="container position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-copy">
                    <span class="eyebrow"><i class="bi bi-geo-alt-fill"></i> Kabupaten Jember</span>
                    <h1 class="mt-3">Jelajahi pesona yang <em>lebih dekat.</em></h1>
                    <p class="lead">Temukan destinasi terbaik, rencanakan kunjunganmu, dan pesan tiket wisata Jember dalam satu langkah sederhana.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ route('destinations.index') }}" class="btn btn-jg-primary btn-lg">Mulai Eksplorasi <i class="bi bi-arrow-right"></i></a>
                        <a href="#tentang" class="btn btn-outline-jg btn-lg">Tentang JemberGo</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Section -->
    <section id="tentang" class="section-pad" style="background: linear-gradient(135deg, var(--jg-sky) 0%, var(--jg-white) 100%);">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <span class="eyebrow text-orange">Tentang Kami</span>
                    <h2 class="mt-3">Apa itu <em>JemberGo?</em></h2>
                    <p class="mt-3">JemberGo adalah platform informasi dan layanan pariwisata resmi Kabupaten Jember yang membantu wisatawan menemukan, merencanakan, dan memesan tiket wisata dengan mudah.</p>
                    <p>Kami menghubungkan Anda dengan destinasi terbaik di Jember, mulai dari wisata alam, bahari, hingga buatan. Dengan sistem pemesanan yang terintegrasi, perjalanan Anda menjadi lebih terencana dan menyenangkan.</p>
                    <div class="mt-4">
                        <a href="{{ route('destinations.index') }}" class="btn btn-jg-primary">
                            Jelajahi Destinasi <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="category-grid">
                        <a href="{{ route('destinations.index', ['kategori' => 'Alam']) }}" class="category-item">
                            <span class="category-icon"><i class="bi bi-tree-fill"></i></span>
                            <strong>Wisata Alam</strong>
                            <small>Air Terjun Tancak, Kali Jompo, dan perbukitan hijau</small>
                            <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </a>
                        <a href="{{ route('destinations.index', ['kategori' => 'Bahari']) }}" class="category-item">
                            <span class="category-icon"><i class="bi bi-water"></i></span>
                            <strong>Wisata Bahari</strong>
                            <small>Pantai Papuma, Teluk Love, dan keindahan laut selatan</small>
                            <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </a>
                        <a href="{{ route('destinations.index', ['kategori' => 'Buatan']) }}" class="category-item">
                            <span class="category-icon"><i class="bi bi-buildings-fill"></i></span>
                            <strong>Wisata Buatan</strong>
                            <small>Taman Botani, kebun raya, dan wisata edukasi</small>
                            <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Destinations Section -->
    <section id="destinasi" class="section-pad destinations-section">
        <div class="container">
            <div class="section-heading d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="eyebrow text-orange">Temukan tempat baru</span>
                    <h2 class="mt-2">Destinasi pilihan <em>untukmu.</em></h2>
                </div>
                <a href="{{ route('destinations.index') }}" class="text-dark text-decoration-none fw-semibold d-none d-md-block">Lihat semua <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            
            <div class="row g-4">
                @forelse ($destinations as $destination)
                    @php
                        $image = $destination->foto_utama ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';
                        $minPrice = $destination->jenisTiket && $destination->jenisTiket->count() > 0 ? $destination->jenisTiket->min('harga') : 0;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <article class="destination-card h-100">
                            <div class="destination-image">
                                <img src="{{ $image }}" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';" alt="{{ $destination->nama_wisata }}">
                                <span class="category-pill">{{ $destination->kategori ?? 'Umum' }}</span>
                            </div>
                            <div class="destination-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="mb-0" style="font-size: 1.1rem;">{{ $destination->nama_wisata }}</h3>
                                </div>
                                <p class="mb-3" style="flex-grow: 1;">
                                    <i class="bi bi-geo-alt-fill text-orange me-1"></i>
                                    {{ Str::limit($destination->alamat, 60) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center" style="padding-top: 1rem; border-top: 1px solid #E2E8F0;">
                                    <span class="price">Mulai <strong style="color: #F58B05;">Rp {{ number_format($minPrice, 0, ',', '.') }}</strong></span>
                                    <a href="{{ route('destinations.show', $destination->id_destinasi) }}" 
                                       style="display: inline-flex; align-items: center; gap: 0.3rem; background: #F58B05; color: #fff; padding: 0.45rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(245, 139, 5, 0.25);"
                                       onmouseover="this.style.background='#e07b00'; this.style.transform='translateY(-2px)';"
                                       onmouseout="this.style.background='#F58B05'; this.style.transform='translateY(0)';">
                                        Detail <i class="bi bi-arrow-right" style="font-size: 0.8rem;"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state py-5 text-center">
                            <i class="bi bi-map fs-1 text-muted mb-3 d-block"></i>
                            <h4>Destinasi sedang disiapkan</h4>
                            <p class="text-muted">Segera kembali untuk menemukan tempat favoritmu di Jember.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <section id="artikel" class="section-pad">
        <div class="container">
            <div class="section-heading d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="eyebrow text-orange">Catatan perjalanan</span>
                    <h2 class="mt-2">Cerita dari <em>tanah Jember.</em></h2>
                </div>
                <a href="{{ route('articles.index') }}" class="text-dark text-decoration-none fw-semibold d-none d-md-block">Lihat semua <i class="bi bi-arrow-right ms-1"></i></a>
            </div>

            <div class="row g-4">
                @forelse ($articles as $article)
                    <div class="col-md-4">
                        <article class="article-card h-100">
                            <div class="article-image">
                                <img src="{{ $article->gambar ?: 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=800&q=80' }}" 
                                     alt="{{ $article->judul }}">
                            </div>
                            <div class="card-body">
                                <small class="text-muted">{{ optional($article->tanggal_publikasi)->translatedFormat('d F Y') }}</small>
                                <h3 style="font-size: 1.05rem; margin: 0.5rem 0 0.75rem;">{{ $article->judul }}</h3>
                                <a href="{{ route('articles.show', $article->id_artikel) }}" class="read-link">
                                    Baca selengkapnya <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state py-5 text-center">
                            <i class="bi bi-journal-text fs-1 text-muted mb-3 d-block"></i>
                            <h4>Artikel perjalanan akan segera hadir</h4>
                            <p class="text-muted">Nantikan cerita dan tips wisata dari Jember.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section-pad">
        <div class="container">
            <div class="cta-box">
                <div>
                    <span class="eyebrow" style="background: rgba(255,255,255,0.1); color: var(--jg-yellow);">Waktunya berangkat</span>
                    <h2 class="mt-2 text-white">Siap menjelajahi <em>Jember?</em></h2>
                    <p class="text-white-50 mb-0 mt-2">Dapatkan pengalaman wisata terbaik dengan pemesanan yang mudah dan aman.</p>
                </div>
                <a href="{{ route('destinations.index') }}" class="btn btn-jg-primary btn-lg flex-shrink-0">Mulai Eksplorasi <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                    <span class="brand-wordmark">Jember<span class="brand-accent">Go</span></span>
                </a>
                <p class="mt-3">Platform informasi dan layanan pariwisata resmi Kabupaten Jember. Temukan, rencanakan, dan pesan tiket wisata dengan mudah.</p>
            </div>
            <div class="col-lg-4">
                <strong>Jelajahi</strong>
                <a href="{{ route('destinations.index') }}">Destinasi Wisata</a>
                <a href="{{ route('articles.index') }}">Artikel & Tips</a>
                <a href="#tentang">Tentang JemberGo</a>
            </div>
            <div class="col-lg-4">
                <strong>Ikuti Kami</strong>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="fs-4"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="fs-4"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="fs-4"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="fs-4"><i class="bi bi-youtube"></i></a>
                </div>
                <p class="mt-3 small">Dapatkan update destinasi terbaru dan promo menarik.</p>
            </div>
        </div>
        <div class="footer-bottom mt-4">
            <span>© {{ date('Y') }} JemberGo. Hak Cipta Dilindungi.</span>
        </div>
    </div>
</footer>
@endsection