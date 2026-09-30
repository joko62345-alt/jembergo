<footer class="footer site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                    <span class="brand-wordmark footer-brand-wordmark">Jember<span
                            class="brand-accent">Go</span></span>
                </a>
                <p class="mt-3">Platform informasi dan layanan pariwisata resmi Kabupaten Jember.
                    Temukan, rencanakan, dan pesan tiket wisata dengan mudah.</p>
            </div>
            <div class="col-lg-4">
                <strong>Jelajahi</strong>
                <a href="{{ route('destinations.index') }}">Destinasi Wisata</a>
                <a href="{{ route('articles.index') }}">Artikel & Tips</a>
                <a href="{{ route('home') }}#tentang">Tentang JemberGo</a>
            </div>
            <div class="col-lg-4">
                <strong>Ikuti Kami</strong>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="fs-4" aria-label="Instagram"><i
                            class="bi bi-instagram"></i></a>
                    <a href="#" class="fs-4" aria-label="Facebook"><i
                            class="bi bi-facebook"></i></a>
                    <a href="#" class="fs-4" aria-label="Twitter"><i
                            class="bi bi-twitter-x"></i></a>
                    <a href="#" class="fs-4" aria-label="YouTube"><i
                            class="bi bi-youtube"></i></a>
                </div>
                <p class="mt-3 small">Dapatkan update destinasi terbaru </p>
            </div>
        </div>
        <div class="footer-bottom mt-4">
            <span>&copy; {{ date('Y') }} JemberGo.</span>
        </div>
    </div>
</footer>
