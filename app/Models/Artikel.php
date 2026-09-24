<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikel';

    protected $primaryKey = 'id_artikel';

    protected $fillable = ['id_superadmin', 'judul', 'isi', 'gambar', 'tanggal_publikasi', 'status'];

    protected $casts = ['tanggal_publikasi' => 'datetime'];

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'id_superadmin');
    }
}
