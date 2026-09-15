@extends('layouts.app')

@section('title', 'E-ticket - JemberGo')

@section('content')
@include('components.public-navbar')

@php
    $participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values();
@endphp

<main class="section-pad ticket-page" style="padding-top: 8rem; background: var(--jg-bg); min-height: 100vh;">
    <div class="container">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($booking->pembayaran?->status_pembayaran === 'PAID' && $booking->tiket->contains('status_tiket', 'ACTIVE'))
            <div class="ticket-manage-cta"><div><strong>Ingin mengubah jadwal atau menambah tiket?</strong><small>Pilih perubahan, review detailnya, lalu lanjutkan ke pembayaran.</small></div><a href="{{ route('customer.manage', $booking->id_pemesanan) }}" class="btn btn-warning"><i class="bi bi-sliders me-1"></i>Kelola perjalanan</a></div>
        @endif

        <div class="ticket-heading d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div><span class="eyebrow text-orange">E-ticket </span><h1 class="h2 fw-bold mt-2 mb-1">{{ $booking->destinasi->nama_wisata }}</h1><p class="text-secondary mb-0">Tunjukkan kode booking kepada petugas saat tiba di destinasi.</p></div>
            <a href="{{ route('customer.tickets') }}" class="ticket-back"><i class="bi bi-arrow-left"></i> Tiket Saya</a>
        </div>

        <div class="customer-card ticket-overview mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-8">
                        @php($paymentStatus = match ($booking->pembayaran?->status_pembayaran) { 'PAID' => 'Lunas', 'PENDING' => 'Menunggu pembayaran', 'FAILED' => 'Gagal', 'EXPIRED' => 'Kedaluwarsa', default => 'Belum dibayar' })
                        <div class="ticket-statuses mb-3"><span class="badge ticket-neutral-badge"><i class="bi bi-wallet2 me-1"></i>{{ $paymentStatus }}</span><span class="badge ticket-neutral-badge"><i class="bi bi-calendar-event me-1"></i>{{ $booking->tanggal_kunjungan->format('d/m/Y') }}</span><a href="{{ route('customer.ticket.pdf', $booking->id_pemesanan) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a></div>
                        <div class="ticket-booking-code"><small>Kode booking</small><strong>{{ $booking->kode_booking }}</strong></div>
                        <div class="row g-3 mt-2"><div class="col-md-6"><small class="text-secondary d-block">Ketua kelompok</small><strong>{{ $booking->ketua_nama }}</strong><span class="d-block small text-secondary">{{ $booking->ketua_email }} · {{ $booking->ketua_no_hp }}</span></div><div class="col-md-3"><small class="text-secondary d-block">Total peserta</small><strong>{{ $participants->count() }} orang</strong></div><div class="col-md-3"><small class="text-secondary d-block">Total dibayar</small><strong class="text-orange">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></div></div>
                    </div>
                </div>
            </div>
        </div>

        @php($groupTicket = $booking->tiket->first())
        @if($groupTicket)
            <article class="customer-card ticket-card ticket-group-card">
                <div class="row g-0">
                    <div class="col-12 ticket-qr-panel">
                        <div class="card-body p-4 text-center">
                            <div class="ticket-card-top"><span class="ticket-qr-label"><span class="badge {{ $groupTicket->status_tiket === 'ACTIVE' ? 'text-bg-success' : ($groupTicket->status_tiket === 'EXPIRED' ? 'text-bg-danger' : 'text-bg-secondary') }}">{{ \App\Support\StatusLabel::ticket($groupTicket->status_tiket) }}</span></span></div>
                            <div class="ticket-qr-frame"><img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($groupTicket->kode_qr) }}" class="img-fluid" width="220" height="220" alt="QR grup {{ $booking->kode_booking }}"></div>
                            <h2 class="h5 fw-bold mb-1">Kode QR booking</h2>
                            <p class="text-secondary small mb-3">Satu kode QR untuk seluruh peserta dalam booking ini.</p>
                            <div class="ticket-meta text-start small"><div><span>Kode booking</span><strong>{{ $booking->kode_booking }}</strong></div><div><span>Jumlah peserta</span><strong>{{ $participants->count() }} orang</strong></div></div>
                        </div>
                    </div>
                </div>
            </article>
        @endif
    </div>
