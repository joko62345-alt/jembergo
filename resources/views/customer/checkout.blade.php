<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="customer-page"><x-public-navbar />
@php($participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values())
<main class="container checkout-page">
    <a href="{{ route('customer.booking.create', $booking->id_destinasi) }}" class="checkout-back"><i class="bi bi-arrow-left"></i> Kembali ke data peserta</a>
    <div class="checkout-heading"><div><span class="checkout-eyebrow">Langkah terakhir</span><h1>Konfirmasi dan pembayaran</h1><p>Periksa kembali pesananmu sebelum memilih metode pembayaran.</p></div><span class="checkout-code"><i class="bi bi-receipt"></i> {{ $booking->kode_booking }}</span></div>
    <div class="checkout-steps"><span class="is-done"><i class="bi bi-check2"></i> Data peserta</span><span class="is-current"><i class="bi bi-credit-card"></i> Pembayaran</span><span><i class="bi bi-ticket-perforated"></i> E-ticket</span></div>
    <div class="row g-4 align-items-start"><div class="col-lg-7"><div class="customer-card checkout-card"><div class="card-body p-4 p-lg-5">
        <h2 class="checkout-section-title">Ringkasan pesanan</h2>
        <div class="checkout-summary"><div class="checkout-destination"><span class="checkout-icon"><i class="bi bi-geo-alt"></i></span><div><small>Destinasi wisata</small><strong>{{ $booking->destinasi->nama_wisata }}</strong></div></div><div class="checkout-date"><small>Tanggal kunjungan</small><strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong></div></div>
        <div class="checkout-facts"><div><small>Ketua kelompok</small><strong>{{ $booking->ketua_nama }}</strong></div><div><small>Total peserta</small><strong>{{ $participants->count() }} orang</strong></div><div><small>Total pesanan</small><strong class="text-orange">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></div></div>
        <div class="checkout-participants"><strong><i class="bi bi-people me-2"></i>Rincian peserta</strong>@foreach($participants as $participant)<div><span>{{ $participant['nama'] }} @if($loop->first)<small>(Ketua)</small>@endif</span><span>{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}</span></div>@endforeach</div>
        <div class="alert alert-warning"><strong>Pilih metode pembayaran:</strong> Tunai menerbitkan e-ticket dengan status menunggu pembayaran dan dibayar di loket. Payment Gateway meneruskan pembayaran ke gateway; e-ticket diterbitkan setelah status pembayaran berhasil diterima.</div>
        <div class="alert alert-danger small"><i class="bi bi-exclamation-triangle me-1"></i><strong>Perhatian:</strong> Pesanan yang sudah dibuat tidak dapat dibatalkan dan dana yang sudah dibayar tidak dapat direfund.</div>
        <form method="POST" action="{{ route('customer.payment', $booking->id_pemesanan) }}">@csrf<label class="checkout-section-title d-block mb-3" for="metode_pembayaran">Pilih metode pembayaran</label><div class="payment-options"><label class="payment-option is-selected"><input id="cash-payment" type="radio" name="metode_pembayaran" value="CASH" checked><span class="payment-option-icon"><i class="bi bi-wallet2"></i></span><span><strong>Tunai di tempat</strong><small>Bayar di loket saat tiba di destinasi.</small></span><i class="bi bi-check-circle-fill payment-check"></i></label><label class="payment-option"><input id="gateway-payment" type="radio" name="metode_pembayaran" value="QRIS"><span class="payment-option-icon"><i class="bi bi-qr-code-scan"></i></span><span><strong>Payment Gateway</strong><small>Bayar dengan QRIS, transfer, atau e-wallet.</small></span><i class="bi bi-check-circle-fill payment-check"></i></label></div><button class="checkout-submit" type="submit"><span>Lanjutkan pembayaran</span><i class="bi bi-arrow-right"></i></button></form>
    </div></div></div><div class="col-lg-5"><aside class="checkout-side"><div class="checkout-side-icon"><i class="bi bi-shield-check"></i></div><h2>Pemesanan aman</h2><p>Data pesananmu tercatat dan e-ticket akan diterbitkan sesuai status pembayaran.</p><div class="checkout-side-line"><i class="bi bi-lock"></i><span>Pembayaran diproses dengan aman</span></div><div class="checkout-side-line"><i class="bi bi-headset"></i><span>Butuh bantuan? Hubungi pengelola destinasi</span></div></aside></div></div>
    </div>
