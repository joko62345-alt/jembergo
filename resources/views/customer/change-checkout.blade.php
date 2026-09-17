@extends('layouts.app')

@section('title', 'Review Perubahan - JemberGo')

@section('content')
@include('components.public-navbar')

@php
    $newDate = $changeData['tanggal_kunjungan'] ?: $booking->tanggal_kunjungan->toDateString();
    $newMembers = collect($changeData['anggota'] ?? []);
    $totalTickets = 1 + count($booking->anggota_names ?? []) + $newMembers->count();
@endphp

<main class="review-page">
    <div class="container review-container">
        <a href="{{ route('customer.manage', $booking->id_pemesanan) }}" class="review-back"><i class="bi bi-arrow-left"></i> Kembali ke Kelola Perjalanan</a>
        <div class="review-header"><div><span>Review perubahan</span><h1>Periksa sebelum melanjutkan.</h1><p>Perubahan pada tiket akan diperbarui setelah pembayaran berhasil.</p></div><span class="review-code">{{ $booking->kode_booking }}</span></div>
        <div class="review-steps"><div class="done"><span>1</span>Pilih Jadwal</div><i class="bi bi-chevron-right"></i><div class="active"><span>2</span>Review Perubahan</div><i class="bi bi-chevron-right"></i><div><span>3</span>Pembayaran</div><i class="bi bi-chevron-right"></i><div><span>4</span>Selesai</div></div>
        <div class="review-layout">
            <section class="review-main">
                @if($errors->has('payment'))
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first('payment') }}</div>
                @endif
                <article class="review-card"><div class="review-card-title"><i class="bi bi-calendar2-week"></i><div><span>Perubahan perjalanan</span><h2>{{ $booking->destinasi->nama_wisata }}</h2></div></div><div class="date-change"><div><small>Jadwal lama</small><strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong></div><i class="bi bi-arrow-right"></i><div class="new-date"><small>Jadwal baru</small><strong>{{ \Carbon\Carbon::parse($newDate)->translatedFormat('d F Y') }}</strong></div></div></article>
                <article class="review-card"><div class="review-card-title"><i class="bi bi-ticket-perforated"></i><div><span>Data tiket</span><h2>Peserta perjalanan</h2></div></div><div class="ticket-counts"><div><small>Tiket lama</small><strong>{{ 1 + count($booking->anggota_names ?? []) }} tiket</strong></div><div><small>Tiket tambahan</small><strong>+{{ $newMembers->count() }} tiket</strong></div><div><small>Total tiket</small><strong>{{ $totalTickets }} tiket</strong></div></div>@if($newMembers->isNotEmpty())<div class="new-members"><strong>Anggota yang ditambahkan</strong>@foreach($newMembers as $member)<div><span>{{ $member['nama'] }}</span><span>{{ $booking->destinasi->jenisTiket->firstWhere('id_jenis_tiket', (int) $member['id_jenis_tiket'])?->nama_jenis }} · Rp {{ number_format($member['harga'], 0, ',', '.') }}</span></div>@endforeach</div>@endif</article>
            </section>
            <aside class="review-summary"><div class="review-summary-card"><span class="summary-label">Ringkasan biaya</span><h2>{{ $booking->destinasi->nama_wisata }}</h2><div class="summary-row"><span>Tanggal kunjungan</span><strong>{{ \Carbon\Carbon::parse($newDate)->format('d/m/Y') }}</strong></div><div class="summary-row"><span>Jumlah tiket</span><strong>{{ $totalTickets }} tiket</strong></div><div class="summary-row"><span>Biaya tambahan</span><strong>Rp {{ number_format($change->nominal, 0, ',', '.') }}</strong></div><div class="summary-total"><span>TOTAL PEMBAYARAN</span><strong>Rp {{ number_format($change->nominal, 0, ',', '.') }}</strong></div>@if((float) $change->nominal > 0)@if($change->snap_token)<button class="review-action" id="change-pay-button" type="button">Lanjut ke Pembayaran <i class="bi bi-arrow-right"></i></button>@else<form method="POST" action="{{ route('customer.change.payment', [$booking->id_pemesanan, $change->id_perubahan]) }}">@csrf<input type="hidden" name="metode_pembayaran" value="QRIS"><button class="review-action" type="submit">Lanjut ke Pembayaran <i class="bi bi-arrow-right"></i></button></form>@endif<small class="summary-note">Pembayaran diproses melalui QRIS. Booking diperbarui setelah berhasil.</small>@else<form method="POST" action="{{ route('customer.change.payment', [$booking->id_pemesanan, $change->id_perubahan]) }}">@csrf<button class="review-action" type="submit">Konfirmasi Perubahan <i class="bi bi-check2"></i></button></form><small class="summary-note">Tidak ada biaya tambahan untuk perubahan ini.</small>@endif</div></aside>
        </div>
    </div>
</main>