</main>
<x-public-footer />
<style>
    .ticket-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; color: #173b60; font-size: .9rem; line-height: 1.5; }
    .ticket-page { padding-bottom: 5rem; }
    .ticket-notice { display: flex; align-items: center; gap: .65rem; padding: .8rem 1rem; margin-bottom: 1.25rem; border: 1px solid #f3b6bd; border-radius: .7rem; background: #fff3f4; color: #9e3441; font-size: .82rem; }
    .ticket-notice i { font-size: 1rem; }
    .ticket-manage-cta { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.2rem; margin-bottom:1.25rem; border:1px solid #dce6ed; border-radius:.8rem; background:#fff; }.ticket-manage-cta > div { display:grid; gap:.2rem; }.ticket-manage-cta strong { color:#173b60; font-size:.95rem; }.ticket-manage-cta small { color:#708496; font-size:.8rem; }
    .ticket-heading h1 { font-size: clamp(1.7rem, 2.8vw, 2.25rem); line-height: 1.2; font-weight: 650; }
    .ticket-back { display: inline-flex; align-items: center; gap: .45rem; padding: .65rem .9rem; border: 1px solid #dbe6ee; border-radius: .75rem; background: #fff; color: #567087; text-decoration: none; font-size: .85rem; }
    .ticket-back:hover { color: var(--jg-orange); border-color: var(--jg-orange); }
    .ticket-overview { border-color: #dbe6ee; box-shadow: 0 12px 30px rgba(31, 41, 55, .06); }
    .ticket-statuses { display: flex; flex-wrap: wrap; gap: .5rem; }.ticket-statuses .badge { padding: .5rem .7rem; font-weight: 500; }.ticket-neutral-badge { border: 1px solid #dbe6ee; background: #fff; color: #567087; }
    .ticket-booking-code { padding-bottom: 1rem; border-bottom: 1px solid #e5edf2; }.ticket-booking-code small { display: block; color: #7890a1; font-size: .72rem; margin-bottom: .25rem; }.ticket-booking-code strong { color: #173b60; font-family: monospace; font-size: clamp(1.05rem, 1.8vw, 1.35rem); letter-spacing: .02em; }
    .ticket-group-card { overflow: hidden; }
    .ticket-members { height: 100%; padding: 1rem; border-radius: .9rem; background: #f5f9fb; }.ticket-members > small { display: block; margin-bottom: .5rem; color: #7890a1; }.ticket-members > div { display: flex; justify-content: space-between; gap: .75rem; padding: .55rem 0; border-top: 1px solid #e2ebf0; font-size: .8rem; }.ticket-members > div span:last-child { color: #7890a1; text-align: right; }
    .ticket-card { border-color: #dbe6ee; box-shadow: 0 10px 25px rgba(31, 41, 55, .05); }.ticket-card .card-body { padding: 1.25rem; }.ticket-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }.ticket-qr-label { color: #567087; font-size: .8rem; }.ticket-qr-frame { display: grid; place-items: center; width: min(100%, 250px); aspect-ratio: 1; margin: 0 auto 1rem; padding: .8rem; border: 1px solid #e1eaf0; border-radius: 1rem; background: #fff; }.ticket-qr-frame img { width: 100%; height: 100%; }.ticket-meta { padding: .75rem; border-radius: .7rem; background: #f5f9fb; }.ticket-meta div { display: flex; justify-content: space-between; gap: 1rem; padding: .25rem 0; }.ticket-meta span { color: #7890a1; }.ticket-meta strong { color: #173b60; font-family: monospace; font-size: .78rem; }
    @media (max-width: 575.98px) { .ticket-page { padding-top: 6rem !important; }.ticket-notice { align-items: flex-start; }.ticket-summary { padding: 1rem !important; }.ticket-manage-cta { align-items:stretch; flex-direction:column; }.ticket-manage-cta .btn { width:100%; } }
</style>
@endsection