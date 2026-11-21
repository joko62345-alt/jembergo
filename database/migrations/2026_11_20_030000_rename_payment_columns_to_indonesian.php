<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMN_RENAMES = [
        'order_id' => 'id_pesanan_penyedia_pembayaran',
        'snap_token' => 'token_snap',
        'transaction_id' => 'id_transaksi_penyedia_pembayaran',
        'gross_amount' => 'jumlah_bruto',
        'paid_at' => 'waktu_dibayar',
        'referensi_gateway' => 'referensi_penyedia_pembayaran',
        'qris_url' => 'url_qris',
        'qris_expires_at' => 'waktu_kedaluwarsa_qris',
        'transaction_status' => 'status_transaksi',
        'payment_type' => 'jenis_pembayaran',
    ];

    public function up(): void
    {
        foreach (self::COLUMN_RENAMES as $oldName => $newName) {
            if (Schema::hasColumn('pembayaran', $oldName) && ! Schema::hasColumn('pembayaran', $newName)) {
                Schema::table('pembayaran', function (Blueprint $table) use ($oldName, $newName): void {
                    $table->renameColumn($oldName, $newName);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::COLUMN_RENAMES, true) as $oldName => $newName) {
            if (Schema::hasColumn('pembayaran', $newName) && ! Schema::hasColumn('pembayaran', $oldName)) {
                Schema::table('pembayaran', function (Blueprint $table) use ($oldName, $newName): void {
                    $table->renameColumn($newName, $oldName);
                });
            }
        }
    }
};
