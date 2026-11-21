<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->index(
                ['id_destinasi', 'tanggal_pemesanan'],
                'pemesanan_destination_order_date_index',
            );
            $table->index(
                ['id_destinasi', 'tanggal_kunjungan', 'status_pemesanan'],
                'pemesanan_destination_visit_status_index',
            );
            $table->index(
                ['status_pemesanan', 'batas_waktu_pembayaran'],
                'pemesanan_status_payment_deadline_index',
            );
        });

        Schema::table('tiket', function (Blueprint $table): void {
            $table->index(
                ['status_tiket', 'id_pemesanan'],
                'tiket_status_booking_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('tiket', function (Blueprint $table): void {
            $table->dropIndex('tiket_status_booking_index');
        });

        Schema::table('pemesanan', function (Blueprint $table): void {
            $table->dropIndex('pemesanan_destination_order_date_index');
            $table->dropIndex('pemesanan_destination_visit_status_index');
            $table->dropIndex('pemesanan_status_payment_deadline_index');
        });
    }
};
