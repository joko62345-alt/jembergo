@extends('layouts.app')

@section('title', 'Kelola Perjalanan - JemberGo')

@section('content')
    @include('components.public-navbar')

    @php
        $currentMembers = collect($booking->anggota_names ?? []);
        $currentCount = 1 + $currentMembers->count();
        $remaining = max(0, 10 - $currentCount);
        $pendingMembers = collect($pendingData['anggota'] ?? []);
        $memberRows = $pendingMembers->isNotEmpty()
            ? $pendingMembers
            : collect([['nama' => '', 'id_jenis_tiket' => '']]);
    @endphp

    <main class="manage-page">
        <div class="container manage-container">
            <a href="{{ route('customer.ticket', $booking->id_pemesanan) }}" class="manage-back customer-back-link"><i
                    class="bi bi-arrow-left"></i> Kembali ke E-Ticket</a>
            <div class="manage-header">
                <div>
                    <span class="manage-kicker">Perjalanan Anda</span>
                    <h1>Kelola perjalanan</h1>
                    <p>Ubah jadwal atau tambahkan peserta perjalanan.</p>
                </div>
            </div>

            @if ($pending)
                <div class="manage-pending">
                    <div>
                        <strong>Perubahan Anda sudah tersimpan</strong>
                        <p>{{ $pendingPaymentStarted
                            ? 'Lanjutkan pembayaran untuk memperbarui E-Ticket.'
                            : 'Draf yang sama akan diperbarui jika Anda mengubah jadwal atau peserta.' }}</p>
                    </div><a
                        href="{{ route('customer.change.checkout', [$booking->id_pemesanan, $pending->id_perubahan]) }}"
                        class="btn btn-warning">{{ $pendingPaymentStarted ? 'Lanjutkan pembayaran' : 'Lihat perubahan tersimpan' }}</a>
                </div>
            @endif
            @if (session('change_error'))
                <div class="manage-form-error">{{ session('change_error') }}</div>
            @endif

            <form method="POST" action="{{ route('customer.manage.review', $booking->id_pemesanan) }}"
                id="manageForm">
                @csrf
                <div class="manage-layout">
                    <section class="manage-main">
                        <article class="manage-card">
                            <div class="card-heading">
                                <div>
                                    <span>Jadwal perjalanan</span>
                                    <h2>Ubah jadwal</h2>
                                    <p>Pilih tanggal baru untuk kunjungan.</p>
                                </div>
                            </div>
                            <label for="tanggal_kunjungan">Tanggal kunjungan baru</label>
                            <input id="tanggal_kunjungan" name="tanggal_kunjungan" type="date"
                                min="{{ today()->toDateString() }}"
                                max="{{ now()->addMonths(2)->format('Y-m-d') }}"
                                value="{{ old('tanggal_kunjungan', $pendingData['tanggal_kunjungan'] ?? $booking->tanggal_kunjungan->toDateString()) }}"
                                @disabled($pendingPaymentStarted)
                                required>
                            @error('tanggal_kunjungan')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                            <div class="date-note">Jadwal saat ini:
                                <strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong>
                            </div>
                        </article>

                        <article class="manage-card">
                            <div class="card-heading">
                                <div>
                                    <span>Peserta perjalanan</span>
                                    <h2>Tambah tiket</h2>
                                    <p>Tambahkan peserta dan pilih jenis tiketnya.</p>
                                </div>
                            </div>
                            @if ($remaining > 0)
                                <div class="member-list" id="memberList">
                                    @if ($pendingPaymentStarted && $pendingMembers->isEmpty())
                                        <p class="member-limit">Tidak ada tiket tambahan pada perubahan tersimpan.</p>
                                    @else
                                        @foreach ($memberRows as $memberIndex => $member)
                                            <div class="member-row" data-member-row>
                                                <div class="member-number">{{ str_pad((string) ($memberIndex + 1), 2, '0', STR_PAD_LEFT) }}</div>
                                                <div class="member-fields">
                                                    <label>Nama anggota
                                                        <input name="anggota[{{ $memberIndex }}][nama]"
                                                            value="{{ old("anggota.{$memberIndex}.nama", $member['nama'] ?? '') }}"
                                                            placeholder="Masukkan nama lengkap"
                                                            @disabled($pendingPaymentStarted)>
                                                    </label>
                                                    <label>Jenis tiket
                                                        <select name="anggota[{{ $memberIndex }}][id_jenis_tiket]"
                                                            @disabled($pendingPaymentStarted)>
                                                            <option value="">Pilih jenis tiket</option>
                                                            @foreach ($booking->destinasi->jenisTiket as $ticket)
                                                                <option value="{{ $ticket->id_jenis_tiket }}"
                                                                    data-price="{{ $ticket->harga }}"
                                                                    @selected((string) old("anggota.{$memberIndex}.id_jenis_tiket", $member['id_jenis_tiket'] ?? '') === (string) $ticket->id_jenis_tiket)>
                                                                    {{ $ticket->nama_jenis }} · Rp
                                                                    {{ number_format($ticket->harga, 0, ',', '.') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                </div>
                                                <button type="button" class="remove-member" aria-label="Hapus anggota"
                                                    @disabled($pendingPaymentStarted)>Hapus</button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="add-member" id="addMember"
                                    @disabled($pendingPaymentStarted || max(1, $pendingMembers->count()) >= $remaining)>Tambah anggota</button>
                                <p class="member-limit">{{ $remaining }} slot tiket tersedia · maksimal 10 orang per
                                    booking.</p>
                            @else
                                <div class="empty-members"><strong>Booking sudah
                                        penuh</strong><span>Jumlah peserta saat ini sudah mencapai 10
                                        orang.</span></div>
                            @endif
                        </article>
                    </section>

                    <aside class="manage-summary">
                        <div class="summary-card">
                            <span class="summary-kicker">Ringkasan pesanan</span>
                            <h2>{{ $booking->destinasi->nama_wisata }}</h2>
                            <p>{{ Str::limit($booking->destinasi->alamat, 42) }}
                            </p>
                            <div class="summary-line"><span>Jadwal saat
                                    ini</span><strong>{{ $booking->tanggal_kunjungan->translatedFormat('d M Y') }}</strong>
                            </div>
                            <div class="summary-line"><span>Jumlah
                                    tiket</span><strong>{{ $currentCount }} tiket</strong></div>
                            <hr>
                            <div class="summary-addition"><span>Tiket tambahan</span><strong
                                    id="summaryCount">0 tiket</strong></div>
                            <div class="summary-addition"><span>Subtotal tambahan</span><strong
                                    id="summarySubtotal">Rp 0</strong></div>
                            <div class="summary-total"><span>Total biaya tambahan</span><strong
                                    id="summaryTotal">Rp 0</strong></div>
                            <button class="primary-action" id="reviewChanges" type="submit"
                                    disabled>{{ $pending ? 'Lihat perubahan tersimpan' : 'Lihat perubahan' }}</button>
                            <small class="summary-help">Anda akan melihat detail perubahan sebelum
                                melanjutkan ke pembayaran.</small>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </main>

    <style>
        .manage-page {
            font-family: "DM Sans", sans-serif;
            font-size: .9rem;
            line-height: 1.5;
        }

        .manage-header h1 {
            font-size: clamp(1.7rem, 2.8vw, 2.25rem);
            line-height: 1.2;
        }

        .manage-card h2,
        .summary-card h2 {
            font-size: 1.05rem;
            line-height: 1.3;
        }

        .manage-card input,
        .manage-card select {
            font-size: .84rem;
        }

        .card-heading p,
        .manage-header p,
        .summary-help {
            font-size: .8rem;
        }

        .manage-page {
            min-height: 100vh;
            padding: 7.5rem 0 4rem;
            background: var(--jg-bg);
            color: #173b60;
        }

        .manage-container {
            max-width: 1080px;
        }

        .manage-header {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            margin: 1.25rem 0 1.5rem;
        }

        .manage-kicker,
        .summary-kicker {
            color: #567087;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .manage-header h1 {
            margin: .3rem 0 .25rem;
            font-size: clamp(1.6rem, 2.8vw, 2rem);
            font-weight: 600;
            letter-spacing: 0;
        }

        .manage-header p {
            margin: 0;
            color: #708496;
        }

        .manage-pending {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.2rem;
            margin-bottom: 1.25rem;
            border: 1px solid #dce6ed;
            border-radius: .8rem;
            background: #fff;
        }

        .manage-pending p {
            margin: .2rem 0 0;
            color: #708496;
            font-size: .82rem;
        }

        .manage-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.85fr) minmax(290px, 1fr);
            gap: 1.25rem;
            align-items: start;
        }

        .manage-main {
            display: grid;
            gap: 1.25rem;
        }

        .manage-card,
        .summary-card {
            border: 1px solid #dce6ed;
            border-radius: .9rem;
            background: #fff;
            box-shadow: 0 4px 16px rgba(23, 59, 96, .04);
        }

        .manage-card {
            padding: 1.5rem;
        }

        .card-heading {
            display: flex;
            gap: .85rem;
            align-items: flex-start;
            padding-bottom: 1.2rem;
            margin-bottom: 1.2rem;
            border-bottom: 1px solid #edf1f4;
        }

        .card-heading span {
            color: #567087;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .card-heading h2 {
            margin: .2rem 0 .2rem;
            font-size: 1.15rem;
            font-weight: 600;
        }

        .card-heading p {
            margin: 0;
            color: #708496;
            font-size: .86rem;
        }

        .manage-card>label,
        .member-fields label {
            display: block;
            color: #385872;
            font-size: .78rem;
            font-weight: 600;
        }

        .manage-card input,
        .manage-card select {
            display: block;
            width: 100%;
            min-height: 46px;
            margin-top: .4rem;
            padding: .65rem .75rem;
            border: 1px solid #ccd9e2;
            border-radius: .6rem;
            color: #173b60;
            background: #fff;
            outline: 0;
        }

        .manage-card input:focus,
        .manage-card select:focus {
            border-color: #567087;
            box-shadow: 0 0 0 3px rgba(86, 112, 135, .12);
        }

        .date-note,
        .member-limit {
            margin: .8rem 0 0;
            color: #778d9d;
            font-size: .78rem;
        }

        .field-error {
            display: block;
            margin-top: .4rem;
            color: #b23b47;
        }

        .member-list {
            display: grid;
            gap: .7rem;
        }

        .member-row {
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr) 34px;
            gap: .75rem;
            align-items: center;
            padding: .8rem;
            border: 1px solid #e2eaf0;
            border-radius: .75rem;
            background: #fbfcfd;
        }

        .member-number {
            color: #9aabb7;
            font-family: monospace;
            font-size: .8rem;
        }

        .member-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
        }

        .remove-member {
            padding: .45rem .55rem;
            border: 1px solid #dce6ed;
            border-radius: .5rem;
            background: #fff;
            color: #567087;
            font-size: .75rem;
        }

        .add-member {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: 1rem;
            padding: .55rem .75rem;
            border: 1px solid #cdd9e2;
            border-radius: .55rem;
            background: #fff;
            color: #315d78;
            font-size: .82rem;
        }

        .add-member:hover {
            border-color: #315d78;
            color: #173b60;
        }

        .add-member:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .empty-members {
            display: grid;
            gap: .35rem;
            place-items: center;
            padding: 2rem;
            color: #708496;
            text-align: center;
        }

        .empty-members strong {
            color: #173b60;
        }

        .summary-card {
            position: sticky;
            top: 6.5rem;
            padding: 1.5rem;
        }

        .summary-card h2 {
            margin: .4rem 0 .2rem;
            font-size: 1.25rem;
        }

        .summary-card p {
            margin: 0 0 1.25rem;
            color: #708496;
            font-size: .8rem;
        }

        .summary-card p i {
            margin-right: .35rem;
        }

        .summary-line,
        .summary-addition {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .6rem 0;
            color: #708496;
            font-size: .82rem;
        }

        .summary-line strong,
        .summary-addition strong {
            color: #173b60;
            text-align: right;
        }

        .summary-card hr {
            margin: 1rem 0 .4rem;
            border-color: #e5edf2;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: end;
            padding-top: 1rem;
            margin-top: .7rem;
            border-top: 1px solid #e5edf2;
            color: #385872;
            font-size: .86rem;
        }

        .summary-total strong {
            color: #173b60;
            font-size: 1.15rem;
        }

        .primary-action {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            width: 100%;
            margin-top: 1.25rem;
            padding: .85rem 1rem;
            border: 0;
            border-radius: .65rem;
            background: #f58b05;
            color: #fff;
            font-weight: 600;
            transition: background-color 160ms ease;
        }

        .primary-action:hover {
            background: #d97706;
        }

        .summary-help {
            display: block;
            margin-top: .7rem;
            color: #8a9aa6;
            font-size: .72rem;
            text-align: center;
        }

        .manage-form-error {
            padding: .85rem 1rem;
            margin-bottom: 1.25rem;
            border: 1px solid #dbe6ee;
            border-radius: .7rem;
            background: #fff;
            color: #567087;
            font-size: .84rem;
        }

        .primary-action:disabled {
            cursor: not-allowed;
            background: #c8d2d9;
        }

        @media (max-width: 800px) {
            .manage-header {
                align-items: start;
                flex-direction: column;
            }

            .manage-layout {
                grid-template-columns: 1fr;
            }

            .summary-card {
                position: static;
            }
        }

        @media (max-width: 560px) {
            .manage-page {
                padding-top: 6rem;
            }

            .manage-card,
            .summary-card {
                padding: 1rem;
            }

            .member-fields {
                grid-template-columns: 1fr;
            }

            .member-row {
                align-items: start;
            }

            .manage-pending {
                align-items: start;
                flex-direction: column;
            }

            .manage-pending .btn {
                width: 100%;
            }

            .manage-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        .manage-page {
            font-family: "DM Sans", sans-serif;
            font-size: .9rem;
            line-height: 1.5;
        }

        .manage-header h1 {
            font-size: clamp(1.7rem, 2.8vw, 2.25rem);
            line-height: 1.2;
        }

        .manage-card h2,
        .summary-card h2 {
            font-size: 1.05rem;
            line-height: 1.3;
        }

        .manage-card input,
        .manage-card select {
            font-size: .84rem;
        }

        .card-heading p,
        .manage-header p,
        .summary-help {
            font-size: .8rem;
        }
    </style>
    @php
        $ticketOptions = $booking->destinasi->jenisTiket
            ->map(function ($ticket): array {
                return [
                    'id' => $ticket->id_jenis_tiket,
                    'label' =>
                        $ticket->nama_jenis . ' · Rp ' . number_format($ticket->harga, 0, ',', '.'),
                    'price' => $ticket->harga,
                ];
            })
            ->values()
            ->all();
    @endphp
    <script>
        (() => {
            const list = document.getElementById('memberList');
            const add = document.getElementById('addMember');
            const count = document.getElementById('summaryCount');
            const subtotal = document.getElementById('summarySubtotal');
            const total = document.getElementById('summaryTotal');
            const review = document.getElementById('reviewChanges');
            const visitDate = document.getElementById('tanggal_kunjungan');
            if (!review || !visitDate) return;
            const max = {{ $remaining }};
            const pendingPaymentStarted = @json($pendingPaymentStarted);
            const money = value => 'Rp ' + value.toLocaleString('id-ID');
            const rows = () => list ? [...list.querySelectorAll('[data-member-row]')] : [];
            const updateAddState = () => {
                if (add) {
                    add.disabled = pendingPaymentStarted || rows().length >= max;
                }
            };
            const refresh = () => {
                const memberRows = rows();
                let amount = 0;
                memberRows.forEach((row, rowIndex) => {
                    row.querySelector('.member-number').textContent = String(rowIndex +
                        1).padStart(2, '0');
                    row.querySelector('input').name = `anggota[${rowIndex}][nama]`;
                    row.querySelector('select').name = `anggota[${rowIndex}][id_jenis_tiket]`;
                    amount += Number(row.querySelector('select')?.selectedOptions[0]
                        ?.dataset.price || 0);
                });
                count.textContent = memberRows.filter(row => row.querySelector('input')?.value
                    .trim() && row.querySelector('select')?.value).length + ' tiket';
                subtotal.textContent = money(amount);
                total.textContent = money(amount);
                updateAddState();
            };
            const ticketOptions = @json($ticketOptions);
            const options = ticketOptions.map(ticket =>
                `<option value="${ticket.id}" data-price="${ticket.price}">${ticket.label}</option>`
            ).join('');
            add?.addEventListener('click', () => {
                if (rows().length >= max) return;
                const index = rows().length;
                const row = document.createElement('div');
                row.className = 'member-row';
                row.dataset.memberRow = '';
                row.innerHTML =
                    `<div class="member-number">${String(index + 1).padStart(2, '0')}</div><div class="member-fields"><label>Nama anggota<input name="anggota[${index}][nama]" placeholder="Nama lengkap" required></label><label>Jenis tiket<select name="anggota[${index}][id_jenis_tiket]" required><option value="">Pilih jenis tiket</option>${options}</select></label></div><button type="button" class="remove-member" aria-label="Hapus anggota">Hapus</button>`;
                list.appendChild(row);
                refresh();
            });
            const originalDate = @json($booking->tanggal_kunjungan->format('Y-m-d'));
            const maxDate = new Date('{{ now()->addMonths(2)->format('Y-m-d') }}T00:00:00');
            const validateVisitDate = () => {
                if (!visitDate?.value) return;
                const selectedDate = new Date(visitDate.value + 'T00:00:00');
                if (selectedDate > maxDate) {
                    visitDate.setCustomValidity(
                        'Tanggal kunjungan maksimal 2 bulan dari hari ini.');
                } else {
                    visitDate.setCustomValidity('');
                }
            };
            const updateReviewState = () => {
                validateVisitDate();
                const dateChanged = visitDate.value !== originalDate;
                const memberAdded = rows().some(row =>
                    row.querySelector('input')?.value.trim() && row.querySelector('select')?.value);
                review.disabled = !pendingPaymentStarted && !dateChanged && !memberAdded;
            };
            list?.addEventListener('input', () => {
                refresh();
                updateReviewState();
            });
            list?.addEventListener('change', () => {
                refresh();
                updateReviewState();
            });
            list?.addEventListener('click', event => {
                const button = event.target.closest('.remove-member');
                if (button) {
                    button.closest('[data-member-row]').remove();
                    refresh();
                    updateReviewState();
                }
            });
            visitDate?.addEventListener('change', updateReviewState);
            visitDate?.addEventListener('input', updateReviewState);
            refresh();
            updateReviewState();
        })();
    </script>
@endsection
