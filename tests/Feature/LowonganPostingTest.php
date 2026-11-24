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

    public function test_form_create_job_menampilkan_pilihan_full_time_dan_field_pendidikan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard.create-job'))
            ->assertOk()
            ->assertSee('Full-Time')
            ->assertSee('Pendidikan minimum')
            ->assertSee('Jurusan (opsional)')
            ->assertSee('sinkronForm');
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

    public function test_user_bisa_membuat_lowongan_full_time_tanpa_jenis_dan_durasi(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('dashboard.create-job.store'), [
            'judul' => 'Staf Operasional Full Time',
            'perusahaan' => 'CV Satria Niaga',
            'kategori' => 'Full-Time',
            'jenis' => 'desain',
            'durasi' => '3 minggu',
            'metode' => 'onsite',
            'kecamatan' => 'Purwokerto Selatan',
            'deskripsi' => 'Menangani operasional harian dan koordinasi tim lapangan.',
            'persyaratan' => 'Minimal S1 dan mampu bekerja dalam tim.',
            'pendidikan_minimum' => 's1',
            'jurusan' => 'Manajemen',
            'batas_lamaran' => now()->addDays(10)->toDateString(),
            'bayaran' => '4.500.000',
        ]);

        $response->assertRedirect(route('dashboard.posts'));
        $this->assertDatabaseHas('lowongans', [
            'user_id' => $user->id,
            'judul' => 'Staf Operasional Full Time',
            'kategori' => 'Full-Time',
            'jenis' => '',
            'durasi' => '',
            'pendidikan_minimum' => 's1',
            'jurusan' => 'Manajemen',
            'gaji_min' => 4500000,
            'gaji_max' => 4500000,
            'status' => 'aktif',
        ]);

        $lowongan = Lowongan::firstWhere('judul', 'Staf Operasional Full Time');

        $this->get(route('jobs.fulltime'))
            ->assertOk()
            ->assertSee('Staf Operasional Full Time')
            ->assertSee('Pendidikan: S1')
            ->assertSee('Rp4.500.000 / bulan');

        $this->get(route('jobs.show', $lowongan->slug))
            ->assertOk()
            ->assertSee('Gaji / bulan')
            ->assertSee('Pendidikan minimum')
            ->assertSee('S1')
            ->assertSee('Manajemen')
            ->assertDontSee('Durasi proyek');
    }

    public function test_lowongan_full_time_wajib_memiliki_pendidikan_minimum(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard.create-job'))
            ->post(route('dashboard.create-job.store'), [
                'judul' => 'Staf Administrasi Full Time',
                'perusahaan' => 'CV Satria Niaga',
                'kategori' => 'Full-Time',
                'metode' => 'onsite',
                'kecamatan' => 'Purwokerto Selatan',
                'deskripsi' => 'Mengelola administrasi dan dokumentasi operasional perusahaan.',
                'persyaratan' => 'Teliti dan mampu menggunakan perkantoran.',
                'batas_lamaran' => now()->addDays(10)->toDateString(),
                'bayaran' => '3.000.000',
            ])
            ->assertRedirect(route('dashboard.create-job'))
            ->assertSessionHasErrors(['pendidikan_minimum'])
            ->assertSessionDoesntHaveErrors(['jenis', 'durasi']);
    }

    public function test_edit_lowongan_freelance_ke_full_time_menghapus_jenis_dan_durasi(): void
    {
        $user = User::factory()->create();
        $lowongan = Lowongan::factory()->for($user)->create(['kategori' => 'Freelance']);

        $this->actingAs($user)->put(route('dashboard.create-job.update', $lowongan), [
            'judul' => 'Staf IT Full Time',
            'perusahaan' => 'CV Solusi Digital',
            'kategori' => 'Full-Time',
            'metode' => 'hybrid',
            'kecamatan' => 'Purwokerto Utara',
            'deskripsi' => 'Menjaga infrastruktur teknologi dan membantu kebutuhan tim internal.',
            'persyaratan' => 'Menguasai jaringan komputer dan sistem operasi Linux.',
            'pendidikan_minimum' => 'd3',
            'jurusan' => 'Teknik Informatika',
            'batas_lamaran' => now()->addDays(10)->toDateString(),
            'bayaran' => '5.000.000',
        ])->assertRedirect(route('dashboard.posts'));

        $this->assertDatabaseHas('lowongans', [
            'id' => $lowongan->id,
            'kategori' => 'Full-Time',
            'jenis' => '',
            'durasi' => '',
            'pendidikan_minimum' => 'd3',
            'jurusan' => 'Teknik Informatika',
            'gaji_min' => 5000000,
            'gaji_max' => 5000000,
        ]);
    }
}
