<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $primaryKey = 'id_pemesanan';

    protected $fillable = ['id_customer', 'ketua_nama', 'ketua_email', 'ketua_no_hp', 'ketua_jenis_tiket', 'anggota_names', 'id_destinasi', 'kode_booking', 'tanggal_pemesanan', 'tanggal_kunjungan', 'total_harga', 'status_pemesanan', 'batas_waktu_pembayaran'];

    protected $casts = ['anggota_names' => 'array', 'tanggal_pemesanan' => 'datetime', 'tanggal_kunjungan' => 'date', 'total_harga' => 'decimal:2', 'batas_waktu_pembayaran' => 'datetime'];

    public function getAnggotaNamesAttribute($value): array
    {
        return $this->normalizeAnggotaNames($value);
    }

    public function getAnggotaNamesTextAttribute(): string
    {
        return collect($this->anggota_names)
            ->map(fn (mixed $member) => is_array($member) ? trim((string) data_get($member, 'nama', '')) : trim((string) $member))
            ->filter()
            ->implode(', ');
    }

    public function setAnggotaNamesAttribute($value): void
    {
        $this->attributes['anggota_names'] = json_encode($this->normalizeAnggotaNames($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function normalizeAnggotaNames(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                preg_match_all('/"nama"\s*:\s*"((?:\\.|[^"\\])*)"/u', $value, $matches, PREG_SET_ORDER);
                if (! empty($matches)) {
                    $value = collect($matches)
                        ->map(fn (array $match) => ['nama' => stripcslashes($match[1])])
                        ->values()
                        ->all();
                } else {
                    return [];
                }
            }
        }

        if (! is_array($value)) {
            return [];
        }

        if (array_is_list($value)) {
            $members = $value;
        } elseif (array_key_exists('nama', $value) || array_key_exists('id_jenis_tiket', $value)) {
            $members = [$value];
        } else {
            $members = [];
        }

        return collect($members)
            ->map(function (mixed $member): ?array {
                if (is_string($member)) {
                    $member = ['nama' => $member];
                }

                if (! is_array($member)) {
                    return null;
                }

                $name = trim((string) data_get($member, 'nama', ''));
                if ($name === '') {
                    return null;
                }

                $normalizedMember = ['nama' => $name];
                if (array_key_exists('id_jenis_tiket', $member)) {
                    $normalizedMember['id_jenis_tiket'] = (int) $member['id_jenis_tiket'];
                }

                return $normalizedMember;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(DestinasiWisata::class, 'id_destinasi');
    }

    public function detailPemesanan(): HasMany
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pemesanan');
    }

    public function tiket(): HasMany
    {
        return $this->hasMany(Tiket::class, 'id_pemesanan');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_pemesanan');
    }

    public function perubahan(): HasMany
    {
        return $this->hasMany(PerubahanPemesanan::class, 'id_pemesanan');
    }

    public static function expirePendingPayments(?int $destinationId = null): void
    {
        $expiredBookings = static::query()
            ->where('status_pemesanan', 'PENDING')
            ->whereNotNull('batas_waktu_pembayaran')
            ->where('batas_waktu_pembayaran', '<', Carbon::now())
            ->when($destinationId, fn ($query) => $query->where('id_destinasi', $destinationId))
            ->pluck('id_pemesanan');

        if ($expiredBookings->isEmpty()) {
            return;
        }

        static::whereIn('id_pemesanan', $expiredBookings)->update(['status_pemesanan' => 'EXPIRED']);
        Pembayaran::whereIn('id_pemesanan', $expiredBookings)
            ->where('status_pembayaran', 'PENDING')
            ->update(['status_pembayaran' => 'EXPIRED', 'transaction_status' => 'expire']);
    }

    public function expireTicketsIfPastVisitDate(): void
    {
        if ($this->tanggal_kunjungan && date('Y-m-d', strtotime((string) $this->tanggal_kunjungan)) < today()->toDateString()) {
            $this->tiket()->where('status_tiket', 'ACTIVE')->update(['status_tiket' => 'EXPIRED']);
        }
    }
}
