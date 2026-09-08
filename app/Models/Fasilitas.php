<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'fasilitas';
    protected $primaryKey = 'id_fasilitas';
    protected $fillable = ['id_destinasi', 'nama_fasilitas'];

    public function destinasi(): BelongsTo { return $this->belongsTo(DestinasiWisata::class, 'id_destinasi'); }
}