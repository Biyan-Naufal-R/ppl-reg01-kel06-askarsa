<?php

namespace Database\Factories;

use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Lamaran> */
class LamaranFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lowongan_id' => Lowongan::factory(),
            'nama_pelamar' => $this->faker->name(),
            'email_pelamar' => $this->faker->safeEmail(),
            'cv_path' => 'cv/'.$this->faker->uuid().'.pdf',
            'cv_nama' => 'CV_'.$this->faker->lastName().'.pdf',
            'pesan' => 'Saya tertarik dengan lowongan ini dan siap bergabung.',
            'status' => 'menunggu',
        ];
    }
}
