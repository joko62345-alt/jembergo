<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <a href="{{ route('home') }}" class="text-dark text-decoration-none">Kembali ke JemberGo</a>
            <div class="card border-0 shadow-sm rounded-4 mt-4">
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
                        <input name="password" type="password" class="form-control mt-2" placeholder="Kosongkan jika tidak diubah">
                        <input name="password_confirmation" type="password" class="form-control mt-2" placeholder="Konfirmasi password baru">

                        <button class="btn btn-warning rounded-pill w-100 mt-4" type="submit">Simpan perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
