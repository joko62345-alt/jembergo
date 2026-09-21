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
            <article class="destination-card" data-destination-card data-name="{{ strtolower($destination->nama_wisata) }}" data-category="{{ $destination->kategori }}" data-status="{{ $destination->status_aktif === 'aktif' ? 'aktif' : 'nonaktif' }}">
                <div class="destination-card-media">
                    @if($destination->foto_utama)
                        <img src="{{ $destination->foto_utama }}" alt="Foto {{ $destination->nama_wisata }}">
                    @else
                        <div class="destination-placeholder"><i class="bi bi-image" aria-hidden="true"></i><span>Belum ada foto</span></div>
                    @endif
                    <span class="destination-status {{ $destination->status_aktif === 'aktif' ? 'is-active' : 'is-inactive' }}"><span aria-hidden="true"></span>{{ $destination->status_aktif === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="destination-card-body">
                    <span class="destination-category">{{ $destination->kategori }}</span>
                    <h2>{{ $destination->nama_wisata }}</h2>
                    <p><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $destination->alamat }}</p>
                    <div class="destination-card-actions">
                        <button type="button" class="btn btn-outline-dark" data-open-dialog="edit-destination-{{ $destination->id_destinasi }}" onclick="document.getElementById('edit-destination-{{ $destination->id_destinasi }}')?.showModal?.()"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit destinasi</button>
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
            <form method="POST" action="{{ route('superadmin.destinations.store') }}" enctype="multipart/form-data" class="destination-form" data-existing-names="{{ e(json_encode($destinationNames)) }}">@csrf
                @include('superadmin.partials.destination-form', ['destination' => null, 'formId' => 'create'])
                <div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><button class="btn btn-warning" type="submit"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan destinasi</button></div>
            </form>
        </div>
    </dialog>

    @foreach ($destinations as $destination)
        <dialog class="destination-dialog" id="edit-destination-{{ $destination->id_destinasi }}" aria-labelledby="edit-destination-title-{{ $destination->id_destinasi }}">
            <div class="dialog-panel dialog-panel-wide">
                <div class="dialog-header"><div><span class="page-kicker">Mode edit</span><h2 id="edit-destination-title-{{ $destination->id_destinasi }}">Edit destinasi</h2><p>{{ $destination->nama_wisata }}<br><small>Perbarui informasi destinasi yang dipilih.</small></p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                <form method="POST" action="{{ route('superadmin.destinations.update', $destination->id_destinasi) }}" enctype="multipart/form-data" class="destination-form" data-current-name="{{ $destination->nama_wisata }}" data-existing-names="{{ e(json_encode($destinationNames)) }}">@csrf @method('PUT')
                    @include('superadmin.partials.destination-form', ['destination' => $destination, 'formId' => 'edit-' . $destination->id_destinasi])
                    <div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><button class="btn btn-warning" type="submit"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan perubahan</button></div>
                </form>
            </div>
        </dialog>
    @endforeach

    <dialog class="destination-dialog destination-confirm-dialog" id="delete-destination-dialog" aria-labelledby="delete-destination-title">
        <div class="dialog-panel"><div class="dialog-header"><div><span class="page-kicker text-danger">Tindakan permanen</span><h2 id="delete-destination-title">Hapus destinasi?</h2><p>Anda akan menghapus <strong id="delete-destination-name"></strong>. Data yang sudah dihapus tidak dapat dikembalikan.</p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div><div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><form method="POST" id="delete-destination-form">@csrf @method('DELETE')<button class="btn btn-danger" type="submit"><i class="bi bi-trash3 me-1" aria-hidden="true"></i>Hapus destinasi</button></form></div></div>
    </dialog>

    <dialog class="destination-dialog destination-confirm-dialog" id="delete-ticket-dialog" aria-labelledby="delete-ticket-title">
        <div class="dialog-panel"><div class="dialog-header"><div><span class="page-kicker text-danger">Tindakan permanen</span><h2 id="delete-ticket-title">Hapus jenis tiket?</h2><p>Jenis tiket <strong id="delete-ticket-name"></strong> akan dihapus dari destinasi ini.</p></div><button type="button" class="dialog-close" data-close-dialog aria-label="Tutup dialog"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div><div class="dialog-actions"><button type="button" class="btn btn-outline-dark" data-close-dialog>Batal</button><form method="POST" id="delete-ticket-form">@csrf @method('DELETE')<button class="btn btn-danger" type="submit"><i class="bi bi-trash3 me-1" aria-hidden="true"></i>Hapus jenis tiket</button></form></div></div>
    </dialog>
@endsection

