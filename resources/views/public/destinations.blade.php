@extends('layouts.app')

@section('title', 'Destinasi Wisata - JemberGo')

@section('content')
    @include('components.public-navbar')

    <main class="destinations-page-main">
        @if (session('warning'))
            <div class="container pt-3">
                <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        <section class="destinations-hero" data-reveal-section>
            <div class="container">
                <div class="text-center" data-reveal-item>
                    <span class="eyebrow text-orange">Jelajahi Jember</span>
                    <h1 class="mt-2 mb-3">Destinasi Wisata Pilihan</h1>
                    <p class="text-muted mx-auto mb-4" style="max-width: 600px;">
                        Temukan berbagai keindahan alam, wisata bahari, dan wisata buatan di Kabupaten
                        Jember yang siap menemani petualanganmu.
                    </p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6" data-reveal-item style="--reveal-delay: 120ms;">
                        <form action="{{ route('destinations.search') }}" method="GET"
                            class="destination-search">
                            <input type="text" name="q"
                                class="destination-search-input form-control form-control-lg"
                                placeholder="Cari destinasi wisata..." value="{{ request('q') }}">
                            <i class="destination-search-icon bi bi-search text-muted"
                                aria-hidden="true"></i>
                            <button type="submit" class="destination-search-button btn btn-jg-primary">
                                Cari
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <div class="container destinations-results">
            <div class="row g-4">
                @forelse ($destinations as $destination)
                    @php
                        $image =
                            $destination->foto_utama ?:
                            'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';
                        $minPrice =
                            $destination->jenisTiket && $destination->jenisTiket->count() > 0
                                ? $destination->jenisTiket->min('harga')
                                : 0;
                    @endphp
                    <div class="col-md-6 col-lg-4" data-reveal-item
                        style="--reveal-delay: {{ ($loop->index % 3) * 90 }}ms;">
                        <article class="destination-card h-100">
                            <div class="destination-image">
                                <img src="{{ $image }}"
                                    onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';"
                                    alt="{{ $destination->nama_wisata }}">
                            </div>
                            <div class="destination-body d-flex flex-column">
                                <div class="destination-heading">
                                    <h3>{{ $destination->nama_wisata }}</h3>
                                    <span class="destination-category">{{ $destination->kategori ?? 'Umum' }}</span>
                                </div>
                                <p class="destination-location">
                                    <i class="bi bi-geo-alt-fill text-orange me-1"></i>
                                    {{ Str::limit($destination->alamat, 60) }}
                                </p>
                                @if ($destination->deskripsi)
                                    <p class="destination-description">
                                        {{ Str::limit(strip_tags($destination->deskripsi), 100) }}
                                    </p>
                                @endif
                                <div class="mt-auto">
                                    @if ($destination->status_aktif !== 'aktif')
                                        <p class="destination-unavailable">
                                            <i class="bi bi-lock-fill" aria-hidden="true"></i>
                                            Destinasi sedang dinonaktifkan
                                        </p>
                                    @endif
                                    <div class="destination-card-footer">
                                        <div class="destination-card-meta">
                                            <span>Mulai dari</span>
                                            <strong>Rp {{ number_format($minPrice, 0, ',', '.') }}</strong>
                                            @if ($destination->jam_operasional)
                                                <small>
                                                    <i class="bi bi-clock" aria-hidden="true"></i>
                                                    {{ $destination->jam_operasional }}
                                                </small>
                                            @endif
                                        </div>
                                        @if ($destination->status_aktif === 'aktif')
                                            <a href="{{ route('destinations.show', $destination->id_destinasi) }}"
                                                class="destination-detail-button btn btn-jg-primary">
                                                Lihat detail
                                            </a>
                                        @else
                                            <button type="button" class="destination-detail-button btn btn-secondary"
                                                disabled aria-disabled="true">Tidak tersedia</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state py-5 text-center" data-reveal-item>
                            <i class="bi bi-map fs-1 text-muted mb-3 d-block"></i>
                            <h4>Destinasi tidak ditemukan</h4>
                            <p class="text-muted">Coba gunakan kata kunci lain atau jelajahi kategori
                                yang tersedia.</p>
                            <a href="{{ route('destinations.index') }}"
                                class="btn btn-outline-jg mt-2">Reset Pencarian</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($destinations->hasPages())
                <div class="d-flex justify-content-center mt-5" data-reveal-item>
                    <nav aria-label="Page navigation">
                        {{ $destinations->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </main>

    <x-public-footer />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const revealItems = document.querySelectorAll(
                '[data-reveal-section], [data-reveal-item]');

            if (!('IntersectionObserver' in window)) {
                revealItems.forEach((item) => item.classList.add('is-revealed'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => entry.target.classList.toggle(
                    'is-revealed', entry.isIntersecting));
            }, {
                threshold: 0.14,
                rootMargin: '0px 0px -8% 0px'
            });

            revealItems.forEach((item) => observer.observe(item));
        });
    </script>
@endsection
