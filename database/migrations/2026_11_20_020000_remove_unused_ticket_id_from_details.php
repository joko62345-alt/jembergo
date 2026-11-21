<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['id_tiket']);
            $table->dropColumn('id_tiket');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pemesanan', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_tiket')->nullable();
            $table->foreign('id_tiket')
                ->references('id_tiket')
                ->on('tiket')
                ->nullOnDelete();
        });
    }
};
