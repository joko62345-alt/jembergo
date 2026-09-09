<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Daftar | JemberGo</title><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"><link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="auth-page"><main class="container min-vh-100 d-flex align-items-center justify-content-center py-5"><div class="row w-100 justify-content-center"><div class="col-md-8 col-lg-6">
    <a href="{{ route('home') }}" class="auth-brand d-inline-flex align-items-center gap-2 mb-4 fw-bold fs-4"><span class="brand-mark"><i class="bi bi-compass"></i></span> Jember<span class="brand-accent">Go</span></a>
    <div class="auth-card bg-white"><div class="card-body p-4 p-lg-5"><h1 class="h3 fw-bold">Buat akun JemberGo</h1><p class="text-secondary mb-4">Simpan rencana perjalanan dan pesan tiket dengan mudah.</p>
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('register.store') }}" method="POST">@csrf<div class="row g-3"><div class="col-md-6"><label class="form-label" for="nama">Nama lengkap</label><input id="nama" name="nama" value="{{ old('nama') }}" class="form-control" required></div><div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required></div><div class="col-md-6"><label class="form-label" for="no_hp">Nomor HP</label><input id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="form-control"></div><div class="col-md-6"><label class="form-label" for="alamat">Alamat</label><input id="alamat" name="alamat" value="{{ old('alamat') }}" class="form-control"></div><div class="col-md-6"><label class="form-label" for="password">Password</label><input id="password" name="password" type="password" class="form-control" required></div><div class="col-md-6"><label class="form-label" for="password_confirmation">Konfirmasi password</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required></div></div><button class="btn btn-warning w-100 rounded-pill fw-semibold py-2 mt-4" type="submit">Buat akun</button></form>
        <p class="text-center text-secondary small mt-4 mb-0">Sudah memiliki akun? <a href="{{ route('login') }}" class="text-warning fw-semibold">Masuk sekarang</a></p>
    </div></div>
</div></div></main></body></html>