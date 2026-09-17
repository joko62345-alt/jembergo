<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tiket extends Model
{
    use HasFactory;

    protected $table = 'tiket';

    protected $primaryKey = 'id_tiket';

    protected $fillable = ['id_pemesanan', 'kode_qr', 'status_tiket', 'waktu_verifikasi'];

    protected $casts = ['waktu_verifikasi' => 'datetime'];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function detailPemesanan(): HasOne
    {
        return $this->hasOne(DetailPemesanan::class, 'id_tiket');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'id_tiket');
    }
}
