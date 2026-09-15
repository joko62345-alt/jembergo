<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerubahanPemesanan extends Model
{
    use HasFactory;

    protected $table = 'perubahan_pemesanan';

    protected $primaryKey = 'id_perubahan';

    protected $fillable = ['id_pemesanan', 'order_id', 'snap_token', 'transaction_id', 'anggota_baru', 'nominal', 'gross_amount', 'status', 'transaction_status', 'payment_type', 'metode_pembayaran', 'referensi_gateway', 'qris_url', 'qris_expires_at', 'waktu_pembayaran', 'paid_at'];

    protected $casts = ['anggota_baru' => 'array', 'nominal' => 'decimal:2', 'gross_amount' => 'decimal:2', 'waktu_pembayaran' => 'datetime', 'paid_at' => 'datetime', 'qris_expires_at' => 'datetime'];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
