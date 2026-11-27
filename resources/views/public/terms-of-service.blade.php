@extends('layouts.app')

@section('title', 'Persyaratan Layanan - JemberGo')

@section('content')
    @include('components.public-navbar')

    <style>
        .legal-page-main {
            padding-top: 2.5rem;
            padding-bottom: 4rem;
            background: linear-gradient(180deg, #f7fbff 0, #fff 18rem);
        }

        .legal-document {
            padding: clamp(1.25rem, 4vw, 3rem);
            border: 1px solid var(--jg-border, #e2e8f0);
            border-radius: 1.25rem;
            background: #fff;
            box-shadow: 0 8px 24px rgba(31, 41, 55, .045);
        }

        .legal-document h2 {
            margin-top: 2rem;
            margin-bottom: .75rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .legal-document p,
        .legal-document li {
            line-height: 1.75;
        }

        .legal-document li + li {
            margin-top: .4rem;
        }
    </style>

    <main class="legal-page-main">
        <div class="container">
            <header class="page-intro">
                <span class="eyebrow text-orange">Informasi Pengguna</span>
                <h1 class="display-5 fw-bold mt-2">Persyaratan Layanan</h1>
                <p class="text-secondary">Terakhir diperbarui: 27 November 2026</p>
            </header>

            <article class="legal-document">
                <p>
                    Persyaratan ini mengatur penggunaan situs dan layanan JemberGo yang dikelola
                    oleh Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember
                    (“Pengelola JemberGo”). Dengan mengakses atau menggunakan layanan, Anda
                    menyetujui persyaratan ini. Jika tidak setuju, mohon tidak menggunakan fitur
                    yang bersangkutan.
                </p>

                <h2>Layanan JemberGo</h2>
                <p>
                    JemberGo menyediakan informasi pariwisata Kabupaten Jember dan fasilitas
                    pemesanan tiket destinasi yang tersedia di dalam layanan. Informasi
                    ketersediaan, jam operasional, kuota, harga, dan ketentuan kunjungan dapat
                    berubah mengikuti informasi dari pengelola destinasi.
                </p>

                <h2>Akun dan keamanan</h2>
                <ul>
                    <li>Berikan informasi akun yang benar, terbaru, dan milik Anda atau yang Anda berwenang gunakan.</li>
                    <li>Jaga kerahasiaan kredensial dan kode verifikasi; jangan membagikannya kepada orang lain.</li>
                    <li>Anda bertanggung jawab atas aktivitas yang dilakukan melalui akun Anda dan harus segera menghubungi kami jika menduga akun disalahgunakan.</li>
                    <li>Masuk dengan Google mengikuti proses autentikasi Google dan ketersediaan layanannya.</li>
                </ul>

                <h2>Pemesanan dan pembayaran</h2>
                <p>
                    Periksa destinasi, tanggal kunjungan, jenis tiket, jumlah peserta, dan rincian
                    harga sebelum menyelesaikan pemesanan. Pesanan dan tiket berlaku berdasarkan
                    status yang ditampilkan JemberGo serta ketentuan pengelola destinasi.
                    Pembayaran diproses melalui metode atau penyedia pembayaran yang tersedia;
                    ketentuan tambahan dari penyedia tersebut juga berlaku.
                </p>
                <p>
                    Tiket yang telah dipesan tidak dapat dibatalkan atau diminta pengembalian
                    dana, sesuai informasi pemesanan JemberGo. Permintaan perubahan tanggal
                    (reschedule) bergantung pada ketersediaan dan dapat dikenakan biaya. Ketentuan
                    khusus yang ditampilkan saat pemesanan tetap berlaku.
                </p>

                <h2>Penggunaan yang diperbolehkan</h2>
                <p>Anda setuju untuk tidak:</p>
                <ul>
                    <li>menggunakan layanan untuk tindakan yang melanggar hukum atau merugikan orang lain;</li>
                    <li>memberikan data palsu, menyalahgunakan akun, atau menggunakan tiket milik pihak lain tanpa izin;</li>
                    <li>mengganggu keamanan, ketersediaan, atau operasi situs dan layanan; atau</li>
                    <li>menyalin atau menggunakan materi layanan di luar izin dan ketentuan hukum yang berlaku.</li>
                </ul>

                <h2>Konten, ketersediaan, dan perubahan</h2>
                <p>
                    Konten JemberGo disediakan untuk membantu perencanaan perjalanan. Kami
                    berupaya menjaga informasi tetap relevan, tetapi perubahan kondisi destinasi,
                    gangguan teknis, atau informasi dari pihak lain dapat memengaruhi
                    ketersediaan maupun ketepatannya. Kami dapat memperbarui atau menghentikan
                    bagian layanan apabila diperlukan, dengan tetap memperhatikan hak dan
                    transaksi pengguna yang telah berjalan.
                </p>

                <h2>Hukum yang berlaku dan kontak</h2>
                <p>
                    Persyaratan ini ditafsirkan berdasarkan hukum yang berlaku di Republik
                    Indonesia. Jika ada pertanyaan atau kendala terkait layanan, hubungi
                    Pengelola JemberGo melalui
                    <a href="mailto:kpcjoko@gmail.com">kpcjoko@gmail.com</a>.
                </p>
                <p class="mb-0">
                    Persyaratan ini dapat diperbarui dari waktu ke waktu. Versi yang berlaku
                    adalah versi terbaru yang dipublikasikan di halaman ini.
                </p>
            </article>
        </div>
    </main>

    <x-public-footer />
@endsection
