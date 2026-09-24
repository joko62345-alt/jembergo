<x-admin-layout title="Verifikasi Tiket" active="verification">
    <div class="mb-4"><span class="text-warning text-uppercase small fw-bold">Operasional
            destinasi</span>
        <h1 class="display-6 fw-bold mt-2">Verifikasi tiket</h1>
        <p class="text-secondary">Masukkan kode booking atau scan QR untuk melihat data kelompok dan
            memverifikasi tiket.</p>
    </div>
    @if (session('verification_success'))
        <div class="alert alert-success">{{ session('verification_success') }}</div>
    @endif
    @if (session('verification_error'))
        <div class="alert alert-danger">{{ session('verification_error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route('admin.verification.lookup') }}">@csrf<label
                    for="kode_booking" class="form-label">Kode booking atau QR grup</label>
                <div class="input-group"><input id="kode_booking" name="kode_booking"
                        value="{{ old('kode_booking') }}" class="form-control form-control-lg"
                        placeholder="Masukkan kode booking atau scan QR grup" required><button
                        class="btn btn-warning px-4" type="submit">Tampilkan data</button></div>
            </form>
            @if (isset($booking))
                @php(
    $participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values()
)
                <hr class="my-4">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div><span
                            class="verification-status verification-status-success">{{ \App\Support\StatusLabel::payment($booking->pembayaran->status_pembayaran) }}</span>
                        <h2 class="h4 fw-bold mt-2 mb-1">{{ $booking->kode_booking }}</h2><small
                            class="text-secondary">Ketua: {{ $booking->ketua_nama }} ·
                            {{ $booking->ketua_email }} · {{ $booking->ketua_no_hp }}</small>
                    </div>
                    <div class="text-md-end"><span
                            class="verification-status verification-status-warning">Kunjungan
                            {{ $booking->tanggal_kunjungan->format('d/m/Y') }}</span>
                        <div class="small text-secondary mt-2">
                            {{ $booking->destinasi->nama_wisata }} · {{ $participants->count() }}
                            peserta · Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="verification-members mt-4"><strong class="d-block mb-2">Anggota
                        kelompok</strong>
                    @forelse($participants->skip(1) as $member)
                        <span class="verification-member me-3 mb-2">{{ $member['nama'] }} ·
                        {{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $member['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}</span>@empty<span
                            class="small text-secondary">Tidak ada anggota tambahan.</span>
                    @endforelse
                </div>
                <h3 class="h6 fw-bold mt-4">Peserta, jenis tiket, dan status</h3>
                <div class="table-responsive">
                    <table class="table align-middle verification-table">
                        <thead>
                            <tr>
                                <th>Peserta</th>
                                <th>Jenis tiket</th>
                                <th>Kode booking</th>
                                <th>Status</th>
                                <th>Waktu verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($booking->tiket as $index => $ticket)
                                @php($participant = $participants->get($index, ['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket]))
                                <tr
                                    class="{{ $selectedTicketId === $ticket->id_tiket ? 'table-warning' : '' }}">
                                    <td><strong>{{ $participant['nama'] }}</strong>
                                        @if ($index === 0)
                                            <small class="d-block text-secondary">Ketua
                                                kelompok</small>
                                        @endif
                                    </td>
                                    <td>{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}
                                    </td>
                                    <td class="font-monospace">{{ $booking->kode_booking }}</td>
                                    <td><span
                                            class="verification-status {{ $ticket->status_tiket === 'ACTIVE' ? 'verification-status-info' : 'verification-status-muted' }}">{{ \App\Support\StatusLabel::ticket($ticket->status_tiket) }}</span>
                                    </td>
                                    <td>{{ $ticket->waktu_verifikasi?->setTimezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('admin.verification.verify') }}"
                    id="verificationForm">@csrf<input type="hidden" name="id_pemesanan"
                        value="{{ $booking->id_pemesanan }}"><button
                        class="btn btn-success rounded-pill w-100" type="submit">Verifikasi seluruh
                        tiket aktif</button></form>
            @endif
        </div>
    </div>
    <div class="verification-modal" id="verificationModal" hidden>
        <div class="verification-modal-backdrop" data-close-verification-modal></div>
        <section class="verification-modal-dialog" role="dialog" aria-modal="true"
            aria-labelledby="verificationModalTitle">
            <div class="verification-modal-icon"><i class="bi bi-shield-check"
                    aria-hidden="true"></i></div>
            <h2 id="verificationModalTitle">Konfirmasi verifikasi</h2>
            <p>Semua tiket aktif pada booking ini akan ditandai sebagai sudah digunakan.</p>
            <div class="verification-modal-actions"><button type="button" class="btn btn-light"
                    data-close-verification-modal>Batal</button><button type="button"
                    class="btn btn-success" id="confirmVerification">Ya, verifikasi</button></div>
        </section>
    </div>
    <style>
        .verification-status {
            font-weight: 700;
            white-space: nowrap;
        }

        .verification-status-success {
            color: #79e1b5;
        }

        .verification-status-warning {
            color: #ffc85c;
        }

        .verification-status-info {
            color: #8ecbff;
        }

        .verification-status-muted {
            color: #a9bdd2;
        }

        .verification-members {
            color: var(--jg-text);
        }

        .verification-member {
            display: inline-block;
            color: var(--jg-muted);
            font-size: .85rem;
        }

        .verification-table> :not(caption)>*>* {
            border-bottom: 0;
        }

        .verification-table tbody tr:hover {
            background: transparent;
        }

        .verification-modal {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: grid;
            place-items: center;
            padding: 1rem;
        }

        .verification-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 31, 50, .55);
            backdrop-filter: blur(3px);
        }

        .verification-modal-dialog {
            position: relative;
            width: min(100%, 420px);
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 31, 50, .24);
            text-align: center;
            animation: verification-modal-in .18s ease-out;
        }

        .verification-modal-icon {
            display: grid;
            place-items: center;
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: #e8f7ee;
            color: #198754;
            font-size: 1.7rem;
        }

        .verification-modal-dialog h2 {
            margin: 0;
            color: #173b60;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .verification-modal-dialog p {
            margin: .6rem 0 1.5rem;
            color: #68798a;
            font-size: .88rem;
            line-height: 1.55;
        }

        .verification-modal-actions {
            display: flex;
            justify-content: center;
            gap: .65rem;
        }

        .verification-modal-actions button {
            min-width: 8rem;
            border-radius: .65rem;
        }

        @keyframes verification-modal-in {
            from {
                opacity: 0;
                transform: translateY(8px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        body:has(.verification-modal:not([hidden])) {
            overflow: hidden;
        }
    </style>
    <script>
        (() => {
            const form = document.getElementById('verificationForm');
            const modal = document.getElementById('verificationModal');
            const confirmButton = document.getElementById('confirmVerification');
            if (!form || !modal || !confirmButton) return;
            const closeModal = () => {
                modal.hidden = true;
            };
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') return;
                event.preventDefault();
                modal.hidden = false;
                confirmButton.focus();
            });
            confirmButton.addEventListener('click', () => {
                form.dataset.confirmed = 'true';
                form.submit();
            });
            modal.querySelectorAll('[data-close-verification-modal]').forEach((element) => element
                .addEventListener('click', closeModal));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) closeModal();
            });
        })();
    </script>
</x-admin-layout>