<style>
    .review-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: .9rem; line-height: 1.5; }
    .review-header h1 { font-size: clamp(1.7rem, 2.8vw, 2.25rem); line-height: 1.2; }
    .review-card-title h2, .review-summary-card h2 { font-size: 1.05rem; line-height: 1.3; }
    .review-card-title span, .summary-label { font-size: .68rem; }
    .review-page { min-height:100vh; padding:7.5rem 0 5rem; background:#f7f9fb; color:#173b60; }.review-container { max-width:1160px; }.review-back { color:#61788b; text-decoration:none; font-size:.86rem; }.review-back:hover { color:#f58b05; }.review-header { display:flex; justify-content:space-between; align-items:end; gap:1rem; margin:2rem 0 1.5rem; }.review-header span:first-child,.summary-label { color:#f58b05; font-size:.72rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }.review-header h1 { margin:.45rem 0; font-size:clamp(2rem,4vw,3rem); font-weight:600; }.review-header p { margin:0; color:#708496; }.review-code { padding:.7rem .9rem; border:1px solid #dce6ed; border-radius:.65rem; background:#fff; font-family:monospace; font-size:.8rem; }.review-steps { display:flex; align-items:center; gap:.7rem; padding:1rem; margin-bottom:1.25rem; overflow:auto; border:1px solid #dce6ed; border-radius:.8rem; background:#fff; color:#9aabb7; font-size:.76rem; }.review-steps div { display:flex; align-items:center; gap:.4rem; flex:0 0 auto; white-space:nowrap; }.review-steps span { display:grid; place-items:center; width:24px; height:24px; border:1px solid #cdd9e2; border-radius:50%; }.review-steps .done,.review-steps .active { color:#173b60; }.review-steps .done span { background:#315d78; border-color:#315d78; color:#fff; }.review-steps .active span { background:#f58b05; border-color:#f58b05; color:#fff; }.review-steps i { color:#c5d0d8; }.review-layout { display:grid; grid-template-columns:minmax(0,1.85fr) minmax(290px,1fr); gap:1.25rem; align-items:start; }.review-main { display:grid; gap:1.25rem; }.review-card,.review-summary-card { border:1px solid #dce6ed; border-radius:1rem; background:#fff; box-shadow:0 8px 24px rgba(23,59,96,.04); }.review-card { padding:1.5rem; }.review-card-title { display:flex; align-items:center; gap:.75rem; padding-bottom:1.1rem; margin-bottom:1.1rem; border-bottom:1px solid #edf1f4; }.review-card-title > i { color:#315d78; font-size:1.25rem; }.review-card-title span { color:#f58b05; font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }.review-card-title h2 { margin:.25rem 0 0; font-size:1.2rem; }.date-change { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:1rem; }.date-change div { display:grid; gap:.35rem; padding:1rem; border-radius:.7rem; background:#f5f8fa; }.date-change .new-date { border:1px solid #f1c16e; background:#fffaf0; }.date-change small,.ticket-counts small { color:#708496; font-size:.75rem; }.date-change strong { font-size:1rem; }.date-change > i { color:#f58b05; }.ticket-counts { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; }.ticket-counts div { display:grid; gap:.3rem; padding:.9rem; border:1px solid #e5edf2; border-radius:.65rem; }.ticket-counts strong { font-size:1.05rem; }.new-members { margin-top:1rem; padding-top:1rem; border-top:1px solid #edf1f4; }.new-members > strong { font-size:.85rem; }.new-members > div { display:flex; justify-content:space-between; gap:1rem; padding:.7rem 0; border-bottom:1px solid #edf1f4; font-size:.82rem; }.new-members > div span:last-child { color:#708496; text-align:right; }.review-summary-card { position:sticky; top:6.5rem; padding:1.5rem; }.review-summary-card h2 { margin:.45rem 0 1.2rem; font-size:1.2rem; }.summary-row { display:flex; justify-content:space-between; gap:1rem; padding:.65rem 0; color:#708496; font-size:.82rem; }.summary-row strong { color:#173b60; text-align:right; }.summary-total { display:flex; justify-content:space-between; align-items:end; gap:1rem; padding-top:1rem; margin-top:.65rem; border-top:1px solid #e5edf2; font-size:.8rem; }.summary-total strong { color:#f58b05; font-size:1.25rem; }.review-action { display:flex; justify-content:center; align-items:center; gap:.5rem; width:100%; margin-top:1.3rem; padding:.85rem 1rem; border:0; border-radius:.65rem; background:#f58b05; color:#fff; font-weight:700; }.review-action:hover { background:#d97706; }.summary-note { display:block; margin-top:.7rem; color:#8a9aa6; font-size:.72rem; line-height:1.5; text-align:center; }
    @media(max-width:800px){.review-header{align-items:start;flex-direction:column}.review-layout{grid-template-columns:1fr}.review-summary-card{position:static}} @media(max-width:560px){.review-page{padding-top:6rem}.review-card,.review-summary-card{padding:1rem}.date-change{grid-template-columns:1fr}.date-change>i{transform:rotate(90deg);justify-self:center}.ticket-counts{grid-template-columns:1fr}.review-code{align-self:stretch;text-align:center}}
    .review-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: .9rem; line-height: 1.5; }
    .review-header h1 { font-size: clamp(1.7rem, 2.8vw, 2.25rem); line-height: 1.2; }
    .review-card-title h2, .review-summary-card h2 { font-size: 1.05rem; line-height: 1.3; }
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
@if($change->snap_token)
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
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

        const openChangeSnap = () => window.snap.pay(@json($change->snap_token), {
            onSuccess: () => showPaymentSuccessModal(),
            onPending: () => { window.alert('Pembayaran masih menunggu konfirmasi.'); },
            onError: () => { window.alert('Pembayaran gagal. Silakan coba lagi.'); },
            onClose: () => { window.alert('Pembayaran belum selesai.'); },
        });
        document.getElementById('change-pay-button').addEventListener('click', openChangeSnap);
        @if(session('open_snap'))
            openChangeSnap();
        @endif
        setInterval(async () => {
            try {
                const response = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'application/json' } });
                const data = response.ok ? await response.json() : {};
                if (data.status === 'PAID') showPaymentSuccessModal();
            } catch (error) {}
        }, 10000);
    </script>
@endif
@endsection
