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
    protected $fillable = ['id_pemesanan', 'metode_pembayaran', 'nominal', 'waktu_pembayaran', 'referensi_gateway', 'status_pembayaran'];
    protected $casts = ['nominal' => 'decimal:2', 'waktu_pembayaran' => 'datetime'];

    public function pemesanan(): BelongsTo { return $this->belongsTo(Pemesanan::class, 'id_pemesanan'); }
}