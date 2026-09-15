@extends('layouts.app')

@section('title', 'Kelola Perjalanan - JemberGo')

@section('content')
@include('components.public-navbar')

@php
    $currentMembers = collect($booking->anggota_names ?? []);
    $currentCount = 1 + $currentMembers->count();
    $remaining = max(0, 10 - $currentCount);
@endphp

<main class="manage-page">
    <div class="container manage-container">
        <a href="{{ route('customer.ticket', $booking->id_pemesanan) }}" class="manage-back"><i class="bi bi-arrow-left"></i> Kembali ke E-Ticket</a>
        <div class="manage-header"><div><span class="manage-kicker">Kelola perjalanan</span><h1>Ubah perjalanan dengan tenang.</h1><p>Perubahan jadwal dan tiket tambahan akan diproses setelah pembayaran berhasil.</p></div><span class="manage-code"><i class="bi bi-receipt"></i>{{ $booking->kode_booking }}</span></div>

        <div class="manage-steps"><div class="is-active"><span>1</span><strong>Perjalanan</strong></div><i class="bi bi-chevron-right"></i><div><span>2</span><strong>Data Tiket</strong></div><i class="bi bi-chevron-right"></i><div><span>3</span><strong>Review</strong></div><i class="bi bi-chevron-right"></i><div><span>4</span><strong>Pembayaran</strong></div><i class="bi bi-chevron-right"></i><div><span>5</span><strong>Selesai</strong></div></div>

        @if($pending)
            <div class="manage-pending"><div><i class="bi bi-hourglass-split"></i><div><strong>Ada perubahan yang belum selesai</strong><p>Lanjutkan ke review dan pembayaran agar E-Ticket diperbarui.</p></div></div><a href="{{ route('customer.change.checkout', [$booking->id_pemesanan, $pending->id_perubahan]) }}" class="btn btn-warning">Lanjutkan pembayaran <i class="bi bi-arrow-right"></i></a></div>
        @endif
        @if(session('change_error'))
            <div class="manage-form-error">{{ session('change_error') }}</div>
        @endif

        <form method="POST" action="{{ route('customer.manage.review', $booking->id_pemesanan) }}" id="manageForm">
            @csrf
            <div class="manage-layout">
                <section class="manage-main">
                    <article class="manage-card">
                        <div class="card-heading"><div class="heading-icon"><i class="bi bi-calendar2-week"></i></div><div><span>01 · Perjalanan</span><h2>Ubah jadwal</h2><p>Pilih tanggal baru untuk kunjungan Anda.</p></div></div>
                        <label for="tanggal_kunjungan">Tanggal kunjungan baru</label>
                        <input id="tanggal_kunjungan" name="tanggal_kunjungan" type="date" min="{{ today()->toDateString() }}" value="{{ old('tanggal_kunjungan', $booking->tanggal_kunjungan->toDateString()) }}" required>
                        @error('tanggal_kunjungan')<small class="field-error">{{ $message }}</small>@enderror
                        <div class="date-note"><i class="bi bi-info-circle"></i> Jadwal lama: <strong>{{ $booking->tanggal_kunjungan->translatedFormat('d F Y') }}</strong></div>
                    </article>

                    <article class="manage-card">
                        <div class="card-heading"><div class="heading-icon"><i class="bi bi-people"></i></div><div><span>02 · Data tiket</span><h2>Tambah tiket</h2><p>Tambahkan anggota atau tiket tambahan untuk perjalanan Anda.</p></div></div>
                        @if($remaining > 0)
                            <div class="member-list" id="memberList">
                                <div class="member-row" data-member-row><div class="member-number">01</div><div class="member-fields"><label>Nama anggota<input name="anggota[0][nama]" placeholder="Contoh: Dinda Pratama"></label><label>Jenis tiket<select name="anggota[0][id_jenis_tiket]"><option value="">Pilih jenis tiket</option>@foreach($booking->destinasi->jenisTiket as $ticket)<option value="{{ $ticket->id_jenis_tiket }}" data-price="{{ $ticket->harga }}">{{ $ticket->nama_jenis }} · Rp {{ number_format($ticket->harga, 0, ',', '.') }}</option>@endforeach</select></label></div><button type="button" class="remove-member" aria-label="Hapus anggota"><i class="bi bi-trash3"></i></button></div>
                            </div>
                            <button type="button" class="add-member" id="addMember"><i class="bi bi-plus-lg"></i> Tambah anggota</button>
                            <p class="member-limit"><i class="bi bi-shield-check"></i> {{ $remaining }} slot tiket tersedia · maksimal 10 orang per booking.</p>
                        @else
                            <div class="empty-members"><i class="bi bi-check2-circle"></i><strong>Booking sudah penuh</strong><span>Jumlah peserta saat ini sudah mencapai 10 orang.</span></div>
                        @endif
                    </article>
                </section>

                <aside class="manage-summary">
                    <div class="summary-card">
                        <span class="summary-kicker">Ringkasan pesanan</span><h2>{{ $booking->destinasi->nama_wisata }}</h2><p><i class="bi bi-geo-alt"></i>{{ Str::limit($booking->destinasi->alamat, 42) }}</p>
                        <div class="summary-line"><span>Jadwal saat ini</span><strong>{{ $booking->tanggal_kunjungan->translatedFormat('d M Y') }}</strong></div><div class="summary-line"><span>Jumlah tiket</span><strong>{{ $currentCount }} tiket</strong></div>
                        <hr><div class="summary-addition"><span>Tiket tambahan</span><strong id="summaryCount">0 tiket</strong></div><div class="summary-addition"><span>Subtotal tambahan</span><strong id="summarySubtotal">Rp 0</strong></div>
                        <div class="summary-total"><span>Total biaya tambahan</span><strong id="summaryTotal">Rp 0</strong></div>
                        <button class="primary-action" id="reviewChanges" type="submit" disabled>Review perubahan <i class="bi bi-arrow-right"></i></button>
                        <small class="summary-help">Anda akan melihat detail perubahan sebelum melanjutkan ke pembayaran.</small>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</main>

