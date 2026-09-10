@php($authMode = $authMode ?? 'login')
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $authMode === 'register' ? 'Daftar' : 'Masuk' }} | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    <style>
        .auth-premium-page { min-height: 100vh; height: 100vh; display: grid; place-items: center; overflow: auto; padding: clamp(.75rem, 2vw, 1.5rem); background: #edf5f5; color: #173b60; }
        .auth-premium-shell { position: relative; width: min(1120px, 100%); height: min(650px, calc(100vh - clamp(1.5rem, 2.5vw, 2.5rem))); min-height: 0; overflow: hidden; border: 1px solid rgba(255, 255, 255, .8); border-radius: 2rem; background: #fff; box-shadow: 0 24px 70px rgba(23, 59, 96, .16); }
        .auth-panel { position: absolute; top: 0; bottom: 0; width: 50%; transition: transform 650ms cubic-bezier(.76, 0, .24, 1); }
        .auth-form-panel { left: 0; z-index: 2; display: flex; align-items: center; justify-content: center; overflow-y: auto; overflow-x: hidden; padding: 1.75rem clamp(1.5rem, 4vw, 4.5rem); background: #fffefa; }
        .auth-photo-panel { right: 0; z-index: 1; overflow: hidden; background: #123b78 url('{{ route('assets.login') }}') center / cover no-repeat; }
        .auth-photo-panel::before { position: absolute; inset: 0; background: linear-gradient(145deg, rgba(9, 31, 57, .08), rgba(9, 31, 57, .55)); content: ''; }
        .auth-premium-shell.is-register .auth-form-panel { transform: translateX(100%); }
        .auth-premium-shell.is-register .auth-photo-panel { transform: translateX(-100%); }
        .auth-form-inner { width: min(100%, 360px); max-height: 100%; }
        .auth-brand-mark { display: inline-flex; align-items: center; gap: .5rem; color: #123b78; text-decoration: none; font-weight: 600; font-size: .95rem; }
        .auth-brand-mark img { width: 132px; height: 54px; object-fit: contain; }
        .auth-kicker { color: #e77d00; font-size: .68rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; }
        .auth-form-inner h1 { margin: .7rem 0 .45rem; color: #173b60; font-size: clamp(1.1rem, 1.6vw, 1.45rem); font-weight: 700; line-height: 1.15; white-space: nowrap; }
        .auth-subtitle { margin-bottom: 1.2rem; color: #7890a1; font-size: .86rem; line-height: 1.55; }
        .auth-field { margin-bottom: .7rem; }
        .auth-field label { display: block; margin-bottom: .3rem; color: #31516b; font-size: .75rem; font-weight: 600; }
        .auth-field .form-control { min-height: 42px; border: 1px solid #dbe6ee; border-radius: .65rem; background: #f9fcfd; font-size: .82rem; }
        .auth-field .form-control:focus { border-color: #7fbbdb; background: #fff; box-shadow: 0 0 0 3px rgba(127, 187, 221, .16); }
        .auth-field .input-group .btn { border-color: #dbe6ee; background: #f9fcfd; color: #7890a1; }
        .auth-submit { display: flex; align-items: center; justify-content: space-between; width: 100%; margin-top: .5rem; padding: .78rem 1rem; border: 0; border-radius: .7rem; background: #e77d00; color: #fff; font-size: .86rem; font-weight: 600; box-shadow: 0 8px 18px rgba(231, 125, 0, .2); }
        .auth-submit:hover { background: #cc6d00; }
        .auth-extra { display: flex; justify-content: flex-end; margin-top: .7rem; }
        .auth-extra a, .auth-switch { color: #e77d00; font-size: .8rem; text-decoration: none; }
        .auth-extra a:hover, .auth-switch:hover { color: #b75e00; text-decoration: underline; }
        .auth-switch-row { margin: 1rem 0 0; color: #7890a1; font-size: .8rem; text-align: center; }
        .auth-switch { border: 0; background: none; padding: 0; font-weight: 600; }
        .auth-error { margin-bottom: 1rem; border: 0; border-radius: .7rem; background: #fff0f1; color: #a43b49; font-size: .78rem; }
        .auth-error ul { padding-left: 1.1rem; }
        .auth-photo-content { position: absolute; right: 2rem; bottom: 2.2rem; left: 2rem; z-index: 1; color: #fff; }
        .auth-photo-content .auth-location { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .7rem; border: 1px solid rgba(255, 255, 255, .28); border-radius: 999px; background: rgba(255, 255, 255, .13); backdrop-filter: blur(10px); font-size: .7rem; }
        .auth-photo-content h2 { max-width: 12ch; margin: .9rem 0 .35rem; color: #fff; font-size: clamp(1.8rem, 2.7vw, 2.7rem); font-weight: 500; }
        .auth-photo-content p { max-width: 28rem; margin: 0; color: rgba(255, 255, 255, .82); font-size: .86rem; }
        .auth-photo-badge { position: absolute; top: 2rem; right: 2rem; z-index: 1; display: inline-flex; align-items: center; gap: .45rem; padding: .55rem .75rem; border: 1px solid rgba(255, 255, 255, .25); border-radius: .7rem; background: rgba(255, 255, 255, .13); backdrop-filter: blur(10px); color: #fff; font-size: .72rem; }
        @media (max-width: 767.98px) {
            .auth-premium-page { height: 100vh; min-height: 100vh; overflow: auto; padding: .75rem; }
            .auth-premium-shell { width: 100%; height: calc(100vh - 1.5rem); min-height: 0; border-radius: 1.4rem; }
            .auth-panel { width: 100%; }
            .auth-form-panel { padding: 1rem 1rem .9rem; overflow-y: auto; overflow-x: hidden; }
            .auth-form-inner { width: 100%; max-width: 100%; }
            .auth-brand-mark img { width: 112px; height: 45px; }
            .auth-kicker { font-size: .62rem; }
            .auth-form-inner h1 { margin-top: .45rem; font-size: 1.15rem; font-weight: 700; }
            .auth-subtitle { margin-bottom: .85rem; font-size: .78rem; }
            .auth-field { margin-bottom: .6rem; }
            .auth-switch-row { margin-top: .8rem; }
            .auth-photo-panel { top: auto; height: 170px; transform: translateY(100%) !important; }
            .auth-premium-shell.is-register .auth-form-panel { transform: none; }
            .auth-premium-shell.is-register .auth-photo-panel { transform: translateY(0) !important; }
            .auth-photo-content { right: 1.25rem; bottom: 1.1rem; left: 1.25rem; }
            .auth-photo-content h2 { margin: .3rem 0 .05rem; font-size: 1.25rem; }
            .auth-photo-content p, .auth-photo-badge { display: none; }
        }
    </style>
</head>
<body>
<main class="auth-premium-page">
    <section class="auth-premium-shell {{ $authMode === 'register' ? 'is-register' : '' }}" id="authShell">
        <div class="auth-panel auth-form-panel">
            <div class="auth-form-inner">
                <a href="{{ route('home') }}" class="auth-brand-mark"><img src="{{ route('assets.logo') }}" alt="JemberGo"></a>
                <span class="auth-kicker d-block mt-4">Sistem Informasi dan Layanan Pariwisata Kabupaten Jember</span>
                <h1 data-login-title></h1>
                <p class="auth-subtitle" data-login-subtitle>Masuk untuk menikmati informasi dan layanan pariwisata Kabupaten Jember</p>
                @if(session('error'))<div class="alert auth-error">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert auth-error"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <div data-login-form>
                    <form action="{{ route('login.store') }}" method="POST">
                        @csrf
                        <div class="auth-field"><label for="login-email">Email</label><input id="login-email" type="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus></div>
                        <div class="auth-field"><label for="login-password">Password</label><div class="input-group"><input id="login-password" type="password" name="password" class="form-control" placeholder="Masukkan password" required><button type="button" class="btn password-toggle" data-target="login-password" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button></div></div>
                        <button class="auth-submit" type="submit"><span>Masuk</span><i class="bi bi-arrow-right"></i></button>
                    </form>
                    <div class="auth-extra"><a href="{{ route('password.request') }}">Lupa password?</a></div>
                </div>
                <div data-register-form>
                    <h1></h1><p class="auth-subtitle">Daftar untuk mendapatkan akses ke layanan pariwisata Kabupaten Jember</p>
                    <form action="{{ route('register.store') }}" method="POST">@csrf
                        <div class="auth-field"><label for="register-name">Nama Lengkap</label><input id="register-name" name="nama" class="form-control" placeholder="Nama lengkap" value="{{ old('nama') }}" required></div>
                        <div class="auth-field"><label for="register-email">Email</label><input id="register-email" name="email" type="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required></div>
                        <div class="auth-field"><label for="register-password">Password</label><input id="register-password" name="password" type="password" class="form-control" placeholder="Minimal 8 karakter" required></div>
                        <button class="auth-submit" type="submit"><span>Daftar</span><i class="bi bi-arrow-right"></i></button>
                    </form>
                </div>
                <p class="auth-switch-row" data-login-switch>Belum memiliki akun? <button class="auth-switch" type="button" data-switch="register">Daftar</button></p>
                <p class="auth-switch-row" data-register-switch>Sudah memiliki akun? <button class="auth-switch" type="button" data-switch="login">Masuk</button></p>
            </div>
        </div>
        <div class="auth-panel auth-photo-panel" aria-label="Pemandangan wisata Kabupaten Jember">
            <span class="auth-photo-badge"><i class="bi bi-stars"></i> Wonderful Jember</span>
            <div class="auth-photo-content"><span class="auth-location"><i class="bi bi-geo-alt-fill"></i> Kabupaten Jember, Jawa Timur</span><h2>Jelajahi pesona Jember.</h2><p>Pantai, perbukitan hijau, dan pengalaman wisata yang menunggu untuk ditemukan.</p></div>
        </div>
    </section>
</main>
<script>
    const authShell = document.getElementById('authShell');
    const loginForm = document.querySelector('[data-login-form]');
    const registerForm = document.querySelector('[data-register-form]');
    const loginTitle = document.querySelector('[data-login-title]');
    const loginSubtitle = document.querySelector('[data-login-subtitle]');
    const loginSwitch = document.querySelector('[data-login-switch]');
    const registerSwitch = document.querySelector('[data-register-switch]');
    const setMode = mode => {
        const register = mode === 'register';
        authShell.classList.toggle('is-register', register);
        loginForm.hidden = register;
        registerForm.hidden = !register;
        loginTitle.hidden = register;
        loginSubtitle.hidden = register;
        loginSwitch.hidden = register;
        registerSwitch.hidden = !register;
        history.replaceState(null, '', register ? '{{ route('register') }}' : '{{ route('login') }}');
    };
    document.querySelectorAll('[data-switch]').forEach(button => button.addEventListener('click', () => setMode(button.dataset.switch)));
    document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => { const input = document.getElementById(button.dataset.target); const visible = input.type === 'text'; input.type = visible ? 'password' : 'text'; button.innerHTML = `<i class="bi bi-eye${visible ? '' : '-slash'}"></i>`; }));
    setMode('{{ $authMode }}');
</script>
</body>
</html>