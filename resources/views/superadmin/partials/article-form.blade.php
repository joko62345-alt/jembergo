@php
    $isEdit = (bool) $article;
    $formId = $isEdit ? 'edit-article-form' : 'create-article-form';
@endphp
<form id="{{ $formId }}" method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="article-editor-form">
    @csrf
    @if($formMethod === 'PUT') @method('PUT') @endif
    <div class="article-editor-grid">
        <section class="article-content-panel">
            <div class="article-section-heading"><span class="page-kicker">Konten artikel</span><h2>Informasi artikel</h2><p>Tulis konten yang informatif dan mudah dipahami oleh pembaca.</p></div>
            <div class="article-field-group">
                <label class="form-label" for="{{ $formId }}-judul">Judul artikel</label>
                <input id="{{ $formId }}-judul" name="judul" value="{{ old('judul', $article?->judul) }}" class="form-control article-title-input @error('judul') is-invalid @enderror" placeholder="Masukkan Judul Artikel" required>
                <small class="form-helper">Gunakan judul yang singkat dan informatif.</small>
                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="article-field-group article-body-field">
                <label class="form-label" for="{{ $formId }}-isi">Isi artikel</label>
                <textarea id="{{ $formId }}-isi" name="isi" class="form-control article-body-input @error('isi') is-invalid @enderror" placeholder="Tulis isi artikel di sini..." required>{{ old('isi', $article?->isi) }}</textarea>
                <small class="form-helper">Gunakan paragraf yang ringkas agar nyaman dibaca.</small>
                @error('isi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>
        <aside class="article-media-panel">
            <div class="article-section-heading"><span class="page-kicker">Media</span><h2>Gambar artikel</h2><p>Gunakan gambar yang relevan untuk memperkuat cerita.</p></div>
            <div class="article-image-preview" data-article-image-preview>
                @if($article?->gambar)<img src="{{ $article->gambar }}" alt="Gambar {{ $article->judul }}">@else<div class="article-image-empty"><i class="bi bi-image" aria-hidden="true"></i><span>Belum ada gambar</span></div>@endif
            </div>
            <label class="form-label" for="{{ $formId }}-gambar">Upload gambar</label>
            <input id="{{ $formId }}-gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="form-control @error('gambar') is-invalid @enderror" data-article-image-input>
            <small class="form-helper">JPG, PNG, atau WEBP. Maksimal 5 MB.</small>
            <small class="article-image-warning text-danger" role="alert" aria-live="polite" @error('gambar') @else hidden @enderror>@error('gambar'){{ $message }}@else File harus berupa JPG, PNG, atau WEBP dengan ukuran maksimal 5 MB.@enderror</small>
            <div class="article-status-field">
                <label class="form-label" for="{{ $formId }}-status">Status publikasi</label>
                <select id="{{ $formId }}-status" name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="DRAFT" @selected(old('status', $article?->status ?? 'DRAFT') === 'DRAFT')>Draf</option>
                    <option value="PUBLISHED" @selected(old('status', $article?->status) === 'PUBLISHED')>Diterbitkan</option>
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </aside>
    </div>
    <div class="article-form-actions"><a href="{{ $cancelUrl }}" class="btn btn-outline-dark">Batal</a><button type="submit" class="btn btn-warning article-submit-button"><i class="bi bi-check2 me-1" aria-hidden="true"></i>{{ $submitLabel }}</button></div>
</form>
