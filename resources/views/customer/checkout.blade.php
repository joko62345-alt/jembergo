<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>

<body class="customer-page"><x-public-navbar />
    @php(
    $participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values()
)
    @php($canCancelBooking = $booking->status_pemesanan === 'PENDING'
        && $booking->pembayaran?->status_pembayaran === 'PENDING'
        && ! filled($booking->pembayaran?->order_id)
        && ! filled($booking->pembayaran?->snap_token)
        && ! filled($booking->pembayaran?->qris_url)
        && ! filled($booking->pembayaran?->transaction_id)
        && ! filled($booking->pembayaran?->referensi_gateway)
        && $booking->tiket->isEmpty())
    <main class="container checkout-page">
        <a href="{{ route('customer.booking.create', ['id' => $booking->id_destinasi, 'booking_id' => $booking->id_pemesanan]) }}"
            class="checkout-back customer-back-link"><i class="bi bi-arrow-left"></i> Kembali ke data peserta</a>
        <div class="checkout-heading">
            <div>
                <h1>Konfirmasi dan pembayaran</h1>
                <p>Periksa kembali pesananmu sebelum melakukan pembayaran.</p>
            </div><span class="checkout-code">
                {{ $booking->kode_booking }}</span>
        </div>
        <nav class="checkout-steps" aria-label="Tahapan pemesanan">
            <span class="is-done"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Data peserta</span>
            <span class="is-current" aria-current="step"><i class="bi bi-credit-card" aria-hidden="true"></i>Pembayaran</span>
            <span><i class="bi bi-ticket-perforated" aria-hidden="true"></i>E-ticket</span>
        </nav>
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="customer-card checkout-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="checkout-card-heading">
                            <span class="checkout-heading-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                            <div>
                                <h2 class="checkout-section-title">Ringkasan pesanan</h2>
                                <p>Pastikan detail perjalanan dan peserta sudah sesuai.</p>
                            </div>
                        </div>
                        @php($quota = $booking->destinasi->kuota_harian_aktif === 'aktif' ? $booking->destinasi->kuota_harian : null)
                        @php($booked = $quota ? $booking->destinasi->bookedTicketsForDate($booking->tanggal_kunjungan->toDateString()) : null)
                        <div class="checkout-summary">
                            <div class="checkout-destination">
                                <span class="checkout-summary-icon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></span>
                                <div><small>Destinasi
                                        wisata</small><strong>{{ $booking->destinasi->nama_wisata }}</strong>
                                </div>
                            </div>
                            <div class="checkout-date">
                                <span class="checkout-summary-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
                                <div>
                                    <small>Tanggal kunjungan</small>
                                    <strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong>
                                    <small class="mt-1">
                                        @if ($quota)
                                            Sisa kuota: {{ max(0, $quota - $booked) }} peserta
                                        @else
                                            Destinasi ini tidak menerapkan batas kuota tiket
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="checkout-facts">
                            <div><small>Ketua
                                    kelompok</small><strong>{{ $booking->ketua_nama }}</strong>
                            </div>
                            <div><small>Total peserta</small><strong>{{ $participants->count() }}
                                    orang</strong></div>
                            <div><small>Total pesanan</small><strong class="text-orange">Rp
                                    {{ number_format($booking->total_harga, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="checkout-participants"><strong>Rincian peserta</strong>
                            @foreach ($participants as $participant)
                                <div><span>{{ $participant['nama'] }} @if ($loop->first)
                                            <small>(Ketua)</small>
                                        @endif
                                    </span>
                                    <span>{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="checkout-payment-note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            E-ticket diterbitkan setelah pembayaran berhasil dikonfirmasi.
                        </p>
                        @if ($errors->has('payment') || $errors->has('cancel'))
                            <div class="alert alert-danger"><i
                                    class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first('payment') ?: $errors->first('cancel') }}
                            </div>
                        @endif

                        <div class="checkout-actions">
                            @if ($canCancelBooking)
                                <button class="checkout-cancel" type="button" data-bs-toggle="modal"
                                    data-bs-target="#cancelBookingModal">Batalkan pesanan</button>
                            @endif
                            @if ($booking->pembayaran?->snap_token)
                                <button class="checkout-submit" id="pay-button"
                                    type="button"><span>Bayar Sekarang</span></button>
                            @else
                                <form method="POST" action="{{ route('customer.payment', $booking->id_pemesanan) }}"
                                    class="checkout-payment-form">
                                    <button class="checkout-submit" type="submit"><span>Bayar Sekarang</span></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <aside class="checkout-side">
                    <div class="checkout-side-heading">
                        <span class="checkout-side-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
                        <div>
                            <h2>Pemesanan aman</h2>
                            <p>Data pesananmu tercatat dan e-ticket akan diterbitkan.</p>
                        </div>
                    </div>
                    <div class="checkout-side-line">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <span>Pembayaran diproses dengan aman.</span>
                    </div>
                    <div class="checkout-side-line mb-0">
                        <i class="bi bi-headset" aria-hidden="true"></i>
                        <span>Butuh bantuan? Hubungi pengelola destinasi.</span>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    @if ($canCancelBooking)
        <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title fs-5" id="cancelBookingModalLabel">Batalkan pesanan?</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="mb-0">Pesanan ini akan dibatalkan dan tidak dapat dilanjutkan kembali.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill"
                            data-bs-dismiss="modal">Tidak, kembali</button>
                        <form method="POST" action="{{ route('customer.booking.cancel', $booking->id_pemesanan) }}">
                            @csrf
                            <button type="submit" class="btn btn-warning rounded-pill">Ya, batalkan pesanan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center py-4">
                    <div class="mb-3" style="font-size:2rem; color:#1ea878;">✓</div>
                    <h5 class="fw-bold mb-2">Pembayaran berhasil</h5>
                    <p class="mb-0 text-secondary">Anda akan diarahkan ke halaman e-ticket.</p>
                </div>
            </div>
        </div>
    </div>
    <x-public-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
@if ($booking->pembayaran?->snap_token)
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        const ticketUrl = @json(route('customer.ticket', $booking->id_pemesanan));
        const showPaymentSuccessModal = () => {
            const modal = document.getElementById('paymentSuccessModal');
            if (window.bootstrap && modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                setTimeout(() => window.location.href = ticketUrl, 1400);
                return;
            }

            window.alert('Pembayaran berhasil. Anda akan diarahkan ke tiket saya.');
            window.location.href = ticketUrl;
        };

        const payButton = document.getElementById('pay-button');
        const openSnap = () => window.snap.pay(@json($booking->pembayaran->snap_token), {
            onSuccess: () => showPaymentSuccessModal(),
            onPending: () => {
                window.alert('Pembayaran masih menunggu konfirmasi.');
            },
            onError: () => {
                window.alert('Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: () => {
                window.alert('Pembayaran belum selesai.');
            },
        });
        payButton.addEventListener('click', openSnap);
        @if (session('open_snap'))
            openSnap();
        @endif
        setInterval(async () => {
            try {
                const response = await fetch(window.location.href, {
                    cache: 'no-store',
                    headers: {
                        Accept: 'application/json'
                    }
                });
                const data = response.ok ? await response.json() : {};
                if (data.status === 'PAID') showPaymentSuccessModal();
            } catch (error) {}
        }, 10000);
    </script>
@endif
<style>
    .customer-page main.checkout-page {
        font-family: "DM Sans", sans-serif;
        color: #173b60;
        font-size: .9rem;
        line-height: 1.5;
        padding-top: 2.5rem;
        padding-bottom: 4.5rem;
    }

    .checkout-heading {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin: 1.25rem 0 1.25rem;
    }

    .checkout-heading h1 {
        margin: .45rem 0 .35rem;
        font-size: clamp(1.25rem, 1.8vw, 1.55rem) !important;
        line-height: 1.2;
        font-weight: 600;
    }

    .checkout-heading p {
        margin: 0;
        color: #6b7f91;
    }

    .checkout-code {
        padding: .55rem .8rem;
        border: 1px solid #dfe7ed;
        border-radius: .7rem;
        background: #fff;
        color: #64748b;
        font-size: .8rem;
        white-space: nowrap;
    }

    .checkout-steps {
        display: flex;
        align-items: center;
        gap: 1.75rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .checkout-steps span {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .65rem 0 .75rem;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-size: .78rem;
    }

    .checkout-steps i {
        font-size: .9rem;
    }

    .checkout-steps .is-done {
        color: #173b60;
    }

    .checkout-steps .is-current {
        border-color: #173b60;
        color: #173b60;
        font-weight: 600;
    }

    .checkout-card {
        border-color: #dfe7ed;
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .05);
    }

    .checkout-card-heading {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .checkout-heading-icon,
    .checkout-side-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 2.5rem;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: .75rem;
        background: #e8f3f8;
        color: #173b60;
        font-size: 1.1rem;
    }

    .checkout-section-title {
        margin: 0;
        font-size: 1rem !important;
        font-weight: 600;
        color: #173b60;
    }

    .checkout-card-heading p {
        margin: .15rem 0 0;
        color: #64748b;
        font-size: .8rem;
    }

    .checkout-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem;
        margin: 1.25rem 0;
        border: 1px solid #edf1f5;
        border-radius: .85rem;
        background: #f8fafc;
    }

    .checkout-destination,
    .checkout-date {
        display: flex;
        align-items: center;
        gap: .7rem;
    }

    .checkout-summary-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 2.35rem;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: .7rem;
        background: #e8f3f8;
        color: #315d78;
        font-size: 1rem;
    }

    .checkout-destination strong,
    .checkout-date strong {
        display: block;
        color: #173b60;
    }

    .checkout-destination small,
    .checkout-date small,
    .checkout-facts small {
        display: block;
        color: #64748b;
        font-size: .75rem;
        margin-bottom: .2rem;
    }

    .checkout-date {
        flex-direction: row-reverse;
        text-align: right;
    }

    .checkout-date small:last-child {
        color: #567087;
        font-size: .72rem;
    }

    .checkout-facts {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .75rem;
        padding-bottom: 1.2rem;
        border-bottom: 1px solid #e4ebf0;
    }

    .checkout-facts strong {
        color: #173b60;
        font-size: .92rem;
    }

    .checkout-facts > div:last-child strong {
        color: #173b60;
    }

    .checkout-participants {
        padding: 1.15rem 0;
        border-bottom: 1px solid #e4ebf0;
    }

    .checkout-participants>strong {
        color: #173b60;
    }

    .checkout-participants>div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem 0;
        border-bottom: 1px solid #edf1f5;
        color: #405a70;
        font-size: .88rem;
    }

    .checkout-participants>div:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .checkout-participants>div span:last-child {
        color: #7890a1;
    }

    .checkout-participants small {
        color: #7890a1;
    }

    .checkout-payment-note {
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        padding: .85rem .9rem;
        margin: 1rem 0;
        border: 1px solid #e5edf2;
        border-radius: .75rem;
        background: #f8fafc;
        color: #567087;
        font-size: .82rem;
    }

    .checkout-payment-note i {
        margin-top: .12rem;
        color: #315d78;
    }

    .payment-options {
        display: grid;
        gap: .75rem;
        margin-bottom: 1.25rem;
    }

    .payment-option {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: 1rem;
        border: 1px solid #dbe6ee;
        border-radius: .9rem;
        cursor: pointer;
        transition: border-color 160ms ease, background 160ms ease, box-shadow 160ms ease;
    }

    .payment-option:hover,
    .payment-option.is-selected {
        border-color: #173b60;
        background: #f3f8fb;
        box-shadow: 0 5px 14px rgba(23, 59, 96, .09);
    }

    .payment-option input {
        position: absolute;
        opacity: 0;
    }

    .payment-option-icon {
        display: inline-grid;
        place-items: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: .7rem;
        background: #eef5f8;
        color: #35627f;
        font-size: 1.15rem;
    }

    .payment-option strong,
    .payment-option small {
        display: block;
    }

    .payment-option strong {
        color: #173b60;
        font-size: .92rem;
    }

    .payment-option small {
        color: #7890a1;
        font-size: .78rem;
        margin-top: .18rem;
    }

    .payment-check {
        margin-left: auto;
        color: #173b60;
        opacity: 0;
    }

    .payment-option.is-selected .payment-check {
        opacity: 1;
    }

    .qris-only-note {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: 1rem;
        margin-bottom: 1.25rem;
        border: 1px solid #dbe6ee;
        border-radius: .85rem;
        background: #fff;
    }

    .qris-only-note>i {
        color: #315d78;
        font-size: 1.3rem;
    }

    .qris-only-note strong,
    .qris-only-note small {
        display: block;
    }

    .qris-only-note strong {
        color: #173b60;
        font-size: .92rem;
    }

    .qris-only-note small {
        margin-top: .15rem;
        color: #7890a1;
        font-size: .78rem;
    }

    .customer-page .checkout-submit {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        padding: .65rem .5rem;
        border: 1px solid #f58b05;
        border-radius: .65rem;
        background: #f58b05 !important;
        background-image: none !important;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 8px 18px rgba(245, 139, 5, .2);
        transition: background-color 160ms ease, transform 160ms ease, box-shadow 160ms ease;
    }

    .customer-page .checkout-submit i {
        position: absolute;
        right: 1.1rem;
    }

    .customer-page .checkout-submit:hover,
    .customer-page .checkout-submit:focus-visible,
    .customer-page .checkout-submit:active {
        border-color: #df7900;
        background: #df7900 !important;
        background-image: none !important;
        color: #fff;
        box-shadow: 0 10px 20px rgba(245, 139, 5, .24);
        transform: translateY(-1px);
    }

    .checkout-actions {
        display: flex;
        align-items: stretch;
        gap: .75rem;
        margin-top: .75rem;
    }

    .checkout-actions>.checkout-submit,
    .checkout-payment-form,
    .checkout-cancel {
        flex: 1 1 0;
        min-width: 0;
    }

    .checkout-payment-form {
        display: flex;
    }

    .checkout-cancel {
        width: 100%;
        min-height: 48px;
        padding: .65rem .5rem;
        border: 1px solid #f58b05;
        border-radius: .65rem;
        background: #fff;
        color: #df7900;
        font: inherit;
        font-size: .82rem;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        transition: background-color 160ms ease, color 160ms ease, transform 160ms ease;
    }

    .checkout-cancel:hover,
    .checkout-cancel:focus-visible {
        border-color: #df7900;
        background: #f58b05;
        color: #fff;
        transform: translateY(-1px);
    }

    .checkout-side {
        position: sticky;
        top: 6.5rem;
        padding: 1.35rem;
        border: 1px solid #dfe7ed;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 41, 55, .05);
    }

    .checkout-side-heading {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
    }

    .checkout-side h2 {
        margin: .1rem 0 .35rem;
        color: #173b60;
        font-size: 1rem !important;
        font-weight: 600;
    }

    .checkout-side p {
        margin: 0;
        color: #64748b;
        font-size: .82rem;
    }

    .checkout-side-line {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        padding-top: .7rem;
        margin-top: .7rem;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: .8rem;
    }

    .checkout-side-line i {
        margin-top: .15rem;
        color: #315d78;
    }

    @media (max-width: 767.98px) {
        .customer-page main.checkout-page {
            padding-top: 1.5rem;
            padding-bottom: 3rem;
        }

        .checkout-heading {
            align-items: flex-start;
        }

        .checkout-code {
            margin-top: .1rem;
        }

        .checkout-summary {
            align-items: flex-start;
            flex-direction: column;
        }

        .checkout-date {
            flex-direction: row;
            text-align: left;
        }

        .checkout-facts {
            grid-template-columns: 1fr;
            gap: .85rem;
        }

        .checkout-facts > div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .75rem;
        }

        .checkout-facts small {
            margin: 0;
        }

        .checkout-facts strong {
            text-align: left;
        }

        .checkout-side {
            position: static;
        }

        .checkout-steps {
            overflow-x: auto;
            padding-bottom: .25rem;
        }

        .checkout-steps span {
            flex: 0 0 auto;
        }

    }

    @media (max-width: 575.98px) {
        .checkout-heading {
            display: block;
        }

        .checkout-code {
            display: inline-block;
            margin-top: .75rem;
        }

        .checkout-card .card-body {
            padding: 1.25rem !important;
        }

        .checkout-steps {
            gap: 1rem;
        }

        .checkout-steps span {
            font-size: .72rem;
        }

        .checkout-participants>div {
            align-items: flex-start;
            font-size: .82rem;
        }
    }
</style>
<script>
    document.querySelectorAll('.payment-option input').forEach(input => input.addEventListener(
        'change', () => document.querySelectorAll('.payment-option').forEach(option =>
            option.classList.toggle('is-selected', option.querySelector('input').checked))));
</script>
