<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\Artikel;
use App\Models\Customer;
use App\Models\DestinasiWisata;
use App\Models\Fasilitas;
use App\Models\GaleriDestinasi;
use App\Models\Pemesanan;
use App\Support\StatusLabel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuperAdminManagementController extends Controller
{
    public function index(): View
    {
        return view('superadmin.management', [
            'destinations' => DestinasiWisata::orderBy('nama_wisata')->get(),
            'admins' => AdminPariwisata::with('destinasi')->latest('id_admin')->get(),
            'customers' => Customer::latest('id_customer')->paginate(10),
            'articles' => Artikel::latest('tanggal_publikasi')->get(),
            'facilities' => Fasilitas::with('destinasi')->latest('id_fasilitas')->get(),
            'galleries' => GaleriDestinasi::with('destinasi')->latest('id_galeri')->get(),
        ]);
    }

    public function createAdmin(): View
    {
        return view('superadmin.admin-create', [
            'destinations' => DestinasiWisata::orderBy('nama_wisata')->get(),
            'existingAdmins' => AdminPariwisata::query()->get(['nama', 'email', 'no_hp']),
        ]);
    }

    public function admins(): View
    {
        return view('superadmin.admins', [
            'admins' => AdminPariwisata::with('destinasi')->latest('id_admin')->paginate(15),
        ]);
    }

    public function customers(): View
    {
        return view('superadmin.customers', [
            'customers' => Customer::latest('id_customer')->paginate(15),
        ]);
    }

    public function createArticle(): View
    {
        return view('superadmin.article-create');
    }

    public function articles(): View
    {
        return view('superadmin.articles', [
            'articles' => Artikel::latest('created_at')->get(),
        ]);
    }

    public function editArticle(int $id): View
    {
        return view('superadmin.article-edit', ['article' => Artikel::findOrFail($id)]);
    }

    public function facility(Request $request): RedirectResponse
    {
        Fasilitas::create($request->validate(['id_destinasi' => ['required', 'exists:destinasi_wisata,id_destinasi'], 'nama_fasilitas' => ['required', 'string', 'max:100']]));

        return back()->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function gallery(Request $request): RedirectResponse
    {
        GaleriDestinasi::create($request->validate(['id_destinasi' => ['required', 'exists:destinasi_wisata,id_destinasi'], 'url_foto' => ['required', 'url', 'max:500'], 'keterangan' => ['nullable', 'string', 'max:200']]));

        return back()->with('success', 'Galeri berhasil ditambahkan.');
    }

    public function destroyFacility(int $id): RedirectResponse
    {
        Fasilitas::findOrFail($id)->delete();

        return back()->with('success', 'Fasilitas berhasil dihapus.');
    }

    public function destroyGallery(int $id): RedirectResponse
    {
        GaleriDestinasi::findOrFail($id)->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }

    public function destroyArticle(int $id): RedirectResponse
    {
        Artikel::findOrFail($id)->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    public function destroyAdmin(int $id): RedirectResponse
    {
        AdminPariwisata::findOrFail($id)->delete();

        return back()->with('success', 'Akun admin berhasil dihapus.');
    }

    public function article(Request $request): RedirectResponse
    {
        $data = $request->validate(['judul' => ['required', 'string', 'max:200'], 'isi' => ['required', 'string'], 'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'status' => ['required', 'in:DRAFT,PUBLISHED']]);
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('articles', 'public');
            $data['gambar'] = '/storage/'.ltrim($path, '/');
        } else {
            $data['gambar'] = null;
        }
        Artikel::create([...$data, 'id_superadmin' => session('jg_user_id'), 'tanggal_publikasi' => $data['status'] === 'PUBLISHED' ? now() : null]);

        return back()->with('success', 'Artikel berhasil disimpan.');
    }

    public function updateArticle(Request $request, int $id): RedirectResponse
    {
        $article = Artikel::findOrFail($id);
        $data = $request->validate(['judul' => ['required', 'string', 'max:200'], 'isi' => ['required', 'string'], 'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'status' => ['required', 'in:DRAFT,PUBLISHED']]);
        unset($data['gambar']);

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('articles', 'public');
            $data['gambar'] = '/storage/'.ltrim($path, '/');
        }

        $data['tanggal_publikasi'] = $data['status'] === 'PUBLISHED' ? ($article->tanggal_publikasi ?? now()) : null;
        $article->update($data);

        return redirect()->route('superadmin.articles')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function admin(Request $request): RedirectResponse
    {
        $data = $request->validate(['id_destinasi' => ['required', 'exists:destinasi_wisata,id_destinasi'], 'nama' => ['required', 'string', 'max:150', 'unique:admin_pariwisata,nama'], 'email' => ['required', 'email', 'regex:/@gmail\.com$/i', 'unique:admin_pariwisata,email'], 'no_hp' => ['required', 'regex:/^[0-9]+$/', 'unique:admin_pariwisata,no_hp', 'min:10', 'max:12'], 'password' => ['required', 'string', 'min:8']], ['nama.unique' => 'Nama tersebut sudah digunakan oleh admin lain.', 'email.regex' => 'Email admin harus menggunakan @gmail.com.', 'email.unique' => 'Email tersebut sudah digunakan oleh admin lain.', 'no_hp.regex' => 'Nomor HP hanya boleh berisi angka.', 'no_hp.unique' => 'Nomor HP tersebut sudah digunakan oleh admin pariwisata lain.', 'no_hp.min' => 'Nomor HP minimal 10 angka.', 'no_hp.max' => 'Nomor HP maksimal 12 angka.']);
        $destination = DestinasiWisata::findOrFail($data['id_destinasi']);
        $adminName = preg_replace('/^admin\s+/iu', '', trim($data['nama']));
        $normalizeName = static fn (string $name): string => mb_strtolower((string) preg_replace('/\s+/u', ' ', trim($name)));
        if ($normalizeName($adminName) !== $normalizeName($destination->nama_wisata)) {
            return back()->withInput()->withErrors(['id_destinasi' => 'Destinasi tugas harus sesuai dengan nama admin. Contoh: Admin Teluk Love memilih destinasi Teluk Love.']);
        }
        AdminPariwisata::create([...$data, 'password' => Hash::make($data['password']), 'status_akun' => $destination->status_aktif === 'aktif' ? 'AKTIF' : 'NONAKTIF']);

        return back()->with('success', 'Akun admin pariwisata berhasil dibuat.');
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = $this->reportOrders($request);
        $filename = 'laporan-jembergo-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Booking', 'Destinasi', 'Tanggal', 'Status Tiket', 'Status Pembayaran', 'Total']);
            foreach ($orders as $order) {
                $ticketStatus = StatusLabel::ticketStatus($order->tiket->pluck('status_tiket'), $order->status_pemesanan);
                fputcsv($handle, [$order->kode_booking, $order->destinasi->nama_wisata, $order->tanggal_pemesanan->format('Y-m-d H:i'), StatusLabel::ticket($ticketStatus), StatusLabel::order($order->status_pemesanan), $order->total_harga]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function reportPreview(Request $request): View
    {
        $orders = $this->reportOrders($request);

        return view('superadmin.report-print', [
            'orders' => $orders,
            'totalPendapatan' => $orders->sum('total_harga'),
            'destination' => $this->reportDestination($request),
            'from' => $request->date('from'),
            'to' => $request->date('to'),
        ]);
    }

    public function reportPdf(Request $request): mixed
    {
        $orders = $this->reportOrders($request);
        $pdf = Pdf::loadView('superadmin.report-pdf', [
            'orders' => $orders,
            'totalPendapatan' => $orders->sum('total_harga'),
            'destination' => $this->reportDestination($request),
            'from' => $request->date('from'),
            'to' => $request->date('to'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-jembergo-'.now()->format('Ymd-His').'.pdf');
    }

    private function reportOrders(Request $request): Collection
    {
        return Pemesanan::with(['destinasi', 'tiket'])
            ->when($request->integer('id_destinasi'), fn ($query, $destinationId) => $query->where('id_destinasi', $destinationId))
            ->when($request->date('from'), fn ($query, $from) => $query->whereDate('tanggal_pemesanan', '>=', $from))
            ->when($request->date('to'), fn ($query, $to) => $query->whereDate('tanggal_pemesanan', '<=', $to))
            ->latest('tanggal_pemesanan')
            ->get();
    }

    private function reportDestination(Request $request): ?DestinasiWisata
    {
        return $request->integer('id_destinasi')
            ? DestinasiWisata::find($request->integer('id_destinasi'))
            : null;
    }
}
