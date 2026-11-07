<x-admin-layout title="Verifikasi Tiket" active="verification">
    <div class="verification-page-heading">
        <span class="verification-eyebrow">Operasional destinasi</span>
        <h1>Verifikasi tiket</h1>
        <p>Temukan booking dengan kode atau QR, periksa detail peserta, lalu konfirmasi tiket yang digunakan.</p>
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
    <section class="verification-search-card">
        <form method="POST" action="{{ route('admin.verification.lookup') }}" class="verification-search-form">
            @csrf
            <label for="kode_booking" class="form-label">Kode booking</label>
            <div class="verification-search-row">
                <input id="kode_booking" name="kode_booking" value="{{ old('kode_booking') }}"
                    class="form-control" placeholder="Contoh: JGO-20260101-XXXXXX" required>
                <button class="btn verification-search-submit" type="submit">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>Tampilkan data</span>
                </button>
            </div>
            <div class="verification-scan-tools">
                <button class="btn verification-scan-button" type="button" id="startQrScanner">
                    <i class="bi bi-camera" aria-hidden="true"></i>Scan dengan kamera
                </button>
                <span class="verification-scan-message" id="qrScannerMessage" role="status"
                    aria-live="polite">Arahkan kamera ke QR booking. Pemindaian hanya mencari data;
                    verifikasi tetap perlu dikonfirmasi.</span>
            </div>
            <div class="qr-scanner-panel" id="qrScannerPanel" hidden>
                <video class="qr-scanner-video" id="qrScannerVideo" autoplay muted playsinline></video>
                <button class="btn btn-light border mt-2" type="button" id="stopQrScanner">
                    <i class="bi bi-camera-video-off me-1" aria-hidden="true"></i>Tutup kamera
                </button>
            </div>
        </form>
    </section>

    @unless (isset($booking))
        <section class="verification-guide" aria-label="Panduan verifikasi tiket">
            <div class="verification-guide-heading">
                <span class="verification-guide-icon"><i class="bi bi-info-circle" aria-hidden="true"></i></span>
                <div>
                    <h2>Alur verifikasi</h2>
                    <p>Pastikan data booking sesuai sebelum tiket digunakan.</p>
                </div>
            </div>
            <div class="verification-guide-steps">
                <div><span>01</span><p>Masukkan kode booking atau pindai QR.</p></div>
                <div><span>02</span><p>Periksa nama peserta dan jadwal kunjungan.</p></div>
                <div><span>03</span><p>Konfirmasi untuk menandai tiket sebagai digunakan.</p></div>
            </div>
        </section>
    @endunless

    @if (isset($booking))
        @php(
    $participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values()
)
        <section class="verification-result-card">
            <div class="verification-result-header">
                <div>
                    <span class="verification-section-kicker">Booking ditemukan</span>
                    <h2>{{ $booking->kode_booking }}</h2>
                    <span class="verification-status verification-status-success">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        {{ \App\Support\StatusLabel::payment($booking->pembayaran->status_pembayaran) }}
                    </span>
                </div>
                <span class="verification-status verification-status-warning">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    Kunjungan {{ $booking->tanggal_kunjungan->format('d/m/Y') }}
                </span>
            </div>
            <div class="verification-booking-details">
                <div>
                    <span class="verification-detail-label">Ketua kelompok</span>
                    <strong>{{ $booking->ketua_nama }}</strong>
                    <small>{{ $booking->ketua_email }} · {{ $booking->ketua_no_hp }}</small>
                </div>
                <div>
                    <span class="verification-detail-label">Destinasi</span>
                    <strong>{{ $booking->destinasi->nama_wisata }}</strong>
                </div>
                <div>
                    <span class="verification-detail-label">Jumlah peserta</span>
                    <strong>{{ $participants->count() }} peserta</strong>
                </div>
                <div>
                    <span class="verification-detail-label">Total pembayaran</span>
                    <strong>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="verification-members">
                <strong>Anggota kelompok</strong>
                <div class="verification-member-list">
                    @forelse($participants->skip(1) as $member)
                        <span class="verification-member">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            {{ $member['nama'] }} ·
                            {{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $member['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}
                        </span>
                    @empty
                        <span class="verification-no-members">Tidak ada anggota tambahan.</span>
                    @endforelse
                </div>
            </div>
            <div class="verification-ticket-heading">
                <div>
                    <h3>Daftar tiket</h3>
                    <p>Periksa status tiket setiap peserta sebelum verifikasi.</p>
                </div>
                <span>{{ $booking->tiket->count() }} tiket</span>
            </div>
            <div class="table-responsive verification-table-wrap">
                <table class="table align-middle verification-table mb-0">
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
                            <tr class="{{ $selectedTicketId === $ticket->id_tiket ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $participant['nama'] }}</strong>
                                    @if ($index === 0)
                                        <small class="d-block text-secondary">Ketua kelompok</small>
                                    @endif
                                </td>
                                <td>{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}</td>
                                <td class="font-monospace">{{ $booking->kode_booking }}</td>
                                <td>
                                    <span class="verification-status {{ $ticket->status_tiket === 'ACTIVE' ? 'verification-status-info' : 'verification-status-muted' }}">
                                        {{ \App\Support\StatusLabel::ticket($ticket->status_tiket) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->waktu_verifikasi?->setTimezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <form method="POST" action="{{ route('admin.verification.verify') }}" id="verificationForm">
                @csrf
                <input type="hidden" name="id_pemesanan" value="{{ $booking->id_pemesanan }}">
                <button class="btn verification-confirm-button" type="submit">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>Verifikasi seluruh tiket aktif
                </button>
            </form>
        </section>
    @endif
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
                    class="btn verification-modal-confirm" id="confirmVerification">Ya, verifikasi</button></div>
        </section>
    </div>
    <style>
        .verification-page-heading {
            margin-bottom: 1.5rem;
        }

        .verification-eyebrow,
        .verification-section-kicker {
            color: var(--jg-orange);
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .verification-page-heading h1 {
            margin: .35rem 0 .45rem;
            color: var(--jg-text);
            font-size: clamp(1.7rem, 3vw, 2.2rem);
            font-weight: 700;
            letter-spacing: -.035em;
        }

        .verification-page-heading p {
            max-width: 46rem;
            margin: 0;
            color: var(--jg-muted);
            font-size: .95rem;
        }

        .verification-search-card,
        .verification-result-card,
        .verification-guide {
            border: 1px solid var(--jg-border);
            border-radius: 1rem;
            background: var(--jg-card);
            box-shadow: var(--jg-shadow);
        }

        .verification-search-card {
            padding: clamp(1.2rem, 3vw, 2rem);
        }

        .verification-search-form .form-label {
            margin-bottom: .55rem;
            color: var(--jg-text);
            font-size: .85rem;
            font-weight: 650;
        }

        .verification-search-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .65rem;
        }

        .verification-search-row .form-control {
            min-height: 48px;
            padding-inline: 1rem;
            border-color: var(--jg-border);
            border-radius: .7rem;
            background: var(--jg-card);
            color: var(--jg-text);
            box-shadow: none;
        }

        .verification-search-row .form-control:focus {
            border-color: var(--jg-orange);
            box-shadow: 0 0 0 .2rem rgba(245, 158, 11, .16);
        }

        .verification-search-submit,
        .verification-confirm-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 48px;
            padding: .7rem 1.15rem;
            border: 1px solid var(--jg-orange);
            border-radius: .7rem;
            background: var(--jg-orange);
            color: #172033;
            font-weight: 650;
            transition: background-color 160ms ease, border-color 160ms ease, transform 160ms ease;
        }

        .verification-search-submit:hover,
        .verification-confirm-button:hover {
            border-color: #e58b00;
            background: #e58b00;
            color: #172033;
            transform: translateY(-1px);
        }

        .verification-scan-tools {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .85rem;
            margin-top: 1rem;
        }

        .verification-scan-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 42px;
            padding: .55rem .9rem;
            border: 1px solid #9bb9d8;
            border-radius: .7rem;
            background: transparent;
            color: var(--jg-navy-soft);
            font-size: .85rem;
            font-weight: 600;
        }

        .verification-scan-button:hover,
        .verification-scan-button:focus-visible {
            border-color: var(--jg-orange);
            background: var(--jg-orange-soft);
            color: var(--jg-text);
        }

        .verification-scan-message {
            flex: 1 1 18rem;
            color: var(--jg-muted);
            font-size: .82rem;
        }

        .verification-guide {
            display: grid;
            grid-template-columns: minmax(12rem, .7fr) minmax(0, 1.8fr);
            gap: 1.5rem;
            padding: 1.35rem 1.5rem;
            margin-top: 1rem;
        }

        .verification-guide-heading {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
        }

        .verification-guide-icon {
            display: grid;
            flex: 0 0 2.4rem;
            width: 2.4rem;
            height: 2.4rem;
            place-items: center;
            border-radius: .75rem;
            background: var(--jg-orange-soft);
            color: var(--jg-orange);
            font-size: 1rem;
        }

        .verification-guide h2 {
            margin: .1rem 0 .2rem;
            color: var(--jg-text);
            font-size: .95rem;
            font-weight: 700;
        }

        .verification-guide-heading p,
        .verification-guide-steps p {
            margin: 0;
            color: var(--jg-muted);
            font-size: .8rem;
        }

        .verification-guide-steps {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .verification-guide-steps>div {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
        }

        .verification-guide-steps>div>span {
            color: var(--jg-orange);
            font-size: .72rem;
            font-weight: 800;
        }

        .verification-result-card {
            padding: clamp(1.2rem, 3vw, 2rem);
            margin-top: 1rem;
        }

        .verification-result-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--jg-border);
        }

        .verification-result-header h2 {
            margin: .3rem 0 .65rem;
            color: var(--jg-text);
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .verification-status {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .38rem .68rem;
            border: 1px solid transparent;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .verification-status-success {
            border-color: #f5d58b;
            background: #fff7e2;
            color: #8d5c00;
        }

        .verification-status-warning {
            border-color: #f5d58b;
            background: #fff7e2;
            color: #8d5c00;
        }

        .verification-status-info {
            border-color: #c8d6e5;
            background: #f0f4f8;
            color: #0f2747;
        }

        .verification-status-muted {
            border-color: var(--jg-border);
            background: var(--jg-bg);
            color: var(--jg-muted);
        }

        .verification-booking-details {
            display: grid;
            grid-template-columns: 1.4fr 1fr .8fr 1fr;
            gap: .85rem;
            padding: 1.2rem 0;
            border-bottom: 1px solid var(--jg-border);
        }

        .verification-booking-details>div {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: .2rem;
        }

        .verification-detail-label {
            color: var(--jg-muted);
            font-size: .73rem;
            font-weight: 600;
        }

        .verification-booking-details strong {
            overflow-wrap: anywhere;
            color: var(--jg-text);
            font-size: .88rem;
        }

        .verification-booking-details small {
            overflow-wrap: anywhere;
            color: var(--jg-muted);
            font-size: .76rem;
        }

        .verification-members {
            padding: 1rem 0;
            border-bottom: 1px solid var(--jg-border);
            color: var(--jg-text);
        }

        .verification-members>strong {
            display: block;
            margin-bottom: .65rem;
            font-size: .84rem;
        }

        .verification-member-list {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .verification-member {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .65rem;
            border: 1px solid var(--jg-border);
            border-radius: 999px;
            background: var(--jg-bg);
            color: var(--jg-text);
            font-size: .78rem;
        }

        .verification-member i {
            color: var(--jg-orange);
        }

        .verification-no-members {
            color: var(--jg-muted);
            font-size: .8rem;
        }

        .verification-ticket-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.2rem 0 .75rem;
        }

        .verification-ticket-heading h3 {
            margin: 0 0 .2rem;
            color: var(--jg-text);
            font-size: .95rem;
            font-weight: 700;
        }

        .verification-ticket-heading p {
            margin: 0;
            color: var(--jg-muted);
            font-size: .78rem;
        }

        .verification-ticket-heading>span {
            color: var(--jg-muted);
            font-size: .78rem;
            font-weight: 600;
        }

        .verification-table-wrap {
            margin-bottom: 1rem;
            border: 1px solid var(--jg-border);
            border-radius: .75rem;
        }

        .verification-table {
            min-width: 700px;
            color: var(--jg-text);
            font-size: .81rem;
        }

        .verification-table thead th {
            padding: .8rem .9rem;
            background: var(--jg-bg);
            color: var(--jg-muted);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .verification-table> :not(caption)>*>* {
            padding: .8rem .9rem;
            border-bottom-color: var(--jg-border);
        }

        .verification-table tbody tr:hover {
            background: transparent;
        }

        .verification-table tbody tr:last-child>* {
            border-bottom: 0;
        }

        .verification-table tbody strong {
            color: var(--jg-text);
        }

        .verification-confirm-button {
            width: 100%;
        }

        .qr-scanner-panel {
            width: min(100%, 38rem);
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid var(--jg-border);
            border-radius: .85rem;
            background: var(--jg-bg);
        }

        .qr-scanner-video {
            display: block;
            width: 100%;
            max-height: 60vh;
            border-radius: .85rem;
            background: #102b46;
            object-fit: cover;
        }

        .verification-page-heading~.alert {
            border: 0;
            border-radius: .75rem;
        }

        [data-theme="dark"] .verification-status-success {
            border-color: #725722;
            background: #3a301b;
            color: #f5d58b;
        }

        [data-theme="dark"] .verification-status-warning {
            border-color: #725722;
            background: #3a301b;
            color: #f5d58b;
        }

        [data-theme="dark"] .verification-status-info {
            border-color: #35516e;
            background: #1a2c43;
            color: #c1d3e5;
        }

        [data-theme="dark"] .verification-scan-button {
            border-color: #35516e;
        }

        [data-theme="dark"] .verification-modal-dialog {
            border-color: var(--jg-border);
            background: var(--jg-card);
        }

        [data-theme="dark"] .verification-modal-dialog h2 {
            color: var(--jg-text);
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
            background: var(--jg-orange-soft);
            color: #b96d00;
            font-size: 1.7rem;
        }

        .verification-modal-confirm {
            border: 1px solid var(--jg-orange);
            background: var(--jg-orange);
            color: #172033;
            font-weight: 650;
        }

        .verification-modal-confirm:hover,
        .verification-modal-confirm:focus-visible {
            border-color: #e58b00;
            background: #e58b00;
            color: #172033;
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

        @media (max-width: 900px) {
            .verification-guide {
                grid-template-columns: 1fr;
            }

            .verification-booking-details {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .verification-search-row {
                grid-template-columns: 1fr;
            }

            .verification-search-submit {
                width: 100%;
            }

            .verification-scan-tools {
                align-items: stretch;
            }

            .verification-scan-button {
                width: 100%;
            }

            .verification-guide-steps {
                grid-template-columns: 1fr;
                gap: .7rem;
            }

            .verification-result-header {
                align-items: flex-start;
                flex-direction: column;
                gap: .75rem;
            }

            .verification-booking-details {
                grid-template-columns: 1fr;
                gap: .8rem;
            }

            .verification-ticket-heading {
                align-items: flex-start;
            }

            .verification-modal-dialog {
                padding: 1.4rem;
            }

            .verification-modal-actions {
                flex-direction: column-reverse;
            }

            .verification-modal-actions button {
                width: 100%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        (() => {
            const form = document.querySelector('form[action="{{ route('admin.verification.lookup') }}"]');
            const bookingCodeInput = document.getElementById('kode_booking');
            const startButton = document.getElementById('startQrScanner');
            const stopButton = document.getElementById('stopQrScanner');
            const panel = document.getElementById('qrScannerPanel');
            const video = document.getElementById('qrScannerVideo');
            const message = document.getElementById('qrScannerMessage');
            if (!form || !bookingCodeInput || !startButton || !stopButton || !panel || !video || !message) return;

            let cameraStream = null;
            let animationFrame = null;
            let lastScanAt = 0;
            const scanCanvas = document.createElement('canvas');
            const scanContext = scanCanvas.getContext('2d', { willReadFrequently: true });

            const stopScanner = () => {
                if (animationFrame !== null) {
                    cancelAnimationFrame(animationFrame);
                    animationFrame = null;
                }
                cameraStream?.getTracks().forEach((track) => track.stop());
                cameraStream = null;
                video.srcObject = null;
                panel.hidden = true;
                startButton.disabled = false;
            };

            const setErrorMessage = (error) => {
                if (error?.name === 'NotAllowedError' || error?.name === 'SecurityError') {
                    message.textContent = 'Akses kamera ditolak. Izinkan kamera di browser atau masukkan kode booking secara manual.';
                    return;
                }
                if (error?.name === 'NotFoundError' || error?.name === 'OverconstrainedError') {
                    message.textContent = 'Kamera tidak ditemukan. Gunakan scanner QR yang terhubung atau masukkan kode booking secara manual.';
                    return;
                }
                message.textContent = 'Kamera tidak dapat digunakan. Gunakan scanner QR yang terhubung atau masukkan kode booking secara manual.';
            };

            const scanFrame = async (detector) => {
                if (!cameraStream) return;
                if (Date.now() - lastScanAt < 120) {
                    animationFrame = requestAnimationFrame(() => scanFrame(detector));
                    return;
                }
                lastScanAt = Date.now();

                try {
                    let scannedValue = '';

                    if (detector) {
                        const results = await detector.detect(video);
                        scannedValue = results.find((result) => result.rawValue)?.rawValue?.trim() ?? '';
                    } else if (video.videoWidth > 0 && video.videoHeight > 0) {
                        if (!scanContext) {
                            throw new Error('QR scanner canvas is unavailable.');
                        }
                        if (scanCanvas.width !== video.videoWidth || scanCanvas.height !== video.videoHeight) {
                            scanCanvas.width = video.videoWidth;
                            scanCanvas.height = video.videoHeight;
                        }
                        scanContext.drawImage(video, 0, 0, scanCanvas.width, scanCanvas.height);
                        const image = scanContext.getImageData(0, 0, scanCanvas.width, scanCanvas.height);
                        scannedValue = window.jsQR(image.data, image.width, image.height, {
                            inversionAttempts: 'dontInvert',
                        })?.data?.trim() ?? '';
                    }

                    if (scannedValue) {
                        bookingCodeInput.value = scannedValue;
                        stopScanner();
                        message.textContent = 'QR terbaca. Memuat data booking untuk diperiksa.';
                        form.requestSubmit();
                        return;
                    }
                } catch (error) {
                    stopScanner();
                    setErrorMessage(error);
                    return;
                }

                animationFrame = requestAnimationFrame(() => scanFrame(detector));
            };

            startButton.addEventListener('click', async () => {
                const hasNativeScanner = 'BarcodeDetector' in window;
                if (!hasNativeScanner && typeof window.jsQR !== 'function') {
                    message.textContent = 'Fitur scan QR tidak dapat dimuat. Periksa koneksi internet atau masukkan kode booking secara manual.';
                    return;
                }
                if (!navigator.mediaDevices?.getUserMedia) {
                    message.textContent = 'Akses kamera tidak tersedia. Buka situs melalui HTTPS atau masukkan kode booking secara manual.';
                    return;
                }

                startButton.disabled = true;
                message.textContent = 'Meminta akses kamera...';
                try {
                    const detector = hasNativeScanner ? new BarcodeDetector({ formats: ['qr_code'] }) : null;
                    cameraStream = await navigator.mediaDevices.getUserMedia({
                        audio: false,
                        video: { facingMode: { ideal: 'environment' } },
                    });
                    video.srcObject = cameraStream;
                    panel.hidden = false;
                    await video.play();
                    message.textContent = hasNativeScanner
                        ? 'Arahkan kamera ke QR booking.'
                        : 'Arahkan kamera ke QR booking. Pemindaian diproses di browser ini.';
                    animationFrame = requestAnimationFrame(() => scanFrame(detector));
                } catch (error) {
                    stopScanner();
                    setErrorMessage(error);
                }
            });

            stopButton.addEventListener('click', () => {
                stopScanner();
                message.textContent = 'Pemindaian dihentikan. Kamu dapat mencoba lagi atau memasukkan kode booking secara manual.';
            });
            window.addEventListener('pagehide', stopScanner);
        })();

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
