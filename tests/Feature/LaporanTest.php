<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\Lowongan;
use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    public function test_tamu_dialihkan_saat_membuka_form_laporan(): void
    {
        $profil = ProfilJasa::factory()->create();

        $this->get(route('laporan.create.profil-jasa', $profil))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('laporans', 0);
    }

    public function test_user_bisa_membuka_form_laporan_profil_jasa_dan_lowongan(): void
    {
        $pelapor = User::factory()->create();
        $profil = ProfilJasa::factory()->create(['nama_profil' => 'Jasa Bima']);
        $lowongan = Lowongan::factory()->create(['judul' => 'Desainer Konten']);

        $this->actingAs($pelapor)
            ->get(route('laporan.create.profil-jasa', $profil))
            ->assertOk()
            ->assertSee('Jasa Bima')
            ->assertSee('Kategori laporan');

        $this->actingAs($pelapor)
            ->get(route('laporan.create.lowongan', $lowongan))
            ->assertOk()
            ->assertSee('Desainer Konten')
            ->assertSee('Menunggu Pemeriksaan');
    }

    public function test_user_bisa_mengirim_laporan_profil_jasa_dengan_bukti_privat(): void
    {
        $pelapor = User::factory()->create();
        $profil = ProfilJasa::factory()->create(['nama_profil' => 'Jasa Bima']);

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), [
                'jenis_objek' => 'profil_jasa',
                'objek_id' => $profil->id,
                'kategori' => 'penipuan',
                'penjelasan' => 'Penyedia meminta pembayaran di luar sistem.',
                'bukti' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('dashboard.reports'));

        $laporan = Laporan::first();
        $this->assertSame($pelapor->id, $laporan->pelapor_id);
        $this->assertSame('profil_jasa', $laporan->objek_type);
        $this->assertSame($profil->id, $laporan->objek_id);
        $this->assertSame(Laporan::STATUS_MENUNGGU, $laporan->status);
        $this->assertSame('bukti.jpg', $laporan->bukti_nama);
        Storage::disk('private')->assertExists($laporan->bukti_path);

        $this->get(route('dashboard.reports'))
            ->assertOk()
            ->assertSee('Jasa Bima')
            ->assertSee('Menunggu Pemeriksaan');
    }

    public function test_user_bisa_mengirim_laporan_lowongan_tanpa_bukti(): void
    {
        $pelapor = User::factory()->create();
        $lowongan = Lowongan::factory()->create(['judul' => 'Desainer Konten']);

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), [
                'jenis_objek' => 'lowongan',
                'objek_id' => $lowongan->id,
                'kategori' => 'informasi_tidak_sesuai',
                'penjelasan' => 'Detail gaji tidak sesuai dengan katalog.',
            ])
            ->assertRedirect(route('dashboard.reports'));

        $laporan = Laporan::first();
        $this->assertSame('lowongan', $laporan->objek_type);
        $this->assertSame($lowongan->id, $laporan->objek_id);
        $this->assertNull($laporan->bukti_path);
        $this->assertSame(Laporan::STATUS_MENUNGGU, $laporan->status);
    }

    public function test_bukti_harus_jpg_png_atau_pdf_dan_maksimal_lima_mb(): void
    {
        $pelapor = User::factory()->create();
        $profil = ProfilJasa::factory()->create();
        $data = [
            'jenis_objek' => 'profil_jasa',
            'objek_id' => $profil->id,
            'kategori' => 'lainnya',
            'penjelasan' => 'Ada informasi yang perlu ditinjau kembali.',
        ];

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), $data + [
                'bukti' => UploadedFile::fake()->create('bukti.txt', 100, 'text/plain'),
            ])
            ->assertSessionHasErrors('bukti');

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), $data + [
                'bukti' => UploadedFile::fake()->create('bukti.jpg', 6000, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('bukti');

        $this->assertDatabaseCount('laporans', 0);
    }

    public function test_tidak_bisa_melapor_objek_milik_sendiri(): void
    {
        $pelapor = User::factory()->create();
        $profil = ProfilJasa::factory()->create(['user_id' => $pelapor->id]);

        $this->actingAs($pelapor)
            ->from(route('services.index'))
            ->get(route('laporan.create.profil-jasa', $profil))
            ->assertRedirect(route('services.index'))
            ->assertSessionHasErrors('objek');

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), [
                'jenis_objek' => 'profil_jasa',
                'objek_id' => $profil->id,
                'kategori' => 'lainnya',
                'penjelasan' => 'Ada informasi yang perlu ditinjau kembali.',
            ])
            ->assertSessionHasErrors('objek');

        $this->assertDatabaseCount('laporans', 0);
    }

    public function test_tidak_bisa_melapor_objek_yang_sama_dua_kali_selama_belum_selesai(): void
    {
        $pelapor = User::factory()->create();
        $lowongan = Lowongan::factory()->create();
        $data = [
            'jenis_objek' => 'lowongan',
            'objek_id' => $lowongan->id,
            'kategori' => 'penipuan',
            'penjelasan' => 'Lowongan ini diduga tidak sesuai.',
        ];

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), $data)
            ->assertRedirect(route('dashboard.reports'));

        $data['kategori'] = 'lainnya';
        $this->actingAs($pelapor)
            ->post(route('laporan.store'), $data)
            ->assertSessionHasErrors('laporan');

        $this->assertDatabaseCount('laporans', 1);
    }

    public function test_laporan_saya_hanya_menampilkan_laporan_milik_sendiri(): void
    {
        $pelapor = User::factory()->create();
        $orangLain = User::factory()->create();

        $profilSaya = ProfilJasa::factory()->create(['nama_profil' => 'Jasa Milik Saya']);
        $profilOrangLain = ProfilJasa::factory()->create(['nama_profil' => 'Jasa Orang Lain']);

        Laporan::factory()->create([
            'pelapor_id' => $pelapor->id,
            'objek_type' => 'profil_jasa',
            'objek_id' => $profilSaya->id,
        ]);
        Laporan::factory()->create([
            'pelapor_id' => $orangLain->id,
            'objek_type' => 'profil_jasa',
            'objek_id' => $profilOrangLain->id,
        ]);

        $this->actingAs($pelapor)
            ->get(route('dashboard.reports'))
            ->assertOk()
            ->assertSee('Jasa Milik Saya')
            ->assertDontSee('Jasa Orang Lain');
    }

    public function test_bukti_hanya_bisa_diakses_pelapor(): void
    {
        $pelapor = User::factory()->create();
        $orangLain = User::factory()->create();
        $profil = ProfilJasa::factory()->create();

        $this->actingAs($pelapor)
            ->post(route('laporan.store'), [
                'jenis_objek' => 'profil_jasa',
                'objek_id' => $profil->id,
                'kategori' => 'penipuan',
                'penjelasan' => 'Penyedia meminta pembayaran di luar sistem.',
                'bukti' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            ]);

        $laporan = Laporan::first();

        $this->actingAs($pelapor)
            ->get(route('laporan.bukti', $laporan))
            ->assertOk();

        $this->actingAs($orangLain)
            ->get(route('laporan.bukti', $laporan))
            ->assertForbidden();
    }
}
