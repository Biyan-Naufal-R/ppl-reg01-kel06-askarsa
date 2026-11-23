<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Penyedia pada katalog statis diberi akun + profil jasa aktif agar
        // alur pemesanan bisa diuji dengan data yang sama seperti di katalog.
        $penyedia = [
            [
                'name' => 'Bima Saputra',
                'email' => 'bima.saputra@askarsa.test',
                'domisili' => 'Purwokerto Utara',
                'nama_profil' => 'Bima Siap Bantu',
                'tarif' => 25000,
                'kategori' => ['antar-jemput', 'titip-beli', 'antar-barang'],
                'deskripsi' => 'Bantu belanja, antar barang, dan urusan harian Anda.',
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti.rahmawati@askarsa.test',
                'domisili' => 'Sokaraja',
                'nama_profil' => 'Siti Bersih Rumah',
                'tarif' => 75000,
                'kategori' => ['bantuan-rumah'],
                'deskripsi' => 'Rumah kembali nyaman, rapi, dan bersih menyeluruh.',
            ],
            [
                'name' => 'Dimas Prasetyo',
                'email' => 'dimas.prasetyo@askarsa.test',
                'domisili' => 'Purwokerto Timur',
                'nama_profil' => 'Dimas Perbaikan Ringan',
                'tarif' => 50000,
                'kategori' => ['perbaikan'],
                'deskripsi' => 'Bantuan praktis untuk perawatan rumah sehari-hari.',
            ],
            [
                'name' => 'Rina Wulandari',
                'email' => 'rina.wulandari@askarsa.test',
                'domisili' => 'Baturraden',
                'nama_profil' => 'Rina Pendamping Harian',
                'tarif' => 40000,
                'kategori' => ['temani', 'titip-beli'],
                'deskripsi' => 'Teman bantu urusan keluarga dan kebutuhan harian.',
            ],
        ];

        foreach ($penyedia as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'domisili' => $data['domisili'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );

            $user->profilJasa()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_profil' => $data['nama_profil'],
                    'tarif' => $data['tarif'],
                    'kategori' => $data['kategori'],
                    'deskripsi' => $data['deskripsi'],
                    'aktif' => true,
                ]
            );
        }
    }
}
