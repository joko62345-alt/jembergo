<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perubahan_pemesanan', function (Blueprint $table): void {
            $table->id('id_perubahan');
            $table->foreignId('id_pemesanan')->constrained('pemesanan', 'id_pemesanan')->cascadeOnDelete();
            $table->json('anggota_baru');
            $table->decimal('nominal', 12, 2);
            $table->string('status')->default('PENDING')->index();
            $table->string('metode_pembayaran')->nullable();
            $table->string('referensi_gateway')->nullable();
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perubahan_pemesanan');
    }
};
