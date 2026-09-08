<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $table = 'review';
    protected $primaryKey = 'id_review';
    protected $fillable = ['id_customer', 'id_destinasi', 'id_tiket', 'rating', 'ulasan', 'tanggal_review'];
    protected $casts = ['rating' => 'integer', 'tanggal_review' => 'datetime'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'id_customer'); }
    public function destinasi(): BelongsTo { return $this->belongsTo(DestinasiWisata::class, 'id_destinasi'); }
    public function tiket(): BelongsTo { return $this->belongsTo(Tiket::class, 'id_tiket'); }
}