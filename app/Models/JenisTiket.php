<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisTiket extends Model
{
    use HasFactory;

    protected $table = 'jenis_tiket';
    protected $primaryKey = 'id_jenis_tiket';
    protected $fillable = ['id_destinasi', 'nama_jenis', 'harga'];
    protected $casts = ['harga' => 'decimal:2'];

    public function destinasi(): BelongsTo { return $this->belongsTo(DestinasiWisata::class, 'id_destinasi'); }
    public function detailPemesanan(): HasMany { return $this->hasMany(DetailPemesanan::class, 'id_jenis_tiket'); }
}