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
        const validateArticleImage = () => { const file = input?.files?.[0]; const warning = input?.parentElement.querySelector('.article-image-warning'); const isInvalid = Boolean(file) && (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024); const message = file?.size > 5 * 1024 * 1024 ? 'Ukuran file maksimal 5 MB.' : 'File harus berupa JPG, PNG, atau WEBP.'; input?.classList.toggle('is-invalid', isInvalid); input?.setCustomValidity(isInvalid ? message : ''); if (warning) { warning.textContent = message; warning.hidden = !isInvalid; } if (isInvalid) return false; if (file) { const image = document.createElement('img'); image.alt = 'Preview gambar baru'; image.src = URL.createObjectURL(file); preview.replaceChildren(image); } return true; };
        input?.addEventListener('change', validateArticleImage);
        document.querySelector('.article-editor-form')?.addEventListener('submit', (event) => { if (!validateArticleImage()) { event.preventDefault(); return; } const button = event.currentTarget.querySelector('.article-submit-button'); if (button) { button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan...'; } });
    })();
</script>
@endpush
