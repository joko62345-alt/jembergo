<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['id_admin_loket']);
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->renameColumn('id_admin_loket', 'id_admin_pariwisata');
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreign('id_admin_pariwisata')
                ->references('id_admin')
                ->on('admin_pariwisata')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['id_admin_pariwisata']);
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->renameColumn('id_admin_pariwisata', 'id_admin_loket');
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreign('id_admin_loket')
                ->references('id_admin')
                ->on('admin_pariwisata')
                ->nullOnDelete();
        });
    }
};
