<?php

namespace Database\Factories;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pelanggan_id' => User::factory(),
            'penyedia_id' => User::factory(),
            'judul' => fake()->sentence(3),
            'deskripsi' => fake()->paragraph(),
            'harga' => fake()->numberBetween(50, 500) * 1000,
            'status' => Pesanan::STATUS_MENUNGGU,
        ];
    }

    public function diproses(): static
    {
        return $this->state(fn () => ['status' => Pesanan::STATUS_DIPROSES]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => [
            'status' => Pesanan::STATUS_SELESAI,
            'selesai_pada' => now(),
        ]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => [
            'status' => Pesanan::STATUS_DITOLAK,
            'alasan_penolakan' => 'Jadwal tidak tersedia.',
            'direspon_pada' => now(),
        ]);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn () => ['status' => Pesanan::STATUS_DIBATALKAN]);
    }
}