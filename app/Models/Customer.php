<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';

    protected $primaryKey = 'id_customer';

    protected $fillable = ['nama', 'email', 'no_hp', 'password', 'auth_provider'];

    protected $hidden = ['password'];

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class, 'id_customer');
    }

    public function review(): HasMany
    {
        return $this->hasMany(Review::class, 'id_customer');
    }
}
