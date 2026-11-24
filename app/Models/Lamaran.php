<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lamaran extends Model
{
    /** @use HasFactory<\Database\Factories\LamaranFactory> */
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'user_id',
        'lowongan_id',
        'nama_pelamar',
        'email_pelamar',
        'cv_path',
        'cv_nama',
        'pesan',
        'status',
        'direspon_at',
    ];

    protected function casts(): array
    {
        return [
            'direspon_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class);
    }

    public function dimilikiOleh(User $user): bool
    {
        return (int) $this->user_id === (int) $user->id;
    }

    public function lowongannyaMilik(User $user): bool
    {
        return (int) $this->lowongan?->user_id === (int) $user->id;
    }

    /** @return array<string, string> */
    public static function labelStatus(): array
    {
        return [
            self::STATUS_MENUNGGU => 'Menunggu Ditinjau',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /** @return array<string, string> */
    public static function warnaStatus(): array
    {
        return [
            self::STATUS_MENUNGGU => 'border-amber-200 bg-amber-50 text-amber-800',
            self::STATUS_DIPROSES => 'border-sky-200 bg-sky-50 text-sky-700',
            self::STATUS_DITERIMA => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            self::STATUS_DITOLAK => 'border-rose-200 bg-rose-50 text-rose-700',
        ];
    }
}
