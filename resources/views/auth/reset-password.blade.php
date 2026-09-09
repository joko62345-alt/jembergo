<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Baru | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('home') }}" class="auth-brand d-inline-flex align-items-center gap-2 mb-4 fw-bold fs-4"><span class="brand-mark"><i class="bi bi-compass"></i></span> Jember<span class="brand-accent">Go</span></a>
            <div class="auth-card bg-white">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold">Buat password baru</h1>
                    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                    <form method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><input type="hidden" name="email" value="{{ $email }}"><label class="form-label mt-3">Password baru</label><input type="password" name="password" class="form-control" required><label class="form-label mt-3">Konfirmasi password</label><input type="password" name="password_confirmation" class="form-control" required><button class="btn btn-warning rounded-pill w-100 mt-4">Simpan password</button></form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>