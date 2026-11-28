<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Laporan extends Model
{
    /** @use HasFactory<\Database\Factories\LaporanFactory> */
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'pelapor_id',
        'objek_type',
        'objek_id',
        'kategori',
        'penjelasan',
        'bukti_path',
        'bukti_nama',
        'status',
    ];

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function objek(): MorphTo
    {
        return $this->morphTo();
    }

    /** Laporan yang masih menunggu pemeriksaan. */
    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }

    public function labelStatus(): string
    {
        return config('laporan.status.'.$this->status, $this->status);
    }

    public function labelKategori(): string
    {
        return config('laporan.kategori.'.$this->kategori, $this->kategori);
    }

    public function labelObjek(): string
    {
        return match (true) {
            $this->objek instanceof ProfilJasa => $this->objek->nama_profil,
            $this->objek instanceof Lowongan => $this->objek->judul,
            default => '-',
        };
    }
}
