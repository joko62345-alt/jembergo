@include('components.superadmin-sidebar')
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Artikel | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div><a href="{{ route('dashboard') }}" class="text-dark text-decoration-none">Dashboard</a><h1 class="h2 fw-bold mt-2 mb-1">Artikel Publik</h1><p class="text-secondary mb-0">Kelola artikel yang diterbitkan dan draf.</p></div>
            <a href="{{ route('superadmin.management.article.create') }}" class="btn btn-warning rounded-pill"><i class="bi bi-plus-lg me-1"></i>Tulis artikel</a>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
            <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Artikel</th><th>Status</th><th>Publikasi</th><th class="text-end">Aksi</th></tr></thead><tbody>
                @forelse($articles as $article)
                    <tr><td><div class="d-flex align-items-center gap-3">@if($article->gambar)<img src="{{ $article->gambar }}" alt="{{ $article->judul }}" width="72" height="52" class="rounded" style="object-fit:cover">@endif<div><strong>{{ $article->judul }}</strong><small class="d-block text-secondary">{{ \Illuminate\Support\Str::limit($article->isi, 100) }}</small></div></div></td><td><span class="badge {{ $article->status === 'PUBLISHED' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ \App\Support\StatusLabel::article($article->status) }}</span></td><td>{{ $article->tanggal_publikasi?->format('d/m/Y H:i') ?? '-' }}</td><td class="text-end"><a href="{{ route('superadmin.articles.edit', $article->id_artikel) }}" class="btn btn-outline-dark btn-sm rounded-pill me-1"><i class="bi bi-pencil"></i> Edit</a><form method="POST" action="{{ route('superadmin.management.article.destroy', $article->id_artikel) }}" class="d-inline" onsubmit="return confirm('Hapus artikel ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm rounded-pill" type="submit"><i class="bi bi-trash"></i> Hapus</button></form></td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-5">Belum ada artikel.</td></tr>
                @endforelse
            </tbody></table></div>
        </div></div>
    </main>
</body>
</html>
