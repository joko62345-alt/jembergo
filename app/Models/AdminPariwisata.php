<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminPariwisata extends Model
{
    use HasFactory;

    protected $table = 'admin_pariwisata';

    protected $primaryKey = 'id_admin';

    protected $fillable = ['id_destinasi', 'nama', 'email', 'no_hp', 'password', 'status_akun'];

    protected $hidden = ['password'];

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(DestinasiWisata::class, 'id_destinasi');
    }
}
