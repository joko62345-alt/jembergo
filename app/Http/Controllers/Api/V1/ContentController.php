<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Resources\Api\V1\DestinationResource;
use App\Models\Artikel;
use App\Models\DestinasiWisata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentController extends Controller
{
    public function destinations(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:80'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);
        $query = trim((string) ($filters['q'] ?? ''));

        $destinations = DestinasiWisata::query()
            ->where('status_aktif', 'aktif')
            ->with(['jenisTiket', 'galeri'])
            ->withAvg('review', 'rating')
            ->withCount('review')
            ->when($query !== '', fn ($builder) => $builder->where(function ($search) use ($query): void {
                $search->where('nama_wisata', 'like', '%'.$query.'%')
                    ->orWhere('alamat', 'like', '%'.$query.'%')
                    ->orWhere('deskripsi', 'like', '%'.$query.'%');
            }))
            ->when(filled($filters['kategori'] ?? null), fn ($builder) => $builder->where('kategori', $filters['kategori']))
            ->latest('id_destinasi')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return DestinationResource::collection($destinations);
    }

    public function destination(int $id): DestinationResource
    {
        $destination = DestinasiWisata::query()
            ->where('status_aktif', 'aktif')
            ->with(['fasilitas', 'galeri', 'jenisTiket', 'review.customer'])
            ->withAvg('review', 'rating')
            ->withCount('review')
            ->findOrFail($id);

        return new DestinationResource($destination);
    }

    public function publicImage(string $path): StreamedResponse
    {
        abort_unless(
            preg_match('#\A(?:destinations|articles)/[A-Za-z0-9_./-]+\.(?:jpe?g|png|webp|gif)\z#i', $path) === 1
                && ! str_contains($path, '..'),
            404,
        );

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }

    public function articles(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['per_page' => ['nullable', 'integer', 'between:1,50']]);
        $articles = Artikel::query()
            ->where('status', 'PUBLISHED')
            ->latest('tanggal_publikasi')
            ->paginate((int) ($filters['per_page'] ?? 15));

        return ArticleResource::collection($articles);
    }

    public function article(int $id): ArticleResource
    {
        return new ArticleResource(Artikel::query()->where('status', 'PUBLISHED')->findOrFail($id));
    }
}
