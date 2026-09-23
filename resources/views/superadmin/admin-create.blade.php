@extends('layouts.superadmin')
@section('title', 'Buat Admin')
@section('page_label', 'Manajemen / Buat admin')
@section('content')
    <div class="page-heading"><span class="page-kicker">Manajemen pengguna</span><h1>Buat akun Admin Pariwisata</h1><p>Akun ini dibuat oleh Super Admin dan digunakan untuk mengelola satu destinasi.</p></div>
    <div class="row"><div class="col-lg-8"><div class="card"><div class="card-body p-4 p-lg-5">
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('superadmin.management.admin') }}" class="row g-3">@csrf
            <div class="col-md-6"><label class="form-label" for="nama">Nama</label><input id="nama" name="nama" class="form-control" placeholder="Masukkan Nama" value="{{ old('nama') }}" required><small id="nama_warning" class="text-danger" hidden>Nama tersebut sudah digunakan oleh admin lain.</small></div>
            <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" placeholder="nama@gmail.com" value="{{ old('email') }}" required><small id="email_warning" class="text-danger" hidden>Email admin harus menggunakan @gmail.com.</small></div>
            <div class="col-md-6"><label class="form-label" for="no_hp">Nomor HP</label><input id="no_hp" name="no_hp" type="tel" inputmode="numeric" pattern="[0-9]+" class="form-control" placeholder="Masukkan Nomor HP" value="{{ old('no_hp') }}" required><small id="no_hp_format_warning" class="text-danger" hidden></small><small id="no_hp_warning" class="text-danger" hidden>Nomor HP tersebut sudah digunakan oleh admin pariwisata lain.</small></div>
            <div class="col-md-6"><label class="form-label" for="password">Password</label><div class="input-group"><input id="password" name="password" type="password" minlength="8" class="form-control password-input" placeholder="Masukkan Password" required><button type="button" class="btn password-toggle" data-target="password" aria-label="Tampilkan password"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
            <div class="col-12"><label class="form-label" for="id_destinasi">Destinasi tugas</label><select id="id_destinasi" name="id_destinasi" class="form-select @error('id_destinasi') is-invalid @enderror" required><option value="">Pilih destinasi tugas</option>@foreach($destinations as $destination)<option value="{{ $destination->id_destinasi }}" data-destination-name="{{ $destination->nama_wisata }}" @selected(old('id_destinasi') == $destination->id_destinasi)>{{ $destination->nama_wisata }}</option>@endforeach</select><small id="destination_warning" class="text-danger" role="alert" @if(!$errors->has('id_destinasi')) hidden @endif>@error('id_destinasi'){{ $message }}@else Destinasi tugas harus sesuai dengan nama admin.@enderror</small></div>
            <div class="col-12"><button class="btn btn-warning" type="submit">Simpan akun admin</button></div>
        </form>
    </div></div></div></div>
