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
<main class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <a href="{{ route('home') }}" class="text-dark text-decoration-none">Kembali ke JemberGo</a>
            <div class="customer-card mt-4">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold">Profil saya</h1>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('customer.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <label class="form-label mt-3" for="nama">Nama</label>
                        <input id="nama" name="nama" value="{{ old('nama', $customer->nama) }}" class="form-control" required>

                        <label class="form-label mt-3" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" class="form-control" required>

                        <label class="form-label mt-3" for="no_hp">Nomor HP</label>
                        <input id="no_hp" name="no_hp" value="{{ old('no_hp', $customer->no_hp) }}" class="form-control">

                        <label class="form-label mt-3" for="alamat">Alamat</label>
                        <textarea id="alamat" name="alamat" class="form-control">{{ old('alamat', $customer->alamat) }}</textarea>

                        <hr class="my-4">
                        <h2 class="h6">Ganti password</h2>
                        <div class="input-group mt-2"><input id="profile-password" name="password" type="password" class="form-control" placeholder="Kosongkan jika tidak diubah"><button type="button" class="btn btn-outline-secondary password-toggle" data-target="profile-password" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div>
                        <div class="input-group mt-2"><input id="profile-password-confirmation" name="password_confirmation" type="password" class="form-control" placeholder="Konfirmasi password baru"><button type="button" class="btn btn-outline-secondary password-toggle" data-target="profile-password-confirmation" aria-label="Tampilkan konfirmasi password"><i class="bi bi-eye"></i></button></div>

                        <button class="btn btn-warning rounded-pill w-100 mt-4" type="submit">Simpan perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<x-public-footer />
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
