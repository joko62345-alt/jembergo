@extends('layouts.superadmin')
@section('title', 'Buat Admin')
@section('page_label', 'Manajemen / Buat admin')
@section('content')
    <div class="page-heading"><span class="page-kicker">Manajemen pengguna</span><h1>Buat akun Admin Pariwisata</h1><p>Akun ini dibuat oleh Super Admin dan digunakan untuk mengelola satu destinasi.</p></div>
    <div class="row"><div class="col-lg-8"><div class="card"><div class="card-body p-4 p-lg-5">
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('superadmin.management.admin') }}" class="row g-3">@csrf
            <div class="col-md-6"><label class="form-label" for="nama">Nama</label><input id="nama" name="nama" class="form-control" placeholder="Masukkan Nama" value="{{ old('nama') }}" required></div>
            <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" placeholder="Masukkan Email" value="{{ old('email') }}" required></div>
            <div class="col-md-6"><label class="form-label" for="no_hp">Nomor HP</label><input id="no_hp" name="no_hp" type="tel" inputmode="numeric" pattern="[0-9]+" class="form-control" placeholder="Masukkan Nomor HP" value="{{ old('no_hp') }}" required><small id="no_hp_warning" class="text-danger" hidden>Nomor HP hanya boleh berisi angka.</small></div>
            <div class="col-md-6"><label class="form-label" for="password">Password</label><div class="input-group"><input id="password" name="password" type="password" minlength="8" class="form-control password-input" placeholder="Masukkan Password" required><button type="button" class="btn password-toggle" data-target="password" aria-label="Tampilkan password"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
            <div class="col-12"><label class="form-label" for="id_destinasi">Destinasi tugas</label><select id="id_destinasi" name="id_destinasi" class="form-select" required><option value="">Pilih destinasi tugas</option>@foreach($destinations as $destination)<option value="{{ $destination->id_destinasi }}" @selected(old('id_destinasi') == $destination->id_destinasi)>{{ $destination->nama_wisata }}</option>@endforeach</select></div>
            <div class="col-12"><button class="btn btn-warning" type="submit">Simpan akun admin</button></div>
        </form>
    </div></div></div></div>
@endsection
@push('scripts')
<script>
    const adminPhoneInput = document.getElementById('no_hp');
    const adminPhoneWarning = document.getElementById('no_hp_warning');
    const validateAdminPhone = () => {
        const invalid = /[^0-9]/.test(adminPhoneInput.value);
        adminPhoneInput.setCustomValidity(invalid ? 'Nomor HP hanya boleh berisi angka.' : '');
        adminPhoneInput.classList.toggle('is-invalid', invalid);
        adminPhoneWarning.hidden = !invalid;
    };
    adminPhoneInput?.addEventListener('keydown', (event) => {
        if (event.key.length === 1 && !/[0-9]/.test(event.key)) {
            event.preventDefault();
            adminPhoneWarning.hidden = false;
            adminPhoneInput.classList.add('is-invalid');
            adminPhoneInput.setCustomValidity('Nomor HP hanya boleh berisi angka.');
        }
    });
    adminPhoneInput?.addEventListener('input', validateAdminPhone);
    adminPhoneInput?.form.addEventListener('submit', (event) => {
        validateAdminPhone();
        if (/[^0-9]/.test(adminPhoneInput.value)) event.preventDefault();
    });
    document.querySelectorAll('.password-toggle').forEach((button) => button.addEventListener('click', () => {
        const passwordInput = document.getElementById(button.dataset.target);
        const visible = passwordInput.type === 'text';
        passwordInput.type = visible ? 'password' : 'text';
        button.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
        button.innerHTML = `<i class="bi bi-eye${visible ? '' : '-slash'}" aria-hidden="true"></i>`;
    }));
</script>
@endpush
