@php
    $isEdit = (bool) $destination;
    $prefix = $formId;
    $ticketItems =
        $isEdit && $destination->jenisTiket->isNotEmpty() ? $destination->jenisTiket : [null];
    $facilityItems =
        $isEdit && $destination->fasilitas->isNotEmpty() ? $destination->fasilitas : [null];
    $galleryItems = $isEdit && $destination->galeri->isNotEmpty() ? $destination->galeri : [null];
    $operatingHours = old('jam_operasional', $destination?->jam_operasional);
    preg_match('/(\d{2}:\d{2}).*?(\d{2}:\d{2})/', (string) $operatingHours, $hourMatches);
    $openingTime = old('jam_buka', $hourMatches[1] ?? '08:00');
    $closingTime = old('jam_tutup', $hourMatches[2] ?? '17:00');
    $timeOptions = [];
    for ($hour = 0; $hour < 24; $hour++) {
        foreach ([0, 30] as $minute) {
            $timeOptions[] = sprintf('%02d:%02d', $hour, $minute);
        }
    }
    foreach ([$openingTime, $closingTime] as $selectedTime) {
        if ($selectedTime && !in_array($selectedTime, $timeOptions, true)) {
            $timeOptions[] = $selectedTime;
        }
    }
    sort($timeOptions);
@endphp
@once
    @push('head')
        <style>
            .operating-time-dropdown {
                position: relative;
            }

            .operating-time-menu {
                position: absolute;
                top: calc(100% + .35rem);
                left: 0;
                z-index: 20;
                display: none;
                width: 100%;
                max-height: 220px;
                overflow-y: auto;
                padding: .35rem;
                border: 1px solid rgba(148, 163, 184, .35);
                border-radius: .5rem;
                background: #17263d;
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .25);
            }

            .operating-time-dropdown.is-open .operating-time-menu {
                display: block;
            }

            .operating-time-option {
                display: block;
                width: 100%;
                min-height: 2rem;
                padding: .35rem .65rem;
                border: 0;
                border-radius: .3rem;
                background: transparent;
                color: #fff;
                font: inherit;
                text-align: left;
                cursor: pointer;
            }

            .operating-time-option:hover,
            .operating-time-option.is-selected {
                background: #2563eb;
            }

            .operating-time-dropdown .form-select.is-invalid {
                border-color: #dc3545;
                box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .15);
            }

            .operating-hours-error {
                display: block;
                margin-top: .45rem;
                color: #dc3545;
                font-size: .78rem;
            }
        </style>
    @endpush
