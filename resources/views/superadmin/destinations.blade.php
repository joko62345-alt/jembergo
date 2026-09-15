@extends('layouts.superadmin')
@section('title', 'Destinasi')
@section('page_label', 'Destinasi wisata')
@section('content')
    <div class="page-heading destination-heading">
        <div>
            <span class="page-kicker">Katalog pariwisata</span>
            <h1>Destinasi wisata</h1>
            <p>Kelola informasi destinasi pariwisata Jember dalam satu tempat.</p>
        </div>
        <button type="button" class="btn btn-warning" data-open-dialog="create-destination-dialog"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah destinasi</button>
    </div>

    @if (session('success'))
        <div class="destination-toast" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ session('success') }}</span><button type="button" class="toast-close" data-dismiss-toast aria-label="Tutup notifikasi"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
    @endif

    <section class="destination-toolbar" aria-label="Pencarian destinasi">
        <div class="search-field"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="destination-search" placeholder="Cari destinasi..." aria-label="Cari destinasi"></div>
        <select id="destination-category-filter" class="form-select" aria-label="Filter kategori"><option value="">Semua kategori</option><option value="Alam">Alam</option><option value="Bahari">Bahari</option><option value="Buatan">Buatan</option></select>
        <select id="destination-status-filter" class="form-select" aria-label="Filter status"><option value="">Semua status</option><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select>
    </section>

    <section class="destination-grid" id="destination-grid" aria-label="Daftar destinasi">
        @forelse ($destinations as $destination)
            <article class="destination-card" data-destination-card data-name="{{ strtolower($destination->nama_wisata) }}" data-category="{{ $destination->kategori }}" data-status="{{ $destination->status_aktif ? 'aktif' : 'nonaktif' }}">
                <div class="destination-card-media">
                    @if($destination->foto_utama)
                        <img src="{{ $destination->foto_utama }}" alt="Foto {{ $destination->nama_wisata }}">
                    @else
                        <div class="destination-placeholder"><i class="bi bi-image" aria-hidden="true"></i><span>Belum ada foto</span></div>
                    @endif
                    <span class="destination-status {{ $destination->status_aktif ? 'is-active' : 'is-inactive' }}"><span aria-hidden="true"></span>{{ $destination->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="destination-card-body">
                    <span class="destination-category">{{ $destination->kategori }}</span>
                    <h2>{{ $destination->nama_wisata }}</h2>
                    <p><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $destination->alamat }}</p>
                    <div class="destination-card-actions">
                        <button type="button" class="btn btn-outline-dark" data-open-dialog="edit-destination-{{ $destination->id_destinasi }}"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit destinasi</button>
                        <button type="button" class="btn btn-outline-danger" data-delete-destination="{{ $destination->nama_wisata }}" data-delete-action="{{ route('superadmin.destinations.destroy', $destination->id_destinasi) }}"><i class="bi bi-trash3 me-1" aria-hidden="true"></i>Hapus</button>
                    </div>
                </div>
            </article>
        @empty
            <div class="destination-empty"><i class="bi bi-map" aria-hidden="true"></i><h2>Belum ada destinasi</h2><p>Tambahkan destinasi pertama untuk mulai mengelola katalog wisata Jember.</p><button type="button" class="btn btn-warning" data-open-dialog="create-destination-dialog">Tambah destinasi</button></div>
        @endforelse
    </section>
    <p class="destination-no-results" id="destination-no-results" hidden>Tidak ada destinasi yang sesuai dengan pencarian.</p>
    <div class="mt-4">{{ $destinations->links() }}</div>

    <dialog class="destination-dialog" id="create-destination-dialog" aria-labelledby="create-destination-title">
        <div class="dialog-panel dialog-panel-wide">
            <div class="dialog-header"><div><span class="page-kicker">Katalog pariwisata</span><h2 id="create-destination-title">Tambah destinasi</h2><p>Isi informasi destinasi baru untuk katalog JemberGo.</p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
            <form method="POST" action="{{ route('superadmin.destinations.store') }}" enctype="multipart/form-data" class="destination-form">@csrf
                @include('superadmin.partials.destination-form', ['destination' => null, 'formId' => 'create'])
                <div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><button class="btn btn-warning" type="submit"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan destinasi</button></div>
            </form>
        </div>
    </dialog>

    @foreach ($destinations as $destination)
        <dialog class="destination-dialog" id="edit-destination-{{ $destination->id_destinasi }}" aria-labelledby="edit-destination-title-{{ $destination->id_destinasi }}">
            <div class="dialog-panel dialog-panel-wide">
                <div class="dialog-header"><div><span class="page-kicker">Mode edit</span><h2 id="edit-destination-title-{{ $destination->id_destinasi }}">Edit destinasi</h2><p>{{ $destination->nama_wisata }}<br><small>Perbarui informasi destinasi yang dipilih.</small></p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                <form method="POST" action="{{ route('superadmin.destinations.update', $destination->id_destinasi) }}" enctype="multipart/form-data" class="destination-form">@csrf @method('PUT')
                    @include('superadmin.partials.destination-form', ['destination' => $destination, 'formId' => 'edit-' . $destination->id_destinasi])
                    <div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><button class="btn btn-warning" type="submit"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan perubahan</button></div>
                </form>
            </div>
        </dialog>
    @endforeach

    <dialog class="destination-dialog destination-confirm-dialog" id="delete-destination-dialog" aria-labelledby="delete-destination-title">
        <div class="dialog-panel"><div class="dialog-header"><div><span class="page-kicker text-danger">Tindakan permanen</span><h2 id="delete-destination-title">Hapus destinasi?</h2><p>Anda akan menghapus <strong id="delete-destination-name"></strong>. Data yang sudah dihapus tidak dapat dikembalikan.</p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div><div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><form method="POST" id="delete-destination-form">@csrf @method('DELETE')<button class="btn btn-danger" type="submit"><i class="bi bi-trash3 me-1" aria-hidden="true"></i>Hapus destinasi</button></form></div></div>
    </dialog>
