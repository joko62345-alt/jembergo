@extends('layouts.app')

@section('title', 'Review Perubahan - JemberGo')

@section('content')
    <div class="review-flow">
        @include('components.public-navbar')

    @php
        $newDate = $changeData['tanggal_kunjungan'] ?: $booking->tanggal_kunjungan->toDateString();
        $newMembers = collect($changeData['anggota'] ?? []);
        $totalTickets = 1 + count($booking->nama_anggota ?? []) + $newMembers->count();
    @endphp

    <main class="review-page">
        <div class="container review-container">
            <a href="{{ route('customer.manage', $booking->id_pemesanan) }}" class="review-back customer-back-link"><i
                    class="bi bi-arrow-left"></i> Kembali ke Kelola Perjalanan</a>
            <div class="review-header">
                <div><span>Review perubahan</span>
                    <h1>Periksa sebelum melanjutkan.</h1>
                    <p>Perubahan pada tiket akan diperbarui setelah pembayaran berhasil.</p>
                </div>
            </div>
            <div class="review-steps">
                <div class="done"><span>1</span>Pilih Jadwal</div>
                <div class="active"><span>2</span>Review Perubahan</div>
                <div><span>3</span>Pembayaran</div>
                <div><span>4</span>Selesai</div>
            </div>
            <div class="review-layout">
                <section class="review-main">
                    @if ($errors->has('payment'))
                        <div class="alert alert-danger"><i
                                class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first('payment') }}
                        </div>
                    @endif
                    <article class="review-card">
                        <div class="review-card-title">
                            <div><span>Perubahan perjalanan</span>
                                <h2>{{ $booking->destinasi->nama_wisata }}</h2>
                            </div>
                        </div>
                        <div class="date-change">
                            <div><small>Jadwal
                                    lama</small><strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong>
                            </div><i class="bi bi-arrow-right"></i>
                            <div class="new-date"><small>Jadwal
                                    baru</small><strong>{{ \Carbon\Carbon::parse($newDate)->translatedFormat('d F Y') }}</strong>
                            </div>
                        </div>
                    </article>
                    <article class="review-card">
                        <div class="review-card-title">
                            <div><span>Data tiket</span>
                                <h2>Peserta perjalanan</h2>
                            </div>
                        </div>
                        <div class="ticket-counts">
                            <div><small>Tiket
                                    lama</small><strong>{{ 1 + count($booking->nama_anggota ?? []) }}
                                    tiket</strong></div>
                            <div><small>Tiket tambahan</small><strong>+{{ $newMembers->count() }}
                                    tiket</strong></div>
                            <div><small>Total tiket</small><strong>{{ $totalTickets }} tiket</strong>
                            </div>
                        </div>
                        @if ($newMembers->isNotEmpty())
                            <div class="new-members"><strong>Anggota yang ditambahkan</strong>
                                @foreach ($newMembers as $member)
                                    <div><span>{{ $member['nama'] }}</span><span>{{ $booking->destinasi->jenisTiket->firstWhere('id_jenis_tiket', (int) $member['id_jenis_tiket'])?->nama_jenis }}
                                            · Rp
                                            {{ number_format($member['harga'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                </section>
                <aside class="review-summary">
                    <div class="review-summary-card"><span class="summary-label">Ringkasan biaya</span>
                        <h2>{{ $booking->destinasi->nama_wisata }}</h2>
                        <div class="summary-row"><span>Tanggal
                                kunjungan</span><strong>{{ \Carbon\Carbon::parse($newDate)->format('d/m/Y') }}</strong>
                        </div>
                        <div class="summary-row"><span>Jumlah tiket</span><strong>{{ $totalTickets }}
                                tiket</strong></div>
                        <div class="summary-row"><span>Biaya tambahan</span><strong>Rp
                                {{ number_format($change->nominal, 0, ',', '.') }}</strong></div>
                        <div class="summary-total"><span>TOTAL PEMBAYARAN</span><strong>Rp
                                {{ number_format($change->nominal, 0, ',', '.') }}</strong></div>
                        @if ((float) $change->nominal > 0)
                            <form method="POST"
                                action="{{ route('customer.change.payment', [$booking->id_pemesanan, $change->id_perubahan]) }}">
                                @csrf<input type="hidden" name="metode_pembayaran"
                                    value="QRIS"><button class="review-action"
                                    type="submit">Lanjut ke Pembayaran</button></form>
                            <small class="summary-note">
                                Pembayaran diproses melalui QRIS. Booking diperbarui setelah
                            berhasil.</small>@else<form method="POST"
                                action="{{ route('customer.change.payment', [$booking->id_pemesanan, $change->id_perubahan]) }}">
                                @csrf<button class="review-action" type="submit">Konfirmasi Perubahan</button></form><small
                                class="summary-note">Tidak ada biaya tambahan untuk perubahan
                                ini.</small>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </main>
    </div>

    <style>
        .review-flow,
        .review-flow .btn,
        .review-flow .form-control,
        .review-flow .form-select,
        .review-flow .navbar .dropdown-item {
            font-family: "DM Sans", sans-serif !important;
        }

        .review-page {
            font-family: "DM Sans", sans-serif;
            color: #173b60;
            font-size: .9rem;
            line-height: 1.5;
            min-height: 100vh;
            padding: 2.5rem 0 4.5rem;
            background: #f7f9fb;
        }

        .review-container {
            max-width: 1050px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            margin: 1.25rem 0;
        }

        .review-header span:first-child,
        .summary-label {
            color: #567087;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .review-header h1 {
            margin: .45rem 0 .35rem;
            font-size: clamp(1.25rem, 1.8vw, 1.55rem);
            font-weight: 600;
            line-height: 1.2;
        }

        .review-header p {
            margin: 0;
            color: #6b7f91;
        }

        .review-steps {
            display: flex;
            align-items: center;
            gap: 1.75rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            border-bottom: 1px solid #e2e8f0;
        }

        .review-steps div {
            display: flex;
            align-items: center;
            gap: .45rem;
            flex: 0 0 auto;
            padding: .65rem 0 .75rem;
            border-bottom: 2px solid transparent;
            color: #64748b;
            font-size: .78rem;
            white-space: nowrap;
        }

        .review-steps span {
            display: grid;
            place-items: center;
            width: 1.35rem;
            height: 1.35rem;
            border: 1px solid #cdd9e2;
            border-radius: 50%;
            font-size: .72rem;
        }

        .review-steps .done,
        .review-steps .active {
            color: #173b60;
        }

        .review-steps .active {
            border-color: #173b60;
            font-weight: 600;
        }

        .review-steps .done span {
            background: #315d78;
            border-color: #315d78;
            color: #fff;
        }

        .review-steps .active span {
            background: #173b60;
            border-color: #173b60;
            color: #fff;
        }

        .review-layout {
            display: grid;
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
            gap: 1.5rem;
            align-items: start;
        }

        .review-main {
            display: grid;
            gap: 1rem;
        }

        .review-card,
        .review-summary-card {
            border: 1px solid #dce6ed;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgba(23, 59, 96, .04);
        }

        .review-card {
            padding: 1.5rem;
        }

        .review-card-title {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding-bottom: 1.1rem;
            margin-bottom: 1.1rem;
            border-bottom: 1px solid #edf1f4;
        }

        .review-card-title span {
            color: #567087;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .review-card-title h2 {
            margin: .25rem 0 0;
            color: #173b60;
            font-size: 1rem;
            font-weight: 600;
        }

        .date-change {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
        }

        .date-change div {
            display: grid;
            gap: .35rem;
            padding: 1rem;
            border: 1px solid #edf1f5;
            border-radius: .7rem;
            background: #f8fafc;
        }

        .date-change .new-date {
            border-color: #cbdde8;
            background: #f3f8fb;
        }

        .date-change small,
        .ticket-counts small {
            color: #708496;
            font-size: .75rem;
        }

        .date-change strong {
            color: #173b60;
            font-size: .92rem;
        }

        .date-change>i {
            color: #315d78;
        }

        .ticket-counts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
        }

        .ticket-counts div {
            display: grid;
            gap: .3rem;
            padding: .9rem;
            border: 1px solid #dfe7ed;
            border-radius: .7rem;
        }

        .ticket-counts strong {
            color: #173b60;
            font-size: .92rem;
        }

        .new-members {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #edf1f4;
        }

        .new-members>strong {
            font-size: .85rem;
        }

        .new-members>div {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .7rem 0;
            border-bottom: 1px solid #edf1f4;
            font-size: .82rem;
        }

        .new-members>div span:last-child {
            color: #708496;
            text-align: right;
        }

        .review-summary-card {
            position: sticky;
            top: 6.5rem;
            padding: 1.5rem;
        }

        .review-summary-card h2 {
            margin: .45rem 0 1.2rem;
            color: #173b60;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .65rem 0;
            color: #708496;
            font-size: .82rem;
        }

        .summary-row strong {
            max-width: 58%;
            color: #173b60;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            padding-top: 1rem;
            margin-top: .65rem;
            border-top: 1px solid #e5edf2;
            font-size: .8rem;
        }

        .summary-total strong {
            color: #f58b05;
            font-size: 1.25rem;
        }

        .review-action {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            width: 100%;
            margin-top: 1.3rem;
            padding: .85rem 1rem;
            border: 0;
            border-radius: .65rem;
            background: #f58b05;
            color: #fff;
            font-weight: 600;
            transition: background-color 160ms ease, transform 160ms ease;
        }

        .review-action:hover {
            background: #df7900;
            color: #fff;
            transform: translateY(-1px);
        }

        .review-action:focus-visible {
            outline: 2px solid #315d78;
            outline-offset: 3px;
        }

        .summary-note {
            display: block;
            margin-top: .7rem;
            color: #8a9aa6;
            font-size: .72rem;
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .review-header {
                align-items: flex-start;
                flex-direction: column
            }

            .review-layout {
                grid-template-columns: 1fr;
            }

            .review-summary-card {
                position: static;
            }
        }

        @media (max-width: 575.98px) {
            .review-page {
                padding-top: 1.5rem;
                padding-bottom: 3rem;
            }

            .review-steps {
                gap: .9rem;
            }

            .review-card,
            .review-summary-card {
                padding: 1.25rem;
            }

            .date-change {
                grid-template-columns: 1fr;
            }

            .date-change>i {
                transform: rotate(90deg);
                justify-self: center;
            }

            .ticket-counts {
                grid-template-columns: 1fr;
            }

            .new-members>div {
                align-items: flex-start;
                flex-direction: column;
                gap: .25rem;
            }

        }
    </style>
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
@endsection