@endonce
<div class="form-section">
    <div class="form-section-heading"><span class="form-section-number">01</span>
        <div>
            <h3>Informasi dasar</h3>
            <p>Identitas utama yang tampil di katalog wisata.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label" for="{{ $prefix }}-nama">Nama
                destinasi</label><input id="{{ $prefix }}-nama" name="nama_wisata"
                value="{{ old('nama_wisata', $destination?->nama_wisata) }}"
                class="form-control destination-name-input" placeholder="Masukkan Nama Destinasi"
                required>
            <div class="destination-name-warning" role="alert" aria-live="polite"
                @if (!$errors->has('nama_wisata')) hidden @endif><i
                    class="bi bi-exclamation-circle me-1" aria-hidden="true"></i><span>Destinasi
                    sudah ada. Gunakan nama yang berbeda.</span></div>
        </div>
        <div class="col-md-4"><label class="form-label"
                for="{{ $prefix }}-kategori">Kategori</label><select
                id="{{ $prefix }}-kategori" name="kategori" class="form-select" required>
                @foreach (['Alam', 'Bahari', 'Buatan'] as $category)
                    <option value="{{ $category }}" @selected(old('kategori', $destination?->kategori ?? 'Alam') === $category)>
                        {{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 photo-upload"><label class="form-label"
                for="{{ $prefix }}-foto">Foto utama</label>
            <div class="photo-upload-row">
                @if ($destination?->foto_utama)
                    <img src="{{ $destination->foto_utama }}"
                    alt="Foto {{ $destination->nama_wisata }}" class="photo-preview">@else<span
                        class="photo-preview photo-preview-empty"><i class="bi bi-image"
                            aria-hidden="true"></i><span>Belum ada foto</span></span>
                @endif
                <div>
                    <input id="{{ $prefix }}-foto" name="foto_utama" type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="form-control @error('foto_utama') is-invalid @enderror"
                        data-image-upload><small class="form-helper">JPG, PNG, atau WEBP, maksimal 5
                        MB. Foto lama tetap tersimpan sampai perubahan disimpan.</small><small
                        class="file-upload-warning text-danger" role="alert" aria-live="polite"
                        @error('foto_utama') @else hidden @enderror>
                        @error('foto_utama')
                            {{ $message }}
                        @else
                            File harus berupa JPG, PNG, atau WEBP dengan ukuran maksimal 5 MB.
                        @enderror
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-section">
    <div class="form-section-heading"><span class="form-section-number">02</span>
        <div>
            <h3>Lokasi</h3>
            <p>Bantu pengunjung menemukan destinasi dengan tepat.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-12"><label class="form-label"
                for="{{ $prefix }}-alamat">Alamat</label><input
                id="{{ $prefix }}-alamat" name="alamat"
                value="{{ old('alamat', $destination?->alamat) }}" class="form-control"
                placeholder="Masukkan Alamat Lengkap" required></div>
        <div class="col-md-6"><label class="form-label"
                for="{{ $prefix }}-latitude">Latitude</label><input
                id="{{ $prefix }}-latitude" name="latitude" type="number" step="any"
                value="{{ old('latitude', $destination?->latitude) }}" class="form-control"
                placeholder="Masukkan Latitude" required></div>
        <div class="col-md-6"><label class="form-label"
                for="{{ $prefix }}-longitude">Longitude</label><input
                id="{{ $prefix }}-longitude" name="longitude" type="number" step="any"
                value="{{ old('longitude', $destination?->longitude) }}" class="form-control"
                placeholder="Masukkan Longitude" required></div>
    </div>
</div>
<div class="form-section">
    <div class="form-section-heading"><span class="form-section-number">03</span>
        <div>
            <h3>Operasional</h3>
            <p>Informasi waktu kunjungan untuk pengunjung.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Jam operasional</label>
            <div class="row g-2 align-items-center">
                <div class="col-sm-5">
                    <div class="operating-time-dropdown" data-time-dropdown><input type="hidden"
                            name="jam_buka" value="{{ $openingTime }}" required><button
                            type="button" class="form-select text-start" data-time-trigger
                            aria-haspopup="listbox"
                            aria-expanded="false">{{ $openingTime }}</button>
                        <div class="operating-time-menu" role="listbox">
                            @foreach ($timeOptions as $time)
                                <button type="button"
                                    class="operating-time-option @if ($openingTime === $time) is-selected @endif"
                                    data-time-value="{{ $time }}" role="option"
                                    aria-selected="{{ $openingTime === $time ? 'true' : 'false' }}">{{ $time }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-sm-2 text-center text-secondary">sampai</div>
                <div class="col-sm-5">
                    <div class="operating-time-dropdown" data-time-dropdown><input type="hidden"
                            name="jam_tutup" value="{{ $closingTime }}" required><button
                            type="button" class="form-select text-start" data-time-trigger
                            aria-haspopup="listbox"
                            aria-expanded="false">{{ $closingTime }}</button>
                        <div class="operating-time-menu" role="listbox">
                            @foreach ($timeOptions as $time)
                                <button type="button"
                                    class="operating-time-option @if ($closingTime === $time) is-selected @endif"
                                    data-time-value="{{ $time }}" role="option"
                                    aria-selected="{{ $closingTime === $time ? 'true' : 'false' }}">{{ $time }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="operating-hours-error" role="alert" data-operating-hours-alert
                @if (!$errors->has('jam_tutup')) hidden @endif>
                {{ $errors->first('jam_tutup') ?: 'Jam tutup harus lebih besar dari jam buka.' }}
            </div><input type="hidden" name="jam_operasional" value="{{ $operatingHours }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch mb-2"><input id="{{ $prefix }}-status"
                    name="status_aktif" value="1" type="checkbox" class="form-check-input"
                    @checked(old('status_aktif', $destination ? $destination->status_aktif === 'aktif' : true))><label for="{{ $prefix }}-status"
                    class="form-check-label">Destinasi aktif</label></div>
        </div>
    </div>
</div>
<div class="form-section">
    <div class="form-section-heading"><span class="form-section-number">04</span>
        <div>
            <h3>Deskripsi dan fasilitas</h3>
            <p>Ceritakan pengalaman yang ditawarkan destinasi.</p>
        </div>
    </div>
    <label class="form-label" for="{{ $prefix }}-deskripsi">Deskripsi destinasi</label>
    <textarea id="{{ $prefix }}-deskripsi" name="deskripsi" rows="4"
        class="form-control mb-4" placeholder="Masukkan Deskripsi Destinasi" required>{{ old('deskripsi', $destination?->deskripsi) }}</textarea>
    <label class="form-label">Fasilitas</label><input type="hidden" name="fasilitas_present"
        value="1">
    <div class="repeater-list" data-repeater="facility-{{ $prefix }}">
        @foreach ($facilityItems as $facility)
            <input name="fasilitas[]" value="{{ $facility?->nama_fasilitas }}"
                class="form-control mb-2" placeholder="Masukkan Fasilitas yang ada di Destinasi">
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-dark repeater-add"
        data-repeater-add="facility-{{ $prefix }}" data-repeater-type="facility"><i
            class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah fasilitas</button>
</div>
<div class="form-section form-section-last">
    <div class="form-section-heading"><span class="form-section-number">05</span>
        <div>
            <h3>Tiket dan galeri</h3>
            <p>Kelola tiket masuk serta foto pendukung destinasi.</p>
        </div>
    </div>
    <label class="form-label">Jenis tiket dan harga</label>
    <div class="repeater-list" data-repeater="ticket-{{ $prefix }}">
        @foreach ($ticketItems as $ticket)
            <div class="row g-2 mb-2 repeater-row">
                @if ($ticket)
                    <input type="hidden" name="jenis_tiket[{{ $loop->index }}][id]"
                        value="{{ $ticket->id_jenis_tiket }}">
                @endif
                <div class="col-md-5">
                    <input name="jenis_tiket[{{ $loop->index }}][nama_jenis]"
                        value="{{ $ticket?->nama_jenis }}" class="form-control"
                        placeholder="Masukkan Jenis Tiket" required>
                </div>
                <div class="col-md-5">
                    <div class="input-group"><span class="input-group-text">Rp</span><input
                            type="number" inputmode="numeric" min="0" step="1"
                            name="jenis_tiket[{{ $loop->index }}][harga_display]"
                            value="{{ $ticket ? number_format((float) $ticket->harga, 0, ',', '.') : '' }}"
                            class="form-control" data-price-display
                            placeholder="Masukkan Harga Tiket" required><input type="hidden"
                            name="jenis_tiket[{{ $loop->index }}][harga]"
                            value="{{ $ticket?->harga }}" data-price-value></div>
                </div>
                <div class="col-md-2 d-flex">
                    @if ($ticket)
                        <button type="button" class="btn btn-outline-danger w-100"
                            data-delete-ticket data-delete-ticket-name="{{ $ticket->nama_jenis }}"
                            data-delete-ticket-action="{{ route('superadmin.destinations.ticket-types.destroy', [$destination->id_destinasi, $ticket->id_jenis_tiket]) }}"
                            aria-label="Hapus {{ $ticket->nama_jenis }}"><i class="bi bi-trash3"
                                aria-hidden="true"></i><span
                            class="visually-hidden">Hapus</span></button>@else<button
                            type="button" class="btn btn-outline-danger w-100"
                            data-remove-ticket-row aria-label="Hapus baris tiket"><i
                                class="bi bi-trash3" aria-hidden="true"></i><span
                                class="visually-hidden">Hapus</span></button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-dark repeater-add mb-4"
        data-repeater-add="ticket-{{ $prefix }}" data-repeater-type="ticket"><i
            class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah jenis tiket</button><small
        class="form-helper d-block mb-3"></small>
    <label class="form-label">Galeri foto</label>
    <div class="repeater-list gallery-repeater" data-repeater="gallery-{{ $prefix }}">
        @foreach ($galleryItems as $gallery)
            <div class="gallery-row repeater-row">
                @if ($gallery)
                    <input type="hidden" name="galeri[{{ $loop->index }}][id]"
                        value="{{ $gallery->id_galeri }}">
                @endif
                <div class="gallery-row-preview">
                    @if ($gallery?->url_foto)
                        <img src="{{ $gallery->url_foto }}"
                            alt="Galeri {{ $destination?->nama_wisata }}"
                        class="gallery-preview">@else<span class="gallery-empty"><i
                                class="bi bi-image" aria-hidden="true"></i>Foto baru</span>
                    @endif
                </div>
                <div class="gallery-row-fields">
                    <div><label class="form-label"
                            for="{{ $prefix }}-gallery-{{ $loop->index }}-foto">File
                            foto</label><input
                            id="{{ $prefix }}-gallery-{{ $loop->index }}-foto"
                            name="galeri[{{ $loop->index }}][foto]" type="file"
                            accept="image/jpeg,image/png,image/webp" class="form-control"></div>
                    <div><label class="form-label"
                            for="{{ $prefix }}-gallery-{{ $loop->index }}-caption">Keterangan</label><input
                            id="{{ $prefix }}-gallery-{{ $loop->index }}-caption"
                            name="galeri[{{ $loop->index }}][keterangan]"
                            value="{{ $gallery?->keterangan }}" class="form-control"
                            placeholder="Masukkan Keterangan Foto"></div>
                    @if ($gallery)
                        <button type="button"
                            class="btn btn-sm btn-outline-danger gallery-delete-button"
                            data-delete-gallery
                            data-delete-gallery-name="{{ $gallery->keterangan ?: 'foto ini' }}"
                            data-delete-gallery-action="{{ route('superadmin.destinations.galleries.destroy', [$destination->id_destinasi, $gallery->id_galeri]) }}"><i
                                class="bi bi-trash3" aria-hidden="true"></i><span
                            class="visually-hidden">Hapus foto</span></button>@else<button
                            type="button"
                            class="btn btn-sm btn-outline-danger gallery-delete-button"
                            data-remove-gallery-row aria-label="Hapus baris foto"><i
                                class="bi bi-trash3" aria-hidden="true"></i><span
                                class="visually-hidden">Hapus baris foto</span></button>
                    @endif
                </div>
            </div>
        @endforeach
    </div><button type="button" class="btn btn-sm btn-outline-dark repeater-add"
        data-repeater-add="gallery-{{ $prefix }}" data-repeater-type="gallery"><i
            class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah foto</button><small
        class="form-helper d-block mt-2"></small>
</div>
