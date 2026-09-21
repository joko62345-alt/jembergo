<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinasi_wisata', function (Blueprint $table) {
            $table->string('status_aktif')->default('aktif')->change();
            $table->string('kuota_harian_aktif')->default('nonaktif')->change();
        });

        DB::table('destinasi_wisata')->update([
            'status_aktif' => DB::raw("CASE WHEN status_aktif IN ('1', 'aktif') THEN 'aktif' ELSE 'nonaktif' END"),
            'kuota_harian_aktif' => DB::raw("CASE WHEN kuota_harian_aktif IN ('1', 'aktif') THEN 'aktif' ELSE 'nonaktif' END"),
        ]);

        Schema::table('destinasi_wisata', function (Blueprint $table) {
            $table->enum('status_aktif', ['aktif', 'nonaktif'])->default('aktif')->change();
            $table->enum('kuota_harian_aktif', ['aktif', 'nonaktif'])->default('nonaktif')->change();
        });
    }

    public function down(): void
    {
        DB::table('destinasi_wisata')->update([
            'status_aktif' => DB::raw("CASE WHEN status_aktif = 'aktif' THEN 1 ELSE 0 END"),
            'kuota_harian_aktif' => DB::raw("CASE WHEN kuota_harian_aktif = 'aktif' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('destinasi_wisata', function (Blueprint $table) {
            $table->boolean('status_aktif')->default(true)->change();
            $table->boolean('kuota_harian_aktif')->default(false)->change();
        });
    }
};
