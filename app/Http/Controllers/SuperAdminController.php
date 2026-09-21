<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use App\Models\DestinasiWisata;
use App\Models\Fasilitas;
use App\Models\GaleriDestinasi;
use App\Models\JenisTiket;
use App\Models\Pemesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function destinations(): View
    {
        return view('superadmin.destinations', [
            'destinations' => DestinasiWisata::query()->with(['fasilitas', 'galeri', 'jenisTiket'])->latest('id_destinasi')->paginate(10),
        ]);
    }

    public function storeDestination(Request $request): RedirectResponse
    {
        $data = $this->validatedDestination($request);
        $data['id_superadmin'] = session('jg_user_id');
        $data['status_aktif'] = $request->boolean('status_aktif') ? 'aktif' : 'nonaktif';
        $destination = DestinasiWisata::create($data);
        $this->storeMainPhoto($request, $destination);
        $this->syncAmenities($request, $destination);
        $this->syncTicketTypes($request, $destination);

        return back()->with('success', 'Destinasi berhasil ditambahkan.');
    }

    public function updateDestination(Request $request, int $id): RedirectResponse
    {
        $destination = DestinasiWisata::findOrFail($id);
        $data = $this->validatedDestination($request);
        $data['status_aktif'] = $request->boolean('status_aktif') ? 'aktif' : 'nonaktif';
        $destination->update($data);
        AdminPariwisata::query()->where('id_destinasi', $destination->id_destinasi)->update([
            'status_akun' => $data['status_aktif'] === 'aktif' ? 'AKTIF' : 'NONAKTIF',
        ]);
        $this->storeMainPhoto($request, $destination);
        $this->syncAmenities($request, $destination);
        $this->syncTicketTypes($request, $destination);

        return back()->with('success', 'Destinasi berhasil diperbarui.');
    }

    public function destroyDestination(int $id): RedirectResponse
    {
        DestinasiWisata::findOrFail($id)->delete();

        return back()->with('success', 'Destinasi berhasil dihapus.');
    }

    public function destroyTicketType(int $destination, int $ticket): RedirectResponse
    {
        $ticketType = JenisTiket::query()
            ->where('id_destinasi', $destination)
            ->whereKey($ticket)
            ->firstOrFail();

        if ($ticketType->detailPemesanan()->exists()) {
            return back()->withErrors(['jenis_tiket' => 'Jenis tiket yang sudah dipakai dalam pemesanan tidak dapat dihapus.']);
        }

        $ticketType->delete();

        return back()->with('success', 'Jenis tiket berhasil dihapus.');
    }

    public function report(Request $request): View
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $destinationId = $request->integer('id_destinasi') ?: null;
        $destinations = DestinasiWisata::orderBy('nama_wisata')->get(['id_destinasi', 'nama_wisata']);
        $totalPendapatan = 0;

        if (Schema::hasTable('pemesanan')) {
            $ordersQuery = Pemesanan::query()
                ->when($destinationId, fn ($query) => $query->where('id_destinasi', $destinationId))
                ->when($from, fn ($query) => $query->where('tanggal_pemesanan', '>=', $from))
                ->when($to, fn ($query) => $query->where('tanggal_pemesanan', '<=', $to));
            $totalPendapatan = (clone $ordersQuery)->sum('total_harga');
            $orders = $ordersQuery->with(['destinasi', 'tiket'])->latest('tanggal_pemesanan')->paginate(20)->withQueryString();
        } else {
            $orders = collect();
        }

        return view('superadmin.report', compact('orders', 'from', 'to', 'destinationId', 'destinations', 'totalPendapatan'));
    }

    private function validatedDestination(Request $request): array
    {
        return $request->validate([
            'nama_wisata' => ['required', 'string', 'max:150'],
            'foto_utama' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'deskripsi' => ['required', 'string'],
            'kategori' => ['required', 'string', 'max:80'],
            'alamat' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'jam_operasional' => ['required', 'string', 'max:100'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);
    }

    private function syncAmenities(Request $request, DestinasiWisata $destination): void
    {
        $data = $request->validate([
            'fasilitas' => ['nullable', 'array', 'max:20'],
            'fasilitas.*' => ['nullable', 'string', 'max:100'],
            'galeri' => ['nullable', 'array', 'max:20'],
            'galeri.*.foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'galeri.*.keterangan' => ['nullable', 'string', 'max:200'],
        ]);

        if ($request->has('fasilitas')) {
            $destination->fasilitas()->delete();
            foreach (collect($data['fasilitas'] ?? [])->map(fn (?string $name) => trim((string) $name))->filter() as $name) {
                Fasilitas::create(['id_destinasi' => $destination->id_destinasi, 'nama_fasilitas' => $name]);
            }
        }

        if ($request->hasFile('galeri')) {
            $uploads = collect($request->file('galeri', []))->filter(fn ($gallery) => ! empty($gallery['foto']));

            if ($uploads->isNotEmpty()) {
                $destination->galeri()->delete();
                foreach ($uploads as $index => $gallery) {
                    $path = $gallery['foto']->store('destinations', 'public');
                    GaleriDestinasi::create(['id_destinasi' => $destination->id_destinasi, 'url_foto' => '/storage/'.ltrim($path, '/'), 'keterangan' => $request->input("galeri.$index.keterangan")]);
                }
            }
        }
    }

    private function syncTicketTypes(Request $request, DestinasiWisata $destination): void
    {
        $data = $request->validate([
            'jenis_tiket' => ['nullable', 'array', 'max:20'],
            'jenis_tiket.*.id' => ['nullable', 'integer'],
            'jenis_tiket.*.nama_jenis' => ['required', 'string', 'max:100'],
            'jenis_tiket.*.harga_display' => ['required', 'regex:/^\d[\d.]*$/'],
            'jenis_tiket.*.harga' => ['required', 'numeric', 'min:0'],
        ], [
            'jenis_tiket.*.harga_display.regex' => 'Harga tiket hanya boleh berisi angka.',
            'jenis_tiket.*.harga_display.required' => 'Harga tiket wajib diisi.',
        ]);

        foreach ($data['jenis_tiket'] ?? [] as $ticketData) {
            if (! empty($ticketData['id'])) {
                $ticket = JenisTiket::query()->where('id_destinasi', $destination->id_destinasi)->whereKey($ticketData['id'])->firstOrFail();
                $ticket->update(['nama_jenis' => $ticketData['nama_jenis'], 'harga' => $ticketData['harga']]);

                continue;
            }

            JenisTiket::create([
                'id_destinasi' => $destination->id_destinasi,
                'nama_jenis' => $ticketData['nama_jenis'],
                'harga' => $ticketData['harga'],
            ]);
        }
    }

    private function storeMainPhoto(Request $request, DestinasiWisata $destination): void
    {
        if ($request->hasFile('foto_utama')) {
            $path = $request->file('foto_utama')->store('destinations/main', 'public');
            $destination->update(['foto_utama' => '/storage/'.ltrim($path, '/')]);
        }
    }
}
