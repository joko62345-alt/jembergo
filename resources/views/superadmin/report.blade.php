@extends('layouts.superadmin')
@section('title', 'Laporan')
@section('page_label', 'Laporan transaksi')
@section('content')
    <div class="page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div><span class="page-kicker">Insight operasional</span><h1>Laporan transaksi</h1><p>Status mengikuti verifikasi tiket di destinasi.</p></div>
        <a href="{{ route('superadmin.report.export', request()->query()) }}" class="btn btn-outline-dark"><i class="bi bi-download me-1" aria-hidden="true"></i>Export CSV</a>
    </div>
    <div class="card mb-4"><div class="card-body p-4"><form class="row g-3">
        <div class="col-md-4"><label class="form-label" for="id_destinasi">Destinasi</label><select id="id_destinasi" name="id_destinasi" class="form-select"><option value="">Semua destinasi</option>@foreach($destinations as $destination)<option value="{{ $destination->id_destinasi }}" @selected((string) $destinationId === (string) $destination->id_destinasi)>{{ $destination->nama_wisata }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label" for="from">Dari</label><input id="from" type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
        <div class="col-md-3"><label class="form-label" for="to">Sampai</label><input id="to" type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
    </form></div></div>
    <div class="report-summary-card"><div><span class="report-summary-label">Total pendapatan</span><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong><small>Berdasarkan filter laporan aktif</small></div><i class="bi bi-wallet2" aria-hidden="true"></i></div>
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Kode booking</th><th>Destinasi</th><th>Tanggal pesan</th><th>Status tiket</th><th>Total</th></tr></thead><tbody>
        @forelse($orders as $order)
            @php($displayStatus = \App\Support\StatusLabel::ticketStatus($order->tiket->pluck('status_tiket'), $order->status_pemesanan))
            <tr><td class="fw-semibold">{{ $order->kode_booking }}</td><td>{{ $order->destinasi->nama_wisata }}</td><td>{{ $order->tanggal_pemesanan->format('d/m/Y H:i') }}</td><td><span class="status-text {{ $displayStatus === 'USED' ? 'status-used' : ($displayStatus === 'PARTIAL' ? 'status-partial' : 'status-default') }}">{{ \App\Support\StatusLabel::ticket($displayStatus) }}</span><small class="d-block text-secondary">Pembayaran: {{ \App\Support\StatusLabel::order($order->status_pemesanan) }}</small></td><td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada transaksi.</td></tr>
        @endforelse
    </tbody></table></div></div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
@push('scripts')
<script>
    document.querySelector('.card form')?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.currentTarget.requestSubmit();
        }
    });
</script>
@endpush
