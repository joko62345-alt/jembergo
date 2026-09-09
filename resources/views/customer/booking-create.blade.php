@extends('layouts.app')

@section('title', 'Pemesanan Tiket - JemberGo')

@section('content')
@include('components.public-navbar')

<main class="section-pad" style="padding-top: 8rem; background: var(--jg-bg); min-height: 100vh;">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('destinations.index') }}" class="text-decoration-none">Destinasi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pemesanan</li>
            </ol>
        </nav>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="customer-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="page-intro mb-4">
                            <span class="eyebrow text-orange">Pemesanan kelompok</span>
                            <h1 class="h2 fw-bold mt-3">Data ketua dan anggota</h1>
                            <p class="text-secondary mb-0">Maksimal 10 orang termasuk ketua. Setiap peserta dapat memilih jenis tiketnya sendiri.</p>
                        </div>

                        <form action="{{ route('customer.booking.store', $destination->id_destinasi) }}" method="POST" id="bookingForm">
                            @csrf

                            <section class="booking-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3"><span class="step-number">1</span><h2 class="h5 mb-0">Ketua kelompok</h2></div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label" for="ketua_nama">Nama lengkap</label><input id="ketua_nama" name="ketua_nama" value="{{ old('ketua_nama') }}" class="form-control" required></div>
                                    <div class="col-md-6"><label class="form-label" for="ketua_email">Email</label><input id="ketua_email" name="ketua_email" type="email" value="{{ old('ketua_email') }}" class="form-control" required></div>
                                    <div class="col-md-6"><label class="form-label" for="ketua_no_hp">Nomor WhatsApp</label><input id="ketua_no_hp" name="ketua_no_hp" value="{{ old('ketua_no_hp') }}" class="form-control" placeholder="08xxxxxxxxxx" required></div>
                                    <div class="col-md-6"><label class="form-label" for="tanggal_kunjungan">Tanggal kunjungan</label><input id="tanggal_kunjungan" type="date" name="tanggal_kunjungan" value="{{ old('tanggal_kunjungan') }}" class="form-control" min="{{ date('Y-m-d') }}" required></div>
                                </div>
                            </section>

                            <section>
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div class="d-flex align-items-center gap-2"><span class="step-number">2</span><h2 class="h5 mb-0">Peserta dan jenis tiket</h2></div><span class="small text-secondary"><span id="participantCount">1</span>/10 orang</span></div>
                                <div id="participants" class="d-grid gap-3">
                                    <div class="participant-row public-card p-3" data-index="0">
                                        <div class="d-flex justify-content-between align-items-center mb-3"><strong>Peserta 1 · Ketua</strong><span class="badge text-bg-light">Ketua kelompok</span></div>
                                        <div class="row g-3"><div class="col-md-6"><label class="form-label" for="participant-name-0">Nama ketua</label><input id="participant-name-0" name="peserta[0][nama]" value="{{ old('peserta.0.nama', old('ketua_nama')) }}" class="form-control" readonly required></div><div class="col-md-6"><label class="form-label" for="participant-ticket-0">Jenis tiket ketua</label><select id="participant-ticket-0" name="peserta[0][id_jenis_tiket]" class="form-select participant-ticket" required><option value="">Pilih jenis tiket</option>@foreach($destination->jenisTiket as $ticket)<option value="{{ $ticket->id_jenis_tiket }}" data-price="{{ $ticket->harga }}">{{ $ticket->nama_jenis }} · Rp {{ number_format($ticket->harga, 0, ',', '.') }}</option>@endforeach</select></div></div>
                                    </div>
                                </div>
                                <button type="button" id="addParticipant" class="btn btn-outline-secondary rounded-pill mt-3"><i class="bi bi-person-plus me-1"></i>Tambah anggota</button>
                                <p class="small text-secondary mt-2 mb-0">Ketua dan setiap anggota wajib memilih satu jenis tiket.</p>
                            </section>

                            <div class="d-flex justify-content-end gap-2 mt-5"><a href="{{ route('destinations.show', $destination->id_destinasi) }}" class="btn btn-outline-secondary rounded-pill">Batal</a><button type="submit" class="btn btn-jg-primary btn-lg px-5">Lanjut ke Pembayaran</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="customer-card sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <span class="eyebrow text-orange">Ringkasan</span>
                        <div class="d-flex gap-3 align-items-center mt-3 mb-3"><img src="{{ $destination->foto_utama ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=200&q=80' }}" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;" alt="{{ $destination->nama_wisata }}"><div><h2 class="h6 mb-1">{{ $destination->nama_wisata }}</h2><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($destination->alamat, 32) }}</small></div></div>
                        <hr>
                        <div class="d-flex justify-content-between"><span class="text-secondary">Ketua kelompok</span><strong><span id="summaryLeader">0</span> orang</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span class="text-secondary">Anggota</span><strong><span id="summaryMembers">0</span> orang</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span class="text-secondary">Total harga</span><strong class="text-orange" id="summaryTotal">Rp 0</strong></div>
                        <p class="small text-secondary mt-3 mb-0">Harga akhir dihitung berdasarkan jenis tiket yang dipilih setiap peserta.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .step-number { display: inline-grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; background: var(--jg-orange); color: #fff; font-weight: 700; }
    .participant-row { border-color: var(--jg-border); }
</style>
<script>
    (() => {
        const participants = document.getElementById('participants');
        const addButton = document.getElementById('addParticipant');
        const count = document.getElementById('participantCount');
        const summaryLeader = document.getElementById('summaryLeader');
        const summaryMembers = document.getElementById('summaryMembers');
        const summaryTotal = document.getElementById('summaryTotal');
        let nextIndex = 1;

        const updateCount = () => {
            const leaderReady = ['ketua_nama', 'ketua_email', 'ketua_no_hp', 'tanggal_kunjungan'].every(name => document.querySelector(`[name="${name}"]`)?.value.trim());
            const rows = [...participants.querySelectorAll('.participant-row')];
            const leaderTicket = rows[0]?.querySelector('.participant-ticket');
            const memberCount = rows.slice(1).filter(row => row.querySelector('input[name*="[nama]"]')?.value.trim() && row.querySelector('.participant-ticket')?.value).length;
            const leaderCount = leaderReady && leaderTicket?.value ? 1 : 0;
            const total = leaderCount + memberCount;
            count.textContent = total;
            summaryLeader.textContent = leaderCount;
            summaryMembers.textContent = memberCount;
            addButton.disabled = rows.length >= 10;
            const totalPrice = leaderCount ? rows.reduce((sum, row) => sum + Number(row.querySelector('.participant-ticket')?.options[row.querySelector('.participant-ticket')?.selectedIndex]?.dataset.price || 0), 0) : 0;
            summaryTotal.textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
        };

        document.querySelectorAll('[name="ketua_nama"], [name="ketua_email"], [name="ketua_no_hp"], [name="tanggal_kunjungan"]').forEach(input => input.addEventListener('input', () => {
            if (input.name === 'ketua_nama') document.getElementById('participant-name-0').value = input.value;
            updateCount();
        }));

        addButton.addEventListener('click', () => {
            if (participants.children.length >= 10) return;
            const row = document.createElement('div');
            row.className = 'participant-row public-card p-3';
            row.dataset.index = nextIndex;
            row.innerHTML = `<div class="d-flex justify-content-between align-items-center mb-3"><strong>Peserta ${nextIndex + 1} · Anggota</strong><button type="button" class="btn btn-sm btn-outline-danger rounded-pill remove-participant"><i class="bi bi-trash"></i><span class="visually-hidden">Hapus anggota</span></button></div><div class="row g-3"><div class="col-md-6"><label class="form-label" for="participant-name-${nextIndex}">Nama peserta</label><input id="participant-name-${nextIndex}" name="peserta[${nextIndex}][nama]" class="form-control" required></div><div class="col-md-6"><label class="form-label" for="participant-ticket-${nextIndex}">Jenis tiket</label><select id="participant-ticket-${nextIndex}" name="peserta[${nextIndex}][id_jenis_tiket]" class="form-select participant-ticket" required><option value="">Pilih jenis tiket</option>@foreach($destination->jenisTiket as $ticket)<option value="{{ $ticket->id_jenis_tiket }}" data-price="{{ $ticket->harga }}">{{ $ticket->nama_jenis }} · Rp {{ number_format($ticket->harga, 0, ',', '.') }}</option>@endforeach</select></div></div>`;
            participants.appendChild(row);
            nextIndex++;
            updateCount();
        });

        participants.addEventListener('click', event => {
            const removeButton = event.target.closest('.remove-participant');
            if (!removeButton) return;
            removeButton.closest('.participant-row').remove();
            updateCount();
        });

        participants.addEventListener('change', event => {
            if (event.target.matches('.participant-ticket')) updateCount();
        });

        updateCount();
    })();
</script>
@endsection