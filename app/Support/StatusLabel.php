<?php

namespace App\Support;

final class StatusLabel
{
    public static function order(?string $status): string
    {
        return match ($status) {
            'PAID' => 'Lunas',
            'PENDING' => 'Menunggu pembayaran',
            'CANCELLED' => 'Dibatalkan',
            'EXPIRED' => 'Kedaluwarsa',
            'FAILED' => 'Gagal',
            default => 'Belum diproses',
        };
    }

    public static function payment(?string $status): string
    {
        return match ($status) {
            'PAID' => 'Lunas',
            'PENDING' => 'Menunggu pembayaran',
            'FAILED' => 'Gagal',
            'EXPIRED' => 'Kedaluwarsa',
            default => 'Belum dibayar',
        };
    }

    public static function ticket(?string $status): string
    {
        return match ($status) {
            'PENDING' => 'Menunggu pembayaran',
            'ACTIVE' => 'Aktif',
            'USED' => 'Sudah digunakan',
            'PARTIAL' => 'Sebagian digunakan',
            'CANCELLED' => 'Dibatalkan',
            'EXPIRED' => 'Kedaluwarsa',
            default => 'Tidak diketahui',
        };
    }

    public static function ticketStatus(iterable $statuses, ?string $orderStatus = null): string
    {
        $statuses = collect($statuses)->filter()->values();

        if ($statuses->isEmpty()) {
            return $orderStatus === 'PAID' ? 'ACTIVE' : ($orderStatus ?: 'PENDING');
        }

        if ($statuses->every(fn ($status) => $status === 'USED')) {
            return 'USED';
        }

        if ($statuses->contains('USED')) {
            return 'PARTIAL';
        }

        if ($statuses->every(fn ($status) => $status === 'CANCELLED')) {
            return 'CANCELLED';
        }

        return $statuses->first();
    }

    public static function article(?string $status): string
    {
        return $status === 'PUBLISHED' ? 'Diterbitkan' : 'Draf';
    }

    public static function account(?string $status): string
    {
        return match ($status) {
            'AKTIF' => 'Aktif',
            'NONAKTIF' => 'Nonaktif',
            default => 'Tidak diketahui',
        };
    }
}