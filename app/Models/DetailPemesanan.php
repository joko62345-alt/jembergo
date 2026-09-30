<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPemesanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pemesanan';

    protected $primaryKey = 'id_detail';

    protected $fillable = ['id_pemesanan', 'id_tiket', 'id_jenis_tiket', 'jumlah', 'subtotal'];

    protected $casts = ['subtotal' => 'decimal:2'];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class, 'id_tiket');
    }

    public function jenisTiket(): BelongsTo
    {
        return $this->belongsTo(JenisTiket::class, 'id_jenis_tiket');
    }
}
