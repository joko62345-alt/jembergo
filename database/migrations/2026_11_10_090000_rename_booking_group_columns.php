<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['ketua_jenis_tiket']);
            $table->renameColumn('ketua_jenis_tiket', 'jenis_tiket_ketua');
            $table->renameColumn('anggota_names', 'nama_anggota');
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreign('jenis_tiket_ketua')
                ->references('id_jenis_tiket')
                ->on('jenis_tiket')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['jenis_tiket_ketua']);
            $table->renameColumn('jenis_tiket_ketua', 'ketua_jenis_tiket');
            $table->renameColumn('nama_anggota', 'anggota_names');
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreign('ketua_jenis_tiket')
                ->references('id_jenis_tiket')
                ->on('jenis_tiket')
                ->restrictOnDelete();
        });
    }
};
