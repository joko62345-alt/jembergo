<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <a href="{{ route('customer.booking.create', $booking->id_destinasi) }}" class="text-dark text-decoration-none">Kembali ke data peserta</a>
    <div class="row justify-content-center mt-4"><div class="col-lg-8"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
        <span class="text-warning text-uppercase small fw-bold">Alur pemesanan</span>
        <h1 class="h2 fw-bold mt-2">Ringkasan pesanan</h1>
        <div class="d-flex flex-wrap gap-2 my-4"><span class="badge text-bg-success p-2">1. Data peserta</span><span class="badge text-bg-warning p-2">2. Pembayaran</span><span class="badge bg-light text-dark p-2">3. E-ticket</span></div>
        <div class="bg-white border rounded-3 p-3 mb-4"><div class="d-flex justify-content-between"><span>Kode booking</span><strong>{{ $booking->kode_booking }}</strong></div><div class="d-flex justify-content-between mt-2"><span>Destinasi</span><strong>{{ $booking->destinasi->nama_wisata }}</strong></div><div class="d-flex justify-content-between mt-2"><span>Total peserta</span><strong>{{ 1 + count($booking->anggota_names ?? []) }} orang</strong></div><div class="d-flex justify-content-between mt-2"><span>Total pesanan</span><strong>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></div><div class="small text-secondary mt-2">Tanggal kunjungan: {{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</div></div>
        <div class="alert alert-warning"><strong>Kebijakan pembayaran:</strong> pembayaran yang sudah berhasil tidak dapat dibatalkan dan tidak dapat di-refund.</div>
        <form method="POST" action="{{ route('customer.payment', $booking->id_pemesanan) }}">@csrf<label class="form-label" for="metode_pembayaran">Metode pembayaran</label><select id="metode_pembayaran" name="metode_pembayaran" class="form-select mb-4" required><option value="QRIS">QRIS</option><option value="TRANSFER_BANK">Transfer Bank</option><option value="E_WALLET">E-Wallet</option></select><button class="btn btn-warning rounded-pill w-100 fw-semibold" type="submit">Lanjutkan alur ke e-ticket</button></form>
    </div></div></div></div>
</main>
</body>
</html>
