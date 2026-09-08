<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Destinasi | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-light">
<x-public-navbar />
<main class="container py-5"><span class="text-warning text-uppercase small fw-bold">Jelajah Jember</span><h1 class="display-4 fw-bold mt-2">Destinasi untuk <em>ceritamu.</em></h1><p class="lead text-secondary mb-5">Pilih tempat yang ingin kamu kunjungi berikutnya.</p>
<div class="row g-4">@forelse($destinations as $destination)@php($image = $destination->foto_utama)@php($fallback = 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80')<div class="col-md-6 col-lg-4"><article class="destination-card"><div class="destination-image"><img src="{{ $image ?: $fallback }}" onerror="this.onerror=null;this.src='{{ $fallback }}';" alt="{{ $destination->nama_wisata }}"><span class="category-pill">{{ $destination->kategori }}</span></div><div class="destination-body"><h3>{{ $destination->nama_wisata }}</h3><p><i class="bi bi-geo-alt me-1"></i>{{ $destination->alamat }}</p><div class="d-flex justify-content-between align-items-center mt-3"><span class="price">Mulai <strong>Rp {{ number_format($destination->jenisTiket->min('harga') ?? 0, 0, ',', '.') }}</strong></span><a href="{{ route('destinations.show', $destination->id_destinasi) }}" class="card-arrow"><i class="bi bi-arrow-up-right"></i></a></div></div></article></div>@empty<div class="col-12"><div class="empty-state">Belum ada destinasi aktif.</div></div>@endforelse</div>
<div class="mt-5">{{ method_exists($destinations, 'links') ? $destinations->links() : '' }}</div></main>
</body></html>