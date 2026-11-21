@php
    $participants = collect($booking->nama_anggota ?? [])
        ->prepend([
            'nama' => $booking->ketua_nama,
            'id_jenis_tiket' => (int) $booking->jenis_tiket_ketua,
        ])
        ->values();
    $verifiedTickets = $booking->tiket->where('status_tiket', 'USED')->values();
    $isCounterSale = $booking->kanal_penjualan === 'LOKET';
    $isVerified = $verifiedTickets->isNotEmpty();
    $receiptTime = $isCounterSale && !$isVerified
        ? ($booking->pembayaran?->paid_at ?? $booking->pembayaran?->waktu_pembayaran)
        : $verifiedTickets->first()?->waktu_verifikasi;
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isCounterSale && !$isVerified ? 'Struk Pembelian' : 'Struk Verifikasi' }} {{ $booking->kode_booking }} | JemberGo</title>
    <style>
        :root {
            color: #17212b;
            font-family: Arial, Helvetica, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f5;
        }

        .receipt-actions {
            display: flex;
            justify-content: center;
            gap: .75rem;
            padding: 1.5rem 1rem;
        }

        .receipt-actions a,
        .receipt-actions button {
            border: 0;
            border-radius: .45rem;
            padding: .7rem 1rem;
            background: #173b60;
            color: #fff;
            cursor: pointer;
            font: inherit;
            text-decoration: none;
        }

        .receipt-actions .secondary {
            background: #fff;
            color: #173b60;
            box-shadow: inset 0 0 0 1px #c8d2dc;
        }

        .receipt {
            width: 80mm;
            max-width: calc(100% - 2rem);
            margin: 0 auto 2rem;
            padding: 5mm 4mm;
            background: #fff;
            box-shadow: 0 8px 30px rgba(23, 59, 96, .12);
            font-size: 11px;
            line-height: 1.35;
        }

        .receipt-header {
            text-align: center;
        }

        .receipt-logo {
            display: block;
            width: 36mm;
            max-height: 17mm;
            margin: 0 auto 2mm;
            object-fit: contain;
        }

        .receipt-header p {
            margin: 2px 0;
        }

        .muted {
            color: #637181;
        }

        .rule {
            margin: 9px 0;
            border-top: 1px dashed #68737d;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .receipt-row strong:last-child {
            text-align: right;
        }

        .receipt-section-title {
            margin: 8px 0 4px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .ticket-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 2px 6px;
            padding: 5px 0;
            border-bottom: 1px dotted #b9c1c8;
            align-items: center;
        }

        .ticket-item:last-child {
            border-bottom: 0;
        }

        .ticket-item .participant-name {
            display: block;
            font-weight: 700;
            word-break: break-word;
        }

        .ticket-item .participant-meta {
            display: block;
            color: #637181;
        }

        .ticket-item .qty {
            min-width: 13px;
            font-weight: 700;
            text-align: right;
        }

        .ticket-item .line-total {
            min-width: 56px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .total {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
        }

        .receipt-footer {
            margin-top: 14px;
            text-align: center;
        }

        .receipt-footer p {
            margin: 3px 0;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                background: #fff;
            }

            .receipt-actions {
                display: none;
            }

            .receipt {
                width: 80mm;
                max-width: none;
                margin: 0;
                padding: 4mm;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <nav class="receipt-actions" aria-label="Aksi struk">
        <a href="{{ $isCounterSale && !$isVerified ? route('admin.counter-sales.create') : route('admin.verification') }}"
            class="secondary">{{ $isCounterSale && !$isVerified ? 'Penjualan berikutnya' : 'Kembali' }}</a>
        <button type="button" onclick="window.print()">Cetak struk</button>
    </nav>
    <main class="receipt">
        <header class="receipt-header">
            <img class="receipt-logo" src="{{ route('assets.logo') }}" alt="JemberGo">
            <p><strong>{{ $isCounterSale && !$isVerified ? 'BUKTI PEMBELIAN TIKET' : 'BUKTI VERIFIKASI TIKET' }}</strong></p>
            <p>{{ $booking->destinasi->nama_wisata }}</p>
            <p class="muted">{{ $booking->destinasi->alamat }}</p>
        </header>
        <div class="rule"></div>
        <div class="receipt-row"><span>Kode
                booking</span><strong>{{ $booking->kode_booking }}</strong></div>
        <div class="receipt-row"><span>Tanggal
                kunjungan</span><strong>{{ $booking->tanggal_kunjungan->format('d/m/Y') }}</strong>
        </div>
        <div class="receipt-row"><span>{{ $isCounterSale && !$isVerified ? 'Waktu transaksi' : 'Waktu verifikasi' }}</span><strong>{{ $receiptTime?->setTimezone(config('app.timezone'))->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</strong>
        </div>
        <div class="rule"></div>
        <div class="receipt-section-title">{{ $isCounterSale ? 'Penjualan di loket' : 'Pemesan' }}</div>
        @if ($isCounterSale)
            <div class="receipt-row"><span>Metode pembayaran</span><strong>{{ $booking->pembayaran?->metode_pembayaran ?? '-' }}</strong></div>
            <div class="receipt-row"><span>Status pembayaran</span><strong>{{ \App\Support\StatusLabel::payment($booking->pembayaran?->status_pembayaran) }}</strong></div>
            @if ($booking->pembayaran?->uang_diterima !== null)
                <div class="receipt-row"><span>Uang diterima</span><strong>Rp {{ number_format($booking->pembayaran->uang_diterima, 0, ',', '.') }}</strong></div>
                <div class="receipt-row"><span>Kembalian</span><strong>Rp {{ number_format($booking->pembayaran->uang_kembalian, 0, ',', '.') }}</strong></div>
            @endif
        @else
            <div>{{ $booking->ketua_nama }}</div>
            <div class="muted">{{ $booking->ketua_email }}</div>
            <div class="muted">{{ $booking->ketua_no_hp }}</div>
        @endif
        <div class="rule"></div>
        <div class="receipt-section-title">Rincian tiket</div>
        @if ($booking->kanal_penjualan === 'LOKET')
            @foreach ($booking->detailPemesanan as $detail)
                @php($ticketPrice = (float) ($detail->jenisTiket?->harga ?? 0))
                <div class="ticket-item">
                    <span>
                        <span class="participant-name">{{ $detail->jenisTiket?->nama_jenis ?? 'Tiket wisata' }}</span>
                        <span class="participant-meta">Rp {{ number_format($ticketPrice, 0, ',', '.') }} / tiket</span>
                    </span>
                    <strong class="qty">{{ $detail->jumlah }}</strong>
                    <strong class="line-total">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        @else
            @foreach ($participants as $index => $participant)
                @php($participantName = data_get($participant, 'nama', 'Peserta ' . ($index + 1)))
                @php($participantTypeId = (int) data_get($participant, 'id_jenis_tiket', 0))
                @php($ticketDetail = $booking->detailPemesanan->firstWhere('id_jenis_tiket', $participantTypeId))
                @php($ticketType = $ticketDetail?->jenisTiket?->nama_jenis ?? 'Tiket wisata')
                @php($ticketPrice = (float) ($ticketDetail?->jenisTiket?->harga ?? 0))
                <div class="ticket-item">
                    <span>
                        <span class="participant-name">{{ $participantName }}</span>
                        <span class="participant-meta">{{ $ticketType }} · Rp {{ number_format($ticketPrice, 0, ',', '.') }}</span>
                    </span>
                    <strong class="qty">1</strong>
                    <strong class="line-total">Rp {{ number_format($ticketPrice, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        @endif
        <div class="receipt-row total"><span>Total bayar</span><strong>Rp
                {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></div>
        <div class="rule"></div>
        <footer class="receipt-footer">
            @if ($isCounterSale && !$isVerified)
                <p><strong>PEMBAYARAN LUNAS</strong></p>
                <p class="muted">Simpan struk ini sebagai bukti pembelian tiket.</p>
                <p class="muted">Tiket berlaku untuk tanggal kunjungan di atas.</p>
            @else
                <p><strong>VERIFIKASI BERHASIL</strong></p>
                <p class="muted">Terima kasih telah berkunjung.</p>
                <p class="muted">Simpan struk ini sebagai bukti masuk.</p>
            @endif
        </footer>
    </main>
    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>

</html>
