<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['id_customer']);
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreignId('id_customer')->nullable()->change();
            $table->string('kanal_penjualan')->default('ONLINE')->after('id_destinasi');
            $table->foreignId('id_admin_loket')
                ->nullable()
                ->after('kanal_penjualan')
                ->constrained('admin_pariwisata', 'id_admin')
                ->nullOnDelete();
            $table->index(
                ['id_destinasi', 'kanal_penjualan', 'tanggal_pemesanan'],
                'pemesanan_sales_channel_index'
            );
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreign('id_customer')
                ->references('id_customer')
                ->on('customer')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('pemesanan')->whereNull('id_customer')->exists()) {
            throw new RuntimeException('Cannot make id_customer required while walk-in sales exist.');
        }

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropForeign(['id_customer']);
            $table->dropForeign(['id_admin_loket']);
            $table->dropIndex('pemesanan_sales_channel_index');
            $table->dropColumn(['kanal_penjualan', 'id_admin_loket']);
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreignId('id_customer')->nullable(false)->change();
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->foreign('id_customer')
                ->references('id_customer')
                ->on('customer')
                ->restrictOnDelete();
        });
    }
};
