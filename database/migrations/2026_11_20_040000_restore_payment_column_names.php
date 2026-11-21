<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMN_RENAMES = [
        'id_pesanan_penyedia_pembayaran' => 'order_id',
        'token_snap' => 'snap_token',
        'id_transaksi_penyedia_pembayaran' => 'transaction_id',
        'jumlah_bruto' => 'gross_amount',
        'waktu_dibayar' => 'paid_at',
        'referensi_penyedia_pembayaran' => 'referensi_gateway',
        'url_qris' => 'qris_url',
        'waktu_kedaluwarsa_qris' => 'qris_expires_at',
        'status_transaksi' => 'transaction_status',
        'jenis_pembayaran' => 'payment_type',
    ];

    public function up(): void
    {
        $this->renameColumns(self::COLUMN_RENAMES);
    }

    public function down(): void
    {
        $this->renameColumns(array_flip(self::COLUMN_RENAMES));
    }

    private function renameColumns(array $renames): void
    {
        foreach ($renames as $oldName => $newName) {
            if (Schema::hasColumn('pembayaran', $oldName) && ! Schema::hasColumn('pembayaran', $newName)) {
                Schema::table('pembayaran', function (Blueprint $table) use ($oldName, $newName): void {
                    $table->renameColumn($oldName, $newName);
                });
            }
        }
    }
};
