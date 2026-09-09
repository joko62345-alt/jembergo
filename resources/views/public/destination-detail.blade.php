<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $destination->nama_wisata }} | JemberGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link href="{{ asset('css/jembergo-fallback.css') }}" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>#map { height: 330px; border-radius: 1rem; }</style>
</head>
<body class="public-page">
<x-public-navbar />
@php($mainImage = $destination->foto_utama ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=85')
<main class="container"><div class="row g-5"><div class="col-lg-7"><div class="destination-hero-image"><img class="w-100 h-100 object-fit-cover" src="{{ $mainImage }}" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=85';" alt="{{ $destination->nama_wisata }}"></div></div><div class="col-lg-5"><span class="badge text-bg-warning rounded-pill">{{ $destination->kategori }}</span><h1 class="display-5 fw-bold mt-3">{{ $destination->nama_wisata }}</h1><p class="text-secondary">{{ $destination->alamat }}</p><p class="lead">{{ $destination->deskripsi }}</p><p class="text-secondary"><i class="bi bi-clock me-1"></i>{{ $destination->jam_operasional }} · <i class="bi bi-star-fill text-warning"></i> {{ number_format($destination->review->avg('rating') ?: 0, 1) }}</p>@if(session('jg_role') === 'CUSTOMER')<a href="{{ route('customer.booking.create', $destination->id_destinasi) }}" class="btn btn-warning rounded-pill">Pesan tiket</a>@else<a href="{{ route('login') }}" class="btn btn-warning rounded-pill">Masuk untuk memesan</a>@endif</div></div>
<div class="row g-5 mt-2"><div class="col-lg-7"><h2 class="h4 fw-bold">Lokasi & rute</h2><div id="map"></div><button id="locate" class="btn btn-outline-secondary rounded-pill mt-3">Gunakan lokasi saya</button><p id="location-status" class="small text-secondary mt-2"></p><div id="route-estimate" class="d-none row g-2 mt-2"><div class="col-6"><div class="bg-white rounded-3 p-3"><small class="text-secondary d-block">Jarak</small><strong id="route-distance">-</strong></div></div><div class="col-6"><div class="bg-white rounded-3 p-3"><small class="text-secondary d-block">Estimasi waktu</small><strong id="route-duration">-</strong></div></div></div></div><div class="col-lg-5"><h2 class="h4 fw-bold">Fasilitas</h2><div class="d-flex flex-wrap gap-2">@forelse($destination->fasilitas as $facility)<span class="badge bg-white text-dark border rounded-pill p-2">{{ $facility->nama_fasilitas }}</span>@empty<span class="text-secondary">Belum tersedia.</span>@endforelse</div><h2 class="h4 fw-bold mt-5">Jenis tiket</h2>@foreach($destination->jenisTiket as $ticket)<div class="d-flex justify-content-between border-bottom py-2"><span>{{ $ticket->nama_jenis }}</span><strong>Rp {{ number_format($ticket->harga, 0, ',', '.') }}</strong></div>@endforeach</div></div>
<section class="mt-5"><div class="d-flex justify-content-between align-items-end mb-3"><div><span class="text-warning text-uppercase small fw-bold">Jelajahi lebih dekat</span><h2 class="h4 fw-bold mt-2 mb-0">Galeri {{ $destination->nama_wisata }}</h2></div></div><div class="row g-3">@forelse($destination->galeri as $gallery)<div class="col-6 col-md-4"><figure class="bg-white rounded-4 overflow-hidden shadow-sm h-100 mb-0"><img src="{{ $gallery->url_foto }}" class="w-100" style="height:190px;object-fit:cover" alt="{{ $gallery->keterangan ?: $destination->nama_wisata }}"><figcaption class="p-3 small text-secondary">{{ $gallery->keterangan ?: 'Foto destinasi' }}</figcaption></figure></div>@empty<div class="col-12"><p class="text-secondary">Galeri destinasi belum tersedia.</p></div>@endforelse</div></section>
<section class="mt-5"><h2 class="h4 fw-bold">Ulasan pengunjung</h2>@forelse($destination->review->sortByDesc('tanggal_review')->take(5) as $review)<article class="bg-white rounded-4 p-3 mt-3"><div class="d-flex justify-content-between"><strong>{{ $review->customer->nama }}</strong><span class="text-warning">{{ str_repeat('★', $review->rating) }}</span></div><p class="mb-1 mt-2 text-secondary">{{ $review->ulasan }}</p></article>@empty<p class="text-secondary">Belum ada ulasan.</p>@endforelse</section></main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const coordinate = [{{ $destination->latitude }}, {{ $destination->longitude }}];
const map = L.map('map').setView(coordinate, 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
L.marker(coordinate).addTo(map).bindPopup(@json($destination->nama_wisata)).openPopup();
document.getElementById('locate').addEventListener('click', () => {
    const status = document.getElementById('location-status');
    const estimate = document.getElementById('route-estimate');
    const distanceOutput = document.getElementById('route-distance');
    const durationOutput = document.getElementById('route-duration');
    if (!navigator.geolocation) { status.textContent = 'Browser tidak mendukung lokasi.'; return; }
    status.textContent = 'Menghitung jarak dan estimasi waktu perjalanan...';
    navigator.geolocation.getCurrentPosition(async position => {
        const user = [position.coords.latitude, position.coords.longitude];
        L.marker(user).addTo(map).bindPopup('Lokasi saya').openPopup();
        map.fitBounds([coordinate, user], { padding: [30, 30] });
        const straightDistance = L.latLng(coordinate).distanceTo(L.latLng(user));
        let distance = straightDistance * 1.2;
        let duration = distance / 8.33;
        try {
            const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${user[1]},${user[0]};${coordinate[1]},${coordinate[0]}?overview=false`);
            const result = await response.json();
            if (result.code === 'Ok' && result.routes?.[0]) { distance = result.routes[0].distance; duration = result.routes[0].duration; }
        } catch (error) { /* Gunakan estimasi lokal jika routing tidak tersedia. */ }
        distanceOutput.textContent = `${(distance / 1000).toFixed(1).replace('.', ',')} km`;
        const minutes = Math.max(1, Math.round(duration / 60));
        durationOutput.textContent = minutes >= 60 ? `${Math.floor(minutes / 60)} jam ${minutes % 60} menit` : `${minutes} menit`;
        status.textContent = 'Rute berhasil dihitung dari lokasi Anda.';
        estimate.classList.remove('d-none');
    }, () => { status.textContent = 'Lokasi tidak dapat diakses. Anda tetap dapat melihat lokasi wisata pada peta.'; });
});
</script>
</body></html>