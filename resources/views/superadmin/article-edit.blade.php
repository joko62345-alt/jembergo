@extends('layouts.superadmin')
@section('title', 'Edit Artikel')
@section('page_label', 'Artikel / Edit artikel')
@section('content')
    <div class="article-page-heading"><div><span class="page-kicker">Content management</span><h1>Artikel</h1><p>Kelola artikel dan informasi pariwisata JemberGo.</p></div><a href="{{ route('superadmin.articles') }}" class="btn btn-outline-dark"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke artikel</a></div>
    <div class="article-breadcrumb"><a href="{{ route('superadmin.articles') }}">Artikel</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Edit artikel</span></div>
    @if($errors->any())<div class="alert alert-danger article-alert"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>{{ $errors->first() }}</div>@endif
    <section class="article-editor-card"><div class="article-editor-card-heading"><div><span class="page-kicker">Mode edit</span><h2>Edit artikel</h2><p>Perbarui informasi artikel yang dipilih.</p></div><span class="article-id">Artikel #{{ $article->id_artikel }}</span></div>@include('superadmin.partials.article-form', ['formAction' => route('superadmin.articles.update', $article->id_artikel), 'formMethod' => 'PUT', 'submitLabel' => 'Simpan perubahan', 'cancelUrl' => route('superadmin.articles')])</section>
@endsection
@push('scripts')
<script>
    (() => {
        const input = document.querySelector('[data-article-image-input]');
        const preview = document.querySelector('[data-article-image-preview]');
        input?.addEventListener('change', () => { const file = input.files?.[0]; if (!file) return; const image = document.createElement('img'); image.alt = 'Preview gambar baru'; image.src = URL.createObjectURL(file); preview.replaceChildren(image); });
        document.querySelector('.article-editor-form')?.addEventListener('submit', (event) => { const button = event.currentTarget.querySelector('.article-submit-button'); if (button) { button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan...'; } });
    })();
</script>
@endpush
