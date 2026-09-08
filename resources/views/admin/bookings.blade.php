<x-admin-layout title="Pemesanan Destinasi" active="bookings">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><span class="text-warning text-uppercase small fw-bold">Operasional destinasi</span><h1 class="h2 fw-bold mt-2 mb-1">Pemesanan destinasi</h1><p class="text-secondary mb-0">Semua pemesan dan status tiket pada destinasi Anda.</p></div>
        <a href="{{ route('admin.verification') }}" class="btn btn-warning rounded-pill">Verifikasi tiket</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><small class="text-secondary">Tiket terverifikasi</small><strong class="display-6 d-block mt-2">{{ number_format($report['tickets']) }}</strong></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><small class="text-secondary">Booking terverifikasi</small><strong class="display-6 d-block mt-2">{{ number_format($report['bookings']) }}</strong></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><small class="text-secondary">Pendapatan terverifikasi</small><strong class="h2 d-block mt-3">Rp {{ number_format($report['revenue'], 0, ',', '.') }}</strong></div></div></div>
    </div>
    <div class="card border-0 shadow-sm rounded-4"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Booking & pemesan</th><th>Peserta</th><th>Kunjungan</th><th>Pembayaran</th><th>Status tiket</th><th>Total</th></tr></thead><tbody>
        @forelse($bookings as $booking)
            <tr><td><strong>{{ $booking->kode_booking }}</strong><small class="d-block">{{ $booking->customer->nama ?? '-' }}</small><small class="d-block text-secondary">{{ $booking->customer->email ?? '-' }}</small></td><td><strong>{{ $booking->ketua_nama }}</strong> <span class="badge text-bg-light">Ketua</span>@foreach($booking->anggota_names ?? [] as $member)<small class="d-block">{{ data_get($member, 'nama', $member) }} <span class="text-secondary">(Anggota)</span></small>@endforeach</td><td>{{ $booking->tanggal_kunjungan->format('d/m/Y') }}<small class="d-block text-secondary">{{ $booking->status_pemesanan }}</small></td><td><span class="badge {{ $booking->pembayaran?->status_pembayaran === 'PAID' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $booking->pembayaran->status_pembayaran ?? 'BELUM ADA' }}</span><small class="d-block text-secondary mt-1">{{ $booking->pembayaran->metode_pembayaran ?? '-' }}</small></td><td>@forelse($booking->tiket as $ticket)<div class="d-flex align-items-center gap-2 mb-1"><span class="badge {{ $ticket->status_tiket === 'USED' ? 'text-bg-success' : ($ticket->status_tiket === 'ACTIVE' ? 'text-bg-primary' : 'text-bg-secondary') }}">{{ $ticket->status_tiket }}</span><small>{{ $ticket->waktu_verifikasi?->setTimezone(config('app.timezone'))->format('d/m H:i') ?? '-' }}</small></div>@empty<span class="text-secondary small">Belum dibuat</span>@endforelse</td><td class="text-nowrap">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-secondary py-5">Belum ada pemesanan di destinasi ini.</td></tr>
        @endforelse
    </tbody></table></div></div>
    <div class="mt-4">{{ $bookings->links() }}</div>
</x-admin-layout>