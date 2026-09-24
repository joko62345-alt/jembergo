<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $destination->nama_wisata }} | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        #map {
            height: 280px;
            border-radius: 1rem;
        }

        .destination-detail-page .jg-navbar .nav-link {
            font-family: 'DM Sans', sans-serif !important;
            font-size: .95rem !important;
            font-weight: 500;
        }

        .destination-detail-page .jg-navbar .nav-link::after {
            right: .9rem;
            bottom: .15rem;
            left: .9rem;
            width: auto;
            height: 2px;
            transform: scaleX(0);
            transform-origin: center;
        }

        .destination-detail-page .jg-navbar .nav-link:hover::after,
        .destination-detail-page .jg-navbar .nav-link.active::after {
            transform: scaleX(1);
        }

        .destination-detail-page main {
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        .destination-hero-image {
            min-height: 320px;
            height: min(58vw, 520px);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(31, 41, 55, 0.12);
        }

        .destination-hero-copy {
            padding: 1.25rem 0;
        }

        .destination-hero-copy h1 {
            max-width: 14ch;
        }

        .route-stat {
            height: 100%;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 8px 20px rgba(31, 41, 55, 0.05);
        }

        .route-stat-icon {
            width: 2rem;
            height: 2rem;
            display: inline-grid;
            place-items: center;
            border-radius: 0.7rem;
            background: rgba(245, 139, 5, 0.12);
            color: #f58b05;
        }

        .destination-locate-button {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border: 0;
            border-radius: 0.85rem;
            padding: 0.8rem 1.1rem;
            background: linear-gradient(135deg, #f58b05, #ffad1f);
            box-shadow: 0 8px 18px rgba(245, 139, 5, 0.2);
            color: #fff;
            font-weight: 600;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
        }

        .destination-locate-button:hover {
            color: #fff;
            filter: brightness(0.97);
            transform: translateY(-2px);
            box-shadow: 0 11px 22px rgba(245, 139, 5, 0.28);
        }

        .destination-locate-button:active {
            transform: translateY(0);
        }

        .destination-locate-button:disabled {
            cursor: wait;
            opacity: 0.75;
            transform: none;
        }

        .destination-locate-button i {
            font-size: 1.1rem;
        }

        .destination-gallery-scroller {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 0 0.25rem 0.75rem;
            scroll-snap-type: x mandatory;
            scrollbar-width: thin;
        }

        .destination-gallery-item {
            flex: 0 0 auto;
            scroll-snap-align: start;
        }

        .destination-gallery-item figure {
            position: relative;
            width: fit-content;
            max-width: min(72vw, 380px);
            background: #e9ecef;
        }

        .destination-gallery-item figure::after {
            position: absolute;
            inset: 35% 0 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.72));
            content: '';
        }

        .destination-gallery-image {
            display: block;
            width: auto;
            max-width: 100%;
            max-height: 230px;
            height: auto;
            object-fit: contain;
        }

        .destination-gallery-caption {
            position: absolute;
            right: 1.25rem;
            bottom: 1rem;
            left: 1.25rem;
            z-index: 1;
            color: #fff;
        }

        .destination-review-scroller {
            display: flex;
            gap: .75rem;
            overflow-x: auto;
            padding: 0 .25rem .65rem;
            scroll-snap-type: x mandatory;
            scrollbar-width: thin;
        }

        .destination-review-item {
            flex: 0 0 min(72vw, 280px);
            min-height: 120px;
            scroll-snap-align: start;
            padding: 1rem;
            border: 1px solid #edf1f4;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 7px 18px rgba(31, 41, 55, .06);
        }

        .destination-review-item strong {
            color: #173b60;
            font-size: .84rem;
        }

        .destination-review-item .review-stars {
            color: #f5a400;
            font-size: .75rem;
            letter-spacing: .06em;
            white-space: nowrap;
        }

        .destination-review-item p {
            margin: .65rem 0 0;
            color: #64748b;
            font-size: .8rem;
            line-height: 1.5;
        }

        @media (min-width: 768px) {
            .destination-gallery-item figure {
                max-width: 380px;
            }
        }
    </style>
