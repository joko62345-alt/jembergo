<?php

namespace App\Http\Controllers;

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
        $data['status_aktif'] = $request->boolean('status_aktif');
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
        $data['status_aktif'] = $request->boolean('status_aktif');
        $destination->update($data);
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

    public function report(Request $request): View
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $destinationId = $request->integer('id_destinasi') ?: null;
        $destinations = DestinasiWisata::orderBy('nama_wisata')->get(['id_destinasi', 'nama_wisata']);
        $orders = Schema::hasTable('pemesanan')
            ? Pemesanan::with(['destinasi', 'tiket'])->when($destinationId, fn ($query) => $query->where('id_destinasi', $destinationId))->when($from, fn ($query) => $query->where('tanggal_pemesanan', '>=', $from))->when($to, fn ($query) => $query->where('tanggal_pemesanan', '<=', $to))->latest('tanggal_pemesanan')->paginate(20)->withQueryString()
            : collect();

        return view('superadmin.report', compact('orders', 'from', 'to', 'destinationId', 'destinations'));
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
                    GaleriDestinasi::create(['id_destinasi' => $destination->id_destinasi, 'url_foto' => '/storage/' . ltrim($path, '/'), 'keterangan' => $request->input("galeri.$index.keterangan")]);
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
            'jenis_tiket.*.harga' => ['required', 'numeric', 'min:0'],
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
            $destination->update(['foto_utama' => '/storage/' . ltrim($path, '/')]);
        }
    }
}