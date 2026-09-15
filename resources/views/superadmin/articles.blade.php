@extends('layouts.superadmin')
@section('title', 'Artikel')
@section('page_label', 'Artikel publik')
@section('content')
    <div class="article-page-heading"><div><span class="page-kicker">Content management</span><h1>Artikel</h1><p>Kelola artikel dan informasi pariwisata JemberGo.</p></div><a href="{{ route('superadmin.articles.create') }}" class="btn btn-warning"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah artikel</a></div>
    @if(session('success'))<div class="alert alert-success article-alert"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger article-alert"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>{{ $errors->first() }}</div>@endif
    <section class="article-toolbar"><div class="article-search"><i class="bi bi-search" aria-hidden="true"></i><input id="article-search" type="search" placeholder="Cari artikel..." aria-label="Cari artikel"></div><select id="article-status-filter" class="form-select" aria-label="Filter status"><option value="">Semua status</option><option value="PUBLISHED">Diterbitkan</option><option value="DRAFT">Draf</option></select></section>
    <section class="article-list-card"><div class="table-responsive"><table class="table article-table align-middle mb-0"><thead><tr><th>Artikel</th><th>Status</th><th>Publikasi</th><th class="text-end">Aksi</th></tr></thead><tbody id="article-list">
        @forelse($articles as $article)
            <tr data-article-row data-title="{{ strtolower($article->judul) }}" data-status="{{ $article->status }}"><td><div class="article-list-item">@if($article->gambar)<img src="{{ $article->gambar }}" alt="{{ $article->judul }}" class="article-list-image">@else<div class="article-list-image article-list-image-empty"><i class="bi bi-image" aria-hidden="true"></i></div>@endif<div><strong>{{ $article->judul }}</strong><small>{{ \Illuminate\Support\Str::limit($article->isi, 115) }}</small></div></div></td><td><span class="article-status-badge {{ $article->status === 'PUBLISHED' ? 'is-published' : 'is-draft' }}"><span></span>{{ \App\Support\StatusLabel::article($article->status) }}</span></td><td class="article-date">{{ $article->tanggal_publikasi?->format('d/m/Y H:i') ?? 'Belum diterbitkan' }}</td><td class="text-end"><div class="article-row-actions"><a href="{{ route('superadmin.articles.edit', $article->id_artikel) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</a><button type="button" class="btn btn-outline-danger btn-sm" data-delete-article data-article-title="{{ $article->judul }}" data-delete-action="{{ route('superadmin.articles.destroy', $article->id_artikel) }}"><i class="bi bi-trash3 me-1" aria-hidden="true"></i>Hapus</button></div></td></tr>
        @empty
            <tr><td colspan="4"><div class="article-empty"><i class="bi bi-journal-text" aria-hidden="true"></i><h2>Belum ada artikel</h2><p>Mulai tulis artikel pertama untuk pembaca JemberGo.</p><a href="{{ route('superadmin.articles.create') }}" class="btn btn-warning">Tambah artikel</a></div></td></tr>
        @endforelse
    </tbody></table></div></section>
    <p class="article-no-results" id="article-no-results" hidden>Tidak ada artikel yang sesuai.</p>

    <dialog class="article-delete-dialog" id="article-delete-dialog" aria-labelledby="article-delete-title"><div class="article-dialog-panel"><div class="dialog-header"><div><span class="page-kicker text-danger">Tindakan permanen</span><h2 id="article-delete-title">Hapus artikel?</h2><p>Artikel <strong id="article-delete-name"></strong> akan dihapus dan tidak dapat dikembalikan.</p></div><button type="button" class="dialog-close" data-close-article-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div><div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-article-dialog>Batal</button><form method="POST" id="article-delete-form">@csrf @method('DELETE')<button type="submit" class="btn btn-danger"><i class="bi bi-trash3 me-1" aria-hidden="true"></i>Hapus artikel</button></form></div></div></dialog>
@endsection
@push('scripts')
<script>
    (() => {
        const search = document.getElementById('article-search');
        const status = document.getElementById('article-status-filter');
        const empty = document.getElementById('article-no-results');
        const filter = () => { const query = search.value.toLowerCase().trim(); let visible = 0; document.querySelectorAll('[data-article-row]').forEach((row) => { const match = (!query || row.dataset.title.includes(query)) && (!status.value || row.dataset.status === status.value); row.hidden = !match; if (match) visible++; }); empty.hidden = visible > 0; };
        [search, status].forEach((control) => control.addEventListener('input', filter));
        const dialog = document.getElementById('article-delete-dialog');
        const name = document.getElementById('article-delete-name');
        const form = document.getElementById('article-delete-form');
        document.querySelectorAll('[data-delete-article]').forEach((button) => button.addEventListener('click', () => { name.textContent = button.dataset.articleTitle; form.action = button.dataset.deleteAction; dialog.showModal(); }));
        document.querySelectorAll('[data-close-article-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
        dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    })();
</script>
@endpush
