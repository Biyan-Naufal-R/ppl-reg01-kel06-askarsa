<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'pelanggan_id',
        'penyedia_id',
        'judul',
        'deskripsi',
        'harga',
        'status',
        'catatan_penyelesaian',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'selesai_pada' => 'datetime',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyedia_id');
    }

    /** Pesanan yang melibatkan user, baik sebagai pelanggan maupun penyedia. */
    public function scopeMilik(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('pelanggan_id', $user->id)
                ->orWhere('penyedia_id', $user->id);
        });
    }

    /** Pesanan yang sudah berakhir (selesai atau dibatalkan). */
    public function scopeRiwayat(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SELESAI, self::STATUS_DIBATALKAN]);
    }

    public function bisaDiselesaikan(): bool
    {
        return $this->status === self::STATUS_DIPROSES;
    }

    public function dimilikiOleh(User $user): bool
    {
        return (int) $this->pelanggan_id === $user->id
            || (int) $this->penyedia_id === $user->id;
    }
}