@extends('layouts.app')

@section('title', 'Pemesanan Tiket - JemberGo')

@section('content')
    @include('components.public-navbar')

    <main class="section-pad booking-page"
        style="padding-top: 8rem; background: var(--jg-bg); min-height: 100vh;">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"
                            class="text-decoration-none">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('destinations.index') }}"
                            class="text-decoration-none">Destinasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pemesanan</li>
                </ol>
            </nav>

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="customer-card">
                        <div class="card-body p-4 p-lg-5">
                            <div class="page-intro mb-4">
                                <h1 class="booking-title mt-3">Data ketua dan anggota</h1>
                                <p class="text-secondary mb-0">Maksimal 10 orang termasuk ketua. Setiap
                                    peserta dapat memilih jenis tiketnya sendiri.</p>
                            </div>

                            <form
                                action="{{ route('customer.booking.store', $destination->id_destinasi) }}"
                                method="POST" id="bookingForm">
                                @csrf

                                <section class="booking-section mb-4">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <h2 class="h5 mb-0">Ketua kelompok</h2>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label"
                                                for="ketua_nama">Nama lengkap</label><input
                                                id="ketua_nama" name="ketua_nama"
                                                value="{{ old('ketua_nama') }}" class="form-control"
                                                placeholder="Masukkan nama lengkap" required
                                                data-booking-name><small class="booking-field-warning"
                                                data-booking-name-warning hidden></small></div>
                                        <div class="col-md-6"><label class="form-label"
                                                for="ketua_email">Email</label><input id="ketua_email"
                                                name="ketua_email" type="email"
                                                value="{{ old('ketua_email') }}" class="form-control"
                                                placeholder="Masukkan email anda" required
                                                data-booking-email><small class="booking-field-warning"
                                                data-booking-email-warning hidden></small></div>
                                        <div class="col-md-6"><label class="form-label"
                                                for="ketua_no_hp">Nomor WhatsApp</label><input
                                                id="ketua_no_hp" name="ketua_no_hp" type="tel"
                                                inputmode="numeric" value="{{ old('ketua_no_hp') }}"
                                                class="form-control" placeholder="Masukkan nomor anda"
                                                minlength="10" required data-booking-phone><small
                                                class="booking-field-warning" data-booking-phone-warning
                                                hidden></small></div>
                                        <div class="col-md-6"><label class="form-label"
                                                for="tanggal_kunjungan">Tanggal kunjungan</label><input
                                                id="tanggal_kunjungan" type="date"
                                                name="tanggal_kunjungan"
                                                value="{{ old('tanggal_kunjungan') }}"
                                                class="form-control" min="{{ date('Y-m-d') }}"
                                                max="{{ now()->addMonths(2)->format('Y-m-d') }}"
                                                required><small id="quotaAvailability"
                                                class="form-text text-secondary">Pilih tanggal untuk
                                                melihat sisa tiket.</small></div>
                                    </div>
                                </section>

                                <section>
                                    <div
                                        class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <h2 class="h5 mb-0">Peserta dan jenis tiket</h2>
                                        </div><span class="small text-secondary"><span
                                                id="participantCount">1</span>/10 orang</span>
                                    </div>
                                    <div id="participants" class="d-grid gap-3">
                                        <div class="participant-row public-card p-3" data-index="0">
                                            <div
                                                class="d-flex justify-content-between align-items-center mb-3">
                                                <strong>Peserta 1 · Ketua</strong><span
                                                    class="badge text-bg-light">Ketua kelompok</span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label"
                                                        for="participant-name-0">Nama
                                                        ketua</label><input id="participant-name-0"
                                                        name="peserta[0][nama]"
                                                        value="{{ old('peserta.0.nama', old('ketua_nama')) }}"
                                                        class="form-control"
                                                        placeholder="Nama ketua mengikuti data di atas"
                                                        readonly required></div>
                                                <div class="col-md-6"><label class="form-label"
                                                        for="participant-ticket-0">Jenis tiket
                                                        ketua</label><select id="participant-ticket-0"
                                                        name="peserta[0][id_jenis_tiket]"
                                                        class="form-select participant-ticket" required>
                                                        <option value="">Pilih jenis tiket
                                                        </option>
                                                        @foreach ($destination->jenisTiket as $ticket)
                                                            <option
                                                                value="{{ $ticket->id_jenis_tiket }}"
                                                                data-price="{{ $ticket->harga }}">
                                                                {{ $ticket->nama_jenis }} · Rp
                                                                {{ number_format($ticket->harga, 0, ',', '.') }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="addParticipant"
                                        class="btn btn-outline-secondary rounded-pill mt-3"><i
                                            class="bi bi-person-plus me-1"></i>Tambah anggota</button>
                                    <p class="small text-secondary mt-2 mb-0">Ketua dan setiap anggota
                                        wajib memilih satu jenis tiket.</p>
                                </section>

                                <div class="d-flex justify-content-end gap-2 mt-5"><a
                                        href="{{ route('destinations.show', $destination->id_destinasi) }}"
                                        class="btn btn-outline-secondary rounded-pill">Batal</a><button
                                        type="submit" class="btn btn-jg-primary btn-lg px-5">Lanjut
                                        ke Pembayaran</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="customer-card booking-summary sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <div class="d-flex gap-3 align-items-center mt-3 mb-3"><img
                                    src="{{ $destination->foto_utama ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=200&q=80' }}"
                                    class="rounded-3"
                                    style="width: 80px; height: 80px; object-fit: cover;"
                                    alt="{{ $destination->nama_wisata }}">
                                <div>
                                    <h2 class="h6 mb-1">{{ $destination->nama_wisata }}</h2><small
                                        class="text-muted"><i
                                            class="bi bi-geo-alt me-1"></i>{{ Str::limit($destination->alamat, 32) }}</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between"><span
                                    class="text-secondary">Ketua kelompok</span><strong><span
                                        id="summaryLeader">0</span> orang</strong></div>
                            <div class="d-flex justify-content-between mt-2"><span
                                    class="text-secondary">Anggota</span><strong><span
                                        id="summaryMembers">0</span> orang</strong></div>
                            <div class="d-flex justify-content-between mt-2"><span
                                    class="text-secondary">Total harga</span><strong
                                    class="text-orange" id="summaryTotal">Rp 0</strong></div>
                            <p class="small text-secondary mt-3 mb-0">Harga akhir dihitung berdasarkan
                                jenis tiket yang dipilih setiap peserta.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .booking-page {
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            color: #173b60;
            font-size: .92rem;
            line-height: 1.5;
        }

        .booking-page .breadcrumb {
            font-size: 0.9rem;
        }

        .booking-title {
            font-size: clamp(1.15rem, 1.4vw, 1.35rem) !important;
            font-weight: 650;
            letter-spacing: 0;
            line-height: 1.2;
        }

        .booking-page .page-intro p {
            max-width: 44rem;
            font-size: 0.86rem;
        }

        .booking-section {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--jg-border);
        }

        .step-number {
            display: inline-grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--jg-orange);
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .booking-page h2 {
            font-size: 1rem !important;
            font-weight: 650;
        }

        .booking-page .form-label {
            font-size: 0.78rem;
            font-weight: 650;
            color: #385872;
        }

        .booking-page .form-control,
        .booking-page .form-select {
            min-height: 48px;
            border-radius: 0.75rem;
        }

        .participant-row {
            border-color: var(--jg-border);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 5px 16px rgba(31, 41, 55, 0.04);
        }

        .participant-row strong {
            font-size: .84rem;
            font-weight: 650;
        }

        .booking-page .form-control,
        .booking-page .form-select {
            font-size: .86rem;
        }

        .booking-field-warning {
            display: block;
            margin-top: .25rem;
            color: #dc3545;
            font-size: .75rem;
        }

        .booking-page .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .12);
        }

        .booking-summary h2 {
            font-size: 1rem;
            font-weight: 650;
        }

        .booking-summary {
            font-size: .82rem;
        }

        .booking-summary {
            border-color: rgba(127, 187, 219, 0.45);
            box-shadow: 0 10px 24px rgba(31, 41, 55, 0.07);
        }

        .booking-summary img {
            border-radius: 0.85rem !important;
        }

        .booking-summary hr {
            border-color: var(--jg-border);
            opacity: 1;
        }

        .booking-page #addParticipant {
            border-color: var(--jg-blue);
            color: #315d78;
        }

        .booking-page #addParticipant:hover {
            background: var(--jg-sky);
        }

        .booking-page button[type="submit"] {
            box-shadow: 0 8px 18px rgba(245, 139, 5, 0.2);
        }

        @media (max-width: 991.98px) {
            .booking-page {
                padding-top: 6rem !important;
            }

            .booking-summary {
                position: static !important;
            }
        }
    </style>
    <script>
        (() => {
            const participants = document.getElementById('participants');
            const addButton = document.getElementById('addParticipant');
            const count = document.getElementById('participantCount');
            const summaryLeader = document.getElementById('summaryLeader');
            const summaryMembers = document.getElementById('summaryMembers');
            const summaryTotal = document.getElementById('summaryTotal');
            const visitDate = document.getElementById('tanggal_kunjungan');
            const quotaAvailability = document.getElementById('quotaAvailability');
            let nextIndex = 1;

            const showBookingValidation = (input, warning, message) => {
                const invalid = Boolean(message);
                input.classList.toggle('is-invalid', invalid);
                input.setCustomValidity(message);
                warning.textContent = message;
                warning.hidden = !invalid;
            };
            const validateBookingName = (input, warning) => {
                const message = input.value.length > 0 && !/^[\p{L}\s]+$/u.test(input.value) ?
                    'Nama lengkap hanya boleh berisi huruf dan spasi.' : '';
                showBookingValidation(input, warning, message);
            };
            const validateBookingEmail = () => {
                const input = document.getElementById('ketua_email');
                const warning = document.querySelector('[data-booking-email-warning]');
                const message = input.value.length > 0 && !/@gmail\.com$/i.test(input.value
                    .trim()) ? 'Email harus menggunakan alamat @gmail.com.' : '';
                showBookingValidation(input, warning, message);
            };
            const validateBookingPhone = () => {
                const input = document.getElementById('ketua_no_hp');
                const warning = document.querySelector('[data-booking-phone-warning]');
                const invalidCharacters = /[^0-9]/.test(input.value);
                const message = invalidCharacters ? 'Nomor telepon hanya boleh berisi angka.' :
                    input.value.length > 0 && input.value.length < 10 ?
                    'Nomor telepon minimal 10 angka.' : input.value.length > 12 ?
                    'Nomor telepon maksimal 12 angka.' : '';
                showBookingValidation(input, warning, message);
            };
            const bookingForm = document.getElementById('bookingForm');
            const leaderNameInput = document.getElementById('ketua_nama');
            const leaderNameWarning = document.querySelector('[data-booking-name-warning]');
            leaderNameInput.addEventListener('input', () => validateBookingName(leaderNameInput,
                leaderNameWarning));
            document.getElementById('ketua_email').addEventListener('input', validateBookingEmail);
            document.getElementById('ketua_no_hp').addEventListener('input', validateBookingPhone);
            document.getElementById('ketua_no_hp').addEventListener('keydown', (event) => {
                if (event.key.length === 1 && !/[0-9]/.test(event.key)) {
                    event.preventDefault();
                    validateBookingPhone();
                }
            });
            bookingForm.addEventListener('submit', () => {
                validateBookingName(leaderNameInput, leaderNameWarning);
                validateBookingEmail();
                validateBookingPhone();
            });

            const updateQuotaAvailability = async () => {
                if (!visitDate.value) {
                    quotaAvailability.textContent =
                        'Pilih tanggal untuk melihat sisa tiket.';
                    return;
                }

                const maxDate = new Date('{{ now()->addMonths(2)->format('Y-m-d') }}');
                const selectedDate = new Date(visitDate.value + 'T00:00:00');
                if (selectedDate > maxDate) {
                    quotaAvailability.textContent =
                        'Tanggal kunjungan maksimal 2 bulan dari hari ini.';
                    visitDate.setCustomValidity(
                        'Tanggal kunjungan maksimal 2 bulan dari hari ini.');
                    return;
                }

                visitDate.setCustomValidity('');
                quotaAvailability.textContent = 'Memeriksa sisa tiket...';
                try {
                    const response = await fetch(
                        '{{ route('customer.booking.quota', $destination->id_destinasi) }}?date=' +
                        encodeURIComponent(visitDate.value), {
                            headers: {
                                Accept: 'application/json'
                            }
                        });
                    const data = await response.json();
                    quotaAvailability.textContent = data.enabled ?
                        'Sisa tiket pada tanggal ini: ' + Number(data.remaining)
                        .toLocaleString('id-ID') + ' peserta.' :
                        'Destinasi ini tidak menerapkan batas kuota tiket.';
                } catch (error) {
                    quotaAvailability.textContent =
                        'Sisa tiket akan diperiksa saat pesanan dikirim.';
                }
            };

            const updateCount = () => {
                const leaderReady = ['ketua_nama', 'ketua_email', 'ketua_no_hp',
                    'tanggal_kunjungan'
                ].every(name => document.querySelector(`[name="${name}"]`)?.value.trim());
                const rows = [...participants.querySelectorAll('.participant-row')];
                const leaderTicket = rows[0]?.querySelector('.participant-ticket');
                const memberCount = rows.slice(1).filter(row => row.querySelector(
                    'input[name*="[nama]"]')?.value.trim() && row.querySelector(
                    '.participant-ticket')?.value).length;
                const leaderCount = leaderReady && leaderTicket?.value ? 1 : 0;
                const total = leaderCount + memberCount;
                count.textContent = total;
                summaryLeader.textContent = leaderCount;
                summaryMembers.textContent = memberCount;
                addButton.disabled = rows.length >= 10;
                const totalPrice = leaderCount ? rows.reduce((sum, row) => sum + Number(row
                    .querySelector('.participant-ticket')?.options[row.querySelector(
                        '.participant-ticket')?.selectedIndex]?.dataset.price || 0), 0) : 0;
                summaryTotal.textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
            };

            document.querySelectorAll(
                '[name="ketua_nama"], [name="ketua_email"], [name="ketua_no_hp"], [name="tanggal_kunjungan"]'
            ).forEach(input => input.addEventListener('input', () => {
                if (input.name === 'ketua_nama') document.getElementById(
                    'participant-name-0').value = input.value;
                updateCount();
            }));
            visitDate.addEventListener('change', updateQuotaAvailability);
            visitDate.addEventListener('input', updateQuotaAvailability);

            addButton.addEventListener('click', () => {
                if (participants.children.length >= 10) return;
                const row = document.createElement('div');
                row.className = 'participant-row public-card p-3';
                row.dataset.index = nextIndex;
                row.innerHTML =
                    `<div class="d-flex justify-content-between align-items-center mb-3"><strong>Peserta ${nextIndex + 1} · Anggota</strong><button type="button" class="btn btn-sm btn-outline-danger rounded-pill remove-participant"><i class="bi bi-trash"></i><span class="visually-hidden">Hapus anggota</span></button></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="participant-name-${nextIndex}">Nama peserta</label><input id="participant-name-${nextIndex}" name="peserta[${nextIndex}][nama]" class="form-control" placeholder="Masukkan nama peserta" required><small class="booking-field-warning participant-name-warning" hidden></small></div><div class="col-md-6"><label class="form-label" for="participant-ticket-${nextIndex}">Jenis tiket</label><select id="participant-ticket-${nextIndex}" name="peserta[${nextIndex}][id_jenis_tiket]" class="form-select participant-ticket" required><option value="">Pilih jenis tiket</option>@foreach ($destination->jenisTiket as $ticket)<option value="{{ $ticket->id_jenis_tiket }}" data-price="{{ $ticket->harga }}">{{ $ticket->nama_jenis }} · Rp {{ number_format($ticket->harga, 0, ',', '.') }}</option>@endforeach</select></div></div>`;
                participants.appendChild(row);
                const participantName = row.querySelector('input[name*="[nama]"]');
                const participantNameWarning = row.querySelector(
                    '.participant-name-warning');
                participantName.addEventListener('input', () => {
                    const invalid = participantName.value.length > 0 && !
                        /^[\p{L}\s]+$/u.test(participantName.value);
                    showBookingValidation(participantName, participantNameWarning,
                        invalid ?
                        'Nama peserta hanya boleh berisi huruf dan spasi.' : '');
                });
                nextIndex++;
                updateCount();
            });

            participants.addEventListener('click', event => {
                const removeButton = event.target.closest('.remove-participant');
                if (!removeButton) return;
                removeButton.closest('.participant-row').remove();
                updateCount();
            });

            participants.addEventListener('change', event => {
                if (event.target.matches('.participant-ticket')) updateCount();
            });

            updateCount();
            updateQuotaAvailability();
        })();
    </script>
@endsection
