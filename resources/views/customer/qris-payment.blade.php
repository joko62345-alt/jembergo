@extends('layouts.app')

@section('title', 'Pembayaran QRIS - JemberGo')

@section('content')
@include('components.public-navbar')

@php
    $isChange = $change !== null;
    $amount = (float) ($payment->gross_amount ?? $payment->nominal);
    $expiresAt = $payment->qris_expires_at?->timestamp ?? now()->addHours(2)->timestamp;
    $paymentLabel = match ($payment->status_pembayaran ?? $payment->status ?? 'PENDING') {
        'PAID' => 'Pembayaran berhasil',
        'FAILED' => 'Pembayaran gagal',
        'EXPIRED' => 'QRIS kedaluwarsa',
        default => 'Menunggu pembayaran',
    };
@endphp

<main class="qris-page">
    <div class="container qris-container">
        <a href="{{ $isChange ? route('customer.change.checkout', [$booking->id_pemesanan, $change->id_perubahan]) : route('customer.checkout', $booking->id_pemesanan) }}" class="qris-back"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div class="qris-header"><div><h1>Scan dan selesaikan pembayaran.</h1><p>Gunakan aplikasi pembayaran yang mendukung QRIS untuk membayar pesanan JemberGo.</p></div><strong>{{ $payment->order_id }}</strong></div>
        <div class="qris-grid">
            <section class="qris-card qris-main-card">
                <div class="qris-status"><span class="status-dot"></span><span>Status pembayaran</span><strong id="paymentStatus">{{ $paymentLabel }}</strong></div>
                <div class="qris-brand">QRIS</div><div class="qris-frame"><img src="{{ $payment->qris_url }}" alt="QRIS pembayaran {{ $payment->order_id }}"></div>
                <h2>Scan QRIS untuk melakukan pembayaran</h2><p class="qris-instruction">Buka aplikasi pembayaran yang mendukung QRIS, lalu scan QR Code di atas.</p>
                <div class="qris-countdown"><i class="bi bi-clock"></i><span>Berlaku selama</span><strong id="countdown">--:--:--</strong></div>
                @if(in_array($payment->status_pembayaran ?? $payment->status, ['FAILED', 'EXPIRED'], true))
                    <form method="POST" action="{{ $isChange ? route('customer.change.payment', [$booking->id_pemesanan, $change->id_perubahan]) : route('customer.payment', $booking->id_pemesanan) }}">@csrf<button class="qris-retry" type="submit">Buat QRIS baru</button></form>
                @endif
                <button type="button" class="qris-check-status" id="checkPaymentStatus">Cek status pembayaran</button>
                <div class="qris-refresh" id="refreshMessage">Status pembayaran diperbarui otomatis dari Midtrans Sandbox.</div>
            </section>
            <aside class="qris-card qris-summary"><span class="summary-label">Ringkasan pesanan</span><h2>{{ $booking->destinasi->nama_wisata }}</h2><div class="summary-row"><span>Tanggal kunjungan</span><strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong></div><div class="summary-row"><span>Jumlah tiket</span><strong>{{ 1 + count($booking->anggota_names ?? []) + ($isChange ? count($change->anggota_baru['anggota'] ?? []) : 0) }} tiket</strong></div><div class="summary-row"><span>Kode pesanan</span><strong>{{ $booking->kode_booking }}</strong></div><div class="summary-total"><span>Total pembayaran</span><strong>Rp {{ number_format($amount, 0, ',', '.') }}</strong></div><div class="only-qris"><i class="bi bi-qr-code-scan"></i><span>Metode pembayaran: <strong>QRIS</strong></span></div></aside>
        </div>
    </div>
</main>

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

