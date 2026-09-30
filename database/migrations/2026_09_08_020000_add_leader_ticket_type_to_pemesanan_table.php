<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreignId('ketua_jenis_tiket')->nullable()->after('ketua_no_hp')->constrained('jenis_tiket', 'id_jenis_tiket')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['ketua_jenis_tiket']);
            $table->dropColumn('ketua_jenis_tiket');
        });
    }
};
