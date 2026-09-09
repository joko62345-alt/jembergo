@extends('layouts.app')

@section('title', 'Masuk - JemberGo')

@section('content')
<main class="auth-page d-flex align-items-center min-vh-100 p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="auth-card bg-white">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                <span class="brand-mark" style="width: 48px; height: 48px; font-size: 1.5rem;"><i class="bi bi-compass"></i></span>
                                <span class="fs-3 fw-bold text-dark">Jember<span class="text-orange">Go</span></span>
                            </a>
                            <p class="text-muted mt-2 mb-0">Masuk untuk melanjutkan petualanganmu</p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('login.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="login-password">Kata Sandi</label>
                                <div class="input-group">
                                    <input id="login-password" type="password" name="password" class="form-control" placeholder="••••••••" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle" data-target="login-password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-jg-primary w-100 btn-lg mb-3">Masuk</button>
                            
                            <div class="text-center">
                                <a href="{{ route('password.request') }}" class="text-muted small text-decoration-none">Lupa kata sandi?</a>
                            </div>
                        </form>

                        <hr class="my-4">
                        <div class="text-center">
                            <p class="text-muted small mb-0">Belum punya akun? <a href="{{ route('register') }}" class="text-orange fw-semibold text-decoration-none">Daftar sekarang</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        button.innerHTML = `<i class="bi bi-eye${visible ? '' : '-slash'}"></i>`;
    }));
</script>
@endsection