<x-admin-layout title="Laporan Transaksi" active="report">
    <div class="admin-dashboard-heading">
        <div>
            <span class="admin-kicker">Insight operasional</span>
            <h1>Laporan transaksi</h1>
            <p>Ringkasan pemesanan untuk destinasi yang Anda kelola.</p>
        </div>
        <span class="admin-destination-chip"><i class="bi bi-geo-alt"
                aria-hidden="true"></i>{{ $destination->nama_wisata }}</span>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.report.preview', request()->only(['from', 'to'])) }}"
            target="_blank" rel="noopener" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1" aria-hidden="true"></i>Preview & Cetak
        </a>
        <a href="{{ route('admin.report.pdf', request()->only(['from', 'to'])) }}"
            class="btn btn-warning">
            <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Simpan PDF
        </a>
        <a href="{{ route('admin.report.export', request()->only(['from', 'to'])) }}"
            class="btn btn-outline-dark">
            <i class="bi bi-download me-1" aria-hidden="true"></i>Export CSV
        </a>
    </div>

    <section class="admin-panel mb-4">
        <div class="admin-panel-heading">
            <div>
                <span class="admin-kicker">Periode laporan</span>
                <h2>Filter transaksi</h2>
                <p>Pilih rentang tanggal pemesanan untuk mempersempit laporan.</p>
            </div>
        </div>
        <form id="report-filter-form" method="GET" action="{{ route('admin.report') }}"
            class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="from">Dari tanggal</label>
                <input id="from" type="date" name="from"
                    value="{{ $dateFilters['from']?->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="to">Sampai tanggal</label>
                <input id="to" type="date" name="to"
                    value="{{ $dateFilters['to']?->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <button class="btn btn-warning w-100" type="submit">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i>Tampilkan laporan
                </button>
            </div>
        </form>
    </section>

    <section class="admin-stat-grid" aria-label="Ringkasan laporan">
        <article class="admin-stat-card">
            <div class="admin-stat-icon"><i class="bi bi-receipt" aria-hidden="true"></i></div>
            <span>Total transaksi</span>
            <strong>{{ number_format($totalTransaksi) }}</strong>
            <small>Sesuai periode yang dipilih</small>
        </article>
        <article class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-green"><i class="bi bi-patch-check"
                    aria-hidden="true"></i></div>
            <span>Transaksi lunas</span>
            <strong>{{ number_format($totalTransaksiLunas) }}</strong>
            <small>Pembayaran berhasil</small>
        </article>
        <article class="admin-stat-card">
            <div class="admin-stat-icon admin-stat-icon-blue"><i class="bi bi-cash-coin"
                    aria-hidden="true"></i></div>
            <span>Pendapatan</span>
            <strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong>
            <small>Hanya dari booking lunas</small>
        </article>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div>
                <span class="admin-kicker">Rincian transaksi</span>
                <h2>Riwayat pemesanan</h2>
                <p>Semua transaksi pada {{ $destination->nama_wisata }} di periode terpilih.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode booking</th>
                        <th>Tanggal pesan</th>
                        <th>Status tiket</th>
                        <th>Pembayaran</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php($displayStatus = \App\Support\StatusLabel::ticketStatus($order->tiket->pluck('status_tiket'), $order->status_pemesanan))
                        <tr>
                            <td class="fw-semibold">{{ $order->kode_booking }}</td>
                            <td>{{ $order->tanggal_pemesanan->format('d/m/Y H:i') }}</td>
                            <td>
                                <span
                                    class="status-text {{ $displayStatus === 'USED' ? 'status-used' : ($displayStatus === 'PARTIAL' ? 'status-partial' : 'status-default') }}">
                                    {{ \App\Support\StatusLabel::ticket($displayStatus) }}
                                </span>
                            </td>
                            <td>{{ \App\Support\StatusLabel::order($order->status_pemesanan) }}</td>
                            <td class="text-nowrap">Rp
                                {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                Belum ada transaksi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin-layout>
