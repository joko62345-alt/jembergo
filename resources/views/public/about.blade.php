@extends('layouts.app')

@section('title', 'Tentang JemberGo - Informasi Pariwisata Kabupaten Jember')

@section('content')
    @include('components.public-navbar')

    <style>
        .about-page-main {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .about-section {
            padding: 3.5rem 0;
        }

        .about-section.pt-0 {
            padding-top: 3.5rem !important;
        }

        .about-service-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 260px;
            padding: 1.75rem;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 1.25rem;
            background: linear-gradient(160deg, #fff 0%, #fbfdff 100%);
            box-shadow: 0 8px 24px rgba(31, 41, 55, .045);
            transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }

        .about-service-card:hover {
            transform: translateY(-4px);
            border-color: rgba(245, 139, 5, .45);
            box-shadow: 0 16px 32px rgba(31, 41, 55, .09);
        }

        .about-service-icon {
            display: inline-grid;
            width: 3rem;
            height: 3rem;
            margin-bottom: 1.25rem;
            place-items: center;
            border: 1px solid rgba(245, 139, 5, .12);
            border-radius: 1rem;
            background: linear-gradient(145deg, #fff4e5, #fff9f1);
            color: var(--jg-orange, #f58b05);
            font-size: 1.25rem;
        }

        .about-service-card h3 {
            margin-bottom: .65rem;
            font-weight: 700;
        }

        .about-service-card p {
            margin-bottom: 1.25rem;
        }

        .about-service-link {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-top: auto;
            color: var(--jg-orange, #f58b05) !important;
            font-weight: 700 !important;
            text-decoration: none !important;
        }

        .about-service-link i {
            transition: transform 180ms ease;
        }

        .about-service-link:hover i {
            transform: translateX(4px);
        }

        .about-policy-card {
            height: 100%;
            padding: 1.5rem;
            border: 1px solid var(--jg-border, #e2e8f0);
            border-radius: 1.1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgba(31, 41, 55, .045);
        }

        .about-policy-icon {
            display: inline-grid;
            width: 2.75rem;
            height: 2.75rem;
            place-items: center;
            border-radius: .9rem;
            background: #fff4e5;
            color: var(--jg-orange, #f58b05);
            font-size: 1.15rem;
        }

        .about-policy-card h3 {
            margin-bottom: .5rem;
            font-weight: 700;
        }

        .about-policy-card p {
            line-height: 1.6;
        }

        .about-steps {
            position: relative;
            max-width: 920px;
            margin: 0 auto;
        }

        .about-steps::before {
            position: absolute;
            top: 1.4rem;
            bottom: 1.4rem;
            left: 50%;
            width: 2px;
            background: linear-gradient(var(--jg-orange, #f58b05), #fbd9ad);
            content: "";
            transform: translateX(-50%);
        }

        .about-step {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 2.8rem minmax(0, 1fr);
            column-gap: 1.5rem;
            align-items: center;
            min-height: 7rem;
            padding-bottom: 1.5rem;
        }

        .about-step-number {
            position: relative;
            z-index: 1;
            display: inline-grid;
            grid-column: 2;
            grid-row: 1;
            width: 2.8rem;
            height: 2.8rem;
            place-items: center;
            border: 4px solid #fff;
            border-radius: 50%;
            background: var(--jg-orange, #f58b05);
            box-shadow: 0 0 0 1px rgba(245, 139, 5, .25), 0 4px 12px rgba(245, 139, 5, .2);
            color: #fff;
            font-size: .9rem;
            font-weight: 700;
        }

        .about-step-content {
            grid-row: 1;
            padding: .75rem 0;
        }

        .about-step:nth-child(odd) .about-step-content {
            grid-column: 1;
            text-align: right;
        }

        .about-step:nth-child(even) .about-step-content {
            grid-column: 3;
            text-align: left;
        }

        .about-step:last-child {
            padding-bottom: 0;
        }

        .about-step-label {
            display: block;
            margin-bottom: .35rem;
            color: var(--jg-orange, #f58b05);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .about-step-content h3 {
            margin-bottom: .4rem;
            font-weight: 700;
        }

        .about-step-content p {
            margin-bottom: 0;
        }

        @media (max-width: 767.98px) {
            .about-section {
                padding: 2.5rem 0;
            }

            .about-section.pt-0 {
                padding-top: 2.5rem !important;
            }

            .about-steps {
                max-width: 620px;
                padding-left: 1.5rem;
            }

            .about-steps::before {
                left: 1.4rem;
            }

            .about-step {
                grid-template-columns: 2.8rem minmax(0, 1fr);
                column-gap: 1rem;
            }

            .about-step-number {
                grid-column: 1;
            }

            .about-step:nth-child(n) .about-step-content {
                grid-column: 2;
                text-align: left;
            }
        }

    </style>

    <main class="about-page-main">
        <section id="tentang" class="section-pad" data-reveal-section
            style="background: linear-gradient(135deg, var(--jg-sky) 0%, var(--jg-white) 100%);">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5" data-reveal-item>
                        <span class="eyebrow text-orange">Tentang Kami</span>
                        <h1 class="mt-3">Apa itu <em>JemberGo?</em></h1>
                        <p class="mt-3">JemberGo adalah platform informasi dan layanan pariwisata
                            resmi Kabupaten Jember yang membantu wisatawan menemukan, merencanakan, dan
                            memesan tiket wisata dengan mudah.</p>
                        <p>Kami menghubungkan Anda dengan destinasi terbaik di Jember, mulai dari wisata
                            alam, bahari, hingga buatan. Dengan sistem pemesanan yang terintegrasi,
                            perjalanan Anda menjadi lebih terencana dan menyenangkan.</p>
                    </div>
                    <div class="col-lg-7" data-reveal-item style="--reveal-delay: 120ms;">
                        <div class="category-grid">
                            <a href="{{ route('destinations.index', ['kategori' => 'Alam']) }}"
                                class="category-item">
                                <span class="category-icon"><i class="bi bi-tree-fill"></i></span>
                                <strong>Wisata Alam</strong>
                                <small>Nikmati ruang terbuka dan suasana alam yang menyegarkan.</small>
                                <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                            <a href="{{ route('destinations.index', ['kategori' => 'Bahari']) }}"
                                class="category-item">
                                <span class="category-icon"><i class="bi bi-water"></i></span>
                                <strong>Wisata Bahari</strong>
                                <small>Rasakan suasana pesisir dan ragam aktivitas tepi laut.</small>
                                <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                            <a href="{{ route('destinations.index', ['kategori' => 'Buatan']) }}"
                                class="category-item">
                                <span class="category-icon"><i class="bi bi-buildings-fill"></i></span>
                                <strong>Wisata Buatan</strong>
                                <small>Temukan pilihan rekreasi, edukasi, dan hiburan untuk semua.</small>
                                <span class="category-arrow"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-section pt-0" data-reveal-section>
            <div class="container">
                <div class="text-center mb-4" data-reveal-item>
                    <span class="eyebrow text-orange">Layanan JemberGo</span>
                    <h2 class="h2 fw-bold mt-2">Jelajahi layanan yang tersedia</h2>
                    <p class="mx-auto mb-0" style="max-width: 620px;">
                        Dari mencari inspirasi wisata hingga mengelola tiket perjalanan, semua bisa dilakukan di JemberGo.
                    </p>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6 col-lg-4" data-reveal-item style="--reveal-delay: 0ms;">
                        <article class="about-service-card">
                            <span class="about-service-icon" aria-hidden="true">
                                <i class="bi bi-geo-alt"></i>
                            </span>
                            <h3 class="h5">Informasi destinasi</h3>
                            <p>Jelajahi beragam wisata alam, bahari, dan buatan di Kabupaten Jember.</p>
                            <a href="{{ route('destinations.index') }}"
                                class="about-service-link">
                                Lihat destinasi <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-4" data-reveal-item style="--reveal-delay: 90ms;">
                        <article class="about-service-card">
                            <span class="about-service-icon" aria-hidden="true">
                                <i class="bi bi-journal-richtext"></i>
                            </span>
                            <h3 class="h5">Artikel &amp; tips wisata</h3>
                            <p>Dapatkan cerita dan inspirasi untuk merencanakan perjalanan Anda.</p>
                            <a href="{{ route('articles.index') }}"
                                class="about-service-link">
                                Baca artikel <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-4" data-reveal-item style="--reveal-delay: 180ms;">
                        <article class="about-service-card">
                            <span class="about-service-icon" aria-hidden="true">
                                <i class="bi bi-ticket-perforated"></i>
                            </span>
                            <h3 class="h5">Tiket wisata &amp; perjalanan</h3>
                            <p>Pesan tiket, akses e-ticket, ubah tanggal, atau tambahkan anggota perjalanan.</p>
                            <a href="{{ route('destinations.index') }}"
                                class="about-service-link">
                                Pesan tiket <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-section pt-0" data-reveal-section>
            <div class="container">
                <div class="text-center mb-4" data-reveal-item>
                    <span class="eyebrow text-orange">Cara memesan</span>
                    <h2 class="h2 fw-bold mt-2">Pesan tiket dalam beberapa langkah</h2>
                    <p class="mx-auto mb-0" style="max-width: 620px;">
                        Siapkan perjalanan Anda lewat alur pemesanan yang praktis di JemberGo.
                    </p>
                </div>
                <div class="about-steps" data-reveal-item style="--reveal-delay: 120ms;">
                    <article class="about-step">
                        <span class="about-step-number" aria-hidden="true">1</span>
                        <div class="about-step-content">
                            <span class="about-step-label">Langkah 1</span>
                            <h3 class="h5">Pilih destinasi</h3>
                            <p>Buka halaman Destinasi dan pilih tempat wisata yang ingin dikunjungi.</p>
                        </div>
                    </article>
                    <article class="about-step">
                        <span class="about-step-number" aria-hidden="true">2</span>
                        <div class="about-step-content">
                            <span class="about-step-label">Langkah 2</span>
                            <h3 class="h5">Tentukan kunjungan</h3>
                            <p>Pilih tanggal, jenis tiket, dan jumlah peserta sesuai rencana.</p>
                        </div>
                    </article>
                    <article class="about-step">
                        <span class="about-step-number" aria-hidden="true">3</span>
                        <div class="about-step-content">
                            <span class="about-step-label">Langkah 3</span>
                            <h3 class="h5">Isi data pemesan</h3>
                            <p>Masuk atau daftar, lalu lengkapi data ketua dan peserta sebelum memeriksa pesanan.</p>
                        </div>
                    </article>
                    <article class="about-step">
                        <span class="about-step-number" aria-hidden="true">4</span>
                        <div class="about-step-content">
                            <span class="about-step-label">Langkah 4</span>
                            <h3 class="h5">Bayar dan simpan tiket</h3>
                            <p>Selesaikan pembayaran dan buka tiket digital dari menu Tiket Saya.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="about-section pt-0" data-reveal-section>
            <div class="container">
                <div class="text-center mb-4" data-reveal-item>
                    <span class="eyebrow text-orange">Informasi penting</span>
                    <h2 class="h2 fw-bold mt-2">Ketentuan tiket dan perubahan perjalanan</h2>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6" data-reveal-item>
                        <article class="about-policy-card">
                            <div class="d-flex gap-3">
                                <span class="about-policy-icon flex-shrink-0" aria-hidden="true">
                                    <i class="bi bi-exclamation-circle"></i>
                                </span>
                                <div>
                                    <h3 class="h5">Tiket tidak bisa dibatalkan atau refund</h3>
                                    <p class="mb-0">Pastikan tanggal dan jumlah tiket sebelum membayar. Tiket yang sudah dipesan tidak dapat dibatalkan atau di-refund.</p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div class="col-lg-6" data-reveal-item style="--reveal-delay: 120ms;">
                        <article class="about-policy-card">
                            <div class="d-flex gap-3">
                                <span class="about-policy-icon flex-shrink-0" aria-hidden="true">
                                    <i class="bi bi-calendar2-check"></i>
                                </span>
                                <div>
                                    <h3 class="h5">Bisa reschedule dan tambah anggota</h3>
                                    <p class="mb-0">Ajukan perubahan tanggal atau penambahan anggota melalui e-ticket. Reschedule mengikuti ketersediaan, dan tiket tambahan dapat dikenakan biaya.</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <x-public-footer />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const revealItems = document.querySelectorAll(
                '[data-reveal-section], [data-reveal-item]');

            if (!('IntersectionObserver' in window)) {
                revealItems.forEach((item) => item.classList.add('is-revealed'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => entry.target.classList.toggle(
                    'is-revealed', entry.isIntersecting));
            }, {
                threshold: 0.14,
                rootMargin: '0px 0px -8% 0px'
            });

            revealItems.forEach((item) => observer.observe(item));
        });
    </script>
@endsection
