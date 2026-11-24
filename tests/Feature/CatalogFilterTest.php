<?php

namespace Tests\Feature;

use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFilterTest extends TestCase
{
    use RefreshDatabase;

    private function buatPenyedia(string $nama, string $domisili, array $kategori, int $tarif): User
    {
        $user = User::factory()->create([
            'name' => $nama,
            'domisili' => $domisili,
        ]);

        ProfilJasa::factory()->create([
            'user_id' => $user->id,
            'nama_profil' => $nama.' Siap Bantu',
            'kategori' => $kategori,
            'tarif' => $tarif,
            'deskripsi' => 'Layanan '.$nama.' untuk kebutuhan harian.',
        ]);

        return $user;
    }

    public function test_tamu_bisa_memfilter_kecamatan(): void
    {
        $this->buatPenyedia('Andi Purwokerto', 'Purwokerto Utara', ['temani'], 20000);
        $this->buatPenyedia('Budi Sokaraja', 'Sokaraja', ['temani'], 30000);

        $this->get(route('services.index', ['kecamatan' => 'Sokaraja']))
            ->assertOk()
            ->assertSee('Budi Sokaraja')
            ->assertDontSee('Andi Purwokerto');
    }

    public function test_tamu_bisa_memfilter_kategori_jasa(): void
    {
        $this->buatPenyedia('Rani Temani', 'Sokaraja', ['temani'], 20000);
        $this->buatPenyedia('Dani Titip', 'Sokaraja', ['titip-beli'], 25000);

        $this->get(route('services.index', ['kategori' => 'temani']))
            ->assertOk()
            ->assertSee('Rani Temani')
            ->assertDontSee('Dani Titip');
    }

    public function test_tamu_bisa_memfilter_tarif_minimal_dan_maksimal(): void
    {
        $this->buatPenyedia('Andi Murah', 'Sokaraja', ['temani'], 15000);
        $this->buatPenyedia('Budi Menengah', 'Sokaraja', ['temani'], 50000);
        $this->buatPenyedia('Siti Mahal', 'Sokaraja', ['temani'], 120000);

        $this->get(route('services.index', ['tarif_min' => 40000, 'tarif_max' => 100000]))
            ->assertOk()
            ->assertSee('1 profil jasa ditemukan')
            ->assertSee('Budi Menengah')
            ->assertDontSee('Andi Murah')
            ->assertDontSee('Siti Mahal');
    }

    public function test_filter_gabungan_bisa_dipakai_user_yang_sudah_login(): void
    {
        $user = User::factory()->create();

        $this->buatPenyedia('Andi Cocok', 'Purwokerto Timur', ['antar-jemput'], 50000);
        $this->buatPenyedia('Budi Beda Kecamatan', 'Sokaraja', ['antar-jemput'], 50000);
        $this->buatPenyedia('Siti Beda Kategori', 'Purwokerto Timur', ['perbaikan'], 50000);

        $this->actingAs($user)
            ->get(route('services.index', [
                'kecamatan' => 'Purwokerto Timur',
                'kategori' => 'antar-jemput',
                'tarif_min' => 40000,
                'tarif_max' => 60000,
            ]))
            ->assertOk()
            ->assertSee('Andi Cocok')
            ->assertDontSee('Budi Beda Kecamatan')
            ->assertDontSee('Siti Beda Kategori');
    }

    public function test_pencarian_keyword_menyaring_nama_dan_deskripsi(): void
    {
        $this->buatPenyedia('Andi Khusus', 'Sokaraja', ['temani'], 20000);
        $this->buatPenyedia('Budi Biasa', 'Sokaraja', ['temani'], 20000);

        $this->get(route('services.index', ['q' => 'Khusus']))
            ->assertOk()
            ->assertSee('Andi Khusus')
            ->assertDontSee('Budi Biasa');
    }

    public function test_reset_filter_menampilkan_semua_profil(): void
    {
        $this->buatPenyedia('Andi Satu', 'Sokaraja', ['temani'], 20000);
        $this->buatPenyedia('Budi Dua', 'Sokaraja', ['titip-beli'], 30000);

        $this->get(route('services.index', ['kategori' => 'temani']))
            ->assertOk()
            ->assertSee('1 profil jasa ditemukan');

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('2 profil jasa ditemukan')
            ->assertSee('Andi Satu')
            ->assertSee('Budi Dua');
    }
}
