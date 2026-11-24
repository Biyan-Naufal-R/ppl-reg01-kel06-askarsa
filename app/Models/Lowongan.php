<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lowongan extends Model
{
    /** @use HasFactory<\Database\Factories\LowonganFactory> */
    use HasFactory;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DITUTUP = 'ditutup';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $fillable = [
        'user_id',
        'judul',
        'perusahaan',
        'slug',
        'kategori',
        'jenis',
        'metode',
        'kecamatan',
        'deskripsi',
        'persyaratan',
        'pendidikan_minimum',
        'jurusan',
        'durasi',
        'batas_lamaran',
        'gaji_min',
        'gaji_max',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'batas_lamaran' => 'date',
            'gaji_min' => 'integer',
            'gaji_max' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lamarans(): HasMany
    {
        return $this->hasMany(Lamaran::class);
    }

    /** Lowongan yang masih boleh dilihat publik (nonaktif disembunyikan). */
    public function scopeTampilDiKatalog(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_AKTIF, self::STATUS_DITUTUP]);
    }

    public function bisaDilamar(): bool
    {
        return $this->status === self::STATUS_AKTIF
            && $this->batas_lamaran?->toDateString() >= today()->toDateString();
    }

    public static function buatSlug(string $judul): string
    {
        $dasar = Str::slug($judul) ?: 'lowongan';

        return $dasar.'-'.Str::lower(Str::random(6));
    }

    /** Ubah model menjadi format array yang dipakai kartu dan halaman detail. */
    public function dataKartu(): array
    {
        $rentang = number_format($this->gaji_min, 0, ',', '.');
        if ((int) $this->gaji_max !== (int) $this->gaji_min) {
            $rentang .= '–'.number_format($this->gaji_max, 0, ',', '.');
        }
        $satuanGaji = $this->kategori === 'Full-Time' ? ' / bulan' : ' / proyek';

        return [
            'lowongan_id' => $this->id,
            'slug' => $this->slug,
            'company' => $this->perusahaan,
            'title' => $this->judul,
            'type' => $this->kategori,
            'mode' => config('lowongan.metode.'.$this->metode, $this->metode),
            'location' => $this->kecamatan.', Banyumas',
            'salary' => 'Rp'.$rentang.$satuanGaji,
            'kecamatan' => $this->kecamatan,
            'jenis' => $this->jenis,
            'metode' => $this->metode,
            'gaji_min' => (int) $this->gaji_min,
            'gaji_max' => (int) $this->gaji_max,
            'deskripsi' => $this->deskripsi,
            'persyaratan' => $this->persyaratan,
            'pendidikan_minimum' => $this->pendidikan_minimum,
            'pendidikan_label' => $this->pendidikan_minimum
                ? config('lowongan.pendidikan.'.$this->pendidikan_minimum, $this->pendidikan_minimum)
                : null,
            'jurusan' => $this->jurusan,
            'durasi' => $this->durasi,
            'batas_lamaran' => $this->batas_lamaran?->toDateString(),
            'status' => $this->status,
            'bisa_dilamar' => $this->bisaDilamar(),
            'created_at' => $this->created_at?->format('d F Y'),
        ];
    }
}
