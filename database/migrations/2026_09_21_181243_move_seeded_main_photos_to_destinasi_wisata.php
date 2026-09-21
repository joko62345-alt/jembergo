<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $mainPhotos = DB::table('galeri_destinasi')
                ->where('keterangan', 'like', 'Pemandangan utama %')
                ->get(['id_galeri', 'id_destinasi', 'url_foto']);

            foreach ($mainPhotos as $mainPhoto) {
                DB::table('destinasi_wisata')
                    ->where('id_destinasi', $mainPhoto->id_destinasi)
                    ->whereNull('foto_utama')
                    ->update(['foto_utama' => $mainPhoto->url_foto]);

                DB::table('galeri_destinasi')->where('id_galeri', $mainPhoto->id_galeri)->delete();
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $destinations = DB::table('destinasi_wisata')
                ->where('foto_utama', 'like', 'images/destinations/%')
                ->get(['id_destinasi', 'nama_wisata', 'foto_utama']);

            foreach ($destinations as $destination) {
                DB::table('galeri_destinasi')->insert([
                    'id_destinasi' => $destination->id_destinasi,
                    'url_foto' => $destination->foto_utama,
                    'keterangan' => 'Pemandangan utama '.$destination->nama_wisata,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('destinasi_wisata')
                    ->where('id_destinasi', $destination->id_destinasi)
                    ->update(['foto_utama' => null]);
            }
        });
    }
};
