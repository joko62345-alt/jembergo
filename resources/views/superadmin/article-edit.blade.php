@include('components.superadmin-sidebar')
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Artikel | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <a href="{{ route('superadmin.articles') }}" class="text-dark text-decoration-none">Kembali ke Artikel</a>
        <div class="row justify-content-center mt-4"><div class="col-lg-8"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
            <h1 class="h3 fw-bold">Edit artikel</h1>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('superadmin.articles.update', $article->id_artikel) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <label class="form-label mt-3" for="judul">Judul artikel</label><input id="judul" name="judul" value="{{ old('judul', $article->judul) }}" class="form-control" required>
                <label class="form-label mt-3" for="isi">Isi artikel</label><textarea id="isi" name="isi" rows="10" class="form-control" required>{{ old('isi', $article->isi) }}</textarea>
                <label class="form-label mt-3" for="gambar">Ganti gambar</label>@if($article->gambar)<img src="{{ $article->gambar }}" alt="{{ $article->judul }}" class="d-block rounded mb-2" width="140" height="90" style="object-fit:cover">@endif<input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"><small class="text-secondary">Kosongkan jika gambar tidak ingin diganti. Maksimal 5 MB.</small>
                <label class="form-label mt-3" for="status">Status</label><select id="status" name="status" class="form-select"><option value="DRAFT" @selected(old('status', $article->status) === 'DRAFT')>Draft</option><option value="PUBLISHED" @selected(old('status', $article->status) === 'PUBLISHED')>Published</option></select>
                <button class="btn btn-warning rounded-pill mt-4" type="submit">Simpan perubahan</button>
            </form>
        </div></div></div></div>
    </main>
</body>
</html>
