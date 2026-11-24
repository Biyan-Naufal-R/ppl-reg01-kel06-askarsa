<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\Lowongan> */
class LowonganFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $judul = $this->faker->unique()->jobTitle();

        return [
            'user_id' => User::factory(),
            'judul' => $judul,
            'perusahaan' => $this->faker->company(),
            'slug' => Str::slug($judul).'-'.Str::lower(Str::random(6)),
            'kategori' => 'Freelance',
            'jenis' => 'desain',
            'metode' => 'remote',
            'kecamatan' => $this->faker->randomElement(config('domisili.kecamatan')),
            'deskripsi' => 'Menyelesaikan pekerjaan kreatif sesuai brief dan tenggat yang disepakati.',
            'persyaratan' => 'Memiliki portofolio relevan dan mampu berkomunikasi dengan baik.',
            'durasi' => '2 minggu',
            'batas_lamaran' => now()->addDays(7)->toDateString(),
            'gaji_min' => 1000000,
            'gaji_max' => 1000000,
            'status' => 'aktif',
        ];
    }
}
