@extends('layouts.superadmin')
@section('title', 'Arsip destinasi')
@section('page_label', 'Destinasi wisata')
@section('content')
    <div class="page-heading destination-heading">
        <div>
            <span class="page-kicker">Katalog pariwisata</span>
            <h1>Arsip destinasi</h1>
            <p>Destinasi yang dipulihkan akan aktif kembali dan dapat menerima pemesanan.</p>
        </div>
        <a href="{{ route('superadmin.destinations') }}" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke destinasi
        </a>
    </div>

    @if (session('success'))
        <div class="destination-toast" role="status"><i class="bi bi-check-circle-fill"
                aria-hidden="true"></i><span>{{ session('success') }}</span><button type="button"
                class="toast-close" data-dismiss-toast aria-label="Tutup notifikasi"><i
                    class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    @endif

    <section class="destination-grid" aria-label="Destinasi diarsipkan">
        @forelse ($archivedDestinations as $destination)
            <article class="destination-card">
                <div class="destination-card-media">
                    @if ($destination->foto_utama)
                        <img src="{{ $destination->foto_utama }}"
                            alt="Foto {{ $destination->nama_wisata }}">
                    @else
                        <div class="destination-placeholder"><i class="bi bi-image"
                                aria-hidden="true"></i><span>Belum ada foto</span></div>
                    @endif
                    <span class="destination-status is-inactive"><span
                            aria-hidden="true"></span>Diarsipkan</span>
                </div>
                <div class="destination-card-body">
                    <span class="destination-category">{{ $destination->kategori }}</span>
                    <h2>{{ $destination->nama_wisata }}</h2>
                    <p><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $destination->alamat }}</p>
                    <form method="POST"
                        action="{{ route('superadmin.destinations.restore', $destination->id_destinasi) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Pulihkan
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="destination-empty">
                <i class="bi bi-archive" aria-hidden="true"></i>
                <h2>Arsip masih kosong</h2>
                <p>Destinasi yang diarsipkan akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </section>

    <div class="mt-4">{{ $archivedDestinations->links() }}</div>
@endsection