</head>

<body class="public-page destination-detail-page">
    <x-public-navbar />
    @php($mainImage = $destination->foto_utama ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=85')
    <main class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="destination-hero-image"><img class="w-100 h-100 object-fit-cover"
                        src="{{ $mainImage }}"
                        onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=85';"
                        alt="{{ $destination->nama_wisata }}"></div>
            </div>
            <div class="col-lg-5">
                <div class="destination-hero-copy"><span
                        class="badge text-bg-warning rounded-pill">{{ $destination->kategori }}</span>
                    <h1 class="display-5 fw-bold mt-3">{{ $destination->nama_wisata }}</h1>
                    <p class="text-secondary">{{ $destination->alamat }}</p>
                    <p class="lead">{{ $destination->deskripsi }}</p>
                    <p class="text-secondary"><i
                            class="bi bi-clock me-1"></i>{{ $destination->jam_operasional }}
                        @if ($destination->review->isNotEmpty())
                            · <i class="bi bi-star-fill text-warning"></i>
                            {{ number_format($destination->review->avg('rating'), 1) }}
                        @endif
                    </p>
                    @if (session('jg_role') === 'CUSTOMER')
                        <a href="{{ route('customer.booking.create', $destination->id_destinasi) }}"
                        class="btn btn-warning rounded-pill">Pesan tiket</a>@else<a
                            href="{{ route('login') }}" class="btn btn-warning rounded-pill">Masuk
                            untuk memesan</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row g-4 mt-2">
            <div class="col-lg-7">
                <h2 class="h4 fw-bold">Lokasi & rute</h2>
                <div id="map"></div><button id="locate"
                    class="destination-locate-button mt-3" type="button"><i
                        class="bi bi-crosshair2" aria-hidden="true"></i><span
                        id="locate-label">Lihat estimasi jarak & waktu</span></button>
                <p id="location-status" class="small text-secondary mt-2"></p>
                <div id="route-estimate" class="d-none row g-2 mt-2">
                    <div class="col-6">
                        <div class="route-stat bg-white rounded-3 p-3"><span
                                class="route-stat-icon mb-2"><i
                                    class="bi bi-signpost-2"></i></span><small
                                class="text-secondary d-block">Jarak</small><strong
                                id="route-distance">-</strong></div>
                    </div>
                    <div class="col-6">
                        <div class="route-stat bg-white rounded-3 p-3"><span
                                class="route-stat-icon mb-2"><i
                                    class="bi bi-clock"></i></span><small
                                class="text-secondary d-block">Estimasi waktu</small><strong
                                id="route-duration">-</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <h2 class="h4 fw-bold">Fasilitas</h2>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($destination->fasilitas as $facility)
                        <span
                        class="badge bg-white text-dark border rounded-pill p-2">{{ $facility->nama_fasilitas }}</span>@empty<span
                            class="text-secondary">Belum tersedia.</span>
                    @endforelse
                </div>
                <h2 class="h4 fw-bold mt-4">Jenis tiket</h2>
                @foreach ($destination->jenisTiket as $ticket)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ $ticket->nama_jenis }}</span><strong>Rp
                            {{ number_format($ticket->harga, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div><span class="text-warning text-uppercase small fw-bold">Jelajahi lebih
                        dekat</span>
                    <h2 class="h4 fw-bold mt-2 mb-0">Galeri {{ $destination->nama_wisata }}</h2>
                </div>
            </div>
            <div class="destination-gallery-scroller">
                @forelse($destination->galeri as $gallery)
                    <div class="destination-gallery-item">
                        <figure class="rounded-4 overflow-hidden shadow-sm mb-0"><img
                                src="{{ $gallery->url_foto }}" class="destination-gallery-image"
                                alt="{{ $gallery->keterangan ?: $destination->nama_wisata }}">
                            <figcaption class="destination-gallery-caption small">
                                {{ $gallery->keterangan ?: 'Foto destinasi' }}</figcaption>
                        </figure>
                </div>@empty<div>
                        <p class="text-secondary mb-0">Galeri destinasi belum tersedia.</p>
                    </div>
                @endforelse
            </div>
        </section>
        <section class="mt-5">
            <h2 class="h4 fw-bold mb-3">Ulasan pengunjung</h2>
            <div class="destination-review-scroller">
                @forelse($destination->review->sortByDesc('tanggal_review')->take(5) as $review)
                    <article class="destination-review-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <strong>{{ $review->customer->nama }}</strong><span
                                class="review-stars">{{ str_repeat('★', $review->rating) }}</span>
                        </div>
                        <p>{{ $review->ulasan }}</p>
                </article>@empty<p class="text-secondary">Belum ada ulasan.</p>
                @endforelse
            </div>
        </section>
    </main>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const coordinate = [{{ $destination->latitude }}, {{ $destination->longitude }}];
        const map = L.map('map').setView(coordinate, 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        L.marker(coordinate).addTo(map).bindPopup(@json($destination->nama_wisata)).openPopup();
        document.getElementById('locate').addEventListener('click', () => {
            const locateButton = document.getElementById('locate');
            const locateLabel = document.getElementById('locate-label');
            const status = document.getElementById('location-status');
            const estimate = document.getElementById('route-estimate');
            const distanceOutput = document.getElementById('route-distance');
            const durationOutput = document.getElementById('route-duration');
            if (!navigator.geolocation) {
                status.textContent = 'Browser tidak mendukung lokasi.';
                return;
            }
            locateButton.disabled = true;
            locateLabel.textContent = 'Menghitung rute...';
            status.textContent = 'Menghitung jarak dan estimasi waktu perjalanan...';
            navigator.geolocation.getCurrentPosition(async position => {
                const user = [position.coords.latitude, position.coords.longitude];
                L.marker(user).addTo(map).bindPopup('Lokasi saya').openPopup();
                map.fitBounds([coordinate, user], {
                    padding: [30, 30]
                });
                const straightDistance = L.latLng(coordinate).distanceTo(L.latLng(
                    user));
                let distance = straightDistance * 1.2;
                let duration = distance / 8.33;
                let usesRoadRoute = false;
                try {
                    const response = await fetch(
                        `https://router.project-osrm.org/route/v1/driving/${user[1]},${user[0]};${coordinate[1]},${coordinate[0]}?overview=false`
                    );
                    const result = await response.json();
                    if (result.code === 'Ok' && result.routes?.[0]) {
                        distance = result.routes[0].distance;
                        duration = result.routes[0].duration;
                        usesRoadRoute = true;
                    }
                } catch (error) {
                    /* Gunakan estimasi lokal jika routing tidak tersedia. */
                }
                if (usesRoadRoute) duration *= 1.35;
                distanceOutput.textContent =
                    `${(distance / 1000).toFixed(1).replace('.', ',')} km`;
                const minutes = Math.max(5, Math.ceil(duration / 60 / 5) * 5);
                durationOutput.textContent = minutes >= 60 ?
                    `${Math.floor(minutes / 60)} jam ${minutes % 60} menit` :
                    `${minutes} menit`;
                status.textContent = 'Rute berhasil dihitung dari lokasi Anda.';
                estimate.classList.remove('d-none');
                locateButton.disabled = false;
                locateLabel.textContent = 'Perbarui estimasi rute';
            }, () => {
                status.textContent =
                    'Lokasi tidak dapat diakses. Anda tetap dapat melihat lokasi wisata pada peta.';
                locateButton.disabled = false;
                locateLabel.textContent = 'Coba lagi';
            });
        });
    </script>
</body>

</html>
