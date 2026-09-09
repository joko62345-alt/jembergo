<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="customer-page"><x-public-navbar />
@php($participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values())
<main class="container">
    <a href="{{ route('customer.booking.create', $booking->id_destinasi) }}" class="text-dark text-decoration-none">Kembali ke data peserta</a>
    <div class="row justify-content-center mt-4"><div class="col-lg-8"><div class="customer-card"><div class="card-body p-4 p-lg-5">
        <span class="text-warning text-uppercase small fw-bold">Alur pemesanan</span>
        <h1 class="h2 fw-bold mt-2">Ringkasan pesanan</h1>
        <div class="d-flex flex-wrap gap-2 my-4"><span class="badge text-bg-success p-2">1. Data peserta</span><span class="badge text-bg-warning p-2">2. Pembayaran</span><span class="badge bg-light text-dark p-2">3. E-ticket</span></div>
        <div class="bg-white border rounded-3 p-3 mb-4"><div class="d-flex justify-content-between"><span>Kode booking</span><strong class="font-monospace">{{ $booking->kode_booking }}</strong></div><div class="d-flex justify-content-between mt-2"><span>Destinasi</span><strong>{{ $booking->destinasi->nama_wisata }}</strong></div><div class="d-flex justify-content-between mt-2"><span>Ketua kelompok</span><strong>1 orang</strong></div><div class="d-flex justify-content-between mt-2"><span>Anggota</span><strong>{{ max(0, $participants->count() - 1) }} orang</strong></div><div class="d-flex justify-content-between mt-2"><span>Total peserta</span><strong>{{ $participants->count() }} orang</strong></div><div class="d-flex justify-content-between mt-2"><span>Total pesanan</span><strong class="text-orange">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></div><div class="small text-secondary mt-2">Tanggal kunjungan: {{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</div><hr><strong class="small d-block mb-2">Rincian peserta</strong>@foreach($participants as $participant)<div class="d-flex justify-content-between small py-1"><span>{{ $participant['nama'] }} @if($loop->first)<span class="text-secondary">(Ketua)</span>@endif</span><span class="text-secondary">{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}</span></div>@endforeach</div>
        <div class="alert alert-warning"><strong>Kebijakan pembayaran:</strong> pembayaran yang sudah berhasil tidak dapat dibatalkan dan tidak dapat di-refund.</div>
        <form method="POST" action="{{ route('customer.payment', $booking->id_pemesanan) }}">@csrf<label class="form-label" for="metode_pembayaran">Metode pembayaran</label><select id="metode_pembayaran" name="metode_pembayaran" class="form-select mb-4" required><option value="QRIS">QRIS</option><option value="TRANSFER_BANK">Transfer Bank</option><option value="E_WALLET">E-Wallet</option></select><button class="btn btn-warning rounded-pill w-100 fw-semibold" type="submit">Lanjutkan alur ke e-ticket</button></form>
    </div></div></div></div>
</main>
<x-public-footer />
</body>
</html>