<style>
    .manage-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: .9rem; line-height: 1.5; }
    .manage-header h1 { font-size: clamp(1.7rem, 2.8vw, 2.25rem); line-height: 1.2; }
    .manage-card h2, .summary-card h2 { font-size: 1.05rem; line-height: 1.3; }
    .manage-card input, .manage-card select { font-size: .84rem; }
    .card-heading p, .manage-header p, .summary-help { font-size: .8rem; }
    .manage-page { min-height: 100vh; padding: 7.5rem 0 5rem; background: #f7f9fb; color: #173b60; }.manage-container { max-width: 1160px; }.manage-back { color: #61788b; text-decoration: none; font-size: .86rem; }.manage-back:hover { color: #f58b05; }.manage-header { display:flex; justify-content:space-between; align-items:end; gap:1rem; margin:2rem 0 1.5rem; }.manage-kicker,.summary-kicker { color:#f58b05; font-size:.72rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }.manage-header h1 { margin:.45rem 0; font-size:clamp(2rem,4vw,3rem); font-weight:600; letter-spacing:0; }.manage-header p { margin:0; color:#708496; }.manage-code { padding:.75rem 1rem; border:1px solid #dce6ed; border-radius:.7rem; background:#fff; color:#567087; font-family:monospace; font-size:.8rem; }.manage-code i { margin-right:.45rem; }.manage-steps { display:flex; align-items:center; gap:.7rem; padding:1rem 1.1rem; margin-bottom:1.25rem; border:1px solid #dce6ed; border-radius:.8rem; background:#fff; color:#9aabb7; }.manage-steps div { display:flex; align-items:center; gap:.4rem; font-size:.75rem; white-space:nowrap; }.manage-steps span { display:grid; place-items:center; width:24px; height:24px; border:1px solid #cdd9e2; border-radius:50%; font-size:.7rem; }.manage-steps .is-active { color:#173b60; }.manage-steps .is-active span { border-color:#f58b05; background:#f58b05; color:#fff; }.manage-steps i { color:#c5d0d8; font-size:.7rem; }.manage-pending { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.2rem; margin-bottom:1.25rem; border:1px solid #f0c36b; border-radius:.8rem; background:#fffaf0; }.manage-pending > div { display:flex; align-items:center; gap:.8rem; }.manage-pending > div > i { color:#f58b05; font-size:1.25rem; }.manage-pending p { margin:.2rem 0 0; color:#708496; font-size:.82rem; }.manage-layout { display:grid; grid-template-columns:minmax(0, 1.85fr) minmax(290px, 1fr); gap:1.25rem; align-items:start; }.manage-main { display:grid; gap:1.25rem; }.manage-card,.summary-card { border:1px solid #dce6ed; border-radius:1rem; background:#fff; box-shadow:0 8px 24px rgba(23,59,96,.04); }.manage-card { padding:1.5rem; }.card-heading { display:flex; gap:.85rem; align-items:flex-start; padding-bottom:1.2rem; margin-bottom:1.2rem; border-bottom:1px solid #edf1f4; }.heading-icon { display:grid; place-items:center; width:38px; height:38px; border-radius:.65rem; background:#edf5f8; color:#315d78; }.card-heading span { color:#f58b05; font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }.card-heading h2 { margin:.3rem 0 .2rem; font-size:1.25rem; }.card-heading p { margin:0; color:#708496; font-size:.86rem; }.manage-card > label,.member-fields label { display:block; color:#385872; font-size:.78rem; font-weight:600; }.manage-card input,.manage-card select { display:block; width:100%; min-height:46px; margin-top:.4rem; padding:.65rem .75rem; border:1px solid #ccd9e2; border-radius:.6rem; color:#173b60; background:#fff; outline:0; }.manage-card input:focus,.manage-card select:focus { border-color:#f58b05; box-shadow:0 0 0 3px rgba(245,139,5,.12); }.date-note,.member-limit { margin:.8rem 0 0; color:#778d9d; font-size:.78rem; }.date-note i,.member-limit i { color:#f58b05; margin-right:.3rem; }.field-error { display:block; margin-top:.4rem; color:#b23b47; }.member-list { display:grid; gap:.7rem; }.member-row { display:grid; grid-template-columns:32px minmax(0,1fr) 34px; gap:.75rem; align-items:center; padding:.8rem; border:1px solid #e2eaf0; border-radius:.75rem; background:#fbfcfd; }.member-number { color:#9aabb7; font-family:monospace; font-size:.8rem; }.member-fields { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; }.remove-member { width:32px; height:32px; border:0; border-radius:.5rem; background:#fff1f2; color:#b23b47; }.add-member { display:inline-flex; align-items:center; gap:.4rem; margin-top:1rem; padding:.55rem .75rem; border:1px dashed #9eb6c5; border-radius:.55rem; background:#fff; color:#315d78; font-size:.82rem; }.add-member:hover { border-color:#f58b05; color:#f58b05; }.empty-members { display:grid; gap:.35rem; place-items:center; padding:2rem; color:#708496; text-align:center; }.empty-members i { color:#4c9b6b; font-size:1.6rem; }.empty-members strong { color:#173b60; }.summary-card { position:sticky; top:6.5rem; padding:1.5rem; }.summary-card h2 { margin:.4rem 0 .2rem; font-size:1.25rem; }.summary-card p { margin:0 0 1.25rem; color:#708496; font-size:.8rem; }.summary-card p i { margin-right:.35rem; }.summary-line,.summary-addition { display:flex; justify-content:space-between; gap:1rem; padding:.6rem 0; color:#708496; font-size:.82rem; }.summary-line strong,.summary-addition strong { color:#173b60; text-align:right; }.summary-card hr { margin:1rem 0 .4rem; border-color:#e5edf2; }.summary-total { display:flex; justify-content:space-between; gap:1rem; align-items:end; padding-top:1rem; margin-top:.7rem; border-top:1px solid #e5edf2; color:#385872; font-size:.86rem; }.summary-total strong { color:#f58b05; font-size:1.3rem; }.primary-action { display:flex; justify-content:center; align-items:center; gap:.5rem; width:100%; margin-top:1.25rem; padding:.85rem 1rem; border:0; border-radius:.65rem; background:#f58b05; color:#fff; font-weight:700; }.primary-action:hover { background:#d97706; }.summary-help { display:block; margin-top:.7rem; color:#8a9aa6; font-size:.72rem; text-align:center; }
    .manage-form-error { padding:.85rem 1rem; margin-bottom:1.25rem; border:1px solid #dbe6ee; border-radius:.7rem; background:#fff; color:#567087; font-size:.84rem; }.primary-action:disabled { cursor:not-allowed; background:#c8d2d9; }
    @media (max-width: 800px) { .manage-header { align-items:start; flex-direction:column; }.manage-layout { grid-template-columns:1fr; }.summary-card { position:static; }.manage-steps { overflow:auto; }.manage-steps i { flex:0 0 auto; }.manage-steps div { flex:0 0 auto; } }
    @media (max-width: 560px) { .manage-page { padding-top:6rem; }.manage-card,.summary-card { padding:1rem; }.member-fields { grid-template-columns:1fr; }.member-row { align-items:start; }.manage-pending { align-items:start; flex-direction:column; }.manage-pending .btn { width:100%; }.manage-code { align-self:stretch; text-align:center; } }
    .manage-page { font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif; font-size: .9rem; line-height: 1.5; }
    .manage-header h1 { font-size: clamp(1.7rem, 2.8vw, 2.25rem); line-height: 1.2; }
    .manage-card h2, .summary-card h2 { font-size: 1.05rem; line-height: 1.3; }
    .manage-card input, .manage-card select { font-size: .84rem; }
    .card-heading p, .manage-header p, .summary-help { font-size: .8rem; }
</style>
@php
    $ticketOptions = $booking->destinasi->jenisTiket->map(function ($ticket): array {
        return [
            'id' => $ticket->id_jenis_tiket,
            'label' => $ticket->nama_jenis . ' · Rp ' . number_format($ticket->harga, 0, ',', '.'),
            'price' => $ticket->harga,
        ];
    })->values()->all();
@endphp
<script>
(() => {
    const list = document.getElementById('memberList');
    const add = document.getElementById('addMember');
    const count = document.getElementById('summaryCount');
    const subtotal = document.getElementById('summarySubtotal');
    const total = document.getElementById('summaryTotal');
    if (!list || !add) return;
    let index = 1;
    const max = {{ $remaining }};
    const money = value => 'Rp ' + value.toLocaleString('id-ID');
    const refresh = () => {
        const rows = [...list.querySelectorAll('[data-member-row]')];
        let amount = 0;
        rows.forEach((row, rowIndex) => { row.querySelector('.member-number').textContent = String(rowIndex + 1).padStart(2, '0'); amount += Number(row.querySelector('select')?.selectedOptions[0]?.dataset.price || 0); });
        count.textContent = rows.filter(row => row.querySelector('input')?.value.trim() && row.querySelector('select')?.value).length + ' tiket'; subtotal.textContent = money(amount); total.textContent = money(amount);
    };
    const ticketOptions = @json($ticketOptions);
    const options = ticketOptions.map(ticket => `<option value="${ticket.id}" data-price="${ticket.price}">${ticket.label}</option>`).join('');
    add.addEventListener('click', () => { if (index >= max) return; const row = document.createElement('div'); row.className = 'member-row'; row.dataset.memberRow = ''; row.innerHTML = `<div class="member-number">${String(index + 1).padStart(2, '0')}</div><div class="member-fields"><label>Nama anggota<input name="anggota[${index}][nama]" placeholder="Nama lengkap" required></label><label>Jenis tiket<select name="anggota[${index}][id_jenis_tiket]" required><option value="">Pilih jenis tiket</option>${options}</select></label></div><button type="button" class="remove-member" aria-label="Hapus anggota"><i class="bi bi-trash3"></i></button>`; list.appendChild(row); index++; if (index >= max) add.disabled = true; refresh(); });
    const review = document.getElementById('reviewChanges');
    const originalDate = @json($booking->tanggal_kunjungan->format('Y-m-d'));
    const updateReviewState = () => {
        const dateChanged = document.getElementById('tanggal_kunjungan')?.value !== originalDate;
        const memberAdded = [...list.querySelectorAll('[data-member-row]')].some(row => row.querySelector('input')?.value.trim() && row.querySelector('select')?.value);
        review.disabled = !dateChanged && !memberAdded;
    };
    list.addEventListener('input', () => { refresh(); updateReviewState(); }); list.addEventListener('change', () => { refresh(); updateReviewState(); }); list.addEventListener('click', event => { const button = event.target.closest('.remove-member'); if (button) { button.closest('[data-member-row]').remove(); add.disabled = false; refresh(); updateReviewState(); } }); document.getElementById('tanggal_kunjungan')?.addEventListener('change', updateReviewState); refresh(); updateReviewState();
})();
</script>
@endsection
