<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Tiket | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <a href="{{ route('destinations.show', $destination->id_destinasi) }}" class="text-dark text-decoration-none">Kembali ke destinasi</a>
    <div class="row justify-content-center mt-4"><div class="col-lg-9"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
        <span class="text-warning text-uppercase small fw-bold">Pilih tiket per peserta</span>
        <h1 class="h2 fw-bold mt-2">{{ $destination->nama_wisata }}</h1>
        <p class="text-secondary">Tentukan jenis tiket di samping nama masing-masing peserta. Maksimal 10 orang termasuk ketua.</p>
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form action="{{ route('customer.booking.store', $destination->id_destinasi) }}" method="POST">
            @csrf
            <h2 class="h5 fw-bold mt-4">Data ketua kelompok</h2>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama ketua</label><input name="ketua_nama" value="{{ old('ketua_nama', session('jg_user_name')) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email ketua</label><input name="ketua_email" type="email" value="{{ old('ketua_email') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Nomor HP ketua</label><input name="ketua_no_hp" value="{{ old('ketua_no_hp') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Tanggal kunjungan</label><input name="tanggal_kunjungan" type="date" min="{{ now()->format('Y-m-d') }}" value="{{ old('tanggal_kunjungan') }}" class="form-control" required></div>
            </div>
            <div class="table-responsive mt-5"><table class="table align-middle"><thead><tr><th>Peserta</th><th>Nama</th><th style="min-width:220px">Jenis tiket</th><th></th></tr></thead><tbody id="participants">
                <tr><td><span class="badge text-bg-warning">Ketua</span></td><td><input name="peserta[0][nama]" value="{{ old('peserta.0.nama', session('jg_user_name')) }}" class="form-control" required></td><td><select name="peserta[0][id_jenis_tiket]" class="form-select" required><option value="">Pilih tiket</option>@foreach($ticketOptions as $ticket)<option value="{{ $ticket['id'] }}">{{ $ticket['label'] }}</option>@endforeach</select></td><td></td></tr>
            </tbody></table></div>
            <button type="button" id="add-member" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-plus-circle me-1"></i>Tambah anggota</button>
            <span class="small text-secondary ms-2"><span id="participant-count">1</span>/10 peserta</span>
            <button class="btn btn-warning rounded-pill w-100 fw-semibold mt-4" type="submit">Lanjut ke pembayaran <i class="bi bi-arrow-right ms-1"></i></button>
        </form>
    </div></div></div></div>
</main>
<script>
const participants = document.getElementById('participants');
const addMember = document.getElementById('add-member');
const participantCount = document.getElementById('participant-count');
const ticketOptions = @json($ticketOptions);
let nextIndex = 1;

addMember.addEventListener('click', () => {
    if (participants.rows.length >= 10) return;
    const row = document.createElement('tr');
    const options = ticketOptions.map(ticket => `<option value="${ticket.id}">${ticket.label}</option>`).join('');
    row.innerHTML = `<td><span class="badge bg-light text-dark">Anggota ${nextIndex}</span></td><td><input name="peserta[${nextIndex}][nama]" class="form-control" placeholder="Nama anggota ${nextIndex}" required></td><td><select name="peserta[${nextIndex}][id_jenis_tiket]" class="form-select" required><option value="">Pilih tiket</option>${options}</select></td><td><button type="button" class="btn btn-outline-danger btn-sm remove-member" aria-label="Hapus anggota"><i class="bi bi-x"></i></button></td>`;
    row.querySelector('.remove-member').addEventListener('click', () => { row.remove(); participantCount.textContent = participants.rows.length; addMember.disabled = false; });
    participants.appendChild(row);
    nextIndex++;
    participantCount.textContent = participants.rows.length;
    addMember.disabled = participants.rows.length >= 10;
});
</script>
</body>
</html>