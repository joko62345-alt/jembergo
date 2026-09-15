<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinasi_wisata', function (Blueprint $table): void {
            $table->boolean('kuota_harian_aktif')->default(false)->after('status_aktif');
            $table->unsignedInteger('kuota_harian')->nullable()->after('kuota_harian_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('destinasi_wisata', function (Blueprint $table): void {
            $table->dropColumn(['kuota_harian_aktif', 'kuota_harian']);
        });
    }
};