<style>
    .qris-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: .9rem; line-height: 1.5; }
    .qris-header h1 { font-size: clamp(1.25rem, 1.8vw, 1.55rem) !important; line-height: 1.2; }
    .qris-main-card h2 { font-size: 1.05rem; line-height: 1.35; }
    .qris-summary h2 { font-size: 1.05rem; }
    .qris-page{min-height:100vh;padding:7.5rem 0 5rem;background:#f7f9fb;color:#173b60}.qris-container{max-width:1050px}.qris-back{color:#61788b;text-decoration:none;font-size:.86rem}.qris-header{display:flex;justify-content:space-between;align-items:end;gap:1rem;margin:2rem 0 1.5rem}.qris-header span,.summary-label{color:#f58b05;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase}.qris-header h1{margin:.45rem 0;font-size:clamp(2rem,4vw,3rem);font-weight:600}.qris-header p{margin:0;color:#708496}.qris-header>strong{padding:.7rem .9rem;border:1px solid #dce6ed;border-radius:.65rem;background:#fff;font-family:monospace;font-size:.78rem}.qris-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.75fr);gap:1.25rem;align-items:start}.qris-card{border:1px solid #dce6ed;border-radius:1rem;background:#fff;box-shadow:0 8px 24px rgba(23,59,96,.04)}.qris-main-card{padding:1.5rem;text-align:center}.qris-status{display:flex;align-items:center;justify-content:center;gap:.45rem;padding-bottom:1rem;border-bottom:1px solid #edf1f4;color:#708496;font-size:.8rem}.qris-status strong{color:#173b60}.status-dot{width:8px;height:8px;border-radius:50%;background:#f58b05}.qris-brand{margin-top:1.25rem;color:#173b60;font-size:1.1rem;font-weight:800;letter-spacing:.08em}.qris-frame{display:grid;place-items:center;width:min(100%,330px);aspect-ratio:1;margin:.75rem auto 1.5rem;padding:1rem;border:1px solid #dce6ed;border-radius:1rem;background:#fff}.qris-frame img{width:100%;height:100%;object-fit:contain}.qris-main-card h2{font-size:1.15rem}.qris-instruction{max-width:410px;margin:.5rem auto 1.2rem;color:#708496;font-size:.86rem}.qris-countdown{display:inline-flex;align-items:center;gap:.45rem;padding:.65rem .9rem;border:1px solid #dce6ed;border-radius:.65rem;color:#708496;font-size:.8rem}.qris-countdown i{color:#f58b05}.qris-countdown strong{color:#173b60;font-family:monospace}.qris-refresh{margin-top:1rem;color:#8a9aa6;font-size:.72rem}.qris-summary{position:sticky;top:6.5rem;padding:1.5rem}.qris-summary h2{margin:.45rem 0 1.3rem;font-size:1.25rem}.summary-row{display:flex;justify-content:space-between;gap:1rem;padding:.7rem 0;color:#708496;font-size:.82rem}.summary-row strong{text-align:right;color:#173b60}.summary-total{display:flex;justify-content:space-between;gap:1rem;padding-top:1rem;margin-top:.6rem;border-top:1px solid #e5edf2}.summary-total span{font-size:.82rem;color:#385872}.summary-total strong{color:#f58b05;font-size:1.3rem}.only-qris{display:flex;align-items:center;gap:.55rem;padding:.8rem;margin-top:1.2rem;border:1px solid #dce6ed;border-radius:.65rem;color:#567087;font-size:.8rem}.only-qris i{color:#315d78;font-size:1.1rem}
    .demo-payment-form{display:grid;gap:.4rem;margin:1.25rem auto 0}.demo-payment-button{padding:.8rem 1rem;border:0;border-radius:.65rem;background:#f58b05;color:#fff;font-weight:700}.demo-payment-button:hover{background:#d97706}.demo-payment-form small{color:#8a9aa6;font-size:.72rem}.qris-retry{margin-top:1rem;padding:.7rem 1rem;border:1px solid #dce6ed;border-radius:.65rem;background:#fff;color:#315d78;font-weight:600}.qris-retry:hover{border-color:#f58b05;color:#f58b05}
    @media(max-width:800px){.qris-header{align-items:start;flex-direction:column}.qris-grid{grid-template-columns:1fr}.qris-summary{position:static}}@media(max-width:560px){.qris-page{padding-top:6rem}.qris-main-card,.qris-summary{padding:1rem}.qris-header>strong{align-self:stretch;text-align:center}}
    .qris-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: .9rem; line-height: 1.5; }
    .qris-header h1 { font-size: clamp(1.7rem, 2.8vw, 2.25rem); line-height: 1.2; }
    .qris-main-card h2, .qris-summary h2 { font-size: 1rem !important; line-height: 1.35; }
    .qris-instruction { font-size: .8rem !important; }
    .qris-check-status{margin-top:1rem;padding:.7rem 1rem;border:1px solid #315d78;border-radius:.65rem;background:#fff;color:#315d78;font-weight:600}.qris-check-status:disabled{opacity:.6;cursor:wait}
</style>
<script>
(() => {
    const output = document.getElementById('countdown');
    const status = document.getElementById('paymentStatus');
    const checkButton = document.getElementById('checkPaymentStatus');
    const refreshMessage = document.getElementById('refreshMessage');
    const expiresAt = {{ $expiresAt }} * 1000;
    const tick = () => { const remaining = Math.max(0, expiresAt - Date.now()); const seconds = Math.floor(remaining / 1000); output.textContent = [Math.floor(seconds / 3600), Math.floor((seconds % 3600) / 60), seconds % 60].map(value => String(value).padStart(2, '0')).join(':'); if (!remaining) { status.textContent = 'QRIS kedaluwarsa'; clearInterval(timer); } };
    const timer = setInterval(tick, 1000); tick();
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

    const checkStatus = async () => {
        checkButton.disabled = true;
        refreshMessage.textContent = 'Memeriksa status pembayaran ke Midtrans...';
        try {
            const response = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'application/json' } });
            const data = response.ok ? await response.json() : {};
            if (data.status === 'PAID') {
                showPaymentSuccessModal();
                return;
            }
            refreshMessage.textContent = 'Pembayaran masih menunggu konfirmasi Midtrans.';
        } catch (error) {
            refreshMessage.textContent = 'Status belum dapat diperiksa. Coba lagi.';
        } finally {
            checkButton.disabled = false;
        }
    };
    checkButton.addEventListener('click', checkStatus);
    setInterval(checkStatus, 10000);
})();
</script>
@endsection
