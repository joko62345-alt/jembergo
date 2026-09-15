<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Artikel | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="public-page">
<x-public-navbar />
<main class="container"><div class="page-intro"><span class="eyebrow text-orange">Catatan perjalanan</span><h1 class="display-4 fw-bold mt-2">Cerita dari <em>Jember.</em></h1><p class="text-secondary">Temukan cerita, inspirasi, dan tips untuk merencanakan perjalananmu di Jember.</p></div>
<div class="row g-4 mt-3">@forelse($articles as $article)<div class="col-md-6 col-lg-4"><article class="card border-0 shadow-sm rounded-4 overflow-hidden h-100"><img src="{{ $article->gambar ?: 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=800&q=80' }}" class="article-cover-image" alt="{{ $article->judul }}"><div class="card-body p-4"><small class="text-secondary">{{ optional($article->tanggal_publikasi)->translatedFormat('d F Y') }}</small><h2 class="h5 fw-bold mt-2">{{ $article->judul }}</h2><a href="{{ route('articles.show', $article->id_artikel) }}" class="text-warning fw-semibold text-decoration-none">Baca selengkapnya <i class="bi bi-arrow-up-right"></i></a></div></article></div>@empty<div class="col-12"><div class="alert alert-light">Belum ada artikel yang dipublikasikan.</div></div>@endforelse</div>
<div class="mt-4">{{ method_exists($articles, 'links') ? $articles->links() : '' }}</div></main><x-public-footer />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>