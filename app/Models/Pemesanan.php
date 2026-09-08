<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    protected $fillable = ['id_customer', 'ketua_nama', 'ketua_email', 'ketua_no_hp', 'ketua_jenis_tiket', 'anggota_names', 'id_destinasi', 'kode_booking', 'tanggal_pemesanan', 'tanggal_kunjungan', 'total_harga', 'status_pemesanan', 'batas_waktu_pembayaran'];
    protected $casts = ['anggota_names' => 'array', 'tanggal_pemesanan' => 'datetime', 'tanggal_kunjungan' => 'date', 'total_harga' => 'decimal:2', 'batas_waktu_pembayaran' => 'datetime'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'id_customer'); }
    public function destinasi(): BelongsTo { return $this->belongsTo(DestinasiWisata::class, 'id_destinasi'); }
    public function detailPemesanan(): HasMany { return $this->hasMany(DetailPemesanan::class, 'id_pemesanan'); }
    public function tiket(): HasMany { return $this->hasMany(Tiket::class, 'id_pemesanan'); }
    public function pembayaran(): HasOne { return $this->hasOne(Pembayaran::class, 'id_pemesanan'); }
}