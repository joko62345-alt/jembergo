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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function destinations(): View
    {
        return view('superadmin.destinations', [
            'destinations' => DestinasiWisata::query()->with(['fasilitas', 'galeri', 'jenisTiket'])->latest('id_destinasi')->paginate(10),
            'destinationNames' => DestinasiWisata::query()->pluck('nama_wisata'),
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
        $data = $this->validatedDestination($request, $destination->id_destinasi);
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

    public function destroyGallery(int $destination, int $gallery): RedirectResponse
    {
        GaleriDestinasi::query()
            ->where('id_destinasi', $destination)
            ->whereKey($gallery)
            ->firstOrFail()
            ->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus.');
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

    private function validatedDestination(Request $request, ?int $destinationId = null): array
    {
        $request->merge([
            'jam_operasional' => $request->filled('jam_buka') && $request->filled('jam_tutup')
                ? $request->input('jam_buka').' - '.$request->input('jam_tutup')
                : $request->input('jam_operasional'),
        ]);

        return $request->validate([
            'nama_wisata' => [
                'required',
                'string',
                'max:150',
                'regex:/^[\p{L}\s]+$/u',
                Rule::unique('destinasi_wisata', 'nama_wisata')->ignore($destinationId, 'id_destinasi'),
            ],
            'foto_utama' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'deskripsi' => ['required', 'string'],
            'kategori' => ['required', 'string', 'max:80'],
            'alamat' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'jam_operasional' => ['required', 'string', 'max:100'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => [
                'required',
                'date_format:H:i',
                function (string $attribute, string $value, \Closure $fail) use ($request): void {
                    if ($request->input('jam_buka') === $value) {
                        $fail('Jam buka dan jam tutup tidak boleh sama.');
                    } elseif ($request->input('jam_buka') > $value) {
                        $fail('Jam tutup harus lebih besar dari jam buka.');
                    }
                },
            ],
            'status_aktif' => ['nullable', 'boolean'],
        ], [
            'nama_wisata.unique' => 'Destinasi dengan nama tersebut sudah terdaftar.',
            'nama_wisata.regex' => 'Nama destinasi hanya boleh berisi huruf dan spasi.',
        ]);
    }

    private function syncAmenities(Request $request, DestinasiWisata $destination): void
    {
        $data = $request->validate([
            'fasilitas' => ['nullable', 'array', 'max:20'],
            'fasilitas.*' => ['nullable', 'string', 'max:100', 'regex:/^[\p{L}\s]*$/u'],
            'galeri' => ['nullable', 'array', 'max:20'],
            'galeri.*.id' => ['nullable', 'integer'],
            'galeri.*.foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'galeri.*.keterangan' => ['nullable', 'string', 'max:200'],
        ], [
            'fasilitas.*.regex' => 'Fasilitas hanya boleh berisi huruf dan spasi.',
        ]);

        if ($request->has('fasilitas') || $request->boolean('fasilitas_present')) {
            $destination->fasilitas()->delete();
            foreach (collect($data['fasilitas'] ?? [])->map(fn (?string $name) => trim((string) $name))->filter() as $name) {
                Fasilitas::create(['id_destinasi' => $destination->id_destinasi, 'nama_fasilitas' => $name]);
            }
        }

        foreach ($data['galeri'] ?? [] as $index => $galleryData) {
            $galleryId = $galleryData['id'] ?? null;
            $gallery = $galleryId
                ? GaleriDestinasi::query()->where('id_destinasi', $destination->id_destinasi)->whereKey($galleryId)->firstOrFail()
                : null;
            $uploadedPhoto = $request->file("galeri.$index.foto");

            if (! $gallery && ! $uploadedPhoto) {
                continue;
            }

            $attributes = ['keterangan' => $galleryData['keterangan'] ?? null];
            if ($uploadedPhoto) {
                $path = $uploadedPhoto->store('destinations', 'public');
                $attributes['url_foto'] = '/storage/'.ltrim($path, '/');
            }

            if ($gallery) {
                $gallery->update($attributes);
            } else {
                GaleriDestinasi::create(array_merge($attributes, ['id_destinasi' => $destination->id_destinasi]));
            }
        }
    }

    private function syncTicketTypes(Request $request, DestinasiWisata $destination): void
    {
        $data = $request->validate([
            'jenis_tiket' => ['nullable', 'array', 'max:20'],
            'jenis_tiket.*.id' => ['nullable', 'integer'],
            'jenis_tiket.*.nama_jenis' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'jenis_tiket.*.harga_display' => ['required', 'regex:/^\d[\d.]*$/'],
            'jenis_tiket.*.harga' => ['required', 'numeric', 'min:0'],
        ], [
            'jenis_tiket.*.harga_display.regex' => 'Harga tiket hanya boleh berisi angka.',
            'jenis_tiket.*.harga_display.required' => 'Harga tiket wajib diisi.',
            'jenis_tiket.*.nama_jenis.regex' => 'Jenis tiket hanya boleh berisi huruf dan spasi.',
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
            $oldPhoto = $destination->foto_utama;
            $path = $request->file('foto_utama')->store('destinations/main', 'public');
            $newPhoto = '/storage/'.ltrim($path, '/');
            $destination->update(['foto_utama' => $newPhoto]);

            if ($oldPhoto) {
                GaleriDestinasi::query()
                    ->where('id_destinasi', $destination->id_destinasi)
                    ->where('url_foto', $oldPhoto)
                    ->update(['url_foto' => $newPhoto]);
            }
        }
    }
}
