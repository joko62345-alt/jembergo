@extends('layouts.app')

@section('title', 'Data E-ticket - JemberGo')

@section('content')
    @php
        $ticketStatusClass = match ($scanData['status_tiket']) {
            'ACTIVE' => 'scan-status-active',
            'USED' => 'scan-status-used',
            default => 'scan-status-muted',
        };
    @endphp

    <main class="ticket-scan-page">
        <article class="ticket-scan-sheet">
            <header class="scan-header">
                <img src="{{ route('assets.logo') }}" alt="JemberGo" class="scan-logo">
                <div class="scan-heading">
                    <p class="scan-eyebrow">JEMBERGO · E-TICKET</p>
                    <h1>Data Pemesanan Tiket</h1>
                    <p>Ringkasan booking dan peserta perjalanan.</p>
                </div>
                <span class="scan-status {{ $ticketStatusClass }}">
                    {{ \App\Support\StatusLabel::ticket($scanData['status_tiket']) }}
                </span>
            </header>

            <section class="scan-section" aria-labelledby="booking-details-heading">
                <h2 id="booking-details-heading">Ringkasan Pemesanan</h2>
                <div class="table-responsive">
                    <table class="table scan-details-table">
                        <tbody>
                            <tr>
                                <th scope="row">Kode booking</th>
                                <td class="scan-booking-code">{{ $scanData['kode_booking'] }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Destinasi</th>
                                <td>{{ $scanData['destinasi'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Tanggal kunjungan</th>
                                <td>{{ $scanData['tanggal_kunjungan'] ? \Illuminate\Support\Carbon::parse($scanData['tanggal_kunjungan'])->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Status pembayaran</th>
                                <td>{{ \App\Support\StatusLabel::payment($scanData['status_pembayaran']) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Total pembayaran</th>
                                <td>Rp {{ number_format($scanData['total_harga'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Waktu verifikasi</th>
                                <td>{{ $scanData['waktu_verifikasi'] ? \Illuminate\Support\Carbon::parse($scanData['waktu_verifikasi'])->format('d/m/Y H:i') : 'Belum diverifikasi' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="scan-section" aria-labelledby="participants-heading">
                <div class="scan-section-heading">
                    <h2 id="participants-heading">Data Peserta</h2>
                    <span>{{ count($scanData['peserta']) }} orang</span>
                </div>
                <div class="table-responsive">
                    <table class="table scan-participants-table">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Nama peserta</th>
                                <th scope="col">Jenis tiket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($scanData['peserta'] as $participant)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $participant['nama'] }}</td>
                                    <td>{{ $participant['jenis_tiket'] ?? 'Tiket wisata' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="scan-empty">Data peserta tidak tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="scan-footer">
                Informasi kontak dan data akun tidak ditampilkan pada halaman scan ini.
            </footer>
        </article>
    </main>

    <style>
        .ticket-scan-page {
            min-height: 100vh;
            padding: 3rem 1rem;
            background: #edf2f4;
            color: #1d303b;
            font-family: "Times New Roman", Times, serif;
            font-size: 1.05rem;
        }

        .ticket-scan-sheet {
            width: min(100%, 850px);
            margin: 0 auto;
            padding: clamp(1.25rem, 5vw, 3.25rem);
            border: 1px solid #d6e0e4;
            border-top: 5px solid #176b63;
            background: #fff;
            box-shadow: 0 14px 36px rgba(24, 49, 62, .08);
        }

        .scan-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #dce4e7;
        }

        .scan-logo {
            width: 6rem;
            height: auto;
            flex: 0 0 auto;
        }

        .scan-heading {
            flex: 1 1 auto;
        }

        .scan-eyebrow {
            margin: 0 0 .35rem;
            color: #176b63;
            font-size: .82rem;
            font-weight: 700;
        }

        .scan-heading h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .scan-heading>p:last-child {
            margin: .3rem 0 0;
            color: #647782;
        }

        .scan-status {
            flex: 0 0 auto;
            padding: .35rem .7rem;
            border: 1px solid currentColor;
            font-weight: 700;
            white-space: nowrap;
        }

        .scan-status-active {
            color: #176b45;
            background: #edf8f1;
        }

        .scan-status-used {
            color: #455965;
            background: #f0f3f5;
        }

        .scan-status-muted {
            color: #9a5b11;
            background: #fff6e9;
        }

        .scan-section {
            margin-top: 1.75rem;
        }

        .scan-section h2 {
            margin: 0 0 .8rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .scan-section-heading {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
        }

        .scan-section-heading span {
            color: #647782;
        }

        .scan-details-table,
        .scan-participants-table {
            margin: 0;
            border-color: #dce4e7;
        }

        .scan-details-table th {
            width: 34%;
            background: #f3f6f7;
            font-weight: 700;
        }

        .scan-details-table td,
        .scan-details-table th,
        .scan-participants-table td,
        .scan-participants-table th {
            padding: .75rem .9rem;
            vertical-align: middle;
        }

        .scan-booking-code {
            font-weight: 700;
        }

        .scan-participants-table thead th {
            background: #176b63;
            color: #fff;
            font-weight: 700;
        }

        .scan-empty,
        .scan-footer {
            color: #647782;
        }

        .scan-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #dce4e7;
            font-size: .95rem;
        }

        @media (max-width: 600px) {
            .ticket-scan-page {
                padding: 1rem .65rem;
            }

            .ticket-scan-sheet {
                padding: 1.1rem;
            }

            .scan-header {
                flex-wrap: wrap;
                gap: .8rem;
            }

            .scan-logo {
                width: 4.5rem;
            }

            .scan-heading {
                flex-basis: calc(100% - 5.5rem);
            }

            .scan-heading h1 {
                font-size: 1.45rem;
            }

            .scan-status {
                margin-left: 5.3rem;
            }

            .scan-details-table th {
                width: 42%;
            }

            .scan-details-table td,
            .scan-details-table th,
            .scan-participants-table td,
            .scan-participants-table th {
                padding: .6rem .5rem;
            }
        }
    </style>
@endsection