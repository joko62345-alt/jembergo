@extends('layouts.app')

@section('title', 'JemberGo - Informasi & Layanan Pariwisata Kabupaten Jember')

@section('content')
    @include('components.public-navbar')

    <main class="landing-main">
        <!-- Hero Section -->
        <section id="beranda" class="hero-section hero-carousel-section">
            <div id="homeHeroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel"
                data-bs-interval="6500">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="0"
                        class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="1"
                        aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="2"
                        aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="3"
                        aria-label="Slide 4"></button>
                    <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="4"
                        aria-label="Slide 5"></button>
                </div>
                <div class="carousel-inner">
                    @php
                        $heroSlides = [
                            [
                                'image' => route('assets.background') . '?v=20260923-8',
                                'icon' => 'bi-compass-fill',
                                'eyebrow' => 'Pemandangan Indah',
                                'title' => 'Lihat lebih jauh, rasakan lebih banyak.',
                                'copy' =>
                                    'Biarkan cahaya, udara segar, dan lanskap yang luas membuka awal perjalanan yang baru.',
                            ],
                            [
                                'image' => route('assets.hero2') . '?v=20260923-7',
                                'icon' => 'bi-water',
                                'eyebrow' => 'Pesona Alam',
                                'title' => 'Biarkan keindahan alam mengubah suasana.',
                                'copy' =>
                                    'Temukan ruang untuk bernapas di antara warna hijau, langit, dan udara yang menenangkan.',
                            ],
                            [
                                'image' => route('assets.hero3') . '?v=20260923-7',
                                'icon' => 'bi-sunrise-fill',
                                'eyebrow' => 'Momen Perjalanan',
                                'title' => 'Temukan tenang di tempat yang indah.',
                                'copy' =>
                                    'Nikmati pemandangan, warna alam, dan momen sederhana yang terasa istimewa.',
                            ],
                            [
                                'image' => route('assets.hero4') . '?v=20260923-7',
                                'icon' => 'bi-tree-fill',
                                'eyebrow' => 'Ruang Hijau',
                                'title' => 'Jeda sejenak dari ramainya rutinitas.',
                                'copy' =>
                                    'Masuk lebih dekat ke alam dan biarkan suasana hijau menyegarkan pikiranmu.',
                            ],
                            [
                                'image' => route('assets.hero5') . '?v=20260923-7',
                                'icon' => 'bi-stars',
                                'eyebrow' => 'Pengalaman Wisata',
                                'title' => 'Buat setiap perjalanan terasa istimewa.',
                                'copy' =>
                                    'Jelajahi tempat baru, nikmati suasana, dan bawa pulang cerita yang ingin kamu ulangi.',
                            ],
                        ];
                    @endphp
                    @foreach ($heroSlides as $index => $slide)
                        <div
                            class="carousel-item hero-carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $slide['image'] }}" class="hero-carousel-image"
                                alt="{{ $slide['title'] }}"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                            <div class="hero-carousel-overlay"></div>
                            <div class="container position-relative z-1 h-100">
                                <div class="row align-items-center h-100">
                                    <div class="col-lg-7 hero-copy">
                                        <span class="eyebrow"><i class="bi {{ $slide['icon'] }}"></i>
                                            {{ $slide['eyebrow'] }}</span>
                                        <h1 class="hero-title mt-3">“{{ $slide['title'] }}”</h1>
                                        <p class="lead">{{ $slide['copy'] }}</p>
                                        <div class="hero-actions d-flex flex-wrap gap-3 mt-4">
                                            <a href="{{ route('destinations.index') }}"
                                                class="btn btn-jg-primary btn-lg">Mulai Eksplorasi <i
                                                    class="bi bi-arrow-right"></i></a>
                                            <a href="#tentang"
                                                class="btn btn-outline-light btn-lg">Tentang
                                                JemberGo</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev hero-carousel-control" type="button"
                    data-bs-target="#homeHeroCarousel" data-bs-slide="prev"
                    aria-label="Slide sebelumnya"><span class="carousel-control-prev-icon"
                        aria-hidden="true"></span></button>
                <button class="carousel-control-next hero-carousel-control" type="button"
                    data-bs-target="#homeHeroCarousel" data-bs-slide="next"
                    aria-label="Slide berikutnya"><span class="carousel-control-next-icon"
                        aria-hidden="true"></span></button>
            </div>
        </section>

        <!-- Tentang Section -->
        <section id="tentang" class="section-pad" data-reveal-section
            style="background: linear-gradient(135deg, var(--jg-sky) 0%, var(--jg-white) 100%);">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5" data-reveal-item>
                        <span class="eyebrow text-orange">Tentang Kami</span>
                        <h2 class="mt-3">Apa itu <em>JemberGo?</em></h2>
                        <p class="mt-3">JemberGo adalah platform informasi dan layanan pariwisata
                            resmi Kabupaten Jember yang membantu wisatawan menemukan, merencanakan, dan
                            memesan tiket wisata dengan mudah.</p>
                        <p>Kami menghubungkan Anda dengan destinasi terbaik di Jember, mulai dari wisata
                            alam, bahari, hingga buatan. Dengan sistem pemesanan yang terintegrasi,
                            perjalanan Anda menjadi lebih terencana dan menyenangkan.</p>
                        <div class="mt-4">
                            <a href="{{ route('destinations.index') }}" class="btn btn-jg-primary">
                                Jelajahi Destinasi <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-7" data-reveal-item style="--reveal-delay: 120ms;">
                        <div class="category-grid">
                            <a href="{{ route('destinations.index', ['kategori' => 'Alam']) }}"
                                class="category-item">
                                <span class="category-icon"><i class="bi bi-tree-fill"></i></span>
                                <strong>Wisata Alam</strong>
                                <small>Air Terjun Tancak, Kali Jompo, dan perbukitan hijau</small>
                                <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                            <a href="{{ route('destinations.index', ['kategori' => 'Bahari']) }}"
                                class="category-item">
                                <span class="category-icon"><i class="bi bi-water"></i></span>
                                <strong>Wisata Bahari</strong>
                                <small>Pantai Papuma, Teluk Love, dan keindahan laut selatan</small>
                                <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                            <a href="{{ route('destinations.index', ['kategori' => 'Buatan']) }}"
                                class="category-item">
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
                            $image =
                                $destination->foto_utama ?:
                                'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4" data-reveal-item
                            style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms;">
                            @if ($destination->status_aktif === 'aktif')
                                <a href="{{ route('destinations.show', $destination->id_destinasi) }}"
                                    class="destination-gallery-card"
                                aria-label="Lihat {{ $destination->nama_wisata }}">@else<div
                                        class="destination-gallery-card opacity-75"
                                        aria-label="{{ $destination->nama_wisata }} sedang dinonaktifkan">
                            @endif
                            <img src="{{ $image }}"
                                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';"
                                alt="{{ $destination->nama_wisata }}" loading="lazy">
                            <span class="destination-gallery-overlay"></span>
                            <span class="destination-gallery-name">{{ $destination->nama_wisata }}
                                @if ($destination->status_aktif !== 'aktif')
                                    <small class="d-block text-warning">Sedang dinonaktifkan</small>
                                @endif
                            </span>
                            @if ($destination->status_aktif === 'aktif')
                                </a>
                            @else
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state py-5 text-center">
                        <i class="bi bi-map fs-1 text-muted mb-3 d-block"></i>
                        <h4>Destinasi sedang disiapkan</h4>
                        <p class="text-muted">Segera kembali untuk menemukan tempat favoritmu di
                            Jember.</p>
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
                    from {
                        transform: scale(1);
                    }

                    to {
                        transform: scale(1.06);
                    }
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

                #destinasi .destination-gallery-card:hover .destination-gallery-overlay {
                    background: linear-gradient(180deg, rgba(8, 25, 42, .08) 25%, rgba(8, 25, 42, .9) 100%);
                }

                @media (prefers-reduced-motion: reduce) {
                    #destinasi .destination-gallery-card img {
                        animation: none;
                    }
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
                    <a href="{{ route('articles.index') }}"
                        class="text-dark text-decoration-none fw-semibold d-none d-md-block">Lihat
                        semua <i class="bi bi-arrow-right ms-1"></i></a>
                </div>

                <div class="row g-4">
                    @forelse ($articles as $article)
                        <div class="col-md-4" data-reveal-item
                            style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms;">
                            <article class="article-card h-100">
                                <div class="article-image">
                                    <img src="{{ $article->gambar ?: 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=800&q=80' }}"
                                        alt="{{ $article->judul }}">
                                </div>
                                <div class="card-body">
                                    <small
                                        class="text-muted">{{ optional($article->tanggal_publikasi)->translatedFormat('d F Y') }}</small>
                                    <h3 style="font-size: 1.05rem; margin: 0.5rem 0 0.75rem;">
                                        {{ $article->judul }}</h3>
                                    <a href="{{ route('articles.show', $article->id_artikel) }}"
                                        class="read-link">
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

        <section id="peta-wisata" class="section-pad" data-reveal-section>
            <div class="container">
                <div class="section-heading d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <span class="eyebrow text-orange">Jelajahi Jember</span>
                        <h2 class="mt-2 mb-0">Peta <em>Wisata</em></h2>
                    </div>
                    <a href="{{ route('destinations.index') }}" class="text-dark text-decoration-none fw-semibold">
                        Semua destinasi <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="tourism-map-tools">
                    <label class="tourism-map-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input id="tourism-map-search" type="search" placeholder="Cari nama wisata..."
                            aria-label="Cari nama wisata di peta">
                    </label>
                    <label class="tourism-map-category">
                        <span class="visually-hidden">Filter kategori</span>
                        <select id="tourism-map-category" aria-label="Filter kategori wisata">
                            <option value="">Semua kategori</option>
                        </select>
                    </label>
                    <span id="tourism-map-count" class="tourism-map-count" aria-live="polite"></span>
                </div>

                <div class="tourism-map-frame">
                    <div id="tourism-map" role="region" aria-label="Peta destinasi wisata Jember"></div>
                    @if ($mapDestinations->isEmpty())
                        <div class="tourism-map-empty">Belum ada destinasi aktif dengan koordinat untuk ditampilkan.</div>
                    @endif
                </div>
                <div class="tourism-map-legend" aria-label="Kategori wisata">
                    <span><i class="bi bi-geo-alt-fill tourism-map-legend-icon tourism-map-legend-icon-alam"
                            aria-hidden="true"></i>Alam</span>
                    <span><i class="bi bi-geo-alt-fill tourism-map-legend-icon tourism-map-legend-icon-bahari"
                            aria-hidden="true"></i>Bahari</span>
                    <span><i class="bi bi-geo-alt-fill tourism-map-legend-icon tourism-map-legend-icon-buatan"
                            aria-hidden="true"></i>Buatan</span>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="section-pad" data-reveal-section>
            <div class="container">
                <div class="cta-box">
                    <div>
                        <span class="eyebrow"
                            style="background: rgba(255,255,255,0.1); color: var(--jg-yellow);">Waktunya
                            berangkat</span>
                        <h2 class="mt-2 text-white">Siap menjelajahi <em>Jember?</em></h2>
                        <p class="text-white-50 mb-0 mt-2">Dapatkan pengalaman wisata terbaik dengan
                            pemesanan yang mudah dan aman.</p>
                    </div>
                    <a href="{{ route('destinations.index') }}"
                        class="btn btn-jg-primary btn-lg flex-shrink-0">Mulai Eksplorasi <i
                            class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </section>
    </main>

    <x-public-footer />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .tourism-map-tools {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(170px, 240px) auto;
            gap: .75rem;
            align-items: center;
            margin-bottom: .85rem;
        }

        .tourism-map-search,
        .tourism-map-category {
            display: flex;
            min-height: 44px;
            align-items: center;
            gap: .65rem;
            border: 1px solid var(--jg-border);
            border-radius: .5rem;
            background: #fff;
            color: var(--jg-text);
            padding: 0 .8rem;
        }

        .tourism-map-search input,
        .tourism-map-category select {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--jg-dark);
        }

        .tourism-map-count {
            color: var(--jg-text);
            font-size: .9rem;
            text-align: right;
        }

        .tourism-map-frame {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--jg-border);
            border-radius: .65rem;
            background: #e7edf0;
        }

        #tourism-map {
            z-index: 0;
            width: 100%;
            height: 500px;
        }

        .tourism-map-empty {
            position: absolute;
            z-index: 500;
            inset: auto 1rem 1rem;
            border-radius: .4rem;
            background: #fff;
            padding: .65rem .8rem;
            color: var(--jg-text);
            text-align: center;
        }

        .tourism-map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: .8rem;
            color: var(--jg-text);
            font-size: .9rem;
        }

        .tourism-map-legend span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .tourism-map-legend-icon {
            font-size: 1rem;
            line-height: 1;
        }

        .tourism-map-legend-icon-alam { color: #168a62; }
        .tourism-map-legend-icon-bahari { color: #1685b5; }
        .tourism-map-legend-icon-buatan { color: #e47b16; }

        .tourism-map-marker {
            border: 0;
            background: transparent;
        }

        .tourism-map-marker-icon {
            font-size: 2rem;
            filter: drop-shadow(0 1px 2px rgba(15, 39, 71, .45));
            line-height: 1;
        }

        .tourism-map-popup-title {
            margin: 0 0 .2rem;
            color: #1f2937;
            font-size: 1rem;
            font-weight: 700;
        }

        .tourism-map-popup-category,
        .tourism-map-popup-address {
            margin: 0 0 .45rem;
            color: #64748b;
        }

        @media (max-width: 640px) {
            .tourism-map-tools {
                grid-template-columns: 1fr;
            }

            .tourism-map-count {
                text-align: left;
            }

            #tourism-map {
                height: 390px;
            }
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (() => {
            const locations = @json($mapDestinations);
            const mapElement = document.getElementById('tourism-map');
            const searchInput = document.getElementById('tourism-map-search');
            const categorySelect = document.getElementById('tourism-map-category');
            const resultCount = document.getElementById('tourism-map-count');
            const map = L.map(mapElement).setView([-8.17, 113.70], 10);
            const markerLayer = L.featureGroup().addTo(map);
            const markerColors = {
                Alam: '#168a62',
                Bahari: '#1685b5',
                Buatan: '#e47b16'
            };

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            [...new Set(locations.map((location) => location.category).filter(Boolean))]
                .sort((first, second) => first.localeCompare(second, 'id'))
                .forEach((category) => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    categorySelect.append(option);
                });

            const createPopup = (location) => {
                const content = document.createElement('div');
                const title = document.createElement('p');
                title.className = 'tourism-map-popup-title';
                title.textContent = location.name;
                content.append(title);

                const category = document.createElement('p');
                category.className = 'tourism-map-popup-category';
                category.textContent = location.category || 'Wisata';
                content.append(category);

                if (location.address) {
                    const address = document.createElement('p');
                    address.className = 'tourism-map-popup-address';
                    address.textContent = location.address;
                    content.append(address);
                }

                const link = document.createElement('a');
                link.href = location.url;
                link.textContent = 'Lihat detail';
                link.className = 'fw-semibold text-decoration-none';
                content.append(link);

                return content;
            };

            const renderMarkers = () => {
                const query = searchInput.value.trim().toLocaleLowerCase('id');
                const selectedCategory = categorySelect.value;
                const visibleLocations = locations.filter((location) => {
                    const matchesName = location.name.toLocaleLowerCase('id').includes(query);
                    const matchesCategory = !selectedCategory || location.category === selectedCategory;

                    return matchesName && matchesCategory;
                });

                markerLayer.clearLayers();
                visibleLocations.forEach((location) => {
                    const markerColor = markerColors[location.category] || '#d94a48';
                    const marker = L.marker([location.latitude, location.longitude], {
                        icon: L.divIcon({
                            className: 'tourism-map-marker',
                            html: `<i class="bi bi-geo-alt-fill tourism-map-marker-icon" style="color: ${markerColor}" aria-hidden="true"></i>`,
                            iconSize: [30, 36],
                            iconAnchor: [15, 36],
                            popupAnchor: [0, -34]
                        })
                    }).bindPopup(createPopup(location));

                    marker.addTo(markerLayer);
                });

                resultCount.textContent = `${visibleLocations.length} dari ${locations.length} destinasi`;

                if (visibleLocations.length) {
                    map.fitBounds(markerLayer.getBounds().pad(.12), { maxZoom: 13 });
                } else {
                    map.setView([-8.17, 113.70], 10);
                }
            };

            searchInput.addEventListener('input', renderMarkers);
            categorySelect.addEventListener('change', renderMarkers);
            renderMarkers();

            const revealItems = document.querySelectorAll(
                '[data-reveal-section], [data-reveal-item]');
            if (!('IntersectionObserver' in window)) {
                revealItems.forEach((item) => item.classList.add('is-revealed'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => entry.target.classList.toggle('is-revealed',
                    entry.isIntersecting));
            }, {
                threshold: 0.14,
                rootMargin: '0px 0px -8% 0px'
            });

            revealItems.forEach((item) => observer.observe(item));
        })();
    </script>
@endsection
