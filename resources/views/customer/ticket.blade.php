@extends('layouts.app')

@section('title', 'E-ticket - JemberGo')

@section('content')
@include('components.public-navbar')

@php
    $participants = collect($booking->anggota_names ?? [])->prepend(['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket])->values();
@endphp

<main class="section-pad" style="padding-top: 8rem; background: var(--jg-bg); min-height: 100vh;">
    <div class="container">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div><span class="eyebrow text-orange">E-ticket aktif</span><h1 class="h2 fw-bold mt-2 mb-1">{{ $booking->destinasi->nama_wisata }}</h1><p class="text-secondary mb-0">Tunjukkan QR tiket masing-masing saat verifikasi.</p></div>
            <a href="{{ route('customer.tickets') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i>Tiket Saya</a>
        </div>

        <div class="customer-card mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-8">
                        <div class="d-flex flex-wrap gap-2 mb-3"><span class="badge text-bg-success">{{ $booking->status_pemesanan }}</span><span class="badge text-bg-warning">{{ $booking->tanggal_kunjungan->format('d/m/Y') }}</span></div>
                        <h2 class="h4 fw-bold">Kode booking: <span class="font-monospace">{{ $booking->kode_booking }}</span></h2>
                        <div class="row g-3 mt-2"><div class="col-md-6"><small class="text-secondary d-block">Ketua kelompok</small><strong>{{ $booking->ketua_nama }}</strong><span class="d-block small text-secondary">{{ $booking->ketua_email }} · {{ $booking->ketua_no_hp }}</span></div><div class="col-md-3"><small class="text-secondary d-block">Total peserta</small><strong>{{ $participants->count() }} orang</strong></div><div class="col-md-3"><small class="text-secondary d-block">Total dibayar</small><strong class="text-orange">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></div></div>
                    </div>
                    <div class="col-lg-4"><div class="bg-light rounded-3 p-3"><small class="text-secondary d-block mb-2">Data anggota</small>@forelse($participants->skip(1) as $member)<div class="d-flex justify-content-between gap-2 small py-1"><span>{{ $member['nama'] }}</span><span class="text-secondary">{{ $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $member['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket' }}</span></div>@empty<span class="small text-secondary">Tidak ada anggota tambahan.</span>@endforelse</div></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($booking->tiket as $index => $ticket)
                @php
                    $participant = $participants->get($index, ['nama' => $booking->ketua_nama, 'id_jenis_tiket' => (int) $booking->ketua_jenis_tiket]);
                    $ticketType = $booking->detailPemesanan->firstWhere('id_jenis_tiket', (int) $participant['id_jenis_tiket'])?->jenisTiket?->nama_jenis ?? 'Tiket wisata';
                @endphp
                <div class="col-md-6 col-xl-4">
                    <article class="customer-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="d-flex justify-content-between align-items-center mb-3"><span class="badge text-bg-light">Peserta {{ $index + 1 }}</span><span class="badge {{ $ticket->status_tiket === 'ACTIVE' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $ticket->status_tiket }}</span></div>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($ticket->kode_qr) }}" class="img-fluid rounded-3 mb-3" width="220" height="220" alt="QR tiket {{ $participant['nama'] }}">
                            <h2 class="h5 fw-bold mb-1">{{ $participant['nama'] }}</h2>
                            <p class="text-secondary small mb-3">{{ $ticketType }}</p>
                            <div class="bg-light rounded-3 p-2 text-start small"><div class="d-flex justify-content-between gap-2"><span>Kode booking</span><strong class="font-monospace">{{ $booking->kode_booking }}</strong></div><div class="d-flex justify-content-between gap-2 mt-1"><span>ID tiket</span><strong class="font-monospace">#{{ $ticket->id_tiket }}</strong></div></div>
                            @if($ticket->status_tiket === 'USED' && ! $ticket->review)<a href="{{ route('customer.review.create', $ticket->id_tiket) }}" class="btn btn-warning rounded-pill w-100 mt-3"><i class="bi bi-star me-1"></i>Beri review</a>@elseif($ticket->status_tiket === 'USED')<span class="badge text-bg-success mt-3">Sudah direview</span>@endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</main>
<x-public-footer />
@endsection