@extends('layouts.superadmin')

@section('title', 'Dashboard')
@section('page_label', 'Dashboard overview')

@section('content')
    @php
        $isSuperAdmin = $role === 'SUPER_ADMIN';
        $cards = $isSuperAdmin
            ? [
                [
                    'key' => 'destinasi',
                    'label' => 'Destinasi aktif',
                    'icon' => 'bi-geo-alt',
                    'route' => route('superadmin.destinations'),
                ],
                [
                    'key' => 'admin',
                    'label' => 'Admin aktif',
                    'icon' => 'bi-person-badge',
                    'route' => route('superadmin.management.admins'),
                ],
                [
                    'key' => 'customer',
                    'label' => 'Customer terdaftar',
                    'icon' => 'bi-people',
                    'route' => route('superadmin.management.customers'),
                ],
                [
                    'key' => 'pemesanan',
                    'label' => 'Total pemesanan',
                    'icon' => 'bi-receipt',
                    'route' => route('superadmin.report'),
                ],
            ]
            : ($role === 'ADMIN_PARIWISATA'
                ? [
                    [
                        'key' => 'tiket',
                        'label' => 'Tiket terverifikasi',
                        'icon' => 'bi-qr-code-scan',
                        'route' => route('admin.verification.history'),
                    ],
                    [
                        'key' => 'pemesanan',
                        'label' => 'Pemesanan destinasi',
                        'icon' => 'bi-calendar-check',
                        'route' => route('admin.verification'),
                    ],
                ]
                : [
                    [
                        'key' => 'tiket',
                        'label' => 'Tiket digunakan',
                        'icon' => 'bi-ticket-perforated',
                        'route' => route('customer.orders'),
                    ],
                    [
                        'key' => 'pemesanan',
                        'label' => 'Total pemesanan',
                        'icon' => 'bi-bag-check',
                        'route' => route('customer.orders'),
                    ],
                ]);
    @endphp

    <div class="page-heading">
        <span class="page-kicker">Ringkasan sistem</span>
        <h1>Selamat datang, {{ session('jg_user_name') }}.</h1>
        <p>Pantau operasional pariwisata Jember dan akses pekerjaan penting dari satu ruang kerja.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <section class="row g-3 mb-4" aria-label="Statistik utama">
        @foreach ($cards as $card)
            <div class="col-sm-6 col-xl-3">
                <a href="{{ $card['route'] }}" class="text-decoration-none">
                    <article class="card stat-panel h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <span class="stat-label">{{ $card['label'] }}</span>
                                <span class="stat-icon"><i class="bi {{ $card['icon'] }}"
                                        aria-hidden="true"></i></span>
                            </div>
                            <strong
                                class="stat-value">{{ number_format($counts[$card['key']]) }}</strong>
                            <span class="small text-warning-emphasis">Buka rincian <i
                                    class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></span>
                        </div>
                    </article>
                </a>
            </div>
        @endforeach
    </section>

    @if ($isSuperAdmin)
        <section class="dashboard-analytics" aria-label="Analitik pariwisata">
            <div class="analytics-card analytics-favorites">
                <div class="analytics-card-heading">
                    <div><span class="page-kicker"></span>
                        <h2>Destinasi favorit</h2>
                        <p>Destinasi dengan jumlah tiket terbanyak.</p>
                    </div><a href="{{ route('superadmin.report') }}" class="analytics-link">Lihat
                        laporan <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                </div>
                @if (count($analytics['favorite']))
                    <div class="favorite-chart" role="img" aria-label="Grafik destinasi favorit">
                        @php($favoriteMax = max(array_column($analytics['favorite'], 'tickets')))
                        @foreach ($analytics['favorite'] as $item)
                            <div class="favorite-row"><span class="favorite-name"
                                    title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                                <div class="favorite-track"><span
                                        style="width: {{ $favoriteMax ? ($item['tickets'] / $favoriteMax) * 100 : 0 }}%"></span>
                                </div><strong>{{ number_format($item['tickets']) }}</strong>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="analytics-empty"><i class="bi bi-bar-chart"
                            aria-hidden="true"></i><strong>Belum ada data pemesanan
                            tiket</strong><span>Grafik akan muncul setelah transaksi berhasil
                            tersedia.</span></div>
                @endif
            </div>
            <div class="analytics-summary-row">
                <div class="analytics-mini-card"><span>Total
                        transaksi</span><strong>{{ number_format($analytics['totalTransactions']) }}</strong><small>Transaksi
                        berhasil</small></div>
                <div class="analytics-mini-card">
                    <span>Pendapatan</span><strong>{{ $analytics['totalRevenue'] ? 'Rp ' . number_format($analytics['totalRevenue'], 0, ',', '.') : 'Rp 0' }}</strong><small>Transaksi
                        berhasil</small>
                </div>
                <div class="analytics-mini-card"><span>Destinasi terfavorit</span><strong
                        class="analytics-mini-name">{{ $analytics['favoriteName'] ?: 'Belum ada data' }}</strong><small>{{ number_format($analytics['totalTickets']) }}
                        tiket terjual</small></div>
            </div>
            <div class="analytics-line-grid">
                <article class="analytics-card line-chart-card">
                    <div class="analytics-card-heading">
                        <div><span class="page-kicker">Aktivitas</span>
                            <h2>Transaksi</h2>
                            <p>Perkembangan enam bulan terakhir.</p>
                        </div><i class="bi bi-graph-up-arrow analytics-heading-icon"
                            aria-hidden="true"></i>
                    </div>
                    <div class="line-chart" data-chart="transactions"
                        data-values="{{ json_encode($analytics['transactions']) }}"
                        data-labels="{{ json_encode(array_column($analytics['months'], 'label')) }}"
                        data-format="number"><svg viewBox="0 0 600 220" preserveAspectRatio="none"
                            aria-hidden="true">
                            <polyline class="chart-area" points=""></polyline>
                            <polyline class="chart-line" points=""></polyline>
                            <g class="chart-dots"></g>
                        </svg>
                        <div class="chart-labels"></div>
                    </div>
                </article>
                <article class="analytics-card line-chart-card">
                    <div class="analytics-card-heading">
                        <div><span class="page-kicker">Kinerja</span>
                            <h2>Pendapatan</h2>
                            <p>Total pendapatan transaksi berhasil.</p>
                        </div><i class="bi bi-wallet2 analytics-heading-icon" aria-hidden="true"></i>
                    </div>
                    <div class="line-chart" data-chart="revenue"
                        data-values="{{ json_encode($analytics['revenue']) }}"
                        data-labels="{{ json_encode(array_column($analytics['months'], 'label')) }}"
                        data-format="currency"><svg viewBox="0 0 600 220" preserveAspectRatio="none"
                            aria-hidden="true">
                            <polyline class="chart-area" points=""></polyline>
                            <polyline class="chart-line" points=""></polyline>
                            <g class="chart-dots"></g>
                        </svg>
                        <div class="chart-labels"></div>
                    </div>
                </article>
            </div>
        </section>
    @endif

    <section class="row g-4">
        <div class="col-lg-8">
            <article class="card h-100">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="section-title mb-1">Pusat tindakan</h2>
                            <p class="section-subtitle mb-0">Akses cepat ke pekerjaan yang paling sering
                                digunakan.</p>
                        </div>
                        <span class="stat-icon"><i class="bi bi-lightning-charge"
                                aria-hidden="true"></i></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($role === 'ADMIN_PARIWISATA')
                            <a href="{{ route('admin.verification') }}"
                                class="btn btn-warning">Verifikasi tiket <i
                                    class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                            <a href="{{ route('admin.verification.history') }}"
                                class="btn btn-outline-dark">Riwayat verifikasi</a>
                        @elseif($role === 'CUSTOMER')
                            <a href="{{ route('destinations.index') }}"
                                class="btn btn-warning">Jelajahi destinasi <i
                                    class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                            <a href="{{ route('customer.orders') }}"
                                class="btn btn-outline-dark">Pesanan saya</a>
                        @else
                            <a href="{{ route('superadmin.destinations') }}"
                                class="btn btn-warning">Kelola destinasi <i
                                    class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                            <a href="{{ route('superadmin.report') }}"
                                class="btn btn-outline-dark">Lihat laporan</a>
                        @endif
                    </div>
                </div>
            </article>
        </div>
        <div class="col-lg-4">
            <article class="card h-100 border-0"
                style="background: var(--jg-navy) !important; color: white;">
                <div class="card-body p-4 p-lg-5">
                    <span class="page-kicker">JemberGo workspace</span>
                    <h2 class="h5 fw-bold text-white mt-3">Informasi wisata Jember, lebih terarah.</h2>
                    <p class="small mb-0" style="color: #b7c8da;">Kelola destinasi, pengguna, artikel,
                        dan transaksi tanpa kehilangan konteks.</p>
                </div>
            </article>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        (() => {
            const formatValue = (value, format) => format === 'currency' ?
                new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(value) :
                new Intl.NumberFormat('id-ID').format(value);
            document.querySelectorAll('.line-chart').forEach((chart) => {
                const values = JSON.parse(chart.dataset.values || '[]').map(Number);
                const labels = JSON.parse(chart.dataset.labels || '[]');
                const format = chart.dataset.format;
                if (!values.length || Math.max(...values) === 0) {
                    chart.innerHTML =
                        '<div class="analytics-empty analytics-empty-chart"><i class="bi bi-graph-up" aria-hidden="true"></i><strong>Belum ada data transaksi</strong><span>Belum ada transaksi yang dapat ditampilkan.</span></div>';
                    return;
                }
                const width = 600;
                const height = 190;
                const padding = {
                    top: 14,
                    right: 12,
                    bottom: 22,
                    left: 10
                };
                const max = Math.max(...values) || 1;
                const step = values.length > 1 ? (width - padding.left - padding.right) / (
                    values.length - 1) : 0;
                const points = values.map((value, index) =>
                    `${padding.left + index * step},${height - padding.bottom - (value / max) * (height - padding.top - padding.bottom)}`
                ).join(' ');
                const area =
                    `${padding.left},${height - padding.bottom} ${points} ${padding.left + (values.length - 1) * step},${height - padding.bottom}`;
                chart.querySelector('.chart-line').setAttribute('points', points);
                chart.querySelector('.chart-area').setAttribute('points', area);
                chart.querySelector('.chart-dots').innerHTML = values.map((value,
                    index) => {
                    const x = padding.left + index * step;
                    const y = height - padding.bottom - (value / max) * (height -
                        padding.top - padding.bottom);
                    return `<circle cx="${x}" cy="${y}" r="4" tabindex="0"><title>${labels[index]}: ${formatValue(value, format)}</title></circle>`;
                }).join('');
                chart.querySelector('.chart-labels').innerHTML = labels.map((label) =>
                    `<span>${label}</span>`).join('');
            });
        })();
    </script>
@endpush
