<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="customer-page"><x-public-navbar />
<main class="container profile-page">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <a href="{{ route('home') }}" class="profile-back"><i class="bi bi-arrow-left"></i> Kembali ke JemberGo</a>
            <div class="profile-heading"><span class="profile-eyebrow">Akun saya</span><h1>Profil saya</h1><p>Kelola informasi akun dan keamananmu di JemberGo.</p></div>
            <div class="customer-card profile-card mt-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="profile-card-heading"><span class="profile-avatar"><i class="bi bi-person"></i></span><div><h2>Informasi pribadi</h2><p>Pastikan data yang digunakan untuk pemesanan selalu benar.</p></div></div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('customer.profile.update') }}" class="profile-form">
                        @csrf
                        @method('PUT')

                        <div class="row g-3"><div class="col-md-6"><label class="form-label" for="nama">Nama lengkap</label><input id="nama" name="nama" value="{{ old('nama', $customer->nama) }}" class="form-control" placeholder="Nama lengkap" required></div><div class="col-md-6"><label class="form-label" for="email">Alamat email</label><input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" class="form-control" placeholder="nama@email.com" required></div><div class="col-md-6"><label class="form-label" for="no_hp">Nomor WhatsApp</label><input id="no_hp" name="no_hp" value="{{ old('no_hp', $customer->no_hp) }}" class="form-control" placeholder="08xxxxxxxxxx"></div></div>

                        <div class="profile-security"><div class="profile-card-heading"><span class="profile-avatar security"><i class="bi bi-shield-lock"></i></span><div><h2>Keamanan akun</h2><p>Gunakan minimal 8 karakter. Kosongkan jika tidak ingin mengubahnya.</p></div></div>
                        <div class="row g-3"><div class="col-md-6"><label class="form-label" for="profile-password">Password baru</label><div class="input-group"><input id="profile-password" name="password" type="password" class="form-control" placeholder="Password baru"><button type="button" class="btn btn-outline-secondary password-toggle" data-target="profile-password" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div><div class="col-md-6"><label class="form-label" for="profile-password-confirmation">Konfirmasi password</label><div class="input-group"><input id="profile-password-confirmation" name="password_confirmation" type="password" class="form-control" placeholder="Ulangi password"><button type="button" class="btn btn-outline-secondary password-toggle" data-target="profile-password-confirmation" aria-label="Tampilkan konfirmasi password"><i class="bi bi-eye"></i></button></div></div></div>
                        </div>

                        <div class="profile-submit-wrap"><button class="profile-submit" type="submit"><span>Simpan perubahan</span><i class="bi bi-arrow-right"></i></button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<x-public-footer />
<style>
    .profile-page { padding-top: 3rem; padding-bottom: 5rem; font-size: .9rem; }.profile-back { display: inline-flex; align-items: center; gap: .45rem; color: #567087; text-decoration: none; font-size: .8rem; }.profile-back:hover { color: var(--jg-orange); }.profile-heading { margin-top: 1.5rem; }.profile-eyebrow { color: var(--jg-orange); font-size: .68rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }.profile-heading h1 { margin: .35rem 0 .2rem; font-size: clamp(1.75rem, 3vw, 2.3rem); font-weight: 500; }.profile-heading p { margin: 0; color: #7890a1; font-size: .85rem; }.profile-card { border-color: #dbe6ee; box-shadow: 0 12px 30px rgba(31, 41, 55, .06); }.profile-card-heading { display: flex; align-items: center; gap: .7rem; margin-bottom: 1.25rem; }.profile-card-heading h2 { margin: 0; color: #173b60; font-size: 1rem; font-weight: 600; }.profile-card-heading p { margin: .15rem 0 0; color: #7890a1; font-size: .72rem; }.profile-avatar { display: inline-grid; place-items: center; flex: 0 0 2.3rem; width: 2.3rem; height: 2.3rem; border-radius: .7rem; background: #ffecd5; color: var(--jg-orange); font-size: 1rem; }.profile-avatar.security { background: #e8f3f8; color: #315d78; }.profile-page .form-label { margin-bottom: .35rem; color: #31516b; font-size: .76rem; font-weight: 600; }.profile-page .form-control { min-height: 44px; border-radius: .7rem; padding: .55rem .75rem; font-size: .85rem; background: #fbfdfe; }.profile-page .form-control::placeholder { color: #a1b0ba; font-size: .8rem; }.profile-page .form-control:focus { background: #fff; }.profile-form .input-group .form-control { border-right: 0; }.profile-form .input-group .btn { border-color: #dbe6ee; background: #fbfdfe; color: #7890a1; }.profile-form .input-group .btn:hover { color: var(--jg-orange); background: #fff; }.profile-security { margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e5edf2; }.profile-security .profile-card-heading { margin-bottom: .85rem; }.profile-security .input-group + .input-group { margin-top: .7rem !important; }.profile-submit-wrap { display: flex; justify-content: center; margin-top: 1.35rem; }.profile-submit { display: inline-flex; align-items: center; justify-content: space-between; gap: 1.25rem; width: auto; margin-top: 0; padding: .7rem 1rem; border: 0; border-radius: .7rem; background: var(--jg-orange); color: #fff; font-size: .85rem; font-weight: 600; box-shadow: 0 8px 18px rgba(245, 139, 5, .2); }.profile-submit:hover { background: #df7900; }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
        button.innerHTML = `<i class="bi bi-eye${visible ? '' : '-slash'}"></i>`;
    }));
</script>
</body>
</html>
