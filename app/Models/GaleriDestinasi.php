<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriDestinasi extends Model
{
    use HasFactory;

    protected $table = 'galeri_destinasi';

    protected $primaryKey = 'id_galeri';

    protected $fillable = ['id_destinasi', 'url_foto', 'keterangan'];

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(DestinasiWisata::class, 'id_destinasi');
    }
}