</main>
<x-public-footer />
</body>
</html>
<style>
    .checkout-page { padding-top: 3rem; padding-bottom: 5rem; }
    .checkout-back { color: #567087; text-decoration: none; font-size: .9rem; }
    .checkout-back:hover { color: var(--jg-orange); }
    .checkout-heading { display: flex; align-items: end; justify-content: space-between; gap: 1rem; margin: 2rem 0 1.5rem; }
    .checkout-eyebrow { color: var(--jg-orange); font-size: .72rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
    .checkout-heading h1 { margin: .45rem 0 .35rem; font-size: clamp(2rem, 4vw, 3rem); font-weight: 500; }
    .checkout-heading p { margin: 0; color: #6b7f91; }
    .checkout-code { padding: .7rem .9rem; border: 1px solid #dbe6ee; border-radius: .75rem; background: #fff; color: #567087; font-size: .8rem; white-space: nowrap; }
    .checkout-steps { display: flex; gap: .65rem; margin-bottom: 1.5rem; }
    .checkout-steps span { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem .75rem; border-radius: 999px; background: #eef3f6; color: #7890a1; font-size: .78rem; }
    .checkout-steps .is-done { background: #e4f5ec; color: #238250; }.checkout-steps .is-current { background: #fff0dc; color: #d97706; }
    .checkout-card { border-color: #dbe6ee; box-shadow: 0 12px 30px rgba(31, 41, 55, .06); }
    .checkout-section-title { font-size: 1.15rem; font-weight: 600; color: #173b60; }
    .checkout-summary { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem; margin: 1.25rem 0; border-radius: 1rem; background: #f5f9fb; }
    .checkout-destination, .checkout-date { display: flex; align-items: center; gap: .7rem; }.checkout-destination strong, .checkout-date strong { display: block; color: #173b60; }.checkout-destination small, .checkout-date small, .checkout-facts small { display: block; color: #7890a1; font-size: .75rem; margin-bottom: .2rem; }
    .checkout-icon, .checkout-side-icon { display: inline-grid; place-items: center; width: 2.35rem; height: 2.35rem; border-radius: .7rem; background: #ffecd5; color: var(--jg-orange); }
    .checkout-date { display: block; text-align: right; }.checkout-facts { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; padding-bottom: 1.2rem; border-bottom: 1px solid #e4ebf0; }.checkout-facts strong { color: #173b60; font-size: .92rem; }
    .checkout-participants { padding: 1.15rem 0; border-bottom: 1px solid #e4ebf0; }.checkout-participants > strong { color: #173b60; }.checkout-participants > div { display: flex; justify-content: space-between; gap: 1rem; padding-top: .65rem; color: #405a70; font-size: .88rem; }.checkout-participants > div span:last-child { color: #7890a1; }.checkout-participants small { color: #7890a1; }
    .payment-options { display: grid; gap: .75rem; margin-bottom: 1.25rem; }.payment-option { display: flex; align-items: center; gap: .8rem; padding: 1rem; border: 1px solid #dbe6ee; border-radius: .9rem; cursor: pointer; transition: border-color 160ms ease, background 160ms ease, box-shadow 160ms ease; }.payment-option:hover, .payment-option.is-selected { border-color: var(--jg-orange); background: #fffaf3; box-shadow: 0 5px 14px rgba(245, 139, 5, .09); }.payment-option input { position: absolute; opacity: 0; }.payment-option-icon { display: inline-grid; place-items: center; width: 2.4rem; height: 2.4rem; border-radius: .7rem; background: #eef5f8; color: #35627f; font-size: 1.15rem; }.payment-option strong, .payment-option small { display: block; }.payment-option strong { color: #173b60; font-size: .92rem; }.payment-option small { color: #7890a1; font-size: .78rem; margin-top: .18rem; }.payment-check { margin-left: auto; color: var(--jg-orange); opacity: 0; }.payment-option.is-selected .payment-check { opacity: 1; }
    .checkout-submit { display: flex; align-items: center; justify-content: space-between; width: 100%; padding: .9rem 1.1rem; border: 0; border-radius: .85rem; background: var(--jg-orange); color: #fff; font-weight: 600; box-shadow: 0 8px 18px rgba(245, 139, 5, .2); }.checkout-submit:hover { background: #df7900; }
    .checkout-side { position: sticky; top: 6.5rem; padding: 1.5rem; border: 1px solid #dbe6ee; border-radius: 1rem; background: linear-gradient(145deg, #f7fbfc, #edf7fa); }.checkout-side h2 { margin: 1rem 0 .4rem; font-size: 1.25rem; font-weight: 600; }.checkout-side p { font-size: .88rem; }.checkout-side-line { display: flex; gap: .6rem; align-items: flex-start; padding-top: .8rem; margin-top: .8rem; border-top: 1px solid rgba(127, 187, 221, .3); color: #567087; font-size: .8rem; }.checkout-side-line i { color: var(--jg-orange); }
    @media (max-width: 767.98px) { .checkout-page { padding-top: 2rem; }.checkout-heading { display: block; }.checkout-code { display: inline-block; margin-top: 1rem; }.checkout-summary { display: block; }.checkout-date { margin-top: .8rem; text-align: left; }.checkout-facts { grid-template-columns: 1fr; }.checkout-side { position: static; }.checkout-steps { overflow-x: auto; padding-bottom: .25rem; }.checkout-steps span { flex: 0 0 auto; } }
</style>
<script>
    document.querySelectorAll('.payment-option input').forEach(input => input.addEventListener('change', () => document.querySelectorAll('.payment-option').forEach(option => option.classList.toggle('is-selected', option.querySelector('input').checked))));
</script>
