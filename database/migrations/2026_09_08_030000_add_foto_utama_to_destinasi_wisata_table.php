<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinasi_wisata', function (Blueprint $table): void {
            $table->string('foto_utama')->nullable()->after('nama_wisata');
        });
    }

    public function down(): void
    {
        Schema::table('destinasi_wisata', function (Blueprint $table): void {
            $table->dropColumn('foto_utama');
        });
    }
};