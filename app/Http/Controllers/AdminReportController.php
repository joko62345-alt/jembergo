<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\DestinasiWisata;
use App\Models\Pemesanan;
use App\Support\StatusLabel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $this->currentAdmin();
        $destination = $this->destinationFor($admin);
        $dateFilters = $this->validatedDateFilters($request);
        $ordersQuery = $this->ordersQuery($admin, $dateFilters);
        $paidOrdersQuery = (clone $ordersQuery)->where('status_pemesanan', 'PAID');
        $totalTransaksi = (clone $ordersQuery)->count();
        $totalNilaiTransaksi = (float) (clone $ordersQuery)->sum('total_harga');
        $totalTransaksiLunas = (clone $paidOrdersQuery)->count();
        $totalPendapatan = (float) $paidOrdersQuery->sum('total_harga');
        $orders = $ordersQuery
            ->with(['destinasi', 'tiket'])
            ->latest('tanggal_pemesanan')
            ->paginate(20)
            ->withQueryString();

        return view('admin.report', compact(
            'orders',
            'destination',
            'dateFilters',
            'totalTransaksi',
            'totalNilaiTransaksi',
            'totalTransaksiLunas',
            'totalPendapatan',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $admin = $this->currentAdmin();
        $orders = $this->reportOrders($admin, $this->validatedDateFilters($request));
        $filename = 'laporan-wisata-'.$admin->id_destinasi.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new \RuntimeException('Tidak dapat membuat file laporan CSV.');
            }

            fputcsv($handle, ['Kode Booking', 'Destinasi', 'Tanggal', 'Status Tiket', 'Status Pembayaran', 'Total']);
            foreach ($orders as $order) {
                $ticketStatus = StatusLabel::ticketStatus($order->tiket->pluck('status_tiket'), $order->status_pemesanan);
                fputcsv($handle, [
                    $order->kode_booking,
                    $order->destinasi?->nama_wisata ?? '',
                    $order->tanggal_pemesanan->format('Y-m-d H:i'),
                    StatusLabel::ticket($ticketStatus),
                    StatusLabel::order($order->status_pemesanan),
                    $order->total_harga,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function preview(Request $request): View
    {
        $admin = $this->currentAdmin();
        $reportData = $this->reportDocumentData($admin, $this->validatedDateFilters($request));

        return view('superadmin.report-print', $reportData);
    }

    public function pdf(Request $request): mixed
    {
        $admin = $this->currentAdmin();
        $reportData = $this->reportDocumentData($admin, $this->validatedDateFilters($request));
        $pdf = Pdf::loadView('superadmin.report-pdf', $reportData)->setPaper('a4', 'landscape');

        return $pdf->download('laporan-wisata-'.$admin->id_destinasi.'-'.now()->format('Ymd-His').'.pdf');
    }

    private function currentAdmin(): AdminPariwisata
    {
        return AdminPariwisata::findOrFail(session('jg_user_id'));
    }

    private function destinationFor(AdminPariwisata $admin): DestinasiWisata
    {
        return DestinasiWisata::withTrashed()->findOrFail($admin->id_destinasi);
    }

    /**
     * @return array{from: ?Carbon, to: ?Carbon}
     */
    private function validatedDateFilters(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return [
            'from' => isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null,
            'to' => isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null,
        ];
    }

    /**
     * @param  array{from: ?Carbon, to: ?Carbon}  $dateFilters
     */
    private function ordersQuery(AdminPariwisata $admin, array $dateFilters): Builder
    {
        return Pemesanan::query()
            ->where('id_destinasi', $admin->id_destinasi)
            ->when($dateFilters['from'], fn (Builder $query, Carbon $from) => $query->whereDate('tanggal_pemesanan', '>=', $from))
            ->when($dateFilters['to'], fn (Builder $query, Carbon $to) => $query->whereDate('tanggal_pemesanan', '<=', $to));
    }

    /**
     * @param  array{from: ?Carbon, to: ?Carbon}  $dateFilters
     * @return Collection<int, Pemesanan>
     */
    private function reportOrders(AdminPariwisata $admin, array $dateFilters): Collection
    {
        return $this->ordersQuery($admin, $dateFilters)
            ->with(['destinasi', 'tiket'])
            ->latest('tanggal_pemesanan')
            ->get();
    }

    /**
     * @param  array{from: ?Carbon, to: ?Carbon}  $dateFilters
     * @return array{orders: Collection<int, Pemesanan>, totalPendapatan: float, destination: DestinasiWisata, from: ?Carbon, to: ?Carbon}
     */
    private function reportDocumentData(AdminPariwisata $admin, array $dateFilters): array
    {
        $orders = $this->reportOrders($admin, $dateFilters);

        return [
            'orders' => $orders,
            'totalPendapatan' => (float) $orders->where('status_pemesanan', 'PAID')->sum('total_harga'),
            'destination' => $this->destinationFor($admin),
            'from' => $dateFilters['from'],
            'to' => $dateFilters['to'],
        ];
    }
}
