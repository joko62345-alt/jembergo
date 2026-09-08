<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Destinasi | Super Admin JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light"><x-superadmin-sidebar />
<main class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('dashboard') }}" class="text-dark text-decoration-none">Dashboard</a>
            <h1 class="h2 fw-bold mt-2 mb-0">Kelola destinasi</h1>
        </div>
        <a href="{{ route('superadmin.management') }}" class="btn btn-outline-dark rounded-pill">Management</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold">Tambah destinasi</h2>
            <form method="POST" action="{{ route('superadmin.destinations.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-6"><label class="form-label" for="nama_wisata">Nama wisata</label><input id="nama_wisata" name="nama_wisata" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label" for="foto_utama">Foto utama</label><input id="foto_utama" name="foto_utama" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"><small class="text-secondary">JPG, PNG, WEBP, maksimal 5 MB.</small></div>
                <div class="col-md-6"><label class="form-label" for="kategori">Kategori</label><select id="kategori" name="kategori" class="form-select" required><option value="Alam">Alam</option><option value="Bahari">Bahari</option><option value="Buatan">Buatan</option></select></div>
                <div class="col-md-6"><label class="form-label" for="alamat">Alamat</label><input id="alamat" name="alamat" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label" for="latitude">Latitude</label><input id="latitude" name="latitude" type="number" step="any" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label" for="longitude">Longitude</label><input id="longitude" name="longitude" type="number" step="any" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label" for="jam_operasional">Jam operasional</label><input id="jam_operasional" name="jam_operasional" class="form-control" placeholder="08:00 - 17:00" required></div>
                <div class="col-md-6"><label class="form-label" for="deskripsi">Deskripsi</label><textarea id="deskripsi" name="deskripsi" class="form-control" required></textarea></div>
                <div class="col-12"><label class="form-label">Fasilitas</label><div id="new-facilities"><input name="fasilitas[]" class="form-control mb-2" placeholder="Contoh: Parkir"></div><button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="addFacility('new-facilities')">Tambah fasilitas</button></div>
                <div class="col-12"><label class="form-label">Galeri foto</label><div id="new-gallery"><div class="row g-2 mb-2"><div class="col-md-7"><input name="galeri[0][foto]" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"></div><div class="col-md-5"><input name="galeri[0][keterangan]" class="form-control" placeholder="Keterangan"></div></div></div><button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="addGallery('new-gallery')">Tambah foto</button><small class="d-block text-secondary mt-1">JPG, PNG, atau WEBP, maksimal 5 MB per foto.</small></div>
                <div class="col-12"><input type="hidden" name="status_aktif" value="1"><button class="btn btn-warning rounded-pill" type="submit">Simpan destinasi</button></div>
            </form>
        </div>
    </section>

    <section class="row g-4">
        @forelse ($destinations as $destination)
            <div class="col-lg-6">
                <article class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <span class="badge text-bg-warning rounded-pill">{{ $destination->kategori }}</span>
                                <h2 class="h5 fw-bold mt-2">{{ $destination->nama_wisata }}</h2>
                                <p class="small text-secondary mb-0">{{ $destination->alamat }}</p>
                            </div>
                            <span class="badge {{ $destination->status_aktif ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $destination->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>

                        <details class="mt-4">
                            <summary class="text-warning fw-semibold">Edit destinasi</summary>
                            <form method="POST" action="{{ route('superadmin.destinations.update', $destination->id_destinasi) }}" enctype="multipart/form-data" class="row g-2 mt-2">
                                @csrf
                                @method('PUT')
                                <div class="col-md-6"><input name="nama_wisata" value="{{ $destination->nama_wisata }}" class="form-control" required></div>
                                <div class="col-md-6">@if($destination->foto_utama)<img src="{{ $destination->foto_utama }}" width="80" height="60" class="rounded mb-2" style="object-fit:cover" alt="Foto utama"><br>@endif<input name="foto_utama" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"><small class="text-secondary">Kosongkan jika tidak ingin mengganti foto utama.</small></div>
                                <div class="col-md-6"><select name="kategori" class="form-select" required>@foreach (['Alam', 'Bahari', 'Buatan'] as $category)<option value="{{ $category }}" @selected($destination->kategori === $category)>{{ $category }}</option>@endforeach</select></div>
                                <div class="col-12"><input name="alamat" value="{{ $destination->alamat }}" class="form-control" required></div>
                                <div class="col-6"><input name="latitude" type="number" step="any" value="{{ $destination->latitude }}" class="form-control" required></div>
                                <div class="col-6"><input name="longitude" type="number" step="any" value="{{ $destination->longitude }}" class="form-control" required></div>
                                <div class="col-12"><input name="jam_operasional" value="{{ $destination->jam_operasional }}" class="form-control" required></div>
                                <div class="col-12"><textarea name="deskripsi" class="form-control" required>{{ $destination->deskripsi }}</textarea></div>
                                <div class="col-12"><label class="form-label">Fasilitas</label><div id="facilities-{{ $destination->id_destinasi }}">@forelse($destination->fasilitas as $facility)<input name="fasilitas[]" value="{{ $facility->nama_fasilitas }}" class="form-control mb-2">@empty<input name="fasilitas[]" class="form-control mb-2" placeholder="Contoh: Parkir">@endforelse</div><button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="addFacility('facilities-{{ $destination->id_destinasi }}')">Tambah fasilitas</button></div>
                                <div class="col-12"><label class="form-label">Galeri foto baru</label><div id="gallery-{{ $destination->id_destinasi }}">@forelse($destination->galeri as $gallery)<div class="row g-2 mb-2"><div class="col-md-7"><img src="{{ $gallery->url_foto }}" class="rounded mb-2" width="80" height="60" style="object-fit:cover" alt="Galeri"><input name="galeri[{{ $loop->index }}][foto]" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"></div><div class="col-md-5"><input name="galeri[{{ $loop->index }}][keterangan]" value="{{ $gallery->keterangan }}" class="form-control" placeholder="Keterangan"></div></div>@empty<div class="row g-2 mb-2"><div class="col-md-7"><input name="galeri[0][foto]" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"></div><div class="col-md-5"><input name="galeri[0][keterangan]" class="form-control" placeholder="Keterangan"></div></div>@endforelse</div><button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="addGallery('gallery-{{ $destination->id_destinasi }}')">Tambah foto</button></div>
                                <div class="col-12"><div class="form-check"><input id="active-{{ $destination->id_destinasi }}" name="status_aktif" value="1" type="checkbox" class="form-check-input" @checked($destination->status_aktif)><label for="active-{{ $destination->id_destinasi }}" class="form-check-label">Destinasi aktif</label></div></div>
                                <div class="col-12"><button class="btn btn-warning rounded-pill btn-sm" type="submit">Simpan perubahan</button></div>
                            </form>
                        </details>

                        <div class="d-flex justify-content-end mt-3">
                            <form method="POST" action="{{ route('superadmin.destinations.destroy', $destination->id_destinasi) }}" onsubmit="return confirm('Hapus destinasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm rounded-pill" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-light">Belum ada destinasi.</div></div>
        @endforelse
    </section>

    <div class="mt-4">{{ $destinations->links() }}</div>
</main>
<script>
let galleryIndex = 1;
function addFacility(id) { const box = document.getElementById(id); const input = document.createElement('input'); input.name = 'fasilitas[]'; input.className = 'form-control mb-2'; input.placeholder = 'Nama fasilitas'; box.appendChild(input); }
function addGallery(id) { const box = document.getElementById(id); const row = document.createElement('div'); row.className = 'row g-2 mb-2'; row.innerHTML = `<div class="col-md-7"><input name="galeri[${galleryIndex}][foto]" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"></div><div class="col-md-5"><input name="galeri[${galleryIndex}][keterangan]" class="form-control" placeholder="Keterangan"></div>`; box.appendChild(row); galleryIndex++; }
</script>
</body>
</html>
