@extends('layouts.app')

@section('title', 'Destinasi Wisata - JemberGo')

@section('content')
    @include('components.public-navbar')

    <main class="section-pad" style="padding-top: 8rem; background: var(--jg-bg); min-height: 100vh;">
        <div class="container">
            @if (session('warning'))
                <div class="alert alert-warning d-flex align-items-center gap-2" role="alert"><i
                        class="bi bi-exclamation-triangle-fill"
                        aria-hidden="true"></i><span>{{ session('warning') }}</span></div>
            @endif
            <!-- Header Section -->
            <div class="text-center mb-5">
                <span class="eyebrow text-orange">Jelajahi Jember</span>
                <h1 class="mt-2 mb-3">Destinasi Wisata Pilihan</h1>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    Temukan berbagai keindahan alam, wisata bahari, dan wisata buatan di Kabupaten
                    Jember yang siap menemani petualanganmu.
                </p>
            </div>

            <!-- Search / Filter -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-8 col-lg-6">
                    <form action="{{ route('destinations.search') }}" method="GET"
                        class="position-relative">
                        <input type="text" name="q" class="form-control form-control-lg ps-5"
                            placeholder="Cari destinasi wisata..." value="{{ request('q') }}">
                        <i
                            class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <button type="submit"
                            class="btn btn-jg-primary position-absolute top-50 end-0 translate-middle-y me-2">Cari</button>
                    </form>
                </div>
            </div>

            <!-- Destinations Grid -->
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
                    <div class="col-md-6 col-lg-4">
                        <article class="destination-card h-100">
                            <div class="destination-image">
                                <img src="{{ $image }}"
                                    onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80';"
                                    alt="{{ $destination->nama_wisata }}">
                                <span
                                    class="category-pill">{{ $destination->kategori ?? 'Umum' }}</span>
                            </div>
                            <div class="destination-body d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="mb-0 fs-5">{{ $destination->nama_wisata }}</h3>
                                </div>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-geo-alt-fill text-orange me-1"></i>
                                    {{ Str::limit($destination->alamat, 60) }}
                                </p>
                                <div class="mt-auto">
                                    @if ($destination->status_aktif !== 'aktif')
                                        <div class="alert alert-warning py-2 mb-3 small"><i
                                                class="bi bi-lock-fill me-1"
                                                aria-hidden="true"></i>Destinasi sedang dinonaktifkan.
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="price">Mulai <strong class="text-orange">Rp
                                                {{ number_format($minPrice, 0, ',', '.') }}</strong></span>
                                    </div>
                                    @if ($destination->status_aktif === 'aktif')
                                        <a href="{{ route('destinations.show', $destination->id_destinasi) }}"
                                            class="destination-detail-button btn btn-jg-primary w-100">Lihat
                                            detail destinasi <i class="bi bi-arrow-right"></i></a>
                                    @else
                                        <button type="button"
                                            class="destination-detail-button btn btn-secondary w-100"
                                            disabled aria-disabled="true">Detail tidak tersedia</button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state py-5 text-center">
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
                <div class="d-flex justify-content-center mt-5">
                    <nav aria-label="Page navigation">
                        {{ $destinations->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </main>

    <x-public-footer />
@endsection
