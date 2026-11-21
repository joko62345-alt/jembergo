<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerubahanPemesanan extends Model
{
    use HasFactory;

    protected $table = 'perubahan_pemesanan';

    protected $primaryKey = 'id_perubahan';

    protected $fillable = ['id_pemesanan', 'anggota_baru', 'nominal', 'status'];

    protected $casts = ['anggota_baru' => 'array', 'nominal' => 'decimal:2'];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_perubahan');
    }
}
