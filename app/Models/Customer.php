<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;
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
