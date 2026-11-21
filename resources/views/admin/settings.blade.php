<x-admin-layout title="Pengaturan Admin Pariwisata" active="settings">
    @php($quotaActive = old('kuota_harian_aktif', $destination->kuota_harian_aktif === 'aktif'))

    <div class="admin-dashboard-heading">
        <div>
            <span class="admin-kicker">Akun & destinasi</span>
            <h1>Pengaturan</h1>
            <p>Kelola pengaturan destinasi, akun, dan keamanan {{ $destination->nama_wisata }}.</p>
        </div>
        <span class="admin-destination-chip"><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $destination->nama_wisata }}</span>
    </div>

    @if (session('password_success'))
        <div class="alert alert-success">{{ session('password_success') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="admin-settings-grid">
        <section class="admin-panel admin-settings-quota">
            <div class="admin-panel-heading">
                <div>
                    <span class="admin-kicker">Pengaturan destinasi</span>
                    <h2>Kuota tiket harian</h2>
                    <p>Atur batas maksimum tiket yang dapat dijual setiap hari.</p>
                </div>
                <span class="admin-settings-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
            </div>
            <form method="POST" action="{{ route('admin.quota.update') }}" class="admin-settings-form admin-quota-form">
                @csrf
                @method('PUT')
                <div class="admin-quota-controls">
                    <div class="admin-quota-field">
                        <label class="form-label" for="kuota_harian">Maksimal peserta per hari</label>
                        <div class="admin-quota-input-wrap">
                            <input class="form-control @error('kuota_harian') is-invalid @enderror"
                                type="text" id="kuota_harian" name="kuota_harian"
                                inputmode="numeric" pattern="[1-9][0-9]{0,4}|100000" maxlength="6"
                                value="{{ old('kuota_harian', $destination->kuota_harian) }}"
                                placeholder="Contoh: 100"
                                @disabled(!$quotaActive) @required($quotaActive)>
                            <span>tiket / hari</span>
                        </div>
                        @error('kuota_harian')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <label class="admin-quota-toggle" for="kuota_harian_aktif">
                        <span class="admin-quota-toggle-icon">
                            <i class="bi bi-calendar2-check" aria-hidden="true"></i>
                        </span>
                        <span class="admin-quota-toggle-copy">
                            <strong>Terapkan kuota harian</strong>
                            <small>Batasi jumlah tiket yang dapat terjual per hari.</small>
                        </span>
                        <span class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="kuota_harian_aktif" name="kuota_harian_aktif" value="1"
                                aria-label="Terapkan kuota harian"
                                @checked($quotaActive)>
                        </span>
                    </label>
                </div>
                <div class="admin-quota-actions">
                    <span><i class="bi bi-info-circle" aria-hidden="true"></i> Berlaku untuk pembelian online dan loket</span>
                    <button class="btn btn-warning admin-quota-submit" type="submit">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan kuota
                    </button>
                </div>
            </form>
        </section>

        <section class="admin-panel admin-settings-account-security">
            <div class="admin-panel-heading">
                <div>
                    <span class="admin-kicker">Akun & keamanan</span>
                    <h2>Profil dan keamanan akun</h2>
                    <p>Informasi akun Admin Pariwisata dan pengelolaan password.</p>
                </div>
                <span class="admin-settings-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
            </div>
            <div class="admin-settings-account-security-grid">
                <section class="admin-settings-profile">
                    <h3 class="admin-settings-subheading">Profil </h3>
                    <p class="admin-settings-description">Informasi akun yang ditetapkan untuk destinasi ini.</p>
                    <dl class="admin-account-data">
                        <div><dt>Nama admin</dt><dd>{{ $admin->nama }}</dd></div>
                        <div><dt>Email</dt><dd>{{ $admin->email }}</dd></div>
                        <div><dt>Nomor telepon</dt><dd>{{ $admin->no_hp }}</dd></div>
                        <div><dt>Status akun</dt><dd><span class="admin-account-status">{{ $admin->status_akun }}</span></dd></div>
                        <div class="admin-account-destination">
                            <dt>Destinasi dikelola</dt>
                            <dd>
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <span>
                                    <strong>{{ $destination->nama_wisata }}</strong>
                                    <small>{{ $destination->alamat }}</small>
                                </span>
                            </dd>
                        </div>
                    </dl>
                </section>
                <section class="admin-settings-security">
                    <div class="admin-settings-security-heading">
                        <span class="admin-settings-security-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="admin-settings-subheading">Keamanan akun</h3>
                            <p class="admin-settings-description">Perbarui password untuk menjaga keamanan akun.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.password') }}"
                        class="admin-settings-form admin-password-form">
                        @csrf
                        @method('PUT')
                        <div class="admin-settings-field">
                            <label class="form-label" for="current_password">Password saat ini</label>
                            <div class="admin-password-input">
                                <input class="form-control @error('current_password') is-invalid @enderror"
                                    type="password" id="current_password" name="current_password"
                                    autocomplete="current-password" required>
                                <button class="admin-password-toggle" type="button"
                                    data-password-toggle data-password-label="password saat ini"
                                    aria-label="Tampilkan password saat ini" aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="admin-settings-field">
                            <label class="form-label" for="new_password">Password baru</label>
                            <div class="admin-password-input">
                                <input class="form-control @error('new_password') is-invalid @enderror"
                                    type="password" id="new_password" name="new_password"
                                    autocomplete="new-password" minlength="8" maxlength="128" required>
                                <button class="admin-password-toggle" type="button"
                                    data-password-toggle data-password-label="password baru"
                                    aria-label="Tampilkan password baru" aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="admin-settings-field-hint">Minimal 8 karakter.</small>
                        </div>
                        <div class="admin-settings-field">
                            <label class="form-label" for="new_password_confirmation">Konfirmasi password baru</label>
                            <div class="admin-password-input">
                                <input class="form-control" type="password" id="new_password_confirmation"
                                    name="new_password_confirmation" autocomplete="new-password"
                                    minlength="8" maxlength="128" required>
                                <button class="admin-password-toggle" type="button"
                                    data-password-toggle data-password-label="konfirmasi password baru"
                                    aria-label="Tampilkan konfirmasi password baru" aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-warning admin-password-submit" type="submit">
                            <i class="bi bi-key me-1" aria-hidden="true"></i>Simpan password baru
                        </button>
                    </form>
                </section>
            </div>
        </section>
    </div>

    <style>
        .admin-settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }

        .admin-settings-account-security-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
            gap: 1.75rem;
            align-items: start;
        }

        .admin-settings-subheading {
            margin: 0 0 .25rem;
            color: var(--jg-text);
            font-size: .95rem;
            font-weight: 700;
        }

        .admin-settings-description {
            margin: 0 0 1rem;
            color: var(--jg-muted);
            font-size: .78rem;
        }

        .admin-settings-icon {
            display: grid;
            flex: 0 0 auto;
            width: 2.5rem;
            height: 2.5rem;
            place-items: center;
            border-radius: .75rem;
            background: var(--jg-orange-soft);
            color: var(--jg-orange);
            font-size: 1.1rem;
        }

        .admin-account-data {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 1.25rem;
            margin: .25rem 0 1rem;
            border-top: 1px solid var(--jg-border);
        }

        .admin-account-data>div {
            min-width: 0;
            padding: .75rem .15rem;
            border-bottom: 1px solid var(--jg-border);
        }

        .admin-account-data dt {
            margin-bottom: .3rem;
            color: var(--jg-muted);
            font-size: .72rem;
            font-weight: 600;
        }

        .admin-account-data dd {
            margin: 0;
            overflow-wrap: anywhere;
            color: var(--jg-text);
            font-size: .86rem;
            font-weight: 700;
        }

        .admin-account-status {
            display: inline-flex;
            padding: .2rem .55rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--jg-success) 12%, var(--jg-card));
            color: var(--jg-success);
            font-size: .72rem;
            font-weight: 700;
        }

        .admin-settings-security-icon {
            display: grid;
            flex: 0 0 auto;
            width: 2.5rem;
            height: 2.5rem;
            place-items: center;
            border-radius: .7rem;
            background: var(--jg-orange-soft);
            color: var(--jg-orange);
        }

        .admin-account-destination {
            grid-column: 1 / -1;
        }

        .admin-account-destination dd {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        .admin-account-destination dd>i {
            margin-top: .1rem;
            color: var(--jg-orange);
        }

        .admin-account-destination dd>span {
            display: grid;
            gap: .15rem;
        }

        .admin-account-destination dd strong {
            color: var(--jg-text);
            font-size: .84rem;
        }

        .admin-account-destination dd small {
            color: var(--jg-muted);
            font-size: .74rem;
        }

        .admin-settings-security {
            padding: 1rem;
            border: 1px solid var(--jg-border);
            border-radius: .85rem;
            background: var(--jg-bg);
        }

        .admin-settings-security-heading {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding-bottom: .85rem;
            margin-bottom: .9rem;
            border-bottom: 1px solid var(--jg-border);
        }

        .admin-settings-security-heading .admin-settings-description {
            margin: .2rem 0 0;
        }

        .admin-settings-security-heading .admin-settings-subheading {
            margin: 0;
        }

        .admin-settings-field {
            display: grid;
            gap: .4rem;
        }

        .admin-settings-field-hint {
            color: var(--jg-muted);
            font-size: .72rem;
        }

        .admin-password-input {
            position: relative;
        }

        .admin-password-input .form-control {
            padding-right: 3.1rem;
        }

        .admin-password-input .form-control.is-invalid {
            background-position: right 2.8rem center;
        }

        .admin-password-toggle {
            position: absolute;
            top: 50%;
            right: .4rem;
            display: grid;
            width: 2.25rem;
            height: 2.25rem;
            place-items: center;
            padding: 0;
            border: 0;
            border-radius: .5rem;
            background: transparent;
            color: var(--jg-muted);
            transform: translateY(-50%);
        }

        .admin-password-toggle:hover {
            background: var(--jg-orange-soft);
            color: var(--jg-orange);
        }

        .admin-password-toggle:focus-visible {
            outline: 2px solid var(--jg-orange);
            outline-offset: 1px;
        }

        .admin-password-submit {
            justify-self: end;
            width: auto;
            padding-inline: 1rem;
        }

        .admin-settings-form {
            display: grid;
            gap: 1rem;
        }

        .admin-quota-form {
            gap: 1.1rem;
        }

        .admin-quota-controls {
            display: grid;
            grid-template-columns: minmax(240px, .8fr) minmax(0, 1.2fr);
            gap: 1.25rem;
            align-items: stretch;
        }

        .admin-quota-field {
            display: grid;
            align-content: start;
        }

        .admin-quota-input-wrap {
            position: relative;
        }

        .admin-quota-input-wrap .form-control {
            height: 3rem;
            padding-right: 6rem;
            font-size: .95rem;
            font-weight: 650;
        }

        .admin-quota-input-wrap .form-control:disabled {
            background: var(--jg-bg);
            color: var(--jg-muted);
            opacity: .8;
        }

        .admin-quota-input-wrap>span {
            position: absolute;
            top: 50%;
            right: .85rem;
            color: var(--jg-muted);
            font-size: .72rem;
            font-weight: 650;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .admin-quota-submit {
            width: auto;
            min-height: 2.65rem;
            padding: .6rem 1rem;
            border-radius: .65rem;
            font-size: .78rem;
            font-weight: 700;
        }

        .admin-quota-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-top: .9rem;
            border-top: 1px solid var(--jg-border);
        }

        .admin-quota-actions>span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--jg-muted);
            font-size: .72rem;
        }

        .admin-quota-actions>span i {
            color: var(--jg-orange);
        }

        .admin-settings-form .form-label {
            color: var(--jg-text);
            font-size: .8rem;
            font-weight: 650;
        }

        .admin-settings-form .form-control {
            min-height: 2.8rem;
            border-color: var(--jg-border);
            background-color: var(--jg-card);
            color: var(--jg-text);
        }

        .admin-quota-toggle {
            display: grid;
            grid-template-columns: 2.5rem minmax(0, 1fr) auto;
            align-items: center;
            gap: .85rem;
            min-height: 4.7rem;
            padding: .8rem 1rem;
            border: 1px solid var(--jg-border);
            border-radius: .8rem;
            background: var(--jg-bg);
            cursor: pointer;
            transition: border-color .16s ease, background-color .16s ease;
        }

        .admin-quota-toggle:hover {
            border-color: color-mix(in srgb, var(--jg-orange) 48%, var(--jg-border));
        }

        .admin-quota-toggle:focus-within {
            border-color: var(--jg-orange);
            outline: 2px solid color-mix(in srgb, var(--jg-orange) 16%, transparent);
        }

        .admin-quota-toggle-icon {
            display: grid;
            width: 2.5rem;
            height: 2.5rem;
            place-items: center;
            border-radius: .7rem;
            background: var(--jg-orange-soft);
            color: var(--jg-orange);
            font-size: 1rem;
        }

        .admin-quota-toggle-copy {
            display: grid;
            gap: .25rem;
        }

        .admin-quota-toggle-copy strong {
            color: var(--jg-text);
            font-size: .82rem;
        }

        .admin-quota-toggle-copy small {
            color: var(--jg-muted);
            font-size: .75rem;
            line-height: 1.4;
        }

        .admin-quota-toggle .form-check-input {
            float: none;
            width: 2.35rem;
            height: 1.3rem;
            margin: 0;
            cursor: pointer;
        }

        .admin-quota-toggle .form-check-input:checked {
            border-color: var(--jg-orange);
            background-color: var(--jg-orange);
        }

        .admin-quota-toggle .form-check-input:focus {
            border-color: var(--jg-orange);
            box-shadow: 0 0 0 .2rem color-mix(in srgb, var(--jg-orange) 22%, transparent);
        }

        @media (max-width: 900px) {
            .admin-settings-account-security-grid,
            .admin-quota-controls {
                grid-template-columns: 1fr;
            }

            .admin-quota-toggle {
                min-height: 4.25rem;
            }
        }

        @media (max-width: 575px) {
            .admin-account-data {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575px) {
            .admin-quota-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .admin-quota-submit {
                align-self: flex-end;
            }
        }
    </style>

    <script>
        const quotaToggle = document.getElementById('kuota_harian_aktif');
        const quotaInput = document.getElementById('kuota_harian');

        const syncQuotaInput = () => {
            quotaInput.disabled = !quotaToggle.checked;
            quotaInput.required = quotaToggle.checked;
        };

        quotaInput.addEventListener('input', () => {
            quotaInput.value = quotaInput.value.replace(/\D/g, '');
        });
        quotaToggle?.addEventListener('change', () => {
            syncQuotaInput();
        });
        syncQuotaInput();

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.closest('.admin-password-input').querySelector('input');
                const icon = button.querySelector('i');
                const visible = input.type === 'password';

                input.type = visible ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(visible));
                button.setAttribute(
                    'aria-label',
                    `${visible ? 'Sembunyikan' : 'Tampilkan'} ${button.dataset.passwordLabel}`,
                );
                icon.classList.toggle('bi-eye', !visible);
                icon.classList.toggle('bi-eye-slash', visible);
            });
        });
    </script>
</x-admin-layout>