@endsection

@push('scripts')
<script>
    (() => {
        const openDialog = (id) => document.getElementById(id)?.showModal();
        const closeDialog = (dialog) => dialog?.close();

        document.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => openDialog(button.dataset.openDialog)));
        document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => closeDialog(button.closest('dialog'))));
        document.querySelectorAll('.destination-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => { if (event.target === dialog) closeDialog(dialog); }));
        document.querySelectorAll('.destination-form').forEach((form) => form.addEventListener('submit', () => { const button = form.querySelector('button[type="submit"]'); if (button) { button.disabled = true; button.dataset.originalLabel = button.innerHTML; button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan...'; } }));

        const deleteDialog = document.getElementById('delete-destination-dialog');
        const deleteName = document.getElementById('delete-destination-name');
        const deleteForm = document.getElementById('delete-destination-form');
        document.querySelectorAll('[data-delete-destination]').forEach((button) => button.addEventListener('click', () => { deleteName.textContent = button.dataset.deleteDestination; deleteForm.action = button.dataset.deleteAction; deleteDialog.showModal(); }));

        const search = document.getElementById('destination-search');
        const category = document.getElementById('destination-category-filter');
        const status = document.getElementById('destination-status-filter');
        const noResults = document.getElementById('destination-no-results');
        const filterCards = () => { const query = search.value.toLowerCase().trim(); let visible = 0; document.querySelectorAll('[data-destination-card]').forEach((card) => { const matches = (!query || card.dataset.name.includes(query)) && (!category.value || card.dataset.category === category.value) && (!status.value || card.dataset.status === status.value); card.hidden = !matches; if (matches) visible++; }); noResults.hidden = visible > 0; };
        [search, category, status].forEach((control) => control.addEventListener('input', filterCards));
        document.querySelector('[data-dismiss-toast]')?.addEventListener('click', (event) => event.currentTarget.closest('.destination-toast').remove());
        document.querySelectorAll('.destination-form input[type="file"]').forEach((input) => input.addEventListener('change', () => { const preview = input.closest('.photo-upload')?.querySelector('img'); const file = input.files?.[0]; if (preview && file) preview.src = URL.createObjectURL(file); }));
            document.querySelectorAll('[data-repeater-add]').forEach((button) => button.addEventListener('click', () => {
                const list = document.querySelector(`[data-repeater="${button.dataset.repeaterAdd}"]`);
                const type = button.dataset.repeaterType;
                const index = list.querySelectorAll('.repeater-row, input[name="fasilitas[]"]').length;
                const row = document.createElement('div');
                row.className = type === 'facility' ? '' : (type === 'gallery' ? 'gallery-row repeater-row' : 'row g-2 mb-2 repeater-row');
                if (type === 'facility') row.innerHTML = `<input name="fasilitas[]" class="form-control mb-2" placeholder="Nama fasilitas">`;
                if (type === 'ticket') row.innerHTML = `<div class="col-md-6"><input name="jenis_tiket[${index}][nama_jenis]" class="form-control" placeholder="Contoh: Tiket Dewasa" required></div><div class="col-md-6"><div class="input-group"><span class="input-group-text">Rp</span><input name="jenis_tiket[${index}][harga]" type="number" min="0" step="0.01" class="form-control" placeholder="Harga" required></div></div>`;
                if (type === 'gallery') row.innerHTML = `<div class="gallery-row-preview"><span class="gallery-empty"><i class="bi bi-image" aria-hidden="true"></i>Foto baru</span></div><div class="gallery-row-fields"><div><label class="form-label" for="gallery-${index}-foto">File foto</label><input id="gallery-${index}-foto" name="galeri[${index}][foto]" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"></div><div><label class="form-label" for="gallery-${index}-caption">Keterangan</label><input id="gallery-${index}-caption" name="galeri[${index}][keterangan]" class="form-control" placeholder="Contoh: Area taman utama"></div></div>`;
                list.appendChild(row);
            }));
    })();
</script>
@endpush
