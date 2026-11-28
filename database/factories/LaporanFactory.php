<?php

namespace Database\Factories;

use App\Models\Laporan;
use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laporan>
 */
class LaporanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pelapor_id' => User::factory(),
            'objek_type' => 'profil_jasa',
            'objek_id' => ProfilJasa::factory(),
            'kategori' => array_key_first(config('laporan.kategori')),
            'penjelasan' => fake()->sentence(12),
            'status' => Laporan::STATUS_MENUNGGU,
        ];
    }
}
