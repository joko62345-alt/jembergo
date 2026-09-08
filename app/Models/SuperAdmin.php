<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuperAdmin extends Model
{
    use HasFactory;

    protected $table = 'super_admin';
    protected $primaryKey = 'id_superadmin';
    protected $fillable = ['nama', 'email', 'password'];
    protected $hidden = ['password'];

    public function destinasi(): HasMany { return $this->hasMany(DestinasiWisata::class, 'id_superadmin'); }
    public function artikel(): HasMany { return $this->hasMany(Artikel::class, 'id_superadmin'); }
}