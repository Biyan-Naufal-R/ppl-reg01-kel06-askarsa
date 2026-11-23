<?php

namespace Database\Factories;

use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfilJasa>
 */
class ProfilJasaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_profil' => fake()->firstName().' Siap Bantu',
            'tarif' => fake()->numberBetween(10, 100) * 1000,
            'kategori' => [fake()->randomElement(array_keys(config('jasa.kategori')))],
            'deskripsi' => fake()->sentence(),
            'aktif' => true,
            'tersedia' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['aktif' => false]);
    }

    public function tidakTersedia(): static
    {
        return $this->state(fn () => ['tersedia' => false]);
    }
}
