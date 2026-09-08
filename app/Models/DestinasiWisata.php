<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DestinasiWisata extends Model
{
    use HasFactory;

    protected $table = 'destinasi_wisata';
    protected $primaryKey = 'id_destinasi';
    protected $fillable = ['id_superadmin', 'nama_wisata', 'foto_utama', 'deskripsi', 'kategori', 'latitude', 'longitude', 'alamat', 'jam_operasional', 'status_aktif'];
    protected $casts = ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'status_aktif' => 'boolean'];

    public function superAdmin(): BelongsTo { return $this->belongsTo(SuperAdmin::class, 'id_superadmin'); }
    public function fasilitas(): HasMany { return $this->hasMany(Fasilitas::class, 'id_destinasi'); }
    public function galeri(): HasMany { return $this->hasMany(GaleriDestinasi::class, 'id_destinasi'); }
    public function jenisTiket(): HasMany { return $this->hasMany(JenisTiket::class, 'id_destinasi'); }
    public function adminPariwisata(): HasMany { return $this->hasMany(AdminPariwisata::class, 'id_destinasi'); }
    public function pemesanan(): HasMany { return $this->hasMany(Pemesanan::class, 'id_destinasi'); }
    public function review(): HasMany { return $this->hasMany(Review::class, 'id_destinasi'); }
}