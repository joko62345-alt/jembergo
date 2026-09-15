<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Booking {{ $booking->kode_booking }}</title>
    <style>
        @page { margin: 28px 34px 34px; }
        body { margin: 0; color: #111; font-family: "Times New Roman", Times, serif; font-size: 10px; }
        .watermark { position: fixed; top: 39%; left: 17%; z-index: -1; color: #d7d7d7; font-size: 58px; font-weight: bold; letter-spacing: 8px; opacity: .28; transform: rotate(-35deg); }
        .header { display: table; width: 100%; padding-bottom: 10px; border-bottom: 1.5px solid #111; }
        .header-brand { display: table-cell; width: 64%; vertical-align: middle; }
        .brand-logo { width: 145px; height: 48px; object-fit: contain; object-position: left center; filter: grayscale(100%); }
        .brand-subtitle { margin-top: 2px; color: #555; font-size: 8px; }
        .header-meta { display: table-cell; width: 36%; color: #555; font-size: 8px; text-align: right; vertical-align: middle; }
        .header-meta strong { color: #111; font-size: 9px; }
        .document-title { margin: 18px 0 16px; color: #111; font-size: 15px; font-weight: bold; text-align: center; }
        .document-title span { display: block; margin-top: 3px; color: #555; font-size: 9px; font-weight: normal; }
        .section-title { margin: 13px 0 6px; color: #111; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .details { width: 100%; border-collapse: collapse; }
        .details td { width: 50%; padding: 3px 5px; vertical-align: top; }
        .details td:first-child { width: 22%; color: #555; }
        .details td:nth-child(2) { width: 28%; font-weight: bold; }
        .details td:nth-child(3) { width: 22%; color: #555; }
        .details td:nth-child(4) { width: 28%; font-weight: bold; }
        .details .label { color: #555; font-weight: normal; }
        .participant-table { width: 100%; margin-top: 6px; border-collapse: collapse; font-size: 8px; }
        .participant-table th { padding: 6px 5px; border: .7px solid #555; background: #e9e9e9; color: #111; font-weight: bold; text-align: left; }
        .participant-table td { padding: 5px; border: .7px solid #98a2b3; }
        .participant-table th:first-child, .participant-table td:first-child { width: 7%; text-align: center; }
        .participant-table th:last-child, .participant-table td:last-child { width: 18%; }
        .status { color: #111; font-weight: bold; }
        .qr-section { margin-top: 22px; text-align: center; }
        .qr-section img { width: 150px; height: 150px; }
        .qr-caption { margin-top: 5px; color: #111; font-size: 9px; font-weight: bold; }
        .qr-code { margin-top: 3px; color: #555; font-family: monospace; font-size: 8px; }
        .notes { margin-top: 18px; padding-top: 8px; border-top: .7px solid #777; color: #555; font-size: 8px; line-height: 1.55; }
        .notes strong { color: #111; }
    </style>
</head>
<body>
    @php($participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values())
    @php($groupTicket = $booking->tiket->first())
    @php($paymentStatus = \App\Support\StatusLabel::payment($booking->pembayaran?->status_pembayaran))

    <div class="watermark">JEMBERGO</div>
    <header class="header">
        <div class="header-brand"><img class="brand-logo" src="data:image/png;base64,{{ base64_encode(file_get_contents(resource_path('images/logo.png'))) }}" alt="JemberGo"><div class="brand-subtitle">Sistem Informasi dan Layanan Pariwisata Kabupaten Jember</div></div>
        <div class="header-meta"><strong>BUKTI BOOKING ONLINE</strong><br>Dokumen e-ticket kelompok<br>{{ now()->format('d/m/Y H:i') }}</div>
    </header>

    <div class="document-title">BUKTI PEMESANAN TIKET WISATA<span>Harap tunjukkan QR grup kepada petugas destinasi</span></div>

    <div class="section-title">Informasi pemesanan</div>
    <table class="details">
        <tr><td>Kode booking</td><td>{{ $booking->kode_booking }}</td><td>Destinasi</td><td>{{ $booking->destinasi->nama_wisata }}</td></tr>
        <tr><td>Tanggal pesan</td><td>{{ $booking->tanggal_pemesanan->format('d/m/Y H:i') }}</td><td>Tanggal kunjungan</td><td>{{ $booking->tanggal_kunjungan->format('d/m/Y') }}</td></tr>
        <tr><td>Status pembayaran</td><td class="status">{{ $paymentStatus }}</td><td>Total peserta</td><td>{{ $participants->count() }} orang</td></tr>
        <tr><td>Total pembayaran</td><td>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td><td>Metode</td><td>QRIS</td></tr>
    </table>

    <div class="section-title">Data ketua kelompok</div>
    <table class="details">
        <tr><td>Nama</td><td>{{ $booking->ketua_nama }}</td><td>Email</td><td>{{ $booking->ketua_email }}</td></tr>
        <tr><td>Nomor HP</td><td>{{ $booking->ketua_no_hp }}</td><td>Status</td><td class="status">{{ $groupTicket ? \App\Support\StatusLabel::ticket($groupTicket->status_tiket) : 'Belum diterbitkan' }}</td></tr>
    </table>

    <div class="section-title">Daftar peserta</div>
    <table class="participant-table">
        <thead><tr><th>No.</th><th>Nama peserta</th><th>Jenis tiket</th><th>Status peserta</th></tr></thead>
        <tbody>@foreach($participants as $participant)<tr><td>{{ $loop->iteration }}</td><td>{{ $participant['nama'] }}{{ $loop->first ? ' (Ketua)' : '' }}</td><td>{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket wisata' }}</td><td class="status">{{ $groupTicket ? \App\Support\StatusLabel::ticket($groupTicket->status_tiket) : 'Belum diterbitkan' }}</td></tr>@endforeach</tbody>
    </table>

    @if($groupTicket)
        <div class="qr-section"><img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($groupTicket->kode_qr) }}" alt="QR grup {{ $booking->kode_booking }}"><div class="qr-caption">QR BOOKING GRUP</div><div class="qr-code">Scan satu QR ini untuk seluruh {{ $participants->count() }} peserta</div></div>
    @endif

    <div class="notes"><strong>Catatan:</strong><br>• QR code berlaku untuk seluruh peserta dalam satu booking.<br>• Tunjukkan dokumen ini kepada petugas saat tiba di destinasi.<br>• E-ticket berlaku sesuai tanggal kunjungan dan status pembayaran.<br>• Data pada dokumen ini diterbitkan oleh JemberGo.</div>
</body>
</html>
