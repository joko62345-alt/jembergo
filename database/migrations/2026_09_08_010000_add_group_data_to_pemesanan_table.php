<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->string('ketua_nama')->nullable()->after('id_customer');
            $table->string('ketua_email')->nullable()->after('ketua_nama');
            $table->string('ketua_no_hp', 30)->nullable()->after('ketua_email');
            $table->json('anggota_names')->nullable()->after('ketua_no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropColumn(['ketua_nama', 'ketua_email', 'ketua_no_hp', 'anggota_names']);
        });
    }
};