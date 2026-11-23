<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilJasa extends Model
{
    /** @use HasFactory<\Database\Factories\ProfilJasaFactory> */
    use HasFactory;

    protected $table = 'profil_jasa';

    protected $fillable = [
        'user_id',
        'nama_profil',
        'tarif',
        'kategori',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tarif' => 'integer',
            'kategori' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Hanya profil jasa yang sedang diaktifkan pemiliknya. */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