@endsection
@push('scripts')
<script>
    const existingAdmins = @js($existingAdmins);
    const duplicateFields = [
        { input: document.getElementById('nama'), warning: document.getElementById('nama_warning'), key: 'nama', message: 'Nama tersebut sudah digunakan oleh admin lain.' },
        { input: document.getElementById('email'), warning: document.getElementById('email_warning'), key: 'email', message: 'Email tersebut sudah digunakan oleh admin lain.' },
    ];
    const normalizeDuplicateValue = (value) => String(value || '').trim().toLowerCase();
    const emailInput = document.getElementById('email');
    const emailWarning = document.getElementById('email_warning');
    const validateAdminEmail = () => {
        const invalidDomain = Boolean(emailInput?.value.trim()) && !/@gmail\.com$/i.test(emailInput.value.trim());
        const duplicate = existingAdmins.some((admin) => normalizeDuplicateValue(admin.email) === normalizeDuplicateValue(emailInput?.value));
        const message = invalidDomain ? 'Email admin harus menggunakan @gmail.com.' : 'Email tersebut sudah digunakan oleh admin lain.';
        emailInput?.classList.toggle('is-invalid', invalidDomain || duplicate);
        emailInput?.setCustomValidity(invalidDomain ? message : (duplicate ? message : ''));
        if (emailWarning) { emailWarning.textContent = message; emailWarning.hidden = !invalidDomain && !duplicate; }
    };
    const destinationInput = document.getElementById('id_destinasi');
    const destinationWarning = document.getElementById('destination_warning');
    const normalizeDestinationName = (value) => String(value || '').trim().replace(/^admin\s+/i, '').replace(/\s+/g, ' ').toLowerCase();
    const validateAdminDestination = () => {
        const selectedDestination = destinationInput?.selectedOptions[0]?.dataset.destinationName || '';
        const hasMismatch = Boolean(document.getElementById('nama')?.value.trim() && selectedDestination) && normalizeDestinationName(document.getElementById('nama').value) !== normalizeDestinationName(selectedDestination);
        const message = 'Destinasi  harus sesuai dengan nama admin.';
        destinationInput?.classList.toggle('is-invalid', hasMismatch);
        destinationInput?.setCustomValidity(hasMismatch ? message : '');
        if (destinationWarning) { destinationWarning.textContent = message; destinationWarning.hidden = !hasMismatch; }
        return !hasMismatch;
    };
    const validateDuplicates = () => duplicateFields.forEach(({ input, warning, key, message }) => {
        const duplicate = Boolean(input.value.trim()) && existingAdmins.some((admin) => normalizeDuplicateValue(admin[key]) === normalizeDuplicateValue(input.value));
        input.classList.toggle('is-invalid', duplicate);
        input.setCustomValidity(duplicate ? message : '');
        warning.hidden = !duplicate;
    });
    duplicateFields.forEach(({ input }) => input?.addEventListener('input', validateDuplicates));
    emailInput?.addEventListener('input', validateAdminEmail);
    document.getElementById('nama')?.addEventListener('input', validateAdminDestination);
    destinationInput?.addEventListener('change', validateAdminDestination);
    validateDuplicates();
    validateAdminEmail();
    validateAdminDestination();
    const adminPhoneInput = document.getElementById('no_hp');
    const adminPhoneFormatWarning = document.getElementById('no_hp_format_warning');
    const adminPhoneWarning = document.getElementById('no_hp_warning');
    const validateAdminPhone = () => {
        const invalid = /[^0-9]/.test(adminPhoneInput.value);
        const tooShort = adminPhoneInput.value.length > 0 && adminPhoneInput.value.length < 10;
        const tooLong = adminPhoneInput.value.length > 12;
        const duplicate = Boolean(adminPhoneInput.value.trim()) && existingAdmins.some((admin) => normalizeDuplicateValue(admin.no_hp) === normalizeDuplicateValue(adminPhoneInput.value));
        const formatMessage = invalid ? 'Nomor HP hanya boleh berisi angka.' : (tooShort ? 'Nomor HP minimal 10 angka.' : (tooLong ? 'Nomor HP maksimal 12 angka.' : ''));
        const duplicateMessage = 'Nomor HP tersebut sudah digunakan oleh admin pariwisata lain.';
        adminPhoneInput.setCustomValidity(formatMessage || (duplicate ? duplicateMessage : ''));
        adminPhoneInput.classList.toggle('is-invalid', invalid || tooShort || tooLong || duplicate);
        adminPhoneFormatWarning.textContent = formatMessage;
        adminPhoneFormatWarning.hidden = !formatMessage;
        adminPhoneWarning.hidden = !duplicate || Boolean(formatMessage);
    };
    adminPhoneInput?.addEventListener('keydown', (event) => {
        if (event.key.length === 1 && !/[0-9]/.test(event.key)) {
            event.preventDefault();
            adminPhoneFormatWarning.textContent = 'Nomor HP hanya boleh berisi angka.';
            adminPhoneFormatWarning.hidden = false;
            adminPhoneWarning.hidden = true;
            adminPhoneInput.classList.add('is-invalid');
            adminPhoneInput.setCustomValidity('Nomor HP hanya boleh berisi angka.');
        }
    });
    adminPhoneInput?.addEventListener('input', validateAdminPhone);
    adminPhoneInput?.form.addEventListener('submit', (event) => {
        validateDuplicates();
        validateAdminEmail();
        validateAdminPhone();
        validateAdminDestination();
        if (/[^0-9]/.test(adminPhoneInput.value) || adminPhoneInput.value.length < 10 || adminPhoneInput.value.length > 12 || duplicateFields.some(({ input }) => input.validity.customError) || destinationInput?.validity.customError) event.preventDefault();
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
