<?php

namespace Tests\Feature;

use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowonganPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dialihkan_saat_membuka_form_buat_lowongan(): void
    {
        $this->get(route('dashboard.create-job'))->assertRedirect(route('login'));
    }

    public function test_user_bisa_membuat_lowongan_dan_muncul_di_katalog_freelance(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('dashboard.create-job.store'), [
            'judul' => 'Desainer Konten Media Sosial',
            'perusahaan' => 'Studio Karsa Banyumas',
            'kategori' => 'Freelance',
            'jenis' => 'desain',
            'metode' => 'remote',
            'kecamatan' => 'Purwokerto Utara',
            'deskripsi' => 'Membuat 12 desain konten Instagram dan 4 templat cerita untuk UMKM lokal.',
            'persyaratan' => 'Menguasai Figma, Canva, atau Adobe Illustrator.',
            'durasi' => '3 minggu',
            'batas_lamaran' => now()->addDays(10)->toDateString(),
            'bayaran' => '1.500.000',
        ]);

        $response->assertRedirect(route('dashboard.posts'));
        $this->assertDatabaseHas('lowongans', [
            'user_id' => $user->id,
            'judul' => 'Desainer Konten Media Sosial',
            'kategori' => 'Freelance',
            'jenis' => 'desain',
            'metode' => 'remote',
            'kecamatan' => 'Purwokerto Utara',
            'gaji_min' => 1500000,
            'gaji_max' => 1500000,
            'status' => 'aktif',
        ]);

        $this->actingAs($user)
            ->get(route('jobs.freelance', ['jenis' => 'desain', 'metode' => 'remote']))
            ->assertOk()
            ->assertSee('Desainer Konten Media Sosial')
            ->assertSee('Studio Karsa Banyumas');
    }

    public function test_validasi_lowongan_wajib_terisi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard.create-job'))
            ->post(route('dashboard.create-job.store'), [])
            ->assertRedirect(route('dashboard.create-job'))
            ->assertSessionHasErrors(['judul', 'perusahaan', 'kategori', 'jenis', 'metode', 'kecamatan', 'deskripsi', 'persyaratan', 'durasi', 'batas_lamaran', 'bayaran']);
    }

    public function test_lowongan_hybrid_bisa_difilter(): void
    {
        $user = User::factory()->create();
        Lowongan::factory()->for($user)->create([
            'judul' => 'UI Designer Hybrid',
            'metode' => 'hybrid',
            'kategori' => 'Part Time',
            'jenis' => 'desain',
        ]);

        $this->get(route('jobs.freelance', ['metode' => 'hybrid']))
            ->assertOk()
            ->assertSee('UI Designer Hybrid')
            ->assertSee('bg-emerald-50');
    }
}
