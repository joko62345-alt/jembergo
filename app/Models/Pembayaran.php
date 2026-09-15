<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    protected $fillable = ['id_pemesanan', 'order_id', 'snap_token', 'transaction_id', 'metode_pembayaran', 'nominal', 'gross_amount', 'waktu_pembayaran', 'paid_at', 'referensi_gateway', 'qris_url', 'qris_expires_at', 'status_pembayaran', 'transaction_status', 'payment_type'];

    protected $casts = ['nominal' => 'decimal:2', 'gross_amount' => 'decimal:2', 'waktu_pembayaran' => 'datetime', 'paid_at' => 'datetime', 'qris_expires_at' => 'datetime'];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
