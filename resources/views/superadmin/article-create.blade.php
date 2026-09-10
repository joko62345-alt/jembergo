@include('components.superadmin-sidebar')
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tulis Artikel | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <a href="{{ route('superadmin.management') }}" class="text-dark text-decoration-none">Kembali ke Management</a>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 fw-bold">Tulis artikel publik</h1>
                        <p class="text-secondary">Artikel berstatus Diterbitkan akan tampil di website publik dan customer.</p>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('superadmin.management.article') }}" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label mt-3" for="judul">Judul artikel</label>
                            <input id="judul" name="judul" value="{{ old('judul') }}" class="form-control" required>

                            <label class="form-label mt-3" for="isi">Isi artikel</label>
                            <textarea id="isi" name="isi" rows="10" class="form-control" required>{{ old('isi') }}</textarea>

                            <label class="form-label mt-3" for="gambar">Upload gambar</label>
                            <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
                            <small class="text-secondary">JPG, PNG, atau WEBP, maksimal 5 MB.</small>

                            <label class="form-label mt-3" for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="DRAFT" @selected(old('status', 'DRAFT') === 'DRAFT')>Draf</option>
                                <option value="PUBLISHED" @selected(old('status') === 'PUBLISHED')>Diterbitkan</option>
                            </select>

                            <button class="btn btn-warning rounded-pill mt-4" type="submit">Simpan artikel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>