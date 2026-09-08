<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->judul }} | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-light">
<x-public-navbar />
<main class="container py-5 pt-5">
    <a href="{{ route('articles.index') }}" class="text-dark text-decoration-none">Kembali ke artikel</a>
    <article class="bg-white rounded-4 shadow-sm overflow-hidden mt-4"><div class="p-4 p-lg-5"><small class="text-warning text-uppercase fw-bold">Cerita perjalanan</small><h1 class="display-5 fw-bold mt-3">{{ $article->judul }}</h1><p class="text-secondary">{{ optional($article->tanggal_publikasi)->translatedFormat('d F Y') }}</p>@if($article->gambar)<img src="{{ $article->gambar }}" class="w-100 rounded-4 my-4" style="max-height:480px;object-fit:cover" alt="{{ $article->judul }}">@endif<div class="lead" style="white-space:pre-line">{{ $article->isi }}</div></div></article>
</main>
</body>
</html>
