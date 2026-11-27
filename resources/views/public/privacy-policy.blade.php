@extends('layouts.app')

@section('title', 'Kebijakan Privasi - JemberGo')

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
                <h1 class="display-5 fw-bold mt-2">Kebijakan Privasi</h1>
                <p class="text-secondary">Terakhir diperbarui: 27 November 2026</p>
            </header>

            <article class="legal-document">
                <p>
                    Dinas Kepemudaan dan Olahraga, Kebudayaan dan Pariwisata Kabupaten Jember
                    (“Pengelola JemberGo”) menghargai privasi Anda. Kebijakan ini menjelaskan
                    data yang diproses ketika Anda menggunakan situs dan layanan JemberGo,
                    termasuk fitur akun, masuk dengan Google, informasi destinasi, pemesanan
                    tiket, dan e-ticket.
                </p>

                <h2>Data yang kami proses</h2>
                <ul>
                    <li>
                        <strong>Data akun:</strong> nama, alamat email, nomor telepon, serta
                        informasi autentikasi yang diperlukan untuk membuat dan mengamankan akun.
                        Jika Anda memilih masuk dengan Google, kami menerima informasi profil
                        dasar seperti nama dan alamat email sesuai izin yang Anda berikan.
                    </li>
                    <li>
                        <strong>Data pemesanan:</strong> informasi pemesan dan peserta yang Anda
                        masukkan, destinasi dan tanggal kunjungan, jenis serta jumlah tiket, dan
                        informasi yang ditampilkan pada e-ticket.
                    </li>
                    <li>
                        <strong>Data transaksi:</strong> rincian yang diperlukan untuk membuat
                        pesanan, mencocokkan pembayaran, dan menampilkan status transaksi.
                        Informasi instrumen pembayaran diproses oleh penyedia pembayaran yang
                        Anda gunakan sesuai kebijakan penyedia tersebut.
                    </li>
                    <li>
                        <strong>Data penggunaan layanan:</strong> ulasan yang Anda kirimkan serta
                        informasi teknis dan catatan yang diperlukan untuk menjalankan,
                        memelihara, dan melindungi layanan.
                    </li>
                </ul>

                <h2>Tujuan pemrosesan</h2>
                <p>Data digunakan seperlunya untuk:</p>
                <ul>
                    <li>membuat dan mengelola akun serta memverifikasi alamat email;</li>
                    <li>menyediakan informasi destinasi dan memproses pemesanan tiket;</li>
                    <li>menerbitkan e-ticket, mengelola perubahan perjalanan, dan mencocokkan pembayaran;</li>
                    <li>mengirim kode verifikasi dan pemberitahuan terkait akun atau pesanan;</li>
                    <li>menangani pertanyaan, menjaga keamanan, mencegah penyalahgunaan, dan memenuhi kewajiban hukum.</li>
                </ul>

                <h2>Berbagi data dengan pihak lain</h2>
                <p>
                    Kami membagikan data hanya sejauh diperlukan untuk menjalankan layanan,
                    memenuhi kewajiban hukum, atau menindaklanjuti permintaan Anda. Ini dapat
                    mencakup Google saat Anda memilih masuk dengan Google, penyedia pembayaran
                    untuk memproses atau mengonfirmasi transaksi, penyedia pengiriman email untuk
                    mengirim kode verifikasi, serta pengelola destinasi terkait untuk memvalidasi
                    pemesanan atau tiket.
                </p>
                <p>
                    Setiap pihak ketiga memproses data berdasarkan layanan yang diberikannya dan
                    kebijakan privasinya sendiri. Silakan tinjau kebijakan pihak tersebut saat
                    Anda menggunakan layanannya.
                </p>

                <h2>Penyimpanan dan keamanan</h2>
                <p>
                    Kami menyimpan data selama diperlukan untuk menyediakan layanan, mengelola
                    akun dan transaksi, menyelesaikan sengketa, serta memenuhi kewajiban hukum.
                    Kami menerapkan langkah perlindungan yang wajar untuk menjaga data, tetapi
                    tidak ada metode penyimpanan atau pengiriman melalui internet yang dapat
                    dijamin sepenuhnya aman.
                </p>

                <h2>Hak dan permintaan Anda</h2>
                <p>
                    Anda dapat meminta akses, koreksi, atau penghapusan data pribadi dengan
                    menghubungi kami. Permintaan akan ditinjau dan dipenuhi sesuai ketentuan yang
                    berlaku. Data tertentu mungkin perlu tetap disimpan apabila diperlukan untuk
                    menyelesaikan transaksi atau memenuhi kewajiban hukum.
                </p>

                <h2>Perubahan kebijakan dan kontak</h2>
                <p>
                    Kami dapat memperbarui kebijakan ini apabila layanan atau ketentuan yang
                    berlaku berubah. Versi terbaru akan tersedia di halaman ini.
                </p>
                <p class="mb-0">
                    Untuk pertanyaan atau permintaan terkait privasi, hubungi Pengelola JemberGo
                    melalui
                    <a href="mailto:kpcjoko@gmail.com">kpcjoko@gmail.com</a>.
                </p>
            </article>
        </div>
    </main>

    <x-public-footer />
@endsection
