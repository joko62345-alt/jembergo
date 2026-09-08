<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-light">
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center"><div class="col-md-6 col-lg-4">
        <a href="{{ route('home') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-2 mb-4 fw-bold fs-4"><span class="badge rounded-circle text-bg-warning p-2"><i class="bi bi-compass"></i></span> Jember<span class="text-warning">Go</span></a>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-lg-5">
            <h1 class="h3 fw-bold">Selamat datang kembali</h1><p class="text-secondary mb-4">Masuk untuk melanjutkan perjalananmu di JemberGo.</p>
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <form action="{{ route('login.store') }}" method="POST" novalidate>@csrf
                <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-4"><div class="d-flex justify-content-between"><label for="password" class="form-label">Password</label><a href="{{ route('password.request') }}" class="small text-warning">Lupa password?</a></div><input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <button class="btn btn-warning w-100 rounded-pill fw-semibold py-2" type="submit">Masuk <i class="bi bi-arrow-right ms-1"></i></button>
            </form>
            <p class="text-center text-secondary small mt-4 mb-0">Belum memiliki akun? <a href="{{ route('register') }}" class="text-warning fw-semibold">Daftar sebagai customer</a></p>
        </div></div>
    </div></div>
</main>
</body>
</html>