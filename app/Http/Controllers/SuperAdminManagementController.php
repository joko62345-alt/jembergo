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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $data = $request->validate(['id_destinasi' => ['required', 'exists:destinasi_wisata,id_destinasi'], 'nama' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'unique:admin_pariwisata,email'], 'no_hp' => ['required', 'regex:/^[0-9]+$/', 'unique:admin_pariwisata,no_hp', 'max:30'], 'password' => ['required', 'string', 'min:8']], ['no_hp.regex' => 'Nomor HP hanya boleh berisi angka.', 'no_hp.unique' => 'Nomor HP tersebut sudah digunakan oleh admin pariwisata lain.']);
        $destination = DestinasiWisata::findOrFail($data['id_destinasi']);
        AdminPariwisata::create([...$data, 'password' => Hash::make($data['password']), 'status_akun' => $destination->status_aktif === 'aktif' ? 'AKTIF' : 'NONAKTIF']);

        return back()->with('success', 'Akun admin pariwisata berhasil dibuat.');
    }

    public function export(Request $request): StreamedResponse
    {
        $destinationId = $request->integer('id_destinasi') ?: null;
        $orders = Pemesanan::with(['destinasi', 'tiket'])->when($destinationId, fn ($query) => $query->where('id_destinasi', $destinationId))->when($request->date('from'), fn ($query, $from) => $query->whereDate('tanggal_pemesanan', '>=', $from))->when($request->date('to'), fn ($query, $to) => $query->whereDate('tanggal_pemesanan', '<=', $to))->get();
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
}
