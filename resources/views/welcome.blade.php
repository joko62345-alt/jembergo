@extends('layouts.app')

@section('title', 'JemberGo - Informasi & Layanan Pariwisata Kabupaten Jember')

@section('content')
@include('components.public-navbar')

<main class="landing-main">
    <!-- Hero Section -->
    <section id="beranda" class="hero-section hero-carousel-section">
        <div id="homeHeroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6500">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
            </div>
            <div class="carousel-inner">
                @php
                    $heroSlides = [
                    ['image' => route('assets.hero2'), 'icon' => 'bi-sunrise-fill', 'eyebrow' => 'Panorama Jember', 'title' => 'Pagi yang tak ingin kau lewatkan.', 'copy' => 'Hirup udara pegunungan, nikmati hamparan hijau, dan biarkan Jember menyambut langkahmu.'],
                    ['image' => route('assets.hero3'), 'icon' => 'bi-tree-fill', 'eyebrow' => 'Petualangan Alam', 'title' => 'Temukan sisi luar Jember.', 'copy' => 'Air terjun, hutan, dan jalan baru siap mengantarmu pada cerita yang berbeda.'],
                    ['image' => route('assets.hero4'), 'icon' => 'bi-water', 'eyebrow' => 'Ruang Untuk Berhenti', 'title' => 'Berhenti sejenak, rasakan lebih banyak.', 'copy' => 'Temukan ruang tenang di antara bukit dan danau untuk bernapas lebih lega.'],
                    ['image' => route('assets.hero6'), 'icon' => 'bi-compass-fill', 'eyebrow' => 'Jember Penuh Cerita', 'title' => 'Setiap perjalanan punya cerita.', 'copy' => 'Jelajahi alam, bahari, dan pengalaman lokal yang membuat Jember terasa dekat.'],
                    ['image' => route('assets.background'), 'icon' => 'bi-stars', 'eyebrow' => 'The Real Hidden Paradise', 'title' => 'Jember, kejutan indah di setiap langkah.', 'copy' => 'Biarkan pesona Jember membawamu menemukan tempat-tempat yang belum pernah kamu bayangkan.'],
                    ];
                @endphp
                @foreach($heroSlides as $index => $slide)
                    <div class="carousel-item hero-carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $slide['image'] }}" class="hero-carousel-image" alt="{{ $slide['title'] }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                        <div class="hero-carousel-overlay"></div>
                        <div class="container position-relative z-1 h-100">
                            <div class="row align-items-center h-100">
                                <div class="col-lg-7 hero-copy">
                                    <span class="eyebrow"><i class="bi {{ $slide['icon'] }}"></i> {{ $slide['eyebrow'] }}</span>
                                    <h1 class="hero-title mt-3">“{{ $slide['title'] }}”</h1>
                                    <p class="lead">{{ $slide['copy'] }}</p>
                                    <div class="d-flex flex-wrap gap-3 mt-4">
                                        <a href="{{ route('destinations.index') }}" class="btn btn-jg-primary btn-lg">Mulai Eksplorasi <i class="bi bi-arrow-right"></i></a>
                                        <a href="#tentang" class="btn btn-outline-light btn-lg">Tentang JemberGo</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev hero-carousel-control" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="prev" aria-label="Slide sebelumnya"><span class="carousel-control-prev-icon" aria-hidden="true"></span></button>
            <button class="carousel-control-next hero-carousel-control" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="next" aria-label="Slide berikutnya"><span class="carousel-control-next-icon" aria-hidden="true"></span></button>
        </div>
    </section>

    <!-- Tentang Section -->
    <section id="tentang" class="section-pad" data-reveal-section style="background: linear-gradient(135deg, var(--jg-sky) 0%, var(--jg-white) 100%);">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-5" data-reveal-item>
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
                <div class="col-lg-7" data-reveal-item style="--reveal-delay: 120ms;">
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
    <section id="destinasi" class="section-pad destinations-section" data-reveal-section>
        <div class="container">
            <div class="section-heading mb-4">
                <h2 class="mt-2">Destinasi Pilihan di <em>Kab Jember</em></h2>
            </div>

            <div class="row g-3 g-lg-4 destination-gallery-grid">
                @forelse ($destinations as $destination)
                    @php
                        $image = $destination->foto_utama ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4" data-reveal-item style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms;">
                        <a href="{{ route('destinations.show', $destination->id_destinasi) }}" class="destination-gallery-card" aria-label="Lihat {{ $destination->nama_wisata }}">
                            <img src="{{ $image }}" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';" alt="{{ $destination->nama_wisata }}" loading="lazy">
                            <span class="destination-gallery-overlay"></span>
                            <span class="destination-gallery-name">{{ $destination->nama_wisata }}</span>
                        </a>
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
        <style>
            #destinasi .destination-gallery-card {
                position: relative;
                display: block;
                overflow: hidden;
                aspect-ratio: 4 / 3;
                border-radius: 10px;
                background: #dce6ed;
                cursor: pointer;
                isolation: isolate;
            }
            #destinasi .destination-gallery-card img,
            #destinasi .destination-gallery-overlay {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
            }
            #destinasi .destination-gallery-card img {
                object-fit: contain;
                background: #e8eef2;
                animation: destinationGalleryZoom 10s ease-in-out infinite alternate;
            }
            @keyframes destinationGalleryZoom {
                from { transform: scale(1); }
                to { transform: scale(1.06); }
            }
            #destinasi .destination-gallery-overlay {
                z-index: 1;
                background: linear-gradient(180deg, transparent 45%, rgba(8, 25, 42, .82) 100%);
                transition: background 250ms ease;
            }
            #destinasi .destination-gallery-name {
                position: absolute;
                z-index: 2;
                right: 1rem;
                bottom: 1rem;
                left: 1rem;
                color: #fff;
                font-size: .98rem;
                font-weight: 700;
                line-height: 1.25;
                text-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            }
            #destinasi .destination-gallery-card:hover .destination-gallery-overlay { background: linear-gradient(180deg, rgba(8, 25, 42, .08) 25%, rgba(8, 25, 42, .9) 100%); }
            @media (prefers-reduced-motion: reduce) {
                #destinasi .destination-gallery-card img { animation: none; }
            }
        </style>
    </section>

    <!-- Articles Section -->
    <section id="artikel" class="section-pad" data-reveal-section>
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
                    <div class="col-md-4" data-reveal-item style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms;">
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
    <section class="section-pad" data-reveal-section>
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
<script>
    (() => {
        const revealItems = document.querySelectorAll('[data-reveal-section], [data-reveal-item]');
        if (!('IntersectionObserver' in window)) {
            revealItems.forEach((item) => item.classList.add('is-revealed'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => entry.target.classList.toggle('is-revealed', entry.isIntersecting));
        }, { threshold: 0.14, rootMargin: '0px 0px -8% 0px' });

        revealItems.forEach((item) => observer.observe(item));
    })();
</script>
@endsection