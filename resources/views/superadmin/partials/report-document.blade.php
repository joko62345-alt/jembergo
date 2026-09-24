<header class="document-header">
    <h1>JEMBERGO</h1>
    <p>LAPORAN TRANSAKSI WISATA</p>
</header>

<table class="document-meta">
    <tr>
        <td>Destinasi: {{ $destination?->nama_wisata ?? 'Semua destinasi' }}</td>
        <td>Dicetak: {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <td>Periode: {{ $from?->format('d/m/Y') ?? 'Semua tanggal' }} s.d.
            {{ $to?->format('d/m/Y') ?? 'sekarang' }}</td>
        <td>Dokumen resmi JemberGo</td>
    </tr>
</table>

<table class="report-table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Kode Booking</th>
            <th>Destinasi</th>
            <th>Tanggal Pesan</th>
            <th>Status Tiket</th>
            <th>Status Pembayaran</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
            @php($ticketStatus = \App\Support\StatusLabel::ticketStatus($order->tiket->pluck('status_tiket'), $order->status_pemesanan))
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $order->kode_booking }}</td>
                <td>{{ $order->destinasi->nama_wisata }}</td>
                <td>{{ $order->tanggal_pemesanan->format('d/m/Y H:i') }}</td>
                <td>{{ \App\Support\StatusLabel::ticket($ticketStatus) }}</td>
                <td>{{ \App\Support\StatusLabel::order($order->status_pemesanan) }}</td>
                <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td class="empty" colspan="7">Belum ada transaksi pada filter laporan ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="summary">
    <tr>
        <td><span class="summary-label">TOTAL
                TRANSAKSI</span><br><strong>{{ $orders->count() }}</strong></td>
        <td><span class="summary-label">TOTAL PENDAPATAN</span><br><strong>Rp
                {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></td>
    </tr>
</table>

<div class="document-footer">Laporan ini dibuat oleh sistem JemberGo dan dicetak untuk keperluan
    administrasi.</div>