@push('scripts')
<script>
    (() => {
        const openDialog = (id) => {
            const dialog = document.getElementById(id);
            if (!dialog || dialog.open) return;
            if (typeof dialog.showModal === 'function') dialog.showModal();
            else dialog.setAttribute('open', '');
        };
        const closeDialog = (dialog) => {
            if (!dialog) return;
            if (typeof dialog.close === 'function') dialog.close();
            else dialog.removeAttribute('open');
        };

        document.addEventListener('click', (event) => { const button = event.target.closest('[data-open-dialog]'); if (button) openDialog(button.dataset.openDialog); });
        document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => closeDialog(button.closest('dialog'))));
        document.querySelectorAll('.destination-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => { if (event.target === dialog) closeDialog(dialog); }));
        const formatPriceInput = (input) => { const container = input.closest('.col-md-5'); const priceValue = input.closest('.input-group')?.querySelector('[data-price-value]'); const hasInvalidCharacters = /[^0-9.]/.test(input.value); let warning = container?.querySelector('.price-input-warning'); if (!warning && container) { warning = document.createElement('small'); warning.className = 'price-input-warning text-danger'; warning.textContent = 'Harga hanya boleh berisi angka.'; warning.hidden = true; container.appendChild(warning); } input.classList.toggle('is-invalid', hasInvalidCharacters); if (warning) warning.hidden = !hasInvalidCharacters; input.setCustomValidity(hasInvalidCharacters ? 'Harga hanya boleh berisi angka.' : ''); if (hasInvalidCharacters) { if (priceValue) priceValue.value = ''; return; } const rawValue = input.value.replace(/\D/g, ''); input.value = rawValue ? Number(rawValue).toLocaleString('id-ID') : ''; if (priceValue) priceValue.value = rawValue; };
        const showPriceWarning = (input) => { const container = input.closest('.col-md-5'); let warning = container?.querySelector('.price-input-warning'); if (!warning && container) { warning = document.createElement('small'); warning.className = 'price-input-warning text-danger'; warning.textContent = 'Harga tiket hanya boleh berisi angka.'; container.appendChild(warning); } input.classList.add('is-invalid'); input.setCustomValidity('Harga tiket hanya boleh berisi angka.'); if (warning) warning.hidden = false; };
        const bindPriceInput = (input) => { input.addEventListener('keydown', (event) => { if (event.key.length === 1 && !/[0-9]/.test(event.key)) { event.preventDefault(); showPriceWarning(input); } }); input.addEventListener('beforeinput', (event) => { if (event.data && /[^0-9.]/.test(event.data)) { event.preventDefault(); showPriceWarning(input); } }); input.addEventListener('paste', (event) => { const pastedValue = event.clipboardData?.getData('text') || ''; if (/[^0-9.]/.test(pastedValue)) { event.preventDefault(); showPriceWarning(input); } }); input.addEventListener('input', () => formatPriceInput(input)); };
        document.querySelectorAll('[data-price-display]').forEach((input) => bindPriceInput(input));
        document.querySelectorAll('.destination-form').forEach((form) => form.addEventListener('submit', (event) => { const invalidPrice = [...form.querySelectorAll('[data-price-display]')].some((input) => /[^0-9.]/.test(input.value)); if (invalidPrice) { event.preventDefault(); form.querySelectorAll('[data-price-display]').forEach((input) => { if (/[^0-9.]/.test(input.value)) { input.classList.add('is-invalid'); input.setCustomValidity('Harga tiket hanya boleh berisi angka.'); const warning = input.closest('.col-md-5')?.querySelector('.price-input-warning'); if (warning) warning.hidden = false; } }); return; } form.querySelectorAll('[data-price-display]').forEach((input) => formatPriceInput(input)); const button = form.querySelector('button[type="submit"]'); if (button) { button.disabled = true; button.dataset.originalLabel = button.innerHTML; button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan...'; } }));

        const deleteDialog = document.getElementById('delete-destination-dialog');
        const deleteName = document.getElementById('delete-destination-name');
        const deleteForm = document.getElementById('delete-destination-form');
        document.querySelectorAll('[data-delete-destination]').forEach((button) => button.addEventListener('click', () => { deleteName.textContent = button.dataset.deleteDestination; deleteForm.action = button.dataset.deleteAction; deleteDialog.showModal(); }));

        const deleteTicketDialog = document.getElementById('delete-ticket-dialog');
        const deleteTicketName = document.getElementById('delete-ticket-name');
        const deleteTicketForm = document.getElementById('delete-ticket-form');
        document.querySelectorAll('[data-delete-ticket]').forEach((button) => button.addEventListener('click', () => { deleteTicketName.textContent = button.dataset.deleteTicketName; deleteTicketForm.action = button.dataset.deleteTicketAction; deleteTicketDialog.showModal(); }));

        const search = document.getElementById('destination-search');
        const category = document.getElementById('destination-category-filter');
        const status = document.getElementById('destination-status-filter');
        const noResults = document.getElementById('destination-no-results');
        const normalizeFilterValue = (value) => String(value || '').trim().toLowerCase();
        const filterCards = () => { const query = normalizeFilterValue(search.value); const selectedCategory = normalizeFilterValue(category.value); const selectedStatus = normalizeFilterValue(status.value); let visible = 0; document.querySelectorAll('[data-destination-card]').forEach((card) => { const matches = (!query || normalizeFilterValue(card.dataset.name).includes(query)) && (!selectedCategory || normalizeFilterValue(card.dataset.category) === selectedCategory) && (!selectedStatus || normalizeFilterValue(card.dataset.status) === selectedStatus); card.hidden = !matches; if (matches) visible++; }); noResults.hidden = visible > 0; };
        [search, category, status].forEach((control) => { control.addEventListener('input', filterCards); control.addEventListener('change', filterCards); });
        filterCards();
        const normalizeDestinationName = (value) => String(value || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase();
        const existingDestinationNames = @js($destinationNames->values()->all());
        document.querySelectorAll('.destination-form').forEach((form) => {
            const nameInput = form.querySelector('.destination-name-input');
            const warning = form.querySelector('.destination-name-warning');
            const submitButton = form.querySelector('button[type="submit"]');
            const existingNames = existingDestinationNames.map(normalizeDestinationName);
            const currentName = normalizeDestinationName(form.dataset.currentName);
            const validateDestinationName = () => {
                const name = normalizeDestinationName(nameInput?.value);
                const isDuplicate = Boolean(name) && existingNames.includes(name) && name !== currentName;
                if (warning) {
                    warning.hidden = !isDuplicate;
                    warning.style.display = isDuplicate ? 'block' : 'none';
                }
                nameInput?.classList.toggle('is-invalid', isDuplicate);
                nameInput?.setCustomValidity(isDuplicate ? 'Destinasi sudah ada. Gunakan nama yang berbeda.' : '');
                if (submitButton && !submitButton.dataset.submitting) submitButton.disabled = isDuplicate;
            };
            nameInput?.addEventListener('input', validateDestinationName);
            form.addEventListener('submit', (event) => { validateDestinationName(); if (nameInput?.validity.customError) event.preventDefault(); });
            validateDestinationName();
        });
        document.querySelector('[data-dismiss-toast]')?.addEventListener('click', (event) => event.currentTarget.closest('.destination-toast').remove());
        document.querySelectorAll('.destination-form input[type="file"]').forEach((input) => input.addEventListener('change', () => { const preview = input.closest('.photo-upload')?.querySelector('img'); const file = input.files?.[0]; if (preview && file) preview.src = URL.createObjectURL(file); }));
            document.querySelectorAll('[data-repeater-add]').forEach((button) => button.addEventListener('click', () => {
                const list = document.querySelector(`[data-repeater="${button.dataset.repeaterAdd}"]`);
                const type = button.dataset.repeaterType;
                const index = list.querySelectorAll('.repeater-row, input[name="fasilitas[]"]').length;
                const row = document.createElement('div');
                row.className = type === 'facility' ? '' : (type === 'gallery' ? 'gallery-row repeater-row' : 'row g-2 mb-2 repeater-row');
                if (type === 'facility') row.innerHTML = `<input name="fasilitas[]" class="form-control mb-2" placeholder="Nama fasilitas">`;
                if (type === 'ticket') row.innerHTML = `<div class="col-md-5"><input name="jenis_tiket[${index}][nama_jenis]" class="form-control" placeholder="Masukkan Jenis Tiket" required></div><div class="col-md-5"><div class="input-group"><span class="input-group-text">Rp</span><input type="number" inputmode="numeric" min="0" step="1" class="form-control" data-price-display placeholder="15000" required><input type="hidden" name="jenis_tiket[${index}][harga]" data-price-value></div></div><div class="col-md-2 d-flex"><button type="button" class="btn btn-outline-danger w-100" data-remove-ticket-row aria-label="Hapus baris tiket"><i class="bi bi-trash3" aria-hidden="true"></i><span class="visually-hidden">Hapus</span></button></div>`;
                if (type === 'gallery') row.innerHTML = `<div class="gallery-row-preview"><span class="gallery-empty"><i class="bi bi-image" aria-hidden="true"></i>Foto baru</span></div><div class="gallery-row-fields"><div><label class="form-label" for="gallery-${index}-foto">File foto</label><input id="gallery-${index}-foto" name="galeri[${index}][foto]" type="file" accept="image/jpeg,image/png,image/webp" class="form-control"></div><div><label class="form-label" for="gallery-${index}-caption">Keterangan</label><input id="gallery-${index}-caption" name="galeri[${index}][keterangan]" class="form-control" placeholder="Masukkan Keterangan Foto"></div></div>`;
                list.appendChild(row);
                const newPriceInput = row.querySelector('[data-price-display]');
                if (newPriceInput) { newPriceInput.name = `jenis_tiket[${index}][harga_display]`; bindPriceInput(newPriceInput); }
            }));
            document.addEventListener('click', (event) => { const button = event.target.closest('[data-remove-ticket-row]'); if (button) button.closest('.repeater-row')?.remove(); });
    })();
</script>
@endpush
