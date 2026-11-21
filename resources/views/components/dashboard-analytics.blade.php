<section class="dashboard-analytics" aria-label="{{ $isSuperAdmin ? 'Analitik pariwisata' : 'Analitik destinasi' }}">
    <div class="analytics-card analytics-favorites">
        <div class="analytics-card-heading">
            <div>
                <span class="page-kicker">{{ $isSuperAdmin ? 'Ringkasan wisata' : 'Kinerja destinasi' }}</span>
                <h2>{{ $isSuperAdmin ? 'Destinasi favorit' : 'Jenis tiket terlaris' }}</h2>
                <p>
                    {{ $isSuperAdmin
                        ? 'Destinasi dengan jumlah tiket terbanyak.'
                        : 'Jenis tiket dengan penjualan terbanyak di '.$destinationName.'.' }}
                </p>
            </div>
            <a href="{{ $reportRoute }}" class="analytics-link">Lihat laporan
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
        </div>
        @if (count($analytics['favorite']))
            <div class="favorite-chart" role="img"
                aria-label="{{ $isSuperAdmin ? 'Grafik destinasi favorit' : 'Grafik jenis tiket terlaris' }}">
                @php($favoriteMax = max(array_column($analytics['favorite'], 'tickets')))
                @foreach ($analytics['favorite'] as $item)
                    <div class="favorite-row">
                        <span class="favorite-name" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                        <div class="favorite-track">
                            <span style="width: {{ $favoriteMax ? ($item['tickets'] / $favoriteMax) * 100 : 0 }}%"></span>
                        </div>
                        <strong>{{ number_format($item['tickets']) }}</strong>
                    </div>
                @endforeach
            </div>
        @else
            <div class="analytics-empty">
                <i class="bi bi-bar-chart" aria-hidden="true"></i>
                <strong>Belum ada data pemesanan tiket</strong>
                <span>Grafik akan muncul setelah transaksi berhasil tersedia.</span>
            </div>
        @endif
    </div>

    @if ($isSuperAdmin)
        <div class="analytics-summary-row">
            <div class="analytics-mini-card">
                <span>Total transaksi</span>
                <strong>{{ number_format($analytics['totalTransactions']) }}</strong>
                <small>Transaksi berhasil</small>
            </div>
            <div class="analytics-mini-card">
                <span>Pendapatan</span>
                <strong>{{ $analytics['totalRevenue'] ? 'Rp '.number_format($analytics['totalRevenue'], 0, ',', '.') : 'Rp 0' }}</strong>
                <small>Transaksi berhasil</small>
            </div>
            <div class="analytics-mini-card">
                <span>Destinasi terfavorit</span>
                <strong class="analytics-mini-name">{{ $analytics['favoriteName'] ?: 'Belum ada data' }}</strong>
                <small>{{ number_format($analytics['totalTickets']) }} tiket terjual</small>
            </div>
        </div>
    @endif

    <div class="analytics-line-grid">
        <article class="analytics-card line-chart-card">
            <div class="analytics-card-heading">
                <div>
                    <span class="page-kicker">Aktivitas</span>
                    <h2>Transaksi</h2>
                    <p>Perkembangan enam bulan terakhir.</p>
                </div>
                <i class="bi bi-graph-up-arrow analytics-heading-icon" aria-hidden="true"></i>
            </div>
            <div class="line-chart" data-chart="transactions"
                data-values="{{ json_encode($analytics['transactions']) }}"
                data-labels="{{ json_encode(array_column($analytics['months'], 'label')) }}"
                data-format="number">
                <svg viewBox="0 0 600 220" preserveAspectRatio="none" aria-hidden="true">
                    <polyline class="chart-area" points=""></polyline>
                    <polyline class="chart-line" points=""></polyline>
                    <g class="chart-dots"></g>
                </svg>
                <div class="chart-labels"></div>
            </div>
        </article>
        <article class="analytics-card line-chart-card">
            <div class="analytics-card-heading">
                <div>
                    <span class="page-kicker">Kinerja</span>
                    <h2>Pendapatan</h2>
                    <p>Total pendapatan transaksi berhasil.</p>
                </div>
                <i class="bi bi-wallet2 analytics-heading-icon" aria-hidden="true"></i>
            </div>
            <div class="line-chart" data-chart="revenue"
                data-values="{{ json_encode($analytics['revenue']) }}"
                data-labels="{{ json_encode(array_column($analytics['months'], 'label')) }}"
                data-format="currency">
                <svg viewBox="0 0 600 220" preserveAspectRatio="none" aria-hidden="true">
                    <polyline class="chart-area" points=""></polyline>
                    <polyline class="chart-line" points=""></polyline>
                    <g class="chart-dots"></g>
                </svg>
                <div class="chart-labels"></div>
            </div>
        </article>
    </div>
</section>

@once
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
                const step = values.length > 1 ?
                    (width - padding.left - padding.right) / (values.length - 1) : 0;
                const points = values.map((value, index) =>
                    `${padding.left + index * step},${height - padding.bottom - (value / max) * (height - padding.top - padding.bottom)}`
                ).join(' ');
                const area =
                    `${padding.left},${height - padding.bottom} ${points} ${padding.left + (values.length - 1) * step},${height - padding.bottom}`;

                chart.querySelector('.chart-line').setAttribute('points', points);
                chart.querySelector('.chart-area').setAttribute('points', area);
                chart.querySelector('.chart-dots').innerHTML = values.map((value, index) => {
                    const x = padding.left + index * step;
                    const y = height - padding.bottom - (value / max) * (height - padding.top - padding.bottom);

                    return `<circle cx="${x}" cy="${y}" r="4" tabindex="0"><title>${labels[index]}: ${formatValue(value, format)}</title></circle>`;
                }).join('');
                chart.querySelector('.chart-labels').innerHTML = labels.map((label) =>
                    `<span>${label}</span>`).join('');
            });
        })();
    </script>
@endonce
