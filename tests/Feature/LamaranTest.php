<?php

namespace Tests\Feature;

use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LamaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dialihkan_ke_login_saat_membuka_form_lamaran(): void
    {
        $lowongan = Lowongan::factory()->create();

        $this->get(route('lamaran.create', $lowongan->slug))
            ->assertRedirect(route('login'));
    }

    public function test_user_bisa_melamar_lowongan_freelance_dengan_cv_pdf(): void
    {
        Storage::fake('local');
        $pemberiKerja = User::factory()->create();
        $pelamar = User::factory()->create(['name' => 'Nadia Ayuningtyas', 'email' => 'nadia@example.com']);
        $lowongan = Lowongan::factory()->for($pemberiKerja)->create(['status' => Lowongan::STATUS_AKTIF]);

        $response = $this->actingAs($pelamar)->post(route('lamaran.store', $lowongan->slug), [
            'cv' => UploadedFile::fake()->create('CV_Nadia.pdf', 200, 'application/pdf'),
            'pesan' => 'Saya siap bergabung.',
        ]);

        $response->assertRedirect(route('dashboard.applications'));
        $lamaran = Lamaran::first();
        $this->assertNotNull($lamaran);
        $this->assertSame($pelamar->id, $lamaran->user_id);
        $this->assertSame(Lamaran::STATUS_MENUNGGU, $lamaran->status);
        Storage::disk('local')->assertExists($lamaran->cv_path);

        $this->actingAs($pelamar)
            ->get(route('dashboard.applications'))
            ->assertOk()
            ->assertSee($lowongan->judul)
            ->assertSee('Menunggu Ditinjau');
    }

    public function test_user_tidak_bisa_melamar_lowongan_milik_sendiri_atau_dua_kali(): void
    {
        Storage::fake('local');
        $pemberiKerja = User::factory()->create();
        $lowongan = Lowongan::factory()->for($pemberiKerja)->create();

        $this->actingAs($pemberiKerja)
            ->get(route('lamaran.create', $lowongan->slug))
            ->assertRedirect(route('jobs.show', $lowongan->slug));

        $pelamar = User::factory()->create();
        Lamaran::factory()->for($pelamar)->for($lowongan)->create();

        $this->actingAs($pelamar)
            ->post(route('lamaran.store', $lowongan->slug), [
                'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('jobs.show', $lowongan->slug));
    }

    public function test_cv_harus_pdf_dan_maksimal_5mb(): void
    {
        Storage::fake('local');
        $pelamar = User::factory()->create();
        $lowongan = Lowongan::factory()->create();

        $this->actingAs($pelamar)
            ->from(route('lamaran.create', $lowongan->slug))
            ->post(route('lamaran.store', $lowongan->slug), [
                'cv' => UploadedFile::fake()->create('cv.png', 100, 'image/png'),
            ])
            ->assertRedirect(route('lamaran.create', $lowongan->slug))
            ->assertSessionHasErrors('cv');

        $this->actingAs($pelamar)
            ->from(route('lamaran.create', $lowongan->slug))
            ->post(route('lamaran.store', $lowongan->slug), [
                'cv' => UploadedFile::fake()->create('cv.pdf', 6000, 'application/pdf'),
            ])
            ->assertRedirect(route('lamaran.create', $lowongan->slug))
            ->assertSessionHasErrors('cv');
    }

    public function test_pemberi_kerja_bisa_mengubah_status_lamaran_dan_pelamar_melihatnya(): void
    {
        Storage::fake('local');
        $pemberiKerja = User::factory()->create();
        $pelamar = User::factory()->create();
        $lowongan = Lowongan::factory()->for($pemberiKerja)->create();
        $lamaran = Lamaran::factory()->for($pelamar)->for($lowongan)->create(['status' => Lamaran::STATUS_MENUNGGU]);

        $this->actingAs($pemberiKerja)
            ->post(route('lamaran.proses', $lamaran))
            ->assertRedirect();

        $this->assertDatabaseHas('lamarans', ['id' => $lamaran->id, 'status' => Lamaran::STATUS_DIPROSES]);

        $this->actingAs($pelamar)
            ->get(route('dashboard.applications'))
            ->assertOk()
            ->assertSee('Diproses');

        $this->actingAs($pemberiKerja)
            ->post(route('lamaran.terima', $lamaran))
            ->assertRedirect();

        $this->assertDatabaseHas('lamarans', ['id' => $lamaran->id, 'status' => Lamaran::STATUS_DITERIMA]);
    }

    public function test_cv_hanya_bisa_diakses_pelamar_dan_pemberi_kerja_terkait(): void
    {
        Storage::fake('local');
        $pemberiKerja = User::factory()->create();
        $pelamar = User::factory()->create();
        $orangLain = User::factory()->create();
        $lowongan = Lowongan::factory()->for($pemberiKerja)->create();
        $path = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')->store('cv', 'local');
        $lamaran = Lamaran::factory()->for($pelamar)->for($lowongan)->create(['cv_path' => $path, 'cv_nama' => 'cv.pdf']);

        $this->actingAs($pelamar)->get(route('lamaran.cv', $lamaran))->assertOk();
        $this->actingAs($pemberiKerja)->get(route('lamaran.cv', $lamaran))->assertOk();
        $this->actingAs($orangLain)->get(route('lamaran.cv', $lamaran))->assertForbidden();
    }
}
