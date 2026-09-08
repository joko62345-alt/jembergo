<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\DestinasiWisata;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $hasDestinationTable = Schema::hasTable('destinasi_wisata');
        $hasArticleTable = Schema::hasTable('artikel');

        return view('welcome', [
            'destinations' => $hasDestinationTable
                ? DestinasiWisata::query()->with(['galeri', 'jenisTiket'])->where('status_aktif', true)->latest('id_destinasi')->take(5)->get()
                : collect(),
            'articles' => $hasArticleTable
                ? Artikel::query()->where('status', 'PUBLISHED')->latest('tanggal_publikasi')->take(3)->get()
                : collect(),
        ]);
    }

    public function destinations(): View
    {
        return view('public.destinations', [
            'destinations' => Schema::hasTable('destinasi_wisata')
                ? DestinasiWisata::query()->with(['jenisTiket', 'galeri'])->where('status_aktif', true)->latest('id_destinasi')->paginate(9)
                : collect(),
        ]);
    }

    public function destination(int $id): View
    {
        abort_unless(Schema::hasTable('destinasi_wisata'), 404);

        return view('public.destination-detail', [
            'destination' => DestinasiWisata::query()->with(['fasilitas', 'galeri', 'jenisTiket', 'review.customer'])->findOrFail($id),
        ]);
    }

    public function articles(): View
    {
        return view('public.articles', [
            'articles' => Schema::hasTable('artikel') ? Artikel::where('status', 'PUBLISHED')->latest('tanggal_publikasi')->paginate(9) : collect(),
        ]);
    }

    public function article(int $id): View
    {
        abort_unless(Schema::hasTable('artikel'), 404);

        return view('public.article-detail', ['article' => Artikel::where('status', 'PUBLISHED')->findOrFail($id)]);
    }
}